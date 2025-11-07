{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="page-content">
  <h4 class="fw-bold mb-3">Profil Saya</h4>

  @if(session('success'))  <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))    <div class="alert alert-danger">{{ session('error') }}</div>   @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
  @endif

  <div class="row g-3">
    {{-- Data Akun --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Data Akun</div>
        <div class="card-body">
          <form method="POST" action="{{ route('profile.update') }}" class="d-grid gap-3">
            @csrf @method('PATCH')

            <div>
              <label class="form-label">Nama</label>
              <input type="text" name="name" value="{{ old('name',$user->name) }}"
                     class="form-control @error('name') is-invalid @enderror">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Email</label>
              <input type="email" name="email" value="{{ old('email',$user->email) }}"
                     class="form-control @error('email') is-invalid @enderror">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="text-end">
              <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Ubah Password (dinonaktifkan jika route belum ada) --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header">Ubah Password</div>
        <div class="card-body">
          @php $hasPwdRoute = \Illuminate\Support\Facades\Route::has('profil.password.update'); @endphp
          <form method="POST" action="{{ $hasPwdRoute ? route('profil.password.update') : '#' }}" class="d-grid gap-3">
            @csrf
            @if($hasPwdRoute) @method('PUT') @endif

            <div>
              <label class="form-label">Password Saat Ini</label>
              <input type="password" name="current_password"
                     class="form-control @error('current_password') is-invalid @enderror" {{ $hasPwdRoute ? 'required' : '' }}>
              @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Password Baru</label>
              <input type="password" name="password"
                     class="form-control @error('password') is-invalid @enderror" {{ $hasPwdRoute ? 'required' : '' }}>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div>
              <label class="form-label">Konfirmasi Password Baru</label>
              <input type="password" name="password_confirmation" class="form-control" {{ $hasPwdRoute ? 'required' : '' }}>
            </div>

            <div class="text-end">
              <button class="btn btn-outline-primary" {{ $hasPwdRoute ? '' : 'disabled' }}>
                <i class="bi bi-shield-lock"></i> Update Password
              </button>
            </div>

            @unless($hasPwdRoute)
              <small class="text-muted">Route <code>profil.password.update</code> belum dibuat, tombol dinonaktifkan.</small>
            @endunless
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
