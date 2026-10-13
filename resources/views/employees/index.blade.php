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

{{-- Header Bar --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-700" style="color:var(--hijau-tua)">Data Karyawan</h5>
        <div style="font-size:12.5px;color:#6b7c70">
            Total ditemukan: <strong>{{ number_format($employees->total()) }}</strong> karyawan
            @if($activeFilters > 0)
            <span class="badge ms-1" style="background:var(--hijau-tua);font-size:11px">
                {{ $activeFilters }} filter aktif
            </span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @auth @if(auth()->user()->isAdmin())
        <a href="{{ route('employees.create') }}" class="btn btn-hijau btn-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Tambah Karyawan
        </a>
        @endif @endauth

        <button class="btn btn-sm btn-hijau-outline rounded-pill px-3 filter-toggle-btn"
                type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
            <i class="bi bi-funnel-fill me-1"></i> Filter
            @if($activeFilters > 0)
            <span class="filter-badge ms-1">{{ $activeFilters }}</span>
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
                        <i class="bi bi-funnel me-1"></i> Panel Filter
                    </span>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light rounded-pill"
                       style="font-size:11px">Reset</a>
                </div>

                {{-- Filter Unit/Kebun --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        Unit / Kebun
                        @if(request('unit_ids')) <span class="filter-badge">{{ count(request('unit_ids')) }}</span> @endif
                    </div>
                    <input type="text" class="filter-search" placeholder="Cari unit..." oninput="filterList(this,'unitList')">
                    <div class="filter-list" id="unitList">
                        @foreach($units as $unit)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="unit_ids[]"
                                   value="{{ $unit->id }}" id="unit{{ $unit->id }}"
                                   {{ in_array($unit->id, request('unit_ids',[]))?'checked':'' }}>
                            <label class="form-check-label" for="unit{{ $unit->id }}">
                                {{ $unit->nama_unit }}
                            </label>
                        </div>
                        @endforeach
                    </div>
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

                {{-- Tombol Terapkan --}}
                <div class="p-3">
                    <button type="submit" class="btn btn-hijau w-100 rounded-pill">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Utama --}}
    <div class="{{ $activeFilters > 0 ? 'col-lg-9' : 'col-12' }}">

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('employees.index') }}" class="mb-3">
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
                <input type="text" name="q" class="form-control border-start-0 ps-0"
                       placeholder="Cari nama, NIK SAP, atau nomor karyawan..."
                       value="{{ request('q') }}" style="font-size:13.5px">
                <button type="submit" class="btn btn-hijau px-3">Cari</button>
            </div>
        </form>

        {{-- Active Filter Chips --}}
        @if($activeFilters > 0)
        <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
            <span style="font-size:12px;color:#6b7c70">Filter aktif:</span>
            @if(request('q'))
            <span class="active-filter-chip">
                Pencarian: "{{ request('q') }}"
                <a href="{{ request()->fullUrlWithoutQuery(['q']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('unit_ids'))
            <span class="active-filter-chip">
                Unit ({{ count(request('unit_ids')) }})
                <a href="{{ request()->fullUrlWithoutQuery(['unit_ids']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('bagian_ids'))
            <span class="active-filter-chip">
                Bagian ({{ count(request('bagian_ids')) }})
                <a href="{{ request()->fullUrlWithoutQuery(['bagian_ids']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('statuses'))
            <span class="active-filter-chip">
                Status ({{ count(request('statuses')) }})
                <a href="{{ request()->fullUrlWithoutQuery(['statuses']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('jenis_kelamin'))
            <span class="active-filter-chip">
                JK: {{ implode(', ', request('jenis_kelamin')) }}
                <a href="{{ request()->fullUrlWithoutQuery(['jenis_kelamin']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('agama'))
            <span class="active-filter-chip">
                Agama ({{ count(request('agama')) }})
                <a href="{{ request()->fullUrlWithoutQuery(['agama']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('rentang_usia'))
            <span class="active-filter-chip">
                Usia ({{ count(request('rentang_usia')) }})
                <a href="{{ request()->fullUrlWithoutQuery(['rentang_usia']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-danger rounded-pill" style="font-size:11px">
                <i class="bi bi-x-circle me-1"></i>Reset Semua
            </a>
        </div>
        @endif

        {{-- Tabel --}}
        <div class="card">
            @if($employees->isEmpty())
            <div class="empty-state">
                <i class="bi bi-people"></i>
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
                            <th>Unit / Kebun</th>
                            <th>Bagian</th>
                            <th>Jabatan</th>
                            <th>J.K</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $k)
                        @php
                            $badgeClass = match(true) {
                                str_contains($k->status_karyawan??'', 'Karpim') => 'badge-karpim',
                                str_contains($k->status_karyawan??'', 'Karpel') => 'badge-karpel',
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
                            <td><code style="font-size:11.5px">{{ $k->personnel_number ?? '-' }}</code></td>
                            <td style="font-size:12.5px">{{ $k->unit?->nama_unit ?? '-' }}</td>
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
                                <div class="d-flex gap-1">
                                    <a href="{{ route('employees.show', $k) }}"
                                       class="btn btn-sm btn-light rounded-pill" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @auth @if(auth()->user()->isAdmin())
                                    <a href="{{ route('employees.edit', $k) }}"
                                       class="btn btn-sm btn-light rounded-pill" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.destroy', $k) }}"
                                          onsubmit="return confirm('Hapus data {{ addslashes($k->nama) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-pill text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
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
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
                <div style="font-size:12.5px;color:#6b7c70">
                    Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }}
                    dari {{ number_format($employees->total()) }} karyawan
                </div>
                {{ $employees->withQueryString()->links('vendor.pagination.bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
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

// Submit filter saat checkbox berubah (opsional — bisa manual dengan tombol)
// Dibiarkan manual agar filter bisa dikombinasikan dulu
</script>
@endpush
