<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        $profileId = optional($user->profile)->id;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nik' => ['nullable', 'string', 'max:20', Rule::unique('user_profiles', 'nik')->ignore($profileId)],
            'no_kk' => ['nullable', 'string', 'max:20', Rule::unique('user_profiles', 'no_kk')->ignore($profileId)],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:15',
            'pekerjaan' => 'nullable|string|max:255',
            'status_perkawinan' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'kelurahan' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'nama_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ]);

        DB::transaction(function () use ($request, $user) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            $profileData = [
                'role_id' => $user->role_id,
                'nama_lengkap' => $request->name,
                'no_kk' => $request->no_kk,
                'nik' => $request->nik,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'rt' => $request->rt,
                'rw' => $request->rw,
                'kelurahan' => $request->kelurahan,
                'kecamatan' => $request->kecamatan,
                'kota' => $request->kota,
                'no_hp' => $request->no_hp,
                'pekerjaan' => $request->pekerjaan,
                'status_perkawinan' => $request->status_perkawinan,
                'pendidikan' => $request->pendidikan,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
            ];

            if ($user->profile) {
                $user->profile->update($profileData);
            } else {
                $user->profile()->create($profileData + [
                    'status' => 'aktif',
                ]);
            }
        });
        
        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui!');
    }
    
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        $user = Auth::user();
        $file = $request->file('avatar');
        $imageData = file_get_contents($file->getRealPath());
        $base64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode($imageData);
        
        $profile = $user->profile;
        if ($profile) {
            $profile->update(['foto_base64' => $base64]);
        } else {
            $user->profile()->create([
                'nama_lengkap' => $user->name,
                'foto_base64' => $base64,
            ]);
        }
        
        return redirect()->route('profile.index')->with('success', 'Foto profil berhasil diubah!');
    }
    
    public function updateKependudukan(Request $request)
    {
        $user = Auth::user();
        $profileId = optional($user->profile)->id;
        
        $request->validate([
            'nik' => ['nullable', 'string', 'max:20', Rule::unique('user_profiles', 'nik')->ignore($profileId)],
            'no_kk' => ['nullable', 'string', 'max:20', Rule::unique('user_profiles', 'no_kk')->ignore($profileId)],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:15',
            'pekerjaan' => 'nullable|string|max:255',
            'status_perkawinan' => 'nullable|string|max:50',
            'pendidikan' => 'nullable|string|max:50',
        ]);

        $profileData = $request->only([
            'nik', 'no_kk', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'agama', 'no_hp', 'pekerjaan',
            'status_perkawinan', 'pendidikan'
        ]);

        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            $user->profile()->create($profileData + [
                'role_id' => $user->role_id,
                'nama_lengkap' => $user->name,
                'status' => 'aktif',
            ]);
        }
        
        return redirect()->route('profile.index')->with('success', 'Data kependudukan berhasil diperbarui!');
    }
    
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah!']);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('profile.index')->with('success', 'Password berhasil diubah!');
    }
    
    public function destroy(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required',
        ]);
        
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah!']);
        }
        
        $user->delete();
        
        return redirect('/')->with('success', 'Akun berhasil dihapus.');
    }
}
