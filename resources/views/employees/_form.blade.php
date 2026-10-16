{{-- Partial: Form fields karyawan (digunakan di create & edit) --}}
<div class="row g-3">
    {{-- Identitas --}}
    <div class="col-12">
        <h6 class="fw-700 mb-2 pb-2 border-bottom" style="color:var(--hijau-tua)">
            <i class="bi bi-person-badge-fill me-2"></i>Identitas Karyawan
        </h6>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
               value="{{ old('nama', $employee->nama ?? '') }}" placeholder="Nama sesuai dokumen resmi" required>
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">NIK SAP (Personnel Number)</label>
        <input type="text" name="personnel_number" class="form-control @error('personnel_number') is-invalid @enderror"
               value="{{ old('personnel_number', $employee->personnel_number ?? '') }}" placeholder="Cth: 13007750">
        @error('personnel_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
               value="{{ old('tanggal_lahir', isset($employee->tanggal_lahir) ? $employee->tanggal_lahir->format('Y-m-d') : '') }}">
        @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
            <option value="">-- Pilih --</option>
            @foreach(['Laki-Laki','Perempuan','Tidak Diketahui'] as $jk)
            <option value="{{ $jk }}" {{ old('jenis_kelamin', $employee->jenis_kelamin ?? '') == $jk ? 'selected' : '' }}>{{ $jk }}</option>
            @endforeach
        </select>
        @error('jenis_kelamin') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Agama</label>
        <select name="agama" class="form-select">
            <option value="">-- Pilih --</option>
            @foreach(['Islam','Protestan','Old Catholic','Katolik','Hindu','Buddha','Kepercayaan'] as $a)
            <option value="{{ $a }}" {{ old('agama', $employee->agama ?? '') == $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Pendidikan Terakhir</label>
        <select name="pendidikan_terakhir" class="form-select">
            <option value="">-- Pilih --</option>
            @foreach(['SD','SLTP','SLTA','D1','D2','D3','D4','S1','S2','S3'] as $p)
            <option value="{{ $p }}" {{ old('pendidikan_terakhir', $employee->pendidikan_terakhir ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>

    {{-- Penempatan --}}
    <div class="col-12 mt-2">
        <h6 class="fw-700 mb-2 pb-2 border-bottom" style="color:var(--hijau-tua)">
            <i class="bi bi-building-fill me-2"></i>Penempatan & Jabatan
        </h6>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">Bagian</label>
        <select name="bagian_id" class="form-select">
            <option value="">-- Pilih Bagian --</option>
            @foreach($bagians as $bagian)
            <option value="{{ $bagian->id }}" {{ old('bagian_id', $employee->bagian_id ?? '') == $bagian->id ? 'selected' : '' }}>
                {{ $bagian->nama_bagian }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">Jabatan</label>
        <select name="jabatan_id" class="form-select">
            <option value="">-- Pilih Jabatan --</option>
            @foreach($jabatans as $jabatan)
            <option value="{{ $jabatan->id }}" {{ old('jabatan_id', $employee->jabatan_id ?? '') == $jabatan->id ? 'selected' : '' }}>
                {{ $jabatan->nama_jabatan }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Status Karyawan</label>
        @php
            $currentStatus = old('status_karyawan', $employee->status_karyawan ?? '');
            // Map nilai lama gabungan ke Karpel (fallback, jika ada data lama)
            if ($currentStatus === 'Karpim & Karpel') {
                $currentStatus = 'Karpel';
            }
        @endphp
        <select name="status_karyawan" id="status_karyawan_select" class="form-select" onchange="togglePkwtInput()">
            <option value="">-- Pilih Status --</option>
            @foreach($statuses as $s)
            <option value="{{ $s }}" {{ $currentStatus == $s ? 'selected' : '' }}>
                {{ $s }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Tanggal Mulai Kerja</label>
        <input type="date" name="tanggal_mulai_kerja" class="form-control"
               value="{{ old('tanggal_mulai_kerja', isset($employee->tanggal_mulai_kerja) ? $employee->tanggal_mulai_kerja->format('Y-m-d') : '') }}">
    </div>

    @php
        $initStatus = old('status_karyawan', $employee->status_karyawan ?? '');
        $isInitPkwt = str_contains(strtoupper((string)$initStatus), 'PKWT');
    @endphp
    <div class="col-md-4" id="pkwt_field_col" style="{{ $isInitPkwt ? '' : 'display:none;' }}">
        <label class="form-label fw-600" style="font-size:13px">No. PKWT</label>
        <input type="text" name="no_pkwt" id="no_pkwt_input" class="form-control"
               value="{{ old('no_pkwt', $employee->no_pkwt ?? '') }}" placeholder="Nomor kontrak PKWT">
    </div>

    {{-- Golongan & Grade --}}
    <div class="col-md-6" id="golongan_field_col" style="{{ $isInitPkwt ? 'display:none;' : '' }}">
        <label class="form-label fw-600" style="font-size:13px">Golongan</label>
        <input type="text" name="golongan" id="golongan_input" class="form-control"
               value="{{ old('golongan', $employee->golongan ?? '') }}" placeholder="IA/0, IB, dst.">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">Grade</label>
        <input type="text" name="grade" class="form-control"
               value="{{ old('grade', $employee->grade ?? '') }}" placeholder="Cth: 1, 2, G1, dst.">
    </div>

    {{-- Sumber data --}}
    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">Sumber Lembar Excel</label>
        <input type="text" name="sumber_lembar" class="form-control"
               value="{{ old('sumber_lembar', $employee->sumber_lembar ?? '') }}" placeholder="PELAI, Region Office, GUNME, dst.">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600" style="font-size:13px">Catatan</label>
        <textarea name="catatan" class="form-control" rows="2"
                  placeholder="Catatan tambahan (opsional)">{{ old('catatan', $employee->catatan ?? '') }}</textarea>
    </div>

    @if(!isset($employee))
    {{-- Input Keluarga Saat Tambah Karyawan Baru --}}
    <div class="col-12 mt-3 pt-3 border-top">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-700 mb-0" style="color:var(--hijau-tua)">
                <i class="bi bi-people-fill me-2"></i>Data Anggota Keluarga / Batih (Opsional)
            </h6>
            <button type="button" class="btn btn-sm btn-hijau-outline rounded-pill px-3" onclick="tambahBarisKeluarga()">
                <i class="bi bi-plus-lg me-1"></i> Tambah Baris Keluarga
            </button>
        </div>
        <p class="text-muted mb-2" style="font-size:12px">
            Anda dapat langsung menambahkan anggota keluarga sekarang atau nanti melalui menu profil karyawan.
        </p>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0" id="tabelKeluargaCreate">
                <thead class="table-light" style="font-size:12px">
                    <tr>
                        <th style="min-width:180px">Nama Anggota Keluarga</th>
                        <th style="min-width:140px">Hubungan</th>
                        <th style="min-width:130px">Jenis Kelamin</th>
                        <th style="min-width:140px">Tanggal Lahir</th>
                        <th style="width:50px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="wadahBarisKeluarga">
                    {{-- Baris ditambahkan dinamis melalui tombol di atas --}}
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
function togglePkwtInput() {
    var sel = document.getElementById('status_karyawan_select');
    var colPkwt = document.getElementById('pkwt_field_col');
    var colGol = document.getElementById('golongan_field_col');
    var inputPkwt = document.getElementById('no_pkwt_input');
    var inputGol = document.getElementById('golongan_input');
    if (!sel) return;
    var isPkwt = (sel.value || '').toUpperCase().indexOf('PKWT') !== -1;
    if (colPkwt) colPkwt.style.display = isPkwt ? '' : 'none';
    if (colGol) colGol.style.display = isPkwt ? 'none' : '';
    if (!isPkwt && inputPkwt) {
        inputPkwt.value = '';
    }
    if (isPkwt && inputGol) {
        inputGol.value = '';
    }
}
document.addEventListener('DOMContentLoaded', togglePkwtInput);

var indexFam = 0;
function tambahBarisKeluarga() {
    var tbody = document.getElementById('wadahBarisKeluarga');
    if (!tbody) return;
    var tr = document.createElement('tr');
    tr.id = 'fam_row_' + indexFam;
    tr.innerHTML = `
        <td><input type="text" name="families[${indexFam}][nama]" class="form-control form-control-sm" placeholder="Nama lengkap" required></td>
        <td>
            <select name="families[${indexFam}][hubungan]" class="form-select form-select-sm" required>
                <option value="Istri">Istri</option>
                <option value="Suami">Suami</option>
                <option value="Anak" selected>Anak</option>
                <option value="Orang Tua">Orang Tua</option>
                <option value="Mertua">Mertua</option>
                <option value="Famili Lain">Famili Lain</option>
            </select>
        </td>
        <td>
            <select name="families[${indexFam}][jenis_kelamin]" class="form-select form-select-sm" required>
                <option value="Laki-Laki">Laki-Laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </td>
        <td><input type="date" name="families[${indexFam}][tanggal_lahir]" class="form-control form-control-sm"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle" onclick="document.getElementById('fam_row_${indexFam}').remove()">
                <i class="bi bi-trash3"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    indexFam++;
}
</script>
