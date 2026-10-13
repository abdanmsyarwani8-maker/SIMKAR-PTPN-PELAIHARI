@extends('layouts.app')
@section('title', $employee->nama . ' — Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
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
                        str_contains($employee->status_karyawan??'', 'Papam')  => 'badge-papam',
                        default => 'bg-secondary text-white',
                    };
                @endphp
                <span class="badge {{ $bc }} mb-3" style="font-size:12px;padding:6px 14px">
                    {{ $employee->status_karyawan ?? 'Status belum diisi' }}
                </span>

                <div class="border-top pt-3 text-start">
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-building text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Unit / Kebun</div>
                            <div style="font-size:13px;font-weight:500">{{ $employee->unit?->nama_unit ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-diagram-3 text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Bagian</div>
                            <div style="font-size:13px;font-weight:500">{{ $employee->bagian?->nama_bagian ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="bi bi-calendar-check text-muted mt-1" style="width:18px"></i>
                        <div>
                            <div style="font-size:11px;color:#888">Mulai Kerja</div>
                            <div style="font-size:13px;font-weight:500">
                                {{ $employee->tanggal_mulai_kerja?->format('d M Y') ?? '-' }}
                                @if($employee->masa_kerja)
                                <span style="font-size:11px;color:#6b7c70">({{ $employee->masa_kerja }})</span>
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
                        <i class="bi bi-pencil me-1"></i> Edit Data
                    </a>
                    <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 rounded-pill" style="font-size:13px">
                            <i class="bi bi-trash me-1"></i> Hapus Data
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
                <i class="bi bi-person-lines-fill me-2 text-success"></i>
                Informasi Lengkap Karyawan
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                    $fields = [
                        ['label'=>'Pers. No.', 'val'=>$employee->pers_no, 'icon'=>'hash'],
                        ['label'=>'NIK SAP', 'val'=>$employee->personnel_number, 'icon'=>'credit-card'],
                        ['label'=>'NIK Lokal', 'val'=>$employee->nik_lokal, 'icon'=>'card-text'],
                        ['label'=>'Tanggal Lahir', 'val'=>$employee->tanggal_lahir?->format('d M Y').' ('.($employee->umur ? $employee->umur.' tahun' : '-').')', 'icon'=>'calendar'],
                        ['label'=>'Jenis Kelamin', 'val'=>$employee->jenis_kelamin, 'icon'=>'gender-ambiguous'],
                        ['label'=>'Agama', 'val'=>$employee->agama ?? 'Belum diisi', 'icon'=>'moon-stars'],
                        ['label'=>'Pendidikan Terakhir', 'val'=>$employee->pendidikan_label, 'icon'=>'mortarboard'],
                        ['label'=>'Rentang Usia', 'val'=>$employee->rentang_usia, 'icon'=>'clock-history'],
                        ['label'=>'Golongan', 'val'=>$employee->golongan ?? '-', 'icon'=>'layers'],
                        ['label'=>'Grup Golongan', 'val'=>$employee->grup_golongan ?? '-', 'icon'=>'stack'],
                        ['label'=>'Grup Jabatan', 'val'=>$employee->grup_jabatan ?? '-', 'icon'=>'briefcase'],
                        ['label'=>'No. PKWT', 'val'=>$employee->no_pkwt ?? '-', 'icon'=>'file-earmark-text'],
                    ];
                    @endphp

                    @foreach($fields as $field)
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--hijau-pale);border:1px solid #e0ede5">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-{{ $field['icon'] }}" style="color:var(--hijau-med);font-size:13px"></i>
                                <span style="font-size:11px;color:#6b7c70;font-weight:600;text-transform:uppercase;letter-spacing:.3px">
                                    {{ $field['label'] }}
                                </span>
                            </div>
                            <div style="font-size:14px;font-weight:500;color:#1e2d24">
                                {{ $field['val'] ?? '-' }}
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
