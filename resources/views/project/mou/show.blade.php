{{-- resources/views/studio/mou/show.blade.php --}}
@extends('layouts.app')
@section('title','Detail MOU')

@section('content')
<section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail MOU</h3>
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
    $k = $mou->klien ?? null;
  @endphp

  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">

        {{-- KIRI --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $pick($mou->nama, optional($k)->nama, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Email</small>
            <div class="fw-semibold">{{ $pick($mou->email ?? null, optional($k)->email, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">No HP</small>
            <div class="fw-semibold">{{ $pick($mou->no_hp ?? null, optional($k)->no_hp, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $pick($mou->alamat_tinggal, optional($k)->alamat_tinggal, '-') }}</div>
          </div>

          <hr>

          <h6 class="mb-2">Data Proyek</h6>
          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $pick($mou->kode_proyek, optional($k)->kode_proyek, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $pick($mou->kelas, optional($k)->kelas, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Lokasi Proyek</small>
            <div class="fw-semibold">{{ $pick($mou->lokasi_lahan, optional($k)->lokasi_lahan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $pick($mou->luas_lahan, optional($k)->luas_lahan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $pick($mou->luas_bangunan, optional($k)->luas_bangunan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $pick($mou->kebutuhan_ruang, optional($k)->kebutuhan_ruang, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $pick($mou->konsep_bangunan, optional($k)->konsep_bangunan, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $pick($mou->arah_mata_angin, optional($k)->arah_mata_angin, '-') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $pick($mou->batas_keliling, optional($k)->batas_keliling, '-') }}</div>
          </div>
        </div>

        {{-- KANAN --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Rincian Proyek</h5>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($pick($mou->budget, optional($k)->budget)) }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">
              {{ optional($mou->created_at)?->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-' }}
            </div>
          </div>

          <div class="mb-2"><small class="text-muted">Keterangan</small>
            <div class="fw-semibold">{{ $pick($mou->keterangan ?? null, optional($k)->keterangan, '-') }}</div>
          </div>

          <hr>

          <div class="mb-3">
            <h6 class="mb-2">Berkas</h6>

            @php $u = $fileUrl($pick($mou->lembar_diskusi, optional($k)->lembar_diskusi)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($mou->layout, optional($k)->layout)); @endphp
            <div class="mb-1"><small class="text-muted">Layout</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($mou->desain_3d, optional($k)->desain_3d)); @endphp
            <div class="mb-1"><small class="text-muted">Desain 3D</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($mou->rab_boq, optional($k)->rab_boq)); @endphp
            <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($mou->lembar_survei, optional($k)->lembar_survei)); @endphp
            <div class="mb-1"><small class="text-muted">Lembar Survei</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($mou->gambar_kerja, optional($k)->gambar_kerja)); @endphp
            <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>

            @php $u = $fileUrl($pick($mou->referensi ?? null, optional($k)->referensi)); @endphp
            <div class="mb-1"><small class="text-muted">Referensi</small>
              <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
            </div>
          </div>

          <hr>

          <small class="text-muted d-block mb-1">Catatan saat survei</small>
          <textarea class="form-control" rows="4" readonly>{{ $pick($mou->catatan_survei, optional($k)->catatan_survei, '') }}</textarea>
        </div>
      </div>

{{-- Tombol Aksi --}}
<div class="mt-4 d-flex flex-wrap gap-2">
  {{-- Lanjut ke Proyek Berjalan (MOU -> Proyek Jalan) --}}
  <form id="form-to-proyek"
        action="{{ route('project.mou.to_proyekjalan', $mou->id) }}"
        method="POST" class="d-inline">
    @csrf
    {{-- nilai dari popup akan ditaruh di sini --}}
    <input type="hidden" name="tanggal_mulai" id="tanggal_mulai_hidden">
    <button type="button" id="btn-to-proyek" class="btn btn-success">
      <i class="bi bi-play-circle me-1"></i> Lanjut Pembangunan
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
  const backUrl = @json(route('project.mou.index')); // setelah berhasil balik ke daftar MOU

  // ========== Mulai Proyek (minta tanggal mulai) ==========
document.getElementById('btn-to-proyek')?.addEventListener('click', async () => {
  const { isConfirmed, value } = await Swal.fire({
    icon: 'question',
    title: 'Lanjut ke Pembangunan ?',
    html: `
      <div style="text-align:center">
        <div class="mb-2">Kapan proyek mulai pembangunan :</div>
        <input type="date" id="tgl-mulai" class="swal2-input" />
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Mulai',
    cancelButtonText: 'Batal',
    reverseButtons: true,
    preConfirm: () => {
      const v = (document.getElementById('tgl-mulai')?.value || '').trim();
      if (!v) { Swal.showValidationMessage('Tanggal mulai wajib diisi'); }
      return v;
    }
  });

  if (!isConfirmed) return;

  const form = document.getElementById('form-to-proyek');
  try {
    const resp = await fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
      },
      body: new URLSearchParams({ tanggal_mulai: value }), // <-- KIRIM TANGGAL
      credentials: 'same-origin'
    });
    if (!resp.ok) throw new Error(await resp.text());
    await Swal.fire({ icon:'success', title:'Dipindahkan', timer:1200, showConfirmButton:false });
    window.location.href = @json(route('project.mou.index'));
  } catch (e) {
    // fallback submit biasa
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'tanggal_mulai';
    hidden.value = value;
    form.appendChild(hidden);
    form.submit();
  }
});

})();
</script>
@endpush
