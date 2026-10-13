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
            --sidebar-w:   260px;
            --topbar-h:    60px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f4f7f5;
            color: #1e2d24;
            margin: 0;
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
            transition: transform .3s ease;
            box-shadow: 4px 0 20px rgba(0,0,0,.15);
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-brand .logo-icon {
            width: 44px; height: 44px;
            background: var(--hijau-muda);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff; margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(76,175,114,.4);
        }
        .sidebar-brand h6 {
            color: #fff; font-weight: 700; font-size: 13px;
            margin: 0; line-height: 1.3;
        }
        .sidebar-brand small {
            color: rgba(255,255,255,.55); font-size: 10.5px;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

        .nav-section-label {
            color: rgba(255,255,255,.35);
            font-size: 10px; font-weight: 600; letter-spacing: .8px;
            text-transform: uppercase;
            padding: 14px 20px 6px;
        }

        .sidebar-nav .nav-link {
            color: rgba(255,255,255,.72);
            padding: 10px 20px;
            border-radius: 8px;
            margin: 1px 10px;
            font-size: 13.5px;
            font-weight: 500;
            display: flex; align-items: center; gap: 11px;
            transition: all .18s ease;
            text-decoration: none;
        }
        .sidebar-nav .nav-link i { font-size: 16px; width: 20px; text-align: center; }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,.12);
            color: #fff;
            transform: translateX(2px);
        }
        .sidebar-nav .nav-link.active {
            background: var(--hijau-muda);
            color: #fff;
            box-shadow: 0 4px 12px rgba(76,175,114,.35);
        }

        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid rgba(255,255,255,.1);
        }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-user .avatar {
            width: 36px; height: 36px;
            background: var(--hijau-muda);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; color: #fff; font-weight: 700; flex-shrink: 0;
        }
        .sidebar-user .user-info { flex: 1; min-width: 0; }
        .sidebar-user .user-name { color: #fff; font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user .user-role { color: rgba(255,255,255,.5); font-size: 10.5px; }

        /* ── TOPBAR ──────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e8ede9;
            z-index: 1030;
            display: flex; align-items: center;
            padding: 0 24px;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        .topbar .page-title {
            font-size: 15px; font-weight: 600; color: var(--hijau-tua); flex: 1;
        }
        .topbar .btn-sidebar-toggle {
            display: none;
            border: none; background: none;
            font-size: 20px; color: var(--hijau-tua); cursor: pointer;
        }

        /* ── MAIN CONTENT ────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .content-area {
            padding: 24px;
        }

        /* ── CARDS ───────────────────────────────── */
        .card {
            border: 1px solid #e8ede9;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e8ede9;
            border-radius: 14px 14px 0 0 !important;
            padding: 16px 20px;
            font-weight: 600;
        }

        /* ── STAT CARDS ──────────────────────────── */
        .stat-card {
            background: #fff;
            border: 1px solid #e8ede9;
            border-radius: 14px;
            padding: 20px;
            display: flex; align-items: center; gap: 16px;
            transition: transform .2s, box-shadow .2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .stat-icon.green  { background: var(--hijau-light); color: var(--hijau-tua); }
        .stat-icon.blue   { background: #e8f0fe; color: #1a5296; }
        .stat-icon.orange { background: #fff3e0; color: #e65100; }
        .stat-icon.purple { background: #f3e8fd; color: #6a1b9a; }
        .stat-value { font-size: 26px; font-weight: 800; color: var(--hijau-tua); line-height: 1; }
        .stat-label { font-size: 12px; color: #6b7c70; font-weight: 500; margin-top: 3px; }

        /* ── BUTTONS ─────────────────────────────── */
        .btn-hijau { background: var(--hijau-tua); color: #fff; border: none; }
        .btn-hijau:hover { background: var(--hijau-med); color: #fff; }
        .btn-hijau-outline { border: 2px solid var(--hijau-tua); color: var(--hijau-tua); background: transparent; }
        .btn-hijau-outline:hover { background: var(--hijau-tua); color: #fff; }

        /* ── TABLE ───────────────────────────────── */
        .table-simkar th {
            background: var(--hijau-pale);
            color: var(--hijau-tua);
            font-weight: 600; font-size: 12px;
            text-transform: uppercase; letter-spacing: .4px;
            border-bottom: 2px solid #d4e8da;
            white-space: nowrap;
        }
        .table-simkar td { vertical-align: middle; font-size: 13.5px; }
        .table-simkar tbody tr:hover { background: var(--hijau-pale); }

        /* ── BADGE ───────────────────────────────── */
        .badge-karpim  { background: #e8f0fe; color: #1a5296; }
        .badge-karpel  { background: var(--hijau-light); color: var(--hijau-tua); }
        .badge-pkwt    { background: #fff3e0; color: #e65100; }
        .badge-papam   { background: #f3e8fd; color: #6a1b9a; }

        /* ── FILTER PANEL ────────────────────────── */
        .filter-panel {
            background: #fff;
            border: 1px solid #e8ede9;
            border-radius: 14px;
            overflow: hidden;
        }
        .filter-group { padding: 14px 16px; border-bottom: 1px solid #f0f4f1; }
        .filter-group:last-child { border-bottom: none; }
        .filter-group-title {
            font-size: 11px; font-weight: 700;
            color: var(--hijau-tua); text-transform: uppercase;
            letter-spacing: .5px; margin-bottom: 8px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .filter-search {
            border: 1px solid #d4e8da; border-radius: 8px;
            font-size: 12px; padding: 5px 10px;
            width: 100%; margin-bottom: 8px; outline: none;
        }
        .filter-search:focus { border-color: var(--hijau-med); }
        .filter-list { max-height: 160px; overflow-y: auto; }
        .filter-list::-webkit-scrollbar { width: 4px; }
        .filter-list::-webkit-scrollbar-thumb { background: #d4e8da; border-radius: 2px; }
        .filter-list .form-check { margin-bottom: 4px; }
        .filter-list .form-check-label { font-size: 12.5px; cursor: pointer; }
        .filter-list .form-check-input:checked { background-color: var(--hijau-tua); border-color: var(--hijau-tua); }
        .filter-badge {
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--hijau-tua); color: #fff;
            font-size: 10px; font-weight: 700;
            width: 18px; height: 18px; border-radius: 50%;
        }

        /* ── ALERTS ──────────────────────────────── */
        .alert { border-radius: 10px; border: none; font-size: 13.5px; }
        .alert-success { background: var(--hijau-light); color: var(--hijau-tua); }
        .alert-danger   { background: #fce8e8; color: #c0392b; }

        /* ── EMPTY STATE ─────────────────────────── */
        .empty-state { text-align: center; padding: 60px 20px; color: #8ba390; }
        .empty-state i { font-size: 56px; margin-bottom: 16px; opacity: .4; }
        .empty-state h5 { font-weight: 600; color: #4a6650; }

        /* ── RESPONSIVE ──────────────────────────── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { left: 0; }
            .topbar .btn-sidebar-toggle { display: block; }
            .sidebar-overlay {
                display: none; position: fixed; inset: 0;
                background: rgba(0,0,0,.5); z-index: 1039;
            }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">🌴</div>
        <h6>SIMKAR PTPN IV</h6>
        <small>Kebun Pelaihari · Regional 5</small>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-fill"></i> Dashboard
        </a>

        <a href="{{ route('employees.index') }}"
           class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Data Karyawan
        </a>

        @auth
        @if(auth()->user()->isAdmin())
        <div class="nav-section-label">Administrasi</div>

        <a href="{{ route('users.index') }}"
           class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-shield-person-fill"></i> Manajemen Pengguna
        </a>

        <a href="{{ route('employees.import.form') }}"
           class="nav-link {{ request()->routeIs('employees.import*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-arrow-up-fill"></i> Import Excel
        </a>
        @endif
        @endauth

        <div class="nav-section-label">Akun</div>

        <a href="{{ route('profile.edit') }}"
           class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> Profil Saya
        </a>

        <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
            @csrf
            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
                    style="color:rgba(255,255,255,.72)">
                <i class="bi bi-box-arrow-left"></i> Keluar
            </button>
        </form>
    </nav>

    <div class="sidebar-footer">
        @auth
        <div class="sidebar-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->role_label }}</div>
            </div>
        </div>
        @endauth
    </div>
</aside>

<!-- TOPBAR -->
<header class="topbar">
    <button class="btn-sidebar-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    <div class="page-title">@yield('page-title', 'SIMKAR')</div>

    @auth
    <div class="dropdown">
        <button class="btn btn-sm btn-light rounded-pill px-3 d-flex align-items-center gap-2"
                data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
            <span class="d-none d-md-inline" style="font-size:13px">{{ auth()->user()->name }}</span>
            <i class="bi bi-chevron-down" style="font-size:10px"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:180px">
            <li><a class="dropdown-item small" href="{{ route('profile.edit') }}">
                <i class="bi bi-person me-2"></i>Profil Saya</a></li>
            @if(auth()->user()->isAdmin())
            <li><a class="dropdown-item small" href="{{ route('users.index') }}">
                <i class="bi bi-shield-person me-2"></i>Pengguna</a></li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item small text-danger">
                        <i class="bi bi-box-arrow-left me-2"></i>Keluar
                    </button>
                </form>
            </li>
        </ul>
    </div>
    @endauth
</header>

<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="content-area">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @yield('content')
    </div>
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 5000);
</script>

@stack('scripts')
</body>
</html>
