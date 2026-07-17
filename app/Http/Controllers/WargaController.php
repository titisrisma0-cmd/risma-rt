<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Warga;
use App\Models\UserProfile;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Kas;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class WargaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Warga::class);
        $roleWarga = Role::where('nama_role', 'warga')->first();
        
        if (!$roleWarga) {
            return redirect()->back()->with('error', 'Role warga tidak ditemukan');
        }
        
        $query = Warga::where('role_id', $roleWarga->id)->with(['profile.wilayahRt']);
        
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
        
        $warga = $query
            ->orderBy(UserProfile::select('no_kk')->whereColumn('user_profiles.user_id', 'users.id'))
            ->orderBy('id')
            ->paginate(15);

        $familyCounts = UserProfile::whereIn('no_kk', $warga->getCollection()
                ->pluck('profile.no_kk')->filter()->unique())
            ->select('no_kk', DB::raw('COUNT(*) as total'))
            ->groupBy('no_kk')
            ->pluck('total', 'no_kk');
        
        return view('warga.index', compact('warga', 'familyCounts'));
    }
    
    public function create()
    {
        $this->authorize('create', Warga::class);

        return view('warga.create', ['rts' => $this->availableRts()]);
    }
    
    public function store(Request $request)
    {
        $this->authorize('create', Warga::class);

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
            'rt_id' => Auth::user()->isAdminRw() ? 'required|exists:rts,id' : 'nullable',
        ]);
        
        $roleWarga = Role::where('nama_role', 'warga')->first();
        
        if (!$roleWarga) {
            return redirect()->back()->with('error', 'Role warga tidak ditemukan. Silakan jalankan seeder terlebih dahulu.');
        }
        
        $rtId = $this->resolveRtId($request);

        $user = Warga::create([
            'name' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make('password123'),
            'role_id' => $roleWarga->id,
            'rt_id' => $rtId,
        ]);
        
        UserProfile::updateOrCreate(['user_id' => $user->id], [
            'user_id' => $user->id,
            'role_id' => $roleWarga->id,
            'rt_id' => $rtId,
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
        $warga = $this->findWargaForAuthorization($id);
        $this->authorize('update', $warga);

        return view('warga.edit', ['warga' => $warga, 'rts' => $this->availableRts()]);
    }

    public function show($id)
    {
        $warga = $this->findWargaForAuthorization($id);
        $this->authorize('view', $warga);

        return redirect()->route('warga.edit', $id);
    }
    
    public function update(Request $request, $id)
    {
        $user = $this->findWargaForAuthorization($id);
        $this->authorize('update', $user);
        
        $request->validate([
            'nama_lengkap' => 'required',
            'nik' => 'required|unique:user_profiles,nik,' . ($user->profile ? $user->profile->id : 'NULL'),
            'no_kk' => 'required|unique:user_profiles,no_kk,' . ($user->profile ? $user->profile->id : 'NULL'),
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'rt_id' => Auth::user()->isAdminRw() ? 'required|exists:rts,id' : 'nullable',
        ]);
        
        $rtId = $this->resolveRtId($request);
        $user->update(['name' => $request->nama_lengkap, 'rt_id' => $rtId]);
        
        if ($user->profile) {
            $user->profile->update([
                'no_kk' => $request->no_kk,
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'rt_id' => $rtId,
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
        $roleWarga = Role::where('nama_role', 'warga')->first();
        $user = $this->findWargaForAuthorization($id);
        $this->authorize('delete', $user);

        if (!$roleWarga || $user->role_id !== $roleWarga->id) {
            return redirect()->route('warga.index')->with('error', 'Data yang dipilih bukan warga.');
        }

        DB::beginTransaction();

        try {
            $pembayaranIds = Pembayaran::where('user_id', $user->id)->pluck('id');

            if ($pembayaranIds->isNotEmpty()) {
                $buktiTransfer = Pembayaran::whereIn('id', $pembayaranIds)
                    ->whereNotNull('bukti_transfer')
                    ->pluck('bukti_transfer');

                Kas::whereIn('pembayaran_id', $pembayaranIds)->delete();
                Pembayaran::whereIn('id', $pembayaranIds)->delete();

                foreach ($buktiTransfer as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

            Notifikasi::where('user_id', $user->id)
                ->orWhere('from_user_id', $user->id)
                ->delete();

            $user->profile()->delete();
            $user->delete();

            DB::commit();

            return redirect()->route('warga.index')->with('success', 'Warga berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('warga.index')->with('error', 'Gagal menghapus warga: ' . $e->getMessage());
        }
    }
    
    public function verify($id)
    {
        $user = $this->findWargaForAuthorization($id);
        $this->authorize('update', $user);
        
        if ($user->profile) {
            $user->profile->update(['status' => 'aktif']);
        }
        
        return redirect()->route('warga.index')->with('success', 'Warga ' . $user->name . ' berhasil diverifikasi dan dapat login ke aplikasi.');
    }

    private function findWargaForAuthorization($id): Warga
    {
        $roleWarga = Role::where('nama_role', 'warga')->firstOrFail();

        return Warga::withoutGlobalScopes()
            ->with('profile')
            ->where('role_id', $roleWarga->id)
            ->findOrFail($id);
    }

    private function resolveRtId(Request $request): int
    {
        $user = Auth::user();

        if ($user->isAdminRt()) {
            abort_if($user->managedRtId() === null, 403, 'Admin RT belum ditugaskan ke RT.');

            return $user->managedRtId();
        }

        return (int) $request->input('rt_id');
    }

    private function availableRts()
    {
        if (Auth::user()->isAdminRt()) {
            return Rt::whereKey(Auth::user()->managedRtId())->get();
        }

        return Rt::with('rw')->orderBy('rw_id')->orderBy('kode_rt')->get();
    }
}
