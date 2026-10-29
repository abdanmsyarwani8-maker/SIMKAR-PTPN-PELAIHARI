@extends('layouts.app')
@section('title', 'Snapshot Bulanan')
@section('page-title', 'Snapshot Bulanan')

@push('styles')
<style>
.snapshot-card {
    background: #fff;
    border: 1px solid #e0eae3;
    border-radius: 14px;
    transition: transform .2s, box-shadow .2s;
}
.snapshot-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,.07);
}
.snapshot-badge-month {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--hijau-light);
    color: var(--hijau-tua);
    border-radius: 10px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 700;
}
.snapshot-meta {
    font-size: 12px;
    color: #7a9080;
}
.snapshot-count {
    font-size: 22px;
    font-weight: 800;
    color: var(--hijau-tua);
    line-height: 1;
}
.form-section-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--hijau-tua);
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>
@endpush

@section('content')
<div class="row g-4">
    {{-- Kolom Kiri: Form Simpan Snapshot --}}
    @auth
    @if(auth()->user()->isAdmin())
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Simpan Snapshot Bulan Ini</span>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{!! session('success') !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>{!! session('error') !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <p class="text-muted mb-3" style="font-size:13px">
                    Simpan data seluruh karyawan aktif sebagai snapshot Excel untuk arsip bulanan.
                    Jika bulan & tahun sudah ada, file akan ditimpa.
                </p>

                <form method="POST" action="{{ route('snapshots.store') }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-600" style="font-size:12.5px;color:var(--hijau-tua)">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                @foreach($bulanIndo as $num => $label)
                                <option value="{{ $num }}" {{ (int) now()->month === $num ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-600" style="font-size:12.5px;color:var(--hijau-tua)">Tahun</label>
                            <input type="number" name="tahun" class="form-control" value="{{ now()->year }}" min="2020" max="2099" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:12.5px;color:var(--hijau-tua)">Catatan <span class="text-muted fw-400">(opsional)</span></label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Data setelah mutasi Oktober" maxlength="500"></textarea>
                    </div>
                    <button type="submit" class="btn btn-hijau rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2 w-100 justify-content-center">
                        <i class="bi bi-archive-fill"></i> Simpan Snapshot
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endauth

    {{-- Kolom Kanan: Daftar Snapshot --}}
    <div class="{{ auth()->user()?->isAdmin() ? 'col-lg-8' : 'col-12' }}">
        <div class="card">
            <div class="card-header justify-content-between flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-archive text-success"></i>
                    <span>Riwayat Snapshot Bulanan</span>
                </div>
                <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:11.5px">
                    {{ $snapshots->count() }} Snapshot
                </span>
            </div>
            <div class="card-body p-4">
                @if($snapshots->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-archive"></i>
                    <h5>Belum Ada Snapshot</h5>
                    <p class="text-muted" style="font-size:13px">
                        @if(auth()->user()?->isAdmin())
                        Gunakan form di samping untuk menyimpan snapshot data karyawan per bulan.
                        @else
                        Admin belum membuat snapshot data karyawan bulanan.
                        @endif
                    </p>
                </div>
                @else
                <div class="row g-3">
                    @foreach($snapshots as $snap)
                    <div class="col-md-6 col-xl-4">
                        <div class="snapshot-card p-3 h-100 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="snapshot-badge-month">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $snap->bulan_label }} {{ $snap->tahun }}
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="snapshot-count">{{ number_format($snap->jumlah_karyawan) }}</div>
                                <div class="snapshot-meta">karyawan tersimpan</div>
                            </div>
                            @if($snap->catatan)
                            <div class="snapshot-meta mb-2" style="font-style:italic">
                                "{{ Str::limit($snap->catatan, 60) }}"
                            </div>
                            @endif
                            <div class="snapshot-meta mb-3">
                                <i class="bi bi-person-fill"></i> {{ $snap->user?->name ?? 'Sistem' }}
                                &middot;
                                <i class="bi bi-clock"></i> {{ $snap->created_at->translatedFormat('d M Y H:i') }}
                            </div>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('snapshots.download', $snap) }}" class="btn btn-sm btn-hijau-outline rounded-pill flex-fill d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                @if(auth()->user()?->isAdmin())
                                <form method="POST" action="{{ route('snapshots.destroy', $snap) }}" onsubmit="return confirm('Hapus snapshot {{ $snap->bulan_label }} {{ $snap->tahun }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill d-inline-flex align-items-center gap-1" title="Hapus">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
