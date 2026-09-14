<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil user yang sedang login.
     */
    public function showProfile()
    {
        $user = Auth::user();
        $user->loadMissing('role');

        return view('profile.show', compact('user'));
    }

    /**
     * Tampilkan form ubah password.
     */
    public function showChangePassword()
    {
        $user = Auth::user();
        $user->loadMissing('role');

        return view('profile.change-password', compact('user'));
    }

    /**
     * Proses ubah password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama'          => ['required', 'string'],
            'password_baru'          => ['required', 'string', 'min:8', 'confirmed', Password::min(8)],
        ], [
            'password_lama.required'          => 'Password lama wajib diisi.',
            'password_baru.required'          => 'Password baru wajib diisi.',
            'password_baru.min'               => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'         => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        // Cek apakah password lama cocok
        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
        }

        // Pastikan password baru tidak sama dengan password lama
        if (Hash::check($request->password_baru, $user->password)) {
            return back()->withErrors(['password_baru' => 'Password baru tidak boleh sama dengan password lama.'])->withInput();
        }

        $user->update([
            'password'         => Hash::make($request->password_baru),
            'must_change_pwd'  => false,
        ]);

        return redirect()->route('profile.change-password')
            ->with('status', 'Password berhasil diubah. Silakan login menggunakan password baru Anda.');
    }
}
