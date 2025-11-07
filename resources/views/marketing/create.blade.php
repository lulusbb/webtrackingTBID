@extends('layouts.app')

@section('content')
<div class="page-content">
    <section class="section px-2 px-md-3">
  <div class="page-heading mb-4 d-flex align-items-center gap-2">
    <a href="{{ url()->previous() }}" class="text-decoration-none">
      <i class="bi bi-arrow-left text-primary fs-4"></i>
    </a>
    <h3 class="mb-0">Tambah Klien Baru</h3>
  </div>
    <section class="row">
        <div class="col-12">
            <div class="card shadow">

                <div class="card-body">
                    <form action="{{ route('marketing.klien.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="row">
{{-- KOLOM KIRI --}}
<div class="col-md-6">

  @foreach ([
      'nama',
      'email',
      'no_hp',
      'alamat_tinggal',
      'lokasi_lahan',
      'luas_lahan',
      'luas_bangunan',
      'arah_mata_angin',
      'budget',
      'share_lokasi',
      'biaya_survei',
      'hoby' => 'hoby',
      'kendaraan' => 'jenis_jumlah_kendaraan',
      'konsep_bangunan',
      'kode_proyek',   {{-- <- kita tangani khusus di bawah --}}
  ] as $field => $label)

    @php
      $fieldName  = is_int($field) ? $label : $field;
      $fieldLabel = ucwords(str_replace('_', ' ', is_int($field) ? $label : $field));
    @endphp

    @if ($fieldName === 'kode_proyek')
      {{-- ===== KODE PROYEK dengan pilihan BA, RE, DE, IN (datalist) ===== --}}
      <div class="form-group mb-3">
        <label for="kode_proyek">Kode Proyek</label>
        <input
          type="text"
          id="kode_proyek"
          name="kode_proyek"
          value="{{ old('kode_proyek') }}"
          list="kodeProyekList"
          minlength="2"
          maxlength="2"
          pattern="BA|RE|DE|IN"
          class="form-control @error('kode_proyek') is-invalid @enderror"
          placeholder="Pilih: BA / RE / DE / IN"
        >
        <datalist id="kodeProyekList">
          <option value="BA">Bangun Baru</option>
          <option value="RE">Renovasi</option>
          <option value="DE">Desain</option>
          <option value="IN">Interior</option>
        </datalist>
        @error('kode_proyek') <div class="text-danger">{{ $message }}</div> @enderror
        <small class="text-muted">Hanya: BA, RE, DE, IN.</small>
      </div>
    @else
      {{-- Input biasa --}}
      <div class="form-group mb-3">
        <label for="{{ $fieldName }}">{{ $fieldLabel }}</label>
        <input
          type="text"
          id="{{ $fieldName }}"
          name="{{ $fieldName }}"
          value="{{ old($fieldName) }}"
          class="form-control @error($fieldName) is-invalid @enderror"
        >
        @error($fieldName) <div class="text-danger">{{ $message }}</div> @enderror
      </div>
    @endif

  @endforeach

  <div class="form-group mb-3">
    <label for="tanggal_masuk">Tanggal Masuk</label>
    <input
      type="date"
      id="tanggal_masuk"
      name="tanggal_masuk"
      value="{{ old('tanggal_masuk') }}"
      class="form-control @error('tanggal_masuk') is-invalid @enderror"
    >
    @error('tanggal_masuk') <div class="text-danger">{{ $message }}</div> @enderror
  </div>

  <div class="form-group mb-3">
    <label for="estimasi_start">Estimasi Mulai</label>
    <input
      type="date"
      id="estimasi_start"
      name="estimasi_start"
      value="{{ old('estimasi_start') }}"
      class="form-control @error('estimasi_start') is-invalid @enderror"
    >
    @error('estimasi_start') <div class="text-danger">{{ $message }}</div> @enderror
  </div>

</div>


                                {{-- KOLOM KANAN --}}
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="keterangan">Keterangan</label>
                                        <select id="keterangan" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">
                                            <option value="">-- Pilih Keterangan --</option>
                                            @foreach(['Survei','Perlu FollowUp','Penawaran RAB','Budget tidak cukup','Diskusi Keluarga','Belum Siap','Parsial'] as $opt)
                                                <option value="{{ $opt }}" {{ old('keterangan')===$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @error('keterangan') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="kelas">Kelas</label>
                                        <select id="kelas" name="kelas" class="form-control @error('kelas') is-invalid @enderror">
                                            <option value="">-- Pilih Kelas --</option>
                                            @foreach(['A','B','C','D'] as $kelas)
                                                <option value="{{ $kelas }}" {{ old('kelas')===$kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                                            @endforeach
                                        </select>
                                        @error('kelas') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    @foreach ([
                                        
                                        'kebutuhan_ruang',
                                        'batas_keliling' => 'batas_keliling_bangunan',
                                        'aktivitas'      => 'aktivitas_klien',
                                        'prioritas_ruang',
                                        'target_user_kos',
                                        'fasilitas_kos'  => 'fasilitas_umum_kos',
                                    ] as $field => $label)
                                        @php
                                            $fieldName  = is_int($field) ? $label : $field;
                                            $fieldLabel = ucwords(str_replace('_', ' ', is_int($field) ? $label : $field));
                                        @endphp
                                        <div class="form-group mb-3">
                                            <label for="{{ $fieldName }}">{{ $fieldLabel }}</label>
                                            <input
                                                type="text"
                                                id="{{ $fieldName }}"
                                                name="{{ $fieldName }}"
                                                value="{{ old($fieldName) }}"
                                                class="form-control @error($fieldName) is-invalid @enderror"
                                            >
                                            @error($fieldName) <div class="text-danger">{{ $message }}</div> @enderror
                                        </div>
                                    @endforeach

                                    {{-- ===== Upload files dengan helper text dinamis ===== --}}
                                        @php
                                            // mapping tipe untuk helper & "accept"
                                            $fileTypeMap = [
                                                'lembar_diskusi' => 'img', // <= BARU (di atas Referensi)
                                                'referensi'      => 'img',
                                                'sertifikat'     => 'img',
                                                'foto_eksisting' => 'img',
                                                'layout'         => 'pdf',
                                                'desain_3d'      => 'pdf',
                                                'rab_boq'        => 'pdf',
                                                'gambar_kerja'   => 'pdf',
                                            ];

                                            // Urutan tampil (lembar_diskusi ditempatkan sebelum referensi)
                                            $uploadFields = [
                                                'lembar_diskusi' => 'Lembar Diskusi',
                                                'referensi'      => 'Referensi',
                                                'sertifikat'     => 'Sertifikat',
                                                'foto_eksisting' => 'Foto Eksisting',
                                                'layout'         => 'Layout',
                                                'desain_3d'      => 'Desain 3D',
                                                'rab_boq'        => 'RAB/BOQ',
                                                'gambar_kerja'   => 'Gambar Kerja',
                                            ];
                                        @endphp

                                    @foreach ($uploadFields as $fieldName => $fieldLabel)
                                    @php
                                        $type   = $fileTypeMap[$fieldName] ?? 'img';
                                        $accept = $type === 'pdf' ? 'application/pdf' : 'image/*';
                                        $helper = $type === 'pdf' ? 'Hanya PDF' : 'Hanya gambar';
                                    @endphp
                                    <div class="form-group mb-3">
                                        <label for="{{ $fieldName }}">{{ $fieldLabel }}</label>
                                        <input
                                            type="file"
                                            id="{{ $fieldName }}"
                                            name="{{ $fieldName }}"
                                            accept="{{ $accept }}"
                                            class="form-control @error($fieldName) is-invalid @enderror"
                                        >
                                        <div class="form-text">{{ $helper }}</div>
                                        @error($fieldName) <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('marketing.klien.index') }}" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                position: 'center'
            });
        });
    </script>
@endif

