{{-- resources/views/project/selesai/show.blade.php --}}
@extends('layouts.app')
@section('title','Detail Proyek Selesai')

@section('content')
<section class="section px-2 px-md-3">
  {{-- Heading --}}
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ route('project.selesai.index') }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Detail Proyek Selesai</h3>
  </div>

  @php
    use Illuminate\Support\Str;

    // ====== Sumber data utama ======
    // Controller bisa kirim $data atau $proyekselesaii; siapkan fallback lain juga
    $base = $data ?? $proyekselesaii ?? $proyek ?? $project ?? null;

    // Jika record selesai menyimpan relasi ke proyek berjalan, ambil untuk field lengkap
    $rel  = $base?->proyek ?? $base?->proyekjalan ?? $base?->proyek_jalan ?? $base?->proyekJalan ?? null;

    // Sumber field detail (prioritas relasi kalau ada)
    $src  = $rel ?: $base;

    // ====== Helpers ======
    $pick = function (...$vals) { foreach ($vals as $v) if (!blank($v)) return $v; return null; };

    // ambil nilai dari beberapa kemungkinan nama kolom/relasi
    $get = function ($obj, array $keys) {
      foreach ($keys as $k) {
        $v = data_get($obj, $k);
        if (!blank($v)) return $v;
      }
      return null;
    };

    $rupiah = fn($n) => $n !== null ? 'Rp. '.number_format((float)$n,0,',','.') : '-';

    // formatter tanggal aman untuk Carbon/string/null
    $fmt = function($v, string $format) {
      if ($v instanceof \Carbon\CarbonInterface) return $v->timezone('Asia/Jakarta')->format($format);
      if (!blank($v)) { try { return \Carbon\Carbon::parse($v)->timezone('Asia/Jakarta')->format($format);} catch(\Throwable $e){} }
      return '-';
    };

    $fileUrl = function (?string $path) {
      if (!$path) return null;
      if (Str::startsWith($path, ['http://','https://','/storage'])) return $path;
      return asset('storage/'.$path);
    };

    // ====== Mapping field umum dengan berbagai kemungkinan nama ======
    $nama            = $get($src, ['nama','nama_klien','client_name','klien.nama']);
    $alamatTinggal   = $get($src, ['alamat_tinggal','alamat','alamat_domisili','klien.alamat_tinggal']);

    $kodeProyek      = $get($src, ['kode_proyek','kode_project','kode']);
    $kelas           = $get($src, ['kelas','kelas_proyek']);
    $lokasiLahan     = $get($src, ['lokasi_lahan','lokasi','alamat_lahan']);
    $luasLahan       = $get($src, ['luas_lahan','luas_tanah']);
    $luasBangunan    = $get($src, ['luas_bangunan','luas_bangunan_rencana','luas']);
    $kebutuhanRuang  = $get($src, ['kebutuhan_ruang','kebutuhan']);
    $konsepBangunan  = $get($src, ['konsep_bangunan','konsep']);
    $arahMataAngin   = $get($src, ['arah_mata_angin','arah']);
    $batasKeliling   = $get($src, ['batas_keliling','batas']);

    $budget          = $get($src, ['budget','anggaran','biaya']);
    $tanggalMasuk    = $get($src, ['tanggal_masuk','tgl_masuk','created_at']);
    $tanggalMulai    = $get($src, ['tanggal_mulai','tgl_mulai','mulai_pembangunan']);
    // tanggal selesai cenderung tersimpan di record selesai; fallback ke src jika perlu
    $tanggalSelesai  = $get($base, ['tanggal_selesai','tgl_selesai','completed_at','finishing_at','updated_at'])
                    ?? $get($src,  ['tanggal_selesai','tgl_selesai','completed_at','finishing_at']);

    // ringkasan/keterangan
    $ringkasan       = $pick(data_get($base,'keterangan'), data_get($src,'keterangan'));

    // Progress proyek selesai = 100%
    $pg  = 100;
    $bar = 'bg-success';

    // id untuk update keterangan
    $rowId = $base?->id ?? $src?->id;
  @endphp

  <div class="card shadow">
    <div class="card-body">
      <div class="row g-4">
        {{-- ===================== KIRI: Data Klien & Proyek ===================== --}}
        <div class="col-lg-6">
          <h5 class="mb-3">Data Klien</h5>

          <div class="mb-2"><small class="text-muted">Nama</small>
            <div class="fw-semibold">{{ $nama ?? '-' }}</div>
          </div>

          <div class="mb-2"><small class="text-muted">Alamat Tinggal</small>
            <div class="fw-semibold">{{ $alamatTinggal ?? '-' }}</div>
          </div>

          <hr>
          <h6 class="mb-2">Data Proyek</h6>

          <div class="mb-2"><small class="text-muted">Kode Proyek</small>
            <div class="fw-semibold">{{ $kodeProyek ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kelas</small>
            <div class="fw-semibold">{{ $kelas ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Lokasi Lahan</small>
            <div class="fw-semibold">{{ $lokasiLahan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Lahan</small>
            <div class="fw-semibold">{{ $luasLahan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Luas Bangunan</small>
            <div class="fw-semibold">{{ $luasBangunan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Kebutuhan Ruang</small>
            <div class="fw-semibold">{{ $kebutuhanRuang ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Konsep Bangunan</small>
            <div class="fw-semibold">{{ $konsepBangunan ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Arah Mata Angin</small>
            <div class="fw-semibold">{{ $arahMataAngin ?? '-' }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Batas Keliling</small>
            <div class="fw-semibold">{{ $batasKeliling ?? '-' }}</div>
          </div>

          <hr>

          <div class="mb-2"><small class="text-muted">Budget</small>
            <div class="fw-semibold">{{ $rupiah($budget) }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Tanggal Masuk</small>
            <div class="fw-semibold">{{ $fmt($tanggalMasuk,'Y-m-d H:i') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Tanggal Mulai</small>
            <div class="fw-semibold">{{ $fmt($tanggalMulai,'d-m-Y') }}</div>
          </div>
          <div class="mb-2"><small class="text-muted">Tanggal Selesai</small>
            <div class="fw-semibold">{{ $fmt($tanggalSelesai,'d-m-Y') }}</div>
          </div>
        </div>

        {{-- ===================== KANAN: Status Selesai + Keterangan + Berkas ===================== --}}
        <div class="col-lg-6 d-flex flex-column align-items-stretch gap-3">

          {{-- STATUS SELESAI --}}
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Status Pembangunan</h6>
                <span class="badge badge-pill-md bg-success">Selesai</span>
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
                <span>Mulai: {{ $fmt($tanggalMulai,'d-m-Y') }}</span>
                <span>Selesai: {{ $fmt($tanggalSelesai,'d-m-Y') }}</span>
              </div>
            </div>
          </div>

          {{-- KETERANGAN (Edit ⇄ Simpan) --}}
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <small class="text-muted">Keterangan</small>

                {{-- type="button" supaya tidak submit otomatis --}}
                <button type="button"
                        id="btnEditKet"
                        class="btn btn-outline-primary btn-sm"
                        data-state="view">Edit</button>
              </div>

              <form id="formKeterangan"
                    action="{{ $rowId ? route('project.selesai.keterangan.update', $rowId) : '#' }}"
                    method="POST">
                @csrf
                @method('PATCH')

                <textarea id="ketField"
                          name="keterangan"
                          class="form-control bg-body-secondary"
                          rows="4"
                          readonly>{{ old('keterangan', (string)($ringkasan ?? '')) }}</textarea>
              </form>
            </div>
          </div>

          {{-- BERKAS --}}
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h5 class="mb-3">Berkas</h5>

              @php $u = $fileUrl($get($src, ['lembar_diskusi','lembar_diskusi_path'])); @endphp
              <div class="mb-1"><small class="text-muted">Lembar Diskusi</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($get($src, ['layout','layout_path'])); @endphp
              <div class="mb-1"><small class="text-muted">Layout</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($get($src, ['desain_3d','desain3d','desain_3d_path'])); @endphp
              <div class="mb-1"><small class="text-muted">Desain 3D</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($get($src, ['rab_boq','rab','boq','rab_boq_path'])); @endphp
              <div class="mb-1"><small class="text-muted">RAB / BOQ</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($get($src, ['lembar_survei','lembar_survei_path'])); @endphp
              <div class="mb-1"><small class="text-muted">Lembar Survei</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-primary">Lihat PDF</a>@else-@endif</div>
              </div>

              @php $u = $fileUrl($get($src, ['gambar_kerja','gambar_kerja_path'])); @endphp
              <div class="mb-1"><small class="text-muted">Gambar Kerja</small>
                <div class="fw-semibold">@if($u)<a href="{{ $u }}" target="_blank" class="badge bg-info text-dark">Lihat file</a>@else-@endif</div>
              </div>
            </div>
          </div>

        </div>{{-- /col kanan --}}
      </div>{{-- /row --}}
    </div>
  </div>
</section>
@endsection

@push('styles')
<style>
  .progress{ background: rgba(0,0,0,.08); }
  .progress .progress-bar{ transition: width .6s ease; }
  .badge-pill-md{
    border-radius: 999px;
    font-weight: 600;
    font-size: .75rem;
    padding: .35rem .6rem;
    display: inline-flex;
    align-items: center;
    line-height: 1;
  }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn   = document.getElementById('btnEditKet');
  const field = document.getElementById('ketField');
  const form  = document.getElementById('formKeterangan');

  if (!btn || !field || !form) return;

  btn.addEventListener('click', () => {
    const mode = btn.dataset.state || 'view';

    if (mode === 'view') {
      // Masuk mode edit
      field.readOnly = false;
      field.classList.remove('bg-body-secondary');
      field.focus();
      const v = field.value; field.value = ''; field.value = v;

      btn.dataset.state = 'edit';
      btn.textContent = 'Simpan';
      btn.classList.remove('btn-outline-primary');
      btn.classList.add('btn-primary');
    } else {
      // Simpan → submit PATCH
      form.requestSubmit();
    }
  });
});
</script>
@endpush
