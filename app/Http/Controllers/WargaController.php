<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\KartuKeluarga;
use App\Models\MutasiWarga;
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
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $query = UserProfile::with(['user', 'wilayahRt', 'kartuKeluarga'])
            ->whereNotIn('status', ['pindah', 'meninggal']);

        // Filter RT untuk Ketua RT vs Admin RW
        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $query->where('rt_id', $rtId);
        } elseif ($request->filled('rt_id')) {
            $query->where('rt_id', (int) $request->rt_id);
        }

        // Filter berdasarkan Nama
        if ($request->filled('nama')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->nama . '%')
                  ->orWhereHas('user', fn ($sub) => $sub->where('name', 'like', '%' . $request->nama . '%'));
            });
        }

        // Filter berdasarkan Email
        if ($request->filled('email')) {
            $query->whereHas('user', fn ($sub) => $sub->where('email', 'like', '%' . $request->email . '%'));
        }

        // Filter berdasarkan NIK
        if ($request->filled('nik')) {
            $query->where('nik', 'like', '%' . $request->nik . '%');
        }

        // Filter berdasarkan No KK
        if ($request->filled('no_kk')) {
            $query->where('no_kk', 'like', '%' . $request->no_kk . '%');
        }

        // Filter berdasarkan No HP
        if ($request->filled('no_hp')) {
            $query->where('no_hp', 'like', '%' . $request->no_hp . '%');
        }

        // Filter status kependudukan / akun
        if ($request->filled('status')) {
            if ($request->status === 'belum_ada_akun') {
                $query->whereNull('user_id');
            } elseif ($request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status === 'aktif') {
                $query->where('status', 'aktif')->whereNotNull('user_id');
            } else {
                $query->where('status', $request->status);
            }
        }

        $warga = $query->orderBy('rt_id')
            ->orderBy('no_kk')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        $familyCounts = UserProfile::whereIn('no_kk', $warga->getCollection()->pluck('no_kk')->filter()->unique())
            ->whereNotIn('status', ['pindah', 'meninggal'])
            ->select('no_kk', DB::raw('COUNT(*) as total'))
            ->groupBy('no_kk')
            ->pluck('total', 'no_kk');

        $rts = Rt::activeRt()->orderBy('kode_rt')->get();

        return view('warga.index', compact('warga', 'familyCounts', 'rts'));
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
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'nik' => 'required|string|digits:16|unique:user_profiles,nik',
            'no_kk' => 'required|string|digits:16',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string|max:500',
            'no_hp' => 'required|string|max:20',
            'rt_id' => Auth::user()->isAdminRw() ? 'required|exists:rts,id' : 'nullable',
            'siap_ronda' => 'nullable|boolean',
            'alasan_pengecualian_ronda' => 'nullable|string|max:255',
        ], [
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
            'no_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);
        
        $roleWarga = Role::where('nama_role', 'warga')->first();
        $rtId = $this->resolveRtId($request);

        DB::transaction(function () use ($request, $roleWarga, $rtId) {
            $user = null;
            if ($request->filled('email')) {
                $user = Warga::create([
                    'name' => $request->nama_lengkap,
                    'email' => $request->email,
                    'password' => Hash::make('password123'),
                    'role_id' => $roleWarga ? $roleWarga->id : null,
                    'rt_id' => $rtId,
                    'jenis_kelamin' => $request->jenis_kelamin,
                ]);
            }

            // Cari atau hubungkan Kartu Keluarga yang ada
            $kk = KartuKeluarga::firstOrCreate(
                ['no_kk' => $request->no_kk],
                [
                    'rt_id' => $rtId,
                    'alamat' => $request->alamat,
                    'status_rumah' => 'tetap',
                    'kepala_keluarga_user_id' => $user ? $user->id : null,
                ]
            );

            UserProfile::create([
                'user_id' => $user ? $user->id : null,
                'kartu_keluarga_id' => $kk->id,
                'role_id' => $roleWarga ? $roleWarga->id : null,
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
                'status_hubungan_keluarga' => $request->status_hubungan_keluarga ?? 'Anggota',
                'status' => 'aktif',
                'siap_ronda' => $request->has('siap_ronda') ? (bool) $request->siap_ronda : ($request->jenis_kelamin === 'L'),
                'alasan_pengecualian_ronda' => $request->alasan_pengecualian_ronda,
            ]);

            AuditLog::record(
                action: 'tambah_warga',
                entityType: 'user_profile',
                entityId: $kk->id,
                deskripsi: "Penambahan data kependudukan warga {$request->nama_lengkap} (NIK: {$request->nik}) oleh " . Auth::user()->name,
                rtId: $rtId,
                userId: Auth::id()
            );
        });
        
        return redirect()->route('warga.index')->with('success', 'Warga berhasil ditambahkan ke data kependudukan.');
    }
    
    public function edit($id)
    {
        $warga = $this->findWargaForAuthorization($id);
        if ($warga instanceof User) {
            $this->authorize('update', $warga);
        }

        return view('warga.edit', ['warga' => $warga, 'rts' => $this->availableRts()]);
    }

    public function show($id)
    {
        return redirect()->route('warga.edit', $id);
    }
    
    public function update(Request $request, $id)
    {
        $entity = $this->findWargaForAuthorization($id);
        $authUser = Auth::user();

        if ($entity instanceof User) {
            $this->authorize('update', $entity);
            $profile = $entity->profile;
            $profileId = $profile ? $profile->id : 'NULL';
        } else {
            $profile = $entity;
            if ($authUser->isAdminRt() && (int) $profile->rt_id !== (int) $authUser->managedRtId()) {
                abort(403);
            }
            $profileId = $profile->id;
        }
        
        $request->validate([
            'nama_lengkap' => 'required',
            'nik' => 'required|digits:16|unique:user_profiles,nik,' . $profileId,
            'no_kk' => 'required|digits:16',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required',
            'no_hp' => 'required',
            'rt_id' => $authUser->isAdminRw() ? 'required|exists:rts,id' : 'nullable',
        ]);
        
        $rtId = $this->resolveRtId($request);
        if ($entity instanceof User) {
            $entity->update(['name' => $request->nama_lengkap, 'rt_id' => $rtId]);
        }
        
        if ($profile) {
            $profile->update([
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
                'status' => $request->status ?? $profile->status,
                'siap_ronda' => $request->has('siap_ronda') ? (bool) $request->siap_ronda : $profile->siap_ronda,
                'alasan_pengecualian_ronda' => $request->alasan_pengecualian_ronda ?? $profile->alasan_pengecualian_ronda,
            ]);
        }
        
        return redirect()->route('warga.index')->with('success', 'Data warga berhasil diupdate');
    }
    
    public function destroy($id)
    {
        $authUser = Auth::user();
        abort_unless($authUser->isAdminRt() || $authUser->isAdminRw(), 403);

        $user = User::find($id);
        $profile = null;

        if ($user) {
            $profile = $user->profile;
            if ($authUser->isAdminRt() && (int) optional($profile)->rt_id !== (int) $authUser->managedRtId()) {
                abort(403);
            }
        } else {
            $profile = UserProfile::find($id);
            if ($profile) {
                if ($authUser->isAdminRt() && (int) $profile->rt_id !== (int) $authUser->managedRtId()) {
                    abort(403);
                }
                $user = $profile->user;
            }
        }

        if (!$profile && !$user) {
            return redirect()->route('warga.index')->with('error', 'Data warga tidak ditemukan.');
        }

        DB::beginTransaction();

        try {
            if ($user) {
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
            }

            $nama = $profile ? $profile->nama_lengkap : ($user ? $user->name : 'Warga');
            $rtId = $profile ? $profile->rt_id : ($user ? $user->rt_id : null);

            if ($profile) {
                $profile->delete();
            }
            if ($user) {
                $user->delete();
            }

            AuditLog::record(
                action: 'hapus_warga',
                entityType: 'user_profile',
                entityId: $id,
                deskripsi: "Data kependudukan warga {$nama} dihapus oleh {$authUser->name}",
                rtId: $rtId,
                userId: $authUser->id
            );

            DB::commit();

            return redirect()->route('warga.index')->with('success', 'Warga ' . $nama . ' berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('warga.index')->with('error', 'Gagal menghapus warga: ' . $e->getMessage());
        }
    }
    
    public function verify($id)
    {
        $user = $this->findWargaForAuthorization($id);
        $this->authorize('update', $user);
        
        $profile = $user->profile;
        if ($profile) {
            // Hubungkan ke KartuKeluarga atau buat baru di RT domisili
            if ($profile->no_kk && $profile->rt_id) {
                $kk = KartuKeluarga::firstOrCreate(
                    ['no_kk' => $profile->no_kk],
                    [
                        'rt_id' => $profile->rt_id,
                        'alamat' => $profile->alamat ?: 'Wilayah RT ' . str_pad($profile->rt_id, 2, '0', STR_PAD_LEFT),
                        'status_rumah' => 'tetap',
                        'kepala_keluarga_user_id' => $user->id,
                    ]
                );
                $profile->kartu_keluarga_id = $kk->id;
            }
            $profile->status = 'aktif';
            $profile->verified_by = Auth::id();
            $profile->verified_at = now();
            $profile->save();
        }
        
        AuditLog::record(
            action: 'verifikasi_warga',
            entityType: 'user',
            entityId: $user->id,
            deskripsi: "Verifikasi pendaftaran warga {$user->name} disetujui",
            rtId: optional($profile)->rt_id,
            userId: Auth::id()
        );

        Notifikasi::create([
            'user_id' => $user->id,
            'from_user_id' => Auth::id(),
            'tipe' => 'akun',
            'judul' => 'Akun Terverifikasi',
            'pesan' => 'Selamat! Akun Anda telah diverifikasi oleh Pengurus RT dan sekarang Anda dapat login.',
            'dibaca' => false,
        ]);

        return redirect()->route('warga.index')->with('success', 'Warga ' . $user->name . ' berhasil diverifikasi dan terhubung ke data kependudukan.');
    }

    public function mutasi(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $activeTab = $request->input('tab', 'masuk');
        $myRtId = $user->isAdminRt() ? $user->managedRtId() : null;
        $selectedRt = null;

        if ($user->isAdminRt()) {
            $selectedRt = $user->managedRt ?? Rt::find($myRtId);
        } elseif ($request->filled('rt_id')) {
            $selectedRt = Rt::find($request->rt_id);
            $myRtId = $selectedRt ? $selectedRt->id : null;
        }

        // Tab 1: Mutasi Masuk
        $queryMasuk = MutasiWarga::with(['rt', 'creator'])->where('jenis_mutasi', 'masuk');
        if ($myRtId) {
            $queryMasuk->where('rt_id', $myRtId);
        }
        $mutasiMasukList = $queryMasuk->latest('tanggal_mutasi')->get();

        // Tab 2: Mutasi Keluar
        $queryKeluar = MutasiWarga::with(['rt', 'creator', 'profile'])->whereIn('jenis_mutasi', ['keluar', 'meninggal']);
        if ($myRtId) {
            $queryKeluar->where('rt_id', $myRtId);
        }
        $mutasiKeluarList = $queryKeluar->latest('tanggal_mutasi')->get();

        // Active warga & KK for dropdown in Mutasi Keluar
        $activeWargaForKeluar = collect();
        $activeKkForKeluar = collect();
        if ($myRtId) {
            $activeWargaForKeluar = UserProfile::where('rt_id', $myRtId)->where('status', 'aktif')->orderBy('nama_lengkap')->get();
            $activeKkForKeluar = KartuKeluarga::where('rt_id', $myRtId)->orderBy('no_kk')->get();
        } else {
            $activeWargaForKeluar = UserProfile::where('status', 'aktif')->orderBy('nama_lengkap')->get();
            $activeKkForKeluar = KartuKeluarga::orderBy('no_kk')->get();
        }

        // Tab 3: Mutasi Antar-RT
        $queryAntarRt = MutasiWarga::with(['rt', 'rtTujuan', 'creator', 'profile'])->where('jenis_mutasi', 'pindah_rt');
        if ($myRtId) {
            $queryAntarRt->where(function ($q) use ($myRtId) {
                $q->where('rt_id', $myRtId)->orWhere('rt_id_tujuan', $myRtId);
            });
        }
        $mutasiAntarRtList = $queryAntarRt->latest('tanggal_mutasi')->get();

        // Tab 4: Riwayat Mutasi (Semua riwayat mutasi dengan pagination & search)
        $queryRiwayat = MutasiWarga::with(['rt', 'rtTujuan', 'creator', 'profile', 'verifiedBy']);
        if ($myRtId) {
            $queryRiwayat->where(function ($q) use ($myRtId) {
                $q->where('rt_id', $myRtId)->orWhere('rt_id_tujuan', $myRtId);
            });
        }
        if ($request->filled('q')) {
            $search = '%' . trim($request->q) . '%';
            $queryRiwayat->where(function ($q) use ($search) {
                $q->where('nama_warga', 'like', $search)
                  ->orWhere('nik', 'like', $search)
                  ->orWhere('no_kk', 'like', $search)
                  ->orWhere('keterangan', 'like', $search);
            });
        }
        if ($request->filled('jenis')) {
            $queryRiwayat->where('jenis_mutasi', $request->jenis);
        }
        $mutasiRiwayatList = $queryRiwayat->latest('tanggal_mutasi')->paginate(15)->withQueryString();

        $rts = Rt::orderBy('kode_rt')->get();

        return view('warga.mutasi', compact(
            'user', 'activeTab', 'selectedRt', 'rts', 'myRtId',
            'mutasiMasukList', 'mutasiKeluarList', 'mutasiAntarRtList', 'mutasiRiwayatList',
            'activeWargaForKeluar', 'activeKkForKeluar'
        ));
    }

    public function storeMutasiMasuk(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : (int) $request->rt_id;
        abort_unless($rtId, 422, 'Wilayah RT harus valid.');

        $validated = $request->validate([
            'nama_kepala_keluarga' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16'],
            'no_kk' => ['required', 'string', 'digits:16'],
            'alamat_asal' => ['required', 'string', 'max:500'],
            'alamat_tujuan' => ['nullable', 'string', 'max:500'],
            'tanggal_mutasi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'dokumen_pendukung' => ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg', 'max:3072'],
        ], [
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'no_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
            'nama_kepala_keluarga.required' => 'Nama Kepala Keluarga wajib diisi.',
            'alamat_asal.required' => 'Alamat asal wajib diisi.',
        ]);

        $dokumenPath = null;
        if ($request->hasFile('dokumen_pendukung')) {
            $dokumenPath = $request->file('dokumen_pendukung')->store('mutasi_dokumen', 'public');
        }

        $mutasi = MutasiWarga::create([
            'rt_id' => $rtId,
            'nama_warga' => $validated['nama_kepala_keluarga'],
            'nama_kepala_keluarga' => $validated['nama_kepala_keluarga'],
            'nik' => $validated['nik'],
            'no_kk' => $validated['no_kk'],
            'alamat_asal' => $validated['alamat_asal'],
            'alamat_tujuan' => $validated['alamat_tujuan'] ?? 'Wilayah RT ' . str_pad($rtId, 2, '0', STR_PAD_LEFT),
            'jenis_mutasi' => 'masuk',
            'tanggal_mutasi' => $validated['tanggal_mutasi'],
            'keterangan' => $validated['keterangan'] ?? null,
            'dokumen_pendukung' => $dokumenPath,
            'status_verifikasi' => 'menunggu',
            'created_by' => $user->id,
        ]);

        AuditLog::record(
            action: 'mutasi_masuk_antrean',
            entityType: 'mutasi_warga',
            entityId: $mutasi->id,
            deskripsi: "Pendaftaran mutasi masuk: {$validated['nama_kepala_keluarga']} (KK: {$validated['no_kk']}) masuk ke antrean verifikasi RT {$rtId}",
            rtId: $rtId,
            userId: $user->id
        );

        return redirect()->route('warga.mutasi', ['tab' => 'masuk'])->with('success', 'Permohonan mutasi masuk berhasil didaftarkan dan menunggu persetujuan verifikasi.');
    }

    public function storeMutasiKeluar(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : (int) $request->rt_id;
        abort_unless($rtId, 422, 'Wilayah RT harus valid.');

        $validated = $request->validate([
            'tipe_keluar' => ['required', 'in:individu,sekeluarga'],
            'user_profile_id' => ['required_if:tipe_keluar,individu', 'nullable', 'exists:user_profiles,id'],
            'kartu_keluarga_id' => ['required_if:tipe_keluar,sekeluarga', 'nullable', 'exists:kartu_keluargas,id'],
            'alamat_tujuan' => ['required', 'string', 'max:500'],
            'tanggal_mutasi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'alamat_tujuan.required' => 'Alamat tujuan kepindahan wajib diisi.',
            'user_profile_id.required_if' => 'Warga yang pindah wajib dipilih.',
            'kartu_keluarga_id.required_if' => 'Kartu Keluarga yang pindah wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $rtId, $user) {
            if ($validated['tipe_keluar'] === 'sekeluarga') {
                $kk = KartuKeluarga::with('anggota')->findOrFail($validated['kartu_keluarga_id']);
                
                // Ubah status seluruh anggota menjadi pindah tanpa menghapus data
                foreach ($kk->anggota as $anggota) {
                    $anggota->update(['status' => 'pindah']);
                }

                MutasiWarga::create([
                    'rt_id' => $rtId,
                    'nama_warga' => $kk->nama_kepala_keluarga . ' (Sekeluarga)',
                    'nama_kepala_keluarga' => $kk->nama_kepala_keluarga,
                    'no_kk' => $kk->no_kk,
                    'alamat_asal' => $kk->alamat,
                    'alamat_tujuan' => $validated['alamat_tujuan'],
                    'jenis_mutasi' => 'keluar',
                    'tanggal_mutasi' => $validated['tanggal_mutasi'],
                    'keterangan' => "Mutasi sekeluarga keluar ke {$validated['alamat_tujuan']}. " . ($validated['keterangan'] ?: ''),
                    'status_verifikasi' => 'disetujui',
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                    'created_by' => $user->id,
                ]);

                AuditLog::record(
                    action: 'mutasi_keluar_sekeluarga',
                    entityType: 'kartu_keluarga',
                    entityId: $kk->id,
                    deskripsi: "Pencatatan mutasi sekeluarga KK {$kk->no_kk} keluar ke {$validated['alamat_tujuan']} oleh {$user->name}",
                    rtId: $rtId,
                    userId: $user->id
                );
            } else {
                $profile = UserProfile::findOrFail($validated['user_profile_id']);
                
                $profile->update([
                    'status' => 'pindah',
                    'kartu_keluarga_id' => null,
                ]);

                MutasiWarga::create([
                    'rt_id' => $rtId,
                    'user_profile_id' => $profile->id,
                    'nama_warga' => $profile->nama_lengkap,
                    'nik' => $profile->nik,
                    'no_kk' => $profile->no_kk,
                    'alamat_asal' => $profile->alamat,
                    'alamat_tujuan' => $validated['alamat_tujuan'],
                    'jenis_mutasi' => 'keluar',
                    'tanggal_mutasi' => $validated['tanggal_mutasi'],
                    'keterangan' => "Mutasi individu keluar ke {$validated['alamat_tujuan']}. " . ($validated['keterangan'] ?: ''),
                    'status_verifikasi' => 'disetujui',
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                    'created_by' => $user->id,
                ]);

                AuditLog::record(
                    action: 'mutasi_keluar_individu',
                    entityType: 'user_profile',
                    entityId: $profile->id,
                    deskripsi: "Pencatatan mutasi keluar warga {$profile->nama_lengkap} (NIK: {$profile->nik}) oleh {$user->name}",
                    rtId: $rtId,
                    userId: $user->id
                );
            }
        });

        return redirect()->route('warga.mutasi', ['tab' => 'keluar'])->with('success', 'Mutasi keluar berhasil dicatat tanpa menghapus data riwayat warga.');
    }

    public function storeMutasiAntarRt(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $rtAsalId = $user->isAdminRt() ? $user->managedRtId() : (int) $request->rt_id_asal;
        abort_unless($rtAsalId, 422, 'RT Asal harus valid.');

        $validated = $request->validate([
            'user_profile_id' => ['required', 'exists:user_profiles,id'],
            'rt_id_tujuan' => ['required', 'exists:rts,id'],
            'tanggal_mutasi' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'user_profile_id.required' => 'Pilih warga yang akan dipindahkan.',
            'rt_id_tujuan.required' => 'RT tujuan wajib dipilih.',
        ]);

        if ((int)$validated['rt_id_tujuan'] === (int)$rtAsalId) {
            return back()->with('error', 'RT tujuan tidak boleh sama dengan RT asal.');
        }

        $profile = UserProfile::findOrFail($validated['user_profile_id']);
        if ($user->isAdminRt()) {
            abort_unless((int)$profile->rt_id === (int)$rtAsalId, 403, 'Warga tidak berada di wilayah RT Anda.');
        }

        $mutasi = MutasiWarga::create([
            'rt_id' => $rtAsalId,
            'rt_id_tujuan' => $validated['rt_id_tujuan'],
            'user_profile_id' => $profile->id,
            'nama_warga' => $profile->nama_lengkap,
            'nik' => $profile->nik,
            'no_kk' => $profile->no_kk,
            'alamat_asal' => $profile->alamat,
            'alamat_tujuan' => 'Wilayah RT ' . str_pad($validated['rt_id_tujuan'], 2, '0', STR_PAD_LEFT),
            'jenis_mutasi' => 'pindah_rt',
            'tanggal_mutasi' => $validated['tanggal_mutasi'],
            'keterangan' => $validated['keterangan'] ?? "Permohonan perpindahan dari RT {$rtAsalId} ke RT {$validated['rt_id_tujuan']}",
            'status_verifikasi' => 'menunggu_tujuan',
            'created_by' => $user->id,
        ]);

        $targetAdmins = User::whereHas('role', fn ($r) => $r->where('nama_role', 'rt'))
            ->whereHas('profile', fn ($p) => $p->where('rt_id', (int) $validated['rt_id_tujuan']))
            ->get();

        foreach ($targetAdmins as $adm) {
            Notifikasi::create([
                'user_id' => $adm->id,
                'from_user_id' => $user->id,
                'tipe' => 'mutasi',
                'judul' => 'Permohonan Mutasi Antar-RT Masuk',
                'pesan' => "Ketua RT {$rtAsalId} mengajukan kepindahan warga {$profile->nama_lengkap} (NIK: {$profile->nik}) ke wilayah RT Anda. Silakan verifikasi.",
                'dibaca' => false,
            ]);
        }

        AuditLog::record(
            action: 'mutasi_antar_rt_diajukan',
            entityType: 'mutasi_warga',
            entityId: $mutasi->id,
            deskripsi: "Pengajuan mutasi warga {$profile->nama_lengkap} dari RT {$rtAsalId} ke RT {$validated['rt_id_tujuan']} oleh {$user->name}",
            rtId: $rtAsalId,
            userId: $user->id
        );

        return redirect()->route('warga.mutasi', ['tab' => 'antar_rt'])->with('success', 'Permohonan mutasi antar-RT berhasil diajukan dan menunggu verifikasi RT Tujuan.');
    }

    public function approveMutasi(Request $request, MutasiWarga $mutasi)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        if ($mutasi->jenis_mutasi === 'pindah_rt') {
            if ($user->isAdminRt()) {
                abort_unless((int)$mutasi->rt_id_tujuan === (int)$user->managedRtId(), 403, 'Hanya Ketua RT Tujuan yang berhak menyetujui mutasi masuk antar-RT.');
            }

            DB::transaction(function () use ($mutasi, $user) {
                if ($mutasi->user_profile_id) {
                    UserProfile::where('id', $mutasi->user_profile_id)->update([
                        'rt_id' => $mutasi->rt_id_tujuan,
                        'kartu_keluarga_id' => null,
                    ]);
                }

                $mutasi->update([
                    'status_verifikasi' => 'disetujui',
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                ]);

                AuditLog::record(
                    action: 'mutasi_antar_rt_disetujui',
                    entityType: 'mutasi_warga',
                    entityId: $mutasi->id,
                    deskripsi: "Persetujuan mutasi warga {$mutasi->nama_warga} masuk ke RT {$mutasi->rt_id_tujuan} oleh {$user->name}",
                    rtId: $mutasi->rt_id_tujuan,
                    userId: $user->id
                );
            });

            return redirect()->route('warga.mutasi', ['tab' => 'antar_rt'])->with('success', "Mutasi warga {$mutasi->nama_warga} ke RT tujuan berhasil disetujui dan wilayah warga telah diperbarui.");
        } elseif ($mutasi->jenis_mutasi === 'masuk') {
            if ($user->isAdminRt()) {
                abort_unless((int)$mutasi->rt_id === (int)$user->managedRtId(), 403);
            }

            DB::transaction(function () use ($mutasi, $user) {
                $kk = KartuKeluarga::firstOrCreate(
                    ['no_kk' => $mutasi->no_kk],
                    [
                        'rt_id' => $mutasi->rt_id,
                        'alamat' => $mutasi->alamat_tujuan ?: 'Wilayah RT ' . str_pad($mutasi->rt_id, 2, '0', STR_PAD_LEFT),
                        'status_rumah' => 'tetap',
                    ]
                );

                UserProfile::firstOrCreate(
                    ['nik' => $mutasi->nik],
                    [
                        'rt_id' => $mutasi->rt_id,
                        'kartu_keluarga_id' => $kk->id,
                        'no_kk' => $mutasi->no_kk,
                        'nama_lengkap' => $mutasi->nama_warga,
                        'status_hubungan_keluarga' => 'Kepala Keluarga',
                        'status' => 'aktif',
                        'alamat' => $kk->alamat,
                    ]
                );

                $mutasi->update([
                    'status_verifikasi' => 'disetujui',
                    'verified_by' => $user->id,
                    'verified_at' => now(),
                ]);

                AuditLog::record(
                    action: 'mutasi_masuk_disetujui',
                    entityType: 'mutasi_warga',
                    entityId: $mutasi->id,
                    deskripsi: "Persetujuan mutasi masuk warga {$mutasi->nama_warga} (KK: {$mutasi->no_kk}) di RT {$mutasi->rt_id} oleh {$user->name}",
                    rtId: $mutasi->rt_id,
                    userId: $user->id
                );
            });

            return redirect()->route('warga.mutasi', ['tab' => 'masuk'])->with('success', "Mutasi masuk {$mutasi->nama_warga} berhasil disetujui dan dicatat ke data kependudukan.");
        }

        return redirect()->route('warga.mutasi')->with('info', 'Mutasi telah diverifikasi.');
    }

    public function rejectMutasi(Request $request, MutasiWarga $mutasi)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ], [
            'catatan_verifikasi.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $mutasi->update([
            'status_verifikasi' => 'ditolak',
            'catatan_verifikasi' => $validated['catatan_verifikasi'],
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        AuditLog::record(
            action: 'mutasi_ditolak',
            entityType: 'mutasi_warga',
            entityId: $mutasi->id,
            deskripsi: "Penolakan mutasi {$mutasi->jenis_mutasi} ({$mutasi->nama_warga}). Alasan: {$validated['catatan_verifikasi']}",
            rtId: $mutasi->rt_id,
            userId: $user->id
        );

        return redirect()->back()->with('success', "Permohonan mutasi berhasil ditolak dengan catatan: {$validated['catatan_verifikasi']}.");
    }

    public function storeMutasi(Request $request)
    {
        return $this->storeMutasiMasuk($request);
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
