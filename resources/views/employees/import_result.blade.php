@extends('layouts.app')
@section('title', 'Hasil Import')
@section('page-title', 'Hasil Import Data Karyawan & Batih')

@section('content')
<div class="mb-3 d-flex align-items-center gap-2">
    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-hijau rounded-pill px-3">
        <i class="bi bi-people-fill me-1"></i> Lihat Data Karyawan
    </a>
    <a href="{{ route('employees.import.form') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Import File Lain
    </a>
</div>

{{-- Ringkasan Statistik --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-person-plus-fill"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['inserted']) }}</div>
                <div class="stat-label">Karyawan Baru Ditambahkan</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['updated']) }}</div>
                <div class="stat-label">Karyawan Diperbarui</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon purple" style="background:#f3e8fd;color:#7e22ce"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['family_processed']) }}</div>
                <div class="stat-label">Anggota Batih Diproses</div>
                <small class="text-muted" style="font-size:11px">
                    +{{ number_format($results['family_inserted']) }} baru &nbsp;·&nbsp; ↻{{ number_format($results['family_updated']) }} diperbarui
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-skip-forward-fill"></i></div>
            <div>
                <div class="stat-value">{{ number_format($results['skipped']) }}</div>
                <div class="stat-label">Baris Dilewati</div>
            </div>
        </div>
    </div>
</div>

{{-- Rincian Hasil per Lembar --}}
@if(!empty($results['sheet_results']))
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div><i class="bi bi-table me-2"></i>Rincian Hasil per Lembar Excel</div>
        @if(!empty($results['file_name']))
        <span class="badge bg-light text-dark" style="font-size:12px">
            <i class="bi bi-file-earmark-excel text-success me-1"></i>{{ $results['file_name'] }}
        </span>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-simkar mb-0">
            <thead>
                <tr>
                    <th>Nama Lembar</th>
                    <th>Karyawan Baru</th>
                    <th>Karyawan Diperbarui</th>
                    <th>Batih Diproses</th>
                    <th>Baris Dilewati</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results['sheet_results'] as $sr)
                <tr>
                    <td class="fw-600">
                        <i class="bi bi-file-spreadsheet me-1 text-muted"></i>
                        {{ $sr['sheet'] }}
                    </td>
                    <td><span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:12px">+{{ number_format($sr['inserted']) }}</span></td>
                    <td><span class="badge" style="background:#e8f0fe;color:#1565c0;font-size:12px">↻{{ number_format($sr['updated']) }}</span></td>
                    <td>
                        <span class="badge" style="background:#f3e8fd;color:#7e22ce;font-size:12px">
                            {{ number_format($sr['family_processed']) }}
                        </span>
                        <small class="text-muted ms-1" style="font-size:11px">
                            (+{{ $sr['family_inserted'] }} / ↻{{ $sr['family_updated'] }})
                        </small>
                    </td>
                    <td><span class="badge bg-light text-muted" style="font-size:12px">{{ number_format($sr['skipped']) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Catatan Data yang Dilewati & Error --}}
@if(!empty($results['errors']) || !empty($results['skipped_details']))
<div class="card mb-4">
    <div class="card-header text-danger d-flex align-items-center justify-content-between">
        <span><i class="bi bi-exclamation-triangle-fill me-2"></i>Catatan Pemeriksaan & Data Dilewati</span>
        <span class="badge bg-danger-subtle text-danger">
            {{ count($results['errors']) + count($results['skipped_details']) }} catatan
        </span>
    </div>
    <div class="card-body p-0" style="max-height:350px;overflow-y:auto">
        @php
            $uniqueNotes = array_unique(array_merge($results['errors'] ?? [], $results['skipped_details'] ?? []));
        @endphp
        @foreach($uniqueNotes as $note)
        <div class="px-4 py-2 border-bottom d-flex align-items-start gap-2" style="font-size:12.5px;color:#c0392b;font-family:monospace">
            <i class="bi bi-info-circle-fill mt-1 text-warning flex-shrink-0"></i>
            <div>{{ $note }}</div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div>
        <strong>Import Berhasil!</strong> Seluruh baris data karyawan dan batih berhasil diproses tanpa kendala.
    </div>
</div>
@endif
@endsection
