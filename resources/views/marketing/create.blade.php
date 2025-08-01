@extends('layouts.app')

@section('content')
<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h4>Tambah Data Klien</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('marketing.klien.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            {{-- KOLOM KIRI --}}
                            <div class="col-md-6">
                                @foreach ([
                                'nama',
                                'lokasi_lahan',
                                'luas_lahan',
                                'luas_bangunan',
                                'arah_mata_angin',
                                'budget',
                                'share_lokasi',
                                'biaya_survei',
                                'hoby_klien',
                                'jenis_jumlah_kendaraan',
                                'konsep_bangunan'
                            ] as $field)
                                    <div class="form-group mb-3">
                                        <label for="{{ $field }}">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                                        <input type="text" name="{{ $field }}" value="{{ old($field) }}" class="form-control @error($field) is-invalid @enderror">
                                        @error($field)
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach

                                <div class="form-group mb-3">
                                    <label for="tanggal_masuk">Tanggal Masuk</label>
                                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}" class="form-control @error('tanggal_masuk') is-invalid @enderror">
                                    @error('tanggal_masuk')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="estimasi_start">Estimasi Mulai</label>
                                    <input type="date" name="estimasi_start" value="{{ old('estimasi_start') }}" class="form-control @error('estimasi_start') is-invalid @enderror">
                                    @error('estimasi_start')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- KOLOM KANAN --}}
                            <div class="col-md-6">
                                @foreach ([
                                'kebutuhan_ruang',
                                'batas_keliling_bangunan',
                                'aktivitas_klien',
                                'prioritas_ruang',
                                'target_user_kos',
                                'fasilitas_umum_kos'
                            ] as $field)
                                    <div class="form-group mb-3">
                                        <label for="{{ $field }}">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                                        <input name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" rows="2">{{ old($field) }}</input>
                                        @error($field)
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach

                                {{-- FILE UPLOAD --}}
                                @foreach (['referensi', 'sertifikat', 'foto_eksisting', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field)
                                    <div class="form-group mb-3">
                                        <label for="{{ $field }}">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                                        <input type="file" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror">
                                        @error($field)
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
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

