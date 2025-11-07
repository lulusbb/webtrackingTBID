<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Setting;

class AdminController extends Controller
{
    public function index(Request $request) { return $this->dashboard($request); }
    public function akun() { return view('admin.akun'); }

    /* =================== KPI defaults =================== */
    protected array $kpiDefaults = [
        'klien_survei' => ['red_lt'=>10, 'yellow_min'=>10, 'yellow_max'=>20, 'green_gt'=>20],
        'klien_masuk'  => ['red_lt'=>10, 'yellow_min'=>20, 'yellow_max'=>30, 'green_gt'=>30],
    ];

    protected function getThresholds(): array
    {
        $saved = Setting::where('key','kpi_thresholds')->value('value');
        $saved = is_array($saved) ? $saved : [];
        return array_replace_recursive($this->kpiDefaults, $saved);
    }

    /* =================== Column helpers =================== */

    protected function pickDateColumn(string $table): ?string
    {
        foreach (['tanggal_masuk','tgl_masuk','tanggal','created_at','updated_at'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    protected function nameExprFor(string $table): string
    {
        $candidates = ['nama','nama_klien','klien','client_name','pelanggan','pemilik','owner','kontak'];
        $cols = [];
        foreach ($candidates as $c) if (Schema::hasColumn($table, $c)) $cols[] = $c;
        return empty($cols) ? "''" : 'COALESCE('.implode(',', $cols).", '')";
    }

    protected function kodeExprFor(string $table): string
    {
        if ($table === 'proyekjalans' && Schema::hasColumn($table, 'status_progres')) {
            return "CONCAT(COALESCE(CAST(status_progres AS CHAR), '0'), '%')";
        }
        $candidates = ['kode_proyek', 'kode', 'kode_project'];
        $cols = [];
        foreach ($candidates as $c) if (Schema::hasColumn($table, $c)) $cols[] = $c;
        return empty($cols) ? "''" : 'UPPER(COALESCE('.implode(',', $cols).", ''))";
    }

    protected function kodeColumnFor(string $table): ?string
    {
        foreach (['kode_proyek','kode','kode_project'] as $c) {
            if (Schema::hasColumn($table,$c)) return $c;
        }
        return null;
    }

    protected function kelasColumnFor(string $table): ?string
    {
        foreach (['kelas'] as $c) if (Schema::hasColumn($table,$c)) return $c;
        return null;
    }

    /* =================== Range helpers =================== */

    protected function parseRange(Request $r): array
    {
        $key   = $r->input('range', '30');
        $today = Carbon::today();

        if ($key === 'today') {
            $start = $today->copy();
            $end   = $today->copy()->endOfDay();
            $label = 'Hari ini';
        } elseif (in_array($key, ['7','14','30'], true)) {
            $days  = (int)$key;
            $start = $today->copy()->subDays($days-1);
            $end   = $today->copy()->endOfDay();
            $label = "$days Hari Terakhir";
        } elseif ($key === 'custom') {
            try {
                $start = Carbon::parse($r->input('start'))->startOfDay();
                $end   = Carbon::parse($r->input('end'))->endOfDay();
                if ($end->lt($start)) [$start,$end] = [$end,$start];
            } catch (\Throwable $e) {
                $start = $today->copy()->subDays(29);
                $end   = $today->copy()->endOfDay();
                $key   = '30';
            }
            $label = $start->format('d/m/Y').' - '.$end->format('d/m/Y');
        } elseif ($key === 'all') {
            $start = null; $end = null;
            $label = 'Semua waktu';
        } else {
            $start = $today->copy()->subDays(29);
            $end   = $today->copy()->endOfDay();
            $key   = '30';
            $label = '30 Hari Terakhir';
        }

        return [$key, $start, $end, $label];
    }

    protected function applyDateFilter(string $table, $q, ?Carbon $start, ?Carbon $end)
    {
        $dateCol = $this->pickDateColumn($table);
        if ($dateCol && $start && $end) {
            $q->whereBetween($dateCol, [$start, $end]);
        }
        return $q;
    }

    protected function findGlobalBounds(array $tables): array
    {
        $min = null; $max = null;
        foreach ($tables as $t) {
            if (!Schema::hasTable($t)) continue;
            $dateCol = $this->pickDateColumn($t);
            if (!$dateCol) continue;

            $tmin = DB::table($t)->min($dateCol);
            $tmax = DB::table($t)->max($dateCol);
            if ($tmin) $min = $min ? min($min, $tmin) : $tmin;
            if ($tmax) $max = $max ? max($max, $tmax) : $tmax;
        }

        try {
            $minC = $min ? Carbon::parse($min)->startOfDay() : null;
            $maxC = $max ? Carbon::parse($max)->endOfDay()   : null;
        } catch (\Throwable $e) {
            $minC = null; $maxC = null;
        }

        if (!$minC || !$maxC) {
            $maxC = Carbon::today()->endOfDay();
            $minC = $maxC->copy()->subMonths(11)->startOfMonth();
        }

        return [$minC, $maxC];
    }

    protected function makeBucketsForTables(?Carbon $start, ?Carbon $end, array $tables): array
    {
        if (!$start || !$end) {
            [$start, $end] = $this->findGlobalBounds($tables);
        }

        $diffDays = $start->diffInDays($end);
        $daily = $diffDays <= 31;

        $buckets = [];
        $labels  = [];
        $keys    = [];    // key untuk join hasil agregasi (YYYY-MM-DD / YYYY-MM-01)

        if ($daily) {
            $d = $start->copy()->startOfDay();
            while ($d->lte($end)) {
                $buckets[] = [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
                $labels[]  = $d->format('d M');
                $keys[]    = $d->format('Y-m-d');
                $d->addDay();
            }
        } else {
            $d = $start->copy()->startOfMonth();
            while ($d->lte($end)) {
                $buckets[] = [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()];
                $labels[]  = $d->format('M Y');
                $keys[]    = $d->format('Y-m-01');
                $d->addMonth();
            }
        }
        return [$buckets, $labels, $keys, $daily];
    }

    /* =================== Data helpers =================== */

    protected int $listLimit = 50; // tampilkan 50 item terbaru saja

    protected function fetchList(string $table, int $limit = null, ?Carbon $start=null, ?Carbon $end=null): array
    {
        if (!Schema::hasTable($table)) return [];

        $limit   = $limit ?? $this->listLimit;
        $dateCol = $this->pickDateColumn($table);
        $nameExpr = $this->nameExprFor($table).' as nama';
        $kodeExpr = $this->kodeExprFor($table).' as kode';

        $q = DB::table($table)->selectRaw("$nameExpr, $kodeExpr");
        $this->applyDateFilter($table, $q, $start, $end);
        if ($dateCol) $q->orderBy($dateCol, 'desc');

        return $q->limit($limit)->get()->map(fn($r) => [
            'nama' => $r->nama ?: '(Tanpa nama)',
            'kode' => $r->kode ?: '–',
        ])->all();
    }

    protected function countTotal(string $table, ?Carbon $start=null, ?Carbon $end=null): int
    {
        if (!Schema::hasTable($table)) return 0;
        $q = DB::table($table);
        $this->applyDateFilter($table, $q, $start, $end);
        return (int) $q->count();
    }

    protected function buildCard(string $title, string $table, ?string $cancelTable = null, ?Carbon $start=null, ?Carbon $end=null): array
    {
        $hasCancel = $cancelTable && Schema::hasTable($cancelTable);

        return [
            'title'     => $title,
            'table'     => $table,
            'total'     => $this->countTotal($table, $start, $end),
            'items'     => $this->fetchList($table, $this->listLimit, $start, $end),
            'hasCancel' => $hasCancel,
            'cancel'    => $hasCancel ? [
                'title' => 'Cancel',
                'table' => $cancelTable,
                'total' => $this->countTotal($cancelTable, $start, $end),
                'items' => $this->fetchList($cancelTable, $this->listLimit, $start, $end),
            ] : null,
        ];
    }

    protected function preferTable(array $candidates): ?string
    {
        foreach ($candidates as $t) if (Schema::hasTable($t)) return $t;
        return null;
    }

    /**
     * Agregasi grafik: 1 query per tabel (GROUP BY hari atau bulan).
     */
    protected function buildSeries(array $tables, ?Carbon $start, ?Carbon $end): array
    {
        [$buckets, $labels, $keys, $daily] = $this->makeBucketsForTables($start, $end, $tables);
        $idx = array_flip($keys);
        $series = array_fill(0, count($keys), 0);

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;
            $dateCol = $this->pickDateColumn($table);
            if (!$dateCol) continue;

            if ($daily) {
                // GROUP BY DATE()
                $rows = DB::table($table)
                    ->selectRaw("DATE($dateCol) as k, COUNT(*) as c")
                    ->whereBetween($dateCol, [$buckets[0][0], end($buckets)[1]])
                    ->groupBy(DB::raw("DATE($dateCol)"))
                    ->get();
            } else {
                // GROUP BY bulan (YYYY-MM-01)
                $rows = DB::table($table)
                    ->selectRaw("DATE_FORMAT($dateCol, '%Y-%m-01') as k, COUNT(*) as c")
                    ->whereBetween($dateCol, [$buckets[0][0], end($buckets)[1]])
                    ->groupBy(DB::raw("DATE_FORMAT($dateCol, '%Y-%m-01')"))
                    ->get();
            }

            foreach ($rows as $r) {
                $k = (string)$r->k;
                if (isset($idx[$k])) $series[$idx[$k]] += (int)$r->c;
            }
        }

        return [$labels, $series];
    }

    protected function buildKelas(array $tables, ?Carbon $start, ?Carbon $end): array
    {
        $map = ['A'=>0,'B'=>0,'C'=>0,'D'=>0];

        foreach ($tables as $t) {
            if (!Schema::hasTable($t)) continue;
            $klass = $this->kelasColumnFor($t);
            if (!$klass) continue;
            $q = DB::table($t)->select($klass.' as k', DB::raw('COUNT(*) as c'))->groupBy('k');
            $this->applyDateFilter($t, $q, $start, $end);
            foreach ($q->get() as $r) {
                $k = strtoupper((string)$r->k);
                if (isset($map[$k])) $map[$k] += (int)$r->c;
            }
        }
        $labels = ['A','B','C','D'];
        $data   = array_map(fn($k)=>$map[$k], $labels);
        $sample = array_sum($data);
        return [$labels, $data, $sample];
    }

    protected function buildKode(array $tables, ?Carbon $start, ?Carbon $end): array
    {
        $map = ['BA'=>0,'RE'=>0,'DE'=>0,'IN'=>0];

        foreach ($tables as $t) {
            if (!Schema::hasTable($t)) continue;
            $col = $this->kodeColumnFor($t);
            if (!$col) continue;
            $q = DB::table($t)->select($col.' as kd', DB::raw('COUNT(*) as c'))->groupBy('kd');
            $this->applyDateFilter($t, $q, $start, $end);
            foreach ($q->get() as $r) {
                $k = strtoupper((string)$r->kd);
                if (isset($map[$k])) $map[$k] += (int)$r->c;
            }
        }
        $labels = ['BA','RE','DE','IN'];
        $data   = array_map(fn($k)=>$map[$k], $labels);
        return [$labels, $data];
    }

    /* =================== Klien Baru optimized =================== */

    /**
     * UNION semua tabel pipeline (hanya kolom klien_id) untuk 1x NOT EXISTS.
     * Kita juga kasih filter tanggal per tabel kalau kolom tanggal ada & rentang diberikan.
     */
    protected function pipelineUnionQuery(?Carbon $start=null, ?Carbon $end=null)
    {
        $tables = [
            // tahap aktif
            'survey_requests','klienfixsurvei','denahs','exteriors','meps',
            'delegasirab','struktur_3ds','skemas','rabs','tahap_akhirs',
            'mous','proyekjalans',
            // cancels
            'klien_cancels','survei_cancels','survei_cancel','denah_cancels',
            'exterior_cancels','mep_cancels','struktur_3ds_cancels','struktur_3d_cancels',
            'skema_cancels','rab_cancels','tahapakhir_cancels','mou_cancels',
        ];

        $subs = [];
        foreach ($tables as $t) {
            if (!Schema::hasTable($t) || !Schema::hasColumn($t, 'klien_id')) continue;

            $q = DB::table($t)->select('klien_id');
            // tanggal untuk memperkecil union saat pakai filter
            $dateCol = $this->pickDateColumn($t);
            if ($dateCol && $start && $end) {
                $q->whereBetween($dateCol, [$start, $end]);
            }
            if (Schema::hasColumn($t,'deleted_at')) $q->whereNull('deleted_at');

            $subs[] = $q;
        }

        if (empty($subs)) {
            // SELECT 0 WHERE 1=0
            return DB::query()->fromRaw('(SELECT 0 AS klien_id) x WHERE 1=0');
        }

        $union = array_shift($subs);
        foreach ($subs as $s) $union->unionAll($s);

        // Bungkus lagi agar bisa dipakai ulang
        return DB::query()->fromSub($union, 'p');
    }

    protected function baseKlienBaruQuery(?Carbon $start, ?Carbon $end)
    {
        if (!Schema::hasTable('kliens')) {
            return DB::query()->fromRaw('(select 1 where 0) x');
        }

        $dateCol = $this->pickDateColumn('kliens');
        $q = DB::table('kliens')
            ->whereRaw("COALESCE(kliens.status,'') = ''");

        $this->applyDateFilter('kliens', $q, $start, $end);
        if ($dateCol) $q->orderBy($dateCol, 'desc');

        $union = $this->pipelineUnionQuery($start, $end);

        // NOT EXISTS terhadap union semua tabel pipeline
        $q->whereNotExists(function ($sub) use ($union) {
            $sub->fromSub($union, 'u')->whereColumn('u.klien_id', 'kliens.id');
        });

        return $q;
    }

    protected function fetchListKlienBaru(int $limit = null, ?Carbon $start=null, ?Carbon $end=null): array
    {
        $limit    = $limit ?? $this->listLimit;
        $nameExpr = $this->nameExprFor('kliens') . ' as nama';
        $kodeExpr = $this->kodeExprFor('kliens') . ' as kode';

        $q = $this->baseKlienBaruQuery($start, $end)->selectRaw("$nameExpr, $kodeExpr");
        return $q->limit($limit)->get()->map(fn($r) => [
            'nama' => $r->nama ?: '(Tanpa nama)',
            'kode' => $r->kode ?: '–',
        ])->all();
    }

    protected function countKlienBaru(?Carbon $start=null, ?Carbon $end=null): int
    {
        return (int) $this->baseKlienBaruQuery($start, $end)->count();
    }

    protected function buildCardKlienBaru(?Carbon $start=null, ?Carbon $end=null): array
    {
        $cancelTable = 'klien_cancels';
        $hasCancel   = Schema::hasTable($cancelTable);

        return [
            'title'     => 'Klien Masuk',
            'table'     => 'kliens',
            'total'     => $this->countKlienBaru($start, $end),
            'items'     => $this->fetchListKlienBaru($this->listLimit, $start, $end),
            'hasCancel' => $hasCancel,
            'cancel'    => $hasCancel ? [
                'title' => 'Cancel',
                'table' => $cancelTable,
                'total' => $this->countTotal($cancelTable, $start, $end),
                'items' => $this->fetchList($cancelTable, $this->listLimit, $start, $end),
            ] : null,
        ];
    }

    /* =================== Page =================== */

    public function dashboard(Request $request)
    {
        // 1) Range filter
        [$rangeKey, $startAt, $endAt, $rangeLabel] = $this->parseRange($request);

        // 2) Tabel
        $mainTables = [
            'kliens','klienfixsurvei','denahs','exteriors','meps','delegasirab',
            'tahap_akhirs','struktur_3ds','skemas','rabs','mous','proyekjalans'
        ];
        $cancelTables = array_values(array_filter([
            'klien_cancels',
            $this->preferTable(['survei_cancel','survei_cancels']),
            'denah_cancels','exterior_cancels','mep_cancels','tahapakhir_cancels',
            'struktur_3ds_cancels','skema_cancels','rab_cancels','mou_cancels'
        ]));

        // 3) Cards
        $marketing = [
            $this->buildCardKlienBaru($startAt, $endAt),
        ];
        $studio = [
            $this->buildCard('Klien Survei', 'klienfixsurvei', $this->preferTable(['survei_cancel','survei_cancels']), $startAt, $endAt),
            $this->buildCard('Denah & Moodboard', 'denahs', 'denah_cancels', $startAt, $endAt),
            $this->buildCard('3D Desain', 'exteriors', 'exterior_cancels', $startAt, $endAt),
            $this->buildCard('MEP & Spek', 'meps', 'mep_cancels', $startAt, $endAt),
            $this->buildCard('Serter Desain', 'tahap_akhirs', null, $startAt, $endAt),
        ];
        $project = [
            $this->buildCard('3D Struktur', 'struktur_3ds', $this->preferTable(['struktur_3ds_cancels','struktur_3d_cancels']), $startAt, $endAt),
            $this->buildCard('Skema Plumbing', 'skemas', 'skema_cancels', $startAt, $endAt),
            $this->buildCard('RAB', 'rabs', 'rab_cancels', $startAt, $endAt),
            $this->buildCard('MOU', 'mous', 'mou_cancels', $startAt, $endAt),
            $this->buildCard('Proyek Berjalan', 'proyekjalans', null, $startAt, $endAt),
        ];

        // 4) Grafik (1 query per tabel)
        [$chartLabels, $totalKlienSeries]  = $this->buildSeries($mainTables,   $startAt, $endAt);
        [,             $totalCancelSeries] = $this->buildSeries($cancelTables, $startAt, $endAt);
        [$kelasLabels, $kelasData, $kelasSample] = $this->buildKelas($mainTables, $startAt, $endAt);
        [$proyekLabels, $proyekData]       = $this->buildKode($mainTables, $startAt, $endAt);

        $thresholds = $this->getThresholds();

        // 5) View
        return view('admin.dashboard', [
            'groups' => [
                ['label' => 'MARKETING', 'cards' => $marketing],
                ['label' => 'STUDIO',    'cards' => $studio],
                ['label' => 'PROJECT',   'cards' => $project],
            ],
            'rangeKey'   => $rangeKey,
            'rangeLabel' => $rangeLabel,
            'rangeStart' => $startAt?->format('Y-m-d'),
            'rangeEnd'   => $endAt?->format('Y-m-d'),
            'chartLabels'       => $chartLabels,
            'totalKlienSeries'  => $totalKlienSeries,
            'totalCancelSeries' => $totalCancelSeries,
            'kelasLabels'       => $kelasLabels,
            'kelasData'         => $kelasData,
            'kelasSample'       => $kelasSample,
            'proyekLabels'      => $proyekLabels,
            'proyekData'        => $proyekData,
            'thresholds'        => $thresholds,
        ]);
    }

    public function dashboardData(Request $request)
{
    // Range sama seperti halaman utama
    [$rangeKey, $startAt, $endAt, $rangeLabel] = $this->parseRange($request);

    // Tabel utama & cancel (sama seperti dashboard)
    $mainTables = [
        'kliens','klienfixsurvei','denahs','exteriors','meps','delegasirab',
        'tahap_akhirs','struktur_3ds','skemas','rabs','mous','proyekjalans'
    ];
    $cancelTables = array_values(array_filter([
        'klien_cancels',
        $this->preferTable(['survei_cancel','survei_cancels']),
        'denah_cancels','exterior_cancels','mep_cancels','tahapakhir_cancels',
        'struktur_3ds_cancels','skema_cancels','rab_cancels','mou_cancels'
    ]));

    // Kartu (batasi list 50 biar ringan)
    $marketing = [
        $this->buildCardKlienBaru($startAt, $endAt), // sudah pakai filter "Klien Baru" yang benar
    ];
    // batasi item list ke 50
    $limit = 50;
    $studio = [
        [
            ...$this->buildCard('Klien Survei','klienfixsurvei',$this->preferTable(['survei_cancel','survei_cancels']),$startAt,$endAt),
            'items' => array_slice($this->fetchList('klienfixsurvei', 1000, $startAt, $endAt), 0, $limit),
            'cancel' => (function() use($startAt,$endAt,$limit){
                $t = $this->preferTable(['survei_cancel','survei_cancels']);
                return $t ? [
                    'title'=>'Cancel','table'=>$t,
                    'total'=>$this->countTotal($t,$startAt,$endAt),
                    'items'=>array_slice($this->fetchList($t,1000,$startAt,$endAt),0,$limit),
                ] : null;
            })(),
        ],
        [
            ...$this->buildCard('Denah & Moodboard','denahs','denah_cancels',$startAt,$endAt),
            'items'  => array_slice($this->fetchList('denahs',1000,$startAt,$endAt),0,$limit),
            'cancel' => [
                'title'=>'Cancel','table'=>'denah_cancels',
                'total'=>$this->countTotal('denah_cancels',$startAt,$endAt),
                'items'=>array_slice($this->fetchList('denah_cancels',1000,$startAt,$endAt),0,$limit),
            ],
        ],
        [
            ...$this->buildCard('3D Desain','exteriors','exterior_cancels',$startAt,$endAt),
            'items'  => array_slice($this->fetchList('exteriors',1000,$startAt,$endAt),0,$limit),
            'cancel' => [
                'title'=>'Cancel','table'=>'exterior_cancels',
                'total'=>$this->countTotal('exterior_cancels',$startAt,$endAt),
                'items'=>array_slice($this->fetchList('exterior_cancels',1000,$startAt,$endAt),0,$limit),
            ],
        ],
        [
            ...$this->buildCard('MEP & Spek','meps','mep_cancels',$startAt,$endAt),
            'items'  => array_slice($this->fetchList('meps',1000,$startAt,$endAt),0,$limit),
            'cancel' => [
                'title'=>'Cancel','table'=>'mep_cancels',
                'total'=>$this->countTotal('mep_cancels',$startAt,$endAt),
                'items'=>array_slice($this->fetchList('mep_cancels',1000,$startAt,$endAt),0,$limit),
            ],
        ],
        $this->buildCard('Serter Desain','tahap_akhirs',null,$startAt,$endAt),
    ];

    $project = [
        [
            ...$this->buildCard('3D Struktur','struktur_3ds',$this->preferTable(['struktur_3ds_cancels','struktur_3d_cancels']),$startAt,$endAt),
            'items'  => array_slice($this->fetchList('struktur_3ds',1000,$startAt,$endAt),0,$limit),
            'cancel' => (function() use($startAt,$endAt,$limit){
                $t = $this->preferTable(['struktur_3ds_cancels','struktur_3d_cancels']);
                return $t ? [
                    'title'=>'Cancel','table'=>$t,
                    'total'=>$this->countTotal($t,$startAt,$endAt),
                    'items'=>array_slice($this->fetchList($t,1000,$startAt,$endAt),0,$limit),
                ] : null;
            })(),
        ],
        [
            ...$this->buildCard('Skema Plumbing','skemas','skema_cancels',$startAt,$endAt),
            'items'  => array_slice($this->fetchList('skemas',1000,$startAt,$endAt),0,$limit),
            'cancel' => [
                'title'=>'Cancel','table'=>'skema_cancels',
                'total'=>$this->countTotal('skema_cancels',$startAt,$endAt),
                'items'=>array_slice($this->fetchList('skema_cancels',1000,$startAt,$endAt),0,$limit),
            ],
        ],
        [
            ...$this->buildCard('RAB','rabs','rab_cancels',$startAt,$endAt),
            'items'  => array_slice($this->fetchList('rabs',1000,$startAt,$endAt),0,$limit),
            'cancel' => [
                'title'=>'Cancel','table'=>'rab_cancels',
                'total'=>$this->countTotal('rab_cancels',$startAt,$endAt),
                'items'=>array_slice($this->fetchList('rab_cancels',1000,$startAt,$endAt),0,$limit),
            ],
        ],
        [
            ...$this->buildCard('MOU','mous','mou_cancels',$startAt,$endAt),
            'items'  => array_slice($this->fetchList('mous',1000,$startAt,$endAt),0,$limit),
            'cancel' => [
                'title'=>'Cancel','table'=>'mou_cancels',
                'total'=>$this->countTotal('mou_cancels',$startAt,$endAt),
                'items'=>array_slice($this->fetchList('mou_cancels',1000,$startAt,$endAt),0,$limit),
            ],
        ],
        $this->buildCard('Proyek Berjalan','proyekjalans',null,$startAt,$endAt),
    ];

    // Grafik
    [$chartLabels, $totalKlienSeries]  = $this->buildSeries($mainTables,   $startAt, $endAt);
    [,             $totalCancelSeries] = $this->buildSeries($cancelTables, $startAt, $endAt);
    [$kelasLabels, $kelasData, $kelasSample] = $this->buildKelas($mainTables, $startAt, $endAt);
    [$proyekLabels, $proyekData] = $this->buildKode($mainTables, $startAt, $endAt);

    return response()->json([
        'rangeKey'   => $rangeKey,
        'rangeLabel' => $rangeLabel,
        'rangeStart' => $startAt?->format('Y-m-d'),
        'rangeEnd'   => $endAt?->format('Y-m-d'),
        'groups' => [
            ['label'=>'MARKETING','cards'=>$marketing],
            ['label'=>'STUDIO',   'cards'=>$studio],
            ['label'=>'PROJECT',  'cards'=>$project],
        ],
        'chart' => [
            'labels'       => $chartLabels,
            'totalKlien'   => $totalKlienSeries,
            'totalCancel'  => $totalCancelSeries,
            'kelasLabels'  => $kelasLabels,
            'kelasData'    => $kelasData,
            'kelasSample'  => $kelasSample,
            'proyekLabels' => $proyekLabels,
            'proyekData'   => $proyekData,
        ],
        'sum' => [
            'totalKlien'  => array_sum($totalKlienSeries),
            'totalCancel' => array_sum($totalCancelSeries),
        ],
        'thresholds' => $this->getThresholds(),
    ]);
}
}
