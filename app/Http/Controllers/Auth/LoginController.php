<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\LogsAudit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Login berbasis username (bukan email), setara auth session sederhana
// di portum.py -- dipertahankan supaya user tidak perlu diberi email
// palsu hanya untuk login.
class LoginController extends Controller
{
    use LogsAudit;

    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
        }

        $request->session()->regenerate();
        auth()->user()->update(['last_login' => now()]);
        $this->audit('LOGIN', 'Auth', 'User', auth()->id(), 'Login ke sistem');

        if (auth()->user()->must_change_pwd) {
            return redirect()->route('password.force-change');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $this->audit('LOGOUT', 'Auth', 'User', auth()->id(), 'Logout dari sistem');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function forceChangeForm()
    {
        return view('auth.force-change');
    }

    public function forceChange(Request $request)
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $user = auth()->user();
        $user->update([
            'password' => bcrypt($request->password),
            'must_change_pwd' => false,
        ]);
        $this->audit('UPDATE', 'Auth', 'User', $user->id, 'Mengganti password wajib saat login pertama');
        return redirect()->route('dashboard')->with('status', 'Password berhasil diganti.');
    }
}
