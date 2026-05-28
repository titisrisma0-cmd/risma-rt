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
    
    // Warga baru hanya boleh masuk setelah diverifikasi Admin RT.
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        
        if ($this->attemptLogin($request)) {
            $user = $this->guard()->user()->loadMissing('profile');

            if ($user->hasRole('warga') && optional($user->profile)->status !== 'aktif') {
                $this->guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only($this->username(), 'remember'))
                    ->with('error', 'Akun Anda masih menunggu verifikasi dari Admin RT. Silakan hubungi admin untuk informasi lebih lanjut.');
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
        if ($user->hasRole('admin')) {
            return redirect('/dashboard');
        } elseif ($user->hasRole('bendahara')) {
            return redirect('/dashboard');
        } else {
            return redirect('/dashboard');
        }
    }
}
