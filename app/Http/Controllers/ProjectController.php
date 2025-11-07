<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\SoftDeletes;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

use App\Models\Struktur3d;
use App\Models\Struktur3dCancel;
use App\Models\Skema;
use App\Models\SkemaCancel;
use App\Models\Rab;
use App\Models\RabCancel;
use App\Models\Mou;
use App\Models\TahapAkhir;
use App\Models\ProyekJalan;
use App\Models\ProyekSelesaii;
use Carbon\Carbon as C;

class ProjectController extends Controller
{
    /* ===================== DASHBOARD / LANDING ===================== */

    public function index()
    {
        return view('dashboards.project');
    }

    public function dashboard()
    {
        return view('project.dashboard');
    }

    /* ===================== PROYEK BERJALAN ===================== */

    // LIST PAGE
    public function proyekIndex()
    {
        return view('project.proyek.index');
    }

    public function proyekData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $q = ProyekJalan::query();

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nama',   fn($r) => $r->nama ?? '-')
            ->addColumn('kode',   fn($r) => $r->kode_proyek ?? '-')
            ->addColumn('lokasi', fn($r) => $r->lokasi_lahan ?? '-')
            ->addColumn('tgl_mulai', function ($r) {
                $d = $r->tanggal_mulai ?: $r->created_at;
                return $d ? Carbon::parse($d)->timezone('Asia/Jakarta')->format('d-m-Y') : '-';
            })
            ->addColumn('status', function ($r) {
                $p = max(0, min(100, (int)($r->status_progres ?? 0)));
                return "
                    <div class='d-flex align-items-center' style='gap:.5rem'>
                      <div class='progress flex-grow-1' style='height:8px;'>
                        <div class='progress-bar' role='progressbar' style='width: {$p}%;' aria-valuenow='{$p}' aria-valuemin='0' aria-valuemax='100'></div>
                      </div>
                      <small class='text-nowrap'>{$p}%</small>
                    </div>
                ";
            })
            ->addColumn('aksi', function ($r) {
                $show = route('project.proyek.show', $r->id);
                $edit = route('project.proyek.edit', $r->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-info me-1" title="Lihat"><i class="bi bi-eye"></i></a>'
                     . '<a href="'.$edit.'" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>';
            })
            ->rawColumns(['status', 'aksi'])
            ->orderColumn('tgl_mulai', 'tanggal_mulai $1')
            ->make(true);
    }

    // SHOW PAGE
    public function proyekShow(ProyekJalan $proyek)
    {
        return view('project.proyek.show', compact('proyek'));
    }

    // EDIT PAGE
    public function proyekEdit(ProyekJalan $proyek)
    {
        return view('project.proyek.edit', compact('proyek'));
    }

    // UPDATE
    public function proyekUpdate(Request $request, ProyekJalan $proyek)
    {
        $data = $request->validate([
            'nama'             => ['nullable','string','max:255'],
            'kode_proyek'      => ['nullable','string','max:255'],
            'kelas'            => ['nullable','string','max:50'],
            'alamat_tinggal'   => ['nullable','string','max:255'],
            'lokasi_lahan'     => ['nullable','string','max:255'],
            'luas_lahan'       => ['nullable','numeric'],
            'luas_bangunan'    => ['nullable','numeric'],
            'kebutuhan_ruang'  => ['nullable','string'],
            'arah_mata_angin'  => ['nullable','string','max:100'],
            'batas_keliling'   => ['nullable','string','max:255'],
            'konsep_bangunan'  => ['nullable','string'],
            'budget'           => ['nullable','numeric'],
            'lembar_diskusi'   => ['nullable','string','max:255'],
            'layout'           => ['nullable','string','max:255'],
            'desain_3d'        => ['nullable','string','max:255'],
            'rab_boq'          => ['nullable','string','max:255'],
            'gambar_kerja'     => ['nullable','string','max:255'],
            'lembar_survei'    => ['nullable','string','max:255'],
            'keterangan'       => ['nullable','string'],
            'tanggal_masuk'    => ['nullable','date'],
            'tanggal_mulai'    => ['nullable','date'],
            'status_progres'   => ['nullable','integer','between:0,100'],
        ]);

        $proyek->update($data);

        return redirect()
            ->route('project.proyek.index')
            ->with('success', 'Proyek berhasil diperbarui.');
    }

    /* ===================== STRUKTUR 3D ===================== */

/* ===================== STRUKTUR 3D ===================== */

public function struktur3dIndex()
{
    return view('project.struktur3d.index');
}

public function struktur3dData(Request $r)
{
    // Ambil langsung dari tabel yang benar + siapkan kolom order by tanggal
    $q = \App\Models\Struktur3d::query()
        ->from('struktur_3ds')
        ->select([
            'id',
            'nama',
            'kode_proyek',
            'lokasi_lahan',
            'tanggal_masuk',
            'created_at',
        ])
        // pakai COALESCE agar kalau tanggal_masuk null, jatuh ke created_at
        ->selectRaw('COALESCE(UNIX_TIMESTAMP(tanggal_masuk), UNIX_TIMESTAMP(created_at)) AS masuk_ts');

    return \Yajra\DataTables\Facades\DataTables::of($q)
        ->addIndexColumn()

        // format tampilan tanggal_masuk (fallback ke created_at)
        ->editColumn('tanggal_masuk', function ($m) {
            $dt = $m->tanggal_masuk ?: $m->created_at;
            try {
                return $dt ? \Carbon\Carbon::parse($dt)->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
            } catch (\Throwable $e) {
                return (string) $dt ?: '-';
            }
        })

        // pastikan order pada kolom tanggal menggunakan masuk_ts
        ->orderColumn('tanggal_masuk', 'masuk_ts $1')

        // tombol aksi (hanya "lihat" yang tampil di tabel)
        ->addColumn('aksi', function ($m) {
            $show = route('project.struktur3d.show', $m->id);
            return '<a href="'.$show.'" class="btn btn-sm btn-info btn-view" title="Lihat"><i class="bi bi-eye"></i></a>';
        })

        ->rawColumns(['aksi'])
        ->make(true);
}


public function struktur3dCancelData(Request $r)
{
    $q = \App\Models\Struktur3dCancel::query()
        ->select('struktur_3d_cancels.*')
        ->selectRaw('UNIX_TIMESTAMP(canceled_at) AS canceled_ts');

    return \Yajra\DataTables\Facades\DataTables::of($q)
        ->addIndexColumn()
        ->addColumn('nama',  fn($m) => $m->nama ?? $m->nama_proyek ?? '-')
        ->addColumn('lokasi',fn($m) => $m->lokasi_lahan ?? $m->lokasi ?? '-')
        ->addColumn('alasan',fn($m) => $m->alasan_cancel ?? $m->alasan ?? '-')
        ->addColumn('tgl_cancel', function ($m) {
            return optional($m->canceled_at)
                    ? $m->canceled_at->timezone('Asia/Jakarta')->format('d-m-Y H:i')
                    : '-';
        })
        ->orderColumn('tgl_cancel', 'canceled_ts $1')
        ->make(true);
}

public function struktur3dShow(\App\Models\Struktur3d $struktur3d)
{
    $struktur3d->load('klien');
    return view('project.struktur3d.show', compact('struktur3d'));
}


public function toSkema(Request $r, Struktur3d $struktur3d)
{
    DB::transaction(function () use ($struktur3d) {
        $k  = $struktur3d->klien;                               // relasi klien (boleh null)
        $kf = $this->fetchKlienSurvei($struktur3d, 'struktur_3ds'); // baris klien_survei (boleh null)

        // helper: ambil nilai pertama yang tidak kosong
        $pick = function (...$vals) {
            foreach ($vals as $v) {
                if (isset($v) && $v !== '' && $v !== []) return $v;
            }
            return null;
        };
        // helper: ambil dari objek dengan beberapa kandidat nama kolom
        $from = function ($obj, string ...$names) {
            if (!$obj) return null;
            foreach ($names as $n) {
                if (isset($obj->{$n}) && $obj->{$n} !== '' && $obj->{$n} !== []) {
                    return $obj->{$n};
                }
            }
            return null;
        };

        // normalisasi tanggal_masuk
        $tglMasuk = $pick(
            $struktur3d->tanggal_masuk,
            $from($kf, 'tanggal_masuk'),
            $from($k,  'tanggal_masuk'),
            $struktur3d->created_at
        );

        // payload lengkap → tabel skemas
        $all = [
            'klien_id'       => $pick($struktur3d->klien_id, optional($k)->id),
            'struktur3d_id'  => $struktur3d->id,
            'status_skema'   => 'draft',

            'nama'           => $pick($struktur3d->nama,           $from($kf,'nama'),           optional($k)->nama) ?: 'Tanpa Nama',
            'email'          => $pick($struktur3d->email,          $from($kf,'email'),          optional($k)->email),
            'no_hp'          => $pick($struktur3d->no_hp,          $from($kf,'no_hp'),          optional($k)->no_hp),
            'alamat_tinggal' => $pick($struktur3d->alamat_tinggal, $from($kf,'alamat_tinggal'), optional($k)->alamat_tinggal),

            'kode_proyek'    => $pick($struktur3d->kode_proyek, $from($kf,'kode_proyek'), optional($k)->kode_proyek) ?: '-',
            'kelas'          => $pick($struktur3d->kelas,       $from($kf,'kelas'),       optional($k)->kelas),
            'lokasi_lahan'   => $pick($struktur3d->lokasi_lahan,$from($kf,'lokasi_lahan'),optional($k)->lokasi_lahan),

            'luas_lahan'       => $pick($struktur3d->luas_lahan,       $from($kf,'luas_lahan'),       optional($k)->luas_lahan),
            'luas_bangunan'    => $pick($struktur3d->luas_bangunan,    $from($kf,'luas_bangunan'),    optional($k)->luas_bangunan),
            'kebutuhan_ruang'  => $pick($struktur3d->kebutuhan_ruang,  $from($kf,'kebutuhan_ruang'),  optional($k)->kebutuhan_ruang),

            'sertifikat'       => $pick($struktur3d->sertifikat,       $from($kf,'sertifikat','Sertifikat'), $from($k,'sertifikat','Sertifikat')),
            'arah_mata_angin'  => $pick($struktur3d->arah_mata_angin,  $from($kf,'arah_mata_angin'),  $from($k,'arah_mata_angin')),
            'batas_keliling'   => $pick($struktur3d->batas_keliling,   $from($kf,'batas_keliling'),   $from($k,'batas_keliling')),
            'foto_eksisting'   => $pick($struktur3d->foto_eksisting,   $from($kf,'foto_eksisting'),   $from($k,'foto_eksisting')),

            'konsep_bangunan'  => $pick($struktur3d->konsep_bangunan,  $from($kf,'konsep_bangunan'),  $from($k,'konsep_bangunan')),
            'referensi'         => $pick($struktur3d->referensi,        $from($kf,'referensi'),        $from($k,'referensi')),
            'budget'            => $pick($struktur3d->budget,           $from($kf,'budget'),           $from($k,'budget')),
            'share_lokasi'      => $pick($struktur3d->share_lokasi,     $from($kf,'share_lokasi'),     $from($k,'share_lokasi')),
            'biaya_survei'      => $pick(
                $struktur3d->biaya_survei ?? $struktur3d->biaya_survey ?? null,
                $from($kf,'biaya_survei','biaya_survey'),
                $from($k,'biaya_survei','biaya_survey')
            ),

            'hoby'              => $pick($struktur3d->hoby,              $from($kf,'hoby'),              $from($k,'hoby')),
            'aktivitas'         => $pick($struktur3d->aktivitas,         $from($kf,'aktivitas','aktifitas'), $from($k,'aktivitas','aktifitas')),
            'prioritas_ruang'   => $pick($struktur3d->prioritas_ruang,   $from($kf,'prioritas_ruang'),   $from($k,'prioritas_ruang')),
            'kendaraan'         => $pick($struktur3d->kendaraan,         $from($kf,'kendaraan'),         $from($k,'kendaraan')),
            'estimasi_start'    => $pick($struktur3d->estimasi_start,    $from($kf,'estimasi_start'),    $from($k,'estimasi_start')),
            'target_user_kos'   => $pick($struktur3d->target_user_kos,   $from($kf,'target_user_kos'),   $from($k,'target_user_kos')),
            'fasilitas_kos'     => $pick($struktur3d->fasilitas_kos,     $from($kf,'fasilitas_kos'),     $from($k,'fasilitas_kos')),

            'layout'            => $pick($struktur3d->layout,            $from($kf,'layout'),            $from($k,'layout')),
            'desain_3d'         => $pick($struktur3d->desain_3d,         $from($kf,'desain_3d'),         $from($k,'desain_3d')),
            'rab_boq'           => $pick($struktur3d->rab_boq,           $from($kf,'rab_boq'),           $from($k,'rab_boq')),
            'gambar_kerja'      => $pick($struktur3d->gambar_kerja,      $from($kf,'gambar_kerja'),      $from($k,'gambar_kerja')),
            'lembar_diskusi'    => $pick($struktur3d->lembar_diskusi,    $from($kf,'lembar_diskusi'),    $from($k,'lembar_diskusi')),
            'lembar_survei'     => $pick($struktur3d->lembar_survei,     $from($kf,'lembar_survei'),     $from($k,'lembar_survei')),
            'catatan_survei'    => $pick($struktur3d->catatan_survei,    $from($kf,'catatan_survei'),    $from($k,'catatan_survei')),

            'tanggal_masuk'     => $tglMasuk,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        $cols    = Schema::getColumnListing('skemas');
        $payload = array_intersect_key($all, array_flip($cols));

        Skema::create($payload);

        if ($struktur3d->klien_id && Schema::hasColumn('kliens', 'status')) {
            DB::table('kliens')->where('id', $struktur3d->klien_id)->update(['status' => 'in_skema']);
        }

        $this->hardDelete($struktur3d);
    });

    return redirect()->route('project.struktur3d.index')->with('success', 'Dipindahkan ke tahap Skema.');
}

    public function cancelStruktur3d(Request $r, Struktur3d $struktur3d)
    {
        DB::transaction(function () use ($r, $struktur3d) {
            Struktur3dCancel::create([
                'struktur3d_id' => $struktur3d->id,
                'klien_id'      => Schema::hasColumn('struktur_3d_cancels', 'klien_id') ? $struktur3d->klien_id : null,
                'nama'          => $struktur3d->nama,
                'kode_proyek'   => $struktur3d->kode_proyek,
                'lokasi_lahan'  => $struktur3d->lokasi_lahan,
                'alasan_cancel' => $r->input('alasan_cancel'),
                'canceled_at'   => now(),
            ]);

            if ($struktur3d->klien_id && Schema::hasColumn('kliens', 'status')) {
                DB::table('kliens')->where('id', $struktur3d->klien_id)->update(['status' => 'cancel_struktur3d']);
            }

            $struktur3d->delete();
        });

        return back()->with('success', 'Masuk daftar cancel Struktur 3D.');
    }

    /* ===================== SKEMA ===================== */

    public function skemaIndex()
    {
        return view('project.skema.index');
    }

    public function skemaData(Request $r)
    {
        $q = Skema::query()
            ->select('skemas.*')
            ->selectRaw('UNIX_TIMESTAMP(created_at) as created_ts');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('created_fmt', fn($m)=> optional($m->created_at)->timezone('Asia/Jakarta')?->format('d-m-Y H:i') ?? '-')
            ->orderColumn('created_fmt','created_ts $1')
            ->addColumn('aksi', function($m){
                $show   = route('project.skema.show', $m->id);
                $to     = route('project.skema.to_rab', $m->id);
                $cancel = route('project.skema.cancel', $m->id);
                return '
                    <a href="'.$show.'" class="btn btn-sm btn-info me-1" title="Lihat"><i class="bi bi-eye"></i></a>
                    <form action="'.$to.'" method="POST" class="d-inline">'.csrf_field().'
                        <button class="btn btn-sm btn-success" title="Lanjut ke RAB"><i class="bi bi-check2-circle"></i></button>
                    </form>
                    <form action="'.$cancel.'" method="POST" class="d-inline">'.csrf_field().'
                        <button class="btn btn-sm btn-danger" title="Cancel"><i class="bi bi-x-circle"></i></button>
                    </form>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function skemaCancelData(Request $r)
    {
        $q = SkemaCancel::query()
            ->select('skema_cancels.*')
            ->selectRaw('UNIX_TIMESTAMP(canceled_at) as canceled_ts');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('tanggal_cancel', fn ($m) =>
                optional($m->canceled_at)->timezone('Asia/Jakarta')?->format('d-m-Y H:i') ?? '-'
            )
            ->orderColumn('tanggal_cancel', 'canceled_ts $1')
            ->make(true);
    }

    public function skemaShow(Skema $skema)
    {
        $skema->load('klien');
        return view('project.skema.show', compact('skema'));
    }

public function toRab(Request $r, Skema $skema)
{
    DB::transaction(function () use ($skema) {
        $k  = $skema->klien;                       // relasi klien (boleh null)
        $kf = $this->fetchKlienSurvei($skema, 'skemas'); // baris klien_survei (boleh null)

        // helper pick: ambil nilai pertama yg tidak kosong
        $pick = function (...$vals) {
            foreach ($vals as $v) {
                if (isset($v) && $v !== '' && $v !== []) return $v;
            }
            return null;
        };
        // helper from: ambil dari objek dg beberapa kandidat nama kolom
        $from = function ($obj, string ...$names) {
            if (!$obj) return null;
            foreach ($names as $n) {
                if (isset($obj->{$n}) && $obj->{$n} !== '' && $obj->{$n} !== []) {
                    return $obj->{$n};
                }
            }
            return null;
        };

        // normalisasi tanggal_masuk (fallback ke created_at sumber)
        $tglMasuk = $pick(
            $skema->tanggal_masuk,
            $from($kf, 'tanggal_masuk'),
            $from($k,  'tanggal_masuk'),
            $skema->created_at
        );

        // rakit payload lengkap
        $all = [
            'klien_id'       => $pick($skema->klien_id, optional($k)->id),
            'skema_id'       => $skema->id,
            'status_rab'     => 'draft',

            'nama'           => $pick($skema->nama,           $from($kf,'nama'),           optional($k)->nama) ?: 'Tanpa Nama',
            'email'          => $pick($skema->email,          $from($kf,'email'),          optional($k)->email),
            'no_hp'          => $pick($skema->no_hp,          $from($kf,'no_hp'),          optional($k)->no_hp),
            'alamat_tinggal' => $pick($skema->alamat_tinggal, $from($kf,'alamat_tinggal'), optional($k)->alamat_tinggal),

            'kode_proyek'    => $pick($skema->kode_proyek, $from($kf,'kode_proyek'), optional($k)->kode_proyek) ?: '-',
            'kelas'          => $pick($skema->kelas,       $from($kf,'kelas'),       optional($k)->kelas),
            'lokasi_lahan'   => $pick($skema->lokasi_lahan,$from($kf,'lokasi_lahan'),optional($k)->lokasi_lahan),

            'luas_lahan'       => $pick($skema->luas_lahan,       $from($kf,'luas_lahan'),       optional($k)->luas_lahan),
            'luas_bangunan'    => $pick($skema->luas_bangunan,    $from($kf,'luas_bangunan'),    optional($k)->luas_bangunan),
            'kebutuhan_ruang'  => $pick($skema->kebutuhan_ruang,  $from($kf,'kebutuhan_ruang'),  optional($k)->kebutuhan_ruang),

            'sertifikat'       => $pick($skema->sertifikat,       $from($kf,'sertifikat','Sertifikat'), $from($k,'sertifikat','Sertifikat')),
            'arah_mata_angin'  => $pick($skema->arah_mata_angin,  $from($kf,'arah_mata_angin'),  $from($k,'arah_mata_angin')),
            'batas_keliling'   => $pick($skema->batas_keliling,   $from($kf,'batas_keliling'),   $from($k,'batas_keliling')),
            'foto_eksisting'   => $pick($skema->foto_eksisting,   $from($kf,'foto_eksisting'),   $from($k,'foto_eksisting')),

            'konsep_bangunan'  => $pick($skema->konsep_bangunan,  $from($kf,'konsep_bangunan'),  $from($k,'konsep_bangunan')),
            'referensi'         => $pick($skema->referensi,        $from($kf,'referensi'),        $from($k,'referensi')),
            'budget'            => $pick($skema->budget,           $from($kf,'budget'),           $from($k,'budget')),
            'share_lokasi'      => $pick($skema->share_lokasi,     $from($kf,'share_lokasi'),     $from($k,'share_lokasi')),
            'biaya_survei'      => $pick(
                $skema->biaya_survei ?? $skema->biaya_survey ?? null,
                $from($kf,'biaya_survei','biaya_survey'),
                $from($k,'biaya_survei','biaya_survey')
            ),

            'hoby'              => $pick($skema->hoby,              $from($kf,'hoby'),              $from($k,'hoby')),
            'aktivitas'         => $pick($skema->aktivitas,         $from($kf,'aktivitas','aktifitas'), $from($k,'aktivitas','aktifitas')),
            'prioritas_ruang'   => $pick($skema->prioritas_ruang,   $from($kf,'prioritas_ruang'),   $from($k,'prioritas_ruang')),
            'kendaraan'         => $pick($skema->kendaraan,         $from($kf,'kendaraan'),         $from($k,'kendaraan')),
            'estimasi_start'    => $pick($skema->estimasi_start,    $from($kf,'estimasi_start'),    $from($k,'estimasi_start')),
            'target_user_kos'   => $pick($skema->target_user_kos,   $from($kf,'target_user_kos'),   $from($k,'target_user_kos')),
            'fasilitas_kos'     => $pick($skema->fasilitas_kos,     $from($kf,'fasilitas_kos'),     $from($k,'fasilitas_kos')),

            'layout'            => $pick($skema->layout,            $from($kf,'layout'),            $from($k,'layout')),
            'desain_3d'         => $pick($skema->desain_3d,         $from($kf,'desain_3d'),         $from($k,'desain_3d')),
            'rab_boq'           => $pick($skema->rab_boq,           $from($kf,'rab_boq'),           $from($k,'rab_boq')),
            'gambar_kerja'      => $pick($skema->gambar_kerja,      $from($kf,'gambar_kerja'),      $from($k,'gambar_kerja')),
            'lembar_diskusi'    => $pick($skema->lembar_diskusi,    $from($kf,'lembar_diskusi'),    $from($k,'lembar_diskusi')),
            'lembar_survei'     => $pick($skema->lembar_survei,     $from($kf,'lembar_survei'),     $from($k,'lembar_survei')),
            'catatan_survei'    => $pick($skema->catatan_survei,    $from($kf,'catatan_survei'),    $from($k,'catatan_survei')),

            'tanggal_masuk'     => $tglMasuk,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        // kirim hanya kolom yang ada di tabel rabs
        $cols    = Schema::getColumnListing('rabs');
        $payload = array_intersect_key($all, array_flip($cols));

        Rab::create($payload);

        if ($skema->klien_id && Schema::hasColumn('kliens', 'status')) {
            DB::table('kliens')->where('id', $skema->klien_id)->update(['status' => 'in_rab']);
        }

        $this->hardDelete($skema);
    });

    return redirect()->route('project.skema.index')->with('success', 'Dipindahkan ke tahap RAB.');
}

    public function cancelSkema(Request $r, Skema $skema)
    {
        DB::transaction(function () use ($r, $skema) {
            SkemaCancel::create([
                'skema_id'      => $skema->id,
                'klien_id'      => Schema::hasColumn('skema_cancels', 'klien_id') ? $skema->klien_id : null,
                'nama'          => $skema->nama,
                'kode_proyek'   => $skema->kode_proyek,
                'lokasi_lahan'  => $skema->lokasi_lahan,
                'alasan_cancel' => $r->input('alasan_cancel'),
                'canceled_at'   => now(),
            ]);

            if ($skema->klien_id && Schema::hasColumn('kliens', 'status')) {
                DB::table('kliens')->where('id', $skema->klien_id)->update(['status' => 'cancel_skema']);
            }

            $skema->delete();
        });

        return back()->with('success', 'Masuk daftar cancel Skema.');
    }

    /* ===================== RAB ===================== */

    public function rabIndex()
    {
        return view('project.rab.index');
    }

    public function rabData(Request $r)
    {
        $q = Rab::query()
            ->select('rabs.*')
            ->selectRaw('UNIX_TIMESTAMP(rabs.created_at) as created_ts');

        return DataTables::of($q)
            ->filter(function ($query) use ($r) {
                $kw = trim((string) data_get($r->all(), 'search.value', ''));
                if ($kw !== '') {
                    $query->where(function ($qq) use ($kw) {
                        $qq->where('rabs.nama', 'like', "%{$kw}%")
                           ->orWhere('rabs.kode_proyek', 'like', "%{$kw}%")
                           ->orWhere('rabs.lokasi_lahan', 'like', "%{$kw}%");
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('created_fmt', fn ($m) =>
                optional($m->created_at)->timezone('Asia/Jakarta')?->format('d-m-Y') ?? '-'
            )
            ->orderColumn('created_fmt', 'created_ts $1')
            ->addColumn('aksi', function ($m) {
                $show = route('project.rab.show', $m->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-info" title="Lihat"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function rabCancelData(Request $r)
    {
        $q = RabCancel::query()
            ->select('rab_cancels.*')
            ->selectRaw('UNIX_TIMESTAMP(canceled_at) as canceled_ts');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('tanggal_cancel', fn ($m) =>
                optional($m->canceled_at)->timezone('Asia/Jakarta')?->format('d-m-Y H:i') ?? '-'
            )
            ->orderColumn('tanggal_cancel', 'canceled_ts $1')
            ->make(true);
    }

    public function cancelRab(Request $r, Rab $rab)
    {
        DB::transaction(function () use ($r, $rab) {
            RabCancel::create([
                'rab_id'        => $rab->id,
                'klien_id'      => Schema::hasColumn('rab_cancels', 'klien_id') ? $rab->klien_id : null,
                'nama'          => $rab->nama,
                'kode_proyek'   => $rab->kode_proyek,
                'lokasi_lahan'  => Schema::hasColumn('rab_cancels', 'lokasi_lahan') ? $rab->lokasi_lahan : null,
                'alasan_cancel' => $r->input('alasan_cancel'),
                'canceled_at'   => now(),
            ]);

            if ($rab->klien_id && Schema::hasColumn('kliens', 'status')) {
                DB::table('kliens')->where('id', $rab->klien_id)->update(['status' => 'cancel_rab']);
            }

            $rab->delete();
        });

        if ($r->wantsJson() || $r->ajax()) {
            return response()->json([
                'status'   => 'ok',
                'redirect' => route('project.rab.index'),
            ]);
        }

        return redirect()
            ->route('project.rab.index')
            ->with('success', 'Masuk daftar cancel RAB.');
    }

    public function rabShow(Rab $rab)
    {
        $rab->load('klien');
        return view('project.rab.show', compact('rab'));
    }

public function toMou(Request $r, Rab $rab)
{
    DB::transaction(function () use ($rab) {
        $k  = $rab->klien;
        $kf = $this->fetchKlienSurvei($rab, 'rabs');

        $base = $this->mapRequiredFields($rab, $k, $kf);

        $all = array_merge($base, [
            'klien_id'   => $this->firstNonEmpty([$rab->klien_id, optional($k)->id]),
            'rab_id'     => $rab->id,
            'status_mou' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cols    = Schema::getColumnListing('mous');
        $payload = array_intersect_key($all, array_flip($cols));

        Mou::create($payload);

        if ($rab->klien_id && Schema::hasColumn('kliens', 'status')) {
            DB::table('kliens')->where('id', $rab->klien_id)->update(['status' => 'in_mou']);
        }

        $this->hardDelete($rab);
    });

    return redirect()->route('project.mou.index')->with('success', 'Dipindahkan ke tahap MOU.');
}


    public function toAkhir(Request $r, Rab $rab)
    {
        DB::transaction(function () use ($rab) {
            $k  = $rab->klien;
            $kf = $this->fetchKlienSurvei($rab, 'rabs');

            $base = $this->mapRequiredFields($rab, $k, $kf);

            $all = array_merge($base, [
                'klien_id'      => $this->firstNonEmpty([$rab->klien_id, optional($k)->id]),
                'rab_id'        => $rab->id,
                // ⬇️ default status serter
                'status_akhir'  => 'Belum Serter',
                'status'        => 'Belum Serter',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $cols    = Schema::getColumnListing('tahap_akhirs');
            $payload = array_intersect_key($all, array_flip($cols));

            TahapAkhir::create($payload);

            if ($rab->klien_id && Schema::hasColumn('kliens', 'status')) {
                DB::table('kliens')->where('id', $rab->klien_id)->update(['status' => 'in_tahap_akhir']);
            }

            $this->hardDelete($rab);
        });

        return redirect()
            ->route('project.rab.index')
            ->with('success', 'Dipindahkan ke Tahap Akhir.');
    }

    public function mouIndex()
    {
        return view('project.mou.index');
    }

    public function mouData(Request $r)
    {
        $q = Mou::query()
            ->select('mous.*')
            ->selectRaw('UNIX_TIMESTAMP(created_at) as created_ts');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('created_fmt', fn($m)=> optional($m->created_at)->timezone('Asia/Jakarta')?->format('d-m-Y H:i') ?? '-')
            ->orderColumn('created_fmt','created_ts $1')
            ->addColumn('aksi', function($m){
                $show = route('project.mou.show', $m->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-info" title="Lihat"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function mouShow(Mou $mou)
    {
        $mou->load('klien');
        return view('project.mou.show', compact('mou'));
    }

public function mouToProyekJalan(Request $r, Mou $mou)
{
    DB::transaction(function () use ($mou, $r) {

        // ==== Resolve tanggal_mulai (berlapis) ====
        // kemungkinan nama kolom di MOU: tanggal_mulai, tanggal_mulai_proyek, tgl_mulai
        $tanggalMulai = $r->input('tanggal_mulai');
        if (blank($tanggalMulai)) {
            $tanggalMulai = $mou->tanggal_mulai
                           ?? $mou->tanggal_mulai_proyek
                           ?? $mou->tgl_mulai
                           ?? now()->toDateString();
        }

        $pick = function (...$vals) {
            foreach ($vals as $v) if (!blank($v)) return $v;
            return null;
        };

        $mou->loadMissing('klien');

        // ==== Fallback lembar_survei ====
        $resolveSurvey = function () use ($mou) {
            if (!blank($mou->lembar_survei)) return $mou->lembar_survei;

            if ($kf = $this->fetchKlienSurvei($mou, (new Mou)->getTable())) {
                if (!blank(data_get($kf,'lembar_survei'))) return $kf->lembar_survei;
            }

            try {
                if ($mou->klien_id && Schema::hasTable('klien_survei')) {
                    $latest = DB::table('klien_survei')->where('klien_id',$mou->klien_id)->orderByDesc('id')->first();
                    if ($latest && !blank($latest->lembar_survei ?? null)) return $latest->lembar_survei;
                }
            } catch (\Throwable $e) {}

            if (!blank($mou->rab_id ?? null)) {
                if ($rab = \App\Models\Rab::find($mou->rab_id)) {
                    if (!blank($rab->lembar_survei)) return $rab->lembar_survei;
                    if (!blank($rab->skema_id ?? null)) {
                        if ($skema = \App\Models\Skema::find($rab->skema_id)) {
                            if (!blank($skema->lembar_survei)) return $skema->lembar_survei;
                            if (!blank($skema->struktur3d_id ?? null)) {
                                if ($s3d = \App\Models\Struktur3d::find($skema->struktur3d_id)) {
                                    if (!blank($s3d->lembar_survei)) return $s3d->lembar_survei;
                                }
                            }
                        }
                    }
                }
            }

            if ($mou->klien && !blank($mou->klien->lembar_survei ?? null)) {
                return $mou->klien->lembar_survei;
            }
            return null;
        };

        $lembarSurvei = $resolveSurvey();
        $k = $mou->klien;

        $all = [
            'klien_id'        => $pick($mou->klien_id, optional($k)->id),
            'mou_id'          => $mou->id,
            'nama'            => $pick($mou->nama, optional($k)->nama) ?: 'Tanpa Nama',
            'kode_proyek'     => $pick($mou->kode_proyek, optional($k)->kode_proyek) ?: '-',
            'kelas'           => $pick($mou->kelas, optional($k)->kelas),
            'alamat_tinggal'  => $pick($mou->alamat_tinggal, optional($k)->alamat_tinggal),
            'lokasi_lahan'    => $pick($mou->lokasi_lahan, optional($k)->lokasi_lahan),
            'luas_lahan'      => $mou->luas_lahan,
            'luas_bangunan'   => $mou->luas_bangunan,
            'kebutuhan_ruang' => $mou->kebutuhan_ruang,
            'arah_mata_angin' => $mou->arah_mata_angin,
            'batas_keliling'  => $mou->batas_keliling,
            'konsep_bangunan' => $mou->konsep_bangunan,
            'referensi'       => $mou->referensi,
            'budget'          => $mou->budget,

            // berkas
            'lembar_diskusi'  => $mou->lembar_diskusi,
            'layout'          => $mou->layout,
            'desain_3d'       => $mou->desain_3d,
            'rab_boq'         => $mou->rab_boq,
            'gambar_kerja'    => $mou->gambar_kerja,
            'lembar_survei'   => $lembarSurvei,
            'catatan_survei'  => $pick($mou->catatan_survei, optional($k)->catatan_survei),
            'keterangan'      => $mou->keterangan,

            'tanggal_masuk'   => $mou->tanggal_masuk ?? $mou->created_at,
            'tanggal_mulai'   => $tanggalMulai,   // <<< penting: selalu terisi
            'status_progres'  => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        $dstTable = (new \App\Models\ProyekJalan)->getTable();
        $cols     = Schema::getColumnListing($dstTable);
        $payload  = array_intersect_key($all, array_flip($cols));

        \App\Models\ProyekJalan::create($payload);

        if ($mou->klien_id && Schema::hasColumn('kliens','status')) {
            DB::table('kliens')->where('id',$mou->klien_id)->update(['status'=>'in_proyek']);
        }

        $this->hardDelete($mou);
    });

    return redirect()->route('project.mou.index')->with('success', 'Dipindahkan ke Proyek Berjalan (tanggal mulai & lembar survei ikut).');
}



    /* ===================== PROYEK SELESAI ===================== */

    public function selesaiIndex()
    {
        return view('project.selesai.index');
    }

public function selesaiData(Request $request)
{
    $q = ProyekSelesaii::query();

    return datatables()->of($q)
        ->addIndexColumn()
        ->addColumn('nama', fn($r) => $r->nama ?? $r->nama_proyek ?? '-')
        ->addColumn('kode', fn($r) => $r->kode ?? $r->kode_proyek ?? '-')
        ->addColumn('lokasi', fn($r) => $r->lokasi ?? $r->lokasi_lahan ?? '-')

        ->addColumn('tgl_mulai', function ($r) {
            $d = $r->tanggal_mulai ?? $r->tgl_mulai ?? null;
            if (!$d) return '-';
            try { return \Carbon\Carbon::parse($d)->format('Y-m-d'); }
            catch (\Throwable $e) { return (string)$d; }
        })
        // NEW: tgl_selesai
        ->addColumn('tgl_selesai', function ($r) {
            $d = $r->tanggal_selesai ?? $r->tgl_selesai ?? $r->tanggal_akhir ?? null;
            if (!$d) return '-';
            try { return \Carbon\Carbon::parse($d)->format('Y-m-d'); }
            catch (\Throwable $e) { return (string)$d; }
        })

        // pastikan nama kolom badge sama dengan di Blade (status_badge)
        ->addColumn('status_badge', fn() => '<span class="badge bg-success">Proyek Selesai</span>')

        ->addColumn('aksi', function ($r) {
            $url = route('project.selesai.show', $r->id);
            return '<a href="'.$url.'" class="btn btn-success btn-sm" title="Lihat"><i class="bi bi-eye"></i></a>';
        })
        ->rawColumns(['status_badge','aksi'])
        ->make(true);
}


    public function selesaiShow($id)
    {
        $data = ProyekSelesaii::findOrFail($id);
        return view('project.selesai.show', compact('data'));
    }

    public function selesai(ProyekJalan $proyek, Request $request)
    {
        DB::transaction(function () use ($proyek) {

            if (ProyekSelesaii::where('moved_from_id', $proyek->getKey())->exists()) {
                if (in_array(SoftDeletes::class, class_uses_recursive($proyek))) {
                    $proyek->forceDelete();
                } else {
                    $proyek->delete();
                }
                return;
            }

            $src = $proyek->getAttributes();
            unset($src['id']);

            $dstTable = (new ProyekSelesaii)->getTable();
            $dstCols  = Schema::getColumnListing($dstTable);
            $attr     = Arr::only($src, $dstCols);

            $now = now();
            if (in_array('created_at', $dstCols) && empty($attr['created_at'])) $attr['created_at'] = $now;
            if (in_array('updated_at', $dstCols)) $attr['updated_at'] = $now;
            if (in_array('tanggal_selesai', $dstCols)) $attr['tanggal_selesai'] = $now;
            if (in_array('moved_from_id', $dstCols)) $attr['moved_from_id'] = $proyek->getKey();
            if (in_array('moved_at', $dstCols)) $attr['moved_at'] = $now;

            ProyekSelesaii::query()->insert($attr);

            if (in_array(SoftDeletes::class, class_uses_recursive($proyek))) {
                $proyek->forceDelete();
            } else {
                $proyek->delete();
            }
        });

        return redirect()
            ->route('project.proyek.index')
            ->with('success', 'Proyek dipindahkan ke "Proyek Selesai" dan dihapus dari daftar Proyek Berjalan.');
    }

    public function selesaiUpdateKeterangan(Request $request, ProyekSelesaii $proyekselesaii)
    {
        $validated = $request->validate([
            'keterangan' => 'nullable|string',
        ]);

        $proyekselesaii->keterangan = $validated['keterangan'];
        $proyekselesaii->save();

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Keterangan diperbarui.');
    }

    /* ===================== HELPER (LOCAL) ===================== */

    /**
     * Ambil hanya kolom yang valid untuk $table dari array $source.
     * Menghapus id/timestamp agar diisi otomatis oleh Eloquent.
     */
    private function filterColumns(array $source, string $table, array $extra = []): array
    {
        $cols = Schema::getColumnListing($table);
        $data = array_intersect_key($source, array_flip($cols));
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        return array_merge($data, $extra);
    }

    /** Hard delete aman untuk model (support SoftDeletes). */
    private function hardDelete($model): void
    {
        if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $model->forceDelete();
        } else {
            $model->delete();
        }
    }

    /** Ambil baris klien_survei jika id-nya ada di model sumber. */
    private function fetchKlienSurvei($model, string $sourceTable)
    {
        try {
            if (Schema::hasTable('klien_survei') && Schema::hasColumn($sourceTable, 'klienfixsurvei_id')) {
                $id = data_get($model, 'klienfixsurvei_id');
                if ($id) return DB::table('klien_survei')->where('id', $id)->first();
            }
        } catch (\Throwable $e) {}
        return null;
    }

    /** Ambil nilai pertama yang tidak kosong. */
    private function firstNonEmpty(array $values)
    {
        foreach ($values as $v) {
            if (isset($v) && $v !== '' && $v !== []) return $v;
        }
        return null;
    }

    /**
     * Bangun payload kolom W*A*J*I*B dengan fallback: sumber → klien_survei → klien.
     * Termasuk normalisasi tanggal_masuk (fallback created_at).
     */
    private function mapRequiredFields($src, $k = null, $kf = null): array
    {
        $fields = [
            'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
            'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
            'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
            'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
            'gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp','kode_proyek','kelas','status',
            'keterangan','lembar_diskusi','lembar_survei','catatan_survei',
        ];

        $out = [];
        foreach ($fields as $f) {
            $out[$f] = $this->firstNonEmpty([
                data_get($src, $f),
                data_get($kf,  $f),
                data_get($k,   $f),
            ]);
        }

        // Normalisasi default
        if (blank($out['nama']))        $out['nama'] = 'Tanpa Nama';
        if (blank($out['kode_proyek'])) $out['kode_proyek'] = '-';
        if (blank($out['tanggal_masuk'])) $out['tanggal_masuk'] = data_get($src, 'created_at');

        return $out;
    }
}
