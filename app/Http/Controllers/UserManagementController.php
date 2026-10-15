<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => 'required|in:admin,user',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Kata sandi wajib diisi.',
            'role.required' => 'Peran wajib dipilih.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        return view('users.edit', ['user' => $pengguna]);
    }

    public function update(Request $request, User $pengguna)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($pengguna->id)],
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $pengguna->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $pengguna->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang aktif.');
        }
        $pengguna->delete();

        return redirect()->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function show(User $pengguna)
    {
        return view('users.show', ['user' => $pengguna]);
    }
}
