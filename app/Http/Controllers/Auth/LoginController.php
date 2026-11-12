<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    // Warga baru hanya boleh masuk setelah diverifikasi Admin RW.
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        
        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user()->loadMissing('profile', 'role');

            if ($user->hasRole('warga') && optional($user->profile)->status !== 'aktif') {
                $status = optional($user->profile)->status;
                $catatan = optional($user->profile)->catatan_verifikasi;
                $this->guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($status === 'ditolak') {
                    $pesan = 'Pendaftaran akun Anda ditolak oleh Ketua RT' . ($catatan ? ': ' . $catatan : '. Silakan hubungi Ketua RT Anda.');
                } elseif ($status === 'nonaktif') {
                    $pesan = 'Akun Anda dinonaktifkan. Silakan hubungi Ketua RT Anda.';
                } else {
                    $pesan = 'Akun Anda masih menunggu verifikasi dari Ketua RT. Silakan tunggu konfirmasi.';
                }

                return back()->withInput($request->only($this->username(), 'remember'))
                    ->with('error', $pesan);
            }

            if ($user->isAdminRt() && optional($user->profile)->status === 'nonaktif') {
                $this->guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only($this->username(), 'remember'))
                    ->with('error', 'Akun Ketua RT Anda telah dinonaktifkan oleh Admin RW. Silakan hubungi pengurus RW.');
            }

            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    
    protected function authenticated($request, $user)
    {
        // Redirect berdasarkan role
        if ($user->hasRole('admin') || $user->hasRole('rw') || $user->hasRole('rt')) {
            return redirect()->intended('/dashboard');
        } elseif ($user->hasRole('bendahara')) {
            return redirect()->intended('/dashboard');
        } else {
            return redirect()->intended('/dashboard');
        }
    }
}
