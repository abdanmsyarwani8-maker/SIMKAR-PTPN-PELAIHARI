<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini yang Anda masukkan salah atau tidak sesuai dengan kata sandi akun Anda saat ini.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok. Pastikan isian kolom kata sandi baru dan ulangi kata sandi terisi sama persis.',
            'password.min' => 'Kata sandi baru terlalu pendek. Minimal harus 8 karakter.',
            'password.letters' => 'Kata sandi baru harus mengandung setidaknya 1 huruf.',
            'password.numbers' => 'Kata sandi baru harus mengandung setidaknya 1 angka.',
            'password.mixed' => 'Kata sandi baru harus mengandung kombinasi huruf besar dan huruf kecil.',
            'password.symbols' => 'Kata sandi baru harus mengandung setidaknya 1 karakter simbol khusus.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
