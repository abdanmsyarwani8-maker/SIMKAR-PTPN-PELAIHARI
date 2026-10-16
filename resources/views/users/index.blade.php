@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div style="font-size:13px;color:#55695c;font-weight:500;">
        Total: <strong class="text-dark">{{ $users->total() }}</strong> pengguna terdaftar
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <a href="{{ route('users.export') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
        </a>
        <a href="{{ route('users.create') }}" class="btn btn-hijau btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
        </a>
    </div>
</div>

<div class="card">
    @if($users->isEmpty())
    <div class="empty-state">
        <i class="bi bi-people-fill"></i>
        <h5>Belum ada pengguna</h5>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-simkar mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Dibuat</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="text-muted" style="font-size:12px">{{ $users->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--hijau-light);
                                        display:flex;align-items:center;justify-content:center;
                                        font-weight:700;color:var(--hijau-tua);font-size:13px;flex-shrink:0">
                                {{ strtoupper(substr($user->name,0,1)) }}
                            </div>
                            <span class="fw-600" style="font-size:13.5px">{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                            <span class="badge" style="background:var(--hijau-light);color:var(--hijau-tua);font-size:10px">Anda</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size:13px">{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->isAdmin()?'badge-karpim':'badge-karpel' }}" style="font-size:11px">
                            <i class="bi bi-{{ $user->isAdmin()?'shield-fill':'person-fill' }} me-1"></i>
                            {{ $user->role_label }}
                        </span>
                    </td>
                    <td>
                        @if($user->is_active)
                        <span class="badge" style="background:#e8f5e9;color:#2e7d32;font-size:11px">
                            <i class="bi bi-circle-fill me-1" style="font-size:7px"></i>Aktif
                        </span>
                        @else
                        <span class="badge bg-secondary" style="font-size:11px">Nonaktif</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#6b7c70">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('users.edit', $user) }}" class="btn-action" title="Edit Pengguna">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline m-0 p-0"
                                  onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-action-danger" title="Hapus Pengguna">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-top">
        {{ $users->links('vendor.pagination.bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
