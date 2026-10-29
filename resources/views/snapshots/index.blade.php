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
.option-pill-choice {
    transition: all .2s ease;
    border: 1.5px solid #dce8e0;
}
.option-pill-choice:hover {
    border-color: var(--hijau-med);
    background: #f7fbf8;
}
.option-pill-choice.selected {
    border-color: var(--hijau-tua);
    background: #f0f7f3;
}
.category-check-item {
    transition: background-color .15s ease, border-color .15s ease;
    border: 1px solid #e3ece6;
}
.category-check-item:hover {
    border-color: var(--hijau-med);
    background: #fbfdfc;
}
</style>
@endpush

@section('content')
@if(!empty($dbError))
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4 p-3 rounded-3 shadow-sm border border-warning" role="alert">
    <i class="bi bi-database-exclamation text-warning fs-3 flex-shrink-0"></i>
    <div style="font-size:13px;line-height:1.5">
        <strong>Pemberitahuan Sistem Database:</strong> {{ $dbError }}
        <div class="mt-1 text-muted" style="font-size:12px">
            Jika ini terjadi di hosting cPanel, pastikan tabel <code>monthly_snapshots</code> sudah di-migrate atau diimpor di phpMyAdmin.
        </div>
    </div>
</div>
@endif

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
                    Simpan arsip bulanan data karyawan aktif ke file Excel. Anda dapat memilih menyimpan lengkap dengan batih atau murni karyawan saja.
                </p>

                <form method="POST" action="{{ route('snapshots.store') }}" id="snapshotForm">
                    @csrf
                    {{-- 1. Periode Bulan & Tahun --}}
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-600 mb-1" style="font-size:12.5px;color:var(--hijau-tua)">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                @foreach($bulanIndo as $num => $label)
                                <option value="{{ $num }}" {{ (int) now()->month === $num ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-600 mb-1" style="font-size:12.5px;color:var(--hijau-tua)">Tahun</label>
                            <input type="number" name="tahun" class="form-control" value="{{ now()->year }}" min="2020" max="2099" required>
                        </div>
                    </div>

                    {{-- 2. Opsi Batih (Keluarga) --}}
                    <div class="mb-3">
                        <label class="form-label fw-600 mb-1" style="font-size:12.5px;color:var(--hijau-tua)">
                            <i class="bi bi-people-fill me-1"></i> Data Batih / Keluarga
                        </label>
                        <div class="d-flex flex-column gap-2">
                            <label class="option-pill-choice selected d-flex align-items-start gap-2 p-2 px-3 rounded-3" style="cursor:pointer" id="lbl_fam_yes">
                                <input type="radio" name="include_family" value="1" checked class="form-check-input mt-1" onchange="updateFamStyle()">
                                <div class="flex-grow-1" style="font-size:12.5px">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <strong style="color:var(--hijau-tua)">Lengkap (+ Data Batih)</strong>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:10px">Default</span>
                                    </div>
                                    <div class="text-muted" style="font-size:11px">Menyertakan kolom nama, hubungan, & tgl lahir anggota keluarga.</div>
                                </div>
                            </label>
                            <label class="option-pill-choice d-flex align-items-start gap-2 p-2 px-3 rounded-3" style="cursor:pointer" id="lbl_fam_no">
                                <input type="radio" name="include_family" value="0" class="form-check-input mt-1" onchange="updateFamStyle()">
                                <div class="flex-grow-1" style="font-size:12.5px">
                                    <strong style="color:var(--hijau-tua)">Simpan Tanpa Batih</strong>
                                    <div class="text-muted" style="font-size:11px">Tepat 1 baris per karyawan, tanpa kolom anggota keluarga.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 3. Kategori Karyawan (Sheet Terpisah, BHL Dipisah) --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-600 mb-0" style="font-size:12.5px;color:var(--hijau-tua)">
                                <i class="bi bi-layers-fill me-1"></i> Status Karyawan (Sheet)
                            </label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-xs btn-outline-success py-0 px-2" style="font-size:10.5px" onclick="toggleCategories(true)">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:10.5px" onclick="toggleCategories(false)">Kosongkan</button>
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-1">
                            {{-- PKWT --}}
                            <label class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2 category-check-item" style="cursor:pointer;font-size:12px">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="categories[]" value="PKWT" checked class="form-check-input mt-0 snap-cat-cb">
                                    <span class="badge badge-pkwt">PKWT</span>
                                    <span>Perjanjian Kerja Waktu Tertentu</span>
                                </div>
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size:11px">{{ number_format($countPkwt ?? 0) }}</span>
                            </label>

                            {{-- Karpim --}}
                            <label class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2 category-check-item" style="cursor:pointer;font-size:12px">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="categories[]" value="Karpim" checked class="form-check-input mt-0 snap-cat-cb">
                                    <span class="badge badge-karpim">Karpim</span>
                                    <span>Karyawan Pimpinan</span>
                                </div>
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size:11px">{{ number_format($countKarpim ?? 0) }}</span>
                            </label>

                            {{-- Karpel --}}
                            <label class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2 category-check-item" style="cursor:pointer;font-size:12px">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="categories[]" value="Karpel" checked class="form-check-input mt-0 snap-cat-cb">
                                    <span class="badge badge-karpel">Karpel</span>
                                    <span>Karyawan Pelaksana</span>
                                </div>
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size:11px">{{ number_format($countKarpel ?? 0) }}</span>
                            </label>

                            {{-- KTNG --}}
                            <label class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2 category-check-item" style="cursor:pointer;font-size:12px">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="categories[]" value="KTNG" checked class="form-check-input mt-0 snap-cat-cb">
                                    <span class="badge badge-ktng">KTNG</span>
                                    <span>Karyawan Tidak Tetap</span>
                                </div>
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size:11px">{{ number_format($countKtng ?? 0) }}</span>
                            </label>

                            {{-- BHL (Buruh Harian Lepas - Dipisah) --}}
                            <label class="d-flex align-items-center justify-content-between p-2 px-3 rounded-2 category-check-item" style="cursor:pointer;font-size:12px;background:#faf8ff">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="categories[]" value="BHL" checked class="form-check-input mt-0 snap-cat-cb">
                                    <span class="badge badge-bhl">BHL</span>
                                    <strong>Buruh Harian Lepas</strong>
                                </div>
                                <span class="badge rounded-pill" style="font-size:11px;background:#ede9fe;color:#6d28d9;border:1px solid #ddd6fe">{{ number_format($countBhl ?? 0) }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- 4. Catatan --}}
                    <div class="mb-3">
                        <label class="form-label fw-600 mb-1" style="font-size:12.5px;color:var(--hijau-tua)">Catatan <span class="text-muted fw-400">(opsional)</span></label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Snapshot akhir bulan setelah rotasi karyawan" maxlength="500"></textarea>
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
                    {{ $snapshots->count() }} Snapshot Tersimpan
                </span>
            </div>
            <div class="card-body p-4">
                @if($snapshots->isEmpty())
                <div class="empty-state text-center py-5">
                    <i class="bi bi-archive text-muted mb-2" style="font-size:42px"></i>
                    <h5 class="fw-700 mt-2">Belum Ada Snapshot</h5>
                    <p class="text-muted" style="font-size:13px">
                        @if(auth()->user()?->isAdmin())
                        Gunakan form di samping untuk menyimpan snapshot arsip data karyawan per bulan.
                        @else
                        Administrator belum membuat snapshot data karyawan bulanan.
                        @endif
                    </p>
                </div>
                @else
                <div class="row g-3">
                    @foreach($snapshots as $snap)
                    <div class="col-md-6 col-xl-4">
                        <div class="snapshot-card p-3 h-100 d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between mb-2 gap-1 flex-wrap">
                                <div class="snapshot-badge-month">
                                    <i class="bi bi-calendar3"></i>
                                    {{ $snap->bulan_label }} {{ $snap->tahun }}
                                </div>
                                <span class="badge {{ ($snap->include_family ?? true) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }}" style="font-size:10.5px">
                                    <i class="bi {{ ($snap->include_family ?? true) ? 'bi-people-fill' : 'bi-person-fill' }} me-1"></i>
                                    {{ $snap->tipe_label }}
                                </span>
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
                                <i class="bi bi-clock"></i> {{ $snap->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                            </div>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('snapshots.download', $snap) }}" class="btn btn-sm btn-hijau-outline rounded-pill flex-fill d-inline-flex align-items-center justify-content-center gap-1">
                                    <i class="bi bi-download"></i> Download
                                </a>
                                @if(auth()->user()?->isAdmin())
                                <form method="POST" action="{{ route('snapshots.destroy', $snap) }}" onsubmit="return confirm('Hapus snapshot {{ $snap->bulan_label }} {{ $snap->tahun }} ({{ $snap->tipe_label }})?')">
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

@push('scripts')
<script>
function updateFamStyle() {
    const isFamYes = document.querySelector('input[name="include_family"]:checked').value === '1';
    const cardYes = document.getElementById('lbl_fam_yes');
    const cardNo = document.getElementById('lbl_fam_no');
    if (cardYes && cardNo) {
        if (isFamYes) {
            cardYes.classList.add('selected');
            cardNo.classList.remove('selected');
        } else {
            cardNo.classList.add('selected');
            cardYes.classList.remove('selected');
        }
    }
}

function toggleCategories(checked) {
    document.querySelectorAll('.snap-cat-cb').forEach(cb => {
        cb.checked = checked;
    });
}

document.getElementById('snapshotForm')?.addEventListener('submit', function (e) {
    const anyChecked = Array.from(document.querySelectorAll('.snap-cat-cb')).some(cb => cb.checked);
    if (!anyChecked) {
        e.preventDefault();
        alert('Silakan pilih minimal satu status karyawan untuk disimpan.');
    }
});
</script>
@endpush
@endsection
