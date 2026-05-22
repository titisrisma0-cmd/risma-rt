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