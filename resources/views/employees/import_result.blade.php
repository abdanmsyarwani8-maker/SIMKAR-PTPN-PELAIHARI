@extends('layouts.app')
@section('title', 'Hasil Import')
@section('page-title', 'Hasil Import Data Karyawan')

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-hijau rounded-pill">
        <i class="bi bi-people me-1"></i> Lihat Data Karyawan
    </a>
    <a href="{{ route('employees.import.form') }}" class="btn btn-sm btn-light rounded-pill ms-2">
        <i class="bi bi-upload me-1"></i> Import Lagi
    </a>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-person-plus-fill"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['inserted']) }}</div>
                <div class="stat-label">Karyawan Baru Ditambahkan</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['updated']) }}</div>
                <div class="stat-label">Data Diperbarui</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-skip-forward-fill"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['skipped']) }}</div>
                <div class="stat-label">Baris Dilewati</div>
            </div>
        </div>
    </div>
</div>

{{-- Hasil per lembar --}}
@if(!empty($results['sheet_results']))
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-table me-2 text-success"></i>Hasil per Lembar</div>
    <div class="table-responsive">
        <table class="table table-simkar mb-0">
            <thead>
                <tr><th>Nama Lembar</th><th>Ditambah</th><th>Diperbarui</th><th>Dilewati</th><th>Total</th></tr>
            </thead>
            <tbody>
                @foreach($results['sheet_results'] as $sr)
                <tr>
                    <td class="fw-600">{{ $sr['sheet'] }}</td>
                    <td><span class="badge" style="background:#e8f5e9;color:#2e7d32">+{{ $sr['inserted'] }}</span></td>
                    <td><span class="badge" style="background:#e8f0fe;color:#1565c0">↻{{ $sr['updated'] }}</span></td>
                    <td><span class="badge bg-light text-muted">{{ $sr['skipped'] }}</span></td>
                    <td class="fw-600">{{ $sr['inserted'] + $sr['updated'] + $sr['skipped'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Error log --}}
@if(!empty($results['errors']))
<div class="card">
    <div class="card-header text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Log Error ({{ count($results['errors']) }} error)</div>
    <div class="card-body p-0">
        @foreach($results['errors'] as $err)
        <div class="px-4 py-2 border-bottom" style="font-size:12.5px;color:#c0392b;font-family:monospace">
            {{ $err }}
        </div>
        @endforeach
    </div>
</div>
@else
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    Import selesai tanpa error!
</div>
@endif
@endsection
