<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KartuKeluarga;
use App\Models\MutasiWarga;
use App\Models\Rt;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KartuKeluargaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // If Warga, redirect to their personal family page
        if ($user->isWarga()) {
            return redirect()->route('kartu-keluarga.saya');
        }

        $rtId = null;
        $selectedRt = null;
        $rts = collect();

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $selectedRt = $user->managedRt ?? Rt::find($rtId);
        } else {
            // RW or Bendahara can view all RTs or pick a specific RT
            $rts = Rt::orderBy('kode_rt')->get();
            if ($request->filled('rt_id')) {
                $rtId = (int) $request->rt_id;
                $selectedRt = Rt::find($rtId);
            }
        }

        $query = KartuKeluarga::with(['rt', 'kepalaKeluarga', 'anggota' => function ($q) use ($request) {
            if ($request->filled('status_kependudukan')) {
                $q->where('status', $request->status_kependudukan);
            }
            $q->orderByRaw("CASE WHEN status_hubungan_keluarga = 'Kepala Keluarga' THEN 0 WHEN status_hubungan_keluarga = 'Istri' THEN 1 ELSE 2 END");
        }]);

        if ($rtId) {
            $query->where('rt_id', $rtId);
        }

        // Filter status kepemilikan rumah
        if ($request->filled('status_rumah')) {
            $query->where('status_rumah', $request->status_rumah);
        }

        // Pencarian: No KK, Alamat, Nama Kepala, Nama Anggota, NIK
        if ($request->filled('q')) {
            $search = '%' . trim($request->q) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('no_kk', 'like', $search)
                    ->orWhere('alamat', 'like', $search)
                    ->orWhereHas('kepalaKeluarga', fn ($sub) => $sub->where('name', 'like', $search))
                    ->orWhereHas('anggota', fn ($sub) => $sub->where('nama_lengkap', 'like', $search)->orWhere('nik', 'like', $search));
            });
        }

        // Filter status kependudukan KK
        if ($request->filled('status_kependudukan')) {
            $query->whereHas('anggota', fn ($sub) => $sub->where('status', $request->status_kependudukan));
        }

        $kartuKeluargas = $query->latest()->paginate(15)->withQueryString();

        // KK counts per RT for RW summary view
        $rtSummary = [];
        if ($user->isAdminRw() || $user->hasRole('bendahara')) {
            $rtSummary = Rt::withCount('kartuKeluargas')->orderBy('kode_rt')->get();
        }

        // Available target KKs for pindah KK modal (for this RT)
        $allKkRt = $rtId ? KartuKeluarga::where('rt_id', $rtId)->orderBy('no_kk')->get(['id', 'no_kk', 'alamat']) : collect();

        return view('kartu-keluarga.index', compact('kartuKeluargas', 'user', 'rtId', 'selectedRt', 'rts', 'rtSummary', 'allKkRt'));
    }

    public function saya()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $kk = null;

        if ($profile && $profile->kartu_keluarga_id) {
            $kk = KartuKeluarga::with(['rt', 'kepalaKeluarga', 'anggota'])->find($profile->kartu_keluarga_id);
        }

        if (!$kk) {
            $kk = KartuKeluarga::with(['rt', 'kepalaKeluarga', 'anggota'])
                ->where('kepala_keluarga_user_id', $user->id)
                ->first();
        }

        return view('kartu-keluarga.saya', compact('user', 'profile', 'kk'));
    }

    public function show(KartuKeluarga $kartuKeluarga)
    {
        $user = Auth::user();

        // Authorization check
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403, 'Anda tidak berhak mengakses data KK di luar wilayah RT Anda.');
        } elseif ($user->isWarga()) {
            $userKkId = optional($user->profile)->kartu_keluarga_id;
            abort_unless($userKkId === $kartuKeluarga->id || $kartuKeluarga->kepala_keluarga_user_id === $user->id, 403, 'Anda hanya dapat melihat data keluarga Anda sendiri.');
        }

        $kartuKeluarga->load(['rt', 'kepalaKeluarga', 'anggota.user']);

        // Available target KKs for pindah KK
        $otherKk = KartuKeluarga::where('rt_id', $kartuKeluarga->rt_id)
            ->where('id', '!=', $kartuKeluarga->id)
            ->orderBy('no_kk')
            ->get(['id', 'no_kk', 'alamat']);

        return view('kartu-keluarga.show', compact('kartuKeluarga', 'user', 'otherKk'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403, 'Hanya pengurus RT/RW yang dapat menambah Kartu Keluarga.');

        $rtId = $user->managedRtId();
        $rts = $user->isAdminRw() ? Rt::orderBy('kode_rt')->get() : collect();

        return view('kartu-keluarga.create', compact('user', 'rtId', 'rts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : (int) $request->rt_id;
        abort_unless($rtId, 422, 'Wilayah RT harus valid.');

        $validated = $request->validate([
            'no_kk' => ['required', 'string', 'digits:16', 'unique:kartu_keluargas,no_kk'],
            'alamat' => ['required', 'string', 'max:500'],
            'status_rumah' => ['required', 'in:tetap,kontrak,kos,lainnya'],
            'nama_kepala' => ['required', 'string', 'max:255'],
            'nik_kepala' => ['required', 'string', 'digits:16', 'unique:user_profiles,nik'],
            'jenis_kelamin_kepala' => ['required', 'in:L,P'],
            'tempat_lahir_kepala' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir_kepala' => ['nullable', 'date'],
            'agama_kepala' => ['nullable', 'string', 'max:50'],
            'pekerjaan_kepala' => ['nullable', 'string', 'max:100'],
            'no_hp_kepala' => ['nullable', 'string', 'max:20'],
        ], [
            'no_kk.required' => 'Nomor Kartu Keluarga (KK) wajib diisi.',
            'no_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
            'no_kk.unique' => 'Nomor KK ini sudah terdaftar dalam sistem.',
            'nik_kepala.required' => 'NIK Kepala Keluarga wajib diisi.',
            'nik_kepala.digits' => 'NIK Kepala Keluarga harus tepat 16 digit angka.',
            'nik_kepala.unique' => 'NIK ini sudah terdaftar sebagai warga.',
            'nama_kepala.required' => 'Nama Kepala Keluarga wajib diisi.',
            'jenis_kelamin_kepala.required' => 'Jenis kelamin Kepala Keluarga wajib dipilih.',
        ]);

        $kk = DB::transaction(function () use ($validated, $rtId, $user) {
            $newKk = KartuKeluarga::create([
                'rt_id' => $rtId,
                'no_kk' => $validated['no_kk'],
                'alamat' => $validated['alamat'],
                'status_rumah' => $validated['status_rumah'],
            ]);

            // Create Kepala Keluarga UserProfile
            $kepalaProfile = UserProfile::create([
                'rt_id' => $rtId,
                'kartu_keluarga_id' => $newKk->id,
                'no_kk' => $validated['no_kk'],
                'nama_lengkap' => $validated['nama_kepala'],
                'nik' => $validated['nik_kepala'],
                'jenis_kelamin' => $validated['jenis_kelamin_kepala'],
                'tempat_lahir' => $validated['tempat_lahir_kepala'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir_kepala'] ?? null,
                'agama' => $validated['agama_kepala'] ?? null,
                'pekerjaan' => $validated['pekerjaan_kepala'] ?? null,
                'no_hp' => $validated['no_hp_kepala'] ?? null,
                'alamat' => $validated['alamat'],
                'status_hubungan_keluarga' => 'Kepala Keluarga',
                'status' => 'aktif',
            ]);

            AuditLog::record(
                action: 'tambah_kk',
                entityType: 'kartu_keluarga',
                entityId: $newKk->id,
                deskripsi: "Pendaftaran KK Baru {$newKk->no_kk} (Kepala: {$validated['nama_kepala']}) oleh {$user->name}",
                rtId: $rtId,
                userId: $user->id
            );

            return $newKk;
        });

        return redirect()->route('kartu-keluarga.show', $kk)->with('success', 'Kartu Keluarga baru dan data Kepala Keluarga berhasil didaftarkan.');
    }

    public function edit(KartuKeluarga $kartuKeluarga)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        } elseif (!$user->isAdminRw()) {
            abort(403);
        }

        $rts = $user->isAdminRw() ? Rt::orderBy('kode_rt')->get() : collect();

        return view('kartu-keluarga.edit', compact('kartuKeluarga', 'user', 'rts'));
    }

    public function update(Request $request, KartuKeluarga $kartuKeluarga)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        } elseif (!$user->isAdminRw()) {
            abort(403);
        }

        $validated = $request->validate([
            'no_kk' => ['required', 'string', 'digits:16', 'unique:kartu_keluargas,no_kk,' . $kartuKeluarga->id],
            'alamat' => ['required', 'string'],
            'status_rumah' => ['required', 'in:tetap,kontrak,kos,lainnya'],
            'rt_id' => $user->isAdminRw() ? ['required', 'exists:rts,id'] : ['nullable'],
        ], [
            'no_kk.digits' => 'Nomor KK harus tepat 16 digit angka.',
            'no_kk.unique' => 'Nomor KK ini sudah digunakan oleh keluarga lain.',
        ]);

        DB::transaction(function () use ($validated, $kartuKeluarga, $user) {
            if ($user->isAdminRw() && isset($validated['rt_id'])) {
                $kartuKeluarga->rt_id = $validated['rt_id'];
            }

            $oldNoKk = $kartuKeluarga->no_kk;
            $kartuKeluarga->no_kk = $validated['no_kk'];
            $kartuKeluarga->alamat = $validated['alamat'];
            $kartuKeluarga->status_rumah = $validated['status_rumah'];
            $kartuKeluarga->save();

            // Update no_kk on all anggota profiles
            UserProfile::where('kartu_keluarga_id', $kartuKeluarga->id)->update([
                'no_kk' => $validated['no_kk'],
                'alamat' => $validated['alamat'],
                'rt_id' => $kartuKeluarga->rt_id,
            ]);

            AuditLog::record(
                action: 'edit_kk',
                entityType: 'kartu_keluarga',
                entityId: $kartuKeluarga->id,
                deskripsi: "Perubahan data KK {$oldNoKk} menjadi {$kartuKeluarga->no_kk} oleh {$user->name}",
                rtId: $kartuKeluarga->rt_id,
                userId: $user->id
            );
        });

        return redirect()->route('kartu-keluarga.show', $kartuKeluarga)->with('success', 'Data Kartu Keluarga berhasil diperbarui.');
    }

    public function storeAnggota(Request $request, KartuKeluarga $kartuKeluarga)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        } elseif (!$user->isAdminRw()) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16', 'unique:user_profiles,nik'],
            'status_hubungan_keluarga' => ['required', 'string', 'in:Kepala Keluarga,Istri,Suami,Anak,Mertua,Orang Tua,Famili Lain,Pembantu,Lainnya'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'pekerjaan' => ['nullable', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar dalam sistem.',
            'nama_lengkap.required' => 'Nama lengkap anggota wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin anggota wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $kartuKeluarga, $user) {
            UserProfile::create([
                'rt_id' => $kartuKeluarga->rt_id,
                'kartu_keluarga_id' => $kartuKeluarga->id,
                'no_kk' => $kartuKeluarga->no_kk,
                'nama_lengkap' => $validated['nama_lengkap'],
                'nik' => $validated['nik'],
                'status_hubungan_keluarga' => $validated['status_hubungan_keluarga'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'agama' => $validated['agama'] ?? null,
                'pekerjaan' => $validated['pekerjaan'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'alamat' => $kartuKeluarga->alamat,
                'status' => 'aktif',
            ]);

            AuditLog::record(
                action: 'tambah_anggota_kk',
                entityType: 'kartu_keluarga',
                entityId: $kartuKeluarga->id,
                deskripsi: "Penambahan anggota {$validated['nama_lengkap']} (NIK: {$validated['nik']}) ke KK {$kartuKeluarga->no_kk} oleh {$user->name}",
                rtId: $kartuKeluarga->rt_id,
                userId: $user->id
            );
        });

        return back()->with('success', 'Anggota keluarga berhasil ditambahkan ke Kartu Keluarga.');
    }

    public function updateAnggota(Request $request, KartuKeluarga $kartuKeluarga, UserProfile $profile)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        } elseif (!$user->isAdminRw()) {
            abort(403);
        }

        abort_unless($profile->kartu_keluarga_id === $kartuKeluarga->id, 404);

        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'digits:16', Rule::unique('user_profiles', 'nik')->ignore($profile->id)],
            'status_hubungan_keluarga' => ['required', 'string', 'in:Kepala Keluarga,Istri,Suami,Anak,Mertua,Orang Tua,Famili Lain,Pembantu,Lainnya'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:50'],
            'pekerjaan' => ['nullable', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:aktif,pindah,meninggal'],
        ], [
            'nik.digits' => 'NIK harus tepat 16 digit angka.',
            'nik.unique' => 'NIK ini sudah terdaftar oleh warga lain.',
        ]);

        DB::transaction(function () use ($validated, $kartuKeluarga, $profile, $user) {
            $oldStatus = $profile->status;
            $profile->update($validated);

            if ($profile->user) {
                $profile->user->name = $validated['nama_lengkap'];
                $profile->user->jenis_kelamin = $validated['jenis_kelamin'];
                $profile->user->save();
            }

            // Record status change to Mutasi if applicable
            if ($oldStatus !== $validated['status']) {
                MutasiWarga::create([
                    'rt_id' => $kartuKeluarga->rt_id,
                    'user_profile_id' => $profile->id,
                    'nama_warga' => $profile->nama_lengkap,
                    'nik' => $profile->nik,
                    'no_kk' => $kartuKeluarga->no_kk,
                    'jenis_mutasi' => $validated['status'],
                    'tanggal_mutasi' => now()->toDateString(),
                    'keterangan' => "Perubahan status kependudukan dari {$oldStatus} menjadi {$validated['status']}",
                    'created_by' => $user->id,
                ]);
            }

            AuditLog::record(
                action: 'edit_anggota_kk',
                entityType: 'user_profile',
                entityId: $profile->id,
                deskripsi: "Pembaruan data anggota {$profile->nama_lengkap} (KK: {$kartuKeluarga->no_kk}) oleh {$user->name}",
                rtId: $kartuKeluarga->rt_id,
                userId: $user->id
            );
        });

        return back()->with('success', 'Data anggota keluarga berhasil diperbarui.');
    }

    public function pindahKK(Request $request, KartuKeluarga $kartuKeluarga, UserProfile $profile)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        } elseif (!$user->isAdminRw()) {
            abort(403);
        }

        abort_unless($profile->kartu_keluarga_id === $kartuKeluarga->id, 404);

        $validated = $request->validate([
            'target_kartu_keluarga_id' => ['required', 'exists:kartu_keluargas,id'],
            'status_hubungan_keluarga' => ['required', 'string', 'in:Kepala Keluarga,Istri,Suami,Anak,Mertua,Orang Tua,Famili Lain,Pembantu,Lainnya'],
            'alasan_pindah' => ['nullable', 'string', 'max:500'],
        ]);

        $targetKk = KartuKeluarga::findOrFail($validated['target_kartu_keluarga_id']);

        DB::transaction(function () use ($kartuKeluarga, $targetKk, $profile, $validated, $user) {
            $oldNoKk = $kartuKeluarga->no_kk;

            $profile->update([
                'kartu_keluarga_id' => $targetKk->id,
                'no_kk' => $targetKk->no_kk,
                'alamat' => $targetKk->alamat,
                'rt_id' => $targetKk->rt_id,
                'status_hubungan_keluarga' => $validated['status_hubungan_keluarga'],
            ]);

            // Record to Mutasi Warga
            MutasiWarga::create([
                'rt_id' => $targetKk->rt_id,
                'user_profile_id' => $profile->id,
                'nama_warga' => $profile->nama_lengkap,
                'nik' => $profile->nik,
                'no_kk' => $targetKk->no_kk,
                'jenis_mutasi' => 'pindah_kk',
                'tanggal_mutasi' => now()->toDateString(),
                'keterangan' => "Pindah dari KK {$oldNoKk} ke KK {$targetKk->no_kk}. Alasan: " . ($validated['alasan_pindah'] ?: '-'),
                'created_by' => $user->id,
            ]);

            AuditLog::record(
                action: 'pindah_kk',
                entityType: 'user_profile',
                entityId: $profile->id,
                deskripsi: "Warga {$profile->nama_lengkap} dipindahkan dari KK {$oldNoKk} ke KK {$targetKk->no_kk} oleh {$user->name}",
                rtId: $targetKk->rt_id,
                userId: $user->id
            );
        });

        return back()->with('success', "Anggota keluarga {$profile->nama_lengkap} berhasil dipindahkan ke KK {$targetKk->no_kk}.");
    }

    public function destroyAnggota(KartuKeluarga $kartuKeluarga, UserProfile $profile)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        } elseif (!$user->isAdminRw()) {
            abort(403);
        }

        abort_unless($profile->kartu_keluarga_id === $kartuKeluarga->id, 404);

        DB::transaction(function () use ($profile, $kartuKeluarga, $user) {
            $nama = $profile->nama_lengkap;
            $nik = $profile->nik;

            // Record out mutation
            MutasiWarga::create([
                'rt_id' => $kartuKeluarga->rt_id,
                'user_profile_id' => $profile->id,
                'nama_warga' => $nama,
                'nik' => $nik,
                'no_kk' => $kartuKeluarga->no_kk,
                'jenis_mutasi' => 'keluar',
                'tanggal_mutasi' => now()->toDateString(),
                'keterangan' => "Dikeluarkan dari KK {$kartuKeluarga->no_kk}",
                'created_by' => $user->id,
            ]);

            if ($profile->user_id) {
                $profile->update(['kartu_keluarga_id' => null]);
            } else {
                $profile->delete();
            }

            AuditLog::record(
                action: 'hapus_anggota_kk',
                entityType: 'kartu_keluarga',
                entityId: $kartuKeluarga->id,
                deskripsi: "Anggota {$nama} (NIK: {$nik}) dikeluarkan dari KK {$kartuKeluarga->no_kk} oleh {$user->name}",
                rtId: $kartuKeluarga->rt_id,
                userId: $user->id
            );
        });

        return back()->with('success', 'Anggota keluarga berhasil dikeluarkan dari Kartu Keluarga.');
    }

    public function cetak(KartuKeluarga $kartuKeluarga)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($kartuKeluarga->rt_id === $user->managedRtId(), 403);
        }

        $kartuKeluarga->load(['rt.rw', 'kepalaKeluarga', 'anggota']);

        return view('kartu-keluarga.cetak', compact('kartuKeluarga', 'user'));
    }
}
