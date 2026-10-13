@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    {{-- Stat Cards --}}
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value">{{ number_format($totalKaryawan) }}</div>
                <div class="stat-label">Total Karyawan Aktif</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-building-fill"></i></div>
            <div>
                <div class="stat-value">{{ $totalUnit }}</div>
                <div class="stat-label">Unit / Kebun</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-diagram-3-fill"></i></div>
            <div>
                <div class="stat-value">{{ $totalBagian }}</div>
                <div class="stat-label">Bagian / Divisi</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-briefcase-fill"></i></div>
            <div>
                <div class="stat-value">{{ $totalJabatan }}</div>
                <div class="stat-label">Jenis Jabatan</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Rekap Status Karyawan --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-fill text-success"></i>
                Rekap Status Karyawan
            </div>
            <div class="card-body p-0">
                @forelse($rekapStatus as $item)
                <div class="d-flex align-items-center px-4 py-3 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-500" style="font-size:13.5px">{{ $item->status_karyawan ?? 'Belum Diisi' }}</div>
                        <div class="progress mt-1" style="height:6px;border-radius:4px">
                            @php $pct = $totalKaryawan > 0 ? ($item->total / $totalKaryawan * 100) : 0 @endphp
                            <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    <div class="ms-3 text-end">
                        <span class="fw-700" style="font-size:16px;color:#1a5c2e">{{ number_format($item->total) }}</span>
                        <div style="font-size:11px;color:#888">{{ number_format($pct,1) }}%</div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted" style="font-size:13px">Belum ada data</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Rekap Jenis Kelamin & Agama --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-gender-ambiguous text-primary"></i>
                Jenis Kelamin
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($rekapJenisKelamin as $item)
                    <div class="col-6">
                        <div class="text-center p-3 rounded-3" style="background:{{ $item->jenis_kelamin=='Laki-Laki'?'#e8f0fe':'#fce8f5' }}">
                            <div style="font-size:28px">{{ $item->jenis_kelamin=='Laki-Laki'?'👨':'👩' }}</div>
                            <div class="fw-700" style="font-size:20px;color:#1a5c2e">{{ number_format($item->total) }}</div>
                            <div style="font-size:12px;color:#666">{{ $item->jenis_kelamin }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-moon-stars-fill text-warning"></i>
                Agama
            </div>
            <div class="card-body p-0">
                @foreach($rekapAgama as $item)
                <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom">
                    <span style="font-size:13px">{{ $item->agama }}</span>
                    <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:12px">
                        {{ number_format($item->total) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Rentang Usia --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-info"></i>
                Distribusi Usia
            </div>
            <div class="card-body p-0">
                @foreach($rekapUsia as $range => $count)
                @if($count > 0)
                <div class="d-flex align-items-center px-4 py-2 border-bottom">
                    <span class="flex-grow-1" style="font-size:13px">{{ $range }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:80px">
                            @php $pct = $totalKaryawan > 0 ? ($count/$totalKaryawan*100) : 0 @endphp
                            <div class="progress" style="height:5px">
                                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        <span class="fw-600" style="font-size:13px;width:35px;text-align:right">{{ $count }}</span>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top Unit --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                Top 10 Unit Terbesar
            </div>
            <div class="card-body p-0">
                @foreach($rekapUnit as $item)
                <div class="d-flex align-items-center px-4 py-2 border-bottom">
                    <span class="flex-grow-1" style="font-size:12.5px">
                        {{ $item->unit?->nama_unit ?? 'Unit tidak diketahui' }}
                    </span>
                    <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua)">
                        {{ number_format($item->total) }}
                    </span>
                </div>
                @endforeach
                @if($rekapUnit->isEmpty())
                <div class="p-4 text-center text-muted" style="font-size:13px">
                    Belum ada data karyawan
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Karyawan Terbaru --}}
@if($karyawanTerbaru->isNotEmpty())
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-fill text-success"></i>
            Data Karyawan Terbaru Ditambahkan
        </div>
        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-hijau-outline rounded-pill">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-simkar mb-0">
            <thead>
                <tr>
                    <th>Nama</th><th>NIK SAP</th><th>Unit</th><th>Jabatan</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($karyawanTerbaru as $k)
                <tr>
                    <td>
                        <a href="{{ route('employees.show', $k) }}" class="fw-600 text-decoration-none" style="color:var(--hijau-tua)">
                            {{ $k->nama }}
                        </a>
                    </td>
                    <td><code style="font-size:12px">{{ $k->personnel_number ?? '-' }}</code></td>
                    <td style="font-size:12.5px">{{ $k->unit?->nama_unit ?? '-' }}</td>
                    <td style="font-size:12.5px">{{ $k->jabatan?->nama_jabatan ?? '-' }}</td>
                    <td>
                        @php
                            $sc = match(true) {
                                str_contains($k->status_karyawan??'', 'Karpim') => 'badge-karpim',
                                str_contains($k->status_karyawan??'', 'Karpel') => 'badge-karpel',
                                str_contains($k->status_karyawan??'', 'Papam')  => 'badge-papam',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <span class="badge {{ $sc }}" style="font-size:11px">
                            {{ $k->status_karyawan ?? '-' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
