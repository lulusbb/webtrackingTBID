<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /* ======================= Helpers ======================= */

    /** Ambil ekspresi nama yang aman (COALESCE beberapa kandidat). */
    protected function nameExprFor(string $table): string
    {
        $cands = ['nama','nama_klien','klien','client_name','pelanggan','pemilik','owner','kontak','name'];
        $cols  = [];
        foreach ($cands as $c) {
            if (Schema::hasColumn($table, $c)) $cols[] = $c;
        }
        return empty($cols)
            ? "'(Tanpa nama)'"
            : 'COALESCE('.implode(',', $cols).", '(Tanpa nama)')";
    }

    /**
     * Pilih waktu terbaik (punya jam) lalu fallback ke kolom bertipe date.
     * Return: [ "<expr> as created_at", "UNIX_TIMESTAMP(<expr>) as created_ts" ]
     */
    protected function coalescedDateExpr(string $table, bool $isCancel = false): array
    {
        $cands = $isCancel
            ? ['canceled_at','rejected_at','updated_at','created_at','schedule_at','approved_at',
               'tanggal_mulai','tanggal_selesai','survey_done_at','tanggal_masuk','tgl_masuk','tanggal','tgl']
            : ['updated_at','created_at','approved_at','schedule_at',
               'tanggal_mulai','tanggal_selesai','survey_done_at','tanggal_masuk','tgl_masuk','tanggal','tgl'];

        $avail = [];
        foreach ($cands as $c) {
            if (Schema::hasColumn($table, $c)) $avail[] = $c;
        }

        $core = empty($avail)
            ? 'CURRENT_TIMESTAMP'
            : 'COALESCE('.implode(',', $avail).', CURRENT_TIMESTAMP)';

        return [$core.' as created_at', 'UNIX_TIMESTAMP('.$core.') as created_ts'];
    }

    /* ======================= Feed JSON ===================== */

    public function feed(Request $r)
    {
        $sources = [
            // MARKETING
            'kliens'         => 'Marketing - Klien Baru',
            // STUDIO
            'klienfixsurvei' => 'Studio - Klien Survei',
            'denahs'         => 'Studio - Denah',
            'exteriors'      => 'Studio - 3D Desain',
            'meps'           => 'Studio - MEP & Spek Material',
            'tahap_akhirs'   => 'Studio - Serter Desain',
            'delegasirab'    => 'Studio - Delegasi RAB',
            // PROJECT
            'struktur_3ds'   => 'Project - 3D Struktur',
            'skemas'         => 'Project - Skema Plumbing',
            'rabs'           => 'Project - RAB',
            'mous'           => 'Project - MOU',
            'proyekjalans'   => 'Project - Proyek Berjalan',
            'proyekselesaii' => 'Project - Proyek Selesai',
        ];

        $cancelSources = [
            'klien_cancels'       => 'Marketing - Cancel',
            'survei_cancel'       => 'Studio - Klien Survei (Cancel)',
            'survei_cancels'      => 'Studio - Klien Survei (Cancel)',
            'denah_cancels'       => 'Studio - Denah (Cancel)',
            'exterior_cancels'    => 'Studio - 3D Desain (Cancel)',
            'mep_cancels'         => 'Studio - MEP & Spek (Cancel)',
            'tahapakhir_cancels'  => 'Studio - Serter Desain (Cancel)',
            'struktur_3d_cancels' => 'Project - 3D Struktur (Cancel)',
            'skema_cancels'       => 'Project - Skema (Cancel)',
            'rab_cancels'         => 'Project - RAB (Cancel)',
            'mou_cancels'         => 'Project - MOU (Cancel)',
        ];

        $parts = [];

        foreach ($sources as $table => $label) {
            if (!Schema::hasTable($table)) continue;
            $nameExpr = $this->nameExprFor($table).' as nama';
            [$dateExpr,$unixExpr] = $this->coalescedDateExpr($table, false);
            $parts[] = DB::table($table)
                ->selectRaw("$nameExpr, $dateExpr, $unixExpr, ? as label, 0 as is_cancel", [$label]);
        }

        foreach ($cancelSources as $table => $label) {
            if (!Schema::hasTable($table)) continue;
            $nameExpr = $this->nameExprFor($table).' as nama';
            [$dateExpr,$unixExpr] = $this->coalescedDateExpr($table, true);
            $parts[] = DB::table($table)
                ->selectRaw("$nameExpr, $dateExpr, $unixExpr, ? as label, 1 as is_cancel", [$label]);
        }

        if (empty($parts)) {
            return response()->json(['unread' => 0, 'items' => []]);
        }

        $union = array_shift($parts);
        foreach ($parts as $p) $union->unionAll($p);

        $rows = DB::query()
            ->fromSub($union, 'u')
            ->orderByDesc('created_ts')
            ->limit(80)
            ->get();

        // ==== baseline "terakhir dilihat" (DB → session) dalam WIB ====
        $seenAt = optional($r->user())->notifications_seen_at ?? session('notifications_seen_at');
        $seen   = $seenAt ? Carbon::parse($seenAt, 'Asia/Jakarta') : null;

        $items  = [];
        $unread = 0;

        foreach ($rows as $r) {
            $dt = Carbon::createFromTimestamp((int)$r->created_ts, 'Asia/Jakarta');
            $isNew = $seen ? $dt->gt($seen) : true;
            if ($isNew) $unread++;

            $nama  = trim((string)$r->nama);
            $label = trim((string)$r->label);
            $verb  = ((int)$r->is_cancel === 1) ? 'cancel di' : 'telah masuk ke';

            $items[] = [
                'message'    => 'Klien <b>'.e($nama).'</b> '.$verb.' <b>'.e($label).'</b>.',
                'time_human' => $dt->diffForHumans(),          // WIB
                'is_new'     => $isNew,
            ];
        }

        return response()->json(['unread' => $unread, 'items' => $items]);
    }

    /* ==================== Tandai dibaca ==================== */

    /** Dipanggil saat klik "Tandai dibaca" di dropdown (feed custom). */
    public function markSeen(Request $request)
    {
        $now = now('Asia/Jakarta');
        // simpan ke session (fallback) dan DB (persisten)
        session(['notifications_seen_at' => $now->toDateTimeString()]);
        if ($request->user()) {
            $request->user()->forceFill(['notifications_seen_at' => $now])->save();
        }
        return response()->json(['ok' => true]);
    }

    /** Tandai semua notifikasi Laravel (built-in) sebagai dibaca. */
    public function readAll(Request $request)
    {
        if (Schema::hasTable('notifications') && $request->user()) {
            $request->user()->unreadNotifications()->update(['read_at' => now()]);
            Cache::forget("notif_count_{$request->user()->id}");
            Cache::forget("notif_list_{$request->user()->id}");
        }

        // update baseline feed custom di session + DB (WIB)
        $now = now('Asia/Jakarta');
        session(['notifications_seen_at' => $now->toDateTimeString()]);
        if ($request->user()) {
            $request->user()->forceFill(['notifications_seen_at' => $now])->save();
        }

        return response()->json(['ok' => true]);
    }

    /** Tandai satu notifikasi Laravel (built-in) sebagai dibaca. */
    public function readOne(DatabaseNotification $notification, Request $request)
    {
        // keamanan: pastikan milik user yang login
        $user = $request->user();
        abort_unless(
            $user && $notification->notifiable_id === $user->id &&
            $notification->notifiable_type === get_class($user),
            403
        );

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        Cache::forget("notif_count_{$user->id}");
        Cache::forget("notif_list_{$user->id}");

        return response()->json(['ok' => true]);
    }
}
