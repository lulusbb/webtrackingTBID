{{-- resources/views/studio/tahap_akhir/show.blade.php --}}
@extends('layouts.app')
@section('title','Detail Tahap Akhir')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail</h3>
  </div>

  @php
    use Illuminate\Support\Str;

    $pick = function (...$vals) { foreach ($vals as $v) if (!blank($v)) return $v; return null; };
    $rupiah = fn($n) => $n !== null ? 'Rp. '.number_format($n,0,',','.') : '-';
    $fileUrl = function (?string $path) {
      if (!$path) return null;
      if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
      return asset('storage/'.$path);
    };
    $k = $akhir->klien ?? null;

    // Status: default ke "Belum Serter" kalau kosong
  $akhirStatus = $akhir->status_akhir ?? $akhir->status ?? 'Belum Serter';
  $done = ($akhir->serter_at ?? null) || (strtolower($akhirStatus) === 'sudah serter');
  @endphp

  <div class="card shadow">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
          <small class="text-muted d-block">Status Serter</small>
          @if($done)
            <span class="badge bg-success">Sudah Serter</span>
          @else
            <span class="badge bg-warning text-dark">Belum Serter</span>
          @endif
        </div>
        <div class="text-muted">
          <i class="bi bi-clock me-1"></i>
          Terakhir diperbarui:
          {{ optional($akhir->updated_at ?? $akhir->created_at)?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-' }}
        </div>
      </div>

      <div class="row g-4">
        {{-- ==================== KIRI: Data Klien ==================== --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $pick($akhir->nama, optional($k)->nama, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Email</small>
            <div class="fw-semibold">{{ $pick($akhir->email ?? null, optional($k)->email, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">No HP</small>
            <div class="fw-semibold">{{ $pick($akhir->no_hp ?? null, optional($k)->no_hp, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick($akhir->alamat_tinggal, optional($k)->alamat_tinggal, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $pick($akhir->kode_proyek, optional($k)->kode_proyek, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $pick($akhir->kelas, optional($k)->kelas, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small>
            <div class="fw-semibold">{{ $pick($akhir->lokasi_lahan, optional($k)->lokasi_lahan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $pick($akhir->luas_lahan, optional($k)->luas_lahan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pick($akhir->luas_bangunan, optional($k)->luas_bangunan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pick($akhir->kebutuhan_ruang, optional($k)->kebutuhan_ruang, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pick($akhir->konsep_bangunan, optional($k)->konsep_bangunan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pick($akhir->arah_mata_angin, optional($k)->arah_mata_angin, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $pick($akhir->batas_keliling, optional($k)->batas_keliling, '-') }}</div>
          </div>
        </div>

        {{-- ==================== KANAN: Rincian & Berkas ==================== --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pick($akhir->budget, optional($k)->budget)) }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ optional($akhir->created_at)?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-' }}
            </div>
          </div>
          <div class="mb-2"><small class="text-muted">Keterangan</small>
            <div class="fw-semibold">{{ $pick($akhir->keterangan ?? null, optional($k)->keterangan, '-') }}</div>
          </div>

          <hr>

          <div class="mb-3">
            <h6 class="mb-2">Berkas</h6>

            @php $u = $fileUrl($pick($akhir->lembar_diskusi, optional($k)->lembar_diskusi)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($akhir->layout, optional($k)->layout)); @endphp
            <div class="mb-1"><small class="text-muted">Layout</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($akhir->desain_3d, optional($k)->desain_3d)); @endphp
            <div class="mb-1"><small class="text-muted">Desain 3D</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($akhir->rab_boq, optional($k)->rab_boq)); @endphp
            <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($akhir->lembar_survei, optional($k)->lembar_survei)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Survei</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($akhir->gambar_kerja, optional($k)->gambar_kerja)); @endphp
            <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($akhir->referensi ?? null, optional($k)->referensi)); @endphp
            <div class="mb-1"><small class="text-muted">Referensi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>
          </div>

          <hr>

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea class="form-control" rows="4" readonly>{{ $pick($akhir->catatan_survei, optional($k)->catatan_survei, '') }}</textarea>
        </div>
      </div>

      {{-- ==================== Tombol Aksi ==================== --}}
      <div class="mt-4 d-flex flex-wrap gap-2">
      <form id="form-serter-selesai"
            action="{{ route('studio.akhir.serter_selesai', $akhir->id) }}"
            method="POST" class="d-inline">
        @csrf
        <button type="button" id="btn-serter-selesai" class="btn btn-success" {{ $done ? 'disabled' : '' }}>
          <i class="bi bi-check2-circle me-1"></i> Selesaikan Serter
        </button>
      </form>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
  'use strict';
  const csrf        = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const redirectUrl = @json(route('studio.akhir'));

  const handlePost = async (form, successTitle, successText, redirect = redirectUrl) => {
    const resp = await fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: new URLSearchParams({ _token: csrf }), // <-- kirim body
      credentials: 'same-origin'
    });
    if (!resp.ok) throw new Error(await resp.text());

    const data = await resp.json().catch(() => ({}));

    await Swal.fire({
      icon: 'success',
      title: successTitle,
      text: data?.message || successText,
      timer: 1400,
      showConfirmButton: false,
      position: 'center'
    });

    window.location.href = redirect;
  };

  // === Selesaikan Serter ===
  const serterBtn  = document.getElementById('btn-serter-selesai');
  const serterForm = document.getElementById('form-serter-selesai');
  serterBtn?.addEventListener('click', async () => {
    if (serterBtn.disabled) return;

    const ok = await Swal.fire({
      icon: 'question',
      title: 'Selesaikan Serter?',
      text: 'Status akan diubah menjadi "Sudah Serter".',
      showCancelButton: true,
      confirmButtonText: 'Ya, selesaikan',
      cancelButtonText: 'Batal',
      reverseButtons: true
    });
    if (!ok.isConfirmed) return;

    serterBtn.disabled = true;
    const keep = serterBtn.innerHTML;
    serterBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    try {
      await handlePost(serterForm, 'Berhasil', 'Status telah diubah menjadi "Sudah Serter".');
    } catch (e) {
      // fallback submit biasa
      serterForm.submit();
    } finally {
      setTimeout(() => { if (!document.hidden) serterBtn.innerHTML = keep; }, 300);
    }
  });

})();
</script>
@endpush
