@extends('layouts.app')

@section('content')

<div class="page-heading">
    <h3>Data Klien</h3>
    <p class="text-subtitle text-muted">Daftar semua klien yang telah terdaftar</p>
</div>

<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Simple Datatable</span>
            <a href="{{ route('klien.create') }}" class="btn btn-primary">
                + Tambah Klien
            </a>
        </div>

        <div class="card-body">
            <table class="table table-striped" id="table1">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Lokasi</th>
                        <th>Luas Bangunan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kliens as $klien)
                    <tr>
                        <td>{{ $klien->nama }}</td>
                        <td>{{ $klien->lokasi_lahan ?? '-' }}</td>
                        <td>{{ $klien->luas_bangunan ?? '-' }} m²</td>
                        <td>
                            @php
                                $status = rand(0,1) ? 'Active' : 'Inactive'; // Ganti sesuai field sebenarnya
                            @endphp
                            <span class="badge bg-{{ $status == 'Active' ? 'success' : 'danger' }}">{{ $status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('marketing.klien.edit', $klien->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('marketing.klien.destroy', $klien->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus klien ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada data klien.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('mazer/vendors/simple-datatables/simple-datatables.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.querySelector('#table1');
        if (table) {
            new simpleDatatables.DataTable(table, {
                searchable: true,
                fixedHeight: true
            });
        }
    });
</script>
@endsection
@if(session('success'))
    <div 
        id="popup-alert"
        class="fixed top-5 left-1/2 transform -translate-x-1/2 z-50 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg transition-opacity duration-500"
    >
        {{ session('success') }}
    </div>

    <script>
        setTimeout(() => {
            const alert = document.getElementById('popup-alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500); // Remove after fade out
            }
        }, 4000); // Delay 4 seconds
    </script>
@endif
@if(session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            html: '<p>{{ session('success') }}</p>',
            iconHtml: `
                <svg xmlns="http://www.w3.org/2000/svg" class="swal2-custom-icon" viewBox="0 0 24 24" width="60" height="60" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            `,
            customClass: {
                icon: 'no-background-icon',
                popup: 'swal2-custom-popup',
                title: 'swal2-custom-title',
                htmlContainer: 'swal2-custom-content'
            },
            timer: 4000,
            showConfirmButton: false,
            timerProgressBar: true,
        });
    </script>
@endif



