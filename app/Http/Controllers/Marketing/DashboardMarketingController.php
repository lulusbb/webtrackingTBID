<?php
// app/Http/Controllers/Marketing/DashboardMarketingController.php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DashboardMarketingController extends Controller
{
    /**
     * Tabel-tabel cancel yang dijumlahkan untuk KPI "Klien Cancel".
     */
    protected array $cancelTables = [
        'exterior_cancels',
        'mep_cancels',
        'struktur_3d_cancels',
        'skema_cancels',
        'rab_cancels',
        'tahapakhir_cancels',
        'mou_cancels',
        'denah_cancels',    // kalau tidak ada, otomatis 0
        'survei_cancels',   // kalau tidak ada, otomatis 0
    ];

    /**
     * Mapping “Traffic by Step” → nama tabel (boleh string atau array kandidat).
     * Untuk "Proyek Selesai" saya buat fleksibel ke beberapa kemungkinan nama tabel.
     */
    protected array $stepTables = [
        'Survei'              => 'klienfixsurvei',
        'Denah & Moodboard'   => 'denahs',
        '3D Ext & Interior'   => 'exteriors',
        'MEP & Spek Material' => 'meps',
        '3D Struktur'         => 'struktur_3ds',
        'Skema'               => 'skemas',
        'RAB'                 => 'rabs',
        'Serah Terima Desain'         => 'tahap_akhirs',
        'Mou'                 => 'mous',
        'Proyek Berjalan'     => 'proyekjalans',
        'Proyek Selesai'      => 'proyekselesaii',   // ← ini yang benar
    ];

    /**
     * Override kolom tanggal untuk tabel tertentu (kalau bukan created_at).
     * Jika tidak ada kolom ini di DB, otomatis fallback ke created_at/tanggal.
     */
    protected array $dateColumns = [
        'klienfixsurvei'   => 'tgl_masuk',
        // bila di tabel proyek selesai kamu punya kolom 'tanggal_selesai',
        // baris di bawah akan dipakai oleh quickCountFlex():
        'proyekselesaiis'  => 'tanggal_selesai',
        'proyek_selesaiis' => 'tanggal_selesai',
    ];

    /* -----------------------------------------------------------------------
     * Helpers
     * ---------------------------------------------------------------------*/

    /** Ambil kolom tanggal override (jika ada) untuk sebuah tabel */
    protected function dateColumnFor(string $table): ?string
    {
        return $this->dateColumns[$table] ?? null;
    }

    /** Parse preset range → [start, end] (Carbon) */
    protected function parseRange(?string $preset, ?string $start, ?string $end): array
    {
        $today = now()->endOfDay();

        switch ($preset) {
            case 'today':
            case 'hari-ini':
                return [now()->startOfDay(), now()->endOfDay()];
            case '7d':
                return [now()->copy()->startOfDay()->subDays(6), $today];
            case '14d':
                return [now()->copy()->startOfDay()->subDays(13), $today];
            case '30d':
                return [now()->copy()->startOfDay()->subDays(29), $today];
            case 'all':
            // gunakan tanggal sangat awal supaya menghitung semua data
            return [\Carbon\Carbon::create(1970, 1, 1, 0, 0, 0), $today];
            case 'custom':
                $s = $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth();
                $e = $end   ? Carbon::parse($end)->endOfDay()   : now()->endOfDay();
                return [$s, $e];
            default:
                // default 30 hari terakhir
                return [now()->copy()->startOfDay()->subDays(29), $today];
        }
    }

    /**
     * Hitung jumlah baris sebuah tabel dalam rentang tanggal.
     * - Jika $dateColumn diberikan dan ada → pakai itu.
     * - Jika tidak, coba 'created_at' → 'tanggal' → bila keduanya tidak ada, hitung total tabel.
     */
    protected function quickCount(string $table, Carbon $start, Carbon $end, ?string $dateColumn = null): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        $col = $dateColumn ?? $this->dateColumnFor($table);

        // validasi kolom tanggal
        if ($col && !Schema::hasColumn($table, $col)) {
            $col = null;
        }
        if (!$col) {
            $col = Schema::hasColumn($table, 'created_at')
                ? 'created_at'
                : (Schema::hasColumn($table, 'tanggal') ? 'tanggal' : null);
        }
        if (!$col) {
            return (int) DB::table($table)->count();
        }

        return (int) DB::table($table)
            ->whereBetween($col, [$start, $end])
            ->count();
    }

    /** Jumlah klien berdasarkan COALESCE(tanggal_masuk, created_at) */
    protected function countKliensInRange(Carbon $start, Carbon $end): int
    {
        return (int) DB::table('kliens')
            ->whereBetween(DB::raw('COALESCE(tanggal_masuk, created_at)'), [$start, $end])
            ->count();
    }

    /** Total proyek berjalan (all-time, tanpa filter tanggal) */
    protected function totalProyekJalan(): int
    {
        return Schema::hasTable('proyekjalans')
            ? (int) DB::table('proyekjalans')->count()
            : 0;
    }

    /** Data bulanan (12 bulan terakhir) untuk line chart */
    protected function klienPerBulan(Carbon $end): array
    {
        $start  = (clone $end)->startOfMonth()->subMonths(11);
        $period = CarbonPeriod::create($start, '1 month', (clone $end)->startOfMonth());

        $labels = [];
        $series = [];

        foreach ($period as $month) {
            $mStart = $month->copy()->startOfMonth();
            $mEnd   = $month->copy()->endOfMonth();

            $cnt = (int) DB::table('kliens')
                ->whereBetween(DB::raw('COALESCE(tanggal_masuk, created_at)'), [$mStart, $mEnd])
                ->count();

            $labels[] = $month->isoFormat('MMM'); // Jan, Feb, ...
            $series[] = $cnt;
        }

        return [$labels, $series];
    }

    /** Bar chart: jumlah klien per kode_proyek BA/RE/DE/IN */
    protected function trafficByProyek(Carbon $start, Carbon $end): array
    {
        $base = DB::table('kliens')
            ->selectRaw("UPPER(COALESCE(kode_proyek,'')) AS kode, COUNT(*) AS total")
            ->whereBetween(DB::raw('COALESCE(tanggal_masuk, created_at)'), [$start, $end])
            ->groupBy('kode')
            ->pluck('total', 'kode')
            ->all();

        $keys   = ['BA', 'RE', 'DE', 'IN'];
        $result = [];
        foreach ($keys as $k) {
            $result[$k] = (int) ($base[$k] ?? 0);
        }
        return $result;
    }

    /** Donut kelas A–D (%; total selalu 100) */
    protected function trafficByKelas(Carbon $start, Carbon $end): array
    {
        $rows = DB::table('kliens')
            ->selectRaw("UPPER(COALESCE(kelas,'')) AS kelas, COUNT(*) AS total")
            ->whereBetween(DB::raw('COALESCE(tanggal_masuk, created_at)'), [$start, $end])
            ->groupBy('kelas')
            ->pluck('total', 'kelas')
            ->all();

        $keys = ['A','B','C','D'];

        $counts = [];
        $tot = 0;
        foreach ($keys as $k) {
            $counts[$k] = (int)($rows[$k] ?? 0);
            $tot += $counts[$k];
        }

        $pct = array_fill_keys($keys, 0.0);
        if ($tot > 0) {
            $acc = 0.0;
            for ($i = 0; $i < count($keys); $i++) {
                $k = $keys[$i];
                if ($i < count($keys) - 1) {
                    $p = round(($counts[$k] * 100.0) / $tot, 1);
                    $pct[$k] = $p;
                    $acc += $p;
                } else {
                    $pct[$k] = round(100.0 - $acc, 1);
                }
            }
        }

        return [$pct, $tot];
    }

    /**
     * Pilih tabel pertama yang benar-benar ada dari sebuah list kandidat.
     */
    protected function firstExistingTable(string|array $tables): ?string
    {
        $candidates = is_array($tables) ? $tables : [$tables];
        foreach ($candidates as $t) {
            if (Schema::hasTable($t)) {
                return $t;
            }
        }
        return null;
    }

    /**
     * Hitung total all-time untuk string/array kandidat tabel.
     */
    protected function countAllTimeFlex(string|array $tables): int
    {
        $t = $this->firstExistingTable($tables);
        if (!$t) return 0;
        return (int) DB::table($t)->count();
    }

    /**
     * Hitung by range untuk string/array kandidat tabel.
     * Memakai override kolom tanggal jika ada, fallback ke created_at/tanggal.
     */
    protected function quickCountFlex(string|array $tables, Carbon $start, Carbon $end): int
    {
        $t = $this->firstExistingTable($tables);
        if (!$t) return 0;

        $col = $this->dateColumnFor($t);
        if ($col && !Schema::hasColumn($t, $col)) {
            $col = null;
        }
        if (!$col) {
            $col = Schema::hasColumn($t,'created_at') ? 'created_at'
                : (Schema::hasColumn($t,'tanggal') ? 'tanggal' : null);
        }
        if (!$col) {
            return (int) DB::table($t)->count();
        }

        return (int) DB::table($t)
            ->whereBetween($col, [$start, $end])
            ->count();
    }

    /**
     * List kanan pada dashboard: total per step (all-time).
     */
    protected function trafficByStepAllTime(): array
    {
        $out = [];
        foreach ($this->stepTables as $label => $tblOrList) {
            $out[] = [
                'label' => $label,
                'total' => $this->countAllTimeFlex($tblOrList),
            ];
        }
        return $out;
    }

    /**
     * Versi by-range (jika suatu saat ingin dipakai).
     * "Proyek Berjalan" & "Proyek Selesai" tetap all-time agar konsisten.
     */
    protected function trafficByStep(Carbon $start, Carbon $end): array
    {
        $out = [];
        foreach ($this->stepTables as $label => $tblOrList) {
            if (in_array($label, ['Proyek Berjalan', 'Proyek Selesai'], true)) {
                $total = $this->countAllTimeFlex($tblOrList);
            } else {
                $total = $this->quickCountFlex($tblOrList, $start, $end);
            }
            $out[] = ['label' => $label, 'total' => $total];
        }
        return $out;
    }

    /** Total semua cancel dari daftar $cancelTables */
    protected function countAllCancels(Carbon $start, Carbon $end): int
    {
        $sum = 0;
        foreach ($this->cancelTables as $t) {
            $sum += $this->quickCount($t, $start, $end);
        }
        return $sum;
    }

    /* -----------------------------------------------------------------------
     * Page & JSON
     * ---------------------------------------------------------------------*/

    public function index(Request $request)
    {
        // range masih dipakai untuk KPI lain & chart
        [$start, $end] = $this->parseRange(
            $request->get('range', '30d'),
            $request->get('start'),
            $request->get('end')
        );

        // KPI (Proyek Berjalan ditampilkan all-time)
        $kpi = [
            'kliens'           => $this->countKliensInRange($start, $end),
            'cancel'           => $this->countAllCancels($start, $end),
            'survei'           => $this->quickCount('klienfixsurvei', $start, $end),
            'proyek_berjalan'  => $this->totalProyekJalan(),
        ];

        // Chart & cards
        [$months, $line]     = $this->klienPerBulan($end);
        $byProyek            = $this->trafficByProyek($start, $end);
        [$kelasPct, $ttlKls] = $this->trafficByKelas($start, $end);
        $steps               = $this->trafficByStepAllTime(); // ← includes Proyek Selesai (all-time)

        return view('marketing.dashboard', compact(
            'start','end','kpi','months','line','byProyek','kelasPct','ttlKls','steps'
        ));
    }

    public function stats(Request $request)
    {
        [$start, $end] = $this->parseRange(
            $request->get('range', '30d'),
            $request->get('start'),
            $request->get('end')
        );

        $kpi = [
            'kliens'           => $this->countKliensInRange($start, $end),
            'cancel'           => $this->countAllCancels($start, $end),
            'survei'           => $this->quickCount('klienfixsurvei', $start, $end),
            'proyek_berjalan'  => $this->totalProyekJalan(),
        ];

        [$months, $line]     = $this->klienPerBulan($end);
        $byProyek            = $this->trafficByProyek($start, $end);
        [$kelasPct, $ttlKls] = $this->trafficByKelas($start, $end);
        $steps               = $this->trafficByStepAllTime(); // ← includes Proyek Selesai (all-time)

        return response()->json(compact(
            'start','end','kpi','months','line','byProyek','kelasPct','ttlKls','steps'
        ));
    }
    
}
