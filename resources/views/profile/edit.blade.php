@extends('layouts.app')
@section('title', 'Profil Akun')
@section('page-title', 'Profil Akun')

@push('styles')
<style>
.profile-avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: 4px;
}
.profile-avatar-circle {
    width: 86px;
    height: 86px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--hijau-tua) 0%, var(--hijau-muda) 100%);
    color: #fff;
    font-size: 32px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(26, 92, 46, 0.25);
    border: 3px solid #fff;
    overflow: hidden;
}
.profile-avatar-circle img {
    width: 100%; height: 100%; object-fit: cover;
}
.avatar-edit-btn {
    position: absolute;
    bottom: 2px; right: 2px;
    width: 26px; height: 26px;
    border-radius: 50%;
    background: var(--hijau-tua);
    color: #fff;
    font-size: 12px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    border: 2px solid #fff;
    transition: background 0.15s;
    z-index: 2;
}
.avatar-edit-btn:hover { background: var(--hijau-med); }
.profile-meta-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px dashed #e4ede7;
    font-size: 13px;
}
.profile-meta-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.input-icon-group {
    position: relative;
    display: flex;
    align-items: center;
}
.input-icon-group > i.input-icon-start {
    position: absolute;
    left: 14px;
    color: #8da495;
    font-size: 16px;
    pointer-events: none;
    z-index: 5;
}
.input-icon-group .form-control {
    padding-left: 42px;
    padding-right: 42px;
    height: 44px;
}
.input-icon-group .btn-toggle-pwd {
    position: absolute;
    right: 6px;
    background: none;
    border: none;
    color: #8da495;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 16px;
    z-index: 5;
    transition: color 0.15s ease;
}
.input-icon-group .btn-toggle-pwd:hover {
    color: var(--hijau-tua);
}
</style>
@endpush

@section('content')
<div class="row g-4">
    {{-- Kolom Kiri: Ringkasan Akun --}}
    <div class="col-lg-4">
        {{-- Card Identitas Profil --}}
        <div class="card mb-4">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar-circle" id="avatarPreviewWrap">
                            @if($user->avatar)
                                <img src="{{ $user->avatar_url }}" alt="Foto Profil" id="avatarPreviewImg">
                            @else
                                <span id="avatarInitials">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <label for="avatarFileInput" class="avatar-edit-btn" title="Ganti foto profil">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                    </div>
                </div>
                <h5 class="fw-700 mb-1" style="color:var(--hijau-tua)">{{ auth()->user()->name }}</h5>
                <p class="text-muted mb-2" style="font-size:13px">{{ auth()->user()->email }}</p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if(auth()->user()->isAdmin())
                        <span class="badge rounded-pill px-3 py-2" style="background:#e8f5ed;color:var(--hijau-tua);font-size:12px;border:1px solid #c8dfd0">
                            <i class="bi bi-shield-fill-check me-1"></i> Administrator
                        </span>
                    @else
                        <span class="badge rounded-pill px-3 py-2" style="background:#e8f0fe;color:#1a5296;font-size:12px;border:1px solid #c6dafc">
                            <i class="bi bi-person-fill me-1"></i> Pengguna SIMKAR
                        </span>
                    @endif

                    <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success border border-success-subtle" style="font-size:12px">
                        <i class="bi bi-circle-fill me-1" style="font-size:8px"></i> Aktif
                    </span>
                </div>

                <div class="border-top pt-3 text-start">
                    <div class="profile-meta-item">
                        <span class="text-muted">Peran Akun</span>
                        <strong class="text-capitalize text-dark">{{ auth()->user()->role }}</strong>
                    </div>
                    <div class="profile-meta-item">
                        <span class="text-muted">Terdaftar Sejak</span>
                        <strong class="text-dark">{{ auth()->user()->created_at ? auth()->user()->created_at->translatedFormat('d F Y') : '-' }}</strong>
                    </div>
                    <div class="profile-meta-item">
                        <span class="text-muted">Unit Kerja</span>
                        <strong class="text-dark">PTPN IV Regional V</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Informasi Keamanan --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-check"></i>
                <span>Tips Keamanan Akun</span>
            </div>
            <div class="card-body p-3" style="font-size:12.5px;line-height:1.7;color:#55695c">
                <ul class="ps-3 mb-0">
                    <li>Gunakan kata sandi unik minimal 8 karakter dengan kombinasi angka dan huruf.</li>
                    <li>Jangan berikan akses akun atau kredensial login kepada pihak lain.</li>
                    <li>Selalu perbarui kata sandi secara berkala untuk menjaga keamanan data kebun.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Formulir Edit Profil & Password --}}
    <div class="col-lg-8">
        {{-- Card 1: Informasi Profil --}}
        <div class="card mb-4">
            <div class="card-header justify-content-between flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-fill-gear text-success"></i>
                    <span>Informasi Profil Akun</span>
                </div>
                <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:11.5px">
                    Data Pribadi
                </span>
            </div>

            <div class="card-body p-4">
                {{-- Flash messages --}}
                @if(session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><strong>Berhasil!</strong> Informasi profil Anda telah berhasil diperbarui.</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if(session('status') === 'avatar-updated')
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><strong>Berhasil!</strong> Foto profil Anda telah diperbarui.</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if(session('status') === 'avatar-removed')
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><strong>Berhasil!</strong> Foto profil Anda telah dihapus.</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><strong>Perhatian:</strong> {!! session('error') !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Upload Avatar Form (hidden input, submit via JS) --}}
                <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatarUploadForm" class="mb-3">
                    @csrf
                    <input type="file" id="avatarFileInput" name="avatar" accept="image/jpg,image/jpeg,image/png,image/webp" class="d-none">
                    <input type="hidden" name="remove_avatar" id="removeAvatarInput" value="0">
                    @if($user->avatar)
                    <div class="d-flex align-items-center gap-2 mt-2" id="avatarActions">
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill d-inline-flex align-items-center gap-1" onclick="removeAvatar()" id="removeAvatarBtn">
                            <i class="bi bi-trash3-fill" style="font-size:11px"></i> Hapus Foto
                        </button>
                    </div>
                    @endif
                </form>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:var(--hijau-tua)">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-group">
                            <i class="bi bi-person-fill input-icon-start"></i>
                            <input type="text" name="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', auth()->user()->name) }}" 
                                   placeholder="Masukkan nama lengkap Anda" required>
                        </div>
                        @error('name')
                        <div class="text-danger mt-1 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600" style="font-size:13px;color:var(--hijau-tua)">
                            Alamat Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-group">
                            <i class="bi bi-envelope-fill input-icon-start"></i>
                            <input type="email" name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', auth()->user()->email) }}" 
                                   placeholder="nama@ptpn.com" required>
                        </div>
                        @error('email')
                        <div class="text-danger mt-1 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted" style="font-size:12px">
                            Email ini digunakan sebagai akun login Anda pada sistem SIMKAR.
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-hijau rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-floppy-fill"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card 2: Ubah Kata Sandi --}}
        <div class="card">
            <div class="card-header justify-content-between flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock-fill text-success"></i>
                    <span>Keamanan &amp; Kata Sandi</span>
                </div>
                <span class="badge" style="background:#e8f0fe;color:#1a5296;font-size:11.5px">
                    Keamanan
                </span>
            </div>

            <div class="card-body p-4">
                @if(session('status') === 'password-updated')
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><strong>Berhasil!</strong> Kata sandi Anda telah berhasil diubah.</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->updatePassword->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <strong>Gagal memperbarui kata sandi:</strong>
                    </div>
                    <ul class="mb-0 ps-3" style="font-size:13px">
                        @foreach($errors->updatePassword->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:var(--hijau-tua)">
                            Kata Sandi Saat Ini <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-group">
                            <i class="bi bi-key-fill input-icon-start"></i>
                            <input type="password" name="current_password" id="inputCurrentPassword" 
                                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                   placeholder="Ketik kata sandi lama Anda" autocomplete="current-password" required>
                            <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('inputCurrentPassword', this)" aria-label="Lihat kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('current_password', 'updatePassword')
                        <div class="text-danger mt-1 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:13px;color:var(--hijau-tua)">
                            Kata Sandi Baru <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-group">
                            <i class="bi bi-lock-fill input-icon-start"></i>
                            <input type="password" name="password" id="inputNewPassword" 
                                   class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                   placeholder="Minimal 8 karakter kombinasi" autocomplete="new-password" 
                                   oninput="checkProfilePasswordMatch()" required>
                            <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('inputNewPassword', this)" aria-label="Lihat kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password', 'updatePassword')
                        <div class="text-danger mt-1 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted mt-1" style="font-size:12px;">
                            <i class="bi bi-info-circle text-success me-1"></i><strong>Petunjuk:</strong> Minimal 8 karakter, wajib kombinasi huruf dan angka (cth: <code>Pelaihari2026</code>).
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600" style="font-size:13px;color:var(--hijau-tua)">
                            Ulangi Kata Sandi Baru <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-group">
                            <i class="bi bi-shield-check input-icon-start"></i>
                            <input type="password" name="password_confirmation" id="inputConfirmPassword" 
                                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                                   placeholder="Ketik ulang kata sandi baru" autocomplete="new-password" 
                                   oninput="checkProfilePasswordMatch()" required>
                            <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility('inputConfirmPassword', this)" aria-label="Lihat kata sandi">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="profilePasswordMatchIndicator" class="mt-1 small" style="display:none;"></div>
                        @error('password_confirmation', 'updatePassword')
                        <div class="text-danger mt-1 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted mt-1" style="font-size:12px;">
                            <i class="bi bi-info-circle text-success me-1"></i><strong>Petunjuk:</strong> Masukkan kembali kata sandi baru sama persis.
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-hijau rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock"></i> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
}

function checkProfilePasswordMatch() {
    const pwd = document.getElementById('inputNewPassword');
    const confirm = document.getElementById('inputConfirmPassword');
    const indicator = document.getElementById('profilePasswordMatchIndicator');
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

// ─── Avatar Upload ─────────────────────────────────────────────
const avatarFileInput = document.getElementById('avatarFileInput');
const avatarUploadForm = document.getElementById('avatarUploadForm');

if (avatarFileInput) {
    avatarFileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // Validasi klien sederhana
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.');
            this.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran foto maksimal 2 MB.');
            this.value = '';
            return;
        }

        // Preview langsung
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrap = document.getElementById('avatarPreviewWrap');
            if (wrap) {
                wrap.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width:100%;height:100%;object-fit:cover;">';
            }
        };
        reader.readAsDataURL(file);

        // Auto-submit form
        document.getElementById('removeAvatarInput').value = '0';
        avatarUploadForm.submit();
    });
}

function removeAvatar() {
    if (!confirm('Hapus foto profil Anda?')) return;
    document.getElementById('removeAvatarInput').value = '1';
    avatarUploadForm.submit();
}
</script>
@endpush
