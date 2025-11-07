@extends('layouts.app')

@section('title', 'Detail Klien Cancel')

@push('styles')
<style>
/* ===== THEME SELARAS UNTUK HALAMAN SHOW ===== */
.theme-show{
  --show-bg:      #1e1e2d;   /* dasar card + sel tabel */
  --show-hover:   #1e1e2d;   /* nonaktifkan efek hover (tetap sama) */
  --show-border:  #2b3050;   /* garis pemisah */
  --show-text:    #E6E9F2;   /* warna teks utama */
  --show-muted:   #B8BDCE;   /* teks sekunder */
}

/* Card & header */
.theme-show .card{
  background-color: var(--show-bg) !important;
  border-color: var(--show-border) !important;
  box-shadow: none;
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
/* setiap sel (th/td) */
.theme-show .card .table > :not(caption) > * > *{
  background-color: var(--bs-table-bg) !important;
  color: var(--show-text) !important;
  border-color: var(--show-border) !important;
  box-shadow: none !important;
}
/* hilangkan hover yang mengubah warna */
.theme-show .card .table.table-hover > tbody > tr:hover > *{
  background-color: var(--bs-table-bg) !important;
}

/* list-group (kalau ada) */
.theme-show .card .list-group-item{
  background-color: var(--show-bg) !important;
  border-color: var(--show-border) !important;
  color: var(--show-text) !important;
}

/* tombol “lihat file” saat tidak ada file */
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
      <span>Detail Klien Cancel</span>
    </h3>
    <p class="text-subtitle text-muted mb-0">Informasi Data Lengkap Klien yang Dibatalkan</p>
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

    // Ambil model (disamakan penamaan dengan view normal)
    $klien = $klien ?? $klienCancel ?? $cancel ?? null;
  @endphp

  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h2 class="mb-0">{{ $klien->nama ?? '-' }}</h2>
          <span class="badge bg-danger" style="font-size:1rem;padding:.5em 1em;">Cancel</span>
        </div>

        <div class="card-body">
          {{-- ===================== DATA KLIEN ===================== --}}
          <div class="row">
            <h5 class="mt-2">Data Klien</h5>
            <hr class="mt-0">
            <div class="col-md-6">
              <table class="table table-borderless table-hover mb-0">
                <tr><th style="width:220px;">Nama</th><td>{{ $klien->nama ?? '-' }}</td></tr>
                <tr><th>No. HP</th><td>{{ $klien->no_hp ?? '-' }}</td></tr>
                <tr><th>Alamat Tinggal</th><td>{{ $klien->alamat_tinggal ?? '-' }}</td></tr>
                <tr><th>Email</th><td>{{ $klien->email ?? '-' }}</td></tr>
                <tr><th>Alasan Cancel</th><td>{{ $klien->alasan_cancel ?? ($klien->alasan ?? '-') }}</td></tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-borderless table-hover mb-0">
                <tr><th style="width:220px;">Tanggal Masuk</th><td>{{ $klien->tanggal_masuk ?? '-' }}</td></tr>
                <tr><th>Aktivitas Klien</th><td>{{ $klien->aktivitas ?? '-' }}</td></tr>
                <tr><th>Budget</th><td>{{ $klien->budget ?? '-' }}</td></tr>
                <tr><th>Hoby Klien</th><td>{{ $klien->hoby ?? '-' }}</td></tr>
                <tr>
                  <th>Waktu Cancel</th>
                  <td>
                    @php
                      $w = $klien->waktu_cancel ?? $klien->canceled_at ?? null;
                      try { $wFmt = $w ? \Carbon\Carbon::parse($w)->format('d M Y H:i') : '-'; }
                      catch (\Throwable $e) { $wFmt = $w ?: '-'; }
                    @endphp
                    {{ $wFmt }}
                  </td>
                </tr>
              </table>
            </div>
          </div>

          {{-- ===================== DATA PROYEK ===================== --}}
          <div class="row">
            <h5 class="mt-4">Data Proyek</h5>
            <hr class="mt-0">
            <div class="col-md-6">
              <table class="table table-borderless table-hover mb-0">
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
              <table class="table table-borderless table-hover mb-0">
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

          {{-- ===================== BIAYA (opsional) ===================== --}}
          @if(!is_null($klien->biaya_survei ?? null))
          <div class="row">
            <h5 class="mt-4">Biaya</h5>
            <hr class="mt-0">
            <div class="col-md-6">
              <table class="table table-borderless table-hover mb-0">
                <tr>
                  <th style="width:220px;">Biaya Survei</th>
                  <td>{{ 'Rp. '.number_format($klien->biaya_survei ?? 0,0,',','.') }}</td>
                </tr>
              </table>
            </div>
          </div>
          @endif

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
