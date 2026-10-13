@extends('layouts.app')
@section('title', 'Detail Pengguna')
@section('page-title', 'Detail Pengguna')

@section('content')
<div class="mb-3">
    <a href="{{ route('users.index') }}" class="btn btn-sm btn-light rounded-pill">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>
<div class="card" style="max-width:500px">
    <div class="card-header"><i class="bi bi-person-circle me-2 text-success"></i>Informasi Pengguna</div>
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-4">
            <div style="width:56px;height:56px;border-radius:14px;background:var(--hijau-light);
                        display:flex;align-items:center;justify-content:center;
                        font-size:24px;font-weight:800;color:var(--hijau-tua)">
                {{ strtoupper(substr($user->name,0,1)) }}
            </div>
            <div>
                <h5 class="mb-0 fw-700">{{ $user->name }}</h5>
                <span class="badge {{ $user->isAdmin()?'badge-karpim':'badge-karpel' }}">
                    {{ $user->role_label }}
                </span>
            </div>
        </div>
        <table class="table table-borderless" style="font-size:13.5px">
            <tr><td class="text-muted" style="width:40%">Email</td><td>{{ $user->email }}</td></tr>
            <tr><td class="text-muted">Status</td>
                <td>
                    @if($user->is_active)
                    <span class="badge" style="background:#e8f5e9;color:#2e7d32">Aktif</span>
                    @else
                    <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </td>
            </tr>
            <tr><td class="text-muted">Dibuat</td><td>{{ $user->created_at->format('d M Y H:i') }}</td></tr>
            <tr><td class="text-muted">Diperbarui</td><td>{{ $user->updated_at->format('d M Y H:i') }}</td></tr>
        </table>
        <div class="d-flex gap-2 mt-2">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-hijau btn-sm rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                Kembali ke Daftar
            </a>
        </div>
    </div>
</div>
@endsection
