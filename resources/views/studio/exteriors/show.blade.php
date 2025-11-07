@extends('layouts.app')

@section('title','Detail 3D Exterior & Interior')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail 3D Exterior & Interior</h3>
  </div>

  @php
    use Illuminate\Support\Str;

    // helper ambil nilai pertama yang tidak kosong
    $pick = function (...$vals) {
        foreach ($vals as $v) if (!blank($v)) return $v;
        return null;
    };

    $rupiah = fn($n) => $n !== null ? 'Rp. '.number_format($n,0,',','.') : '-';

    $fileUrl = function (?string $path) {
        if (!$path) return null;
        if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
        return asset('storage/'.$path);
    };

    $k = $exterior->klien ?? null; // shortcut relasi klien (jika ada)
  @endphp

  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">

        {{-- ============= KIRI: Data Klien ============= --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2">
            <small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $pick($exterior->nama, optional($k)->nama, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted">Email</small>
            <div class="fw-semibold">{{ $pick($exterior->email, optional($k)->email, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted">No HP</small>
            <div class="fw-semibold">{{ $pick($exterior->no_hp, optional($k)->no_hp, '-') }}</div>
          </div>

          <div class="mb-2">
            <small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick($exterior->alamat_tinggal, optional($k)->alamat_tinggal, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $pick($exterior->kode_proyek, optional($k)->kode_proyek, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $pick($exterior->kelas, optional($k)->kelas, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small>
            <div class="fw-semibold">{{ $pick($exterior->lokasi_lahan, optional($k)->lokasi_lahan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Share Lokasi</small>
            <div class="fw-semibold">{{ $pick($exterior->share_lokasi, optional($k)->share_lokasi, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $pick($exterior->luas_lahan, optional($k)->luas_lahan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pick($exterior->luas_bangunan, optional($k)->luas_bangunan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pick($exterior->kebutuhan_ruang, optional($k)->kebutuhan_ruang, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pick($exterior->konsep_bangunan, optional($k)->konsep_bangunan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pick($exterior->arah_mata_angin, optional($k)->arah_mata_angin, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $pick($exterior->batas_keliling, optional($k)->batas_keliling, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Preferensi & Aktivitas</h6>
          <div class="mb-2"><small class="text-muted">Hoby</small>
            <div class="fw-semibold">{{ $pick($exterior->hoby, optional($k)->hoby, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Aktivitas</small>
            <div class="fw-semibold">{{ $pick($exterior->aktivitas, optional($k)->aktivitas, '-') }}</div>
          </div>


        </div>

        {{-- ============= KANAN: Rincian & Berkas ============= --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pick($exterior->budget, optional($k)->budget)) }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Biaya Survei</small>
            <div class="fw-semibold">{{ $rupiah($pick($exterior->biaya_survei, optional($k)->biaya_survei)) }}</div>
          </div>

                    <div class="mb-2"><small class="text-muted">Prioritas Ruang</small>
            <div class="fw-semibold">{{ $pick($exterior->prioritas_ruang, optional($k)->prioritas_ruang, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kendaraan</small>
            <div class="fw-semibold">{{ $pick($exterior->kendaraan, optional($k)->kendaraan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Estimasi Start Pembangunan</small>
            <div class="fw-semibold">{{ $pick($exterior->estimasi_start, optional($k)->estimasi_start, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ $pick(optional($exterior->tanggal_masuk)?->format('Y-m-d'), optional(optional($k)->tanggal_masuk)?->format('Y-m-d'), '-') }}
            </div>
          </div>

          <div class="mb-2"><small class="text-muted">Keterangan</small>
            <div class="fw-semibold">{{ $pick($exterior->keterangan, optional($k)->keterangan, '-') }}</div>
          </div>

          <hr>

          <div class="mb-3">
            <h6 class="mb-2">Berkas</h6>

            @php $u = $fileUrl($pick($exterior->sertifikat, optional($k)->sertifikat)); @endphp
            <div class="mb-1"><small class="text-muted">Sertifikat</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->lembar_diskusi, optional($k)->lembar_diskusi)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->foto_eksisting, optional($k)->foto_eksisting)); @endphp
            <div class="mb-1"><small class="text-muted">Foto Eksisting</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->referensi, optional($k)->referensi)); @endphp
            <div class="mb-1"><small class="text-muted">Referensi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->layout, optional($k)->layout)); @endphp
            <div class="mb-1"><small class="text-muted">Layout</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->desain_3d, optional($k)->desain_3d)); @endphp
            <div class="mb-1"><small class="text-muted">Desain 3D</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->rab_boq, optional($k)->rab_boq)); @endphp
            <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($exterior->gambar_kerja, optional($k)->gambar_kerja)); @endphp
            <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($exterior->lembar_survei); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Survei (PDF)</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
            </div>
          </div>

          <hr>

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea class="form-control" rows="4" readonly>{{ $pick($exterior->catatan_survei, optional($k)->catatan_survei, '') }}</textarea>
        </div>
      </div>

{{-- Tombol bawah --}}
<div class="mt-4 d-flex flex-wrap gap-2">
  {{-- Lanjut ke MEP & Spek Material --}}
  <form id="form-to-mep"
        action="{{ route('studio.exteriors.to_mep', $exterior->id) }}"
        method="POST" class="d-inline">
    @csrf
    <button type="button" id="btn-to-mep" class="btn btn-success">
      <i class="bi bi-check2-circle me-1"></i> Lanjut MEP &amp; Spek Material
    </button>
  </form>

  {{-- Cancel Exterior --}}
  <form id="form-exterior-cancel"
        action="{{ route('studio.exteriors.cancel', $exterior->id) }}"
        method="POST" class="d-inline">
    @csrf
    <input type="hidden" name="alasan_cancel" value="">
    <button type="button" id="btn-exterior-cancel" class="btn btn-danger">
      <i class="bi bi-x-circle me-1"></i> Klien Cancel
    </button>
  </form>
</div>


    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
  'use strict';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // ====== Lanjut MEP ======
  document.getElementById('btn-to-mep')?.addEventListener('click', async () => {
    const ok = await Swal.fire({
      icon: 'question',
      title: 'Lanjut ke MEP & Spek Material?',
      text: 'Data Exterior akan dipindahkan ke tabel MEP.',
      showCancelButton: true,
      confirmButtonText: 'Ya, lanjut',
      cancelButtonText: 'Batal',
      reverseButtons: true
    });
    if (ok.isConfirmed) document.getElementById('form-to-mep').submit();
  });

  // ====== Cancel Exterior ======
  const btnCancel = document.getElementById('btn-exterior-cancel');
  const formCancel = document.getElementById('form-exterior-cancel');

  btnCancel?.addEventListener('click', async () => {
    const {isConfirmed, value} = await Swal.fire({
      icon: 'warning',
      title: 'Batalkan tahap 3D Exterior & Interior?',
      html: `
        <div class="fw-semibold text-center mb-2">Alasan (opsional)</div>
        <div class="d-flex justify-content-center">
          <textarea id="alasan-denah-cancel"
                    class="swal2-textarea"
                    placeholder="Tulis alasan pembatalan..."
                    style="width:90%;max-width:520px;"></textarea>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: 'Ya, batalkan',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      focusConfirm: false,
      preConfirm: () => (document.getElementById('alasan-exterior-cancel')?.value || '').trim()
    });

    if (!isConfirmed) return;

    try {
      formCancel.querySelector('input[name="alasan_cancel"]').value = (value || '');
      formCancel.submit();
    } catch (e) {
      formCancel.submit();
    }
  });

})();
</script>
@endpush

