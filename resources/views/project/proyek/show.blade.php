{{-- resources/views/project/proyek/show.blade.php --}}
@extends('layouts.app')
@section('title','Detail Proyek Berjalan')

@section('content')
<section class="section px-2 px-md-3">
  {{-- Heading --}}
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ route('project.proyek.index') }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail Proyek Berjalan</h3>
  </div>

  @php
    use Illuminate\Support\Str;

    // helper kecil
    $pick = function (...$vals) { foreach ($vals as $v) if (!blank($v)) return $v; return null; };
    $rupiah = fn($n) => $n !== null ? 'Rp. '.number_format($n,0,',','.') : '-';
    $fileUrl = function (?string $path) {
      if (!$path) return null;
      if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
      return asset('storage/'.$path);
    };

    $pj = $proyek;                                                 // alias
    $pg = max(0, min(100, (int)($pj->status_progres ?? 0)));       // clamp 0..100
    $bar = $pg < 25 ? 'bg-danger'
         : ($pg < 50 ? 'bg-warning'
         : ($pg < 75 ? 'bg-info' : 'bg-success'));
  @endphp

  {{-- Card utama --}}
  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">
        {{-- ===================== KIRI: Data Klien & Proyek ===================== --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $pick($pj->nama, optional($pj->klien)->nama, '-') }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick($pj->alamat_tinggal, optional($pj->klien)->alamat_tinggal, '-') }}</div>
          </div>

          <hr>
          <h6 class="mb-2">Data Proyek</h6>

          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $pj->kode_proyek ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $pj->kelas ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Lokasi Lahan</small>
            <div class="fw-semibold">{{ $pj->lokasi_lahan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $pj->luas_lahan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pj->luas_bangunan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pj->kebutuhan_ruang ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pj->konsep_bangunan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pj->arah_mata_angin ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $pj->batas_keliling ?? '-' }}</div>
          </div>

          <hr>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pj->budget) }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ $pj->tanggal_masuk?->timezone('Asia/Jakarta')?->format('Y-m-d H:i') ?? '-' }}
            </div>
          </div>
          <div class="mb-2"><small class="text-muted">Tanggal Mulai</small>
            <div class="fw-semibold">
              @php
                $mulai = $pj->tanggal_mulai ? \Carbon\Carbon::parse($pj->tanggal_mulai) : null;
              @endphp
              {{ $mulai ? $mulai->format('d-m-Y') : '-' }}
            </div>
          </div>
        </div>

        {{-- ====== KANAN: Progres + Berkas + Catatan ====== --}}
        <div class="col-lg-6 d-flex flex-column align-items-stretch gap-3">

          {{-- PROGRES --}}
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Progres Pembangunan</h6>

                <div class="d-flex align-items-center gap-2">
                  {{-- tombol edit, sama ukuran dengan badge persen --}}
                  <a href="{{ route('project.proyek.edit', $pj->id) }}"
                     class="badge badge-pill-md bg-warning text-dark text-decoration-none"
                     title="Edit progres">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <span class="badge badge-pill-md {{ $bar }}">{{ $pg }}%</span>
                </div>
              </div>

              <div class="position-relative">
                <div class="progress rounded-pill" style="height:14px;">
                  <div class="progress-bar {{ $bar }}"
                       role="progressbar"
                       style="width: {{ $pg }}%;"
                       aria-valuenow="{{ $pg }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="position-absolute top-50 start-50 translate-middle small fw-semibold">
                  {{ $pg }}%
                </div>
              </div>

              <div class="d-flex justify-content-between small text-muted mt-2">
                <span>Mulai: {{ $pj->tanggal_mulai?->format('d-m-Y') ?? '-' }}</span>
                <span>Terakhir update: {{ $pj->updated_at?->format('d-m-Y') ?? '-' }}</span>
              </div>

              <div class="mt-3">
                <small class="text-muted d-block mb-1">Keterangan Progres</small>
                <div class="p-2 rounded border bg-dark-subtle text-light-subtle">
                  {{ $pj->keterangan ?: '-' }}
                </div>
              </div>
            </div>
          </div>

          {{-- BERKAS + CATATAN --}}
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5 class="mb-3">Berkas</h5>

              @php $u = $fileUrl($pj->lembar_diskusi); @endphp
              <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($pj->layout); @endphp
              <div class="mb-1"><small class="text-muted">Layout</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($pj->desain_3d); @endphp
              <div class="mb-1"><small class="text-muted">Desain 3D</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($pj->rab_boq); @endphp
              <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($pj->lembar_survei); @endphp
              <div class="mb-1"><small class="text-muted">Lembar Survei</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($pj->gambar_kerja); @endphp
              <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              <hr class="my-3">

              <small class="text-muted d-block mb-2">Catatan saat survei</small>
              <textarea class="form-control" rows="4" readonly>{{ $pj->catatan_survei ?? '' }}</textarea>
            </div>
          </div>

        </div>

        {{-- ===== Actions bawah kiri ===== --}}
        <div class="col-12 mt-1">
          <form id="form-proyek-selesai"
                action="{{ route('project.proyek.selesai', $pj->id) }}"
                method="POST" class="d-inline">
            @csrf
            <button type="button" id="btn-proyek-selesai" class="btn btn-success">
              <i class="bi bi-check2-circle me-1"></i> Proyek Selesai
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>
@endsection

@push('styles')
<style>
  .progress{ background: rgba(255,255,255,.08); }
  .progress .progress-bar{ transition: width .6s ease; }
  .badge-pill-md{ border-radius:999px; font-weight:600; font-size:.75rem; padding:.35rem .6rem; display:inline-flex; align-items:center; line-height:1; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.getElementById('btn-proyek-selesai')?.addEventListener('click', function(){
    Swal.fire({
      position: 'center',
      icon: 'question',
      title: 'Pindahkan ke "Proyek Selesai"?',
      text: 'Aksi ini akan menghapus data dari daftar Proyek Berjalan.',
      showCancelButton: true,
      confirmButtonText: 'Ya, pindahkan',
      cancelButtonText: 'Batal',
      reverseButtons: true,
      focusCancel: true,
      customClass: { popup: 'swal2-custom-popup' },
      buttonsStyling: false,
      didOpen: (el) => {
        el.querySelector('.swal2-confirm').className = 'btn btn-primary me-2';
        el.querySelector('.swal2-cancel').className  = 'btn btn-outline-secondary';
      }
    }).then((res) => {
      if (res.isConfirmed) document.getElementById('form-proyek-selesai').submit();
    });
  });
</script>
@endpush
