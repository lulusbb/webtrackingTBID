@extends('layouts.app')
@section('title','Edit 3D Exterior')

@section('content')
<div class="page-content">
  @php
    use Illuminate\Support\Str;
    $prev = url()->previous();
    $backUrl = $prev && $prev !== url()->current() ? $prev : route('studio.exteriors.index');

    $ketOps = ['Survei','Perlu FollowUp','Penawaran RAB','Budget tidak cukup','Diskusi Keluarga','Belum Siap','Parsial'];

    $tm = optional($exterior->tanggal_masuk)->format('Y-m-d');
    $es = optional($exterior->estimasi_start)->format('Y-m-d');
  @endphp

  <div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ $backUrl }}" class="text-decoration-none">
      <i class="bi bi-arrow-left fs-4 text-primary"></i>
    </a>
    <h3 class="mb-0">Edit Data Klien</h3>
  </div>

  <div class="card shadow">
    <div class="card-header">
      <h5 class="mb-0">Form Edit</h5>
    </div>

    <div class="card-body">
      <form id="form-exterior-edit" action="{{ route('studio.exteriors.update', $exterior->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row">
          {{-- ========= KOLOM KIRI ========= --}}
          <div class="col-md-6">
            @foreach ([
              'nama'            => 'Nama',
              'email'           => 'Email',
              'no_hp'           => 'No HP',
              'alamat_tinggal'  => 'Alamat Tinggal',
              'lokasi_lahan'    => 'Lokasi Lahan',
              'luas_lahan'      => 'Luas Lahan',
              'luas_bangunan'   => 'Luas Bangunan',
              'arah_mata_angin' => 'Arah Mata Angin',
              'budget'          => 'Budget',
              'share_lokasi'    => 'Share Lokasi',
              'biaya_survei'    => 'Biaya Survei',

              'kendaraan'       => 'Kendaraan',
              'konsep_bangunan' => 'Konsep Bangunan',
              'kebutuhan_ruang' => 'Kebutuhan Ruang',
              'batas_keliling'  => 'Batas Keliling',

              'prioritas_ruang' => 'Prioritas Ruang',
              'target_user_kos' => 'Target User Kos',
              'fasilitas_kos'   => 'Fasilitas Kos',
            ] as $name => $label)
              <div class="form-group mb-3">
                <label for="{{ $name }}">{{ $label }}</label>
                <input type="text" id="{{ $name }}" name="{{ $name }}"
                       value="{{ old($name, $exterior->$name) }}"
                       class="form-control @error($name) is-invalid @enderror">
                @error($name) <div class="text-danger">{{ $message }}</div> @enderror
              </div>
            @endforeach

          </div>

          {{-- ========= KOLOM KANAN ========= --}}
          <div class="col-md-6">

            {{-- Kelas --}}
            <div class="form-group mb-3">
              <label for="kelas">Kelas</label>
              <select id="kelas" name="kelas" class="form-control @error('kelas') is-invalid @enderror">
                <option value="">-- Pilih Kelas --</option>
                @foreach (['A','B','C','D'] as $opt)
                  <option value="{{ $opt }}" {{ old('kelas', $exterior->kelas) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
              </select>
              @error('kelas') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- Kode Proyek --}}
            <div class="form-group mb-3">
              <label for="kode_proyek">Kode Proyek</label>
              <input type="text" id="kode_proyek" name="kode_proyek"
                     value="{{ old('kode_proyek', $exterior->kode_proyek) }}"
                     list="kodeProyekList" minlength="2" maxlength="2"
                     pattern="BA|RE|DE|IN" oninput="this.value=this.value.toUpperCase()"
                     class="form-control @error('kode_proyek') is-invalid @enderror"
                     placeholder="Pilih: BA / RE / DE / IN">
              <datalist id="kodeProyekList">
                <option value="BA">Bangun Baru</option>
                <option value="RE">Renovasi</option>
                <option value="DE">Desain</option>
                <option value="IN">Interior</option>
              </datalist>
              <small class="text-muted">Hanya: BA, RE, DE, IN.</small>
              @error('kode_proyek') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- Keterangan --}}
            <div class="form-group mb-3">
              <label for="keterangan">Keterangan</label>
              <select id="keterangan" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">
                <option value="">-- Pilih Keterangan --</option>
                @foreach($ketOps as $opt)
                  <option value="{{ $opt }}" {{ old('keterangan', $exterior->keterangan) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
              </select>
              @error('keterangan') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            {{-- ===== Upload files ===== --}}
            @php
              $fileTypeMap = [
                'lembar_diskusi' => 'img',
                'referensi'      => 'img',
                'sertifikat'     => 'img',
                'foto_eksisting' => 'img',
                'layout'         => 'pdf',
                'desain_3d'      => 'pdf',
                'rab_boq'        => 'pdf',
                'gambar_kerja'   => 'pdf',
                'lembar_survei'  => 'pdf',
              ];
              $uploadFields = [
                'lembar_diskusi' => 'Lembar Diskusi',
                'referensi'      => 'Referensi',
                'sertifikat'     => 'Sertifikat',
                'foto_eksisting' => 'Foto Eksisting',
                'layout'         => 'Layout (PDF)',
                'desain_3d'      => 'Desain 3D (PDF)',
                'rab_boq'        => 'RAB/BOQ (PDF)',
                'gambar_kerja'   => 'Gambar Kerja (PDF)',
                'lembar_survei'  => 'Lembar Survei (PDF)',
              ];
            @endphp

            @foreach ($uploadFields as $fieldName => $fieldLabel)
              @php
                $type   = $fileTypeMap[$fieldName] ?? 'img';
                $accept = $type === 'pdf' ? 'application/pdf' : 'image/*';
                $helper = $type === 'pdf' ? 'Hanya PDF' : 'Hanya gambar';
                $cur = $exterior->$fieldName ?? null;
              @endphp
              <div class="form-group mb-3">
                <label for="{{ $fieldName }}">{{ $fieldLabel }}</label>
                <input type="file" id="{{ $fieldName }}" name="{{ $fieldName }}" accept="{{ $accept }}"
                       class="form-control @error($fieldName) is-invalid @enderror">
                @if($cur)
                  <small class="text-muted d-block mt-1">
                    File saat ini:
                    <a href="{{ Str::startsWith($cur,['http://','https://','/storage']) ? $cur : asset('storage/'.$cur) }}" target="_blank" class="text-primary">Lihat</a>
                  </small>
                @endif
                <div class="form-text">{{ $helper }}</div>
                @error($fieldName) <div class="text-danger">{{ $message }}</div> @enderror
              </div>
            @endforeach

          </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
          <a href="{{ $backUrl }}" class="btn btn-secondary me-2">Batal</a>
          <button type="submit" class="btn btn-primary" id="btn-save-exterior">
            <i class="bi bi-save me-1"></i> Perbarui
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(() => {
  const form = document.getElementById('form-exterior-edit');
  const btn  = document.getElementById('btn-save-exterior');

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const ok = await Swal.fire({
      icon: 'question',
      title: 'Simpan perubahan?',
      showCancelButton: true,
      confirmButtonText: 'Ya, simpan',
      cancelButtonText: 'Batal',
      reverseButtons: true
    });
    if (!ok.isConfirmed) return;

    const old = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    form.submit();
    setTimeout(()=>{ btn.disabled=false; btn.innerHTML=old; }, 3500);
  });
})();
</script>
@endpush
