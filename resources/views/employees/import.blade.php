@extends('layouts.app')
@section('title', 'Import Data Karyawan')
@section('page-title', 'Import Data Karyawan')

@push('styles')
<style>
.upload-zone {
    border: 2px dashed #c8dfd0;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    transition: all .2s;
    background: var(--hijau-pale);
    cursor: pointer;
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: var(--hijau-med);
    background: var(--hijau-light);
}
.upload-zone .upload-icon { font-size: 48px; color: #9cbfa8; margin-bottom: 12px; }
.sheet-item {
    border: 1.5px solid #e0ede5;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: border-color .15s;
}
.sheet-item:hover { border-color: var(--hijau-med); }
.sheet-item input[type=checkbox]:checked + label { font-weight: 600; color: var(--hijau-tua); }
.sheet-item .sheet-rows { color: #888; font-size: 12px; }
.progress-bar { background: var(--hijau-med); }
</style>
@endpush

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Data Karyawan
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-file-earmark-arrow-up-fill me-1"></i>
                    Import Data Karyawan dari Excel
                </div>
                <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua)">Format .xlsx / .xls</span>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('employees.import') }}"
                      enctype="multipart/form-data" id="importForm">
                    @csrf

                    {{-- Upload Zone --}}
                    <div class="upload-zone mb-4" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <h6 class="fw-700" style="color:var(--hijau-tua)">Klik atau seret file Excel ke sini</h6>
                        <p class="text-muted mb-3" style="font-size:13px">
                            Format: .xlsx atau .xls &nbsp;·&nbsp; Maksimal 50MB
                        </p>
                        <div id="fileInfo" class="d-none">
                            <div class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:13px;padding:8px 16px">
                                <i class="bi bi-file-earmark-excel me-2"></i>
                                <span id="fileName"></span>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="fileInput" name="file" class="d-none" accept=".xlsx,.xls">

                    {{-- Loading indicator --}}
                    <div id="loadingSheets" class="d-none text-center py-4">
                        <div class="spinner-border text-success mb-2"></div>
                        <div style="font-size:13px;color:#6b7c70">Membaca lembar Excel...</div>
                    </div>

                    {{-- Sheet selector --}}
                    <div id="sheetSelector" class="d-none">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-700 mb-0" style="color:var(--hijau-tua)">
                                <i class="bi bi-layers me-1"></i>
                                Pilih Lembar yang Akan Diimport
                            </h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-light rounded-pill"
                                        onclick="selectAllSheets(true)">Pilih Semua</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill"
                                        onclick="selectAllSheets(false)">Hapus Semua</button>
                            </div>
                        </div>

                        <div id="sheetList" style="max-height:400px;overflow-y:auto;padding-right:4px"></div>

                        <div class="mt-4 p-3 rounded-3" style="background:#fff3cd;border:1px solid #ffc107">
                            <div style="font-size:12.5px;color:#856404">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Catatan:</strong> Data yang sudah ada di database akan
                                <strong>diperbarui</strong> jika NIK SAP sama. Data baru akan
                                <strong>ditambahkan</strong>.
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-hijau rounded-pill px-4" id="importBtn">
                                <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Mulai Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Panduan --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle-fill me-2"></i>Panduan Import</div>
            <div class="card-body" style="font-size:13px">
                <ol class="ps-3" style="line-height:1.9">
                    <li>Klik area upload atau seret file .xlsx ke dalamnya</li>
                    <li>Tunggu sistem membaca daftar lembar</li>
                    <li>Centang lembar yang ingin diimport</li>
                    <li>Klik tombol <strong>Mulai Import</strong></li>
                </ol>

                <div class="border-top pt-3 mt-2">
                    <div class="fw-700 mb-2" style="color:var(--hijau-tua)">Format Kolom yang Didukung:</div>
                    <div class="fw-600 text-muted mb-1" style="font-size:12px">Data Utama Karyawan:</div>
                    <ul class="ps-3 mb-2" style="line-height:1.7">
                        <li><strong>NIK SAP</strong> / Personnel Number (Identitas Utama)</li>
                        <li>Nama Karyawan / Employee Name</li>
                        <li>Kategori: PKWT, KTNG, Karpim &amp; Karpel</li>
                        <li>No. PKWT / Kontrak</li>
                        <li>TMT Kerja / Tanggal Mulai Kerja</li>
                        <li>Tanggal Lahir Karyawan</li>
                        <li>Jenis Kelamin, Agama, Pendidikan</li>
                        <li>Grade, Suku, Alamat</li>
                        <li>Bagian, Jabatan</li>
                    </ul>

                    <div class="fw-600 text-muted mb-1" style="font-size:12px">Data Batih / Keluarga:</div>
                    <ul class="ps-3 mb-0" style="line-height:1.7">
                        <li>Nama Anggota Keluarga / Batih</li>
                        <li>Hubungan (Istri, Suami, Anak, dll.)</li>
                        <li>Jenis Kelamin Anggota Keluarga</li>
                        <li>Tanggal Lahir Anggota Keluarga</li>
                    </ul>
                </div>

                <div class="border-top pt-3 mt-2">
                    <div class="fw-700 mb-1" style="color:var(--hijau-tua)">Contoh File yang Didukung:</div>
                    <div class="small">
                        <i class="bi bi-file-earmark-excel text-success me-1"></i>
                        SIMKAR_Import_Satu_Sheet_Karyawan_Batih.xlsx
                        <span class="badge bg-success-subtle text-success ms-1">1 lembar (Karyawan + Batih)</span>
                    </div>
                    <div class="small mt-1">
                        <i class="bi bi-file-earmark-excel text-success me-1"></i>
                        BATIH.xlsx / REVISI EXPORT BATIH.xlsx
                        <span class="badge bg-light text-dark ms-1">Format SAP HCM</span>
                    </div>
                    <div class="small mt-1">
                        <i class="bi bi-file-earmark-excel text-success me-1"></i>
                        Data Karyawan (PKWT, KTNG, Karpim &amp; Karpel)
                        <span class="badge bg-light text-dark ms-1">Multi-sheet</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const uploadZone  = document.getElementById('uploadZone');
const fileInput   = document.getElementById('fileInput');
const loadingEl   = document.getElementById('loadingSheets');
const selectorEl  = document.getElementById('sheetSelector');
const sheetList   = document.getElementById('sheetList');
const fileInfo    = document.getElementById('fileInfo');
const fileName    = document.getElementById('fileName');

// Drag & drop
uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    if (e.dataTransfer.files[0]) {
        fileInput.files = e.dataTransfer.files;
        handleFile(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) handleFile(fileInput.files[0]);
});

function handleFile(file) {
    fileName.textContent = file.name;
    fileInfo.classList.remove('d-none');
    selectorEl.classList.add('d-none');
    loadingEl.classList.remove('d-none');

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route("employees.import.sheets") }}', {
        method: 'POST', body: formData
    })
    .then(r => r.json())
    .then(data => {
        loadingEl.classList.add('d-none');
        if (data.error) { alert('Error: ' + data.error); return; }
        renderSheets(data.sheets);
        selectorEl.classList.remove('d-none');
    })
    .catch(err => {
        loadingEl.classList.add('d-none');
        alert('Gagal membaca file. Pastikan format file benar.');
    });
}

function renderSheets(sheets) {
    sheetList.innerHTML = '';
    sheets.forEach(sheet => {
        sheetList.innerHTML += `
        <div class="sheet-item">
            <input type="checkbox" name="sheet_names[]" value="${escapeHtml(sheet.name)}"
                   id="sheet${sheet.index}" class="form-check-input" checked
                   style="width:18px;height:18px;flex-shrink:0">
            <label for="sheet${sheet.index}" style="cursor:pointer;flex:1">
                <span>${escapeHtml(sheet.name)}</span>
                <span class="sheet-rows ms-2">(~${sheet.rows.toLocaleString()} baris)</span>
            </label>
        </div>`;
    });
}

function selectAllSheets(checked) {
    document.querySelectorAll('#sheetList input[type=checkbox]').forEach(cb => cb.checked = checked);
}

function escapeHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Show loading on form submit
document.getElementById('importForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('#sheetList input:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu lembar untuk diimport.');
        return;
    }
    document.getElementById('importBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengimport...';
    document.getElementById('importBtn').disabled = true;
});
</script>
@endpush
