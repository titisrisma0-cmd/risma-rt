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
            'jenis_kelamin' => ['required', 'in:L,P'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'no_kk.unique' => 'Nomor Kartu Keluarga sudah digunakan.',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
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
        // Ensure profile is created/updated. The User model may auto-create
        // a minimal profile in the created event, so update it if present.
        // Set status to 'pending' for verification by admin RW
        $profileData = [
            'role_id' => $roleWarga ? $roleWarga->id : null,
            'no_kk' => $data['no_kk'],
            'no_hp' => $data['no_hp'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'nama_lengkap' => $data['name'],
            'status' => 'pending'
        ];

        $existingProfile = UserProfile::where('user_id', $user->id)->first();
        if ($existingProfile) {
            $existingProfile->update($profileData);
        } else {
            $profileData = array_merge(['user_id' => $user->id], $profileData);
            UserProfile::create($profileData);
        }
        
        return $user;
    }
    
    // Override showRegistrationForm untuk redirect ke login
    public function showRegistrationForm()
    {
        return redirect('/login#register');
    }
    
    // Override register untuk tetap bisa register via AJAX
    // Warga tidak langsung login, tunggu verifikasi admin RW
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        
        event(new \Illuminate\Auth\Events\Registered($user = $this->create($request->all())));
        
        // Jangan auto-login, tunggu verifikasi admin RW
        // $this->guard()->login($user);
        
        return redirect('/login')->with('success', 'Pendaftaran berhasil! Silakan tunggu verifikasi dari Admin RW sebelum login.');
    }
}
