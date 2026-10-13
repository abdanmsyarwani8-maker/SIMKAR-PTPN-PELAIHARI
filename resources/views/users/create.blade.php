@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('page-title', 'Tambah Pengguna Baru')
@section('content')
<div class="mb-3"><a href="{{ route('users.index') }}" class="btn btn-sm btn-light rounded-pill"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
<div class="card" style="max-width:560px">
    <div class="card-header"><i class="bi bi-person-plus-fill me-2 text-success"></i>Form Tambah Pengguna</div>
    <div class="card-body p-4">
        @if($errors->any())<div class="alert alert-danger mb-3"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li style="font-size:13px">{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Kata Sandi <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
                <div class="form-text">Min. 8 karakter, kombinasi huruf dan angka</div></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required></div>
            <div class="mb-4"><label class="form-label fw-600" style="font-size:13px">Peran <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="user" {{ old('role')=='user'?'selected':'' }}>Pengguna (User)</option>
                    <option value="admin" {{ old('role')=='admin'?'selected':'' }}>Administrator</option>
                </select></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-hijau rounded-pill px-4"><i class="bi bi-check me-1"></i>Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
