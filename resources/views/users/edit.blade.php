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
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <strong>Terjadi kesalahan saat memperbarui data:</strong>
            </div>
            <ul class="mb-0 ps-3" style="font-size:13px">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required></div>
            <div class="mb-3"><label class="form-label fw-600" style="font-size:13px">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required></div>
            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px">Kata Sandi Baru <span class="text-muted fw-normal">(kosongkan jika tidak ingin mengubah)</span></label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="edit_password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Masukkan kata sandi baru jika ingin diubah" 
                           autocomplete="new-password" oninput="checkPasswordMatch()">
                    <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('edit_password', this)" tabindex="-1" title="Lihat/Sembunyikan kata sandi" aria-label="Lihat kata sandi">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                <div class="text-danger mt-1 small d-flex align-items-center gap-1">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                </div>
                @enderror
                <div class="form-text text-muted mt-1" style="font-size:12px;">
                    <i class="bi bi-info-circle text-success me-1"></i><strong>Petunjuk:</strong> Minimal 8 karakter, wajib kombinasi huruf dan angka (cth: <code>Pelaihari2026</code>).
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px">Konfirmasi Kata Sandi Baru</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" id="edit_password_confirmation" 
                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                           placeholder="Ketik ulang kata sandi baru di atas" 
                           autocomplete="new-password" oninput="checkPasswordMatch()">
                    <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('edit_password_confirmation', this)" tabindex="-1" title="Lihat/Sembunyikan kata sandi" aria-label="Lihat kata sandi">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="passwordMatchIndicator" class="mt-1 small" style="display:none;"></div>
                @error('password_confirmation')
                <div class="text-danger mt-1 small d-flex align-items-center gap-1">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                </div>
                @enderror
                <div class="form-text text-muted mt-1" style="font-size:12px;">
                    <i class="bi bi-info-circle text-success me-1"></i><strong>Petunjuk:</strong> Masukkan kembali kata sandi yang persis sama dengan kata sandi baru.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-600" style="font-size:13px">Peran <span class="text-danger">*</span></label>
                <select name="role" class="form-select" required>
                    <option value="user" {{ old('role',$user->role)=='user'?'selected':'' }}>Pengguna (User)</option>
                    <option value="admin" {{ old('role',$user->role)=='admin'?'selected':'' }}>Administrator</option>
                </select>
            </div>
            <div class="mb-4 form-check">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
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

@push('scripts')
<script>
function checkPasswordMatch() {
    const pwd = document.getElementById('edit_password');
    const confirm = document.getElementById('edit_password_confirmation');
    const indicator = document.getElementById('passwordMatchIndicator');
    if (!pwd || !confirm || !indicator) return;

    if (!confirm.value && !pwd.value) {
        indicator.style.display = 'none';
        return;
    }

    if (!confirm.value && pwd.value) {
        indicator.style.display = 'none';
        return;
    }

    indicator.style.display = 'block';
    if (pwd.value === confirm.value) {
        indicator.className = 'mt-1 small text-success d-flex align-items-center gap-1';
        indicator.innerHTML = '<i class="bi bi-check-circle-fill"></i> Konfirmasi kata sandi cocok.';
    } else {
        indicator.className = 'mt-1 small text-danger d-flex align-items-center gap-1';
        indicator.innerHTML = '<i class="bi bi-x-circle-fill"></i> Konfirmasi kata sandi belum sama dengan kata sandi baru.';
    }
}
</script>
@endpush
