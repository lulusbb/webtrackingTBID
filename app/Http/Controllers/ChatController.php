<?php

namespace App\Http\Controllers;

use App\Models\RoleMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Role yang diizinkan pakai chat (CEO sudah termasuk).
     */
    protected array $allowed = ['admin','marketing','studio','project','ceo'];

    /** Normalisasi role ke lower-case tanpa spasi */
    protected function norm(?string $role): string
    {
        return strtolower(trim($role ?? ''));
    }

    /** Validasi target: izinkan 'all' */
    protected function guardTarget(string $role): string
    {
        $role = $this->norm($role);
        if ($role === 'all') return 'all';
        abort_unless(in_array($role, $this->allowed, true), 422, 'Role tidak valid');
        return $role;
    }

    /** Validasi role biasa (bukan 'all') */
    protected function guardRole(string $role): string
    {
        $role = $this->norm($role);
        abort_unless(in_array($role, $this->allowed, true), 422, 'Role tidak valid');
        return $role;
    }

    /**
     * Kunci room: pasangan role di-sort; jika melibatkan 'all' → 'all'
     * Contoh: room('marketing','studio') => "marketing|studio"
     */
    protected function room(string $a, string $b): string
    {
        $a = $this->norm($a);
        $b = $this->norm($b);
        if ($a === 'all' || $b === 'all') return 'all';
        $p = [$a, $b];
        sort($p);
        return implode('|', $p);
    }

    /** (opsional) halaman penuh */
    public function index()
    {
        return view('chat.index');
    }

    /**
     * Ringkas unread untuk $meRole → [total, byRole[]]
     */
    protected function unreadBreakdown(string $meRole): array
    {
        $rows = RoleMessage::query()
            ->whereNull('seen_at')
            ->where('recipient_role', $meRole)
            ->select('sender_role', DB::raw('COUNT(*) as c'))
            ->groupBy('sender_role')
            ->get();

        $by = [];
        $total = 0;
        foreach ($rows as $r) {
            $by[$r->sender_role] = (int) $r->c;
            $total += (int) $r->c;
        }
        return [$total, $by];
    }

    /**
     * GET /chat/feed?role=marketing|studio|project|admin|ceo|all
     * (mendukung param lama 'to' juga)
     */
    public function feed(Request $r)
    {
        $meRole = $this->norm(Auth::user()->role ?? '');
        abort_unless(in_array($meRole, $this->allowed, true), 403);

        // dukung kedua nama parameter: role / to
        $target = $r->query('role', $r->query('to', 'all'));
        $toRole = $this->guardTarget($target);

        // ===== Grup ALL (broadcast) =====
        if ($toRole === 'all') {
            // Ambil pesan broadcast yang relevan dengan saya:
            // - baris yang ditujukan ke saya (recipient_role = saya)
            // - atau baris yang saya kirim (sender_role = saya)
            $rows = RoleMessage::query()
                ->where('room', 'all')
                ->where(function ($q) use ($meRole) {
                    $q->where('recipient_role', $meRole)
                      ->orWhere('sender_role', $meRole);
                })
                ->orderBy('created_at')
                ->get(['id','sender_role','body','created_at']);

            // Dedup pesan saya sendiri (karena broadcast di-insert per role)
            $seen = [];
            $items = [];
            foreach ($rows as $m) {
                $key = $m->sender_role.'|'.Carbon::parse($m->created_at)->format('Y-m-d H:i:s').'|'.$m->body;
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $items[] = [
                    'me'   => $m->sender_role === $meRole,
                    'role' => $m->sender_role,
                    'body' => (string) $m->body,
                    'time' => Carbon::parse($m->created_at)->diffForHumans(),
                ];
            }

            [$unreadTotal, $unreadBy] = $this->unreadBreakdown($meRole);

            return response()->json([
                'items'           => $items,
                'unread_total'    => $unreadTotal,
                'unread_by_role'  => $unreadBy,   // untuk dot hijau per role
            ]);
        }

        // ===== One-to-one antar role =====
        $room = $this->room($meRole, $toRole);

        $rows = RoleMessage::query()
            ->where('room', $room)
            ->orderBy('created_at')
            ->limit(200)
            ->get(['id','sender_role','body','created_at']);

        $items = $rows->map(function ($m) use ($meRole) {
            return [
                'me'   => $m->sender_role === $meRole,
                'role' => $m->sender_role,
                'body' => (string) $m->body,
                'time' => Carbon::parse($m->created_at)->diffForHumans(),
            ];
        })->values();

        [$unreadTotal, $unreadBy] = $this->unreadBreakdown($meRole);

        return response()->json([
            'items'           => $items,
            'unread_total'    => $unreadTotal,
            'unread_by_role'  => $unreadBy,     // untuk dot hijau per role
        ]);
    }

    /** GET /chat/unread -> {count, by_role} (termasuk broadcast ke saya) */
public function unread()
{
    $meRole = $this->norm(Auth::user()->role ?? '');
    abort_unless(in_array($meRole, $this->allowed, true), 403);

    // total semua unread ke saya (broadcast + 1-1)
    $total = RoleMessage::whereNull('seen_at')
        ->where('recipient_role', $meRole)
        ->count();

    // khusus broadcast (room = 'all') yang ditujukan ke saya
    $broadcast = RoleMessage::whereNull('seen_at')
        ->where('recipient_role', $meRole)
        ->where('room', 'all')
        ->count();

    // unread 1-1, dikelompokkan per pengirim (sender_role)
    $rows = RoleMessage::select('sender_role', DB::raw('COUNT(*) as c'))
        ->whereNull('seen_at')
        ->where('recipient_role', $meRole)
        ->where('room', '<>', 'all')
        ->groupBy('sender_role')
        ->get();

    $by = [];
    foreach ($rows as $r) {
        $by[$r->sender_role] = (int) $r->c;
    }

    return response()->json([
        'count'      => $total,       // semua unread
        'broadcast'  => $broadcast,   // hanya broadcast
        'by_role'    => $by,          // 1-1 per role pengirim
    ]);
}


    /**
     * POST /chat/send
     * JSON body: { role: "all|admin|marketing|studio|project|ceo", body: "..." }
     * (mendukung alias 'to' untuk kompatibilitas lama)
     */
    public function send(Request $r)
    {
        $me     = Auth::user();
        $meRole = $this->norm($me->role ?? '');
        abort_unless(in_array($meRole, $this->allowed, true), 403);

        $toRole = $this->guardTarget($r->input('role', $r->input('to', '')));
        $body   = trim((string) $r->input('body', ''));
        abort_if($body === '', 422, 'Pesan kosong');

        // ===== Broadcast ke semua role lain =====
        if ($toRole === 'all') {
            $targets = array_values(array_diff($this->allowed, [$meRole]));
            if (empty($targets)) {
                return response()->json(['ok' => true, 'broadcast' => true, 'to' => []]);
            }

            $now  = now();
            $rows = [];
            foreach ($targets as $t) {
                $rows[] = [
                    'room'           => 'all',
                    'sender_id'      => $me->id,
                    'sender_role'    => $meRole,
                    'sender_name'    => $me->name ?? '',
                    'recipient_role' => $t,
                    'body'           => $body,
                    'seen_at'        => null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
            RoleMessage::insert($rows);

            return response()->json([
                'ok'        => true,
                'broadcast' => true,
                'to'        => $targets,
                'at'        => $now->toIso8601String(),
            ]);
        }

        // ===== Kirim one-to-one =====
        $room = $this->room($meRole, $toRole);

        $msg = RoleMessage::create([
            'room'           => $room,
            'sender_id'      => $me->id,
            'sender_role'    => $meRole,
            'sender_name'    => $me->name ?? '',
            'recipient_role' => $toRole,
            'body'           => $body,
            'seen_at'        => null,
        ]);

        return response()->json([
            'ok' => true,
            'id' => $msg->id,
            'at' => $msg->created_at?->toIso8601String(),
        ]);
    }

    /**
     * POST /chat/seen
     * JSON body: { role: "all|…"} (mendukung alias 'to')
     */
// POST /chat/seen
public function markSeen(Request $r)
{
    $meRole = $this->norm(Auth::user()->role ?? '');
    abort_unless(in_array($meRole, $this->allowed, true), 403);

    $other = $this->guardTarget($r->input('role', $r->input('to', 'all')));

    if ($other === 'all') {
        // HANYA tandai broadcast ke saya sebagai sudah dibaca
        RoleMessage::whereNull('seen_at')
            ->where('recipient_role', $meRole)
            ->where('room', 'all')
            ->update(['seen_at' => now()]);
        return response()->json(['ok' => true]);
    }

    $room = $this->room($meRole, $other);

    RoleMessage::whereNull('seen_at')
        ->where('recipient_role', $meRole)
        ->where('room', $room)
        ->update(['seen_at' => now()]);

    return response()->json(['ok' => true]);
}

    /** Alias ke feed */
    public function history(Request $r)
    {
        return $this->feed($r);
    }
}
