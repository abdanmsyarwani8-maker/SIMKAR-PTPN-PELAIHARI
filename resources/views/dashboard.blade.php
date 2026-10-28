
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<style>
    .dashboard-page {
        --dash-green: #176638;
        --dash-green-dark: #104b2a;
        --dash-bg: #f4f7f5;
        --dash-border: #e5ebe7;
        color: #24352b;
    }

    .dashboard-page .stat-card {
        display: flex;
        align-items: center;
        gap: 16px;
        height: 100%;
        min-height: 112px;
        padding: 22px;
        background: #fff;
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(25, 60, 35, .035);
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .dashboard-page .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(25, 60, 35, .08);
    }

    .dashboard-page .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 54px;
        height: 54px;
        border-radius: 15px;
        font-size: 23px;
    }

    .dashboard-page .stat-icon.green {
        color: #176638;
        background: #e6f4eb;
    }

    .dashboard-page .stat-icon.blue {
        color: #2862ad;
        background: #e9f0fd;
    }

    .dashboard-page .stat-icon.orange {
        color: #c46616;
        background: #fff1df;
    }

    .dashboard-page .stat-icon.purple {
        color: #7842a5;
        background: #f1e8fc;
    }

    .dashboard-page .stat-value {
        color: var(--dash-green);
        font-size: clamp(23px, 2vw, 30px);
        font-weight: 750;
        line-height: 1.2;
        letter-spacing: -.5px;
    }

    .dashboard-page .stat-label {
        margin-top: 5px;
        color: #65736a;
        font-size: 12.5px;
        line-height: 1.4;
    }

    .dashboard-page .dashboard-card {
        height: 100%;
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        box-shadow: 0 3px 12px rgba(25, 60, 35, .035);
    }

    .dashboard-page .dashboard-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 58px;
        padding: 17px 21px;
        border-bottom: 1px solid var(--dash-border);
        color: #24352b;
        font-size: 14px;
        font-weight: 700;
    }

    .dashboard-page .dashboard-card-header i {
        color: var(--dash-green);
        font-size: 17px;
    }

    .dashboard-page .dashboard-card-body {
        padding: 20px;
    }

    .dashboard-page .summary-row {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 13px 20px;
        border-bottom: 1px solid #edf1ee;
    }

    .dashboard-page .summary-row:last-child {
        border-bottom: none;
    }

    .dashboard-page .summary-name {
        color: #34463a;
        font-size: 12.5px;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .dashboard-page .summary-number {
        color: var(--dash-green);
        font-size: 14px;
        font-weight: 750;
    }

    .dashboard-page .summary-percent {
        margin-top: 2px;
        color: #8a958e;
        font-size: 10.5px;
    }

    .dashboard-page .progress {
        height: 6px;
        overflow: hidden;
        background: #edf2ee;
        border-radius: 10px;
    }

    .dashboard-page .progress-bar {
        border-radius: 10px;
        background: #39965b;
    }

    .dashboard-page .age-row {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px 20px;
        border-bottom: 1px solid #edf1ee;
    }

    .dashboard-page .age-row:last-child {
        border-bottom: none;
    }

    .dashboard-page .age-label {
        width: 125px;
        flex-shrink: 0;
        color: #4d5d52;
        font-size: 12px;
    }

    .dashboard-page .age-count {
        width: 35px;
        flex-shrink: 0;
        color: #244b32;
        font-size: 12px;
        font-weight: 700;
        text-align: right;
    }

    .dashboard-page .unit-rank {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        color: #477454;
        background: #edf5ef;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .dashboard-page .unit-rank.first {
        color: #8a6418;
        background: #fff2cf;
    }

    .dashboard-page .unit-name {
        color: #34463a;
        font-size: 12px;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .dashboard-page .unit-total {
        padding: 5px 9px;
        color: #176638;
        background: #e9f5ec;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .dashboard-page .empty-state {
        padding: 32px 16px;
        color: #8a958e;
        font-size: 12.5px;
        text-align: center;
    }

    .dashboard-page .empty-state i {
        display: block;
        margin-bottom: 8px;
        color: #bdc9c0;
        font-size: 25px;
    }

    .dashboard-page .section-heading {
        margin: 0;
        color: #26392d;
        font-size: 16px;
        font-weight: 750;
    }

    .dashboard-page .section-description {
        margin-top: 4px;
        color: #87938a;
        font-size: 11.5px;
    }

    .dashboard-page .table {
        vertical-align: middle;
    }

    .dashboard-page .table thead th {
        padding: 13px 16px;
        color: #77857b;
        background: #f8faf8;
        border-bottom: 1px solid var(--dash-border);
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .35px;
        white-space: nowrap;
    }

    .dashboard-page .table tbody td {
        padding: 14px 16px;
        border-color: #edf1ee;
        font-size: 12px;
    }

    .dashboard-page .employee-name {
        color: #245b36;
        font-weight: 650;
        text-decoration: none;
    }

    .dashboard-page .employee-name:hover {
        color: #31834b;
    }

    .dashboard-page .btn-dashboard {
        padding: 7px 12px;
        color: #176638;
        background: #fff;
        border: 1px solid #cfe1d3;
        border-radius: 9px;
        font-size: 11.5px;
        font-weight: 650;
        text-decoration: none;
        white-space: nowrap;
    }

    .dashboard-page .btn-dashboard:hover {
        background: #edf6ef;
    }

    @media (max-width: 767.98px) {
        .dashboard-page .stat-card {
            gap: 11px;
            min-height: 94px;
            padding: 14px;
            border-radius: 13px;
        }

        .dashboard-page .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            font-size: 19px;
        }

        .dashboard-page .stat-value {
            font-size: 23px;
        }

        .dashboard-page .stat-label {
            font-size: 11px;
        }

        .dashboard-page .dashboard-card-header {
            padding: 15px 16px;
        }

        .dashboard-page .dashboard-card-body {
            padding: 16px;
        }

        .dashboard-page .summary-row,
        .dashboard-page .age-row {
            padding-right: 15px;
            padding-left: 15px;
        }

        .dashboard-page .age-label {
            width: 110px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-page .stat-card {
            transition: none;
        }

        .dashboard-page .stat-card:hover {
            transform: none;
        }
    }
</style>

<div class="dashboard-page">

    {{-- Statistik utama --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value">
                        {{ number_format($totalKaryawan) }}
                    </div>
                    <div class="stat-label d-flex align-items-center gap-1 flex-wrap">
                        <span>Karyawan Aktif</span>
                        @if($totalBhl > 0)
                            <a href="{{ route('employees.index', ['statuses' => ['BHL']]) }}" 
                               class="badge rounded-pill text-decoration-none border" 
                               style="background:#f5f3ff; color:#7c3aed; border-color:#ddd6fe !important; font-size:10px; font-weight:600;"
                               title="Lihat Buruh Harian Lepas">
                                BHL: {{ number_format($totalBhl) }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="stat-value">
                        {{ number_format($totalBatih) }}
                    </div>
                    <div class="stat-label">Keluarga / Batih</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="bi bi-diagram-3-fill"></i>
                </div>
                <div>
                    <div class="stat-value">
                        {{ number_format($totalBagian) }}
                    </div>
                    <div class="stat-label">Bagian / Divisi</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div>
                    <div class="stat-value">
                        {{ number_format($totalJabatan) }}
                    </div>
                    <div class="stat-label">Jenis Jabatan</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Rekap status dan pendidikan --}}
    <div class="row g-3 mb-4">

        <div class="col-lg-6">
            <div class="dashboard-card">

                <div class="dashboard-card-header">
                    <i class="bi bi-bar-chart-fill"></i>
                    Rekap Status Karyawan
                </div>

                @if($rekapStatus->isNotEmpty())
                    @foreach($rekapStatus as $item)
                        @php
                            $persentase = $totalKaryawan > 0
                                ? ($item->total / $totalKaryawan) * 100
                                : 0;
                        @endphp

                        <div class="summary-row">
                            <div class="flex-grow-1">
                                <div class="summary-name">
                                    {{ $item->status_karyawan ?: 'Belum Diisi' }}
                                </div>

                                <div class="progress mt-2">
                                    <div
                                        class="progress-bar"
                                        role="progressbar"
                                        style="width: {{ min(100, $persentase) }}%"
                                        aria-valuenow="{{ round($persentase, 1) }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="summary-number">
                                    {{ number_format($item->total) }}
                                </div>
                                <div class="summary-percent">
                                    {{ number_format($persentase, 1) }}%
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($totalBhl > 0)
                    <div class="p-3 border-top d-flex align-items-center justify-content-between" style="background:#fbfaff;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe; font-size:11px;">BHL</span>
                            <span style="font-size:12px; color:#55695c; font-weight:500;">Buruh Harian Lepas (Dipisahkan)</span>
                        </div>
                        <a href="{{ route('employees.index', ['statuses' => ['BHL']]) }}" class="fw-700 text-decoration-none" style="color:#6d28d9; font-size:12.5px;">
                            {{ number_format($totalBhl) }} orang <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="bi bi-bar-chart"></i>
                        Belum ada data status karyawan.
                    </div>
                @endif

            </div>
        </div>

        <div class="col-lg-6">
            <div class="dashboard-card">

                <div class="dashboard-card-header">
                    <i class="bi bi-mortarboard-fill"></i>
                    Pendidikan Terakhir
                </div>

                @if($rekapPendidikan->isNotEmpty())
                    @foreach($rekapPendidikan as $item)
                        @php
                            $persentase = $totalKaryawan > 0
                                ? ($item->total / $totalKaryawan) * 100
                                : 0;
                        @endphp

                        <div class="summary-row">
                            <div class="flex-grow-1">
                                <div class="summary-name">
                                    {{ $item->pendidikan_terakhir }}
                                </div>

                                <div class="progress mt-2">
                                    <div
                                        class="progress-bar"
                                        role="progressbar"
                                        style="width: {{ min(100, $persentase) }}%"
                                        aria-valuenow="{{ round($persentase, 1) }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="summary-number">
                                    {{ number_format($item->total) }}
                                </div>
                                <div class="summary-percent">
                                    {{ number_format($persentase, 1) }}%
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="bi bi-mortarboard"></i>
                        Belum ada data pendidikan.
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- Distribusi usia dan unit terbanyak --}}
    <div class="row g-3 mb-4">

        <div class="col-lg-6">
            <div class="dashboard-card">

                <div class="dashboard-card-header">
                    <i class="bi bi-clock-fill"></i>
                    Distribusi Usia
                </div>

                <div class="dashboard-card-body pb-2">
                    <p class="section-description mt-0 mb-3">
                        Pengelompokan usia berdasarkan tanggal lahir karyawan aktif.
                    </p>
                </div>

                @php
                    $totalUsiaTerdata = collect($rekapUsia)->sum();

                    $warnaUsia = [
                        'Di bawah 22 tahun' => '#79b5e8',
                        '22–30 tahun' => '#39965b',
                        '31–40 tahun' => '#2c7c49',
                        '41–50 tahun' => '#e3a348',
                        '51 tahun ke atas' => '#9770c4',
                    ];
                @endphp

                @foreach($rekapUsia as $rentang => $jumlah)
                    @php
                        $persentaseUsia = $totalUsiaTerdata > 0
                            ? ($jumlah / $totalUsiaTerdata) * 100
                            : 0;

                        $warna = $warnaUsia[$rentang] ?? '#39965b';
                    @endphp

                    <div class="age-row">
                        <div class="age-label">
                            {{ $rentang }}
                        </div>

                        <div class="flex-grow-1">
                            <div class="progress">
                                <div
                                    class="progress-bar"
                                    style="width: {{ min(100, $persentaseUsia) }}%; background: {{ $warna }}">
                                </div>
                            </div>
                        </div>

                        <div class="age-count">
                            {{ number_format($jumlah) }}
                        </div>
                    </div>
                @endforeach

                <div class="px-3 py-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted" style="font-size:11.5px">
                            Total Karyawan Terdata Usia
                        </span>
                        <strong style="font-size:12px;color:#176638">
                            {{ number_format($totalUsiaTerdata) }} orang
                        </strong>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-6">
            <div class="dashboard-card">

                <div class="dashboard-card-header">
                    <i class="bi bi-diagram-3-fill"></i>
                    5 Bagian dengan Karyawan Terbanyak
                </div>

                <div class="dashboard-card-body pb-2">
                    <p class="section-description mt-0 mb-3">
                        Peringkat bagian / divisi berdasarkan jumlah karyawan aktif.
                    </p>
                </div>

                @if($rekapBagian->isNotEmpty())
                    @foreach($rekapBagian as $index => $item)
                        <div class="summary-row">
                            <div class="unit-rank {{ $index === 0 ? 'first' : '' }}">
                                {{ $index + 1 }}
                            </div>

                            <div class="flex-grow-1">
                                <div class="unit-name">
                                    {{ $item->bagian?->nama_bagian ?? 'Bagian tidak diketahui' }}
                                </div>

                                <div class="progress mt-2">
                                    <div
                                        class="progress-bar"
                                        style="width: {{ $rekapBagian->max('total') > 0 ? ($item->total / $rekapBagian->max('total')) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>

                            <div class="unit-total">
                                {{ number_format($item->total) }} orang
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="bi bi-diagram-3"></i>
                        Belum ada data karyawan per bagian.
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- Data karyawan terbaru --}}
    <div class="dashboard-card mb-4">

        <div class="dashboard-card-header justify-content-between flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-lines-fill"></i>
                Karyawan Terbaru
            </div>

            <a
                href="{{ route('employees.index') }}"
                class="btn-dashboard">
                Lihat Semua
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @if($karyawanTerbaru->isNotEmpty())
            <div class="table-responsive">
                <table class="table mb-0">

                    <thead>
                        <tr>
                            <th style="width:45px">No.</th>
                            <th>Nama Karyawan</th>
                            <th>NIK SAP</th>
                            <th>Bagian</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($karyawanTerbaru as $index => $k)
                            @php
                                $status = $k->status_karyawan ?? '';

                                $kelasStatus = match(true) {
                                    str_contains($status, 'Karpim') => 'badge-karpim',
                                    str_contains($status, 'Karpel') => 'badge-karpel',
                                    str_contains($status, 'PKWT')   => 'badge-pkwt',
                                    str_contains($status, 'KTNG')   => 'badge-ktng',
                                    str_contains($status, 'BHL')    => 'badge-bhl',
                                    str_contains($status, 'Papam')  => 'badge-papam',
                                    default => 'bg-secondary text-white',
                                };
                            @endphp

                            <tr>
                                <td class="text-muted">
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    <a
                                        href="{{ route('employees.show', $k) }}"
                                        class="employee-name">
                                        {{ $k->nama }}
                                    </a>
                                </td>

                                <td>
                                    <span style="font-size:12.5px;color:#2c3e50">{{ $k->personnel_number ?? '-' }}</span>
                                </td>

                                <td>
                                    {{ $k->bagian?->nama_bagian ?? '-' }}
                                </td>

                                <td>
                                    {{ $k->jabatan?->nama_jabatan ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge {{ $kelasStatus }}">
                                        {{ $status ?: 'Belum Diisi' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-people-fill"></i>
                Belum ada data karyawan yang dapat ditampilkan.
            </div>
        @endif

    </div>

</div>

@endsection
