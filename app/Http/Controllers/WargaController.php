<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class WargaController extends Controller
{
    public function index(Request $request)
    {
        $roleWarga = Role::where('nama_role', 'warga')->first();
        
        if (!$roleWarga) {
            return redirect()->back()->with('error', 'Role warga tidak ditemukan');
        }
        
        $query = User::where('role_id', $roleWarga->id)->with('profile');
        
        // Filter berdasarkan Nama
        if ($request->filled('nama')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->nama . '%')
                  ->orWhereHas('profile', function($p) use ($request) {
                      $p->where('nama_lengkap', 'like', '%' . $request->nama . '%');
                  });
            });
        }
        
        // Filter berdasarkan Email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        
        // Filter berdasarkan NIK
        if ($request->filled('nik')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('nik', 'like', '%' . $request->nik . '%');
            });
        }
        
        // Filter berdasarkan No KK
        if ($request->filled('no_kk')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('no_kk', 'like', '%' . $request->no_kk . '%');
            });
        }
        
        // Filter berdasarkan No HP
        if ($request->filled('no_hp')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('no_hp', 'like', '%' . $request->no_hp . '%');
            });
        }
        
        // Filter berdasarkan Status
        if ($request->filled('status')) {
            $query->whereHas('profile', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        
        $warga = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('warga.index', compact('warga'));
    }
    
    public function create()
    {
        return view('warga.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'email' => 'required|email|unique:users',
            'nik' => 'required|unique:user_profiles',
            'no_kk' => 'required|unique:user_profiles',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);
        
        $roleWarga = Role::where('nama_role', 'warga')->first();
        
        if (!$roleWarga) {
            return redirect()->back()->with('error', 'Role warga tidak ditemukan. Silakan jalankan seeder terlebih dahulu.');
        }
        
        $user = User::create([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make('password123'),
            'role_id' => $roleWarga->id
        ]);
        
        UserProfile::create([
            'user_id' => $user->id,
            'role_id' => $roleWarga->id,
            'no_kk' => $request->no_kk,
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'pekerjaan' => $request->pekerjaan,
            'status' => 'aktif'
        ]);
        
        return redirect()->route('warga.index')->with('success', 'Warga berhasil ditambahkan');
    }
    
    public function edit($id)
    {
        $warga = User::with('profile')->findOrFail($id);
        return view('warga.edit', compact('warga'));
    }

    public function show($id)
    {
        return redirect()->route('warga.edit', $id);
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'nama_lengkap' => 'required',
            'nik' => 'required|unique:user_profiles,nik,' . ($user->profile ? $user->profile->id : 'NULL'),
            'no_kk' => 'required|unique:user_profiles,no_kk,' . ($user->profile ? $user->profile->id : 'NULL'),
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
        ]);
        
        $user->update(['name' => $request->nama_lengkap]);
        
        if ($user->profile) {
            $user->profile->update([
                'no_kk' => $request->no_kk,
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pekerjaan' => $request->pekerjaan,
                'status' => $request->status
            ]);
        }
        
        return redirect()->route('warga.index')->with('success', 'Data warga berhasil diupdate');
    }
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->profile) {
            $user->profile()->delete();
        }
        $user->delete();
        
        return redirect()->route('warga.index')->with('success', 'Warga berhasil dihapus');
    }
    
    public function verify($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->profile) {
            $user->profile->update(['status' => 'aktif']);
        }
        
        return redirect()->route('warga.index')->with('success', 'Warga ' . $user->name . ' berhasil diverifikasi dan dapat login ke aplikasi.');
    }
}
