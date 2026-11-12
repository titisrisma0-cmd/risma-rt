<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KartuKeluarga;
use App\Models\Notifikasi;
use App\Models\Rt;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WargaVerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $selectedRtId = null;
        $rts = Rt::activeRt()->orderBy('kode_rt')->get();

        if ($user->isAdminRt()) {
            $selectedRtId = $user->managedRtId();
        } elseif ($request->filled('rt_id')) {
            $selectedRtId = (int) $request->rt_id;
        }

        // Query Pendaftar Pending (Menunggu Verifikasi)
        $pendingQuery = UserProfile::with(['user', 'wilayahRt', 'kartuKeluarga'])
            ->where('status', 'pending');

        if ($selectedRtId) {
            $pendingQuery->where('rt_id', $selectedRtId);
        }

        $pendingList = $pendingQuery->latest()->get();

        // Query Riwayat Verifikasi Terakhir
        $historyQuery = UserProfile::with(['user', 'wilayahRt', 'verifiedBy'])
            ->whereIn('status', ['aktif', 'ditolak'])
            ->whereNotNull('verified_at');

        if ($selectedRtId) {
            $historyQuery->where('rt_id', $selectedRtId);
        }

        $historyList = $historyQuery->latest('verified_at')->limit(20)->get();

        // Statistik Ringkasan
        $stats = [
            'pending' => $selectedRtId ? UserProfile::where('rt_id', $selectedRtId)->where('status', 'pending')->count() : UserProfile::where('status', 'pending')->count(),
            'aktif' => $selectedRtId ? UserProfile::where('rt_id', $selectedRtId)->where('status', 'aktif')->count() : UserProfile::where('status', 'aktif')->count(),
            'ditolak' => $selectedRtId ? UserProfile::where('rt_id', $selectedRtId)->where('status', 'ditolak')->count() : UserProfile::where('status', 'ditolak')->count(),
        ];

        return view('warga.verifikasi', compact('user', 'pendingList', 'historyList', 'stats', 'selectedRtId', 'rts'));
    }

    public function approve($id)
    {
        $authUser = Auth::user();
        abort_unless($authUser->isAdminRt() || $authUser->isAdminRw(), 403);

        $profile = UserProfile::find($id);
        $user = null;

        if ($profile) {
            $user = $profile->user;
        } else {
            $user = User::with('profile')->find($id);
            if ($user) {
                $profile = $user->profile;
            }
        }

        if (!$profile) {
            return redirect()->back()->with('error', 'Data pendaftaran warga tidak ditemukan.');
        }

        if ($authUser->isAdminRt() && (int) $profile->rt_id !== (int) $authUser->managedRtId()) {
            abort(403, 'Anda tidak berhak memverifikasi warga di luar wilayah RT Anda.');
        }

        DB::transaction(function () use ($user, $profile, $authUser) {
            // Sambungkan / Buat Kartu Keluarga jika nomor KK tersedia
            if ($profile->no_kk && $profile->rt_id) {
                $kk = KartuKeluarga::firstOrCreate(
                    ['no_kk' => $profile->no_kk],
                    [
                        'rt_id' => $profile->rt_id,
                        'alamat' => $profile->alamat ?: 'Wilayah RT ' . str_pad($profile->rt_id, 2, '0', STR_PAD_LEFT),
                        'status_rumah' => 'tetap',
                        'kepala_keluarga_user_id' => $user ? $user->id : null,
                    ]
                );
                $profile->kartu_keluarga_id = $kk->id;
            }

            $profile->status = 'aktif';
            $profile->verified_by = $authUser->id;
            $profile->verified_at = now();
            $profile->save();

            AuditLog::record(
                action: 'verifikasi_warga_disetujui',
                entityType: 'user_profile',
                entityId: $profile->id,
                deskripsi: "Pendaftaran akun warga {$profile->nama_lengkap} (NIK: {$profile->nik}) disetujui oleh {$authUser->name}",
                rtId: $profile->rt_id,
                userId: $authUser->id
            );

            if ($user) {
                Notifikasi::create([
                    'user_id' => $user->id,
                    'from_user_id' => $authUser->id,
                    'tipe' => 'akun',
                    'judul' => 'Akun Terverifikasi',
                    'pesan' => 'Selamat! Akun Anda telah diverifikasi oleh Ketua RT dan sekarang Anda dapat login.',
                    'dibaca' => false,
                ]);
            }
        });

        return redirect()->back()->with('success', "Warga {$profile->nama_lengkap} berhasil disetujui dan akun telah aktif.");
    }

    public function reject(Request $request, $id)
    {
        $authUser = Auth::user();
        abort_unless($authUser->isAdminRt() || $authUser->isAdminRw(), 403);

        $request->validate([
            'alasan' => 'required|string|max:255',
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $profile = UserProfile::find($id);
        $user = null;

        if ($profile) {
            $user = $profile->user;
        } else {
            $user = User::with('profile')->find($id);
            if ($user) {
                $profile = $user->profile;
            }
        }

        if (!$profile) {
            return redirect()->back()->with('error', 'Data pendaftaran warga tidak ditemukan.');
        }

        if ($authUser->isAdminRt() && (int) $profile->rt_id !== (int) $authUser->managedRtId()) {
            abort(403, 'Anda tidak berhak menolak pendaftaran warga di luar wilayah RT Anda.');
        }

        $alasan = $request->input('alasan');

        DB::transaction(function () use ($user, $profile, $authUser, $alasan) {
            $profile->status = 'ditolak';
            $profile->catatan_verifikasi = $alasan;
            $profile->verified_by = $authUser->id;
            $profile->verified_at = now();
            $profile->save();

            AuditLog::record(
                action: 'verifikasi_warga_ditolak',
                entityType: 'user_profile',
                entityId: $profile->id,
                deskripsi: "Pendaftaran akun warga {$profile->nama_lengkap} ditolak oleh {$authUser->name}. Alasan: {$alasan}",
                rtId: $profile->rt_id,
                userId: $authUser->id
            );

            if ($user) {
                Notifikasi::create([
                    'user_id' => $user->id,
                    'from_user_id' => $authUser->id,
                    'tipe' => 'akun',
                    'judul' => 'Pendaftaran Akun Ditolak',
                    'pesan' => "Pendaftaran akun Anda ditolak oleh Ketua RT. Alasan: {$alasan}",
                    'dibaca' => false,
                ]);
            }
        });

        return redirect()->back()->with('success', "Pendaftaran warga {$profile->nama_lengkap} telah ditolak dengan alasan: {$alasan}.");
    }
}
