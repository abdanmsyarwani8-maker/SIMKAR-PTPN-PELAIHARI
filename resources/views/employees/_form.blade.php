{{-- Partial: Form fields karyawan (digunakan di create & edit) --}}
<div class="row g-3">
    {{-- Identitas --}}
    <div class="col-12">
        <h6 class="fw-700 mb-2 pb-2 border-bottom" style="color:var(--hijau-tua)">
            <i class="bi bi-person-badge me-2"></i>Identitas Karyawan
        </h6>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
               value="{{ old('nama', $employee->nama ?? '') }}" placeholder="Nama sesuai dokumen resmi" required>
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">NIK SAP (Personnel Number)</label>
        <input type="text" name="personnel_number" class="form-control @error('personnel_number') is-invalid @enderror"
               value="{{ old('personnel_number', $employee->personnel_number ?? '') }}" placeholder="Cth: 13007750">
        @error('personnel_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">NIK Lokal</label>
        <input type="text" name="nik_lokal" class="form-control"
               value="{{ old('nik_lokal', $employee->nik_lokal ?? '') }}" placeholder="NIK sistem internal">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Pers. No.</label>
        <input type="text" name="pers_no" class="form-control"
               value="{{ old('pers_no', $employee->pers_no ?? '') }}" placeholder="Nomor urut SAP">
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
            <i class="bi bi-building me-2"></i>Penempatan & Jabatan
        </h6>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Unit / Kebun</label>
        <select name="unit_id" class="form-select">
            <option value="">-- Pilih Unit --</option>
            @foreach($units as $unit)
            <option value="{{ $unit->id }}" {{ old('unit_id', $employee->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                {{ $unit->nama_unit }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
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

    <div class="col-md-4">
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
        <select name="status_karyawan" class="form-select">
            <option value="">-- Pilih Status --</option>
            @foreach($statuses as $s)
            <option value="{{ $s }}" {{ old('status_karyawan', $employee->status_karyawan ?? '') == $s ? 'selected' : '' }}>
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

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">No. PKWT</label>
        <input type="text" name="no_pkwt" class="form-control"
               value="{{ old('no_pkwt', $employee->no_pkwt ?? '') }}" placeholder="Nomor kontrak PKWT">
    </div>

    {{-- Golongan --}}
    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Golongan</label>
        <input type="text" name="golongan" class="form-control"
               value="{{ old('golongan', $employee->golongan ?? '') }}" placeholder="IA/0, IB, dst.">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Grup Golongan</label>
        <input type="text" name="grup_golongan" class="form-control"
               value="{{ old('grup_golongan', $employee->grup_golongan ?? '') }}" placeholder="IA, IA(NG), MBT">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-600" style="font-size:13px">Grup Jabatan</label>
        <input type="text" name="grup_jabatan" class="form-control"
               value="{{ old('grup_jabatan', $employee->grup_jabatan ?? '') }}" placeholder="Petugas, Mandor, Operator">
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
</div>
