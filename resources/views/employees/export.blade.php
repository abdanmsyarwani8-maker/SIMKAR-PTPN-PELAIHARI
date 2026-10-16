@extends('layouts.app')
@section('title', 'Export Data Karyawan')
@section('page-title', 'Export Data Karyawan')

@push('styles')
<style>
.category-card, .option-card {
    border: 2px solid #e0ede5;
    border-radius: 12px;
    padding: 14px 16px;
    background: #fff;
    cursor: pointer;
    transition: all .2s ease;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.category-card {
    margin-bottom: 10px;
}
.option-card {
    height: 100%;
}
.category-card:hover, .option-card:hover {
    border-color: var(--hijau-med);
    background: #fbfdfc;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,.04);
}
.category-card.selected, .option-card.selected {
    border-color: var(--hijau-tua);
    background: #f4faf6;
}
.category-card .form-check-input, .option-card .form-check-input {
    width: 19px;
    height: 19px;
    margin-top: 2px;
    cursor: pointer;
    flex-shrink: 0;
}
.category-card .form-check-input:checked, .option-card .form-check-input:checked {
    background-color: var(--hijau-tua);
    border-color: var(--hijau-tua);
}
.sheet-preview-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    background: #e8f5e9;
    color: var(--hijau-tua);
    border: 1px solid #c8e6c9;
}
</style>
@endpush

@section('content')
<div class="mb-3 d-flex align-items-center justify-content-end flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill" style="font-size:12.5px">
            <i class="bi bi-people-fill me-1" style="color:var(--hijau-med)"></i> Total Karyawan: <strong>{{ number_format($totalEmployee) }}</strong>
        </span>
        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill" style="font-size:12.5px">
            <i class="bi bi-heart-fill me-1 text-danger"></i> Data Batih/Keluarga: <strong>{{ number_format($totalFamily) }}</strong>
        </span>
    </div>
</div>

<div class="row g-3">
    {{-- Kolom Utama: Formulir Ekspor --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-arrow-down-fill text-success fs-5"></i>
                    <span class="fw-700" style="color:var(--hijau-tua)">Konfigurasi Ekspor Data ke Excel</span>
                </div>
                <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua)">Format .xlsx (Multi-Sheet)</span>
            </div>

            <div class="card-body p-4">
                <form method="GET" action="{{ route('employees.export') }}" id="exportForm">
                    
                    {{-- 1. Pilihan Status / Kategori Karyawan --}}
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-700 mb-1" style="color:var(--hijau-tua)">
                                    <i class="bi bi-layers-fill me-1"></i> 1. Pilih Status Karyawan yang Ingin Diekspor
                                </h6>
                                <p class="text-muted mb-0" style="font-size:13px">
                                    Setiap status yang dipilih akan menjadi <strong>1 sheet terpisah</strong> di dalam 1 file Excel.
                                </p>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success" onclick="selectAllCategories(true)">
                                    Pilih Semua
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="selectAllCategories(false)">
                                    Kosongkan
                                </button>
                            </div>
                        </div>

                        {{-- Category Card: PKWT --}}
                        <label class="category-card selected" for="cat_pkwt" id="card_pkwt">
                            <input class="form-check-input export-cat-check" type="checkbox" name="categories[]" value="PKWT" id="cat_pkwt" checked onchange="updateSelectionState()">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <div>
                                        <span class="badge badge-pkwt me-2">PKWT</span>
                                        <strong style="color:var(--hijau-tua);font-size:14px">Perjanjian Kerja Waktu Tertentu</strong>
                                    </div>
                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size:12px">
                                        {{ number_format($countPkwt) }} Karyawan
                                    </span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:12.5px">
                                    Tenaga kerja kontrak, dilengkapi nomor PKWT & TMT masa kontrak kerja.
                                </div>
                            </div>
                        </label>

                        {{-- Category Card: Karpim --}}
                        <label class="category-card selected" for="cat_karpim" id="card_karpim">
                            <input class="form-check-input export-cat-check" type="checkbox" name="categories[]" value="Karpim" id="cat_karpim" checked onchange="updateSelectionState()">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <div>
                                        <span class="badge badge-karpim me-2">Karpim</span>
                                        <strong style="color:var(--hijau-tua);font-size:14px">Karyawan Pimpinan</strong>
                                    </div>
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle px-2 py-1" style="font-size:12px">
                                        {{ number_format($countKarpim) }} Karyawan
                                    </span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:12.5px">
                                    Karyawan pimpinan tetap PTPN IV Regional V Kebun Pelaihari.
                                </div>
                            </div>
                        </label>

                        {{-- Category Card: Karpel --}}
                        <label class="category-card selected" for="cat_karpel" id="card_karpel">
                            <input class="form-check-input export-cat-check" type="checkbox" name="categories[]" value="Karpel" id="cat_karpel" checked onchange="updateSelectionState()">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <div>
                                        <span class="badge badge-karpel me-2">Karpel</span>
                                        <strong style="color:var(--hijau-tua);font-size:14px">Karyawan Pelaksana</strong>
                                    </div>
                                    <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1" style="font-size:12px">
                                        {{ number_format($countKarpel) }} Karyawan
                                    </span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:12.5px">
                                    Karyawan pelaksana tetap PTPN IV Regional V Kebun Pelaihari.
                                </div>
                            </div>
                        </label>

                        {{-- Category Card: KTNG --}}
                        <label class="category-card selected" for="cat_ktng" id="card_ktng">
                            <input class="form-check-input export-cat-check" type="checkbox" name="categories[]" value="KTNG" id="cat_ktng" checked onchange="updateSelectionState()">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <div>
                                        <span class="badge badge-ktng me-2">KTNG</span>
                                        <strong style="color:var(--hijau-tua);font-size:14px">Karyawan Tidak Tetap / Tenaga Harian</strong>
                                    </div>
                                    <span class="badge rounded-pill bg-teal-subtle text-success-emphasis border border-success-subtle px-2 py-1" style="font-size:12px;background:#e0f2f1">
                                        {{ number_format($countKtng) }} Karyawan
                                    </span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:12.5px">
                                    Tenaga kerja tidak tetap / buruh harian lepas musiman kebun.
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- 2. Pilihan Masukkan Data Keluarga / Batih --}}
                    <div class="mb-4 pt-2 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div>
                                <h6 class="fw-700 mb-1" style="color:var(--hijau-tua)">
                                    <i class="bi bi-people-fill me-1"></i> 2. Opsi Data Keluarga / Batih
                                </h6>
                                <p class="text-muted mb-0" style="font-size:13px">
                                    Pilih apakah file Excel akan menyertakan data anggota keluarga atau murni data profil karyawan saja.
                                </p>
                            </div>
                            <span class="badge" id="badgeFamilySummary" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:12px;padding:6px 12px">
                                Mode Lengkap (+ Batih)
                            </span>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="option-card selected" id="card_fam_yes" for="fam_yes">
                                    <input class="form-check-input" type="radio" name="include_family" id="fam_yes" value="1" checked onchange="updateSelectionState()">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <strong style="color:var(--hijau-tua);font-size:13.5px">
                                                <i class="bi bi-check2-circle text-success me-1"></i> Masukkan Data Keluarga
                                            </strong>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="font-size:11px">Lengkap</span>
                                        </div>
                                        <div class="text-muted" style="font-size:12px;line-height:1.45">
                                            Menyertakan kolom batih (nama, hubungan, jenis kelamin, tgl lahir). Anggota keluarga dimuat di baris tersendiri.
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="option-card" id="card_fam_no" for="fam_no">
                                    <input class="form-check-input" type="radio" name="include_family" id="fam_no" value="0" onchange="updateSelectionState()">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <strong style="color:var(--hijau-tua);font-size:13.5px">
                                                <i class="bi bi-person-x me-1 text-secondary"></i> Tanpa Data Keluarga
                                            </strong>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0" style="font-size:11px">Ringkas</span>
                                        </div>
                                        <div class="text-muted" style="font-size:12px;line-height:1.45">
                                            Hanya profil karyawan. Tepat <strong>1 baris per karyawan</strong> tanpa kolom data keluarga (tabel lebih bersih &amp; padat).
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Live Sheet Generation Preview --}}
                    <div class="p-3 rounded-3 mb-4" style="background:#f0f8f3;border:1.5px dashed #b7dfc5">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                            <div class="fw-700" style="color:var(--hijau-tua);font-size:13px">
                                <i class="bi bi-file-earmark-ruled me-1"></i> Struktur Sheet yang Akan Dihasilkan:
                            </div>
                            <span class="badge" id="sheetCountBadge" style="background:var(--hijau-tua);font-size:12px">
                                3 Sheet
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-2" id="sheetPreviewList">
                            <span class="sheet-preview-pill" id="pill_pkwt"><i class="bi bi-table"></i> Sheet 1: PKWT ({{ $countPkwt }} data)</span>
                            <span class="sheet-preview-pill" id="pill_karpim"><i class="bi bi-table"></i> Sheet 2: Karpim &amp; Karpel ({{ $countKarpim }} data)</span>
                            <span class="sheet-preview-pill" id="pill_ktng"><i class="bi bi-table"></i> Sheet 3: KTNG ({{ $countKtng }} data)</span>
                        </div>
                        <div id="noSheetWarning" class="text-danger small mt-2 d-none">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Silakan pilih minimal 1 kategori karyawan untuk diekspor.
                        </div>
                    </div>

                    {{-- Re-import Compatibility Notice --}}
                    <div class="alert alert-success d-flex align-items-start gap-3 mb-4 p-3" style="border-radius:12px;background:#eaf6ee;border:1px solid #c3e6cb">
                        <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0 mt-1"></i>
                        <div style="font-size:13px;color:#1e4620;line-height:1.6">
                            <strong>Jaminan Kompatibilitas Import Ulang:</strong> Baik opsi <em>Sertakan Data Keluarga</em> maupun <em>Tanpa Data Keluarga</em>, file Excel hasil unduhan memiliki tipe data string NIK SAP (mencegah hilangnya angka 0 di depan) dan <strong>100% kompatibel dan siap di-import kembali ke SIMKAR</strong> kapan pun melalui menu <em>Import Excel</em>.
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-hijau rounded-pill px-4 py-2 fw-600" id="btnDownload">
                            <i class="bi bi-file-earmark-arrow-down-fill me-2 fs-6"></i> Download File Excel (.xlsx)
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill px-3 py-2 text-muted">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Kolom Samping: Petunjuk & Keunggulan --}}
    <div class="col-lg-4">
        {{-- Card Petunjuk --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-primary"></i>
                <span>Petunjuk Ekspor</span>
            </div>
            <div class="card-body" style="font-size:13px;line-height:1.75">
                <ol class="ps-3 mb-0">
                    <li>Pilih status karyawan yang ingin Anda sertakan dalam file.</li>
                    <li>Tentukan apakah ingin <strong>Menyertakan Data Keluarga</strong> atau <strong>Tanpa Data Keluarga</strong>.</li>
                    <li>Jika Anda memilih <strong>1 status</strong>, file akan berisi <strong>1 sheet</strong>.</li>
                    <li>Jika memilih <strong>2 status</strong> (misal PKWT &amp; KTNG), file akan berisi <strong>2 sheet</strong>.</li>
                    <li>Jika memilih <strong>ketiganya</strong>, file akan berisi <strong>3 sheet</strong> lengkap.</li>
                    <li>Klik tombol <strong>Download File Excel</strong> untuk memulai unduhan.</li>
                </ol>
            </div>
        </div>

        {{-- Card Format Kolom & Desain --}}
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success"></i>
                <span>Standar Output Excel SIMKAR</span>
            </div>
            <div class="card-body" style="font-size:12.5px;line-height:1.7">
                <div class="fw-700 mb-1" style="color:var(--hijau-tua)">Kolom Data Karyawan (14 Kolom Standar):</div>
                <div class="text-muted mb-2">
                    <ul class="ps-3 mb-0" style="line-height:1.6">
                        <li><strong>Sheet PKWT:</strong> Menyertakan kolom <em>No. PKWT</em> (tanpa kolom Golongan).</li>
                        <li><strong>Sheet Non-PKWT:</strong> Menyertakan kolom <em>Golongan</em> (tanpa kolom No. PKWT).</li>
                        <li><strong>Kolom Bersama:</strong> NIK SAP, Nama, Status, TMT Kerja, Tanggal Lahir, Jenis Kelamin, Agama, Pendidikan Terakhir, Grade, Bagian, Jabatan, Suku, Alamat.</li>
                    </ul>
                </div>

                <div class="fw-700 mb-1" style="color:var(--hijau-tua)">Kolom Data Batih / Keluarga:</div>
                <div id="sideFamilyStatusInfo" class="mb-2">
                    <div class="text-muted">
                        Nama Anggota Keluarga, Hubungan Keluarga (Istri, Suami, Anak, dll), Jenis Kelamin, Tanggal Lahir Keluarga.
                    </div>
                </div>

                <div class="border-top pt-2 mt-2">
                    <div class="d-flex align-items-center gap-2 text-success fw-600 mb-1">
                        <i class="bi bi-palette-fill"></i> Tampilan Bersih &amp; Rapi
                    </div>
                    <div class="text-muted">
                        Header hijau resmi PTPN IV (#1E5631), teks putih tebal, border tabel rapi, serta lebar kolom otomatis menyesuaikan panjang data.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const counts = {
    'PKWT': {{ $countPkwt }},
    'Karpim & Karpel': {{ $countKarpim }},
    'KTNG': {{ $countKtng }}
};

function updateSelectionState() {
    const checks = document.querySelectorAll('.export-cat-check');
    const checkedValues = [];

    checks.forEach(cb => {
        const card = document.getElementById('card_' + (cb.value === 'PKWT' ? 'pkwt' : (cb.value === 'KTNG' ? 'ktng' : 'karpim')));
        if (card) {
            if (cb.checked) {
                card.classList.add('selected');
                checkedValues.push(cb.value);
            } else {
                card.classList.remove('selected');
            }
        }
    });

    // Family option state
    const famYes = document.getElementById('fam_yes');
    const cardFamYes = document.getElementById('card_fam_yes');
    const cardFamNo = document.getElementById('card_fam_no');
    const badgeFam = document.getElementById('badgeFamilySummary');
    const sideFamInfo = document.getElementById('sideFamilyStatusInfo');
    const isIncludeFamily = famYes && famYes.checked;

    if (isIncludeFamily) {
        cardFamYes.classList.add('selected');
        cardFamNo.classList.remove('selected');
        badgeFam.textContent = 'Mode Lengkap (+ Batih)';
        badgeFam.style.background = 'var(--hijau-light)';
        badgeFam.style.color = 'var(--hijau-tua)';
        if (sideFamInfo) {
            sideFamInfo.innerHTML = '<div class="text-muted">Nama Anggota Keluarga, Hubungan Keluarga (Istri, Suami, Anak, dll), Jenis Kelamin, Tanggal Lahir Keluarga.</div>';
        }
    } else {
        cardFamYes.classList.remove('selected');
        cardFamNo.classList.add('selected');
        badgeFam.textContent = 'Mode Ringkas (Tanpa Batih)';
        badgeFam.style.background = '#e9ecef';
        badgeFam.style.color = '#495057';
        if (sideFamInfo) {
            sideFamInfo.innerHTML = '<div class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-1 px-2 mb-1"><i class="bi bi-dash-circle me-1"></i>Dilewati: Kolom batih tidak dicantumkan</div><div class="text-muted small">Tepat 1 baris per karyawan tanpa pengulangan nama.</div>';
        }
    }

    // Update preview sheets
    const previewList = document.getElementById('sheetPreviewList');
    const badge = document.getElementById('sheetCountBadge');
    const warning = document.getElementById('noSheetWarning');
    const btn = document.getElementById('btnDownload');

    if (checkedValues.length === 0) {
        previewList.innerHTML = '<span class="text-muted small fst-italic">Tidak ada sheet yang dipilih.</span>';
        badge.textContent = '0 Sheet';
        badge.style.background = '#dc3545';
        warning.classList.remove('d-none');
        btn.disabled = true;
    } else {
        warning.classList.add('d-none');
        btn.disabled = false;
        badge.textContent = checkedValues.length + ' Sheet';
        badge.style.background = 'var(--hijau-tua)';

        let html = '';
        const modeLabel = isIncludeFamily ? '+ batih' : '1 baris/karyawan';
        checkedValues.forEach((val, idx) => {
            const count = counts[val] || 0;
            html += `<span class="sheet-preview-pill"><i class="bi bi-table"></i> Sheet ${idx + 1}: ${escapeHtml(val)} (${count} karyawan, ${modeLabel})</span>`;
        });
        previewList.innerHTML = html;
    }
}

function selectAllCategories(state) {
    document.querySelectorAll('.export-cat-check').forEach(cb => {
        cb.checked = state;
    });
    updateSelectionState();
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('exportForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.export-cat-check:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Silakan pilih minimal 1 status karyawan untuk diekspor.');
        return;
    }
});

// Jalankan update awal saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', updateSelectionState);
</script>
@endpush
