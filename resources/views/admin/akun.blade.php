@extends('layouts.app') {{-- atau layouts.mazer jika nama layout anda seperti itu --}}

@section('title', 'Manajemen Akun')

@section('content')
<div class="page-heading">
    <h3>Manajemen Akun</h3>
</div>

<div class="page-content">
    <section class="row">
        <div class="col-12">
            @if(session('success'))
                <div id="success-alert" class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow">
                <div class="card-header">
                    <h4 class="card-title">Daftar Akun Pengguna</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="akunTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Dibuat Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge bg-primary text-capitalize">{{ $user->role }}</span></td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="openResetModal({{ $user->id }}, '{{ $user->name }}')">Reset Password</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.akun.resetPassword') }}">
            @csrf
            <input type="hidden" name="user_id" id="resetUserId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password <span id="resetUserName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openResetModal(id, name) {
        document.getElementById('resetUserId').value = id;
        document.getElementById('resetUserName').textContent = '(' + name + ')';
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }

    document.addEventListener("DOMContentLoaded", function() {
        $('#akunTable').DataTable();

        const alert = document.getElementById('success-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.display = 'none';
            }, 3000);
        }
    });
</script>
@endpush
