{{-- resources/views/marketing/view.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Klien')

@push('styles')
<style>
/* ===== THEME SELARAS UNTUK HALAMAN SHOW ===== */
.theme-show{
  --show-bg:      #1e1e2d;   /* dasar card + sel tabel */
  --show-hover:   #24263b;   /* hover sedikit lebih terang */
  --show-border:  #2b3050;   /* garis pemisah */
  --show-text:    #E6E9F2;   /* warna teks utama */
  --show-muted:   #B8BDCE;   /* teks sekunder */
}

/* Card & header */
.theme-show .card{
  background-color: var(--show-bg) !important;
  border-color: var(--show-border) !important;
}
.theme-show .card-header{
  background-color: var(--show-bg) !important;
  border-bottom-color: var(--show-border) !important;
  color: var(--show-text) !important;
}

/* Headings, paragraf, hr */
.theme-show h2, .theme-show h3, .theme-show h5 { color: var(--show-text) !important; }
.theme-show .text-muted, .theme-show .text-subtitle { color: var(--show-muted) !important; }
.theme-show hr{ border-top-color: var(--show-border) !important; opacity: 1; }

/* ===== TABEL: paksa semua sel bg sama, override bootstrap/mazer ===== */
.theme-show .card .table{
  --bs-table-bg:           var(--show-bg);
  --bs-table-striped-bg:   var(--show-bg);
  --bs-table-hover-bg:     var(--show-hover);
  --bs-table-border-color: var(--show-border);
  color: var(--show-text);
}

/* selector kuat untuk setiap sel (th/td) */
.theme-show .card .table > :not(caption) > * > *{
  background-color: var(--bs-table-bg) !important;
  color: var(--show-text) !important;
  border-color: var(--show-border) !important;
  box-shadow: none !important;
}

/* nonaktifkan striping default, hover serasi */
.theme-show .card .table.table-striped > tbody > tr:nth-of-type(odd) > *{
  background-color: var(--bs-table-bg) !important;
}
.theme-show .card .table.table-hover > tbody > tr:hover > *{
  background-color: var(--show-hover) !important;
}

/* kalau ada elemen bg-dark/bg-light di dalam sel dari template */
.theme-show .card .table .bg-dark,
.theme-show .card .table .bg-light{
  background-color: var(--bs-table-bg) !important;
  color: var(--show-text) !important;
}

/* list-group (kalau ada) */
.theme-show .card .list-group-item{
  background-color: var(--show-bg) !important;
  border-color: var(--show-border) !important;
  color: var(--show-text) !important;
}

/* tombol “lihat file” tetap kontras */
.theme-show .btn.btn-secondary[disabled]{
  background-color: #475069 !important;
  border-color: #475069 !important;
  color: #cfd5e6 !important;
}
</style>
@endpush

@section('content')

<section class="section px-2 px-md-3 theme-show">
  <div class="page-heading mb-4">
    <h3 class="d-flex align-items-center gap-2">
      <a href="{{ url()->previous() }}" class="text-decoration-none">
        <i class="bi bi-arrow-left text-primary"></i>
      </a>
      <span>Detail Klien</span>
    </h3>
    <p class="text-subtitle text-muted mb-0">Informasi Data Lengkap Klien</p>
  </div>

  @php
    use Illuminate\Support\Str;

    // Helper URL file (storage / http / https)
    if (!function_exists('file_url_dm')) {
      function file_url_dm(?string $path) {
        if (!$path) return null;
        return Str::startsWith($path, ['http://','https://','/storage'])
          ? $path
          : asset('storage/'.$path);
      }
    }

    // Tombol "Lihat File"
    if (!function_exists('lihat_file_btn')) {
      function lihat_file_btn($file, $label='Lihat File', $cls='btn-primary') {
        $u = file_url_dm($file);
        return $u
          ? '<a href="'.$u.'" target="_blank" class="btn btn-sm '.$cls.'">'.$label.'</a>'
          : '<button type="button" class="btn btn-sm btn-secondary" disabled>Tidak Ada File</button>';
      }
    }

    // Warna badge keterangan
    $colors = [
      'Survei'              => 'success',
      'Perlu FollowUp'      => 'primary',
      'Penawaran RAB'       => 'warning',
      'Budget tidak cukup'  => 'danger',
      'Diskusi Kelaurga'    => 'purple',
      'Belum Siap'          => 'secondary',
      'Parsial'             => 'brown',
    ];
    $badgeColor = $colors[$klien->keterangan ?? ''] ?? 'dark';

    // Status khusus
    $isInSurvei = (($klien->status ?? '') === 'in_survei');
  @endphp

  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="mb-0">{{ $klien->nama }}</h2>
          @if (!empty($klien->keterangan))
            <span class="badge bg-{{ $badgeColor }}" style="font-size:1rem;padding:.5em 1em;">
              {{ $klien->keterangan }}
            </span>
          @endif
        </div>

        <div class="card-body">

          {{-- DATA KLIEN --}}
          <div class="row">
            <h5 class="mt-2">Data Klien</h5>
            <hr class="mt-0">
            <div class="col-md-6">
              <table class="table table-borderless mb-0">
                <tr><th style="width:220px;">Nama</th><td>{{ $klien->nama }}</td></tr>
                <tr><th>No. HP</th><td>{{ $klien->no_hp ?? '-' }}</td></tr>
                <tr><th>Alamat Tinggal</th><td>{{ $klien->alamat_tinggal ?? '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $klien->email ?? '-' }}</td></tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless mb-0">
                <tr><th style="width:220px;">Tanggal Masuk</th><td>{{ $klien->tanggal_masuk ?? '-' }}</td></tr>
                <tr><th>Aktivitas Klien</th><td>{{ $klien->aktivitas ?? '-' }}</td></tr>
                <tr><th>Budget</th><td>{{ $klien->budget ?? '-' }}</td></tr>
                <tr><th>Hoby Klien</th><td>{{ $klien->hoby ?? '-' }}</td></tr>
              </table>
            </div>
          </div>

          {{-- DATA PROYEK --}}
          <div class="row">
            <h5 class="mt-4">Data Proyek</h5>
            <hr class="mt-0">
            <div class="col-md-6">
              <table class="table table-borderless mb-0">
                <tr><th style="width:220px;">Kode Proyek</th><td>{{ $klien->kode_proyek ?? '-' }}</td></tr>
                <tr><th>Kelas</th><td>{{ $klien->kelas ?? '-' }}</td></tr>
                <tr><th>Lokasi Proyek</th><td>{{ $klien->lokasi_lahan ?? '-' }}</td></tr>
                <tr>
                  <th>Share Lokasi</th>
                  <td>
                    @if (!empty($klien->share_lokasi))
                      <a href="{{ $klien->share_lokasi }}" target="_blank" class="btn btn-primary btn-sm">Link Maps</a>
                    @else
                      -
                    @endif
                  </td>
                </tr>
                <tr><th>Luas Lahan</th><td>{{ $klien->luas_lahan ?? '-' }} m²</td></tr>
                <tr><th>Luas Bangunan</th><td>{{ $klien->luas_bangunan ?? '-' }} m²</td></tr>
                <tr><th>Batas Keliling Bangunan</th><td>{{ $klien->batas_keliling ?? '-' }}</td></tr>
                <tr><th>Estimasi Start Pembangunan</th><td>{{ $klien->estimasi_start ?? '-' }}</td></tr>
                <tr><th>Referensi</th><td>{!! lihat_file_btn($klien->referensi) !!}</td></tr>
                <tr><th>Foto Eksisting</th><td>{!! lihat_file_btn($klien->foto_eksisting) !!}</td></tr>
                <tr><th>Sertifikat</th><td>{!! lihat_file_btn($klien->sertifikat) !!}</td></tr>
                <tr><th>Layout</th><td>{!! lihat_file_btn($klien->layout) !!}</td></tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless mb-0">
                <tr><th style="width:220px;">Konsep Bangunan</th><td>{{ $klien->konsep_bangunan ?? '-' }}</td></tr>
                <tr><th>Kebutuhan Ruang</th><td>{{ $klien->kebutuhan_ruang ?? '-' }}</td></tr>
                <tr><th>Arah Mata Angin</th><td>{{ $klien->arah_mata_angin ?? '-' }}</td></tr>
                <tr><th>Jenis & Jumlah Kendaraan</th><td>{{ $klien->kendaraan ?? '-' }}</td></tr>
                <tr><th>Prioritas Ruang</th><td>{{ $klien->prioritas_ruang ?? '-' }}</td></tr>

                <tr>
                  <td colspan="2">
                    <div style="display:flex;align-items:center;text-align:center;">
                      <hr style="flex:1;border-top:2px solid #555;">
                      <span style="padding:0 6px;font-weight:bold;color:#888;">KHUSUS KOS</span>
                      <hr style="flex:1;border-top:2px solid #555;">
                    </div>
                  </td>
                </tr>

                <tr><th>Target User Kos</th><td>{{ $klien->target_user_kos ?? '-' }}</td></tr>
                <tr><th>Fasilitas Kos</th><td>{{ $klien->fasilitas_kos ?? '-' }}</td></tr>

                <tr><td colspan="2"><hr style="border-top:2px solid #555;"></td></tr>

                <tr><th>Lembar Diskusi</th><td>{!! lihat_file_btn($klien->lembar_diskusi) !!}</td></tr>
                <tr><th>Gambar Kerja</th><td>{!! lihat_file_btn($klien->gambar_kerja) !!}</td></tr>
                <tr><th>Desain 3D</th><td>{!! lihat_file_btn($klien->desain_3d) !!}</td></tr>
                <tr><th>RAB / BOQ</th><td>{!! lihat_file_btn($klien->rab_boq) !!}</td></tr>
              </table>
            </div>
          </div>

          {{-- BIAYA --}}
          <div class="row">
            <h5 class="mt-4">Biaya</h5>
            <hr class="mt-0">
            <div class="col-md-6">
              <table class="table table-borderless mb-0">
                <tr><th style="width:220px;">Biaya Survei</th><td>{{ $klien->biaya_survei ? 'Rp. '.number_format($klien->biaya_survei,0,',','.') : '-' }}</td></tr>
              </table>
            </div>
          </div>
@php
    // --- Status yang mungkin dipakai di tabel ---
    $statusRaw = trim((string) (
        $klien->status_klien
        ?? $klien->status
        ?? $klien->status_name
        ?? $klien->keterangan_status
        ?? ''
    ));

    // Normalisasi sederhana (lowercase + rapikan spasi)
    $statusNorm = mb_strtolower(preg_replace('/\s+/u', ' ', $statusRaw));

    // Anggap "baru" kalau:
    // 1) status kosong (umum terjadi di Klien Baru), ATAU
    // 2) status berisi "klien baru" / "baru"
    $isNew = ($statusNorm === '')
          || in_array($statusNorm, ['klien baru', 'baru']);

    // Kunci tombol kalau TIDAK baru atau sudah masuk inbox survei
    // (variabel $isInSurvei sudah kamu punya sebelumnya)
    $isLocked = (!$isNew) || ($isInSurvei ?? false);
@endphp



<div class="d-flex flex-wrap justify-content-start align-items-center gap-2 mt-4">
  {{-- Cancel --}}
  <button type="button"
          class="btn btn-danger js-klien-cancel"
          data-cancel-url="{{ route('marketing.klien.cancel', $klien->id) }}"
          @if($isLocked) disabled aria-disabled="true" @endif>
    <i class="bi bi-x-circle"></i> Klien Cancel
  </button>

  {{-- Lanjut Survei --}}
  <button type="button"
          class="btn btn-success js-lanjut-survei"
          data-survei-url="{{ route('studio.survei_inbox.store', $klien->id) }}"
          @if($isLocked) disabled aria-disabled="true" @endif>
    Lanjut Survei <i class="bi bi-arrow-right-circle"></i>
  </button>
</div>

  {{-- FORM HIDDEN – Cancel --}}
  <form id="form-klien-cancel"
        action="{{ route('marketing.klien.cancel', $klien->id) }}"
        method="POST" style="display:none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="alasan_cancel" value="">
  </form>

  {{-- FORM HIDDEN – Lanjut Survei --}}
  <form id="form-lanjut-survei"
        action="{{ route('studio.survei_inbox.store', $klien->id) }}"
        method="POST" style="display:none;">
    @csrf
  </form>
</div>


        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
  'use strict';

  const IS_IN_SURVEI = @json($isInSurvei);
  if (IS_IN_SURVEI) return;

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // Helpers
  async function postForm(url, data = {}) {
    const body = new URLSearchParams();
    for (const k in data) body.append(k, data[k]);
    const resp = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body,
      credentials: 'same-origin'
    });
    if (!resp.ok) throw new Error(await resp.text());
    try { return await resp.json(); } catch { return {}; }
  }

  // Elements
  const btnCancel  = document.querySelector('.js-klien-cancel');
  const btnSurvei  = document.querySelector('.js-lanjut-survei');
  const formCancel = document.getElementById('form-klien-cancel');
  const formSurvei = document.getElementById('form-lanjut-survei');

  // Cancel dengan alasan
  btnCancel?.addEventListener('click', async () => {
    const url = btnCancel.getAttribute('data-cancel-url');
    if (!url) return;

    const ask = await Swal.fire({
      icon: 'warning',
      title: 'Apakah anda yakin klien ini cancel?',
      html: `
        <div class="fw-semibold text-center mb-2">Alasan (opsional)</div>
        <div class="d-flex justify-content-center">
            <textarea id="alasan-cancel"
                    class="swal2-textarea"
                    placeholder="Tulis alasan pembatalan..."
                    style="width:90%;max-width:520px;"></textarea>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Ya, batalkan!',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      preConfirm: () => (document.getElementById('alasan-cancel')?.value || '').trim()
    });
    if (!ask.isConfirmed) return;

    try {
      await postForm(url, { _method: 'DELETE', alasan_cancel: ask.value });
      await Swal.fire({ icon:'success', title:'Klien dipindahkan ke Cancel', timer:1300, showConfirmButton:false });
      window.location.replace(@json(route('marketing.klien.index')));
    } catch (e) {
      // fallback form submit
      formCancel.querySelector('[name="alasan_cancel"]').value = ask.value || '';
      formCancel.submit();
    }
  });

  // Lanjut Survei
  btnSurvei?.addEventListener('click', async () => {
    const url = btnSurvei.getAttribute('data-survei-url');
    if (!url) return;

    const ok = await Swal.fire({
      icon:'question',
      title:'Apa benar klien ini lanjut survei?',
      showCancelButton:true,
      confirmButtonText:'Ya, lanjutkan!',
      cancelButtonText:'Batal',
      reverseButtons:true
    });
    if (!ok.isConfirmed) return;

    try {
      await postForm(url);
      btnSurvei.disabled = true; btnSurvei.setAttribute('aria-disabled','true'); btnSurvei.title = 'Sudah In Survei';
      btnCancel.disabled = true; btnCancel.setAttribute('aria-disabled','true'); btnCancel.title = 'Tidak bisa cancel saat status In Survei';

      await Swal.fire({ icon:'success', title:'Permintaan survei dikirim', text:'Data sudah masuk ke Survei Inbox.', timer:1300, showConfirmButton:false });
      window.location.replace(@json(route('marketing.klien.index')));
    } catch (e) {
      formSurvei.submit();
    }
  });

})();
</script>
@endpush
