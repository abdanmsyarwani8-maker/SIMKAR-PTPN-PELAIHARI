@extends('layouts.app')
@section('title', $employee->nama . ' — Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Data Karyawan
    </a>
</div>

<div class="row g-3">
    {{-- Kartu Identitas --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:80px;height:80px;border-radius:20px;background:var(--hijau-light);font-size:36px;font-weight:800;color:var(--hijau-tua)">
                    {{ strtoupper(substr($employee->nama, 0, 1)) }}
                </div>
                <h5 class="fw-700 mb-1" style="color:var(--hijau-tua)">{{ $employee->nama }}</h5>
                <p style="font-size:13px;color:#6b7c70" class="mb-2">
                    {{ $employee->jabatan?->nama_jabatan ?? 'Jabatan belum diisi' }}
                </p>

                @php
                    $bc = match(true) {
                        str_contains($employee->status_karyawan??'', 'Karpim') => 'badge-karpim',
                        str_contains($employee->status_karyawan??'', 'Karpel') => 'badge-karpel',
                        str_contains($employee->status_karyawan??'', 'PKWT')   => 'badge-pkwt',
                        str_contains($employee->status_karyawan??'', 'KTNG')   => 'badge-ktng',
                        str_contains($employee->status_karyawan??'', 'BHL')    => 'badge-bhl',
                        str_contains($employee->status_karyawan??'', 'Papam')  => 'badge-papam',
                        default => 'bg-secondary text-white',
                    };
                @endphp
                <span class="badge {{ $bc }} mb-3" style="font-size:12px;padding:6px 14px">
                    {{ $employee->status_karyawan ?? 'Status belum diisi' }}
                </span>

                <div class="border-top pt-3 text-start">
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-diagram-3 text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Bagian</div>
                            <div style="font-size:13px;font-weight:500">{{ $employee->bagian?->nama_bagian ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-briefcase text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Mulai Kerja & Lama Bekerja</div>
                            <div style="font-size:13px;font-weight:500">
                                {{ $employee->tanggal_mulai_kerja?->format('d M Y') ?? '-' }}
                            </div>
                            @if($employee->masa_kerja)
                            <div class="mt-1" style="font-size:11.5px;font-weight:600;color:var(--hijau-tua)">
                                <i class="bi bi-clock-history me-1"></i>{{ $employee->masa_kerja }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-hourglass-split text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Perkiraan Pensiun (55 Thn)</div>
                            <div style="font-size:13px;font-weight:500">
                                {{ $employee->tanggal_pensiun?->format('d M Y') ?? '-' }}
                                @if($employee->sisa_waktu_pensiun)
                                <span style="font-size:11px;font-weight:600;color:{{ $employee->sisa_waktu_pensiun === 'Sudah Pensiun' ? '#dc3545' : '#198754' }}">
                                    ({{ $employee->sisa_waktu_pensiun }})
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-calendar2-check text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Hak & Bulan Jatuh Tempo Cuti</div>
                            <div style="font-size:13px;font-weight:600;color:var(--hijau-tua)">
                                {{ $employee->bulan_jatuh_tempo_cuti }}
                            </div>
                            @php
                                $statusCutiShow = $employee->getCutiStatusForYear();
                            @endphp
                            <div class="mt-1" style="font-size:11px;">
                                @if(! $statusCutiShow['is_valid'])
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        Perlu Verifikasi
                                    </span>
                                @elseif($statusCutiShow['is_cuti_panjang'])
                                    <span class="badge" style="background:#fff8e1;color:#b78103;border:1px solid #ffe082;">
                                        <i class="bi bi-stars me-1"></i>Cuti Panjang Thn {{ now()->year }}
                                    </span>
                                @else
                                    <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);border:1px solid #c8e6c9;">
                                        <i class="bi bi-check2 me-1"></i>Cuti Tahunan Thn {{ now()->year }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <i class="bi bi-file-earmark-text text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Sumber Data</div>
                            <div style="font-size:13px;font-weight:500">{{ $employee->sumber_lembar ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @auth @if(auth()->user()->isAdmin())
                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-hijau rounded-pill">
                        <i class="bi bi-pencil-square me-1"></i> Edit Data
                    </a>
                    <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill" style="font-size:13px">
                            <i class="bi bi-trash3 me-1"></i> Hapus Data
                        </button>
                    </form>
                </div>
                @endif @endauth
            </div>
        </div>
    </div>

    {{-- Detail Data --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-vcard-fill me-2"></i>
                Informasi Lengkap Karyawan
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                    $isPkwt = str_contains(strtoupper($employee->status_karyawan ?? ''), 'PKWT');
                    $isBhl = str_contains(strtoupper($employee->status_karyawan ?? ''), 'BHL');

                    $pensiunVal = '-';
                    if (! $isBhl && $employee->tanggal_pensiun) {
                        $pensiunVal = $employee->tanggal_pensiun->format('d M Y').' ('.$employee->sisa_waktu_pensiun.')';
                    }

                    $jkVal = in_array($employee->jenis_kelamin, ['Laki-Laki', 'Perempuan']) ? $employee->jenis_kelamin : '-';
                    $domVal = $employee->domisili ?: '-';
                    $nikVal = $employee->personnel_number ?: '-';
                    $agamaVal = $employee->agama ?: '-';
                    $pendidikanVal = ($employee->pendidikan_terakhir && $employee->pendidikan_terakhir !== 'Belum Diisi') ? $employee->pendidikan_label : '-';
                    $tmtVal = $employee->tanggal_mulai_kerja ? $employee->tanggal_mulai_kerja->format('d M Y') : '-';
                    $tglLahirVal = $employee->tanggal_lahir ? $employee->tanggal_lahir->format('d M Y').' ('.($employee->umur ? $employee->umur.' tahun' : '-').')' : '-';
                    $masaKerjaVal = $employee->masa_kerja ?: '-';

                    $fields = [
                        ['label'=>'NIK SAP', 'val'=>$nikVal, 'icon'=>'credit-card'],
                        ['label'=>'Tanggal Lahir', 'val'=>$tglLahirVal, 'icon'=>'calendar'],
                        ['label'=>'Masa Kerja (Lama Bekerja)', 'val'=>$masaKerjaVal, 'icon'=>'briefcase'],
                        ['label'=>'Tanggal Mulai Kerja', 'val'=>$tmtVal, 'icon'=>'calendar-check'],
                        ['label'=>'TMT Cuti (Acuan)', 'val'=>$employee->tmt_cuti ? $employee->tmt_cuti->format('d M Y') : '-', 'icon'=>'calendar-event'],
                        ['label'=>'Pensiun (Usia 55)', 'val'=>$pensiunVal, 'icon'=>'hourglass-split'],
                        ['label'=>'Jenis Kelamin', 'val'=>$jkVal, 'icon'=>'gender-ambiguous'],
                        ['label'=>'Agama', 'val'=>$agamaVal, 'icon'=>'moon-stars'],
                        ['label'=>'Pendidikan Terakhir', 'val'=>$pendidikanVal, 'icon'=>'mortarboard'],
                        ['label'=>'Rentang Usia', 'val'=>$employee->rentang_usia ?: '-', 'icon'=>'clock-history'],
                    ];

                    if ($isPkwt) {
                        $fields[] = ['label'=>'No. PKWT', 'val'=>$employee->no_pkwt ?: '-', 'icon'=>'file-earmark-text'];
                    } elseif (! $isBhl) {
                        $fields[] = ['label'=>'Golongan', 'val'=>$employee->golongan ?: '-', 'icon'=>'layers'];
                    }

                    if ($employee->penghargaan_masa_kerja) {
                        $fields[] = ['label'=>'Penghargaan Masa Kerja', 'val'=>'Penghargaan '.$employee->penghargaan_masa_kerja, 'icon'=>'award'];
                    }

                    $fields[] = ['label'=>'Grade', 'val'=>$employee->grade ?: '-', 'icon'=>'award'];
                    $fields[] = ['label'=>'Suku', 'val'=>$employee->suku ?: '-', 'icon'=>'geo-alt'];
                    $fields[] = ['label'=>'Domisili', 'val'=>$domVal, 'icon'=>'geo-fill'];
                    $fields[] = ['label'=>'Alamat', 'val'=>$employee->alamat ?: '-', 'icon'=>'house'];
                    @endphp

                    @foreach($fields as $field)
                    <div class="col-sm-6 {{ $field['label'] === 'Alamat' ? 'col-sm-12' : '' }}">
                        <div class="p-3 rounded-3" style="background:var(--hijau-pale);border:1px solid #e0ede5">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-{{ $field['icon'] }}" style="color:var(--hijau-med);font-size:13px"></i>
                                <span style="font-size:11px;color:#6b7c70;font-weight:600;text-transform:uppercase;letter-spacing:.3px">
                                    {{ $field['label'] }}
                                </span>
                            </div>
                            <div style="font-size:14px;font-weight:500;color:#1e2d24">
                                @if($field['label'] === 'Domisili')
                                    @if(!empty($employee->domisili))
                                        <span class="badge {{ $employee->domisili === 'Lokal' ? 'bg-success' : 'bg-secondary' }}" style="font-size:12px">
                                            <i class="bi bi-geo-alt-fill me-1"></i>{{ $employee->domisili }} ({{ $employee->domisili === 'Lokal' ? 'Kalimantan Selatan' : 'Luar Kalsel' }})
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @elseif($field['label'] === 'Masa Kerja (Lama Bekerja)' && $employee->masa_kerja)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:13px;font-weight:600">
                                        <i class="bi bi-clock-history me-1"></i>{{ $field['val'] }}
                                    </span>
                                @elseif($field['label'] === 'Penghargaan Masa Kerja' && $employee->penghargaan_masa_kerja)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size:13px;font-weight:600">
                                        <i class="bi bi-award-fill me-1"></i>Penghargaan {{ $employee->penghargaan_masa_kerja }}
                                    </span>
                                @else
                                    {{ $field['val'] ?: '-' }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($employee->catatan)
                <div class="mt-3 p-3 rounded-3" style="background:#fffbe8;border:1px solid #f0e08a">
                    <div style="font-size:11px;font-weight:600;color:#856404;margin-bottom:4px">
                        <i class="bi bi-sticky me-1"></i> CATATAN
                    </div>
                    <div style="font-size:13.5px">{{ $employee->catatan }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Card Hak & Jadwal Cuti --}}
        @php
            $cutiInfo = $employee->getCutiStatusForYear();
        @endphp
        <div class="card mt-3 border-0 shadow-sm" style="border-radius:14px; border:1px solid #e0ede5 !important;">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="fw-700" style="color:var(--hijau-tua); font-size:15px;">
                    <i class="bi bi-calendar2-check-fill me-2 text-success"></i> Hak & Jadwal Cuti Karyawan
                </div>
                <a href="{{ route('leaves.index', ['q' => $employee->personnel_number ?: $employee->nama]) }}"
                   class="btn btn-sm btn-hijau-outline rounded-pill px-3" style="font-size:12px;">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Monitoring Cuti
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 rounded-3 h-100" style="background:var(--hijau-pale); border:1px solid #d4e8da;">
                            <div class="d-flex align-items-center gap-1 mb-1 text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">
                                <i class="bi bi-calendar-event text-success"></i> Tanggal Acuan
                            </div>
                            <div class="fw-700 fs-6 text-dark">
                                {{ $employee->tanggal_acuan_cuti?->format('d/m/Y') ?? 'Perlu Verifikasi' }}
                            </div>
                            <div class="text-muted mt-1" style="font-size:11px;">
                                {{ $employee->tanggal_mulai_kerja ? 'Tgl Pengangkatan Kerja' : ($employee->tmt_cuti ? 'TMT Cuti (Sumber Data)' : 'Belum Terdata') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 rounded-3 h-100" style="background:var(--hijau-pale); border:1px solid #d4e8da;">
                            <div class="d-flex align-items-center gap-1 mb-1 text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">
                                <i class="bi bi-calendar2-month text-success"></i> Bln Jatuh Tempo
                            </div>
                            <div class="fw-700 fs-6" style="color:var(--hijau-tua);">
                                {{ $employee->bulan_jatuh_tempo_cuti }}
                            </div>
                            <div class="text-muted mt-1" style="font-size:11px;">
                                Berulang setiap tahun
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 rounded-3 h-100" style="background:{{ $cutiInfo['is_cuti_panjang'] ? '#fffdf0' : 'var(--hijau-pale)' }}; border:1px solid {{ $cutiInfo['is_cuti_panjang'] ? '#ffe082' : '#d4e8da' }};">
                            <div class="d-flex align-items-center gap-1 mb-1" style="font-size:11px; font-weight:700; text-transform:uppercase; color:{{ $cutiInfo['is_cuti_panjang'] ? '#b78103' : '#6b7c70' }};">
                                <i class="bi bi-stars"></i> Hak Thn {{ now()->year }}
                            </div>
                            <div>
                                @if(! $cutiInfo['is_valid'])
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        Perlu Verifikasi
                                    </span>
                                @elseif($cutiInfo['is_cuti_panjang'])
                                    <span class="badge" style="background:#fff8e1; color:#b78103; border:1px solid #ffe082; font-weight:700;">
                                        Cuti Panjang (Ke-{{ $cutiInfo['siklus_ke'] }})
                                    </span>
                                @elseif($cutiInfo['jenis_cuti'] === 'Belum Memenuhi Syarat')
                                    <span class="badge bg-light text-muted border" style="font-size:11px;">
                                        Belum 1 Thn
                                    </span>
                                @else
                                    <span class="badge" style="background:var(--hijau-light); color:var(--hijau-tua); border:1px solid #c8e6c9; font-weight:600;">
                                        Cuti Tahunan
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1" style="font-size:11px; color:#688070;">
                                {{ $cutiInfo['keterangan'] }}
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 rounded-3 h-100" style="background:var(--hijau-pale); border:1px solid #d4e8da;">
                            <div class="d-flex align-items-center gap-1 mb-1 text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">
                                <i class="bi bi-clock-history text-success"></i> Cuti Pjg Berikutnya
                            </div>
                            <div class="fw-700 fs-6" style="color:#b78103;">
                                @if(is_numeric($employee->tahun_cuti_panjang_berikutnya))
                                    Tahun {{ $employee->tahun_cuti_panjang_berikutnya }}
                                @else
                                    {{ $employee->tahun_cuti_panjang_berikutnya }}
                                @endif
                            </div>
                            <div class="text-muted mt-1" style="font-size:11px;">
                                Siklus kelipatan 6 tahun
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Timeline Proyeksi 5 Tahun --}}
                @if($cutiInfo['is_valid'])
                <div class="mt-3 pt-3 border-top">
                    <div class="fw-600 text-muted mb-2" style="font-size:12px;">
                        <i class="bi bi-clock-history me-1"></i> Proyeksi Hak Cuti 5 Tahun:
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $previewYears = [now()->year, now()->year + 1, now()->year + 2, now()->year + 3, now()->year + 4];
                        @endphp
                        @foreach($previewYears as $pYear)
                            @php
                                $st = $employee->getCutiStatusForYear($pYear);
                            @endphp
                            <div class="px-3 py-2 rounded-3 border d-flex align-items-center gap-2"
                                 style="background: {{ $st['is_cuti_panjang'] ? '#fff8e1' : '#ffffff' }}; border-color: {{ $st['is_cuti_panjang'] ? '#ffe082' : '#e0ede5' }} !important; font-size:12px;">
                                <span class="fw-700 text-dark">{{ $pYear }}:</span>
                                @if($st['is_cuti_panjang'])
                                    <span class="badge" style="background:#b78103; color:#fff; font-size:10.5px;">
                                        <i class="bi bi-stars"></i> Cuti Panjang (Ke-{{ $st['siklus_ke'] }})
                                    </span>
                                @elseif($st['jenis_cuti'] === 'Belum Memenuhi Syarat')
                                    <span class="text-muted" style="font-size:11px;">Belum 1 Thn</span>
                                @else
                                    <span class="text-muted" style="font-size:11.5px;">
                                        Cuti Tahunan ({{ $st['bulan_jatuh_tempo'] }})
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Data Batih / Keluarga --}}
        @include('employees._family_section')

        {{-- Meta info --}}
        <div class="card mt-3">
            <div class="card-body py-3">
                <div class="row text-muted" style="font-size:12px">
                    <div class="col-sm-4">
                        <i class="bi bi-clock me-1"></i>
                        Ditambahkan: {{ $employee->created_at->format('d M Y H:i') }}
                    </div>
                    <div class="col-sm-4">
                        <i class="bi bi-pencil me-1"></i>
                        Diperbarui: {{ $employee->updated_at->format('d M Y H:i') }}
                    </div>
                    <div class="col-sm-4">
                        <i class="bi bi-database me-1"></i>
                        Sumber: {{ $employee->sumber_file ?? 'Input manual' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
