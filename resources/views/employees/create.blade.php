@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan Baru')

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-person-plus-fill me-2 text-success"></i>
        Formulir Tambah Data Karyawan
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

        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            @php $employee = null; @endphp
            @include('employees._form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-hijau rounded-pill px-4">
                    <i class="bi bi-check-lg me-1"></i> Simpan Data
                </button>
                <a href="{{ route('employees.index') }}" class="btn btn-light rounded-pill px-4">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
