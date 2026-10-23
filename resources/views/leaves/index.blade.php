@extends('layouts.app')
@section('title', 'Jadwal & Hak Cuti Karyawan')
@section('page-title', 'Jadwal & Hak Cuti Karyawan')

@push('styles')
<style>
.filter-toggle-btn { font-size: 13px; }
.active-filter-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--hijau-light); color: var(--hijau-tua);
    border: 1px solid #c8dfd0; border-radius: 20px;
    padding: 3px 10px; font-size: 12px; font-weight: 500;
}
.sort-link { color: inherit; text-decoration: none; }
.sort-link:hover { color: var(--hijau-tua); }
.quick-status-pill.active-status-btn {
    background: var(--hijau-tua) !important;
    color: #fff !important;
    border-color: var(--hijau-tua) !important;
    box-shadow: 0 2px 6px rgba(30, 86, 49, 0.25);
}
.quick-status-pill.active-gold-btn {
    background: #b78103 !important;
    color: #fff !important;
    border-color: #b78103 !important;
    box-shadow: 0 2px 6px rgba(183, 129, 3, 0.3);
}
.badge-cuti-panjang {
    background: #fff8e1;
    color: #b78103;
    border: 1px solid #ffe082;
    font-weight: 700;
    padding: 4px 9px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}
.badge-cuti-tahunan {
    background: var(--hijau-light);
    color: var(--hijau-tua);
    border: 1px solid #c8e6c9;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}
.badge-perlu-verif {
    background: #fde8e8;
    color: #c0392b;
    border: 1px solid #f8b4b4;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}
.badge-bulan-tempo {
    background: #f0faf4;
    color: var(--hijau-med);
    border: 1px solid #cce8d7;
    font-weight: 600;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 6px;
}
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

{{-- Header Bar (Sama seperti Halaman Data Karyawan) --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <div id="totalCounterHeader" style="font-size:13px;color:#55695c;font-weight:500;">
            Total ditemukan: <strong class="text-dark">{{ number_format($employees->total()) }}</strong> karyawan
            @if($activeFilters > 0)
            <span class="badge ms-1" style="background:var(--hijau-tua);font-size:11px">
                {{ $activeFilters }} filter aktif
            </span>
            @endif
            <span class="badge ms-1" style="background:#eaf5ee;color:var(--hijau-tua);font-size:11px;border:1px solid #c9e4d3">
                <i class="bi bi-calendar-check me-1"></i>Tahun Acuan: {{ $selectedYear }}
            </span>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap align-items-center">
        {{-- Dropdown Pilih Tahun Acuan --}}
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm dropdown-toggle d-inline-flex align-items-center gap-1"
                    type="button" id="dropdownYearBtn" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:12.5px;font-weight:600">
                <i class="bi bi-calendar-range text-success"></i>
                <span>Tahun: {{ $selectedYear }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownYearBtn"
                style="font-size:13px;border-radius:12px;border:1px solid #c8dfd0;min-width:180px">
                <li class="dropdown-header text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase">
                    Pilih Tahun Acuan Cuti
                </li>
                @php
                    $quickYears = [(int) now()->year - 1, (int) now()->year, (int) now()->year + 1, (int) now()->year + 2, (int) now()->year + 3];
                @endphp
                @foreach($quickYears as $yr)
                <li>
                    <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ $selectedYear === $yr ? 'active bg-success text-white' : '' }}"
                       href="{{ route('leaves.index', array_merge(request()->except(['page', 'year']), ['year' => $yr])) }}">
                        <span>Tahun {{ $yr }}</span>
                        @if($yr === (int) now()->year)
                            <small class="badge bg-light text-dark ms-1">Tahun Ini</small>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- Tombol Export Excel Rekap Cuti --}}
        <a href="{{ route('leaves.export', array_merge(request()->query(), ['year' => $selectedYear])) }}"
           class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" style="font-size:12.5px;font-weight:600">
            <i class="bi bi-file-earmark-excel-fill text-success"></i> Export Excel Rekap Cuti
        </a>

        {{-- Tombol Filter (Toggle) --}}
        <button class="btn btn-hijau btn-sm rounded-pill px-3 filter-toggle-btn shadow-sm d-inline-flex align-items-center gap-1"
                type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" id="filterToggleBtn">
            <i class="bi bi-funnel-fill"></i> Filter
            @if($activeFilters > 0)
            <span class="badge bg-white text-success rounded-pill ms-1" style="font-size:10px; font-weight:700;">{{ $activeFilters }}</span>
            @endif
        </button>
    </div>
</div>

<div class="row g-3">
    {{-- Filter Panel (Collapsible kiri seperti di Data Karyawan) --}}
    <div class="col-lg-3 collapse {{ $activeFilters > 0 ? 'show' : '' }}" id="filterPanel">
        <form method="GET" action="{{ route('leaves.index') }}" id="filterForm">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
            @if(request('dir')) <input type="hidden" name="dir" value="{{ request('dir') }}"> @endif

            <div class="filter-panel mb-3">
                {{-- Header Filter --}}
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <span class="fw-700" style="font-size:13px;color:var(--hijau-tua)">
                        <i class="bi bi-funnel-fill me-1"></i> Panel Filter Cuti
                    </span>
                    <a href="{{ route('leaves.index', ['year' => $selectedYear]) }}" class="btn btn-sm btn-light rounded-pill"
                       style="font-size:11px">Reset</a>
                </div>

                {{-- Filter Jenis Hak Cuti --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        Jenis Hak Cuti ({{ $selectedYear }})
                        @if(request('jenis_cuti')) <span class="badge bg-success" style="font-size:10px">{{ count((array) request('jenis_cuti')) }}</span> @endif
                    </div>
                    <div class="filter-list">
                        @foreach($jenisCutiList as $jc)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="jenis_cuti[]"
                                   value="{{ $jc }}" id="jc_{{ Str::slug($jc) }}"
                                   {{ in_array($jc, (array) request('jenis_cuti', [])) ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label d-flex align-items-center justify-content-between" for="jc_{{ Str::slug($jc) }}">
                                <span>{{ $jc }}</span>
                                @if($jc === 'Cuti Panjang')
                                    <span class="badge" style="background:#fff8e1;color:#b78103;font-size:10px">{{ number_format($countCutiPanjang) }}</span>
                                @elseif($jc === 'Cuti Tahunan')
                                    <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:10px">{{ number_format($countCutiTahunan) }}</span>
                                @elseif($jc === 'Perlu Verifikasi')
                                    <span class="badge bg-danger-subtle text-danger" style="font-size:10px">{{ number_format($countPerluVerifikasi) }}</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Bulan Jatuh Tempo --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        Bulan Jatuh Tempo
                        @if(request('months')) <span class="badge bg-success" style="font-size:10px">{{ count((array) request('months')) }}</span> @endif
                    </div>
                    <input type="text" class="filter-search" placeholder="Cari bulan..." oninput="filterList(this,'bulanList')">
                    <div class="filter-list" id="bulanList">
                        @foreach($monthsList as $num => $namaBulan)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="months[]"
                                   value="{{ $num }}" id="bln_{{ $num }}"
                                   {{ in_array((string)$num, (array) request('months', [])) ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label" for="bln_{{ $num }}">
                                {{ $namaBulan }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Bagian / Afdeling --}}
                <div class="filter-group">
                    <div class="filter-group-title">
                        Bagian / Afdeling
                        @if(request('bagian_ids')) <span class="badge bg-success" style="font-size:10px">{{ count((array) request('bagian_ids')) }}</span> @endif
                    </div>
                    <input type="text" class="filter-search" placeholder="Cari bagian..." oninput="filterList(this,'bagianList')">
                    <div class="filter-list" id="bagianList">
                        @foreach($bagians as $bagian)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="bagian_ids[]"
                                   value="{{ $bagian->id }}" id="bagian_{{ $bagian->id }}"
                                   {{ in_array((string)$bagian->id, (array) request('bagian_ids', [])) ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label" for="bagian_{{ $bagian->id }}">
                                {{ $bagian->nama_bagian }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Status Karyawan --}}
                <div class="filter-group">
                    <div class="filter-group-title">Status Karyawan</div>
                    <div class="filter-list">
                        @foreach($statuses as $st)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="statuses[]"
                                   value="{{ $st }}" id="st_{{ Str::slug($st) }}"
                                   {{ in_array($st, (array) request('statuses', [])) ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label" for="st_{{ Str::slug($st) }}">
                                {{ $st }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol Selesai --}}
                <div class="p-3">
                    <button type="button" class="btn btn-hijau w-100 rounded-pill shadow-sm" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                        <i class="bi bi-check-lg me-1"></i> Selesai Memilih
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Kolom Tabel Utama --}}
    <div class="{{ $activeFilters > 0 ? 'col-lg-9' : 'col-12' }}" id="mainContentCol">

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('leaves.index') }}" class="mb-3">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            @foreach(request()->except(['q', 'page', 'year']) as $key => $val)
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
                       placeholder="Cari nama, NIK SAP..."
                       value="{{ request('q') }}" style="font-size:13.5px">
                <button type="submit" class="btn btn-hijau px-3">Cari</button>
            </div>
        </form>

        {{-- Quick Hak Cuti Filter Pills (Format sama seperti di Data Karyawan) --}}
        <div class="d-flex align-items-center gap-2 flex-wrap mb-3 p-2 bg-white rounded-3 border" style="box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div class="d-flex align-items-center gap-1 text-muted fw-600 px-1" style="font-size:12px">
                <i class="bi bi-stars text-warning"></i> Hak Thn {{ $selectedYear }}:
            </div>

            @php
                $currentJenis = (array) request('jenis_cuti', []);
                $isAllJenisActive = empty($currentJenis);
            @endphp

            <a href="{{ route('leaves.index', array_merge(request()->except(['jenis_cuti', 'page']), ['year' => $selectedYear])) }}"
               class="btn btn-sm rounded-pill px-3 py-1 fw-600 quick-status-pill {{ $isAllJenisActive ? 'active-status-btn btn-hijau' : 'btn-outline-secondary' }}"
               style="font-size:12px; text-decoration:none">
                Semua
                <span class="badge rounded-pill ms-1 {{ $isAllJenisActive ? 'bg-white text-dark' : 'bg-light text-dark border' }}">
                    {{ number_format($totalActive) }}
                </span>
            </a>

            <a href="{{ route('leaves.index', array_merge(request()->except(['jenis_cuti', 'page']), ['year' => $selectedYear, 'jenis_cuti' => ['Cuti Panjang']])) }}"
               class="btn btn-sm rounded-pill px-3 py-1 fw-600 quick-status-pill {{ in_array('Cuti Panjang', $currentJenis) ? 'active-gold-btn' : 'btn-outline-warning text-dark' }}"
               style="font-size:12px; text-decoration:none">
                <i class="bi bi-stars text-warning"></i> Cuti Panjang
                <span class="badge rounded-pill ms-1 {{ in_array('Cuti Panjang', $currentJenis) ? 'bg-white text-dark' : 'bg-light text-dark border' }}">
                    {{ number_format($countCutiPanjang) }}
                </span>
            </a>

            <a href="{{ route('leaves.index', array_merge(request()->except(['jenis_cuti', 'page']), ['year' => $selectedYear, 'jenis_cuti' => ['Cuti Tahunan']])) }}"
               class="btn btn-sm rounded-pill px-3 py-1 fw-600 quick-status-pill {{ in_array('Cuti Tahunan', $currentJenis) ? 'active-status-btn btn-hijau' : 'btn-outline-secondary' }}"
               style="font-size:12px; text-decoration:none">
                <i class="bi bi-calendar-check text-success"></i> Cuti Tahunan
                <span class="badge rounded-pill ms-1 {{ in_array('Cuti Tahunan', $currentJenis) ? 'bg-white text-dark' : 'bg-light text-dark border' }}">
                    {{ number_format($countCutiTahunan) }}
                </span>
            </a>

            @if($countPerluVerifikasi > 0)
            <a href="{{ route('leaves.index', array_merge(request()->except(['jenis_cuti', 'page']), ['year' => $selectedYear, 'jenis_cuti' => ['Perlu Verifikasi']])) }}"
               class="btn btn-sm rounded-pill px-3 py-1 fw-600 quick-status-pill {{ in_array('Perlu Verifikasi', $currentJenis) ? 'btn-danger text-white' : 'btn-outline-danger' }}"
               style="font-size:12px; text-decoration:none">
                <i class="bi bi-exclamation-triangle-fill"></i> Perlu Verifikasi
                <span class="badge rounded-pill ms-1 {{ in_array('Perlu Verifikasi', $currentJenis) ? 'bg-white text-dark' : 'bg-light text-danger border' }}">
                    {{ number_format($countPerluVerifikasi) }}
                </span>
            </a>
            @endif
        </div>

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
            @if(request('jenis_cuti'))
            <span class="active-filter-chip">
                Hak: {{ implode(', ', (array) request('jenis_cuti')) }}
                <a href="{{ request()->fullUrlWithoutQuery(['jenis_cuti']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
            </span>
            @endif
            @if(request('months'))
                @php
                    $mLabels = collect((array) request('months'))->map(fn($m) => $monthsList[(int)$m] ?? $m)->implode(', ');
                @endphp
                <span class="active-filter-chip">
                    Bulan: {{ $mLabels }}
                    <a href="{{ request()->fullUrlWithoutQuery(['months']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
                </span>
            @endif
            @if(request('bagian_ids'))
                @php
                    $bLabels = $bagians->whereIn('id', (array) request('bagian_ids'))->pluck('nama_bagian')->implode(', ');
                @endphp
                <span class="active-filter-chip">
                    Bagian: {{ $bLabels }}
                    <a href="{{ request()->fullUrlWithoutQuery(['bagian_ids']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
                </span>
            @endif
            @if(request('statuses'))
                <span class="active-filter-chip">
                    Status: {{ implode(', ', (array) request('statuses')) }}
                    <a href="{{ request()->fullUrlWithoutQuery(['statuses']) }}" style="color:inherit"><i class="bi bi-x-circle"></i></a>
                </span>
            @endif
            <a href="{{ route('leaves.index', ['year' => $selectedYear]) }}" class="btn btn-sm btn-outline-danger rounded-pill" style="font-size:11px">
                <i class="bi bi-x-circle me-1"></i>Reset Semua
            </a>
        </div>
        @endif

        {{-- Tabel Container --}}
        <div id="tableContainer">
            <div class="card">
                @if($employees->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3" style="width:64px;height:64px;border-radius:50%;background:#f0faf4;color:var(--hijau-med);display:inline-flex;align-items:center;justify-content:center;font-size:28px;">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h6 class="fw-700 text-dark mb-1">Tidak ada data cuti yang sesuai</h6>
                    <p class="text-muted mb-3" style="font-size:13px">
                        Silakan atur kembali kata kunci pencarian, tahun acuan, atau filter yang dipilih.
                    </p>
                    <a href="{{ route('leaves.index', ['year' => $selectedYear]) }}" class="btn btn-hijau-outline btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-simkar mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>
                                    <a class="sort-link" href="{{ request()->fullUrlWithQuery(['sort'=>'nama','dir'=>request('sort')=='nama'&&request('dir')=='asc'?'desc':'asc']) }}">
                                        Nama Karyawan <i class="bi bi-arrow-down-up ms-1 opacity-50"></i>
                                    </a>
                                </th>
                                <th>NIK SAP</th>
                                <th>Bagian / Afd</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                                <th class="text-center">TMT Kerja</th>
                                <th class="text-center">TMT Cuti</th>
                                <th class="text-center">Bulan Tempo</th>
                                <th class="text-center">Hak Cuti Thn {{ $selectedYear }}</th>
                                <th class="text-center">Cuti Pjg Berikutnya</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $index => $emp)
                            @php
                                $cuti = $emp->getCutiStatusForYear($selectedYear);

                                $badgeClass = match(true) {
                                    str_contains($emp->status_karyawan??'', 'Karpim') => 'badge-karpim',
                                    str_contains($emp->status_karyawan??'', 'Karpel') => 'badge-karpel',
                                    str_contains($emp->status_karyawan??'', 'PKWT')   => 'badge-pkwt',
                                    str_contains($emp->status_karyawan??'', 'KTNG')   => 'badge-ktng',
                                    str_contains($emp->status_karyawan??'', 'BHL')    => 'badge-bhl',
                                    str_contains($emp->status_karyawan??'', 'Papam')  => 'badge-papam',
                                    default => 'bg-secondary text-white',
                                };
                            @endphp
                            <tr>
                                <td class="text-muted" style="font-size:12px">{{ $employees->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('employees.show', $emp) }}"
                                       class="fw-600 text-decoration-none" style="color:var(--hijau-tua)">
                                        {{ $emp->nama }}
                                    </a>
                                </td>
                                <td style="font-size:12.5px;color:#2c3e50">{{ $emp->personnel_number ?: '-' }}</td>
                                <td style="font-size:12.5px">{{ $emp->bagian?->nama_bagian ?: '-' }}</td>
                                <td style="font-size:12.5px">{{ $emp->jabatan?->nama_jabatan ?: '-' }}</td>
                                <td>
                                    <span class="badge {{ $badgeClass }}" style="font-size:10.5px">
                                        {{ $emp->status_karyawan ?: '-' }}
                                    </span>
                                </td>
                                <td class="text-center" style="font-size:12px;font-weight:500">
                                    {{ $emp->tanggal_mulai_kerja ? $emp->tanggal_mulai_kerja->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center" style="font-size:12px;font-weight:600;color:var(--hijau-tua)">
                                    {{ $emp->tmt_cuti ? $emp->tmt_cuti->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    @if($cuti['is_valid'])
                                        <span class="badge-bulan-tempo">
                                            <i class="bi bi-calendar-event me-1"></i>{{ $cuti['bulan_jatuh_tempo'] }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:12px">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(! $cuti['is_valid'])
                                        <span class="badge-perlu-verif">
                                            <i class="bi bi-exclamation-triangle-fill"></i> Perlu Verifikasi
                                        </span>
                                    @elseif($cuti['is_cuti_panjang'])
                                        <span class="badge-cuti-panjang" title="Jatuh tempo Cuti Panjang siklus ke-{{ $cuti['siklus_ke'] }}">
                                            <i class="bi bi-stars"></i> Cuti Panjang (Ke-{{ $cuti['siklus_ke'] }})
                                        </span>
                                    @elseif($cuti['jenis_cuti'] === 'Belum Memenuhi Syarat')
                                        <span class="badge bg-light text-muted border" style="font-size:11px">Belum 1 Thn</span>
                                    @else
                                        <span class="badge-cuti-tahunan" title="Hak cuti tahunan berulang tiap bulan {{ $cuti['bulan_jatuh_tempo'] }}">
                                            <i class="bi bi-check2-circle"></i> Cuti Tahunan
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($cuti['is_valid'] && is_numeric($cuti['tahun_cuti_panjang_berikutnya']))
                                        <span class="badge" style="background:#eaf5ee;color:var(--hijau-tua);font-weight:700;font-size:11.5px;border:1px solid #c9e4d3;">
                                            Thn {{ $cuti['tahun_cuti_panjang_berikutnya'] }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size:12px">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('employees.show', $emp) }}" class="btn-action" title="Lihat Profil & Riwayat Cuti">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination (Sama persis seperti Data Karyawan) --}}
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

// Inisialisasi event listener setelah DOM siap
document.addEventListener('DOMContentLoaded', function () {
    const filterPanel = document.getElementById('filterPanel');
    const mainContentCol = document.getElementById('mainContentCol');

    if (filterPanel && mainContentCol) {
        filterPanel.addEventListener('show.bs.collapse', function () {
            mainContentCol.classList.remove('col-12');
            mainContentCol.classList.add('col-lg-9');
        });

        filterPanel.addEventListener('hide.bs.collapse', function () {
            mainContentCol.classList.remove('col-lg-9');
            mainContentCol.classList.add('col-12');
        });
    }
});
</script>
@endpush
