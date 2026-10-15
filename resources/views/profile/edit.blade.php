@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Akun')
@section('content')
<div class="row g-3" style="max-width:700px">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-person-fill-gear me-2"></i>Informasi Profil</div>
            <div class="card-body p-4">
                @if(session('status') === 'profile-updated')
                <div class="alert alert-success mb-3">Profil berhasil diperbarui.</div>
                @endif
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px">Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                        @error('name')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
                        @error('email')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-hijau rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-shield-lock-fill me-2"></i>Ubah Kata Sandi</div>
            <div class="card-body p-4">
                @if(session('status') === 'password-updated')
                <div class="alert alert-success mb-3">Kata sandi berhasil diperbarui.</div>
                @endif
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" autocomplete="current-password">
                        @error('current_password')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px">Kata Sandi Baru</label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password">
                        @error('password')<div class="text-danger mt-1" style="font-size:12px">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-hijau rounded-pill px-4">
                        <i class="bi bi-shield-lock-fill me-1"></i> Perbarui Kata Sandi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
