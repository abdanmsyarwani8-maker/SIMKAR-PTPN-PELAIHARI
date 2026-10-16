<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMKAR') — PTPN IV Kebun Pelaihari Regional 5</title>
    <meta name="description" content="Sistem Informasi Manajemen Karyawan PTPN IV Kebun Pelaihari Regional 5">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --hijau-tua:   #1a5c2e;
            --hijau-med:   #2d7d46;
            --hijau-muda:  #4caf72;
            --hijau-light: #e8f5ed;
            --hijau-pale:  #f0faf4;
            
            --sidebar-w:   270px;
            --sidebar-mini-w: 80px; 
            --topbar-h:    60px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f7f5;
            color: #1e2d24;
            margin: 0;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ─────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, var(--hijau-tua) 0%, #0f3d1e 100%);
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease, transform 0.3s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,.15);
            overflow: hidden;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            height: 90px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            white-space: nowrap;
        }
        .sidebar-brand img {
            width: 50px; height: 50px; background: #fff; padding: 4px;
            border-radius: 12px; flex-shrink: 0; box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .brand-text-container {
            display: flex; flex-direction: column; justify-content: center;
            transition: opacity 0.2s ease, width 0.2s ease; overflow: hidden;
        }
        .brand-title {
            color: #fff; font-weight: 800; font-size: 18px; line-height: 1.1; margin-bottom: 2px; letter-spacing: 1px;
        }
        .brand-subtitle {
            color: rgba(255,255,255,.9); font-weight: 400; font-size: 12px; line-height: 1.2;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 16px; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

        .nav-section-label {
            color: rgba(255,255,255,.35); font-size: 10px; font-weight: 600; letter-spacing: .8px;
            text-transform: uppercase; padding: 12px 12px 8px; white-space: nowrap; transition: opacity 0.2s ease;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,.72); padding: 12px 14px; border-radius: 10px; margin-bottom: 4px;
            font-size: 13.5px; font-weight: 500; display: flex; align-items: center; gap: 12px;
            transition: all .2s ease; text-decoration: none; white-space: nowrap; overflow: hidden;
        }
        .sidebar-nav .nav-link i { font-size: 18px; width: 22px; text-align: center; flex-shrink: 0; }
        .nav-link-text { transition: opacity 0.2s ease; }
        
        .sidebar-nav .nav-link:hover { background: rgba(255,255,255,.12); color: #fff; transform: translateX(2px); }
        .sidebar-nav .nav-link.active { background: var(--hijau-muda); color: #fff; box-shadow: 0 4px 12px rgba(76,175,114,.35); transform: translateX(0); }

        .sidebar-footer {
            padding: 16px; border-top: 1px solid rgba(255,255,255,.1); white-space: nowrap; overflow: hidden; transition: all 0.3s ease;
        }
        .sidebar-user { display: flex; align-items: center; gap: 10px; }
        .sidebar-user .avatar {
            width: 36px; height: 36px; background: var(--hijau-muda); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; color: #fff; font-weight: 700; flex-shrink: 0;
        }
        .sidebar-user .user-info { flex: 1; min-width: 0; transition: opacity 0.2s ease; }
        .sidebar-user .user-name { color: #fff; font-size: 12.5px; font-weight: 600; text-overflow: ellipsis; overflow: hidden; }
        .sidebar-user .user-role { color: rgba(255,255,255,.5); font-size: 10.5px; }

        /* ── TOMBOL TOGGLE DESKTOP (POSISI DI GARIS MERAH) ── */
        .btn-sidebar-toggle-desktop {
            position: fixed;
            top: 50%; /* Posisi tengah vertikal */
            left: calc(var(--sidebar-w) - 14px); /* Berada tepat di tengah batas sidebar dan konten */
            width: 28px;
            height: 28px;
            background: #fff;
            border: 1px solid #d4e8da;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            z-index: 1050;
            color: var(--hijau-tua);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: left 0.3s ease, transform 0.2s ease, background 0.2s;
            transform: translateY(-50%);
            padding: 0;
        }
        .btn-sidebar-toggle-desktop:hover {
            background: var(--hijau-light);
            transform: translateY(-50%) scale(1.1);
        }
        
        /* ── MINI SIDEBAR ──────────────── */
        body.sidebar-mini .sidebar { width: var(--sidebar-mini-w); }
        body.sidebar-mini .btn-sidebar-toggle-desktop { left: calc(var(--sidebar-mini-w) - 14px); } /* Ikut bergeser saat dikecilkan */
        
        body.sidebar-mini .brand-text-container,
        body.sidebar-mini .nav-section-label,
        body.sidebar-mini .nav-link-text,
        body.sidebar-mini .user-info { opacity: 0; width: 0; display: none; }
        body.sidebar-mini .sidebar-brand { justify-content: center; padding: 24px 0; }
        body.sidebar-mini .sidebar-nav { padding: 16px 12px; }
        body.sidebar-mini .sidebar-nav .nav-link { justify-content: center; padding: 12px 0; gap: 0; }
        body.sidebar-mini .sidebar-footer { padding: 16px 12px; display: flex; justify-content: center; }
        body.sidebar-mini .sidebar-user { justify-content: center; }
        body.sidebar-mini .main-content { margin-left: var(--sidebar-mini-w); }
        body.sidebar-mini .topbar { left: var(--sidebar-mini-w); }

        /* ── TOPBAR ──────────────────────────────── */
        .topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h); background: #fff; border-bottom: 1px solid #e8ede9;
            z-index: 1030; display: flex; align-items: center; padding: 0 24px; gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05); transition: left 0.3s ease;
        }
        .topbar .page-title { font-size: 16px; font-weight: 700; color: var(--hijau-tua); flex: 1; letter-spacing: -.2px; }
        
        /* Tombol Mobile Khusus Topbar */
        .btn-sidebar-toggle-mobile {
            border: none; background: none; font-size: 24px; color: var(--hijau-tua);
            cursor: pointer; padding: 0; margin-right: 8px; display: flex; align-items: center;
        }

        /* ── MAIN CONTENT (KEMBALI 100% KE ASLI) ──────── */
        .main-content { margin-left: var(--sidebar-w); padding-top: var(--topbar-h); min-height: 100vh; transition: margin-left 0.3s ease; }
        .content-area { padding: 24px; }

        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff; border: 1.5px solid #dce8df; color: #435b4c;
            border-radius: 20px; padding: 6px 14px; font-size: 12.5px;
            font-weight: 500; text-decoration: none; transition: all .15s ease;
        }
        .btn-back:hover {
            background: var(--hijau-pale); border-color: var(--hijau-med);
            color: var(--hijau-tua); transform: translateX(-2px);
        }

        .card { border: 1px solid #dce8df; border-radius: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.03); background: #fff; }
        .card-header {
            background: #fff; border-bottom: 1px solid #e8ede9;
            border-radius: 14px 14px 0 0 !important; padding: 14px 20px;
            font-weight: 700; font-size: 14px; color: var(--hijau-tua);
            display: flex; align-items: center; gap: 8px;
        }
        .card-header i { font-size: 16px; color: var(--hijau-med); }
        .stat-card { background: #fff; border: 1px solid #e8ede9; border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 16px; transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .stat-icon.green  { background: var(--hijau-light); color: var(--hijau-tua); }
        .stat-icon.blue   { background: #e8f0fe; color: #1a5296; }
        .stat-icon.orange { background: #fff3e0; color: #e65100; }
        .stat-icon.purple { background: #f3e8fd; color: #6a1b9a; }
        .stat-value { font-size: 26px; font-weight: 800; color: var(--hijau-tua); line-height: 1; }
        .stat-label { font-size: 12px; color: #6b7c70; font-weight: 500; margin-top: 3px; }
        .btn-hijau { background: var(--hijau-tua); color: #fff; border: 1.5px solid var(--hijau-tua); font-weight: 600; transition: all .2s; }
        .btn-hijau:hover { background: var(--hijau-med); border-color: var(--hijau-med); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(26,92,46,.25); }
        .btn-hijau-outline { border: 1.5px solid var(--hijau-tua); color: var(--hijau-tua); background: transparent; font-weight: 600; transition: all .2s; }
        .btn-hijau-outline:hover { background: var(--hijau-tua); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(26,92,46,.2); }
        .table-simkar th { background: var(--hijau-pale); color: var(--hijau-tua); font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; border-bottom: 2px solid #d4e8da; white-space: nowrap; padding: 12px 14px; }
        .table-simkar td { vertical-align: middle; font-size: 13px; padding: 11px 14px; }
        .table-simkar tbody tr:hover { background: var(--hijau-pale); }
        .table-simkar .btn-action {
            width: 32px; height: 32px; padding: 0;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 8px; font-size: 13px; background: #fff;
            border: 1px solid #dbe6df; color: #435b4c; transition: all .15s ease;
            text-decoration: none;
        }
        .table-simkar .btn-action:hover {
            background: var(--hijau-light); color: var(--hijau-tua);
            border-color: var(--hijau-med); transform: translateY(-1px);
        }
        .table-simkar .btn-action.btn-action-danger:hover {
            background: #fde8e8; color: #c0392b; border-color: #f5c2c2;
        }
        .badge-karpim, .badge-karpel, .badge-pkwt, .badge-ktng, .badge-papam {
            font-weight: 600; border-radius: 6px; padding: 4px 9px; font-size: 11px;
            display: inline-flex; align-items: center; gap: 4px; letter-spacing: .2px;
        }
        .badge-karpim  { background: #e8f0fe; color: #1a5296; border: 1px solid #c6dafc; }
        .badge-karpel  { background: var(--hijau-light); color: var(--hijau-tua); border: 1px solid #c8e6c9; }
        .badge-pkwt    { background: #fff3e0; color: #c46616; border: 1px solid #ffe0b2; }
        .badge-ktng    { background: #e0f2f1; color: #00695c; border: 1px solid #b2dfdb; }
        .badge-papam   { background: #f3e8fd; color: #6a1b9a; border: 1px solid #e1bee7; }
        .form-control, .form-select {
            border: 1.5px solid #d4e8da; border-radius: 10px; font-size: 13px;
            padding: 8px 12px; transition: border-color .15s ease, box-shadow .15s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--hijau-med); box-shadow: 0 0 0 3px rgba(76, 175, 114, 0.18);
            outline: none;
        }
        .filter-panel { background: #fff; border: 1px solid #e8ede9; border-radius: 14px; overflow: hidden; }
        .filter-group { padding: 14px 16px; border-bottom: 1px solid #f0f4f1; }
        .filter-group:last-child { border-bottom: none; }
        .filter-group-title { font-size: 11px; font-weight: 700; color: var(--hijau-tua); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; }
        .filter-search { border: 1px solid #d4e8da; border-radius: 8px; font-size: 12px; padding: 5px 10px; width: 100%; margin-bottom: 8px; outline: none; }
        .filter-search:focus { border-color: var(--hijau-med); }
        .filter-list { max-height: 160px; overflow-y: auto; }
        .filter-list::-webkit-scrollbar { width: 4px; }
        .filter-list::-webkit-scrollbar-thumb { background: #d4e8da; border-radius: 2px; }
        .filter-list .form-check { margin-bottom: 4px; }
        .filter-list .form-check-label { font-size: 12.5px; cursor: pointer; }
        .filter-list .form-check-input:checked { background-color: var(--hijau-tua); border-color: var(--hijau-tua); }
        .alert { border-radius: 10px; border: none; font-size: 13.5px; }
        .alert-success { background: var(--hijau-light); color: var(--hijau-tua); }
        .alert-danger  { background: #fce8e8; color: #c0392b; }
        .fw-400 { font-weight: 400 !important; }
        .fw-500 { font-weight: 500 !important; }
        .fw-600 { font-weight: 600 !important; }
        .fw-700 { font-weight: 700 !important; }
        .fw-800 { font-weight: 800 !important; }

        .modal-content { border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.12); overflow: hidden; }
        .modal-header { background: var(--hijau-pale); border-bottom: 1px solid #d4e8da; padding: 14px 20px; }
        .modal-header .modal-title { font-weight: 700; font-size: 14.5px; color: var(--hijau-tua); display: flex; align-items: center; gap: 8px; }
        .modal-header .modal-title i { color: var(--hijau-med); }
        .modal-footer { border-top: 1px solid #e8ede9; padding: 12px 20px; }

        .empty-state { text-align: center; padding: 60px 20px; color: #8ba390; }
        .empty-state i { font-size: 56px; margin-bottom: 16px; opacity: .4; }
        .empty-state h5 { font-weight: 600; color: #4a6650; }

        .pagination { margin: 0; gap: 4px; }
        .page-item .page-link {
            border-radius: 8px !important;
            border: 1px solid #d4e8da;
            color: var(--hijau-tua);
            font-size: 12.5px;
            font-weight: 500;
            padding: 6px 12px;
            transition: all .15s ease;
        }
        .page-item .page-link:hover {
            background: var(--hijau-pale);
            border-color: var(--hijau-med);
            color: var(--hijau-tua);
        }
        .page-item.active .page-link {
            background: var(--hijau-tua);
            border-color: var(--hijau-tua);
            color: #fff;
            box-shadow: 0 2px 6px rgba(26,92,46,.25);
        }
        .page-item.disabled .page-link {
            border-color: #e5ece7;
            color: #9cb0a2;
        }

        /* ── RESPONSIVE HP ──────────────────────────── */
        @media (max-width: 991px) {
            body.sidebar-mini .sidebar, .sidebar { width: var(--sidebar-w); transform: translateX(-100%); }
            .sidebar.mobile-show { transform: translateX(0); }
            body.sidebar-mini .main-content, .main-content { margin-left: 0; }
            body.sidebar-mini .topbar, .topbar { left: 0; }
            body.sidebar-mini .brand-text-container, body.sidebar-mini .nav-section-label, body.sidebar-mini .nav-link-text, body.sidebar-mini .user-info { display: flex; opacity: 1; width: auto; pointer-events: auto; }
            body.sidebar-mini .sidebar-brand { justify-content: flex-start; padding: 24px 20px; }
            body.sidebar-mini .sidebar-nav { padding: 16px; }
            body.sidebar-mini .sidebar-nav .nav-link { justify-content: flex-start; padding: 12px 14px; gap: 12px; }
            body.sidebar-mini .sidebar-footer { padding: 16px; display: block; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1039; }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>
<!-- Script mencegah kedip localStorage -->
<script>
    if (localStorage.getItem('sidebarState') === 'mini' && window.innerWidth >= 992) {
        document.body.classList.add('sidebar-mini');
    }
</script>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleMobileSidebar()"></div>

<!-- TOMBOL TOGGLE DESKTOP (Tepat di batas kanan sidebar) -->
<button class="btn-sidebar-toggle-desktop d-none d-lg-flex" onclick="toggleSidebarLogic()">
    <i class="bi bi-chevron-left" id="toggleIcon"></i>
</button>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img
            src="{{ asset('images/ptpn-logo.png') }}"
            alt="Logo PTPN IV Regional V"
            class="brand-logo-img"
        >
        <div class="brand-text-container">
            <div class="brand-title">SIMKAR</div>
            <div class="brand-subtitle">PTPN IV Regional V<br>Kebun Pelaihari</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> <span class="nav-link-text">Dashboard</span>
        </a>

        <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') && !request()->routeIs('employees.import*') && !request()->routeIs('employees.export*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> <span class="nav-link-text">Data Karyawan</span>
        </a>

        <a href="{{ route('employees.export.form') }}" class="nav-link {{ request()->routeIs('employees.export*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-arrow-down-fill"></i> <span class="nav-link-text">Export Excel</span>
        </a>

        @auth
        @if(auth()->user()->isAdmin())
        <div class="nav-section-label">Administrasi</div>
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock-fill"></i> <span class="nav-link-text">Pengguna</span>
        </a>
        <a href="{{ route('employees.import.form') }}" class="nav-link {{ request()->routeIs('employees.import.form') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-arrow-up-fill"></i> <span class="nav-link-text">Import Excel</span>
        </a>
        <a href="{{ route('employees.import.history') }}" class="nav-link {{ request()->routeIs('employees.import.history*') || request()->routeIs('employees.import.result') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> <span class="nav-link-text">Riwayat Import</span>
        </a>
        @endif
        @endauth

        <div class="nav-section-label">Akun</div>
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-fill-gear"></i> <span class="nav-link-text">Profil Saya</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="d-inline w-100 m-0 p-0">
            @csrf
            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
                <i class="bi bi-box-arrow-right"></i> <span class="nav-link-text">Keluar</span>
            </button>
        </form>
    </nav>

    <div class="sidebar-footer">
        @auth
        <div class="sidebar-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->role_label ?? 'Administrator' }}</div>
            </div>
        </div>
        @endauth
    </div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
    <!-- Tombol Burger Khusus HP -->
    <button class="btn-sidebar-toggle-mobile d-lg-none" onclick="toggleMobileSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <div class="page-title">@yield('page-title', 'Dashboard')</div>

    @auth
    <div class="dropdown">
        <button class="btn btn-sm btn-light rounded-pill px-3 d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
            <span class="d-none d-md-inline" style="font-size:13px">{{ auth()->user()->name }}</span>
            <i class="bi bi-chevron-down" style="font-size:10px"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:180px">
            <li><a class="dropdown-item small" href="{{ route('profile.edit') }}"><i class="bi bi-person-fill-gear me-2"></i>Profil Saya</a></li>
            @if(auth()->user()->isAdmin())
            <li><a class="dropdown-item small" href="{{ route('users.index') }}"><i class="bi bi-shield-lock-fill me-2"></i>Pengguna</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item small text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                </form>
            </li>
        </ul>
    </div>
    @endauth
</header>

<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="content-area">
        @yield('content')
    </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleIcon = document.getElementById('toggleIcon');
    if (document.body.classList.contains('sidebar-mini') && window.innerWidth >= 992) {
        toggleIcon.classList.remove('bi-chevron-left');
        toggleIcon.classList.add('bi-chevron-right');
    }
});

function toggleSidebarLogic() {
    const toggleIcon = document.getElementById('toggleIcon');
    
    document.body.classList.toggle('sidebar-mini');
    
    if (document.body.classList.contains('sidebar-mini')) {
        localStorage.setItem('sidebarState', 'mini');
        toggleIcon.classList.remove('bi-chevron-left');
        toggleIcon.classList.add('bi-chevron-right');
    } else {
        localStorage.setItem('sidebarState', 'full');
        toggleIcon.classList.remove('bi-chevron-right');
        toggleIcon.classList.add('bi-chevron-left');
    }
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('mobile-show');
    overlay.classList.toggle('show');
}
</script>

@stack('scripts')
</body>
</html>