<?php

namespace App\Http\Controllers;

use App\Models\Klien;
use App\Models\SurveyRequest;
use App\Models\KlienSurvei;
use App\Models\KlienFixSurvei;
use App\Models\SurveiCancel;

use App\Models\Denah;
use App\Models\DenahCancel;
use App\Models\DelegasiRab;

use App\Models\Exterior;
use App\Models\ExteriorCancel;
use App\Models\Mou;
use App\Models\Mep;
use App\Models\MepCancel;

use App\Models\TahapAkhir;
use App\Models\TahapAkhirCancel;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class StudioController extends Controller
{
    /* ========================== DASHBOARD ========================== */
    /** Halaman dashboard (kirim data awal agar tidak kosong) */
    /** Halaman Dashboard Studio (muat data awal agar tidak kosong) */


    /* ============= KLIEN SURVEI (DAFTAR APPROVED) ============= */
    public function klienSurvei()
    {
        return view('studio.kliensurvei');
    }

    public function klienSurveiData()
    {
        $q = KlienSurvei::query()
            ->leftJoin('kliens as k', 'k.id', '=', 'klien_survei.klien_id')
            ->select([
                'klien_survei.*',
                'k.nama as k_nama',
                'k.kode_proyek as k_kode',
                'k.tanggal_masuk as k_tgl_masuk',
                'k.created_at as k_created',
            ])
            ->orderByDesc('klien_survei.approved_at');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nama', fn($r) => $r->k_nama ?? '-')
            ->addColumn('kode', fn($r) => $r->k_kode ?? '-')
            ->addColumn('tgl_masuk', function ($r) {
                $d = $r->k_tgl_masuk ?: $r->k_created;
                return $d ? Carbon::parse($d)->format('Y-m-d') : '-';
            })
            ->addColumn('status', fn() => '<span class="badge bg-success">Disetujui</span>')
            ->addColumn('aksi', function ($r) {
                $show = route('studio.kliensurvei.show', $r->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-success" title="Lihat"><i class="bi bi-eye"></i></a>';
            })
            ->orderColumn('tgl_masuk', 'COALESCE(k.tanggal_masuk, k.created_at) $1')
            ->rawColumns(['status','aksi'])
            ->make(true);
    }

    public function klienSurveiShow($id)
    {
        $row   = KlienSurvei::with('klien')->findOrFail($id);
        $klien = $row->klien;
        return view('studio.kliensurvei_show', compact('row','klien'));
    }

    /* ================== SURVEI INBOX (PENDING) ================== */
    public function surveiInboxIndex()
    {
        return view('studio.kliensurvei');
    }

    public function surveiInboxData()
    {
        $q = SurveyRequest::query()
            ->leftJoin('kliens', 'kliens.id', '=', 'survey_requests.klien_id')
            ->whereNull('survey_requests.deleted_at')
            ->where('survey_requests.status', 'pending')
            ->select([
                'survey_requests.*',
                'kliens.nama           as k_nama',
                'kliens.kode_proyek    as k_kode',
                'kliens.tanggal_masuk  as k_tanggal_masuk',
                'kliens.created_at     as k_created_at',
                'kliens.lokasi_lahan   as k_lokasi',
            ])
            ->selectRaw('COALESCE(kliens.tanggal_masuk, kliens.created_at) as tgl_sort');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nama', fn ($r) => $r->k_nama ?? '-')
            ->addColumn('kode', fn ($r) => $r->k_kode ?? '-')
            ->editColumn('tgl_masuk', function ($r) {
                $d = $r->k_tanggal_masuk ?: $r->k_created_at;
                return $d ? Carbon::parse($d)->format('Y-m-d') : '-';
            })
            ->orderColumn('tgl_masuk', 'tgl_sort $1')
            ->addColumn('status', fn () => '<span class="badge bg-warning">Pending</span>')
            ->addColumn('aksi', function ($r) {
                $show = route('studio.survei_inbox.show', $r->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-info" title="Lihat"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['status','aksi'])
            ->make(true);
    }

    public function surveiInboxShow(SurveyRequest $req)
    {
        $klien = $req->klien;
        return view('studio.survei_inbox_show', compact('req', 'klien'));
    }

    /** SETUJUI & JADWALKAN dari Inbox */
public function surveiInboxApprove(Request $r, $id)
{
    $r->validate([
        'schedule_at' => ['nullable','date'],
        'tanggal'     => ['nullable','date'],
        'jam'         => ['nullable','date_format:H:i'],
    ]);

    $sr = SurveyRequest::findOrFail($id);
    if ($sr->status !== 'pending') {
        return back()->with('error', 'Request ini sudah diproses.');
    }

    // Bangun waktu jadwal (Asia/Jakarta)
    if ($r->filled('tanggal') && $r->filled('jam')) {
        $scheduleAt = Carbon::createFromFormat('Y-m-d H:i', $r->tanggal.' '.$r->jam, 'Asia/Jakarta');
    } else {
        if (!$r->filled('schedule_at')) {
            return back()->with('error', 'Tanggal/Jam survei wajib diisi.');
        }
        $scheduleAt = Carbon::parse($r->schedule_at, 'Asia/Jakarta');
    }

    return DB::transaction(function () use ($sr, $scheduleAt) {

        $already = SurveyRequest::where('klien_id', $sr->klien_id)
            ->where('status', 'accepted')
            ->exists();
        if ($already) {
            return back()->with('error', 'Klien ini sudah punya jadwal survei yang aktif.');
        }

        // tandai accepted (sementara, sebelum delete)
        $sr->update([
            'status'      => 'accepted',
            'approved_by' => auth()->id(),
            'approved_at' => now('Asia/Jakarta'),
            'schedule_at' => $scheduleAt,
        ]);

        // upsert ke klienfixsurvei
        $kfs = KlienFixSurvei::updateOrCreate(
            ['survey_request_id' => $sr->id],
            [
                'klien_id'        => $sr->klien_id,
                'kode_proyek'     => $sr->kode_proyek ?? optional($sr->klien)->kode_proyek,
                'lokasi_lahan'    => $sr->lokasi_lahan ?? optional($sr->klien)->lokasi_lahan,
                'nama'            => optional($sr->klien)->nama,
                'alamat_tinggal'  => optional($sr->klien)->alamat_tinggal,
                'email'           => optional($sr->klien)->email,
                'no_hp'           => optional($sr->klien)->no_hp,
                'schedule_at'     => $scheduleAt,
                'scheduled_by'    => auth()->id(),
                'status_survei'   => 'Belum diSurvei',
                'survey_done_at'  => null,
            ]
        );

        // safety set default
        DB::table('klienfixsurvei')->where('id', $kfs->id)->update([
            'survey_done_at' => null,
            'status_survei'  => 'Belum diSurvei',
        ]);

        // update status klien
        if ($sr->klien && Schema::hasColumn('kliens','status')) {
            $sr->klien->update(['status' => 'in_survei']);
        }

        // ====== HAPUS SUMBER DARI survey_requests (hard delete) ======
        try {
            // coba hard-delete langsung
            DB::table('survey_requests')->where('id', $sr->id)->delete();
        } catch (\Throwable $e) {
            // jika gagal karena FK, lepaskan relasi lalu coba lagi
            if (Schema::hasColumn('klienfixsurvei','survey_request_id')) {
                DB::table('klienfixsurvei')->where('id', $kfs->id)->update(['survey_request_id' => null]);
            }
            DB::table('survey_requests')->where('id', $sr->id)->delete();
        }

        return back()->with('success', 'Berhasil dijadwalkan & sumber dipindah.');
    });
}

    public function surveiInboxReject(SurveyRequest $req, Request $request)
    {
        if ($req->status !== 'pending') return back()->with('warning', 'Request sudah diproses.');

        $req->update([
            'status'        => 'rejected',
            'rejected_by'   => auth()->id(),
            'rejected_at'   => now(),
            'reject_reason' => $request->input('reason', null),
        ]);

        return back()->with('success', 'Request ditolak.');
    }

    /** Endpoint Marketing kirim ke Inbox */
    public function surveiInboxStore(Klien $klien, Request $request)
    {
        DB::transaction(function () use ($klien, $request) {
            SurveyRequest::firstOrCreate(
                ['klien_id' => $klien->id, 'status' => 'pending'],
                ['sent_by' => $request->user()->id ?? auth()->id(), 'sent_at' => now()]
            );

            if (Schema::hasColumn('kliens','status') && $klien->status !== 'in_survei') {
                $klien->update(['status' => 'in_survei']);
            }
        });

        return response()->json(['ok' => true, 'msg' => 'Klien dikirim ke Survei Inbox']);
    }

    /** SET JADWAL via Ajax */
public function scheduleSurvey(SurveyRequest $req, Request $request)
{
    $request->validate([
        'tanggal' => ['required', 'date'],
        'jam'     => ['required', 'date_format:H:i'],
    ]);

    $scheduleAt = Carbon::createFromFormat('Y-m-d H:i', $request->tanggal.' '.$request->jam, 'Asia/Jakarta');

    $klien = $req->klien;
    if (!$klien) {
        return response()->json(['ok' => false, 'msg' => 'Klien tidak ditemukan'], 404);
    }

    DB::transaction(function () use ($klien, $req, $scheduleAt) {

        $fields = [
            'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang',
            'sertifikat','arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan',
            'referensi','budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang',
            'kendaraan','estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d',
            'rab_boq','gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp',
            'kode_proyek','kelas','keterangan',
        ];
        $payload = $klien->only($fields);

        // copy lembar diskusi (optional)
        $lembarFix = null;
        if ($klien->lembar_diskusi && Storage::disk('public')->exists($klien->lembar_diskusi)) {
            $src    = $klien->lembar_diskusi;
            $dstDir = 'klienfixsurvei/lembar_diskusi';
            $base   = pathinfo($src, PATHINFO_BASENAME);
            $dst    = $dstDir.'/'.$base;
            if (Storage::disk('public')->exists($dst)) {
                $name = pathinfo($base, PATHINFO_FILENAME);
                $ext  = pathinfo($base, PATHINFO_EXTENSION);
                $dst  = $dstDir.'/'.$name.'_'.uniqid().($ext ? '.'.$ext : '');
            }
            Storage::disk('public')->copy($src, $dst);
            $lembarFix = $dst;
        }
        $payload['lembar_diskusi']  = $lembarFix ?? $klien->lembar_diskusi;

        $payload['klien_id']        = $klien->id;
        $payload['schedule_at']     = $scheduleAt;
        $payload['scheduled_by']    = auth()->id();
        $payload['status_survei']   = 'Belum diSurvei';
        $payload['survey_done_at']  = null;

        $kfs = KlienFixSurvei::updateOrCreate(
            ['klien_id' => $klien->id, 'schedule_at' => $scheduleAt],
            $payload
        );

        // safety set default
        DB::table('klienfixsurvei')->where('id', $kfs->id)->update([
            'survey_done_at' => null,
            'status_survei'  => 'Belum diSurvei',
        ]);

        // tandai accepted (sementara)
        $req->update([
            'status'      => 'accepted',
            'approved_by' => auth()->id(),
            'approved_at' => now('Asia/Jakarta'),
            'schedule_at' => $scheduleAt,
        ]);

        if (Schema::hasColumn('kliens','status') && $klien->status !== 'in_survei') {
            $klien->update(['status' => 'in_survei']);
        }

        // ====== HAPUS SUMBER survey_requests (hard delete) ======
        try {
            DB::table('survey_requests')->where('id', $req->id)->delete();
        } catch (\Throwable $e) {
            if (Schema::hasColumn('klienfixsurvei','survey_request_id')) {
                // kalau kamu menyimpan FK ini di skenario lain
                DB::table('klienfixsurvei')
                    ->where('klien_id', $klien->id)
                    ->whereNull('survey_request_id')
                    ->update(['survey_request_id' => null]);
            }
            DB::table('survey_requests')->where('id', $req->id)->delete();
        }
    });

    return response()->json(['ok' => true]);
}


    /* ============ JADWAL SURVEI (KLIEN FIX SURVEI) ============ */
    public function surveiScheduledData()
    {
        $q = KlienFixSurvei::query()
            ->with(['klien:id,nama,kode_proyek,lokasi_lahan'])
            ->select('klienfixsurvei.*')
            ->selectRaw('UNIX_TIMESTAMP(schedule_at) as schedule_ts')
            ->selectRaw("
                CASE
                    WHEN klienfixsurvei.survey_done_at IS NULL
                        THEN '<span class=\"badge bg-secondary\">Belum diSurvei</span>'
                    ELSE '<span class=\"badge bg-success\">Sudah diSurvei</span>'
                END AS status_html
            ");

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nama',   fn($r) => $r->nama ?: optional($r->klien)->nama ?: '-')
            ->addColumn('kode',   fn($r) => $r->kode_proyek ?: optional($r->klien)->kode_proyek ?: '-')
            ->addColumn('lokasi', fn($r) => $r->lokasi_lahan ?: '-')
            ->addColumn('tgl_jadwal', fn($r) =>
                $r->schedule_at ? Carbon::parse($r->schedule_at)->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-'
            )
            ->orderColumn('tgl_jadwal', 'schedule_ts $1')
            ->addColumn('status', fn($r) => $r->status_html)
            ->addColumn('aksi', function ($r) {
                $show = route('studio.survei_scheduled.show', $r->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-info" title="Lihat"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['status','aksi'])
            ->make(true);
    }

    public function surveiScheduledShow(KlienFixSurvei $fix)
    {
        return view('studio.jadwalklien_show', [
            'fix'  => $fix,
            'data' => $fix,
        ]);
    }

    public function markSurveyDone(KlienFixSurvei $fix, Request $request)
    {
        $fix->update([
            'status_survei'  => 'Sudah diSurvei',
            'survey_done_at' => now('Asia/Jakarta'),
        ]);

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Status ditandai: Sudah diSurvei.');
    }

    public function updateScheduledNote(Request $request, KlienFixSurvei $fix)
    {
        $data = $request->validate(['catatan_survei' => ['nullable','string']]);
        $fix->update(['catatan_survei' => $data['catatan_survei'] ?? null]);
        return response()->json(['ok' => true]);
    }

    public function updateSurveySheet(Request $request, KlienFixSurvei $fix)
    {
        $data = $request->validate([
            'lembar_survei' => ['required','file','mimes:pdf','max:10240'],
        ], [
            'lembar_survei.required' => 'File PDF wajib diunggah.',
            'lembar_survei.mimes'    => 'File harus berformat PDF.',
            'lembar_survei.max'      => 'Ukuran maksimum 10 MB.',
        ]);

        if ($fix->lembar_survei && Storage::disk('public')->exists($fix->lembar_survei)) {
            Storage::disk('public')->delete($fix->lembar_survei);
        }

        $path = $request->file('lembar_survei')->store('survei/lembar', 'public');
        $fix->update(['lembar_survei' => $path]);

        return back()->with('success', 'Data Lembar Survei berhasil diunggah.');
    }

    /** BATALKAN JADWAL -> arsip ke survei_cancel */
    public function cancelScheduled(Request $request, KlienFixSurvei $fix)
    {
        $data = $request->validate(['alasan_cancel' => ['nullable','string','max:1000']]);

        SurveiCancel::create([
            'klien_id'           => $fix->klien_id,
            'klienfixsurvei_id'  => $fix->id,
            'nama'               => $fix->nama ?? optional($fix->klien)->nama,
            'alamat_tinggal'     => $fix->alamat_tinggal ?? optional($fix->klien)->alamat_tinggal,
            'lokasi_lahan'       => $fix->lokasi_lahan ?? optional($fix->klien)->lokasi_lahan,
            'alasan_cancel'      => $data['alasan_cancel'] ?? null,
            'canceled_by'        => auth()->id(),
            'canceled_at'        => now(),
        ]);

        $this->hardDelete($fix);

        if (Schema::hasColumn('kliens','status') && $fix->klien) {
            $fix->klien->update(['status' => 'cancel_survei']);
        }

        return $request->wantsJson()
            ? response()->json(['ok' => true])
            : redirect()->route('studio.survei_inbox.index')->with('success', 'Jadwal dibatalkan & sumber dihapus.');
    }

    /* ============= SURVEI CANCEL (LISTING) ============= */
    public function surveiCancelIndex()
    {
        return view('studio.survei.cancel');
    }

    public function surveiCancelData(Request $request)
    {
        $q = SurveiCancel::query()->select([
            'id','nama','alamat_tinggal','lokasi_lahan','alasan_cancel','canceled_at',
        ]);

        $from = $request->input('tanggal_awal_cancel');
        $to   = $request->input('tanggal_akhir_cancel');

        if ($from && $to) {
            $q->whereDate('canceled_at', '>=', $from)
              ->whereDate('canceled_at', '<=', $to);
        } elseif ($from) {
            $q->whereDate('canceled_at', '>=', $from);
        } elseif ($to) {
            $q->whereDate('canceled_at', '<=', $to);
        }

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('tanggal_cancel', function ($r) {
                if ($r->canceled_at instanceof \DateTimeInterface) {
                    return $r->canceled_at->format('d-m-Y H:i');
                }
                return $r->canceled_at ? Carbon::parse($r->canceled_at)->format('d-m-Y H:i') : '-';
            })
            ->editColumn('nama', fn ($r) => $r->nama ?? '-')
            ->editColumn('alamat_tinggal', fn ($r) => $r->alamat_tinggal ?? '-')
            ->editColumn('lokasi_lahan', fn ($r) => $r->lokasi_lahan ?? '-')
            ->editColumn('alasan_cancel', fn ($r) => $r->alasan_cancel ?? '-')
            ->make(true);
    }

    public function surveiCancelShow($id)
    {
        $row = DB::table('survei_cancel')
            ->leftJoin('kliens','kliens.id','=','survei_cancel.klien_id')
            ->select('survei_cancel.*','kliens.nama','kliens.kode_proyek','kliens.lokasi_lahan')
            ->where('survei_cancel.id',$id)
            ->first();

        return view('studio.survei_cancel_show', compact('row'));
    }

    /* ==================== MOVE: Jadwal -> Denah ==================== */
    public function moveToDenah(KlienFixSurvei $fix)
    {
        return DB::transaction(function () use ($fix) {
            $k = $fix->klien;

            $pick = fn (...$vals) => collect($vals)->first(fn($v) => !blank($v));

            $all = [
                'klien_id'          => $pick($fix->klien_id, optional($k)->id),
                'klienfixsurvei_id' => $fix->id,
                'nama'              => $pick($fix->nama,           optional($k)->nama),
                'email'             => $pick($fix->email,          optional($k)->email),
                'no_hp'             => $pick($fix->no_hp,          optional($k)->no_hp),
                'alamat_tinggal'    => $pick($fix->alamat_tinggal, optional($k)->alamat_tinggal),
                'kode_proyek'       => $pick($fix->kode_proyek,    optional($k)->kode_proyek),
                'kelas'             => $pick($fix->kelas,          optional($k)->kelas),
                'lokasi_lahan'      => $pick($fix->lokasi_lahan,   optional($k)->lokasi_lahan),
                'luas_lahan'        => $pick($fix->luas_lahan,     optional($k)->luas_lahan),
                'luas_bangunan'     => $pick($fix->luas_bangunan,  optional($k)->luas_bangunan),
                'kebutuhan_ruang'   => $pick($fix->kebutuhan_ruang, optional($k)->kebutuhan_ruang),
                'arah_mata_angin'   => $pick($fix->arah_mata_angin, optional($k)->arah_mata_angin),
                'batas_keliling'    => $pick($fix->batas_keliling,  optional($k)->batas_keliling),
                'konsep_bangunan'   => $pick($fix->konsep_bangunan, optional($k)->konsep_bangunan),
                'foto_eksisting'    => $pick($fix->foto_eksisting,  optional($k)->foto_eksisting),
                'referensi'         => $pick($fix->referensi,       optional($k)->referensi),
                'budget'            => $pick($fix->budget,          optional($k)->budget),
                'lembar_diskusi'    => $pick($fix->lembar_diskusi,  optional($k)->lembar_diskusi),
                'layout'            => $pick($fix->layout,          optional($k)->layout),
                'desain_3d'         => $pick($fix->desain_3d,       optional($k)->desain_3d),
                'rab_boq'           => $pick($fix->rab_boq,         optional($k)->rab_boq),
                'lembar_survei'     => $pick($fix->lembar_survei,   optional($k)->lembar_survei),
                'gambar_kerja'      => $pick($fix->gambar_kerja,    optional($k)->gambar_kerja),
                'sertifikat'        => $pick($fix->sertifikat,      optional($k)->sertifikat),

                'hoby'              => $pick($fix->hoby,            optional($k)->hoby),
                'aktivitas'         => $pick($fix->aktivitas,       optional($k)->aktivitas),
                'prioritas_ruang'   => $pick($fix->prioritas_ruang, optional($k)->prioritas_ruang),
                'biaya_survei'      => $pick($fix->biaya_survei ?? $fix->biaya_survey, optional($k)->biaya_survei ?? optional($k)->biaya_survey),
                'estimasi_start'    => $pick($fix->estimasi_start,  optional($k)->estimasi_start),
                'kendaraan'         => $pick($fix->kendaraan,       optional($k)->kendaraan),
                'target_user_kos'   => $pick($fix->target_user_kos, optional($k)->target_user_kos),
                'fasilitas_kos'     => $pick($fix->fasilitas_kos,   optional($k)->fasilitas_kos),
                'tanggal_masuk'     => $pick($fix->tanggal_masuk,   optional($k)->tanggal_masuk, $fix->created_at),
                'share_lokasi'      => $pick($fix->share_lokasi,    optional($k)->share_lokasi),

                'catatan_survei'    => $pick($fix->catatan_survei, $fix->catatan, optional($k)->catatan_survei, optional($k)->catatan),
                'status_denah'      => 'draft',
            ];

            $columns = Schema::getColumnListing('denahs');
            $payload = array_intersect_key($all, array_flip($columns));

            Denah::create($payload);

            $this->hardDelete($fix);

            if ($fix->klien_id && Schema::hasColumn('kliens','status')) {
                DB::table('kliens')->where('id', $fix->klien_id)->update(['status' => 'denah_moodboard']);
            }

            return redirect()->route('studio.kliensurvei')->with('success', 'Data dipindah ke Denah & dihapus dari jadwal.');
        });
    }

    /* ====================== DENAH LIST & SHOW ====================== */
    public function denahMoodboard()
    {
        return view('studio.denah.denah_moodboard');
    }

    public function denahData()
    {
        $q = Denah::query()->with('klien')
            ->select('denahs.*')
            ->selectRaw('UNIX_TIMESTAMP(created_at) as created_ts');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nama', fn($r) => $r->nama ?? optional($r->klien)->nama ?? '-')
            ->addColumn('kode', fn($r) => $r->kode_proyek ?? optional($r->klien)->kode_proyek ?? '-')
            ->addColumn('lokasi', fn($r) => $r->lokasi_lahan ?? optional($r->klien)->lokasi_lahan ?? '-')
            ->editColumn('created_fmt', fn($r) =>
                $r->created_at ? $r->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-'
            )
            ->orderColumn('created_fmt', 'created_ts $1')
            ->addColumn('aksi', function($r){
                $show = route('studio.denah.show', $r->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-success" title="View"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function denahShow(Denah $denah)
    {
        $denah->load('klien');
        return view('studio.denah.denah_show', ['denah' => $denah]);
    }

    public function denahCancelData(Request $request)
    {
        $q = DenahCancel::query()
            ->select(['id','nama','alamat_tinggal','lokasi_lahan','alasan_cancel','canceled_at']);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('tanggal_cancel', fn($r) => $r->canceled_at ? Carbon::parse($r->canceled_at)->format('d-m-Y H:i') : '-')
            ->editColumn('alamat_tinggal', fn($r) => $r->alamat_tinggal ?: '-')
            ->editColumn('lokasi_lahan',  fn($r) => $r->lokasi_lahan   ?: '-')
            ->editColumn('alasan_cancel', fn($r) => $r->alasan_cancel  ?: '-')
            ->make(true);
    }

    public function cancelDenah(Request $request, Denah $denah)
    {
        $data = $request->validate(['alasan_cancel' => 'nullable|string|max:1000']);

        return DB::transaction(function () use ($denah, $data) {
            DenahCancel::create([
                'klien_id'       => $denah->klien_id,
                'nama'           => $denah->nama,
                'alamat_tinggal' => $denah->alamat_tinggal,
                'lokasi_lahan'   => $denah->lokasi_lahan,
                'alasan_cancel'  => $data['alasan_cancel'] ?? null,
                'canceled_by'    => auth()->id(),
                'canceled_at'    => now(),
            ]);

            $this->hardDelete($denah);

            if ($denah->klien && Schema::hasColumn('kliens','status')) {
                $denah->klien->update(['status' => 'cancel_denah']);
            }

            return redirect()->route('studio.denah')->with('success', 'Dipindah ke Cancel Denah & sumber dihapus.');
        });
    }

    public function denahCancelShow($id)
    {
        $row = DenahCancel::findOrFail($id);
        return view('studio.denah.denah_cancel_show', compact('row'));
    }

    /* ============== MOVE: Denah -> 3D Exterior & Interior ============== */
    public function moveDenahToExterior(Denah $denah)
    {
        return DB::transaction(function () use ($denah) {
            $k   = $denah->klien;
            $pick = fn (...$v) => collect($v)->first(fn($x) => !blank($x));

            $all = [
                'klien_id'           => $pick($denah->klien_id, optional($k)->id),
                'klienfixsurvei_id'  => $denah->klienfixsurvei_id,
                'denah_id'           => $denah->id,

                'nama'               => $pick($denah->nama, optional($k)->nama) ?: 'Tanpa Nama',
                'email'              => $pick($denah->email, optional($k)->email),
                'no_hp'              => $pick($denah->no_hp, optional($k)->no_hp),
                'alamat_tinggal'     => $pick($denah->alamat_tinggal, optional($k)->alamat_tinggal),

                'kode_proyek'        => $pick($denah->kode_proyek, optional($k)->kode_proyek) ?: '-',
                'kelas'              => $pick($denah->kelas, optional($k)->kelas),

                'lokasi_lahan'       => $pick($denah->lokasi_lahan, optional($k)->lokasi_lahan),
                'luas_lahan'         => $pick($denah->luas_lahan,  optional($k)->luas_lahan),
                'luas_bangunan'      => $pick($denah->luas_bangunan, optional($k)->luas_bangunan),
                'kebutuhan_ruang'    => $pick($denah->kebutuhan_ruang, optional($k)->kebutuhan_ruang),

                'sertifikat'         => $pick($denah->sertifikat, optional($k)->sertifikat),
                'arah_mata_angin'    => $pick($denah->arah_mata_angin, optional($k)->arah_mata_angin),
                'batas_keliling'     => $pick($denah->batas_keliling, optional($k)->batas_keliling),
                'foto_eksisting'     => $pick($denah->foto_eksisting, optional($k)->foto_eksisting),

                'konsep_bangunan'    => $pick($denah->konsep_bangunan, optional($k)->konsep_bangunan),
                'referensi'          => $pick($denah->referensi, optional($k)->referensi),

                'budget'             => $pick($denah->budget, optional($k)->budget),
                'share_lokasi'       => $pick($denah->share_lokasi, optional($k)->share_lokasi),
                'biaya_survei'       => $pick($denah->biaya_survei ?? $denah->biaya_survey, optional($k)->biaya_survei ?? optional($k)->biaya_survey),

                'hoby'               => $pick($denah->hoby, optional($k)->hoby),
                'aktivitas'          => $pick($denah->aktivitas, optional($k)->aktivitas),
                'prioritas_ruang'    => $pick($denah->prioritas_ruang, optional($k)->prioritas_ruang),
                'kendaraan'          => $pick($denah->kendaraan, optional($k)->kendaraan),
                'estimasi_start'     => $pick($denah->estimasi_start, optional($k)->estimasi_start),
                'target_user_kos'    => $pick($denah->target_user_kos, optional($k)->target_user_kos),
                'fasilitas_kos'      => $pick($denah->fasilitas_kos, optional($k)->fasilitas_kos),

                'layout'             => $pick($denah->layout, optional($k)->layout),
                'desain_3d'          => $pick($denah->desain_3d, optional($k)->desain_3d),
                'rab_boq'            => $pick($denah->rab_boq, optional($k)->rab_boq),
                'gambar_kerja'       => $pick($denah->gambar_kerja, optional($k)->gambar_kerja),
                'lembar_diskusi'     => $pick($denah->lembar_diskusi, optional($k)->lembar_diskusi),
                'lembar_survei'      => $pick($denah->lembar_survei, optional($k)->lembar_survei),
                'catatan_survei'     => $pick($denah->catatan_survei, optional($k)->catatan_survei),

                'tanggal_masuk'      => $pick($denah->tanggal_masuk, optional($k)->tanggal_masuk, $denah->created_at),
                'status_exterior'    => 'draft',
            ];

            $cols    = Schema::getColumnListing('exteriors');
            $payload = array_intersect_key($all, array_flip($cols));

            Exterior::create($payload);

            $this->hardDelete($denah);

            if ($denah->klien && Schema::hasColumn('kliens', 'status')) {
                $denah->klien->update(['status' => 'in_3d_ext_int']);
            }

            return redirect()->route('studio.denah')->with('success', 'Dipindah ke 3D Exterior & Interior.');
        });
    }

    // ================= EXTERIORS =================
    public function exteriorsIndex()
    {
        return view('studio.exteriors.index');
    }

public function exteriorsData()
{
    $q = Exterior::query()
        ->select('exteriors.*')
        ->selectRaw('UNIX_TIMESTAMP(created_at) as created_ts');

    return DataTables::of($q)
        ->addIndexColumn()
        ->addColumn('nama',   fn($r) => $r->nama ?? '-')
        ->addColumn('kode',   fn($r) => $r->kode_proyek ?? '-')
        ->addColumn('lokasi', fn($r) => $r->lokasi_lahan ?? '-')
        ->addColumn('created_fmt', fn($r) => $r->created_at
            ? $r->created_at->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-')
        ->orderColumn('created_fmt', 'created_ts $1')
        ->addColumn('aksi', function ($r) {
            $show = route('studio.exteriors.show', $r->id);
            $edit = route('studio.exteriors.edit', $r->id);
            return '
            <div class="d-inline-flex align-items-center gap-2">
                <a href="'.$edit.'" class="btn btn-sm btn-warning btn-edit-exterior" title="Edit">
                <i class="bi bi-pencil-square"></i>
                </a>
                <a href="'.$show.'" class="btn btn-sm btn-success" title="Lihat">
                <i class="bi bi-eye"></i>
                </a>
            </div>
            ';
        })
        ->rawColumns(['aksi'])
        ->make(true);
}


    public function exteriorsCancelData()
    {
        $q = ExteriorCancel::query()
            ->select(['id','nama','alamat_tinggal','lokasi_lahan','alasan_cancel','canceled_at']);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('tanggal_cancel', fn($r) =>
                $r->canceled_at ? Carbon::parse($r->canceled_at)->format('d-m-Y H:i') : '-'
            )
            ->editColumn('alamat_tinggal', fn($r) => $r->alamat_tinggal ?: '-')
            ->editColumn('lokasi_lahan',  fn($r) => $r->lokasi_lahan   ?: '-')
            ->editColumn('alasan_cancel', fn($r) => $r->alasan_cancel  ?: '-')
            ->make(true);
    }

    public function cancelExterior($id)
    {
        $exterior = Exterior::findOrFail($id);

        // simpan hanya kolom yang ada di tabel cancels
        $cols = Schema::getColumnListing('exterior_cancels');
        $payload = array_intersect_key($exterior->toArray(), array_flip($cols));
        ExteriorCancel::create($payload);

        $this->hardDelete($exterior);

        return redirect()->route('studio.exteriors.index')->with('success', 'Dipindah ke Cancel Exterior & sumber dihapus.');
    }

    public function exteriorsShow($id)
    {
        $exterior = \App\Models\Exterior::with('klien')->findOrFail($id);
        return view('studio.exteriors.show', compact('exterior'));
    }

    // ====== Halaman edit ======
    public function exteriorEdit(Exterior $exterior)
    {
        // jika butuh relasi lain, load di sini (mis. $exterior->load('klien'))
    return view('studio.exteriors.edit', compact('exterior')); // ⬅️ exteriors (plural)
    }

    // ====== Update data ======
public function exteriorUpdate(Request $r, Exterior $exterior)
{
    // Normalisasi kode proyek ke uppercase
    if ($r->filled('kode_proyek')) {
        $r->merge(['kode_proyek' => strtoupper($r->kode_proyek)]);
    }

    // Field text (sesuaikan dengan kolom tabelmu)
    $textFields = [
        'nama','email','no_hp','alamat_tinggal','lokasi_lahan',
        'luas_lahan','luas_bangunan','kebutuhan_ruang','konsep_bangunan',
        'arah_mata_angin','batas_keliling','kode_proyek','kelas','keterangan',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas',
        'prioritas_ruang','target_user_kos','fasilitas_kos','kendaraan',
    ];

    // Field tanggal
    $dateFields = ['tanggal_masuk','estimasi_start'];

    // File fields
    $fileFields = [
        'lembar_diskusi' => 'image',
        'referensi'      => 'image',
        'sertifikat'     => 'image',
        'foto_eksisting' => 'image',
        'layout'         => 'mimes:pdf',
        'desain_3d'      => 'mimes:pdf',
        'rab_boq'        => 'mimes:pdf',
        'gambar_kerja'   => 'mimes:pdf',
        'lembar_survei'  => 'mimes:pdf',
    ];

    // ===== Validasi =====
    $rules = [
        'nama'            => ['nullable','string','max:255'],
        'email'           => ['nullable','string','max:255'],
        'no_hp'           => ['nullable','string','max:50'],
        'alamat_tinggal'  => ['nullable','string'],
        'lokasi_lahan'    => ['nullable','string'],
        'luas_lahan'      => ['nullable','string'],
        'luas_bangunan'   => ['nullable','string'],
        'kebutuhan_ruang' => ['nullable','string'],
        'konsep_bangunan' => ['nullable','string'],
        'arah_mata_angin' => ['nullable','string'],
        'batas_keliling'  => ['nullable','string'],
        'budget'          => ['nullable'], // fleksibel
        'share_lokasi'    => ['nullable','string'],
        'biaya_survei'    => ['nullable','string'],
        'hoby'            => ['nullable','string'],
        'aktivitas'       => ['nullable','string'],
        'prioritas_ruang' => ['nullable','string'],
        'target_user_kos' => ['nullable','string'],
        'fasilitas_kos'   => ['nullable','string'],
        'kendaraan'       => ['nullable','string'],

        // Select/datalist
        'kode_proyek'     => ['nullable','string','in:BA,RE,DE,IN'],
        'kelas'           => ['nullable','string','in:A,B,C,D'],
        'keterangan'      => ['nullable','string','in:Survei,Perlu FollowUp,Penawaran RAB,Budget tidak cukup,Diskusi Keluarga,Belum Siap,Parsial'],

        // Tanggal
        'tanggal_masuk'   => ['nullable','date'],
        'estimasi_start'  => ['nullable','date'],
    ];

    foreach ($fileFields as $name => $rule) {
        $rules[$name] = ['nullable',$rule];
    }

    $data = $r->validate($rules);

    // ===== Simpan text =====
    $exterior->fill($r->only($textFields));

    // ===== Simpan tanggal (format yyyy-mm-dd) =====
    foreach ($dateFields as $d) {
        if ($r->filled($d)) {
            $exterior->{$d} = \Carbon\Carbon::parse($r->input($d))->format('Y-m-d');
        } else {
            // jika kolom nullable
            $exterior->{$d} = null;
        }
    }

    // ===== Simpan file ke storage/app/public/exteriors/... =====
    foreach ($fileFields as $name => $rule) {
        if ($r->hasFile($name)) {
            $path = $r->file($name)->store('exteriors', 'public');
            $exterior->{$name} = $path;
        }
    }

    $exterior->save();

    return redirect()->route('studio.exteriors.index')
        ->with('success', 'Data exterior berhasil diperbarui.');
}



    /* ============== MOVE: 3D Exterior & Interior -> MEP & Spek ============== */
    public function moveExteriorToMep(Exterior $exterior)
    {
        return DB::transaction(function () use ($exterior) {
            $k = $exterior->klien;
            $pick = fn (...$v) => collect($v)->first(fn($x) => !blank($x));

            $all = [
                'klien_id'           => $pick($exterior->klien_id, optional($k)->id),
                'klienfixsurvei_id'  => $exterior->klienfixsurvei_id,
                'exterior_id'        => $exterior->id,

                'nama'               => $pick($exterior->nama, optional($k)->nama) ?: 'Tanpa Nama',
                'email'              => $pick($exterior->email, optional($k)->email),
                'no_hp'              => $pick($exterior->no_hp, optional($k)->no_hp),
                'alamat_tinggal'     => $pick($exterior->alamat_tinggal, optional($k)->alamat_tinggal),

                'kode_proyek'        => $pick($exterior->kode_proyek, optional($k)->kode_proyek) ?: '-',
                'kelas'              => $pick($exterior->kelas, optional($k)->kelas),

                'lokasi_lahan'       => $pick($exterior->lokasi_lahan, optional($k)->lokasi_lahan),
                'luas_lahan'         => $pick($exterior->luas_lahan,  optional($k)->luas_lahan),
                'luas_bangunan'      => $pick($exterior->luas_bangunan, optional($k)->luas_bangunan),
                'kebutuhan_ruang'    => $pick($exterior->kebutuhan_ruang, optional($k)->kebutuhan_ruang),

                'sertifikat'         => $pick($exterior->sertifikat, optional($k)->sertifikat),
                'arah_mata_angin'    => $pick($exterior->arah_mata_angin, optional($k)->arah_mata_angin),
                'batas_keliling'     => $pick($exterior->batas_keliling, optional($k)->batas_keliling),
                'foto_eksisting'     => $pick($exterior->foto_eksisting, optional($k)->foto_eksisting),

                'konsep_bangunan'    => $pick($exterior->konsep_bangunan, optional($k)->konsep_bangunan),
                'referensi'          => $pick($exterior->referensi, optional($k)->referensi),

                'budget'             => $pick($exterior->budget, optional($k)->budget),
                'share_lokasi'       => $pick($exterior->share_lokasi, optional($k)->share_lokasi),
                'biaya_survei'       => $pick($exterior->biaya_survei ?? $exterior->biaya_survey, optional($k)->biaya_survei ?? optional($k)->biaya_survey),

                'hoby'               => $pick($exterior->hoby, optional($k)->hoby),
                'aktivitas'          => $pick($exterior->aktivitas, optional($k)->aktivitas),
                'prioritas_ruang'    => $pick($exterior->prioritas_ruang, optional($k)->prioritas_ruang),
                'kendaraan'          => $pick($exterior->kendaraan, optional($k)->kendaraan),
                'estimasi_start'     => $pick($exterior->estimasi_start, optional($k)->estimasi_start),
                'target_user_kos'    => $pick($exterior->target_user_kos, optional($k)->target_user_kos),
                'fasilitas_kos'      => $pick($exterior->fasilitas_kos, optional($k)->fasilitas_kos),

                'layout'             => $pick($exterior->layout, optional($k)->layout),
                'desain_3d'          => $pick($exterior->desain_3d, optional($k)->desain_3d),
                'rab_boq'            => $pick($exterior->rab_boq, optional($k)->rab_boq),
                'gambar_kerja'       => $pick($exterior->gambar_kerja, optional($k)->gambar_kerja),
                'lembar_diskusi'     => $pick($exterior->lembar_diskusi, optional($k)->lembar_diskusi),
                'lembar_survei'      => $pick($exterior->lembar_survei, optional($k)->lembar_survei),
                'catatan_survei'     => $pick($exterior->catatan_survei, optional($k)->catatan_survei),

                'tanggal_masuk'      => $pick($exterior->tanggal_masuk, optional($k)->tanggal_masuk, $exterior->created_at),
                'status_mep'         => 'delegasi_rab',
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            $cols    = Schema::getColumnListing('meps');
            $payload = array_intersect_key($all, array_flip($cols));

            Mep::create($payload);

            $this->hardDelete($exterior);

            if ($exterior->klien && Schema::hasColumn('kliens', 'status')) {
                $exterior->klien->update(['status' => 'in_mep']);
            }

            return redirect()->route('studio.exteriors.index')->with('success','Dipindah ke MEP & Spek Material.');
        });
    }

    /* ====================== MEP (LIST & SHOW) ====================== */
    public function mepIndex()
    {
        return view('studio.mep.index');
    }

    public function mepData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $q = Mep::query()
            ->select('meps.*')
            ->selectRaw('UNIX_TIMESTAMP(COALESCE(created_at, updated_at)) as created_ts');

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('nama',       fn ($r) => $r->nama ?? '-')
            ->addColumn('kode',       fn ($r) => $r->kode_proyek ?? '-')
            ->addColumn('lokasi',     fn ($r) => $r->lokasi_lahan ?? '-')
            ->addColumn('created_fmt', function ($r) {
                $d = $r->created_at ?: $r->updated_at;
                return $d ? Carbon::parse($d)->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
            })
            ->orderColumn('created_fmt', 'created_ts $1')
            ->addColumn('aksi', function ($r) {
                $show = route('studio.mep.show', $r->id);
                return '<a href="'.$show.'" class="btn btn-sm btn-success" title="Lihat"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function cancelMep(Request $request, Mep $mep)
    {
        DB::transaction(function () use ($request, $mep) {
            DB::table('mep_cancels')->insert([
                'mep_id'         => $mep->id,
                'klien_id'       => $mep->klien_id,
                'nama'           => $mep->nama ?? optional($mep->klien)->nama,
                'alamat_tinggal' => $mep->alamat_tinggal ?? optional($mep->klien)->alamat_tinggal,
                'lokasi_lahan'   => $mep->lokasi_lahan ?? optional($mep->klien)->lokasi_lahan,
                'alasan_cancel'  => $request->input('alasan_cancel'),
                'canceled_by'    => auth()->id(),
                'canceled_at'    => now(),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $this->hardDelete($mep);

            if ($mep->klien_id && Schema::hasColumn('kliens','status')) {
                DB::table('kliens')->where('id', $mep->klien_id)->update(['status' => 'cancel_in_mep']);
            }
        });

        return redirect()->route('studio.mep')->with('success', 'Dipindah ke Cancel MEP & sumber dihapus.');
    }

    public function mepCancelData(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $q = DB::table('mep_cancels as mc')->select([
            'mc.id','mc.nama',
            DB::raw('COALESCE(mc.canceled_at, mc.created_at) as tgl_cancel'),
            'mc.alamat_tinggal','mc.lokasi_lahan','mc.alasan_cancel',
        ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('tgl_cancel', fn($r) => $r->tgl_cancel ? Carbon::parse($r->tgl_cancel)->format('d-m-Y H:i') : '-')
            ->toJson();
    }

    public function mepShow(Mep $mep)
    {
        $mep->load('klien');
        return view('studio.mep.show', compact('mep'));
    }

    public function moveMepToDelegasi(Mep $mep)
    {
        return DB::transaction(function () use ($mep) {

            $k = $mep->klien ?? null;

            $pick = fn (...$vals) => collect($vals)->first(fn($v) => !blank($v));

            $all = [
                'klien_id'          => $pick($mep->klien_id, optional($k)->id),
                'klienfixsurvei_id' => $mep->klienfixsurvei_id,

                'nama'              => $pick($mep->nama, optional($k)->nama) ?? 'Tanpa Nama',
                'email'             => $pick($mep->email, optional($k)->email),
                'no_hp'             => $pick($mep->no_hp, optional($k)->no_hp),
                'alamat_tinggal'    => $pick($mep->alamat_tinggal, optional($k)->alamat_tinggal),

                'kode_proyek'       => $pick($mep->kode_proyek, optional($k)->kode_proyek) ?? '-',
                'kelas'             => $pick($mep->kelas, optional($k)->kelas),

                'lokasi_lahan'      => $pick($mep->lokasi_lahan, optional($k)->lokasi_lahan),
                'luas_lahan'        => $pick($mep->luas_lahan,  optional($k)->luas_lahan),
                'luas_bangunan'     => $pick($mep->luas_bangunan, optional($k)->luas_bangunan),
                'kebutuhan_ruang'   => $pick($mep->kebutuhan_ruang, optional($k)->kebutuhan_ruang),

                'sertifikat'        => $pick($mep->sertifikat, optional($k)->sertifikat),
                'arah_mata_angin'   => $pick($mep->arah_mata_angin, optional($k)->arah_mata_angin),
                'batas_keliling'    => $pick($mep->batas_keliling, optional($k)->batas_keliling),
                'foto_eksisting'    => $pick($mep->foto_eksisting, optional($k)->foto_eksisting),

                'konsep_bangunan'   => $pick($mep->konsep_bangunan, optional($k)->konsep_bangunan),
                'referensi'         => $pick($mep->referensi, optional($k)->referensi),

                'budget'            => $pick($mep->budget, optional($k)->budget),
                'share_lokasi'      => $pick($mep->share_lokasi, optional($k)->share_lokasi),
                'biaya_survei'      => $pick($mep->biaya_survei ?? $mep->biaya_survey, optional($k)->biaya_survei ?? optional($k)->biaya_survey),

                'hoby'              => $pick($mep->hoby,             optional($k)->hoby),
                'aktivitas'         => $pick($mep->aktivitas,        optional($k)->aktivitas),
                'prioritas_ruang'   => $pick($mep->prioritas_ruang,  optional($k)->prioritas_ruang),
                'kendaraan'         => $pick($mep->kendaraan,        optional($k)->kendaraan),
                'estimasi_start'    => $pick($mep->estimasi_start,   optional($k)->estimasi_start),
                'target_user_kos'   => $pick($mep->target_user_kos,  optional($k)->target_user_kos),
                'fasilitas_kos'     => $pick($mep->fasilitas_kos,    optional($k)->fasilitas_kos),

                'layout'            => $pick($mep->layout,     optional($k)->layout),
                'desain_3d'         => $pick($mep->desain_3d,  optional($k)->desain_3d),
                'rab_boq'           => $pick($mep->rab_boq,    optional($k)->rab_boq),
                'gambar_kerja'      => $pick($mep->gambar_kerja,optional($k)->gambar_kerja),
                'sertifikat'        => $pick($mep->sertifikat, optional($k)->sertifikat),
                'lembar_diskusi'    => $pick($mep->lembar_diskusi, optional($k)->lembar_diskusi),
                'lembar_survei'     => $pick($mep->lembar_survei,  optional($k)->lembar_survei),
                'catatan_survei'    => $pick($mep->catatan_survei, optional($k)->catatan_survei),

                'tanggal_masuk'     => $pick($mep->tanggal_masuk, optional($k)->tanggal_masuk, $mep->created_at),

                'status_mep'        => 'delegasi_rab',
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            $tbl     = (new DelegasiRab)->getTable(); // biasanya 'delegasirab'
            $cols    = Schema::getColumnListing($tbl);
            $payload = array_intersect_key($all, array_flip($cols));

            DB::table($tbl)->insert($payload);

            try { $mep->forceDelete(); } catch (\Throwable $e) { $mep->delete(); }

            if ($mep->klien_id && Schema::hasColumn('kliens', 'status')) {
                DB::table('kliens')->where('id', $mep->klien_id)->update(['status' => 'in_delegasirab']);
            }

            return redirect()->route('studio.delegasirab.index')->with('success', 'Data dipindah ke Delegasi RAB.');
        });
    }

public function delegasiRabLanjut(DelegasiRab $delegasiRab)
{
    return DB::transaction(function () use ($delegasiRab) {

        $k    = $delegasiRab->klien; // relasi bisa null
        $pick = fn (...$v) => collect($v)->first(fn($x) => !blank($x));

        // Pakai tanggal_masuk jika ada, fallback created_at delegasi, terakhir now()
        $tglMasuk = $delegasiRab->tanggal_masuk
            ?: ($delegasiRab->created_at instanceof \Illuminate\Support\Carbon
                ? $delegasiRab->created_at->copy()
                : now());

        $all = [
            'klien_id'          => $pick($delegasiRab->klien_id, optional($k)->id),
            'klienfixsurvei_id' => $delegasiRab->klienfixsurvei_id,

            'nama'              => $pick($delegasiRab->nama, optional($k)->nama) ?: 'Tanpa Nama',
            'email'             => $pick($delegasiRab->email, optional($k)->email),
            'no_hp'             => $pick($delegasiRab->no_hp, optional($k)->no_hp),
            'alamat_tinggal'    => $pick($delegasiRab->alamat_tinggal, optional($k)->alamat_tinggal),

            'kode_proyek'       => $pick($delegasiRab->kode_proyek, optional($k)->kode_proyek) ?: '-',
            'kelas'             => $pick($delegasiRab->kelas, optional($k)->kelas),
            'lokasi_lahan'      => $pick($delegasiRab->lokasi_lahan, optional($k)->lokasi_lahan),

            'luas_lahan'        => $delegasiRab->luas_lahan,
            'luas_bangunan'     => $delegasiRab->luas_bangunan,
            'kebutuhan_ruang'   => $delegasiRab->kebutuhan_ruang,

            'sertifikat'        => $delegasiRab->sertifikat,
            'arah_mata_angin'   => $delegasiRab->arah_mata_angin,
            'batas_keliling'    => $delegasiRab->batas_keliling,
            'foto_eksisting'    => $delegasiRab->foto_eksisting,

            'konsep_bangunan'   => $delegasiRab->konsep_bangunan,
            'referensi'         => $delegasiRab->referensi,
            'budget'            => $delegasiRab->budget,
            'share_lokasi'      => $delegasiRab->share_lokasi,
            'biaya_survei'      => $delegasiRab->biaya_survei,

            'hoby'              => $delegasiRab->hoby,
            'aktivitas'         => $delegasiRab->aktivitas,
            'prioritas_ruang'   => $delegasiRab->prioritas_ruang,
            'kendaraan'         => $delegasiRab->kendaraan,
            'estimasi_start'    => $delegasiRab->estimasi_start,
            'target_user_kos'   => $delegasiRab->target_user_kos,
            'fasilitas_kos'     => $delegasiRab->fasilitas_kos,

            'layout'            => $delegasiRab->layout,
            'desain_3d'         => $delegasiRab->desain_3d,
            'rab_boq'           => $delegasiRab->rab_boq,
            'gambar_kerja'      => $delegasiRab->gambar_kerja,
            'lembar_diskusi'    => $delegasiRab->lembar_diskusi,
            'lembar_survei'     => $delegasiRab->lembar_survei,
            'catatan_survei'    => $delegasiRab->catatan_survei,

            'tanggal_masuk'     => $tglMasuk,

            'status_struktur'   => 'draft',
            'created_at'        => $tglMasuk,   // supaya urutan konsisten
            'updated_at'        => now(),
        ];

        $tbl     = 'struktur_3ds';
        $cols    = Schema::getColumnListing($tbl);
        $payload = array_intersect_key($all, array_flip($cols));
        if (empty($payload['nama'])) {
            $payload['nama'] = 'Tanpa Nama';
        }

        // Normalisasi kode_proyek: jangan pakai '-' sebagai key upsert
        $kode = isset($payload['kode_proyek']) ? trim((string)$payload['kode_proyek']) : '';
        $isKodeValid = $kode !== '' && $kode !== '-' && $kode !== '—';
        $payload['kode_proyek'] = $isKodeValid ? $kode : null;

        // Prioritas dedup: 1) klienfixsurvei_id  2) kode_proyek  3) insert baru
        if (!empty($payload['klienfixsurvei_id'])) {
            DB::table($tbl)->updateOrInsert(
                ['klienfixsurvei_id' => $payload['klienfixsurvei_id']],
                $payload
            );
        } elseif ($isKodeValid) {
            DB::table($tbl)->updateOrInsert(
                ['kode_proyek' => $payload['kode_proyek']],
                $payload
            );
        } else {
            DB::table($tbl)->insert($payload);
        }

        // bersihkan sumber
        try { $delegasiRab->forceDelete(); } catch (\Throwable $e) { $delegasiRab->delete(); }

        // update status klien
        if ($delegasiRab->klien_id && Schema::hasColumn('kliens', 'status')) {
            DB::table('kliens')
                ->where('id', $delegasiRab->klien_id)
                ->update(['status' => 'in_struktur']);
        }

        return redirect()
            ->route('studio.delegasirab.index')
            ->with('success', 'Data berhasil dipindahkan ke 3D Struktur.');
    });
}



    // Tahap Akhir
    public function tahapAkhirIndex()
    {
        return view('studio.tahap_akhir.index');
    }

public function tahapAkhirData(Request $request)
{
    abort_unless($request->ajax(), 404);

    $hasTanggalMasuk = Schema::hasColumn('tahap_akhirs', 'tanggal_masuk');
    $hasSerterAt     = Schema::hasColumn('tahap_akhirs', 'serter_at');
    $hasStatusAkhir  = Schema::hasColumn('tahap_akhirs', 'status_akhir');
    $hasStatus       = Schema::hasColumn('tahap_akhirs', 'status');

    // ===== Ekspresi status yang konsisten =====
    // Prioritas:
    // 1) serter_at terisi  -> "Sudah Serter"
    // 2) status_akhir      -> nilai kolom
    // 3) status            -> nilai kolom
    // 4) default           -> "Belum Serter"
    if ($hasSerterAt) {
        $statusExpr = "CASE
            WHEN tahap_akhirs.serter_at IS NOT NULL THEN 'Sudah Serter'
            " . ($hasStatusAkhir ? "WHEN NULLIF(tahap_akhirs.status_akhir,'') IS NOT NULL THEN tahap_akhirs.status_akhir" : "") . "
            " . ($hasStatus ? "WHEN NULLIF(tahap_akhirs.status,'') IS NOT NULL THEN tahap_akhirs.status" : "") . "
            ELSE 'Belum Serter'
        END";
    } else {
        if ($hasStatusAkhir && $hasStatus) {
            $statusExpr = "CASE
                WHEN NULLIF(tahap_akhirs.status_akhir,'') IS NOT NULL THEN tahap_akhirs.status_akhir
                WHEN NULLIF(tahap_akhirs.status,'')       IS NOT NULL THEN tahap_akhirs.status
                ELSE 'Belum Serter'
            END";
        } elseif ($hasStatusAkhir) {
            $statusExpr = "COALESCE(NULLIF(tahap_akhirs.status_akhir,''),'Belum Serter')";
        } elseif ($hasStatus) {
            $statusExpr = "COALESCE(NULLIF(tahap_akhirs.status,''),'Belum Serter')";
        } else {
            $statusExpr = "'Belum Serter'";
        }
    }

    // ===== Query dasar tanpa join (biar baris tidak hilang) =====
    $q = TahapAkhir::query()
        ->select('tahap_akhirs.*')
        ->selectRaw(
            ($hasTanggalMasuk
                ? 'UNIX_TIMESTAMP(COALESCE(tanggal_masuk, created_at))'
                : 'UNIX_TIMESTAMP(created_at)'
            ) . ' AS masuk_ts'
        )
        ->selectRaw("$statusExpr AS status_text");

    // NOTE: Jika sebelumnya ada filter seperti ->where('status','Belum Serter'), hapus supaya yang sudah Serter tetap tampil.

    return DataTables::of($q)
        ->addIndexColumn()

        // Kolom tampilan tambahan
        ->addColumn('kode',   fn ($r) => $r->kode_proyek   ?: '-')
        ->addColumn('lokasi', fn ($r) => $r->lokasi_lahan  ?: '-')
        ->addColumn('tanggal', function ($r) use ($hasTanggalMasuk) {
            $d = $hasTanggalMasuk ? ($r->tanggal_masuk ?? $r->created_at) : $r->created_at;
            return $d ? Carbon::parse($d)->timezone('Asia/Jakarta')->format('d-m-Y H:i') : '-';
        })
        ->orderColumn('tanggal', 'masuk_ts $1')

        // Kolom STATUS (badge) dari status_text
        ->addColumn('status', function ($r) {
            $st  = trim((string)($r->status_text ?? 'Belum Serter'));
            $ok  = (mb_strtolower($st) === 'sudah serter');
            $cls = $ok ? 'success' : 'warning text-dark';
            return '<span class="badge bg-'.$cls.'">'.e($st).'</span>';
        })

        // Kolom AKSI
        ->addColumn('aksi', function ($r) {
            $show = route('studio.akhir.show', $r->id);
            return '<a href="'.$show.'" class="btn btn-sm btn-outline-primary" title="Lihat">
                      <i class="bi bi-eye"></i>
                    </a>';
        })

        // ===== Search & Order kolom Status =====
        ->filter(function ($query) use ($request, $statusExpr) {
            $search = $request->input('search.value');
            if (!empty($search)) {
                $like = "%{$search}%";
                $query->where(function($q) use ($like, $statusExpr) {
                    $q->where('tahap_akhirs.nama', 'like', $like)
                      ->orWhere('tahap_akhirs.kode_proyek', 'like', $like)
                      ->orWhere('tahap_akhirs.lokasi_lahan', 'like', $like)
                      ->orWhereRaw("$statusExpr LIKE ?", [$like]);
                });
            }
        })
        ->filterColumn('status', function ($query, $keyword) use ($statusExpr) {
            $query->whereRaw("$statusExpr LIKE ?", ["%{$keyword}%"]);
        })
        ->orderColumn('status', function ($query, $dir) use ($statusExpr) {
            $query->orderByRaw("$statusExpr {$dir}");
        })

        ->rawColumns(['status','aksi'])
        ->make(true);
}

        public function tahapAkhirShow(TahapAkhir $akhir)
    {
        $akhir->load('klien');
        return view('studio.tahap_akhir.show', compact('akhir'));
    }

public function akhirSerterSelesai(Request $r, TahapAkhir $akhir)
{
    $table       = $akhir->getTable(); // biasanya 'tahap_akhirs'
    $hasAkhir    = Schema::hasColumn($table, 'status_akhir');
    $hasStatus   = Schema::hasColumn($table, 'status');
    $hasSerterAt = Schema::hasColumn($table, 'serter_at');

    // Siapkan field yang benar-benar ADA
    $updates = [];
    if ($hasAkhir)    $updates['status_akhir'] = 'Sudah Serter';
    if ($hasStatus)   $updates['status']       = 'Sudah Serter';
    if ($hasSerterAt) $updates['serter_at']    = now();

    DB::transaction(function () use ($table, $akhir, $updates) {
        if (!empty($updates)) {
            DB::table($table)->where('id', $akhir->id)->update($updates);
        }

        // Sinkron ke klien (kalau index baca dari klien)
        if (method_exists($akhir, 'klien')) {
            $k = $akhir->klien()->first();
            if ($k) {
                $ktable     = $k->getTable();
                $ku = [];
                if (Schema::hasColumn($ktable, 'status_akhir')) $ku['status_akhir'] = 'Sudah Serter';
                if (Schema::hasColumn($ktable, 'status'))       $ku['status']       = 'Sudah Serter';
                if (!empty($ku)) DB::table($ktable)->where('id', $k->id)->update($ku);
            }
        }
    });

    // Balasan
    if ($r->expectsJson() || $r->ajax()) {
        return response()->json([
            'ok'         => true,
            'message'    => 'Status diubah menjadi "Sudah Serter".',
            'id'         => $akhir->id,
        ]);
    }
    return redirect()->route('studio.akhir')->with('success','Status diubah menjadi "Sudah Serter".');
}

    public function akhirToMou(Request $r, TahapAkhir $akhir)
    {
        DB::transaction(function () use ($akhir) {
            $k = method_exists($akhir, 'klien') ? $akhir->klien : null;
            $pick = fn (...$vals) => collect($vals)->first(fn($v) => !blank($v));

            $all = [
                'klien_id'        => $pick($akhir->klien_id, optional($k)->id),
                'tahap_akhir_id'  => $akhir->id,

                'nama'            => $pick($akhir->nama,        optional($k)->nama),
                'kode_proyek'     => $pick($akhir->kode_proyek, optional($k)->kode_proyek),
                'kelas'           => $pick($akhir->kelas,       optional($k)->kelas),

                'alamat_tinggal'  => $pick($akhir->alamat_tinggal, optional($k)->alamat_tinggal),
                'lokasi_lahan'    => $pick($akhir->lokasi_lahan,   optional($k)->lokasi_lahan),
                'luas_lahan'      => $akhir->luas_lahan,
                'luas_bangunan'   => $akhir->luas_bangunan,

                'kebutuhan_ruang' => $akhir->kebutuhan_ruang,
                'arah_mata_angin' => $akhir->arah_mata_angin,
                'batas_keliling'  => $akhir->batas_keliling,
                'konsep_bangunan' => $akhir->konsep_bangunan,
                'referensi'       => $akhir->referensi,

                'budget'          => $akhir->budget,
                'lembar_diskusi'  => $akhir->lembar_diskusi,
                'layout'          => $akhir->layout,
                'desain_3d'       => $akhir->desain_3d,
                'rab_boq'         => $akhir->rab_boq,
                'gambar_kerja'    => $akhir->gambar_kerja,

                'lembar_survei'   => $akhir->lembar_survei,
                'catatan_survei'  => $akhir->catatan_survei,

                'keterangan'      => $akhir->keterangan,
                'tanggal_masuk'   => $akhir->tanggal_masuk,

                'created_at'      => now(),
                'updated_at'      => now(),
            ];

            $fillable = (new Mou)->getFillable();
            $payload  = array_intersect_key($all, array_flip($fillable));

            Mou::create($payload);

            if ($akhir->klien_id && Schema::hasColumn('kliens', 'status')) {
                DB::table('kliens')->where('id', $akhir->klien_id)->update(['status' => 'in_mou']);
            }

            $this->hardDelete($akhir);
        });

        return redirect()->route('studio.akhir')->with('success', 'Dipindahkan ke MOU.');
    }

    private function hardDelete($model): void
    {
        try { if (method_exists($model, 'forceDelete')) { $model->forceDelete(); return; } }
        catch (\Throwable $e) {}
        $model->delete();
    }

    /** Halaman index */
    public function delegasiRabIndex()
    {
        return view('studio.delegasi_rab.index');
    }

    /** DataTables JSON */
    public function delegasiRabData(Request $request)
    {
        $q = DelegasiRab::query()
            ->addSelect([
                'delegasirab.*',
                DB::raw('COALESCE(tanggal_masuk, created_at) AS tanggal_masuk_sort'),
            ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('kode',   fn($r) => $r->kode_proyek ?: '-')
            ->addColumn('lokasi', fn($r) => $r->lokasi_lahan ?: '-')
            // kirim hanya tanggal (YYYY-mm-dd)
            ->addColumn('tanggal_masuk', function ($r) {
                $d = $r->tanggal_masuk ?: $r->created_at;
                return $d ? Carbon::parse($d)->timezone('Asia/Jakarta')->format('Y-m-d') : '-';
            })
            ->addColumn('aksi', function ($r) {
                $url = route('studio.delegasirab.show', $r->id);
                return '<a href="'.$url.'" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>';
            })
            ->orderColumn('tanggal_masuk', 'tanggal_masuk_sort $1')
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /** Show satu record */
    public function delegasiRabShow(DelegasiRab $delegasiRab)
    {
        return view('studio/delegasi_rab/show', compact('delegasiRab'));
    }

    /* ====================== UTIL (opsional) ====================== */
    private function copyPublicIfExists(?string $src, string $destDir): ?string
    {
        if (!$src) return null;
        if (!Storage::disk('public')->exists($src)) return null;

        $base = pathinfo($src, PATHINFO_BASENAME);
        $dst  = trim($destDir, '/').'/'.$base;

        if (Storage::disk('public')->exists($dst)) {
            $name = pathinfo($base, PATHINFO_FILENAME);
            $ext  = pathinfo($base, PATHINFO_EXTENSION);
            $dst  = trim($destDir, '/').'/'.$name.'_'.uniqid().($ext ? '.'.$ext : '');
        }
        Storage::disk('public')->copy($src, $dst);
        return $dst;
    }
}
