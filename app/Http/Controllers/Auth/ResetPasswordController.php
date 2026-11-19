<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | Controller ini mengelola permintaan penyetelan ulang kata sandi.
    | Setelah kata sandi berhasil direset, pengguna diarahkan ke halaman login
    | dengan pesan sukses "Kata sandi berhasil diubah, silakan masuk".
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * Reset the given user's password.
     * Tidak melakukan auto-login agar pengguna diarahkan langsung ke halaman login.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Respon setelah kata sandi berhasil direset.
     * Arahkan ke halaman login dengan pesan sukses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        if ($this->guard()->check()) {
            $this->guard()->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $pesanSukses = 'Kata sandi berhasil diubah, silakan masuk';

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $pesanSukses], 200);
        }

        return redirect()->route('login')
            ->with('status', $pesanSukses)
            ->with('success', $pesanSukses);
    }
}
