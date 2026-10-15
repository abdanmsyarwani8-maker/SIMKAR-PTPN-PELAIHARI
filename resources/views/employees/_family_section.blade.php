{{-- Partial: Data Batih / Keluarga Karyawan dengan Aksi Tambah, Edit, & Hapus --}}
<div class="card mt-3">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-people-fill me-2"></i>
            <span class="fw-700" style="color:var(--hijau-tua)">Data Keluarga / Batih</span>
            <span class="badge ms-2" style="background:var(--hijau-pale);color:var(--hijau-tua);font-size:12px">
                {{ $employee->families->count() }} Anggota Keluarga
            </span>
        </div>
        @auth @if(auth()->user()->isAdmin())
        <button type="button" class="btn btn-sm btn-hijau rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahKeluarga">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Anggota Keluarga
        </button>
        @endif @endauth
    </div>
    <div class="card-body p-0">
        @if($employee->families->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-simkar mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Anggota</th>
                        <th>Hubungan</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Umur</th>
                        @auth @if(auth()->user()->isAdmin())
                        <th style="width:110px">Aksi</th>
                        @endif @endauth
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->families as $index => $fam)
                    <tr>
                        <td class="text-muted" style="font-size:12px">{{ $index + 1 }}</td>
                        <td class="fw-600">{{ $fam->nama }}</td>
                        <td>
                            @php
                                $badgeStyle = match($fam->hubungan) {
                                    'Istri', 'Suami' => 'background:#e8f0fe;color:#1565c0',
                                    'Anak' => 'background:#e8f5e9;color:#2e7d32',
                                    default => 'background:#fff3e0;color:#e65100',
                                };
                            @endphp
                            <span class="badge" style="{{ $badgeStyle }};font-size:12px">
                                {{ $fam->hubungan ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <i class="bi {{ $fam->jenis_kelamin == 'Laki-Laki' ? 'bi-gender-male text-primary' : 'bi-gender-female text-danger' }} me-1"></i>
                            {{ $fam->jenis_kelamin }}
                        </td>
                        <td>{{ $fam->tanggal_lahir?->format('d M Y') ?? '-' }}</td>
                        <td>{{ $fam->umur !== null ? $fam->umur . ' tahun' : '-' }}</td>
                        @auth @if(auth()->user()->isAdmin())
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button"
                                        class="btn-action btn-edit-keluarga"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditKeluarga"
                                        data-id="{{ $fam->id }}"
                                        data-nama="{{ $fam->nama }}"
                                        data-hubungan="{{ $fam->hubungan }}"
                                        data-jenis_kelamin="{{ $fam->jenis_kelamin }}"
                                        data-tanggal_lahir="{{ $fam->tanggal_lahir?->format('Y-m-d') ?? '' }}"
                                        data-keterangan="{{ $fam->keterangan ?? '' }}"
                                        data-action="{{ route('employees.families.update', [$employee, $fam]) }}"
                                        title="Edit Anggota Keluarga">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form method="POST" action="{{ route('employees.families.destroy', [$employee, $fam]) }}" class="d-inline m-0 p-0"
                                      onsubmit="return confirm('Hapus {{ addslashes($fam->nama) }} dari daftar keluarga?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-danger" title="Hapus Anggota Keluarga">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif @endauth
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-4 text-center text-muted" style="font-size:13.5px">
            <i class="bi bi-people-fill mb-2 d-block" style="font-size:32px;opacity:0.4"></i>
            Belum ada data keluarga/batih yang tercatat untuk karyawan ini.
            @auth @if(auth()->user()->isAdmin())
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-hijau rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahKeluarga">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Anggota Sekarang
                </button>
            </div>
            @endif @endauth
        </div>
        @endif
    </div>
</div>

@auth @if(auth()->user()->isAdmin())
{{-- Modal Tambah Keluarga --}}
<div class="modal fade" id="modalTambahKeluarga" tabindex="-1" aria-labelledby="modalTambahKeluargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 10px 30px rgba(0,0,0,0.15)">
            <div class="modal-header" style="background:var(--hijau-pale);border-bottom:1px solid #d4e8da">
                <h6 class="modal-title fw-700" id="modalTambahKeluargaLabel" style="color:var(--hijau-tua)">
                    <i class="bi bi-person-plus-fill me-2"></i>Tambah Anggota Keluarga
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('employees.families.store', $employee) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-600" style="font-size:13px">Nama Lengkap Anggota <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama sesuai KTP/Akta" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Hubungan Keluarga <span class="text-danger">*</span></label>
                            <select name="hubungan" class="form-select" required>
                                <option value="">-- Pilih Hubungan --</option>
                                @foreach(['Istri', 'Suami', 'Anak', 'Orang Tua', 'Mertua', 'Famili Lain'] as $hub)
                                <option value="{{ $hub }}">{{ $hub }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-Laki">Laki-Laki</option>
                                <option value="Perempuan">Perempuan</option>
                                <option value="Tidak Diketahui">Tidak Diketahui</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Keterangan tambahan">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e8ede9">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-hijau rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Keluarga --}}
<div class="modal fade" id="modalEditKeluarga" tabindex="-1" aria-labelledby="modalEditKeluargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;box-shadow:0 10px 30px rgba(0,0,0,0.15)">
            <div class="modal-header" style="background:var(--hijau-pale);border-bottom:1px solid #d4e8da">
                <h6 class="modal-title fw-700" id="modalEditKeluargaLabel" style="color:var(--hijau-tua)">
                    <i class="bi bi-pencil-square me-2"></i>Edit Data Anggota Keluarga
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="formEditKeluarga" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-600" style="font-size:13px">Nama Lengkap Anggota <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="edit_fam_nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Hubungan Keluarga <span class="text-danger">*</span></label>
                            <select name="hubungan" id="edit_fam_hubungan" class="form-select" required>
                                <option value="">-- Pilih Hubungan --</option>
                                @foreach(['Istri', 'Suami', 'Anak', 'Orang Tua', 'Mertua', 'Famili Lain'] as $hub)
                                <option value="{{ $hub }}">{{ $hub }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" id="edit_fam_jenis_kelamin" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-Laki">Laki-Laki</option>
                                <option value="Perempuan">Perempuan</option>
                                <option value="Tidak Diketahui">Tidak Diketahui</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="edit_fam_tanggal_lahir" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" style="font-size:13px">Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" id="edit_fam_keterangan" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #e8ede9">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-hijau rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var editButtons = document.querySelectorAll('.btn-edit-keluarga');
    var formEdit = document.getElementById('formEditKeluarga');
    var inputNama = document.getElementById('edit_fam_nama');
    var selectHubungan = document.getElementById('edit_fam_hubungan');
    var selectJk = document.getElementById('edit_fam_jenis_kelamin');
    var inputTglLahir = document.getElementById('edit_fam_tanggal_lahir');
    var inputKeterangan = document.getElementById('edit_fam_keterangan');

    editButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            formEdit.action = this.dataset.action;
            inputNama.value = this.dataset.nama || '';
            selectHubungan.value = this.dataset.hubungan || '';
            selectJk.value = this.dataset.jenis_kelamin || '';
            inputTglLahir.value = this.dataset.tanggal_lahir || '';
            inputKeterangan.value = this.dataset.keterangan || '';
        });
    });
});
</script>
@endif @endauth
