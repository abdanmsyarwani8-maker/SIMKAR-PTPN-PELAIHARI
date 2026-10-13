<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIMKAR PTPN IV</title>
    <meta name="description" content="Login SIMKAR - Sistem Informasi Manajemen Karyawan PTPN IV Regional 5">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f3d1e 0%, #1a5c2e 40%, #2d7d46 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(0,0,0,.25);
            overflow: hidden;
            width: 100%; max-width: 440px;
        }
        .login-header {
            background: linear-gradient(135deg, #1a5c2e, #2d7d46);
            padding: 36px 40px 32px;
            text-align: center;
        }
        .logo-circle {
            width: 72px; height: 72px; border-radius: 18px;
            background: rgba(255,255,255,.15);
            backdrop-filter: blur(10px);
            margin: 0 auto 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 36px;
        }
        .login-header h4 { color:#fff; font-weight:800; margin-bottom:4px; }
        .login-header p { color:rgba(255,255,255,.75); font-size:13px; margin:0; }
        .login-body { padding: 32px 40px; }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; }
        .form-control {
            border: 1.5px solid #d1d5db; border-radius: 10px;
            padding: 11px 14px; font-size: 14px;
            transition: border-color .2s;
        }
        .form-control:focus { border-color: #2d7d46; box-shadow: 0 0 0 3px rgba(45,125,70,.12); }
        .btn-login {
            background: linear-gradient(135deg, #1a5c2e, #2d7d46);
            color: #fff; border: none; border-radius: 10px;
            padding: 12px; font-size: 15px; font-weight: 600;
            width: 100%; transition: all .2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(26,92,46,.35); color:#fff; }
        .input-icon { position: relative; }
        .input-icon i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #9ca3af; font-size: 15px; pointer-events: none;
        }
        .input-icon input { padding-left: 40px; }
        .login-footer {
            background: #f9fafb; padding: 14px 40px;
            text-align: center; font-size: 12px; color: #6b7280;
            border-top: 1px solid #f3f4f6;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <div class="logo-circle">🌴</div>
        <h4>SIMKAR PTPN IV</h4>
        <p>Sistem Informasi Manajemen Karyawan<br>Kebun Pelaihari · Regional 5</p>
    </div>

    <div class="login-body">
        <h5 class="fw-700 mb-1" style="color:#111827">Selamat Datang</h5>
        <p class="text-muted mb-4" style="font-size:13.5px">Masuk dengan akun yang telah diberikan</p>

        @if(session('status'))
        <div class="alert alert-success mb-3" style="font-size:13px;border-radius:10px">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Alamat Email</label>
                <div class="input-icon">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" placeholder="email@ptpn.com" required autofocus autocomplete="username">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Kata Sandi</label>
                <div class="input-icon">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" id="passwordInput"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" required autocomplete="current-password">
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:13px">Ingat saya</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke SIMKAR
            </button>
        </form>
    </div>

    <div class="login-footer">
        &copy; {{ date('Y') }} PTPN IV · Kebun Pelaihari Regional 5
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
