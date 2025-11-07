<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Klien;
use App\Models\KlienCancel;
use App\Models\SurveyRequest;

use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

// Export Excel
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KliensExport;
use App\Exports\KliensCancelExport;

class MarketingController extends Controller
{


    // alias lama
    public function adminDashboard()
    {
        return redirect()->route('marketing.dashboard');
    }

    // ====== KLIEN AKTIF (VIEW) ======
    public function klien()
    {
        return view('marketing.klien');
    }

    // ====== KLIEN (DataTables JSON) ======
    // ====== KLIEN (DataTables JSON) ======
public function klienData(Request $request)
{
    abort_unless($request->ajax(), 404);

    // ===== Base query + kolom bantu untuk sort tanggal
    $q = Klien::query()
        ->addSelect([
            'kliens.*',
            DB::raw('COALESCE(tanggal_masuk, created_at) AS tanggal_masuk_sort'),
        ])

        // ===== Indikator per-tahap (aktif & cancel)
        // 3D Ext & Int
        ->selectRaw('(SELECT COUNT(1) FROM exteriors e            WHERE e.klien_id  = kliens.id) AS has_exterior')
        ->selectRaw('(SELECT COUNT(1) FROM exterior_cancels ec    WHERE ec.klien_id = kliens.id) AS has_exterior_cancel')
        // MEP
        ->selectRaw('(SELECT COUNT(1) FROM meps m                 WHERE m.klien_id  = kliens.id) AS has_mep')
        ->selectRaw('(SELECT COUNT(1) FROM mep_cancels mc         WHERE mc.klien_id = kliens.id) AS has_mep_cancel')
        // Delegasi RAB (baru)
        ->selectRaw('(SELECT COUNT(1) FROM delegasirab dr         WHERE dr.klien_id = kliens.id) AS has_delegasirab')
        // Struktur 3D
        ->selectRaw('(SELECT COUNT(1) FROM struktur_3ds s         WHERE s.klien_id  = kliens.id) AS has_struktur3d')
        ->selectRaw('(SELECT COUNT(1) FROM struktur_3d_cancels sc WHERE sc.klien_id = kliens.id) AS has_struktur3d_cancel')
        // Skema
        ->selectRaw('(SELECT COUNT(1) FROM skemas sk              WHERE sk.klien_id = kliens.id) AS has_skema')
        ->selectRaw('(SELECT COUNT(1) FROM skema_cancels skc      WHERE skc.klien_id= kliens.id) AS has_skema_cancel')
        // RAB
        ->selectRaw('(SELECT COUNT(1) FROM rabs r                 WHERE r.klien_id  = kliens.id) AS has_rab')
        ->selectRaw('(SELECT COUNT(1) FROM rab_cancels rc         WHERE rc.klien_id = kliens.id) AS has_rab_cancel');

    // Denah Cancel (opsional)
    if (Schema::hasTable('denah_cancels')) {
        $q->selectRaw('(SELECT COUNT(1) FROM denah_cancels dc WHERE dc.klien_id = kliens.id) AS has_denah_cancel');
    } else {
        $q->selectRaw('0 AS has_denah_cancel');
    }

    // Serter Desain (dulunya Tahap Akhir) + cancel (opsional)
    if (Schema::hasTable('tahap_akhirs')) {
        $q->selectRaw('(SELECT COUNT(1) FROM tahap_akhirs ta WHERE ta.klien_id = kliens.id) AS has_tahapakhir');
    } else {
        $q->selectRaw('0 AS has_tahapakhir');
    }
    if (Schema::hasTable('tahapakhir_cancels')) {
        $q->selectRaw('(SELECT COUNT(1) FROM tahapakhir_cancels tac WHERE tac.klien_id = kliens.id) AS has_tahapakhir_cancel');
    } else {
        $q->selectRaw('0 AS has_tahapakhir_cancel');
    }

    // MOU (aktif selalu ada), cancel opsional
    $q->selectRaw('(SELECT COUNT(1) FROM mous mo WHERE mo.klien_id = kliens.id) AS has_mou');
    if (Schema::hasTable('mou_cancels')) {
        $q->selectRaw('(SELECT COUNT(1) FROM mou_cancels moc WHERE moc.klien_id = kliens.id) AS has_mou_cancel');
    } else {
        $q->selectRaw('0 AS has_mou_cancel');
    }

    // Proyek Berjalan (opsional)
    if (Schema::hasTable('proyekjalans')) {
        $q->selectRaw('(SELECT COUNT(1) FROM proyekjalans pj WHERE pj.klien_id = kliens.id) AS has_proyekjalan');
    } else {
        $q->selectRaw('0 AS has_proyekjalan');
    }

    // Proyek Selesai: dukung beberapa nama tabel
    $tblSelesai = null;
    foreach (['proyekselesaiis', 'proyekselesaii', 'proyekselesais', 'proyekselesai', 'proyekselesaiiii'] as $cand) {
        if (Schema::hasTable($cand)) { $tblSelesai = $cand; break; }
    }
    if ($tblSelesai) {
        $q->selectRaw("(SELECT COUNT(1) FROM {$tblSelesai} ps WHERE ps.klien_id = kliens.id) AS has_proyekselesai");
    } else {
        $q->selectRaw('0 AS has_proyekselesai');
    }

    // ===== STATUS EFEKTIF
    $case = [];

    // Cancel global paling atas
    if (Schema::hasTable('mou_cancels')) {
        $case[] = "WHEN (SELECT COUNT(1) FROM mou_cancels moc WHERE moc.klien_id = kliens.id) > 0 THEN 'cancel_mou'";
    }
    if (Schema::hasTable('tahapakhir_cancels')) {
        // ganti key cancel_tahap_akhir -> cancel_serter_desain
        $case[] = "WHEN (SELECT COUNT(1) FROM tahapakhir_cancels tac WHERE tac.klien_id = kliens.id) > 0 THEN 'cancel_serter_desain'";
    }

    // Aktif terdalam
    if ($tblSelesai) {
        $case[] = "WHEN (SELECT COUNT(1) FROM {$tblSelesai} ps WHERE ps.klien_id = kliens.id) > 0 THEN 'proyek_selesai'";
    }
    if (Schema::hasTable('proyekjalans')) {
        $case[] = "WHEN (SELECT COUNT(1) FROM proyekjalans pj WHERE pj.klien_id = kliens.id) > 0 THEN 'in_proyekjalan'";
    }
    $case[] = "WHEN (SELECT COUNT(1) FROM mous mo WHERE mo.klien_id = kliens.id) > 0 THEN 'in_mou'";
    if (Schema::hasTable('tahap_akhirs')) {
        // ganti key in_tahap_akhir -> in_serter_desain
        $case[] = "WHEN (SELECT COUNT(1) FROM tahap_akhirs ta WHERE ta.klien_id = kliens.id) > 0 THEN 'in_serter_desain'";
    }

    // Tahap menengah + cancel
    $case = array_merge($case, [
        "WHEN (SELECT COUNT(1) FROM rab_cancels rc         WHERE rc.klien_id = kliens.id) > 0 THEN 'cancel_rab'",
        "WHEN (SELECT COUNT(1) FROM rabs r                 WHERE r.klien_id  = kliens.id) > 0 THEN 'in_rab'",

        "WHEN (SELECT COUNT(1) FROM skema_cancels skc      WHERE skc.klien_id= kliens.id) > 0 THEN 'cancel_skema'",
        "WHEN (SELECT COUNT(1) FROM skemas sk              WHERE sk.klien_id = kliens.id) > 0 THEN 'in_skema'",

        "WHEN (SELECT COUNT(1) FROM struktur_3d_cancels sc WHERE sc.klien_id = kliens.id) > 0 THEN 'cancel_struktur3d'",
        "WHEN (SELECT COUNT(1) FROM struktur_3ds s         WHERE s.klien_id  = kliens.id) > 0 THEN 'in_struktur'",

        // Delegasi RAB (di antara Struktur & MEP)
        "WHEN (SELECT COUNT(1) FROM delegasirab dr         WHERE dr.klien_id = kliens.id) > 0 THEN 'in_delegasirab'",

        "WHEN (SELECT COUNT(1) FROM mep_cancels mc         WHERE mc.klien_id = kliens.id) > 0 THEN 'cancel_in_mep'",
        "WHEN (SELECT COUNT(1) FROM meps m                 WHERE m.klien_id  = kliens.id) > 0 THEN 'in_mep'",

        "WHEN (SELECT COUNT(1) FROM exterior_cancels ec    WHERE ec.klien_id = kliens.id) > 0 THEN 'cancel_in_3d_ext'",

        (Schema::hasTable('denah_cancels')
            ? "WHEN (SELECT COUNT(1) FROM denah_cancels dc WHERE dc.klien_id = kliens.id) > 0 THEN 'cancel_denah'"
            : "WHEN 0 > 0 THEN 'cancel_denah'"),

        "WHEN (SELECT COUNT(1) FROM exteriors e            WHERE e.klien_id  = kliens.id) > 0 THEN 'in_3d_ext_int'",

        // Fallback dari kolom status di tabel kliens
        "WHEN COALESCE(kliens.status,'') <> '' THEN kliens.status",
        "ELSE ''",
    ]);

    $q->selectRaw('CASE ' . implode(' ', $case) . ' END AS effective_status');

    // ===== Filter Tanggal
    if ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir')) {
        $start = $request->filled('tanggal_awal')
            ? Carbon::parse($request->tanggal_awal)->startOfDay()
            : Carbon::minValue();

        $end = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::maxValue();

        $q->where(function ($x) use ($start, $end) {
            $x->whereBetween('tanggal_masuk', [$start, $end])
              ->orWhere(function ($y) use ($start, $end) {
                  $y->whereNull('tanggal_masuk')
                    ->whereBetween('created_at', [$start, $end]);
              });
        });
    }

    // ===== Filter Aktif / Nonaktif
    if ($request->filled('status_filter')) {
        if ($request->status_filter === 'aktif') {
            $q->having('effective_status', '=', '');
        } elseif ($request->status_filter === 'nonaktif') {
            $q->having('effective_status', '<>', '');
        }
    }

    // ===== Filter STATUS spesifik (alias lama -> baru)
    if ($request->filled('pipeline_status') && $request->pipeline_status !== 'all') {
        $want = $request->pipeline_status;
        $aliases = [
            'in_tahap_akhir'       => 'in_serter_desain',
            'cancel_tahap_akhir'   => 'cancel_serter_desain',
        ];
        if (isset($aliases[$want])) $want = $aliases[$want];

        $val = $want === 'klien_baru' ? '' : $want;
        $q->having('effective_status', '=', $val);
    }

    // ===== DataTables
    return DataTables::of($q)
        ->addIndexColumn()

        // Tanggal & budget
        ->addColumn('tanggal_masuk', function ($r) {
            $d = $r->tanggal_masuk ?: $r->created_at;
            return $d ? Carbon::parse($d)->format('Y-m-d') : '-';
        })
        ->addColumn('budget_fmt', fn ($r) => 'Rp ' . number_format($r->budget ?? 0, 0, ',', '.'))

        // Badge status
        ->addColumn('status_badge', function ($r) {
            $effective = $r->effective_status ?? '';
            $map = [
                ''                       => ['success',   'Klien Baru'],
                'in_survei'              => ['secondary', 'In Survei'],
                'cancel_survei'          => ['danger',    'Cancel Survei'],
                'denah_moodboard'        => ['info',      'In Denah & Moodboard'],

                'cancel_denah'           => ['danger',    'Cancel Denah'],

                'in_3d_ext_int'          => ['primary',   'In 3D Ext & Int'],
                'cancel_in_3d_ext'       => ['danger',    'Cancel 3D Ext & Int'],

                'in_mep'                 => ['warning',   'In MEP & Spek'],
                'cancel_in_mep'          => ['danger',    'Cancel MEP'],

                // Baru
                'in_delegasirab'         => ['info',      'Delegasi RAB'],

                'in_struktur'            => ['dark',      'In Struktur 3D'],
                'cancel_struktur3d'      => ['danger',    'Cancel Struktur 3D'],

                'in_skema'               => ['info',      'In Skema'],
                'cancel_skema'           => ['danger',    'Cancel Skema'],

                'in_rab'                 => ['warning',   'In RAB'],
                'cancel_rab'             => ['danger',    'Cancel RAB'],

                // Ganti label Tahap Akhir -> Serter Desain
                'in_serter_desain'       => ['primary',   'Serter Desain'],
                'cancel_serter_desain'   => ['danger',    'Cancel Serter Desain'],

                'in_mou'                 => ['success',   'In MOU'],
                'cancel_mou'             => ['danger',    'Cancel MOU'],

                'in_proyekjalan'         => ['success',   'Progres Pembangunan'],
                'proyek_selesai'         => ['primary',   'Proyek Selesai'],
            ];

            [$cls, $text] = $map[$effective] ?? ['success', 'Klien Baru'];
            return '<span class="badge bg-'.$cls.'">'.e($text).'</span>';
        })

        ->addColumn('keterangan_badge', fn () => '-')

        // Aksi (nonaktif jika status bukan klien baru)
        ->addColumn('aksi', function ($r) {
            $show = route('marketing.klien.show', $r->id);
            $edit = route('marketing.klien.edit', $r->id);
            $del  = route('marketing.klien.destroy', $r->id);

            $isDisabled = (($r->effective_status ?? '') !== '');

            $disAttr  = $isDisabled ? 'disabled aria-disabled="true" tabindex="-1"' : '';
            $disClass = $isDisabled ? ' disabled pe-none opacity-50' : '';
            $titleDis = $isDisabled ? ' title="Aksi nonaktif karena status klien bukan Klien Baru"' : '';

            $btnShow = '<a href="'.$show.'" class="btn btn-sm btn-success me-1" title="Lihat"><i class="bi bi-eye"></i></a>';
            $btnEdit = '<a href="'.$edit.'" class="btn btn-sm btn-warning me-1'.$disClass.'" '.$disAttr.$titleDis.'><i class="bi bi-pencil"></i></a>';

            if ($isDisabled) {
                $btnDelete = '<button type="button" class="btn btn-sm btn-danger'.$disClass.'" '.$disAttr.$titleDis.'><i class="bi bi-trash"></i></button>';
            } else {
                $btnDelete = '
                    <form action="'.$del.'" method="POST" class="d-inline"
                          onsubmit="return confirm(\'Yakin ingin menghapus klien ini?\')">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>';
            }

            return $btnShow . $btnEdit . $btnDelete;
        })

        ->setRowClass(fn ($r) => (($r->effective_status ?? '') === '') ? '' : 'row-muted text-muted')
        ->addColumn('status_raw', fn ($r) => $r->effective_status ?? '')
        ->orderColumn('tanggal_masuk', 'tanggal_masuk_sort $1')
        ->rawColumns(['status_badge', 'keterangan_badge', 'aksi'])
        ->make(true);
}

// ====== DATA KLIEN BARU (LAPORAN) – DataTables JSON ======
public function klienBaruDataLaporan(Request $request)
{
    abort_unless($request->ajax(), 404);

    // pakai tanggal_masuk jika ada, fallback created_at
    $dateExpr = DB::raw('COALESCE(tanggal_masuk, created_at)');

    $q = Klien::query()
        ->select([
            'id',
            'nama',
            'lokasi_lahan as lokasi_proyek',
            'kode_proyek',
            'kelas',
            'budget',
            'keterangan',
            DB::raw('COALESCE(tanggal_masuk, created_at) as tanggal_masuk_sort'),
        ]);

    // Filter tanggal (opsional)
    if ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir')) {
        $start = $request->filled('tanggal_awal')
            ? Carbon::parse($request->tanggal_awal)->startOfDay()
            : Carbon::minValue();

        $end = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::maxValue();

        $q->whereBetween($dateExpr, [$start, $end]);
    }

    return DataTables::of($q)
        ->addIndexColumn()
        ->addColumn('tanggal_masuk', fn ($r) =>
            $r->tanggal_masuk_sort ? Carbon::parse($r->tanggal_masuk_sort)->format('Y-m-d') : '-'
        )
        ->addColumn('budget_fmt', fn ($r) =>
            'Rp. ' . number_format((int) $r->budget, 0, ',', '.')
        )
        ->addColumn('aksi', function ($r) {
            $show = route('marketing.klien.show', $r->id);
            return '<a href="'.$show.'" class="btn btn-sm btn-primary" title="Lihat">
                        <i class="bi bi-eye"></i>
                    </a>';
        })
        ->orderColumn('tanggal_masuk', 'tanggal_masuk_sort $1')
        ->rawColumns(['aksi'])
        ->make(true);
}
// Alias kompatibilitas; panggil method yang sama
public function klienDataLaporan(Request $request)
{
    return $this->klienBaruDataLaporan($request);
}


    
    // ====== DATA KLIEN CANCEL (untuk LAPORAN) – aksi hanya Lihat ======
    public function klienCancelDataLaporan(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $q = Klien::onlyTrashed()
            ->addSelect([
                '*',
                DB::raw('deleted_at as tanggal_cancel_sort'),
            ]);

        if ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir')) {
            $start = $request->filled('tanggal_awal')
                ? Carbon::parse($request->tanggal_awal)->startOfDay()
                : Carbon::minValue();

            $end = $request->filled('tanggal_akhir')
                ? Carbon::parse($request->tanggal_akhir)->endOfDay()
                : Carbon::maxValue();

            $q->whereBetween('deleted_at', [$start, $end]);
        }

        return DataTables::of($q)
            ->addIndexColumn()
            // di laporan ini kamu menampilkan “tanggal_masuk”; jika ingin “tanggal_cancel”, silakan ubah header & data key di Blade
            ->addColumn('tanggal_masuk', function ($r) {
                return $r->deleted_at
                    ? Carbon::parse($r->deleted_at)->format('Y-m-d')
                    : ($r->created_at ? Carbon::parse($r->created_at)->format('Y-m-d') : '-');
            })
            ->addColumn('budget_fmt', fn ($r) => 'Rp. ' . number_format($r->budget ?? 0, 0, ',', '.'))
            ->addColumn('keterangan_badge', function ($r) {
                $colors = [
                    'Survei'              => 'success',
                    'Perlu FollowUp'      => 'primary',
                    'Penawaran RAB'       => 'warning',
                    'Budget tidak cukup'  => 'danger',
                    'Diskusi Keluarga'    => 'purple',
                    'Belum Siap'          => 'secondary',
                    'Parsial'             => 'brown',
                ];
                $badge = $colors[$r->keterangan] ?? 'dark';
                return $r->keterangan
                    ? '<span class="badge bg-' . $badge . '">' . e($r->keterangan) . '</span>'
                    : '<span class="text-muted">-</span>';
            })
            ->addColumn('aksi', function ($r) {
                $show = route('marketing.klien_cancel.show', $r->id);
                return '<a href="' . $show . '" class="btn btn-sm btn-success" title="Lihat">
                            <i class="bi bi-eye"></i>
                        </a>';
            })
            // kalau blade laporan menamai kolom sebagai “tanggal_cancel”, ganti sesuai name kolom js nya
            ->orderColumn('tanggal_masuk', 'tanggal_cancel_sort $1')
            ->rawColumns(['keterangan_badge', 'aksi'])
            ->make(true);
    }

    // ====== KLIEN CANCELLED (VIEW) ======
    public function klienCancelled()
    {
        return redirect()->route('marketing.klien.index');
    }

    // ====== KLIEN CANCELLED (DATATABLES JSON) ======
    public function klienCancelledData(Request $request)
   {
    abort_unless($request->ajax(), 404);

    // Subquery: tanggal cancel terakhir per klien untuk keperluan sort.
    $maxCancelSub = KlienCancel::select([
            'klien_id',
            DB::raw('MAX(canceled_at) AS tanggal_cancel_sort'),
        ])
        ->groupBy('klien_id');

    $q = Klien::onlyTrashed()
        ->leftJoinSub($maxCancelSub, 'kc_max', function ($join) {
            $join->on('kc_max.klien_id', '=', 'kliens.id');
        })
        ->with(['cancels' => function ($qq) {
            $qq->latest('canceled_at');
        }])
        ->addSelect([
            'kliens.*',
            DB::raw('kc_max.tanggal_cancel_sort as tanggal_cancel_sort'),
        ]);

    // Filter tanggal cancel (pakai max canceled_at; fallback deleted_at)
    if ($request->filled('tanggal_awal') || $request->filled('tanggal_akhir')) {
        $start = $request->filled('tanggal_awal')
            ? Carbon::parse($request->tanggal_awal)->startOfDay()
            : Carbon::minValue();

        $end = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::maxValue();

        $q->whereBetween(DB::raw('COALESCE(kc_max.tanggal_cancel_sort, kliens.deleted_at)'), [$start, $end]);
    }

    return DataTables::of($q)
        ->addIndexColumn()

        // Tampilkan TANGGAL CANCEL sebagai STRING rapi (bukan ISO)
        ->addColumn('tanggal_cancel', function ($r) {
            // Ambil cancel terbaru kalau ada; fallback ke deleted_at
            $d = $r->tanggal_cancel_sort ?: $r->deleted_at;
            return $d ? Carbon::parse($d)->format('Y-m-d') : '-';
        })

        ->addColumn('budget_fmt', fn ($r) => 'Rp. ' . number_format($r->budget ?? 0, 0, ',', '.'))

        // Keterangan dari alasan cancel terbaru (badge)
        ->addColumn('keterangan_badge', function ($r) {
            $alasan = optional($r->cancels->first())->alasan_cancel;
            return $alasan
                ? '<span class="badge bg-secondary">' . e($alasan) . '</span>'
                : '<span class="text-muted">-</span>';
        })

        ->addColumn('aksi', function ($r) {
            $show = route('marketing.klien_cancel.show', $r->id);
            return '<a href="'.$show.'" class="btn btn-sm btn-success" title="Lihat">
                        <i class="bi bi-eye"></i>
                    </a>';
        })

        // KUNCI: mapping kolom order "tanggal_cancel" (di JS) -> tanggal_cancel_sort (di DB)
        ->orderColumn('tanggal_cancel', 'tanggal_cancel_sort $1')

        ->rawColumns(['keterangan_badge', 'aksi'])
        ->make(true);
}

    // ====== CRUD ======
    public function klienCreate()
    {
        return view('marketing.create');
    }

    public function klienStore(Request $request)
    {
        $data = $request->validate([
            'nama'            => 'required|string|max:255',
            'lokasi_lahan'    => 'nullable|string',
            'luas_lahan'      => 'nullable|string',
            'luas_bangunan'   => 'nullable|string',
            'kebutuhan_ruang' => 'nullable|string',
            'lembar_diskusi'  => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'sertifikat'      => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120',
            'arah_mata_angin' => 'nullable|string',
            'batas_keliling'  => 'nullable|string',
            'foto_eksisting'  => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'konsep_bangunan' => 'nullable|string',
            'referensi'       => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120',
            'budget'          => 'nullable|string',
            'share_lokasi'    => 'nullable|string',
            'biaya_survei'    => 'nullable|string',
            'hoby'            => 'nullable|string',
            'aktivitas'       => 'nullable|string',
            'prioritas_ruang' => 'nullable|string',
            'kendaraan'       => 'nullable|string',
            'estimasi_start'  => 'nullable|date',
            'target_user_kos' => 'nullable|string',
            'fasilitas_kos'   => 'nullable|string',
            'layout'          => 'nullable|file|mimes:pdf,zip|max:5120',
            'desain_3d'       => 'nullable|file|mimes:pdf,zip|max:5120',
            'rab_boq'         => 'nullable|file|mimes:pdf,zip|max:5120',
            'gambar_kerja'    => 'nullable|file|mimes:pdf,zip|max:5120',
            'tanggal_masuk'   => 'nullable|date',
            'email'           => 'nullable|string',
            'alamat_tinggal'  => 'nullable|string',
            'no_hp'           => 'nullable|string',
            'kode_proyek'     => 'nullable|string',
            'kelas'           => 'nullable|string',
            'keterangan'      => 'nullable|string',
        ]);

foreach (['lembar_diskusi', 'sertifikat', 'foto_eksisting', 'referensi', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field) {
    if ($request->hasFile($field)) {
        // saat UPDATE, hapus file lama jika ada
        if (isset($klien) && $klien->$field && Storage::disk('public')->exists($klien->$field)) {
            Storage::disk('public')->delete($klien->$field);
        }
        $data[$field] = $request->file($field)->store("klien/{$field}", 'public');
    }
}

        Klien::create($data);

        return redirect()->route('marketing.klien.index')
            ->with('success', 'Data klien berhasil ditambahkan.');
    }

    public function lanjutSurvei($id)
    {
        $klien = Klien::findOrFail($id);

        if ($klien->status !== 'baru') {
            return back()->with('error', 'Aksi tidak bisa dilakukan, status sudah '.$klien->status);
        }

        $klien->status = 'in_survei';
        $klien->save();

        return back()->with('success', 'Klien masuk ke status survei.');
    }

    public function show($id)
    {
        $klien = \App\Models\Klien::findOrFail($id);

        // --- Normalisasi status sekali di controller ---
        $raw = (string)($klien->status_klien ?? $klien->status ?? '');

        // Ganti NBSP (&nbsp; / 0xC2A0) -> spasi normal
        $norm = str_replace(["\xC2\xA0", '&nbsp;'], ' ', $raw);
        // Rapikan spasi & lowercase
        $norm = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $norm)));

        // "klien baru" / "baru" dianggap NEW
        $isNew = preg_match('/\b(klien\s*baru|baru)\b/u', $norm) === 1;

        // Kamu juga bisa definisikan status lain jika perlu:
        $isInSurvei = preg_match('/\b(in\s*survei|survei)\b/u', $norm) === 1;

        // Kirim ke view
        return view('marketing.view', [
            'klien'      => $klien,
            'statusRaw'  => $raw,
            'statusNorm' => $norm,
            'isNew'      => $isNew,
            'isInSurvei' => $isInSurvei,
        ]);
    }

    public function klienEdit($id)
    {
        $klien = Klien::findOrFail($id);
        return view('marketing.edit', compact('klien'));
    }

    public function klienUpdate(Request $request, $id)
    {
        $klien = Klien::findOrFail($id);
        $data = $request->except('_token', '_method');

        foreach (['sertifikat', 'foto_eksisting', 'referensi', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field) {
            if ($request->hasFile($field)) {
                if ($klien->$field && Storage::disk('public')->exists($klien->$field)) {
                    Storage::disk('public')->delete($klien->$field);
                }
                $data[$field] = $request->file($field)->store("klien/{$field}", 'public');
            }
        }

        $klien->update($data);

        return redirect()->route('marketing.klien.index')
            ->with('success', 'Data klien berhasil diperbarui.');
    }

    public function klienDestroy($id)
    {
        $klien = Klien::findOrFail($id);

        foreach (['sertifikat', 'foto_eksisting', 'referensi', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field) {
            if ($klien->$field && Storage::disk('public')->exists($klien->$field)) {
                Storage::disk('public')->delete($klien->$field);
            }
        }

        $klien->delete();

        return redirect()->route('marketing.klien.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    // ====== CANCEL FLOW ======
    /**
     * Simpan alasan cancel ke tabel klien_cancels lalu soft-delete klien.
     * Dipanggil via AJAX (fetch) dari SweetAlert pada view.blade.
     */
    public function cancel(Request $request, $id)
    {
        $klien = Klien::findOrFail($id);

        DB::transaction(function () use ($klien, $request) {
            // Ambil semua atribut dari klien
            $source = $klien->getAttributes();

            // Ambil daftar kolom yang ada di tabel tujuan
            $cols = Schema::getColumnListing('klien_cancels');

            // Saring hanya kolom yang ada di klien_cancels
            $payload = array_intersect_key($source, array_flip($cols));

            // (opsional) pastikan kolom yang tidak boleh ikut benar-benar terbuang
            unset($payload['id'], $payload['created_at'], $payload['updated_at'], $payload['deleted_at']);

            // Tambahkan metadata cancel
            $payload['klien_id']      = $klien->id;
            $payload['alasan_cancel'] = $request->input('alasan_cancel');
            $payload['canceled_by']   = auth()->id();
            $payload['canceled_at']   = now();

            KlienCancel::create($payload);
            $klien->delete(); // soft delete
        });

        return response()->json(['status' => 'success']);
    }

    /**
     * Tampilkan detail klien yang sudah di-cancel + informasi alasan cancel.
     * $id = id klien (bukan id klien_cancels).
     */
    public function showKlienCancel($id)
    {
        $klien = Klien::onlyTrashed()->findOrFail($id);

        $cancel = KlienCancel::where('klien_id', $id)
            ->latest('canceled_at')
            ->first();

        $klien->setAttribute('alasan_cancel', optional($cancel)->alasan_cancel);
        $klien->setAttribute('canceled_at',  optional($cancel)->canceled_at);
        $klien->setAttribute('canceled_by',  optional($cancel)->canceled_by);

        return view('marketing.kliens_cancel.show', [
            'klienCancel' => $klien,
        ]);
    }

    // ====== LAPORAN + EXPORT ======
    public function laporan()
    {
        return view('marketing.laporan.index');
    }

    // Export Excel klien aktif
    public function exportKliens(Request $request)
    {
        $awal  = $request->query('tanggal_awal');
        $akhir = $request->query('tanggal_akhir');

        $file = 'kliens_aktif_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new KliensExport($awal, $akhir), $file);
    }

    // Export Excel klien cancel
    public function exportKliensCancel(Request $request)
    {
        $awal  = $request->query('tanggal_awal_cancel');
        $akhir = $request->query('tanggal_akhir_cancel');

        $file = 'kliens_cancel_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new KliensCancelExport($awal, $akhir), $file);
    }

    // Kirim ke inbox survei studio
    public function sendToSurvey($id)
    {
        $klien = Klien::findOrFail($id);

        // Cek kalau sudah pernah pending, jangan dobel
        $exists = SurveyRequest::where('klien_id', $klien->id)
            ->where('status', 'pending')
            ->exists();

        if (!$exists) {
            SurveyRequest::create([
                'klien_id'  => $klien->id,
                'status'    => 'pending',
                'sent_by'   => auth()->id(),
                'sent_at'   => now(),
            ]);
        }

        return back()->with('success', 'Berhasil mengirim ke Studio (Inbox Survei).');
    }
}
