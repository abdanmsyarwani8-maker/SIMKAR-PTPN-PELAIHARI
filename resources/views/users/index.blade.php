@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-700" style="color:var(--hijau-tua)">Manajemen Pengguna</h5>
        <div style="font-size:12.5px;color:#6b7c70">Total: {{ $users->total() }} pengguna terdaftar</div>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-hijau btn-sm rounded-pill px-3">
        <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
    </a>
</div>

<div class="card">
    @if($users->isEmpty())
    <div class="empty-state">
        <i class="bi bi-people"></i>
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
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light rounded-pill" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light rounded-pill text-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
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
