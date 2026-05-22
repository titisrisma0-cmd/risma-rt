<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'no_kk' => ['required', 'string', 'max:20', 'unique:user_profiles,no_kk'],
            'no_hp' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'no_kk.unique' => 'Nomor Kartu Keluarga sudah digunakan.',
        ]);
    }

    protected function create(array $data)
    {
        $roleWarga = Role::where('nama_role', 'warga')->first();
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $roleWarga ? $roleWarga->id : null,
        ]);
        
        // Create profile
        UserProfile::create([
            'user_id' => $user->id,
            'role_id' => $roleWarga ? $roleWarga->id : null,
            'no_kk' => $data['no_kk'],
            'no_hp' => $data['no_hp'],
            'nama_lengkap' => $data['name'],
            'status' => 'aktif'
        ]);
        
        return $user;
    }
    
    // Override showRegistrationForm untuk redirect ke login
    public function showRegistrationForm()
    {
        return redirect('/login#register');
    }
    
    // Override register untuk tetap bisa register via AJAX
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        
        event(new \Illuminate\Auth\Events\Registered($user = $this->create($request->all())));
        
        $this->guard()->login($user);
        
        if ($response = $this->registered($request, $user)) {
            return $response;
        }
        
        return redirect($this->redirectPath())->with('success', 'Selamat datang! Akun Anda berhasil dibuat.');
    }
}