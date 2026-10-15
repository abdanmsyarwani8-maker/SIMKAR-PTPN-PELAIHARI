@extends('layouts.app')
@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

@push('styles')
<style>
.filter-toggle-btn { font-size: 13px; }
.active-filter-chip {
    display:inline-flex; align-items:center; gap:5px;
    background:var(--hijau-light); color:var(--hijau-tua);
    border:1px solid #c8dfd0; border-radius:20px;
    padding:3px 10px; font-size:12px; font-weight:500;
}
.sort-link { color: inherit; text-decoration: none; }
.sort-link:hover { color: var(--hijau-tua); }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-check-circle-fill fs-5"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

{{-- Header Bar --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <div id="totalCounterHeader" style="font-size:13px;color:#55695c;font-weight:500;">
            Total ditemukan: <strong class="text-dark">{{ number_format($employees->total()) }}</strong> karyawan
            @if($activeFilters > 0)
            <span class="badge ms-1" style="background:var(--hijau-tua);font-size:11px">
                {{ $activeFilters }} filter aktif
            </span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        @auth @if(auth()->user()->isAdmin())
        <a href="{{ route('employees.create') }}" class="btn btn-hijau btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-person-plus-fill"></i> Tambah Karyawan
        </a>

        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#modalHapusSemuaData">
            <i class="bi bi-trash3"></i> Hapus Semua Data
        </button>
        @endif @endauth

        <button class="btn btn-hijau btn-sm rounded-pill px-3 filter-toggle-btn shadow-sm d-inline-flex align-items-center gap-1"
                type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" id="filterToggleBtn">
            <i class="bi bi-funnel-fill"></i> Filter
            @if($activeFilters > 0)
            <span class="badge bg-white text-success rounded-pill ms-1" id="filterBadgeSpan" style="font-size:10px; font-weight:700;">{{ $activeFilters }}</span>
            @endif
        </button>
    </div>
</div>

<div class="row g-3">
    {{-- Filter Panel (collapsible) --}}
    <div class="col-lg-3 collapse {{ $activeFilters > 0 ? 'show' : '' }}" id="filterPanel">
        <form method="GET" action="{{ route('employees.index') }}" id="filterForm">
            {{-- Pertahankan sort & search --}}
            @if(request('q'))   <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
            @if(request('dir'))  <input type="hidden" name="dir" value="{{ request('dir') }}"> @endif

            <div class="filter-panel mb-3">
                {{-- Header Filter --}}
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <span class="fw-700" style="font-size:13px;color:var(--hijau-tua)">
                        <i class="bi bi-funnel-fill me-1"></i> Panel Filter
                    </span>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light rounded-pill"
                       style="font-size:11px" onclick="resetFiltersAjax(event)">Reset</a>
                </div>


                {{-- Filter Bagian --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        Bagian
                        @if(request('bagian_ids')) <span class="filter-badge">{{ count(request('bagian_ids')) }}</span> @endif
                    </div>
                    <input type="text" class="filter-search" placeholder="Cari bagian..." oninput="filterList(this,'bagianList')">
                    <div class="filter-list" id="bagianList">
                        @foreach($bagians as $bagian)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="bagian_ids[]"
                                   value="{{ $bagian->id }}" id="bagian{{ $bagian->id }}"
                                   {{ in_array($bagian->id, request('bagian_ids',[]))?'checked':'' }}>
                            <label class="form-check-label" for="bagian{{ $bagian->id }}">
                                {{ $bagian->nama_bagian }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Jabatan --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        Jabatan
                        @if(request('jabatan_ids')) <span class="filter-badge">{{ count(request('jabatan_ids')) }}</span> @endif
                    </div>
                    <input type="text" class="filter-search" placeholder="Cari jabatan..." oninput="filterList(this,'jabatanList')">
                    <div class="filter-list" id="jabatanList">
                        @foreach($jabatans as $jabatan)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="jabatan_ids[]"
                                   value="{{ $jabatan->id }}" id="jabatan{{ $jabatan->id }}"
                                   {{ in_array($jabatan->id, request('jabatan_ids',[]))?'checked':'' }}>
                            <label class="form-check-label" for="jabatan{{ $jabatan->id }}">
                                {{ $jabatan->nama_jabatan }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Status Karyawan --}}
                <div class="filter-group">
                    <div class="filter-group-title">Status Karyawan</div>
                    <div class="filter-list">
                        @foreach($statuses as $status)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="statuses[]"
                                   value="{{ $status }}" id="status{{ Str::slug($status) }}"
                                   {{ in_array($status, request('statuses',[]))?'checked':'' }}>
                            <label class="form-check-label" for="status{{ Str::slug($status) }}">
                                {{ $status }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Jenis Kelamin --}}
                <div class="filter-group">
                    <div class="filter-group-title">Jenis Kelamin</div>
                    <div class="filter-list">
                        @foreach($jenisKelaminList as $jk)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="jenis_kelamin[]"
                                   value="{{ $jk }}" id="jk{{ Str::slug($jk) }}"
                                   {{ in_array($jk, request('jenis_kelamin',[]))?'checked':'' }}>
                            <label class="form-check-label" for="jk{{ Str::slug($jk) }}">{{ $jk }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Agama --}}
                <div class="filter-group">
                    <div class="filter-group-title">Agama</div>
                    <div class="filter-list">
                        @foreach($agamaList as $agama)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="agama[]"
                                   value="{{ $agama }}" id="agama{{ Str::slug($agama) }}"
                                   {{ in_array($agama, request('agama',[]))?'checked':'' }}>
                            <label class="form-check-label" for="agama{{ Str::slug($agama) }}">{{ $agama }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Rentang Usia --}}
                <div class="filter-group">
                    <div class="filter-group-title">Rentang Usia</div>
                    <div class="filter-list">
                        @foreach($rentangUsiaList as $range)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="rentang_usia[]"
                                   value="{{ $range }}" id="usia{{ Str::slug($range) }}"
                                   {{ in_array($range, request('rentang_usia',[]))?'checked':'' }}>
                            <label class="form-check-label" for="usia{{ Str::slug($range) }}">{{ $range }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Sumber Lembar --}}
                @if($sumberList->isNotEmpty())
                <div class="filter-group">
                    <div class="filter-group-title">Sumber Data (Lembar)</div>
                    <input type="text" class="filter-search" placeholder="Cari sumber..." oninput="filterList(this,'sumberList')">
                    <div class="filter-list" id="sumberList">
                        @foreach($sumberList as $sumber)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sumber_lembar[]"
                                   value="{{ $sumber }}" id="sumber{{ Str::slug($sumber) }}"
                                   {{ in_array($sumber, request('sumber_lembar',[]))?'checked':'' }}>
                            <label class="form-check-label" for="sumber{{ Str::slug($sumber) }}">{{ $sumber }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Tombol Selesai --}}
                <div class="p-3">
                    <button type="button" class="btn btn-hijau w-100 rounded-pill shadow-sm" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                        <i class="bi bi-check-lg me-1"></i> Selesai Memilih
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Utama --}}
    <div class="{{ $activeFilters > 0 ? 'col-lg-9' : 'col-12' }}" id="mainContentCol">

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('employees.index') }}" class="mb-3" onsubmit="event.preventDefault(); applyRealtimeFilter();">
            @foreach(request()->except(['q','page']) as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" name="q" id="topSearchInput" class="form-control border-start-0 ps-0"
                       placeholder="Cari nama, NIK SAP, atau nomor karyawan..."
                       value="{{ request('q') }}" style="font-size:13.5px">
                <button type="button" class="btn btn-hijau px-3" onclick="applyRealtimeFilter()">Cari</button>
            </div>
        </form>

        {{-- Active Filter Chips --}}
        <div id="activeFilterChipsContainer">
            @if($activeFilters > 0)
            <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                <span style="font-size:12px;color:#6b7c70">Filter aktif:</span>
                @if(request('q'))
                <span class="active-filter-chip">
                    Pencarian: "{{ request('q') }}"
                    <a href="{{ request()->fullUrlWithoutQuery(['q']) }}" style="color:inherit" onclick="removeFilterParam(event, 'q')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('bagian_ids'))
                <span class="active-filter-chip">
                    Bagian ({{ count(request('bagian_ids')) }})
                    <a href="{{ request()->fullUrlWithoutQuery(['bagian_ids']) }}" style="color:inherit" onclick="removeFilterParam(event, 'bagian_ids')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('jabatan_ids'))
                <span class="active-filter-chip">
                    Jabatan ({{ count(request('jabatan_ids')) }})
                    <a href="{{ request()->fullUrlWithoutQuery(['jabatan_ids']) }}" style="color:inherit" onclick="removeFilterParam(event, 'jabatan_ids')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('statuses'))
                <span class="active-filter-chip">
                    Status ({{ count(request('statuses')) }})
                    <a href="{{ request()->fullUrlWithoutQuery(['statuses']) }}" style="color:inherit" onclick="removeFilterParam(event, 'statuses')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('jenis_kelamin'))
                <span class="active-filter-chip">
                    JK: {{ implode(', ', request('jenis_kelamin')) }}
                    <a href="{{ request()->fullUrlWithoutQuery(['jenis_kelamin']) }}" style="color:inherit" onclick="removeFilterParam(event, 'jenis_kelamin')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('agama'))
                <span class="active-filter-chip">
                    Agama ({{ count(request('agama')) }})
                    <a href="{{ request()->fullUrlWithoutQuery(['agama']) }}" style="color:inherit" onclick="removeFilterParam(event, 'agama')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('rentang_usia'))
                <span class="active-filter-chip">
                    Usia ({{ count(request('rentang_usia')) }})
                    <a href="{{ request()->fullUrlWithoutQuery(['rentang_usia']) }}" style="color:inherit" onclick="removeFilterParam(event, 'rentang_usia')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                @if(request('sumber_lembar'))
                <span class="active-filter-chip">
                    Sumber ({{ count(request('sumber_lembar')) }})
                    <a href="{{ request()->fullUrlWithoutQuery(['sumber_lembar']) }}" style="color:inherit" onclick="removeFilterParam(event, 'sumber_lembar')"><i class="bi bi-x-circle"></i></a>
                </span>
                @endif
                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-danger rounded-pill" style="font-size:11px" onclick="resetFiltersAjax(event)">
                    <i class="bi bi-x-circle me-1"></i>Reset Semua
                </a>
            </div>
            @endif
        </div>

        {{-- Tabel Container --}}
        <div id="tableContainer">
            <div class="card">
            @if($employees->isEmpty())
            <div class="empty-state">
                <i class="bi bi-people-fill"></i>
                <h5>Tidak ada data karyawan</h5>
                <p style="font-size:13.5px">
                    @if($activeFilters > 0)
                        Tidak ada karyawan yang sesuai dengan filter yang dipilih.
                        <a href="{{ route('employees.index') }}">Reset filter</a>
                    @else
                        Belum ada data karyawan yang dimasukkan.
                        @auth @if(auth()->user()->isAdmin())
                        <a href="{{ route('employees.create') }}">Tambah karyawan pertama</a>
                        @endif @endauth
                    @endif
                </p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-simkar mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <a class="sort-link" href="{{ request()->fullUrlWithQuery(['sort'=>'nama','dir'=>request('sort')=='nama'&&request('dir')=='asc'?'desc':'asc']) }}">
                                    Nama <i class="bi bi-arrow-down-up ms-1 opacity-50"></i>
                                </a>
                            </th>
                            <th>NIK SAP</th>
                            <th>Bagian</th>
                            <th>Jabatan</th>
                            <th>J.K</th>
                            <th>Status</th>
                            <th>Pensiun (55 Thn)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $k)
                        @php
                            $badgeClass = match(true) {
                                str_contains($k->status_karyawan??'', 'Karpim') => 'badge-karpim',
                                str_contains($k->status_karyawan??'', 'Karpel') => 'badge-karpel',
                                str_contains($k->status_karyawan??'', 'PKWT')   => 'badge-pkwt',
                                str_contains($k->status_karyawan??'', 'KTNG')   => 'badge-ktng',
                                str_contains($k->status_karyawan??'', 'Papam')  => 'badge-papam',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <tr>
                            <td class="text-muted" style="font-size:12px">{{ $employees->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('employees.show', $k) }}"
                                   class="fw-600 text-decoration-none" style="color:var(--hijau-tua)">
                                    {{ $k->nama }}
                                </a>
                            </td>
                            <td style="font-size:12.5px;color:#2c3e50">{{ $k->personnel_number ?? '-' }}</td>
                            <td style="font-size:12.5px">{{ $k->bagian?->nama_bagian ?? '-' }}</td>
                            <td style="font-size:12.5px">{{ $k->jabatan?->nama_jabatan ?? '-' }}</td>
                            <td>
                                <i class="bi {{ $k->jenis_kelamin=='Laki-Laki'?'bi-gender-male text-primary':'bi-gender-female text-danger' }}"
                                   title="{{ $k->jenis_kelamin }}"></i>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}" style="font-size:10.5px">
                                    {{ $k->status_karyawan ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($k->tanggal_pensiun)
                                    <div style="font-size:12px;font-weight:600;white-space:nowrap">{{ $k->tanggal_pensiun->format('d M Y') }}</div>
                                    <div style="font-size:10.5px;font-weight:500;white-space:nowrap;color:{{ $k->sisa_waktu_pensiun === 'Sudah Pensiun' ? '#dc3545' : '#198754' }}">
                                        {{ $k->sisa_waktu_pensiun }}
                                    </div>
                                @else
                                    <span class="text-muted" style="font-size:12px">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('employees.show', $k) }}"
                                       class="btn-action" title="Detail Karyawan">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @auth @if(auth()->user()->isAdmin())
                                    <a href="{{ route('employees.edit', $k) }}"
                                       class="btn-action" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $k) }}" class="d-inline m-0 p-0"
                                          onsubmit="return confirm('Hapus data {{ addslashes($k->nama) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-danger" title="Hapus Data">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                    @endif @endauth
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 py-3 border-top">
                <div style="font-size:12.5px;color:#6b7c70">
                    Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }}
                    dari {{ number_format($employees->total()) }} karyawan
                </div>
                <div class="ms-auto">
                    {{ $employees->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
        </div> {{-- close #tableContainer --}}
    </div> {{-- close #mainContentCol --}}
</div> {{-- close .row --}}

{{-- Modal Hapus Semua Data Karyawan --}}
@auth @if(auth()->user()->isAdmin())
<div class="modal fade" id="modalHapusSemuaData" tabindex="-1" aria-labelledby="modalHapusSemuaDataLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:#c0392b;">
                <h6 class="modal-title fw-700 mb-0 d-flex align-items-center gap-2" id="modalHapusSemuaDataLabel">
                    <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Hapus Semua Data
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('employees.destroy-all') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-trash3-fill text-danger" style="font-size:48px;"></i>
                    </div>
                    <h5 class="fw-700 text-danger mb-2">Hapus Seluruh Data Karyawan?</h5>
                    <p class="text-muted small mb-0" style="line-height:1.6">
                        Tindakan ini akan <strong>menghapus seluruh data karyawan beserta data anggota keluarga/batih</strong> secara permanen dari sistem. Anda dapat menggunakan fitur ini saat ingin mengosongkan data lama untuk import ulang file baru.
                    </p>
                    <div class="alert alert-warning text-start mt-3 mb-0 py-2 px-3 small">
                        <i class="bi bi-info-circle me-1"></i> Data referensi master (Bagian dan Jabatan) tidak akan terhapus.
                    </div>
                </div>
                <div class="modal-footer justify-content-center bg-light">
                    <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        <i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Semua Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif @endauth
@endsection

@push('scripts')
<script>
// Filter pencarian dalam daftar checkbox
function filterList(input, listId) {
    const q = input.value.toLowerCase();
    document.querySelectorAll('#' + listId + ' .form-check').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// Inisialisasi event listener setelah DOM siap
document.addEventListener('DOMContentLoaded', function () {
    const filterPanel = document.getElementById('filterPanel');
    const mainContentCol = document.getElementById('mainContentCol');

    // Mencegah ruang kosong: Saat filter dibuka, kolom tabel langsung jadi col-lg-9 berdampingan
    if (filterPanel && mainContentCol) {
        filterPanel.addEventListener('show.bs.collapse', function () {
            mainContentCol.classList.remove('col-12');
            mainContentCol.classList.add('col-lg-9');
        });

        // Saat filter ditutup, kolom tabel melebar penuh col-12
        filterPanel.addEventListener('hide.bs.collapse', function () {
            mainContentCol.classList.remove('col-lg-9');
            mainContentCol.classList.add('col-12');
        });
    }

    // Pasang listener realtime pada seluruh checkbox filter
    attachCheckboxListeners();

    // Pasang listener pencarian realtime dengan debounce
    const topSearch = document.getElementById('topSearchInput');
    if (topSearch) {
        let searchTimer = null;
        topSearch.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(applyRealtimeFilter, 300);
        });
    }
});

function attachCheckboxListeners() {
    const filterCheckboxes = document.querySelectorAll('#filterForm input[type="checkbox"]');
    filterCheckboxes.forEach(cb => {
        cb.removeEventListener('change', applyRealtimeFilter);
        cb.addEventListener('change', applyRealtimeFilter);
    });
}

// Fungsi eksekusi filter secara realtime via AJAX
function applyRealtimeFilter() {
    const form = document.getElementById('filterForm');
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, val] of formData.entries()) {
        params.append(key, val);
    }

    const topSearch = document.getElementById('topSearchInput');
    if (topSearch && topSearch.value.trim() !== '') {
        params.set('q', topSearch.value.trim());
    }

    const currentUrlParams = new URLSearchParams(window.location.search);
    if (currentUrlParams.has('sort')) params.set('sort', currentUrlParams.get('sort'));
    if (currentUrlParams.has('dir')) params.set('dir', currentUrlParams.get('dir'));

    const url = '{{ route("employees.index") }}?' + params.toString();

    const tableContainer = document.getElementById('tableContainer');
    if (tableContainer) {
        tableContainer.style.opacity = '0.5';
        tableContainer.style.transition = 'opacity 0.15s ease';
    }

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // 1. Update Tabel & Pagination
        const newTable = doc.getElementById('tableContainer');
        if (newTable && tableContainer) {
            tableContainer.innerHTML = newTable.innerHTML;
            tableContainer.style.opacity = '1';
        }

        // 2. Update Active Filter Chips
        const newChips = doc.getElementById('activeFilterChipsContainer');
        const oldChips = document.getElementById('activeFilterChipsContainer');
        if (newChips && oldChips) {
            oldChips.innerHTML = newChips.innerHTML;
        }

        // 3. Update Header Total Counter
        const newCounter = doc.getElementById('totalCounterHeader');
        const oldCounter = document.getElementById('totalCounterHeader');
        if (newCounter && oldCounter) {
            oldCounter.innerHTML = newCounter.innerHTML;
        }

        // 4. Update Badge Jumlah Filter Aktif di Tombol Filter
        const newBadge = doc.getElementById('filterBadgeSpan');
        const oldBadge = document.getElementById('filterBadgeSpan');
        const filterBtn = document.getElementById('filterToggleBtn');
        if (newBadge && oldBadge) {
            oldBadge.innerHTML = newBadge.innerHTML;
        } else if (newBadge && !oldBadge && filterBtn) {
            filterBtn.appendChild(newBadge);
        } else if (!newBadge && oldBadge) {
            oldBadge.remove();
        }

        // 5. Update URL Browser tanpa reload halaman
        window.history.replaceState({}, '', url);
    })
    .catch(err => {
        console.error('Realtime filter error:', err);
        if (tableContainer) tableContainer.style.opacity = '1';
    });
}

// Reset semua filter secara realtime
function resetFiltersAjax(e) {
    if (e) e.preventDefault();
    document.querySelectorAll('#filterForm input[type="checkbox"]').forEach(cb => cb.checked = false);
    const topSearch = document.getElementById('topSearchInput');
    if (topSearch) topSearch.value = '';
    applyRealtimeFilter();
}

// Hapus parameter filter tertentu dari chip aktif
function removeFilterParam(e, paramName) {
    if (e) e.preventDefault();
    const form = document.getElementById('filterForm');
    if (!form) return;

    form.querySelectorAll(`[name^="${paramName}"]`).forEach(input => {
        if (input.type === 'checkbox') input.checked = false;
        else input.value = '';
    });

    if (paramName === 'q') {
        const topSearch = document.getElementById('topSearchInput');
        if (topSearch) topSearch.value = '';
    }

    applyRealtimeFilter();
}
</script>
@endpush
