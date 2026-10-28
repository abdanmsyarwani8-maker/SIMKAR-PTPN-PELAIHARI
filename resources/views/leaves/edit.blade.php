@extends('layouts.app')
@section('title', 'Edit Data Cuti — ' . $employee->nama)
@section('page-title', 'Edit Data Cuti Karyawan')

@section('content')
<div class="mb-3">
    <a href="{{ route('leaves.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Kembali ke Jadwal Cuti
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Kartu Info Karyawan --}}
        <div class="card mb-3">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:52px;height:52px;border-radius:14px;background:var(--hijau-light);font-size:22px;font-weight:800;color:var(--hijau-tua)">
                    {{ strtoupper(substr($employee->nama, 0, 1)) }}
                </div>
                <div>
                    <div class="fw-700" style="font-size:15px;color:var(--hijau-tua)">{{ $employee->nama }}</div>
                    <div style="font-size:12.5px;color:#6b7c70">
                        {{ $employee->personnel_number ? 'NIK SAP: ' . $employee->personnel_number . ' · ' : '' }}
                        {{ $employee->jabatan?->nama_jabatan ?? 'Jabatan belum diisi' }}
                        @if($employee->bagian)
                            · {{ $employee->bagian->nama_bagian }}
                        @endif
                    </div>
                    @php
                        $bc = match(true) {
                            str_contains($employee->status_karyawan??'', 'Karpim') => 'badge-karpim',
                            str_contains($employee->status_karyawan??'', 'Karpel') => 'badge-karpel',
                            str_contains($employee->status_karyawan??'', 'PKWT')   => 'badge-pkwt',
                            str_contains($employee->status_karyawan??'', 'KTNG')   => 'badge-ktng',
                            str_contains($employee->status_karyawan??'', 'BHL')    => 'badge-bhl',
                            default => 'bg-secondary text-white',
                        };
                    @endphp
                    <span class="badge {{ $bc }} mt-1" style="font-size:11px">
                        {{ $employee->status_karyawan ?? 'Status belum diisi' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form Edit --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-range me-1"></i>
                Edit Data Cuti: <strong>{{ $employee->nama }}</strong>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <div class="fw-600 mb-2"><i class="bi bi-exclamation-circle me-1"></i>Terdapat kesalahan:</div>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                        <li style="font-size:13px">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('leaves.update', $employee) }}">
                    @csrf
                    @method('PUT')

                    {{-- Info ringkasan perhitungan --}}
                    @if($employee->tanggal_acuan_cuti)
                    <div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="font-size:13px;background:#eaf5ee;border-color:#c9e4d3;color:var(--hijau-tua)">
                        <i class="bi bi-info-circle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            Tanggal acuan cuti saat ini:
                            <strong>{{ $employee->tanggal_acuan_cuti->translatedFormat('d F Y') }}</strong>
                            (Bulan jatuh tempo: <strong>{{ $employee->bulan_jatuh_tempo_cuti }}</strong>).
                            @if($employee->tmt_cuti)
                                Menggunakan kolom <em>TMT Cuti</em> sebagai acuan.
                            @else
                                Menggunakan kolom <em>Tanggal Mulai Kerja</em> sebagai acuan fallback.
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="row g-3">
                        {{-- TMT Cuti --}}
                        <div class="col-12">
                            <label for="tmt_cuti" class="form-label fw-600" style="font-size:13px">
                                TMT Cuti
                                <span class="text-muted fw-400">(Tanggal acuan utama untuk perhitungan cuti)</span>
                            </label>
                            <input type="date" name="tmt_cuti" id="tmt_cuti"
                                   class="form-control @error('tmt_cuti') is-invalid @enderror"
                                   value="{{ old('tmt_cuti', $employee->tmt_cuti?->format('Y-m-d')) }}">
                            @error('tmt_cuti')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" style="font-size:11.5px">
                                Jika diisi, tanggal ini digunakan sebagai acuan cuti. Jika dikosongkan, sistem akan menggunakan <em>Tanggal Mulai Kerja</em>.
                            </div>
                        </div>

                        {{-- Tanggal Mulai Kerja --}}
                        <div class="col-12">
                            <label for="tanggal_mulai_kerja" class="form-label fw-600" style="font-size:13px">
                                Tanggal Mulai Kerja
                                <span class="text-muted fw-400">(Acuan fallback jika TMT Cuti kosong)</span>
                            </label>
                            <input type="date" name="tanggal_mulai_kerja" id="tanggal_mulai_kerja"
                                   class="form-control @error('tanggal_mulai_kerja') is-invalid @enderror"
                                   value="{{ old('tanggal_mulai_kerja', $employee->tanggal_mulai_kerja?->format('Y-m-d')) }}">
                            @error('tanggal_mulai_kerja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Tombol aksi --}}
                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-hijau rounded-pill px-4">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('leaves.index') }}" class="btn btn-light rounded-pill px-4 border">
                            Batal
                        </a>
                    </div>
                </form>

                {{-- Tombol Reset TMT Cuti --}}
                @if($employee->tmt_cuti)
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <div>
                            <div class="fw-600" style="font-size:13px;color:#c0392b">Reset TMT Cuti</div>
                            <div style="font-size:12px;color:#888">
                                Menghapus nilai TMT Cuti. Sistem akan kembali menggunakan Tanggal Mulai Kerja sebagai acuan.
                            </div>
                        </div>
                        <form method="POST" action="{{ route('leaves.destroy', $employee) }}"
                              onsubmit="return confirm('Reset TMT Cuti karyawan ini? Sistem akan menggunakan Tanggal Mulai Kerja sebagai acuan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 flex-shrink-0">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset TMT Cuti
                            </button>
                        </form>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>
</div>
@endsection
