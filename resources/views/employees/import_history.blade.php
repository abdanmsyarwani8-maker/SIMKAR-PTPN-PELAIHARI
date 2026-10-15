@extends('layouts.app')
@section('title', 'Riwayat Import Data')
@section('page-title', 'Riwayat Import Data')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <div style="font-size:13px;color:#55695c;font-weight:500;">
            Total: <strong class="text-dark">{{ $histories->total() }}</strong> riwayat import tercatat
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <a href="{{ route('employees.import.form') }}" class="btn btn-hijau btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-arrow-up-fill"></i> Import File Baru
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card">
    @if($histories->isEmpty())
    <div class="empty-state py-5 text-center">
        <i class="bi bi-clock-history fs-1 text-muted d-block mb-3"></i>
        <h5 class="fw-700" style="color:var(--hijau-tua)">Belum Ada Riwayat Import</h5>
        <p class="text-muted small mb-3">Setiap file Excel yang diimport akan otomatis dicatat hasilnya di sini.</p>
        <a href="{{ route('employees.import.form') }}" class="btn btn-hijau btn-sm rounded-pill px-4">
            <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Mulai Import Sekarang
        </a>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-simkar mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Waktu Import</th>
                    <th>Nama File</th>
                    <th>Pengunggah</th>
                    <th>Karyawan Baru</th>
                    <th>Karyawan Update</th>
                    <th>Batih Diproses</th>
                    <th>Dilewati</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($histories as $h)
                <tr>
                    <td class="text-muted" style="font-size:12px">
                        {{ $histories->firstItem() + $loop->index }}
                    </td>
                    <td>
                        <div class="fw-600" style="font-size:13px;color:var(--hijau-tua)">
                            {{ $h->created_at->translatedFormat('d M Y, H:i') }}
                        </div>
                        <small class="text-muted" style="font-size:11px">
                            {{ $h->created_at->diffForHumans() }}
                        </small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-excel text-success fs-5"></i>
                            <div>
                                <span class="fw-600" style="font-size:13px">{{ $h->file_name }}</span>
                                @if(!empty($h->sheet_results))
                                <div class="text-muted" style="font-size:11px">
                                    {{ count($h->sheet_results) }} lembar diproses
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border" style="font-size:11.5px">
                            <i class="bi bi-person me-1 text-muted"></i>
                            {{ $h->user?->name ?? 'Sistem / Admin' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:12px">
                            +{{ number_format($h->inserted) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:#e8f0fe;color:#1565c0;font-size:12px">
                            ↻{{ number_format($h->updated) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:#f3e8fd;color:#7e22ce;font-size:12px">
                            {{ number_format($h->family_processed) }}
                        </span>
                        <small class="text-muted ms-1" style="font-size:11px">
                            (+{{ $h->family_inserted }} / ↻{{ $h->family_updated }})
                        </small>
                    </td>
                    <td>
                        @if($h->skipped > 0)
                        <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:12px">
                            {{ number_format($h->skipped) }}
                        </span>
                        @else
                        <span class="text-muted" style="font-size:12px">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('employees.import.history.show', $h) }}" class="btn-action" title="Lihat Hasil Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('employees.import.history.destroy', $h) }}" onsubmit="return confirm('Hapus catatan riwayat import ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-action-danger border-0" title="Hapus Riwayat">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($histories->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div style="font-size:12.5px;color:#6b7c70">
            Menampilkan {{ $histories->firstItem() }}–{{ $histories->lastItem() }} dari {{ $histories->total() }} riwayat
        </div>
        <div>
            {{ $histories->links() }}
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
