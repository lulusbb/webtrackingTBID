<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;

class ProjectDashboardController extends Controller
{
    /** Tabel cancel yang dijumlahkan untuk KPI Cancel */
    protected array $cancelTables = [
        'struktur_3d_cancels',
        'skema_cancels',
        'rab_cancels',
    ];

    /** Traffic by Step (ALL-TIME; lengkap spt Studio) */
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

    /** Override kolom tanggal beberapa tabel */
    protected array $dateColumns = [
        'klienfixsurvei'   => 'tgl_masuk',
        'proyekselesaii'   => 'tanggal_selesai',
        'proyek_selesaiis' => 'tanggal_selesai',
        'proyek_selesai'   => 'tanggal_selesai',
    ];

    /** cache builder UNION per-request */
    protected ?Builder $unionBase = null;

    /* ================= helpers umum ================= */

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

    protected function dateColumnFor(string $table): ?string
    {
        return $this->dateColumns[$table] ?? null;
    }

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

    protected function countBetween(string|array $tables, ?Carbon $start, ?Carbon $end, bool $isCancel = false): int
    {
        $t = $this->firstExistingTable($tables);
        if (!$t) return 0;

        $col = $isCancel
            ? $this->pickCancelDateColumn($t)
            : ($this->dateColumnFor($t) ?? $this->pickDateColumn($t));

        $q = DB::table($t);
        if ($col && $start && $end) $q->whereBetween($col, [$start, $end]);

        return (int) $q->count();
    }

    /** UNION untuk tabel cancel → 1x COUNT (lebih cepat) */
    protected function sumCancels(?Carbon $start, ?Carbon $end): int
    {
        $parts = [];
        foreach ($this->cancelTables as $t) {
            if (!Schema::hasTable($t)) continue;
            $col = $this->pickCancelDateColumn($t) ?? 'created_at';
            $parts[] = ['table' => $t, 'col' => $col];
        }
        if (empty($parts)) return 0;

        $sub = DB::query()->fromSub(function ($q) use ($parts) {
            $first = array_shift($parts);
            $q->from($first['table'])->selectRaw("{$first['col']} as canceled_at");
            foreach ($parts as $p) {
                $q->unionAll(DB::table($p['table'])->selectRaw("{$p['col']} as canceled_at"));
            }
        }, 'c');

        if ($start && $end) $sub->whereBetween('c.canceled_at', [$start, $end]);

        return (int) $sub->count();
    }

    /* ================== UNION master (6 tabel) ================== */

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

    /** Builder UNION sekali per request, lalu di-clone ketika dipakai */
    protected function baseUnion(): Builder
    {
        if ($this->unionBase instanceof Builder) {
            return clone $this->unionBase;
        }

        $tables = ['struktur_3ds','skemas','rabs','mous','proyekjalans','proyekselesaii'];
        $parts  = [];
        foreach ($tables as $t) {
            $sel = $this->normalizedSelect($t);
            if ($sel) $parts[] = ['table'=>$t,'select'=>$sel];
        }

        $this->unionBase = DB::query()->fromSub(function ($q) use ($parts) {
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

        return clone $this->unionBase;
    }

    /* ================== Builders (chart & list) ================== */

    /** Grafik 12 bulan terakhir – 1 query GROUP BY (kencang) */
    protected function totalPerBulan(): array
    {
        $end   = now()->endOfMonth();
        $start = (clone $end)->startOfMonth()->subMonths(11);

        $rows = DB::query()
            ->fromSub($this->baseUnion(), 'm')
            ->whereBetween('m.created_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(m.created_at,'%Y-%m') ym, COUNT(*) total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total','ym')
            ->all();

        $labels = [];
        $series = [];
        $period = CarbonPeriod::create($start, '1 month', $end->copy()->startOfMonth());
        foreach ($period as $month) {
            $key = $month->format('Y-m');
            $labels[] = $month->isoFormat('MMM Y');
            $series[] = (int) ($rows[$key] ?? 0);
        }
        return [$labels, $series];
    }

    /** Bar: by kode_proyek (ikut filter) */
    protected function trafficByProyek(?Carbon $start, ?Carbon $end): array
    {
        $rows = DB::query()
            ->fromSub($this->baseUnion(), 'p')
            ->when($start && $end, fn($q)=>$q->whereBetween('p.created_at', [$start, $end]))
            ->selectRaw("UPPER(COALESCE(p.kode_proyek,'')) kp, COUNT(*) total")
            ->groupBy('kp')
            ->pluck('total','kp')
            ->all();

        $keys = ['BA','RE','DE','IN'];
        $out  = [];
        foreach ($keys as $k) $out[$k] = (int)($rows[$k] ?? 0);
        return $out;
    }

    /** Donut: by kelas (ikut filter) → persen + total */
    protected function trafficByKelas(?Carbon $start, ?Carbon $end): array
    {
        $rows = DB::query()
            ->fromSub($this->baseUnion(), 'k')
            ->when($start && $end, fn($q)=>$q->whereBetween('k.created_at', [$start, $end]))
            ->selectRaw("UPPER(COALESCE(k.kelas,'')) kl, COUNT(*) total")
            ->groupBy('kl')
            ->pluck('total','kl')
            ->all();

        $keys = ['A','B','C','D'];
        $counts = []; $tot = 0;
        foreach ($keys as $k) { $counts[$k] = (int)($rows[$k] ?? 0); $tot += $counts[$k]; }

        $pct = array_fill_keys($keys, 0.0);
        if ($tot > 0) {
            $acc = 0.0;
            for ($i=0; $i<count($keys); $i++) {
                $k = $keys[$i];
                if ($i < count($keys)-1) {
                    $p = round(($counts[$k]*100)/$tot, 1);
                    $pct[$k] = $p; $acc += $p;
                } else {
                    $pct[$k] = round(100 - $acc, 1);
                }
            }
        }
        return [(object)$pct, $tot];
    }

    /** Panel kanan: Traffic by Step (ALL-TIME) – cache 5 menit */
    protected function trafficByStepAllTime(): array
    {
        return Cache::remember('project_step_alltime', 300, function () {
            $out = [];
            foreach ($this->stepTables as $label => $tblOrList) {
                $out[] = ['label' => $label, 'total' => $this->countAllTimeFlex($tblOrList)];
            }
            return $out;
        });
    }

    /* ================== Page & JSON ================== */

    public function index(Request $r)
    {
        [$start,$end] = $this->parseRange(
            $r->get('range','30d'),
            $r->get('start'),
            $r->get('end')
        );

        // KPI (mengikuti filter)
        $kpi = [
            'sipil'   => $this->countBetween('struktur_3ds', $start,$end)
                       + $this->countBetween('skemas', $start,$end)
                       + $this->countBetween('rabs',   $start,$end),
            'cancel'  => $this->sumCancels($start,$end),
            'mou'     => $this->countBetween('mous', $start,$end),
            'jalan'   => $this->countBetween('proyekjalans', $start,$end),
            'selesai' => $this->countBetween(['proyekselesaii','proyek_selesaiis','proyek_selesai'], $start,$end),
        ];

        // Grafik & Step (ALL-TIME)
        [$months,$line]   = $this->totalPerBulan();        // 1 query
        $steps            = $this->trafficByStepAllTime(); // cache 5 menit

        // Bar & Donut (ikut filter)
        $byProyek         = $this->trafficByProyek($start,$end);
        [$kelasPct,$ttlKls] = $this->trafficByKelas($start,$end);

        return view('project.dashboard', compact(
            'start','end','kpi','months','line','byProyek','kelasPct','ttlKls','steps'
        ));
    }

    public function stats(Request $r)
    {
        [$start,$end] = $this->parseRange(
            $r->get('range','30d'),
            $r->get('start'),
            $r->get('end')
        );

        $kpi = [
            'sipil'   => $this->countBetween('struktur_3ds', $start,$end)
                       + $this->countBetween('skemas', $start,$end)
                       + $this->countBetween('rabs',   $start,$end),
            'cancel'  => $this->sumCancels($start,$end),
            'mou'     => $this->countBetween('mous', $start,$end),
            'jalan'   => $this->countBetween('proyekjalans', $start,$end),
            'selesai' => $this->countBetween(['proyekselesaii','proyek_selesaiis','proyek_selesai'], $start,$end),
        ];

        [$months,$line]     = $this->totalPerBulan();
        $steps              = $this->trafficByStepAllTime();
        $byProyek           = $this->trafficByProyek($start,$end);
        [$kelasPct,$ttlKls] = $this->trafficByKelas($start,$end);

        return response()->json(compact(
            'start','end','kpi','months','line','byProyek','kelasPct','ttlKls','steps'
        ));
    }
}
