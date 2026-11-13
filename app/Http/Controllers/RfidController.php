<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RfidController extends Controller
{
    /**
     * Menerima kiriman UID RFID dari NodeMCU ESP-32S + RC522
     * Endpoint: POST /api/rfid/scan
     * Bebas CSRF (Dikonfigurasi di bootstrap/app.php)
     */
    public function receiveScan(Request $request)
    {
        $rawUid = $request->input('uid');

        if (empty($rawUid)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Parameter UID tidak boleh kosong',
            ], 400);
        }

        // Bersihkan UID: hilangkan spasi, titik dua, dash, dan ubah ke huruf kapital
        $cleanUid = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string) $rawUid));
        
        if (strlen($cleanUid) < 4) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format UID tidak valid',
            ], 422);
        }

        $formattedUid = implode(':', str_split($cleanUid, 2));
        $deviceId = (string) $request->input('device_id', 'ESP32-RC522');

        $scanData = [
            'uid' => $cleanUid,
            'formatted_uid' => $formattedUid,
            'device_id' => $deviceId,
            'scanned_at' => now()->toISOString(),
            'timestamp' => now()->timestamp,
        ];

        // Simpan ke cache buffer selama 60 detik
        Cache::put('rfid_latest_scan', $scanData, now()->addSeconds(60));

        // Cek apakah UID sudah terdaftar pada database
        $registered = UserProfile::where('rfid_uid', $cleanUid)->first();

        Log::info("RFID Scan Received from {$deviceId}: {$cleanUid}");

        return response()->json([
            'status' => 'success',
            'message' => 'UID E-KTP berhasil diterima oleh server',
            'data' => $scanData,
            'is_registered' => (bool) $registered,
            'registered_to' => $registered ? [
                'nama_lengkap' => $registered->nama_lengkap,
                'nik' => $registered->nik,
            ] : null,
        ], 200);
    }

    /**
     * Polling endpoint untuk modal admin data warga
     * Endpoint: GET /warga/rfid/latest
     */
    public function latestScan(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdminRt() || $user->hasRole('rt') || $user->isAdminRw() || $user->isSuperAdmin()), 403);

        $scan = Cache::get('rfid_latest_scan');

        if (! $scan) {
            return response()->json([
                'status' => 'waiting',
                'message' => 'Belum ada kartu E-KTP yang ditempelkan',
                'data' => null,
            ]);
        }

        // Jika request mengirimkan parameter 'since', hanya respon jika scan terjadi setelah timestamp tersebut
        if ($request->filled('since')) {
            $since = (int) $request->input('since');
            if ($scan['timestamp'] < $since) {
                return response()->json([
                    'status' => 'waiting',
                    'message' => 'Menunggu kartu baru ditempelkan',
                    'data' => null,
                ]);
            }
        }

        $cleanUid = $scan['uid'];
        $registered = UserProfile::with('wilayahRt')->where('rfid_uid', $cleanUid)->first();

        return response()->json([
            'status' => 'detected',
            'message' => 'Kartu E-KTP terdeteksi',
            'data' => $scan,
            'is_used' => (bool) $registered,
            'used_by' => $registered ? [
                'id' => $registered->id,
                'nama_lengkap' => $registered->nama_lengkap,
                'nik' => $registered->nik,
                'rt' => $registered->wilayahRt ? $registered->wilayahRt->kode_rt : $registered->rt_id,
            ] : null,
        ]);
    }

    /**
     * Simpan UID E-KTP ke UserProfile warga terpilih
     * Endpoint: POST /warga/{id}/rfid/save
     */
    public function assignToWarga(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdminRt() || $user->hasRole('rt') || $user->isAdminRw() || $user->isSuperAdmin()), 403);

        $profile = UserProfile::findOrFail($id);

        // Jika admin RT / Ketua RT, pastikan warga ini berada di RT binaannya
        if ($user->isAdminRt() || $user->hasRole('rt')) {
            $managedRtId = $user->managedRtId();
            if ($managedRtId !== null && (int) $profile->rt_id !== (int) $managedRtId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk mengelola data warga di luar RT Anda',
                ], 403);
            }
        }

        $request->validate([
            'rfid_uid' => 'required|string|min:4|max:64',
        ]);

        $cleanUid = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string) $request->input('rfid_uid')));

        // Cek apakah UID sudah dipakai oleh warga lain
        $duplicate = UserProfile::where('rfid_uid', $cleanUid)
            ->where('id', '!=', $profile->id)
            ->first();

        if ($duplicate) {
            return response()->json([
                'status' => 'error',
                'message' => "E-KTP dengan UID ini sudah terdaftar atas nama warga: {$duplicate->nama_lengkap} (NIK: {$duplicate->nik}). Gunakan kartu lain atau lepaskan tautan terlebih dahulu.",
            ], 422);
        }

        $oldUid = $profile->rfid_uid;
        $profile->rfid_uid = $cleanUid;
        $profile->save();

        // Catat ke Audit Log sistem
        AuditLog::record(
            action: 'update_rfid',
            entityType: 'UserProfile',
            entityId: $profile->id,
            deskripsi: "Merekam E-KTP UID {$cleanUid} untuk warga {$profile->nama_lengkap} (NIK: {$profile->nik})",
            rtId: $profile->rt_id
        );

        // Bersihkan cache scan agar tidak terbaca berulang kali
        Cache::forget('rfid_latest_scan');

        return response()->json([
            'status' => 'success',
            'message' => "E-KTP berhasil direkam untuk warga {$profile->nama_lengkap}",
            'data' => [
                'id' => $profile->id,
                'rfid_uid' => $cleanUid,
                'formatted_uid' => implode(':', str_split($cleanUid, 2)),
            ],
        ]);
    }

    /**
     * Melepas tautan E-KTP dari warga
     * Endpoint: DELETE /warga/{id}/rfid/unlink
     */
    public function unlinkFromWarga(Request $request, $id)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdminRt() || $user->hasRole('rt') || $user->isAdminRw() || $user->isSuperAdmin()), 403);

        $profile = UserProfile::findOrFail($id);

        if ($user->isAdminRt() || $user->hasRole('rt')) {
            $managedRtId = $user->managedRtId();
            if ($managedRtId !== null && (int) $profile->rt_id !== (int) $managedRtId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk mengelola data warga di luar RT Anda',
                ], 403);
            }
        }

        $oldUid = $profile->rfid_uid;
        $profile->rfid_uid = null;
        $profile->save();

        AuditLog::record(
            action: 'unlink_rfid',
            entityType: 'UserProfile',
            entityId: $profile->id,
            deskripsi: "Melepas tautan E-KTP UID {$oldUid} dari warga {$profile->nama_lengkap}",
            rtId: $profile->rt_id
        );

        return response()->json([
            'status' => 'success',
            'message' => "Tautan E-KTP berhasil dilepas dari warga {$profile->nama_lengkap}",
        ]);
    }
}
