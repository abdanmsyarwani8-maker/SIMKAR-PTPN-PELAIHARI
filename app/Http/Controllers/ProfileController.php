<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Upload atau hapus foto profil pengguna.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ], [
                'avatar.image' => 'File harus berupa gambar.',
                'avatar.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
                'avatar.max' => 'Ukuran foto maksimal 2 MB.',
            ]);

            $user = $request->user();

            if (! Schema::hasColumn('users', 'avatar')) {
                return Redirect::route('profile.edit')
                    ->with('error', 'Kolom `avatar` belum ada di tabel `users` database hosting. Silakan jalankan migrasi database.');
            }

            $folderName = is_dir(public_path('Images')) ? 'Images' : 'images';
            $avatarDir = public_path($folderName.'/avatars');

            if ($request->boolean('remove_avatar')) {
                // Hapus avatar lama
                if ($user->avatar) {
                    $oldPath = public_path($user->avatar);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                    try {
                        Storage::disk('public')->delete($user->avatar);
                    } catch (\Throwable) {
                    }
                }
                $user->avatar = null;
                $user->save();

                return Redirect::route('profile.edit')->with('status', 'avatar-removed');
            }

            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                // Hapus avatar lama jika ada
                if ($user->avatar) {
                    $oldPath = public_path($user->avatar);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                    try {
                        Storage::disk('public')->delete($user->avatar);
                    } catch (\Throwable) {
                    }
                }

                if (! file_exists($avatarDir)) {
                    @mkdir($avatarDir, 0755, true);
                }

                $file = $request->file('avatar');
                $filename = 'avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
                $file->move($avatarDir, $filename);

                $user->avatar = $folderName.'/avatars/'.$filename;
                $user->save();

                return Redirect::route('profile.edit')->with('status', 'avatar-updated');
            }

            return Redirect::route('profile.edit')->with('status', 'avatar-no-change');
        } catch (ValidationException $ve) {
            throw $ve;
        } catch (\Throwable $e) {
            Log::error('Error saat update avatar: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return Redirect::route('profile.edit')
                ->with('error', 'Gagal memperbarui foto profil: '.$e->getMessage().'. Pastikan folder `images` di `public_html` memiliki izin tulis (0755/0775).');
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Hapus avatar saat akun dihapus
        if ($user->avatar) {
            $oldPath = public_path($user->avatar);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            try {
                Storage::disk('public')->delete($user->avatar);
            } catch (\Throwable) {
            }
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
