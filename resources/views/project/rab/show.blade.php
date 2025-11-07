{{-- resources/views/project/rab/show.blade.php --}}
@extends('layouts.app')
@section('title','Detail RAB')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail RAB</h3>
  </div>

  @php
    use Illuminate\Support\Str;

    // nilai pertama yang tidak kosong
    $pick   = function (...$vals) { foreach ($vals as $v) if (!blank($v)) return $v; return null; };
    $rupiah = fn($n) => $n !== null ? 'Rp. '.number_format($n,0,',','.') : '-';

    // normalisasi URL file
    $fileUrl = function (?string $path) {
      if (!$path) return null;
      if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
      return asset('storage/'.$path);
    };

    $k = $rab->klien ?? null;
  @endphp

  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">
        {{-- ============= KIRI: Data Klien ============= --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $pick($rab->nama, optional($k)->nama, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Email</small>
            <div class="fw-semibold">{{ $pick($rab->email, optional($k)->email, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">No HP</small>
            <div class="fw-semibold">{{ $pick($rab->no_hp, optional($k)->no_hp, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick($rab->alamat_tinggal, optional($k)->alamat_tinggal, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $pick($rab->kode_proyek, optional($k)->kode_proyek, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $pick($rab->kelas, optional($k)->kelas, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small>
            <div class="fw-semibold">{{ $pick($rab->lokasi_lahan, optional($k)->lokasi_lahan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Share Lokasi</small>
            <div class="fw-semibold">{{ $pick($rab->share_lokasi, optional($k)->share_lokasi, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $pick($rab->luas_lahan, optional($k)->luas_lahan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pick($rab->luas_bangunan, optional($k)->luas_bangunan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pick($rab->kebutuhan_ruang, optional($k)->kebutuhan_ruang, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pick($rab->konsep_bangunan, optional($k)->konsep_bangunan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pick($rab->arah_mata_angin, optional($k)->arah_mata_angin, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $pick($rab->batas_keliling, optional($k)->batas_keliling, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Preferensi & Aktivitas</h6>
          <div class="mb-2"><small class="text-muted">Hoby</small>
            <div class="fw-semibold">{{ $pick($rab->hoby, optional($k)->hoby, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Aktivitas</small>
            <div class="fw-semibold">{{ $pick($rab->aktivitas, optional($k)->aktivitas, '-') }}</div>
          </div>
        </div>

        {{-- ============= KANAN: Rincian & Berkas ============= --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pick($rab->budget, optional($k)->budget)) }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Biaya Survei</small>
            <div class="fw-semibold">{{ $rupiah($pick($rab->biaya_survei, optional($k)->biaya_survei)) }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Prioritas Ruang</small>
            <div class="fw-semibold">{{ $pick($rab->prioritas_ruang, optional($k)->prioritas_ruang, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Kendaraan</small>
            <div class="fw-semibold">{{ $pick($rab->kendaraan, optional($k)->kendaraan, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Estimasi Start Pembangunan</small>
            <div class="fw-semibold">{{ $pick($rab->estimasi_start, optional($k)->estimasi_start, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ $pick(optional($rab->tanggal_masuk)?->format('Y-m-d'), optional(optional($k)->tanggal_masuk)?->format('Y-m-d'), '-') }}
            </div>
          </div>

          <div class="mb-2"><small class="text-muted">Keterangan</small>
            <div class="fw-semibold">{{ $pick($rab->keterangan, optional($k)->keterangan, '-') }}</div>
          </div>

          <hr>

          <div class="mb-3">
            <h6 class="mb-2">Berkas</h6>

            @php $u = $fileUrl($pick($rab->sertifikat, optional($k)->sertifikat)); @endphp
            <div class="mb-1"><small class="text-muted">Sertifikat</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->lembar_diskusi, optional($k)->lembar_diskusi)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->foto_eksisting, optional($k)->foto_eksisting)); @endphp
            <div class="mb-1"><small class="text-muted">Foto Eksisting</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->referensi, optional($k)->referensi)); @endphp
            <div class="mb-1"><small class="text-muted">Referensi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->layout, optional($k)->layout)); @endphp
            <div class="mb-1"><small class="text-muted">Layout</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->desain_3d, optional($k)->desain_3d)); @endphp
            <div class="mb-1"><small class="text-muted">Desain 3D</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->rab_boq, optional($k)->rab_boq)); @endphp
            <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($rab->gambar_kerja, optional($k)->gambar_kerja)); @endphp
            <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($rab->lembar_survei); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Survei (PDF)</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
            </div>
          </div>

          <hr>

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea class="form-control" rows="4" readonly>{{ $pick($rab->catatan_survei, optional($k)->catatan_survei, '') }}</textarea>
        </div>
      </div>

      {{-- ============= TOMBOL AKSI (semua pakai SweetAlert konfirmasi) ============= --}}
      <div class="d-flex gap-2 mt-3 flex-wrap">

        {{-- 1) Lanjut MOU --}}
        <form id="form-to-mou" action="{{ route('project.rab.to_mou', $rab->id) }}" method="POST" class="d-inline">
          @csrf
          <button type="button" id="btn-to-mou" class="btn btn-primary">
            <i class="bi bi-file-earmark-text me-1"></i> Lanjut MOU
          </button>
        </form>

        {{-- 2) Lanjut Serter Desain (Tahap Akhir) --}}
        <form id="form-to-akhir" action="{{ route('project.rab.to_akhir', $rab->id) }}" method="POST" class="d-inline">
          @csrf
          <button type="button" id="btn-to-akhir" class="btn btn-success">
            <i class="bi bi-check-circle me-1"></i> Lanjut Serter Desain
          </button>
        </form>

        {{-- 3) Klien Cancel --}}
        <form id="form-rab-cancel" action="{{ route('project.rab.cancel', $rab->id) }}" method="POST" class="d-inline">
          @csrf
          <input type="hidden" name="alasan_cancel" value="">
          <button type="button" id="btn-rab-cancel" class="btn btn-danger">
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
(() => {
  'use strict';
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  // Helper: tombol → konfirmasi → submit form
  const confirmAndSubmit = async ({btnId, formId, title, text, confirmText='Ya, lanjut', cancelText='Batal', loadingText='Memproses...'}) => {
    const btn  = document.getElementById(btnId);
    const form = document.getElementById(formId);
    if (!btn || !form) return;

    btn.addEventListener('click', async () => {
      const ok = await Swal.fire({
        icon: 'question',
        title,
        text,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
      });
      if (!ok.isConfirmed) return;

      const old = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>${loadingText}`;

      // Submit normal (biar ikut flow redirect + flash message di server)
      form.submit();

      // Safety restore kalau tidak redirect (jarang terjadi)
      setTimeout(() => { try { btn.innerHTML = old; btn.disabled = false; } catch (_) {} }, 3000);
    });
  };

  // 1) Lanjut MOU
  confirmAndSubmit({
    btnId:  'btn-to-mou',
    formId: 'form-to-mou',
    title:  'Lanjut ke MOU?',
    text:   'Data RAB akan dipindahkan ke modul MOU.',
    confirmText: 'Ya, lanjut',
    loadingText: 'Memindahkan...'
  });

  // 2) Lanjut Serter Desain (Tahap Akhir)
  confirmAndSubmit({
    btnId:  'btn-to-akhir',
    formId: 'form-to-akhir',
    title:  'Lanjut ke Serter Desain?',
    text:   'Data RAB akan dipindahkan ke Tahap Akhir (Serter Desain).',
    confirmText: 'Ya, lanjut',
    loadingText: 'Memindahkan...'
  });

  // 3) Klien Cancel (dengan input alasan)
  const btnCancel  = document.getElementById('btn-rab-cancel');
  const formCancel = document.getElementById('form-rab-cancel');

  btnCancel?.addEventListener('click', async () => {
    const {isConfirmed, value} = await Swal.fire({
      icon: 'warning',
      title: 'Klien Cancel tahap RAB?',
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
      preConfirm: () => (document.getElementById('alasan-rab-cancel')?.value || '').trim()
    });
    if (!isConfirmed) return;

    // Kirim via fetch agar bisa tampilkan popup sukses sebelum redirect
    try {
      const resp = await fetch(formCancel.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({ alasan_cancel: value || '' }),
        credentials: 'same-origin'
      });
      if (!resp.ok) throw new Error(await resp.text());

      await Swal.fire({ icon: 'success', title: 'Dibatalkan', timer: 1200, showConfirmButton: false, position:'center' });
      window.location.href = "{{ route('project.rab.index') }}";
    } catch (e) {
      // Fallback submit biasa
      formCancel.querySelector('input[name="alasan_cancel"]').value = value || '';
      formCancel.submit();
    }
  });
})();
</script>
@endpush
