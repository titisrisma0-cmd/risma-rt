<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'bio' => 'nullable|string',
        ]);
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'rt' => $request->rt,
            'rw' => $request->rw,
            'bio' => $request->bio,
        ]);
        
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
        
        $request->validate([
            'nik' => 'nullable|string|max:16',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string',
            'pekerjaan' => 'nullable|string',
            'status_perkawinan' => 'nullable|string',
        ]);
        
        $user->update($request->only([
            'nik', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'agama', 'pekerjaan', 'status_perkawinan'
        ]));
        
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