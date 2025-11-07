<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StudioDashboardController extends Controller
{
    /* ===== tabel cancel untuk KPI Cancel (digabung) ===== */
    protected array $cancelTables = [
        'survei_cancels','survei_cancel',
        'denah_cancels','denah_cancel',
        'exterior_cancels','exterior_cancel',
        'mep_cancels','mep_cancel',
    ];

    /* ===== traffic by step (ALL-TIME) ===== */
    protected array $stepTables = [
        'Survei'                => 'klienfixsurvei',
        'Denah & Moodboard'     => 'denahs',
        '3D Desain'             => 'exteriors',
        'MEP & Spek Material'   => 'meps',
        '3D Struktur'           => 'struktur_3ds',
        'Skema'                 => 'skemas',
        'RAB'                   => 'rabs',
        'Serah Terima Desain'   => 'tahap_akhirs',
        'Mou'                   => 'mous',
        'Proyek Berjalan'       => 'proyekjalans',
        'Proyek Selesai'        => ['proyekselesaii','proyek_selesaiis','proyek_selesai'],
    ];

    /* override kolom tanggal spesifik */
    protected array $dateColumns = [
        'klienfixsurvei'   => 'tgl_masuk',
        'proyekselesaii'   => 'tanggal_selesai',
        'proyek_selesaiis' => 'tanggal_selesai',
        'proyek_selesai'   => 'tanggal_selesai',
    ];

    /* ================= Range helpers ================= */

    protected function parseRange(?string $preset, ?string $start, ?string $end): array
    {
        $today = now()->endOfDay();
        return match ($preset) {
            'today','hari-ini' => [now()->startOfDay(), $today],
            '7d'   => [now()->copy()->startOfDay()->subDays(6),  $today],
            '14d'  => [now()->copy()->startOfDay()->subDays(13), $today],
            '30d'  => [now()->copy()->startOfDay()->subDays(29), $today],
            'custom' => [
                $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth(),
                $end   ? Carbon::parse($end)->endOfDay()     : $today,
            ],
            'all'  => [null, null],
            default=> [now()->copy()->startOfDay()->subDays(29), $today],
        };
    }

    protected function dateColumnFor(string $table): ?string { return $this->dateColumns[$table] ?? null; }

    protected function pickDateColumn(string $table): ?string
    {
        foreach (['tanggal_masuk','tgl_masuk','tanggal','created_at'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    protected function pickCancelDateColumn(string $table): ?string
    {
        foreach (['canceled_at','tanggal_cancel','tanggal_masuk','created_at','tanggal'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    protected function firstExistingTable(string|array $tables): ?string
    {
        $c = is_array($tables) ? $tables : [$tables];
        foreach ($c as $t) if (Schema::hasTable($t)) return $t;
        return null;
    }

    protected function countAllTimeFlex(string|array $tables): int
    {
        $t = $this->firstExistingTable($tables);
        return $t ? (int) DB::table($t)->count() : 0;
        }

    protected function quickCountFlex(string|array $tables, ?Carbon $start, ?Carbon $end): int
    {
        $t = $this->firstExistingTable($tables);
        if (!$t) return 0;

        $col = $this->dateColumnFor($t);
        if ($col && !Schema::hasColumn($t, $col)) $col = null;
        if (!$col) $col = $this->pickDateColumn($t);

        $q = DB::table($t);
        if ($col && $start && $end) $q->whereBetween($col, [$start, $end]);
        return (int) $q->count();
    }

    protected function countBetween(string $table, ?Carbon $start, ?Carbon $end, bool $isCancel = false): int
    {
        if (!Schema::hasTable($table)) return 0;
        $col = $isCancel ? $this->pickCancelDateColumn($table) : $this->pickDateColumn($table);
        $q = DB::table($table);
        if ($col && $start && $end) $q->whereBetween($col, [$start, $end]);
        return (int) $q->count();
    }

    /**
     * CEPAT: jumlahkan semua cancel pakai UNION ALL lalu sekali COUNT,
     * bukan hitung satu per satu tabel.
     */
protected function sumCancels(?Carbon $start, ?Carbon $end): int
{
    $parts = [];

    foreach ($this->cancelTables as $t) {
        if (!Schema::hasTable($t)) continue;

        $col = $this->pickCancelDateColumn($t) ?? 'created_at';
        // normalisasi nama kolom ke "canceled_at"
        $parts[] = "SELECT $col AS canceled_at FROM $t";
    }

    if (!$parts) return 0;

    $sql = implode(' UNION ALL ', $parts);

    // ❌ sebelumnya: DB::query()->fromSub(DB::raw("($sql) AS c"), 'c')
    // ✅ gunakan table(DB::raw()) agar menerima string subquery
    $qb = DB::table(DB::raw("($sql) as c"));

    if ($start && $end) {
        $qb->whereBetween('c.canceled_at', [$start, $end]);
    }

    return (int) $qb->count();
}

    /* ================= UNION untuk 4 tabel utama ================= */

    /** COALESCE tanggal terbaik → created_at (untuk agregasi bulanan & filter) */
    protected function coalescedDateExpr(string $table): string
    {
        $cand = ['tanggal_masuk','tgl_masuk','tanggal','created_at','updated_at'];
        $cols = [];
        foreach ($cand as $c) if (Schema::hasColumn($table, $c)) $cols[] = $c;
        return empty($cols) ? 'NULL' : 'COALESCE('.implode(',', $cols).')';
    }

    protected function normalizedSelect(string $table): ?string
    {
        if (!Schema::hasTable($table)) return null;

        $dateExpr = $this->coalescedDateExpr($table).' as created_at';

        $kode  = Schema::hasColumn($table,'kode_proyek') ? 'kode_proyek'
               : (Schema::hasColumn($table,'kode') ? 'kode' : 'NULL');
        $kelas = Schema::hasColumn($table,'kelas') ? 'kelas'
               : (Schema::hasColumn($table,'kelas_proyek') ? 'kelas_proyek' : 'NULL');

        return "$dateExpr, $kode as kode_proyek, $kelas as kelas";
    }

    /** Subquery union 4 tabel utama */
    protected function unionBase()
    {
        $parts = [];
        foreach (['klienfixsurvei','denahs','exteriors','meps'] as $t) {
            $sel = $this->normalizedSelect($t);
            if ($sel) $parts[] = ['table'=>$t,'select'=>$sel];
        }

        return DB::query()->fromSub(function ($q) use ($parts) {
            if (empty($parts)) {
                $q->fromRaw('(select 1) dummy')
                  ->selectRaw('NULL as created_at, NULL as kode_proyek, NULL as kelas')
                  ->whereRaw('1=0');
                return;
            }
            $first = array_shift($parts);
            $q->from($first['table'])->selectRaw($first['select']);
            foreach ($parts as $p) {
                $q->unionAll(DB::table($p['table'])->selectRaw($p['select']));
            }
        }, 'u');
    }

    /* ================= Builders ================= */

    /**
     * Grafik bulanan: 12 bulan terakhir dari SEKARANG (tidak ikut filter) dengan 1 query.
     * Missing months diisi 0.
     */
    protected function totalPerBulan(): array
    {
        $end   = now()->endOfMonth();
        $start = (clone $end)->startOfMonth()->subMonths(11);

        // 1 query, group by year-month
        $rows = DB::query()
            ->fromSub($this->unionBase(), 'u')
            ->selectRaw("DATE_FORMAT(u.created_at, '%Y-%m') ym, COUNT(*) total")
            ->whereBetween('u.created_at', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym')
            ->all();

        // Fill ke 12 bulan
        $labels = [];
        $series = [];
        $cur = $start->copy();
        while ($cur <= $end) {
            $ym = $cur->format('Y-m');
            $labels[] = $cur->isoFormat('MMM Y');
            $series[] = (int)($rows[$ym] ?? 0);
            $cur->addMonth();
        }

        return [$labels, $series];
    }

    /** BAR: Traffic by Proyek — union(klienfixsurvei,denahs,exteriors,meps) (ikut filter) */
    protected function trafficByProyek(?Carbon $start, ?Carbon $end): array
    {
        $q = $this->unionBase();
        if ($start && $end) $q->whereBetween('u.created_at', [$start, $end]);

        $rows = DB::query()
            ->fromSub($q, 'p')
            ->selectRaw("UPPER(COALESCE(p.kode_proyek,'')) kp, COUNT(*) total")
            ->groupBy('kp')
            ->pluck('total','kp')
            ->all();

        $keys = ['BA','RE','DE','IN'];
        $out = [];
        foreach ($keys as $k) $out[$k] = (int) ($rows[$k] ?? 0);
        return $out;
    }

    /** DONUT: Traffic by Kelas — union(klienfixsurvei,denahs,exteriors,meps) (ikut filter) */
    protected function trafficByKelas(?Carbon $start, ?Carbon $end): array
    {
        $q = $this->unionBase();
        if ($start && $end) $q->whereBetween('u.created_at', [$start, $end]);

        $rows = DB::query()
            ->fromSub($q, 'k')
            ->selectRaw("UPPER(COALESCE(k.kelas,'')) kl, COUNT(*) total")
            ->groupBy('kl')
            ->pluck('total','kl')
            ->all();

        $keys = ['A','B','C','D'];
        $counts = []; $total = 0;
        foreach ($keys as $k) { $counts[$k] = (int)($rows[$k] ?? 0); $total += $counts[$k]; }

        $pct = array_fill_keys($keys, 0.0);
        if ($total > 0) {
            $acc = 0.0;
            for ($i=0; $i<count($keys); $i++) {
                $k = $keys[$i];
                if ($i < count($keys)-1) {
                    $p = round(($counts[$k] * 100.0) / $total, 1);
                    $pct[$k] = $p; $acc += $p;
                } else {
                    $pct[$k] = round(100.0 - $acc, 1);
                }
            }
        }
        return [(object)$pct, $total];
    }

    /** Traffic by Step (ALL-TIME) */
    protected function trafficByStepAllTime(): array
    {
        $out = [];
        foreach ($this->stepTables as $label => $tblOrList) {
            $out[] = ['label' => $label, 'total' => $this->countAllTimeFlex($tblOrList)];
        }
        return $out;
    }

    /* ================= Page + JSON ================= */

    public function index(Request $r)
    {
        [$start, $end] = $this->parseRange(
            $r->get('range','30d'),
            $r->get('start'),
            $r->get('end')
        );

        // KPI (ikut filter)
        $kpi = [
            'survei' => $this->countBetween('klienfixsurvei', $start, $end),
            'denah'  => $this->countBetween('denahs',        $start, $end),
            'extint' => $this->countBetween('exteriors',     $start, $end),
            'mep'    => $this->countBetween('meps',          $start, $end),
            'cancel' => $this->sumCancels($start, $end),
        ];

        // Grafik & Step: TIDAK ikut filter
        [$months, $line]   = $this->totalPerBulan();
        $steps             = $this->trafficByStepAllTime();

        // Bar & Donut: ikut filter
        $byProyek            = $this->trafficByProyek($start, $end);
        [$kelasPct, $ttlKls] = $this->trafficByKelas($start, $end);

        return view('studio.dashboard', compact(
            'start','end','kpi','months','line','byProyek','kelasPct','ttlKls','steps'
        ));
    }

    public function stats(Request $r)
    {
        [$start, $end] = $this->parseRange(
            $r->get('range','30d'),
            $r->get('start'),
            $r->get('end')
        );

        $kpi = [
            'survei' => $this->countBetween('klienfixsurvei', $start, $end),
            'denah'  => $this->countBetween('denahs',        $start, $end),
            'extint' => $this->countBetween('exteriors',     $start, $end),
            'mep'    => $this->countBetween('meps',          $start, $end),
            'cancel' => $this->sumCancels($start, $end),
        ];

        // grafik & step all-time
        [$months, $line]   = $this->totalPerBulan();
        $steps             = $this->trafficByStepAllTime();

        // bar & donut ikut filter
        $byProyek            = $this->trafficByProyek($start, $end);
        [$kelasPct, $ttlKls] = $this->trafficByKelas($start, $end);

        return response()->json(compact(
            'start','end','kpi','months','line','byProyek','kelasPct','ttlKls','steps'
        ));
    }
}
