@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Data Pengguna')
@section('content')
<div class="mb-3">
    <a href="{{ route('users.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Pengguna
    </a>
</div>
<div class="card" style="max-width:560px">
    <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Edit Pengguna: {{ $user->name }}</div>
    <div class="card-body p-4">
        @if($errors->any())<div class="alert alert-danger mb-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li style="font-size:13px">{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Kata Sandi Baru <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                <input type="password" name="password" class="form-control"></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" class="form-control"></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Peran <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="user" {{ old('role',$user->role)=='user'?'selected':'' }}>Pengguna (User)</option>
                    <option value="admin" {{ old('role',$user->role)=='admin'?'selected':'' }}>Administrator</option>
                </select></div>
            <div class="mb-4 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                       {{ $user->is_active ? 'checked' : '' }}>
                <label class="form-check-label fw-600" for="isActive" style="font-size:13px">Akun Aktif</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-hijau rounded-pill px-4"><i class="bi bi-check-lg me-1"></i> Perbarui Data</button>
                <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4 border">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
