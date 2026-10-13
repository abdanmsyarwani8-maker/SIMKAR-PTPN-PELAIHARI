@extends('layouts.app')
@section('title', 'Edit ' . $employee->nama)
@section('page-title', 'Edit Data Karyawan')

@section('content')
<div class="mb-3">
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-light rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square text-success"></i>
        Edit Data: <strong>{{ $employee->nama }}</strong>
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

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')
            @include('employees._form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-hijau rounded-pill px-4">
                    <i class="bi bi-check-lg me-1"></i> Perbarui Data
                </button>
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-light rounded-pill px-4">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
