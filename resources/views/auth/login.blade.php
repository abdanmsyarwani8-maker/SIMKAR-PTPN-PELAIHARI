

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Masuk — SIMKAR PTPN IV</title>



    <meta name="description" content="SIMKAR PTPN IV Regional 5 - Sistem Informasi Manajemen Karyawan">



    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>



    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">



    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



    <style>

        :root {

            --green-dark: #063d29;

            --green-primary: #087343;

            --green-light: #45a653;

            --text-dark: #172b25;

            --text-muted: #84908e;

            --border-color: #dce3e1;

        }



        * {

            box-sizing: border-box;

        }



        html {
            height: 100%;
            overflow: hidden;
        }

        body {
            margin: 0;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background:
                linear-gradient(135deg, rgba(4, 54, 34, .25), rgba(19, 106, 62, .10)),
                url('https://images.unsplash.com/photo-1781453642131-831ca8f70536?auto=format&fit=crop&w=2000&q=85')
                center / cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1180px;
            height: min(90vh, 760px);
            height: min(90dvh, 760px);
            min-height: 0;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0, 30, 15, .18);
            border: 1px solid rgba(255, 255, 255, .65);
        }


        /* PANEL KIRI */

        .brand-panel {

            position: relative;

            min-height: 0;
            height: 100%;

            padding: 38px 42px 28px;

            color: #fff;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            overflow: hidden;

            background:

                linear-gradient(

                    145deg,

                    rgba(3, 47, 31, .96) 0%,

                    rgba(3, 65, 39, .80) 43%,

                    rgba(5, 50, 32, .30) 100%

                ),

                url('https://images.unsplash.com/photo-1781453642131-831ca8f70536?auto=format&fit=crop&w=1200&q=85')

                center / cover;

        }



        .brand-panel::before {

            content: "";

            position: absolute;

            width: 400px;

            height: 400px;

            top: -230px;

            right: -150px;

            border-radius: 50%;

            background: rgba(99, 184, 102, .18);

            border: 1px solid rgba(255, 255, 255, .12);

        }



        .brand-panel::after {

            content: "";

            position: absolute;

            width: 450px;

            height: 250px;

            bottom: -180px;

            left: -100px;

            border-radius: 50%;

            background: rgba(55, 157, 75, .23);

        }



        .brand-content,

        .brand-bottom {

            position: relative;

            z-index: 1;

        }



        .brand-heading {

            display: flex;

            align-items: center;

            gap: 22px;

            margin-bottom: 38px;

        }



        .brand-logo {

            width: 95px;

            height: 95px;

            flex-shrink: 0;

            border-radius: 20px;

            background: rgba(255, 255, 255, .96);

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 10px;

            box-shadow: 0 8px 28px rgba(0, 0, 0, .12);

        }



        .brand-logo img {

            width: 100%;

            height: 100%;

            object-fit: contain;

        }



        .logo-fallback {

            font-size: 42px;

            color: var(--green-primary);

        }



        .brand-title {

            font-size: 35px;

            font-weight: 800;

            line-height: 1.2;

            letter-spacing: -.8px;

            margin: 0;

        }



        .brand-subtitle {

            font-size: 23px;

            font-weight: 400;

            margin-top: 3px;

        }



        .brand-description {

            margin-top: 8px;

            font-size: 14px;

            color: rgba(255, 255, 255, .85);

            line-height: 1.6;

        }



        .brand-line {

            width: 65px;

            height: 5px;

            background: #55bd61;

            border-radius: 10px;

            margin-bottom: 20px;

        }



        .brand-quote {

            max-width: 400px;

            font-size: 21px;

            line-height: 1.65;

            font-weight: 400;

        }



        .brand-quote strong {

            font-weight: 800;

        }



        .brand-bottom {

            margin-top: 28px;

        }



        .feature-box {

            display: grid;

            grid-template-columns: repeat(3, 1fr);

            padding: 20px 12px;

            border: 1px solid rgba(255, 255, 255, .13);

            background: rgba(255, 255, 255, .10);

            backdrop-filter: blur(15px);

            border-radius: 18px;

        }



        .feature-item {

            text-align: center;

            padding: 0 7px;

            border-right: 1px solid rgba(255, 255, 255, .35);

        }



        .feature-item:last-child {

            border-right: none;

        }



        .feature-icon {

            width: 43px;

            height: 43px;

            margin: 0 auto 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            background: rgba(255, 255, 255, .12);

            font-size: 21px;

        }



        .feature-item span {

            display: block;

            font-size: 12px;

            font-weight: 500;

        }



        /* PANEL KANAN */

        .form-panel {
            min-width: 0;
            min-height: 0;
            overflow: hidden;

            padding: 38px 48px 28px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            background:

                radial-gradient(

                    circle at 100% 100%,

                    rgba(221, 244, 229, .75),

                    transparent 35%

                ),

                #fff;

        }



        .welcome-title {

            color: var(--text-dark);

            font-size: 32px;

            font-weight: 800;

            letter-spacing: -.9px;

            margin-bottom: 12px;

        }



        .welcome-description {

            color: var(--text-muted);

            font-size: 14px;

            line-height: 1.8;

            margin-bottom: 24px;

        }



        .form-label {

            font-size: 13px;

            font-weight: 700;

            color: #273631;

            margin-bottom: 10px;

        }



        .input-group-custom {

            position: relative;

            margin-bottom: 17px;

        }



        .input-group-custom > i {

            position: absolute;

            z-index: 2;

            top: 50%;

            left: 19px;

            transform: translateY(-50%);

            color: #7c8986;

            font-size: 19px;

            pointer-events: none;

        }



        .form-control {

            min-height: 50px;

            border: 1.5px solid var(--border-color);

            border-radius: 13px !important;

            padding: 15px 48px;

            font-family: inherit;

            font-size: 14px;

            color: var(--text-dark);

            background-color: rgba(255, 255, 255, .85);

            transition: all .25s ease;

        }



        .form-control::placeholder {

            color: #a0aaa7;

        }



        .form-control:focus {

            border-color: var(--green-primary);

            box-shadow: 0 0 0 4px rgba(8, 115, 67, .09);

            background: #fff;

        }



        .password-toggle {

            position: absolute;

            top: 50%;

            right: 17px;

            z-index: 3;

            transform: translateY(-50%);

            padding: 5px;

            border: none;

            background: transparent;

            color: #7c8986;

            font-size: 18px;

            cursor: pointer;

        }



        .password-toggle:hover {

            color: var(--green-primary);

        }



        .form-check-input {

            width: 17px;

            height: 17px;

            margin-top: 2px;

            border-color: #c7d0cd;

            cursor: pointer;

        }



        .form-check-input:checked {

            background-color: var(--green-primary);

            border-color: var(--green-primary);

        }



        .form-check-label {

            color: #75817e;

            font-size: 12px;

            cursor: pointer;

        }



        .login-options {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-top: -3px;

            margin-bottom: 20px;

        }



        .forgot-password {

            font-size: 12px;

            color: var(--green-primary);

            text-decoration: none;

            font-weight: 700;

        }



        .forgot-password:hover {

            color: var(--green-dark);

            text-decoration: underline;

        }



        .btn-login {

            width: 100%;

            min-height: 52px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 12px;

            color: #fff;

            border: none;

            border-radius: 14px;

            background: linear-gradient(

                110deg,

                #00643c 0%,

                #087c47 55%,

                #42a34e 100%

            );

            box-shadow: 0 10px 25px rgba(8, 115, 67, .19);

            font-family: inherit;

            font-size: 15px;

            font-weight: 700;

            transition: all .25s ease;

        }



        .btn-login:hover {

            color: #fff;

            transform: translateY(-2px);

            box-shadow: 0 14px 30px rgba(8, 115, 67, .28);

        }



        .btn-login:active {

            transform: translateY(0);

        }



        .divider {

            display: flex;

            align-items: center;

            gap: 15px;

            margin: 20px 0 16px;

            color: #98a19f;

            font-size: 12px;

        }



        .divider::before,

        .divider::after {

            content: "";

            flex: 1;

            height: 1px;

            background: #dce3e1;

        }



        .btn-secondary-login {

            width: 100%;

            min-height: 56px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 12px;

            background: #fff;

            border: 1.5px solid #36805d;

            border-radius: 13px;

            color: var(--green-dark);

            font-size: 13px;

            font-weight: 700;

            text-decoration: none;

            transition: all .2s ease;

        }



        .btn-secondary-login:hover {

            background: #f0f8f3;

            color: var(--green-primary);

        }



        .login-footer {

            margin-top: 55px;

            color: #9aa5a1;

            font-size: 11px;

            text-align: center;

            line-height: 1.8;

        }



        .alert {

            border-radius: 12px;

            font-size: 13px;

        }



        .alert ul {

            margin-bottom: 0;

            padding-left: 20px;

        }



        /* Penyesuaian layar tablet */
        @media (max-width: 1000px) and (min-width: 701px) {
            body { padding: 14px; }
            .login-wrapper {
                height: 94vh;
                height: 94dvh;
                grid-template-columns: .9fr 1.1fr;
            }
            .brand-panel { padding: 28px 24px 22px; }
            .brand-heading { gap: 14px; margin-bottom: 28px; }
            .brand-logo { width: 72px; height: 72px; }
            .brand-title { font-size: 28px; }
            .brand-subtitle { font-size: 19px; }
            .brand-quote { font-size: 17px; }
            .feature-box { padding: 14px 8px; }
            .form-panel { padding: 26px 28px 20px; }
            .welcome-title { font-size: 30px; }
            .login-footer { margin-top: 18px; }
        }

        /* Tampilan HP */
        @media (max-width: 700px) {
            body { padding: 10px; background-attachment: scroll; }
            .login-wrapper {
                width: 100%;
                max-width: 460px;
                height: 96vh;
                height: 96dvh;
                min-height: 0;
                grid-template-columns: 1fr;
                grid-template-rows: auto minmax(0, 1fr);
                border-radius: 16px;
            }
            .brand-panel {
                min-height: 0;
                height: auto;
                padding: 14px 20px;
                background-position: center 45%;
            }
            .brand-heading {
                flex-direction: row;
                align-items: center;
                gap: 12px;
                margin: 0;
            }
            .brand-logo { width: 48px; height: 48px; padding: 5px; border-radius: 10px; }
            .logo-fallback { font-size: 26px; }
            .brand-title { font-size: 22px; }
            .brand-subtitle { font-size: 15px; }
            .brand-description, .brand-content > .brand-line,
            .brand-content > .brand-quote, .brand-bottom { display: none; }
            .form-panel { min-height: 0; overflow: hidden; padding: 22px 22px 14px; }
            .welcome-title { font-size: 25px; margin-bottom: 7px; }
            .welcome-description { font-size: 12px; line-height: 1.5; margin-bottom: 18px; }
            .form-label { margin-bottom: 7px; }
            .input-group-custom { margin-bottom: 14px; }
            .form-control { min-height: 46px; padding: 11px 43px; }
            .login-options { margin-bottom: 17px; }
            .btn-login { min-height: 48px; }
            .divider { margin: 15px 0 12px; }
            .btn-secondary-login { min-height: 46px; }
            .login-footer { margin-top: 14px; font-size: 10px; }
        }

        @media (max-width: 360px) {
            .form-panel { padding: 16px 15px 10px; }
            .brand-panel { padding: 12px 15px; }
            .welcome-title { font-size: 23px; }
            .login-options { gap: 6px; }
            .form-check-label, .forgot-password { font-size: 11px; }
            .login-footer { margin-top: 10px; }
        }

        @media (prefers-reduced-motion: reduce) {

            *,

            *::before,

            *::after {

                transition: none !important;

            }

        }

    </style>

</head>



<body>



<div class="login-wrapper">



    <!-- PANEL KIRI: BRANDING SIMKAR -->

    <section class="brand-panel">



        <div class="brand-content">



            <div class="brand-heading">



                <div class="brand-logo">

                    <!-- Ganti dengan logo resmi PTPN IV jika tersedia -->

                    <img

                        src="{{ asset('images/logo-ptpn4.png') }}"

                        alt="Logo PTPN IV"

                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"

                    >

                    <i class="bi bi-tree-fill logo-fallback" style="display:none"></i>

                </div>



                <div>

                    <h1 class="brand-title">SIMKAR</h1>

                    <div class="brand-subtitle">PTPN IV</div>

                    <p class="brand-description">

                        Sistem Informasi Manajemen<br>

                        Karyawan

                    </p>

                </div>



            </div>



            <div class="brand-line"></div>



            <p class="brand-quote">

                “Mengelola Sumber Daya Manusia untuk Perkebunan yang

                <strong>Lebih Maju</strong>”

            </p>



        </div>



        <div class="brand-bottom">



            <div class="feature-box">



                <div class="feature-item">

                    <div class="feature-icon">

                        <i class="bi bi-people"></i>

                    </div>

                    <span>Profesional</span>

                </div>



                <div class="feature-item">

                    <div class="feature-icon">

                        <i class="bi bi-shield-check"></i>

                    </div>

                    <span>Integritas</span>

                </div>



                <div class="feature-item">

                    <div class="feature-icon">

                        <i class="bi bi-flower1"></i>

                    </div>

                    <span>Berkelanjutan</span>

                </div>



            </div>



        </div>



    </section>





    <!-- PANEL KANAN: FORM LOGIN -->

    <main class="form-panel">



        <div class="login-content">



            <h2 class="welcome-title">Selamat Datang</h2>



            <p class="welcome-description">

                Masuk ke SIMKAR PTPN IV untuk melanjutkan

                aktivitas Anda.

            </p>



            <!-- NOTIFIKASI BERHASIL -->

            @if(session('status'))

                <div class="alert alert-success mb-4" role="status">

                    <i class="bi bi-check-circle me-2"></i>

                    {{ session('status') }}

                </div>

            @endif



            <!-- NOTIFIKASI ERROR -->

            @if($errors->any())

                <div class="alert alert-danger mb-4" role="alert">

                    <i class="bi bi-exclamation-circle me-2"></i>

                    <strong>Login gagal.</strong>

                    <ul class="mt-2">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif



            <form method="POST" action="{{ route('login') }}">

                @csrf



                <!-- EMAIL -->

                <div class="mb-4">



                    <label for="email" class="form-label">

                        Alamat Email

                    </label>



                    <div class="input-group-custom">

                        <i class="bi bi-envelope"></i>



                        <input

                            type="email"

                            name="email"

                            id="email"

                            class="form-control @error('email') is-invalid @enderror"

                            value="{{ old('email') }}"

                            placeholder="email@ptpn.com"

                            autocomplete="username"

                            required

                            autofocus

                        >

                    </div>



                    @error('email')

                        <div class="text-danger small mt-1">

                            {{ $message }}

                        </div>

                    @enderror



                </div>





                <!-- PASSWORD -->

                <div class="mb-3">



                    <label for="passwordInput" class="form-label">

                        Kata Sandi

                    </label>



                    <div class="input-group-custom mb-0">



                        <i class="bi bi-lock"></i>



                        <input

                            type="password"

                            name="password"

                            id="passwordInput"

                            class="form-control @error('password') is-invalid @enderror"

                            placeholder="Masukkan kata sandi"

                            autocomplete="current-password"

                            required

                        >



                        <button

                            type="button"

                            class="password-toggle"

                            id="togglePassword"

                            aria-label="Tampilkan kata sandi"

                            aria-pressed="false"

                        >

                            <i class="bi bi-eye-slash" id="passwordIcon"></i>

                        </button>



                    </div>



                    @error('password')

                        <div class="text-danger small mt-1">

                            {{ $message }}

                        </div>

                    @enderror



                </div>





                <!-- OPSI LOGIN -->

                <div class="login-options">



                    <div class="form-check">

                        <input

                            class="form-check-input"

                            type="checkbox"

                            name="remember"

                            id="remember"

                            value="1"

                            {{ old('remember') ? 'checked' : '' }}

                        >



                        <label

                            class="form-check-label"

                            for="remember"

                        >

                            Ingat saya

                        </label>

                    </div>



                    @if(Route::has('password.request'))

                        <a

                            href="{{ route('password.request') }}"

                            class="forgot-password"

                        >

                            Lupa kata sandi?

                        </a>

                    @endif



                </div>





                <!-- TOMBOL LOGIN -->

                <button type="submit" class="btn-login">

                    <i class="bi bi-box-arrow-in-right fs-5"></i>

                    <span>Masuk ke SIMKAR</span>

                </button>



            </form>





            <!-- PEMISAH -->

            <div class="divider">

                atau

            </div>





            <!-- LOGIN ALTERNATIF OPSIONAL -->

            @if(Route::has('sso.login'))

                <a

                    href="{{ route('sso.login') }}"

                    class="btn-secondary-login"

                >

                    <i class="bi bi-building fs-5"></i>

                    <span>Masuk dengan Akun PTPN</span>

                </a>

            @endif



        </div>





        <!-- FOOTER -->

        <footer class="login-footer">

            &copy; {{ date('Y') }} PTPN IV

            <span class="mx-1">·</span>

            Kebun Pelaihari Regional 5

            <br>

            Sistem Informasi Manajemen Karyawan

        </footer>



    </main>



</div>

<script>

    // Fitur tampil/sembunyikan kata sandi

    const togglePassword = document.getElementById('togglePassword');

    const passwordInput = document.getElementById('passwordInput');

    const passwordIcon = document.getElementById('passwordIcon');



    togglePassword.addEventListener('click', function () {

        const isPassword = passwordInput.type === 'password';



        passwordInput.type = isPassword ? 'text' : 'password';



        passwordIcon.className = isPassword

            ? 'bi bi-eye'

            : 'bi bi-eye-slash';



        togglePassword.setAttribute(

            'aria-pressed',

            String(isPassword)

        );



        togglePassword.setAttribute(

            'aria-label',

            isPassword

                ? 'Sembunyikan kata sandi'

                : 'Tampilkan kata sandi'

        );

    });

</script>



</body>

</html>
