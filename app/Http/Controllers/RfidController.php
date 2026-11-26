<?php

namespace App\Http\Controllers;

use App\Models\AbsensiRonda;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\JadwalRonda;
use App\Models\RfidDevice;
use App\Models\RfidScan;
use App\Models\Rt;
use App\Models\SaldoSampah;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RfidController extends Controller
{
    /**
     * Memperbarui sesi perekaman aktif per pengguna petugas (kedaluwarsa 2 menit)
     */
    public static function refreshRecordingSession(int|string $userId): void
    {
        $expiry = now()->addMinutes(2)->timestamp;
        Cache::put("rfid_recording_session_user_{$userId}", $expiry, 120);
        Cache::put('rfid_recording_session_active', true, 120);

        $activeUsers = Cache::get('rfid_active_recording_users', []);
        if (!is_array($activeUsers)) {
            $activeUsers = [];
        }
        $activeUsers[$userId] = $expiry;

        $now = now()->timestamp;
        $activeUsers = array_filter($activeUsers, fn($exp) => $exp > $now);
        Cache::put('rfid_active_recording_users', $activeUsers, 120);
    }

    /**
     * Menghapus sesi perekaman aktif untuk pengguna petugas tertentu
     */
    public static function clearRecordingSession(int|string $userId): void
    {
        Cache::forget("rfid_recording_session_user_{$userId}");
        $activeUsers = Cache::get('rfid_active_recording_users', []);
        if (is_array($activeUsers)) {
            unset($activeUsers[$userId]);
            $now = now()->timestamp;
            $activeUsers = array_filter($activeUsers, fn($exp) => $exp > $now);
            Cache::put('rfid_active_recording_users', $activeUsers, 120);
        }
        if (empty($activeUsers)) {
            Cache::forget('rfid_recording_session_active');
        }
    }

    /**
     * Memeriksa apakah saat ini ada sesi perekaman aktif yang sedang menunggu kartu
     */
    public static function hasActiveRecordingSession(): bool
    {
        if (Cache::has('rfid_recording_session_active') && Cache::get('rfid_recording_session_active')) {
            return true;
        }

        $activeUsers = Cache::get('rfid_active_recording_users', []);
        if (!is_array($activeUsers) || empty($activeUsers)) {
            return false;
        }

        $now = now()->timestamp;
        foreach ($activeUsers as $uid => $exp) {
            if ($exp > $now && Cache::has("rfid_recording_session_user_{$uid}")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Menerima kiriman UID RFID dari NodeMCU ESP-32S + RC522
     * Endpoint: POST /api/rfid/scan
     * Dilengkapi Device Security Token Authentication (Mencegah Akses Hacker / Spoofing)
     * Bebas CSRF (Dikonfigurasi di bootstrap/app.php)
     */
    public function receiveScan(Request $request)
    {
        $deviceToken = $request->attributes->get('device_token');

        $rawUid = $request->input('uid');

        if (empty($rawUid)) {
            return response()->json([
                'status' => 'error',
                'access_granted' => false,
                'gate_open' => false,
                'buzzer_signal' => 'DENIED',
                'message' => 'Parameter UID tidak boleh kosong',
            ], 400);
        }

        // 1. Bersihkan UID: hilangkan spasi, titik dua, dash, dan ubah ke huruf kapital
        $cleanUid = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string) $rawUid));
        
        if (strlen($cleanUid) < 4) {
            return response()->json([
                'status' => 'error',
                'access_granted' => false,
                'gate_open' => false,
                'buzzer_signal' => 'DENIED',
                'message' => 'Format UID tidak valid',
            ], 422);
        }

        $formattedUid = implode(':', str_split($cleanUid, 2));
        $deviceId = (string) $request->input('device_id', 'ESP32-RC522');
        $cardType = (string) $request->input('card_type', 'E-KTP / ISO 14443A');
        $sak = $request->filled('sak') ? strtoupper(trim(preg_replace('/^0X/i', '', (string) $request->input('sak')))) : null;
        $atqa = $request->filled('atqa') ? strtoupper(trim(preg_replace('/^0X/i', '', (string) $request->input('atqa')))) : null;
        $uidSize = $request->filled('uid_size') ? (int) $request->input('uid_size') : null;
        $nowTimestamp = now()->timestamp;

        // 2. Cek pendaftaran dan fungsi perangkat di tabel rfid_devices
        $device = null;
        if (Schema::hasTable('rfid_devices')) {
            $device = RfidDevice::where('device_id', $deviceId)->first();
            if (! $device) {
                // device_id dari ESP32 yang belum terdaftar dicatat sebagai 'belum dikonfigurasi'
                $device = RfidDevice::create([
                    'device_id' => $deviceId,
                    'nama_lokasi' => 'Perangkat Baru (' . $deviceId . ')',
                    'fungsi' => RfidDevice::FUNGSI_BELUM_DIKONFIGURASI,
                    'is_active' => false,
                ]);
            }
        }

        $deviceFungsi = $device ? $device->fungsi : RfidDevice::FUNGSI_PEREKAMAN;
        $isConfigured = $device ? ($device->fungsi !== RfidDevice::FUNGSI_BELUM_DIKONFIGURASI) : true;
        $isActive = $device ? (bool) $device->is_active : true;

        // Jika perangkat belum dikonfigurasi atau sedang dinonaktifkan: TIDAK menjalankan aksi apa pun!
        if (! $isConfigured || ! $isActive) {
            $deniedMsg = ! $isConfigured
                ? 'Perangkat RFID belum dikonfigurasi'
                : 'Perangkat RFID sedang dinonaktifkan';

            if (Schema::hasTable('rfid_scans')) {
                RfidScan::create([
                    'uid' => $cleanUid,
                    'formatted_uid' => $formattedUid,
                    'card_type' => $cardType,
                    'sak' => $sak,
                    'atqa' => $atqa,
                    'uid_size' => $uidSize,
                    'device_id' => $deviceId,
                    'fungsi' => $deviceFungsi,
                    'device_token_id' => $deviceToken?->id,
                    'user_profile_id' => null,
                    'rt_id' => null,
                    'nama_warga' => 'Tidak terdaftar',
                    'access_granted' => false,
                    'buzzer_signal' => 'DENIED',
                    'access_message' => $deniedMsg,
                    'ip_address' => $request->ip(),
                    'scanned_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'unconfigured',
                'fungsi' => $deviceFungsi,
                'access_granted' => false,
                'gate_open' => false,
                'buzzer_signal' => 'DENIED',
                'message' => $deniedMsg,
                'access_message' => $deniedMsg,
            ], 200);
        }

        // Perangkat aktif dan terkonfigurasi: update timestamp aktivitas terakhir
        $device->update(['last_seen_at' => now()]);

        // 3. Validasi Profil Kartu yang Diterima untuk E-KTP (Super Admin Settings)
        // Default: menerima semua jenis kartu jika pengaturan kosong
        $profileSak = AppSetting::get('rfid_profile_sak');
        $profileUidSize = AppSetting::get('rfid_profile_uid_size');
        $profileCardType = AppSetting::get('rfid_profile_card_type');

        $isProfileMismatched = false;

        if (!empty($profileSak)) {
            $allowedSaks = array_map(function ($s) {
                return strtoupper(trim(preg_replace('/^0X/i', '', (string) $s)));
            }, explode(',', (string) $profileSak));

            if (empty($sak) || !in_array($sak, $allowedSaks)) {
                $isProfileMismatched = true;
            }
        }

        if (!$isProfileMismatched && !empty($profileUidSize)) {
            if (empty($uidSize) || (int) $uidSize !== (int) $profileUidSize) {
                $isProfileMismatched = true;
            }
        }

        if (!$isProfileMismatched && !empty($profileCardType)) {
            if (empty($cardType) || stripos($cardType, trim($profileCardType)) === false) {
                $isProfileMismatched = true;
            }
        }

        if ($isProfileMismatched) {
            if (Schema::hasTable('rfid_scans')) {
                RfidScan::create([
                    'uid' => $cleanUid,
                    'formatted_uid' => $formattedUid,
                    'card_type' => $cardType,
                    'sak' => $sak,
                    'atqa' => $atqa,
                    'uid_size' => $uidSize,
                    'device_id' => $deviceId,
                    'fungsi' => $deviceFungsi,
                    'device_token_id' => $deviceToken?->id,
                    'user_profile_id' => null,
                    'rt_id' => null,
                    'nama_warga' => 'Tidak terdaftar',
                    'access_granted' => false,
                    'buzzer_signal' => 'DENIED',
                    'access_message' => 'Jenis kartu tidak sesuai',
                    'ip_address' => $request->ip(),
                    'scanned_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'error',
                'fungsi' => $deviceFungsi,
                'access_granted' => false,
                'gate_open' => false,
                'buzzer_signal' => 'DENIED',
                'message' => 'Jenis kartu tidak sesuai',
                'access_message' => 'Jenis kartu tidak sesuai',
            ], 422);
        }

        // 4. Cek apakah UID sudah terdaftar pada database kependudukan
        $registered = UserProfile::with(['wilayahRt', 'user'])->where('rfid_uid', $cleanUid)->first();
        $statusWarga = strtolower($registered->status ?? 'aktif');
        $isWargaActive = $registered && in_array($statusWarga, ['aktif', 'terdaftar'], true);
        $rtKode = optional($registered?->wilayahRt)->kode_rt ?? $registered?->rt_id;

        // 5. Eksekusi Aksi Berdasarkan Fungsi Perangkat
        $hasActiveRecording = self::hasActiveRecordingSession();
        $accessGranted = false;
        $gateOpen = false;
        $accessMessage = 'Kartu belum terdaftar dalam sistem SMART RW.';
        $buzzerSignal = 'DENIED';

        if ($hasActiveRecording) {
            // Sesi perekaman aktif di modal: tap dari perangkat aktif mana pun (apa pun fungsinya)
            // harus ditangkap modal, lalu tidak memicu aksi lain (misalnya gate_open tidak boleh true pada tap itu).
            // Tap tetap dicatat di Riwayat dengan keterangan "dipakai untuk perekaman".
            $accessGranted = false;
            $gateOpen = false;
            $buzzerSignal = 'GRANTED';
            $accessMessage = 'dipakai untuk perekaman';
        } else {
            switch ($deviceFungsi) {
            case RfidDevice::FUNGSI_GERBANG:
                if ($isWargaActive) {
                    $gateOpen = true;
                    $accessGranted = true;
                    $buzzerSignal = 'GRANTED';
                    $accessMessage = "Akses Gerbang Dibuka: Selamat Datang, {$registered->nama_lengkap}" . ($rtKode ? " (RT {$rtKode})" : '');
                } else {
                    $gateOpen = false;
                    $accessGranted = false;
                    $buzzerSignal = 'DENIED';
                    $accessMessage = $registered
                        ? "Akses Gerbang Ditolak: Status warga {$registered->nama_lengkap} berstatus {$registered->status}"
                        : "Akses Gerbang Ditolak: Kartu belum terdaftar dalam sistem";
                }
                break;

            case RfidDevice::FUNGSI_BANK_SAMPAH:
                $gateOpen = false;
                if (! $registered) {
                    $accessGranted = false;
                    $buzzerSignal = 'DENIED';
                    $accessMessage = "Kartu belum terdaftar sebagai warga SMART RW.";
                } elseif (! $isWargaActive) {
                    $accessGranted = false;
                    $buzzerSignal = 'DENIED';
                    $accessMessage = "Akses Ditolak: Status warga {$registered->nama_lengkap} berstatus {$registered->status}";
                } else {
                    $accessGranted = true;
                    $buzzerSignal = 'GRANTED';

                    $saldoAmount = 0.0;
                    $totalBerat = 0.0;
                    if ($registered->user_id && Schema::hasTable('saldo_sampahs')) {
                        $saldoModel = SaldoSampah::firstOrCreate(
                            ['user_id' => $registered->user_id],
                            ['saldo' => 0, 'total_berat_kg' => 0]
                        );
                        $saldoAmount = (float) $saldoModel->saldo;
                        $totalBerat = (float) $saldoModel->total_berat_kg;
                    }

                    $bankSampahData = [
                        'uid' => $cleanUid,
                        'formatted_uid' => $formattedUid,
                        'card_type' => $cardType,
                        'sak' => $sak,
                        'atqa' => $atqa,
                        'uid_size' => $uidSize,
                        'device_id' => $deviceId,
                        'user_profile_id' => $registered->id,
                        'user_id' => $registered->user_id,
                        'nama_warga' => $registered->nama_lengkap,
                        'nik' => $registered->nik,
                        'rt_id' => $registered->rt_id,
                        'rt_kode' => $rtKode,
                        'saldo' => $saldoAmount,
                        'total_berat_kg' => $totalBerat,
                        'timestamp' => $nowTimestamp,
                        'scanned_at' => now()->toISOString(),
                    ];

                    Cache::put('rfid_bank_sampah_latest_scan', $bankSampahData, now()->addSeconds(60));
                    $accessMessage = "Warga Terdeteksi: {$registered->nama_lengkap}" . ($rtKode ? " (RT {$rtKode})" : '') . ". Saldo Bank Sampah: Rp " . number_format($saldoAmount, 0, ',', '.');
                }
                break;

            case RfidDevice::FUNGSI_PRESENSI:
                $gateOpen = false;
                if (! $registered) {
                    $accessGranted = false;
                    $buzzerSignal = 'DENIED';
                    $accessMessage = "Kartu belum terdaftar dalam sistem SMART RW.";
                } elseif (! $isWargaActive) {
                    $accessGranted = false;
                    $buzzerSignal = 'DENIED';
                    $accessMessage = "Presensi Ditolak: Status warga {$registered->nama_lengkap} berstatus {$registered->status}";
                } else {
                    $accessGranted = true;
                    $buzzerSignal = 'GRANTED';
                    $lokasiPos = $device->nama_lokasi ?: 'Pos Ronda';
                    $accessMessage = "Presensi Berhasil: {$registered->nama_lengkap} tercatat hadir ({$lokasiPos})";

                    $today = now()->toDateString();
                    $jadwal = null;
                    if (Schema::hasTable('jadwal_rondas')) {
                        $jadwal = JadwalRonda::where('rt_id', $registered->rt_id)
                            ->whereDate('tanggal', $today)
                            ->first();

                        if (! $jadwal) {
                            $jadwal = JadwalRonda::create([
                                'rt_id' => $registered->rt_id,
                                'tanggal' => $today,
                                'shift' => 1,
                                'jam_mulai' => '22:00',
                                'jam_selesai' => '04:00',
                                'lokasi_pos' => $lokasiPos,
                                'status' => 'berlangsung',
                                'created_by' => $registered->user_id,
                            ]);
                        }
                    }

                    if ($jadwal && Schema::hasTable('absensi_rondas')) {
                        AbsensiRonda::updateOrCreate(
                            [
                                'jadwal_ronda_id' => $jadwal->id,
                                'user_profile_id' => $registered->id,
                            ],
                            [
                                'user_id' => $registered->user_id,
                                'status' => 'hadir',
                                'waktu_absen' => now(),
                                'catatan' => "Presensi RFID Tap ({$deviceId})",
                                'recorded_by' => $registered->user_id,
                            ]
                        );
                    }

                    AuditLog::record(
                        action: 'presensi_rfid',
                        entityType: 'UserProfile',
                        entityId: $registered->id,
                        deskripsi: "Presensi kehadiran ronda RFID warga {$registered->nama_lengkap} di {$lokasiPos}",
                        rtId: $registered->rt_id,
                        userId: $registered->user_id
                    );
                }
                break;

            case RfidDevice::FUNGSI_PEREKAMAN:
            default:
                $gateOpen = false;
                if ($registered) {
                    if ($statusWarga === 'aktif' || $statusWarga === 'terdaftar') {
                        $accessGranted = true;
                        $accessMessage = "Akses Diterima: Selamat Datang, {$registered->nama_lengkap}" . ($rtKode ? " (RT {$rtKode})" : '');
                        $buzzerSignal = 'GRANTED';
                    } elseif ($statusWarga === 'pending') {
                        $accessMessage = "Akses Ditunda: Akun warga {$registered->nama_lengkap} masih menunggu verifikasi RT {$rtKode}";
                        $buzzerSignal = 'PENDING';
                    } else {
                        $accessMessage = "Akses Ditolak: Status warga {$registered->nama_lengkap} berstatus {$statusWarga}";
                        $buzzerSignal = 'DENIED';
                    }
                } else {
                    $accessGranted = false;
                    $accessMessage = 'Kartu belum terdaftar dalam sistem SMART RW.';
                    $buzzerSignal = 'DENIED';
                }
                break;
        }
        }

        // 6. Buat signature hash HMAC sebagai bukti verifikasi server anti-pemalsuan
        $tokenSecret = $deviceToken ? $deviceToken->token_hash : 'smart-rw-iot-verified';
        $verificationSignature = hash_hmac(
            'sha256',
            "{$cleanUid}|{$nowTimestamp}|" . ($accessGranted ? '1' : '0'),
            (string) $tokenSecret
        );

        $isGateOpenFinal = (bool) (! $hasActiveRecording && $deviceFungsi === RfidDevice::FUNGSI_GERBANG ? $gateOpen : false);

        $scanData = [
            'uid' => $cleanUid,
            'formatted_uid' => $formattedUid,
            'card_type' => $cardType,
            'sak' => $sak,
            'atqa' => $atqa,
            'uid_size' => $uidSize,
            'device_id' => $deviceId,
            'fungsi' => $deviceFungsi,
            'scanned_at' => now()->toISOString(),
            'timestamp' => $nowTimestamp,
            'access_granted' => $accessGranted,
            'gate_open' => $isGateOpenFinal,
            'access_message' => $accessMessage,
            'buzzer_signal' => $buzzerSignal,
            'verification_token' => $verificationSignature,
        ];

        $recordedCardType = $cardType;
        if ($registered && ! empty($registered->jenis_kartu)) {
            $recordedCardType = ($cardType && $cardType !== 'Smart Card / RFID')
                ? \Illuminate\Support\Str::limit("{$registered->jenis_kartu} ({$cardType})", 100, '')
                : $registered->jenis_kartu;
        }

        // 7. Simpan riwayat scan ke database untuk halaman Riwayat Scan RFID
        if (Schema::hasTable('rfid_scans')) {
            RfidScan::create([
                'uid' => $cleanUid,
                'formatted_uid' => $formattedUid,
                'card_type' => $recordedCardType,
                'sak' => $sak,
                'atqa' => $atqa,
                'uid_size' => $uidSize,
                'device_id' => $deviceId,
                'fungsi' => $deviceFungsi,
                'device_token_id' => $deviceToken?->id,
                'user_profile_id' => $registered?->id,
                'rt_id' => $registered?->rt_id,
                'nama_warga' => $registered?->nama_lengkap,
                'access_granted' => $accessGranted,
                'buzzer_signal' => $buzzerSignal,
                'access_message' => $accessMessage,
                'ip_address' => $request->ip(),
                'scanned_at' => now(),
            ]);
        }

        // Simpan ke cache buffer selama 60 detik untuk dibaca modal rekam web
        Cache::put('rfid_latest_scan', $scanData, now()->addSeconds(60));

        Log::info("RFID Scan Received from {$deviceId} (Fungsi: {$deviceFungsi}) | UID: {$cleanUid} | Access: " . ($accessGranted ? 'GRANTED' : 'DENIED'));

        return response()->json([
            'status' => 'success',
            'fungsi' => $deviceFungsi,
            'message' => $accessMessage,
            'access_granted' => $accessGranted,
            'gate_open' => $isGateOpenFinal,
            'access_message' => $accessMessage,
            'buzzer_signal' => $buzzerSignal,
            'verification_token' => $verificationSignature,
            'data' => $scanData,
            'is_registered' => (bool) $registered,
            'is_recording_session' => $hasActiveRecording,
            'registered_to' => $registered ? [
                'id' => $registered->id,
                'nama_lengkap' => $registered->nama_lengkap,
                'nik' => $registered->nik,
                'jenis_kartu' => $registered->jenis_kartu ?? 'Kartu RFID',
                'rt' => $rtKode,
                'status' => $registered->status ?? 'aktif',
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

        // Perbarui sesi perekaman aktif tiap polling modal (kedaluwarsa 2 menit)
        self::refreshRecordingSession($user->id);

        $scan = Cache::get('rfid_latest_scan');

        if (! $scan) {
            return response()->json([
                'status' => 'waiting',
                'message' => 'Belum ada kartu yang ditempelkan',
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
        $isUsed = (bool) $registered;
        $duplicateMessage = $isUsed ? "Kartu ini sudah terdaftar atas nama {$registered->nama_lengkap}" : null;

        return response()->json([
            'status' => 'detected',
            'message' => 'Kartu terdeteksi',
            'data' => $scan,
            'is_used' => $isUsed,
            'duplicate_message' => $duplicateMessage,
            'used_by' => $registered ? [
                'id' => $registered->id,
                'nama_lengkap' => $registered->nama_lengkap,
                'nik' => $registered->nik,
                'rt' => $registered->wilayahRt ? $registered->wilayahRt->kode_rt : $registered->rt_id,
            ] : null,
        ]);
    }

    /**
     * Memulai sesi perekaman aktif saat modal rekam dibuka
     * Endpoint: POST /warga/rfid/session/start
     */
    public function startRecordingSession(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdminRt() || $user->hasRole('rt') || $user->isAdminRw() || $user->isSuperAdmin()), 403);

        self::refreshRecordingSession($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi perekaman aktif',
            'expires_in' => 120,
        ]);
    }

    /**
     * Menghentikan sesi perekaman saat modal rekam ditutup
     * Endpoint: POST /warga/rfid/session/stop
     */
    public function stopRecordingSession(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            self::clearRecordingSession($user->id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi perekaman dihentikan',
        ]);
    }

    /**
     * Simpan UID Kartu RFID (E-KTP, e-Money, Flazz, Kartu Akses, Tag RFID) ke UserProfile warga terpilih
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
            'jenis_kartu' => 'nullable|string|max:100',
        ]);

        $cleanUid = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string) $request->input('rfid_uid')));

        if (strlen($cleanUid) < 4) {
            return response()->json([
                'status' => 'error',
                'message' => 'UID kartu RFID tidak valid (minimal 4 karakter heksadesimal).',
            ], 422);
        }

        // Tolak UID yang sudah terikat ke warga lain (unique UID tanpa validasi NIK)
        $duplicate = UserProfile::where('rfid_uid', $cleanUid)
            ->where('id', '!=', $profile->id)
            ->first();

        if ($duplicate) {
            return response()->json([
                'status' => 'error',
                'message' => "Kartu ini sudah terdaftar atas nama {$duplicate->nama_lengkap}",
            ], 422);
        }

        $jenisKartu = trim((string) $request->input('jenis_kartu')) ?: 'Kartu RFID';
        $cardType = (string) ($request->input('card_type')
            ?: optional(Cache::get('rfid_latest_scan'))['card_type']
            ?: $jenisKartu);

        $profile->rfid_uid = $cleanUid;
        $profile->jenis_kartu = $jenisKartu;
        $profile->rfid_card_type = $cardType;
        $profile->rfid_registered_at = now();
        $profile->rfid_registered_by = $user->name;
        if (empty($profile->status)) {
            $profile->status = 'aktif';
        }
        $profile->save();

        // Catat petugas ke AuditLog sistem
        AuditLog::record(
            action: 'update_rfid',
            entityType: 'UserProfile',
            entityId: $profile->id,
            deskripsi: "Mendaftarkan kartu akses RFID ({$jenisKartu} - {$cardType}) UID {$cleanUid} untuk warga {$profile->nama_lengkap} oleh petugas {$user->name}.",
            rtId: $profile->rt_id,
            userId: $user->id
        );

        // Bersihkan cache scan dan sesi perekaman aktif petugas agar tidak terbaca berulang kali
        Cache::forget('rfid_latest_scan');
        self::clearRecordingSession($user->id);

        return response()->json([
            'status' => 'success',
            'message' => "Kartu akses RFID ({$jenisKartu}) berhasil direkam untuk warga {$profile->nama_lengkap}",
            'data' => [
                'id' => $profile->id,
                'rfid_uid' => $cleanUid,
                'formatted_uid' => implode(':', str_split($cleanUid, 2)),
                'jenis_kartu' => $jenisKartu,
                'card_type' => $cardType,
                'status' => $profile->status,
                'registered_at' => $profile->rfid_registered_at?->format('d/m/Y H:i:s'),
                'registered_by' => $profile->rfid_registered_by,
            ],
        ]);
    }

    /**
     * Melepas tautan kartu RFID dari warga
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
        $profile->jenis_kartu = null;
        $profile->rfid_card_type = null;
        $profile->save();

        AuditLog::record(
            action: 'unlink_rfid',
            entityType: 'UserProfile',
            entityId: $profile->id,
            deskripsi: "Melepas tautan kartu RFID UID {$oldUid} dari warga {$profile->nama_lengkap}",
            rtId: $profile->rt_id,
            userId: $user->id
        );

        return response()->json([
            'status' => 'success',
            'message' => "Tautan kartu akses RFID berhasil dilepas dari warga {$profile->nama_lengkap}",
        ]);
    }

    /**
     * Halaman Riwayat Scan RFID untuk Super Admin, Admin RW, dan Ketua RT sesuai wilayah
     * Endpoint: GET /rfid/riwayat
     */
    public function history(Request $request)
    {
        $user = $this->authorizeHistoryAccess();

        $baseQuery = $this->buildHistoryQuery($request, $user);

        $scans = (clone $baseQuery)
            ->orderByDesc('scanned_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'granted' => (clone $baseQuery)->where('access_granted', true)->count(),
            'denied' => (clone $baseQuery)->where('access_granted', false)->count(),
            'unregistered' => (clone $baseQuery)->whereNull('user_profile_id')->count(),
        ];

        $isRtScoped = $user->isAdminRt();
        $managedRtKode = $isRtScoped ? $user->getRtKode() : null;
        $rts = $isRtScoped ? collect() : Rt::orderBy('kode_rt')->get();

        return view('rfid.history', compact(
            'scans',
            'stats',
            'isRtScoped',
            'managedRtKode',
            'rts'
        ));
    }

    /**
     * Endpoint JSON untuk auto-refresh Riwayat Scan RFID tiap 3 detik
     * Endpoint: GET /rfid/riwayat/data
     */
    public function historyData(Request $request)
    {
        $user = $this->authorizeHistoryAccess();

        $baseQuery = $this->buildHistoryQuery($request, $user);

        $scans = (clone $baseQuery)
            ->orderByDesc('scanned_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'granted' => (clone $baseQuery)->where('access_granted', true)->count(),
            'denied' => (clone $baseQuery)->where('access_granted', false)->count(),
            'unregistered' => (clone $baseQuery)->whereNull('user_profile_id')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'updated_at' => now()->format('H:i:s'),
            'stats' => $stats,
            'data' => $scans->map(fn (RfidScan $scan) => $this->formatHistoryItem($scan))->values(),
        ]);
    }

    private function authorizeHistoryAccess()
    {
        $user = Auth::user();
        $canAccess = $user && (
            ($user->isSuperAdmin() && ! $user->isSimulatingRole())
            || $user->isAdminRw()
            || $user->isAdminRt()
        );

        abort_unless($canAccess, 403, 'Akses ditolak: Hanya Super Admin, Admin RW, dan Ketua RT yang dapat melihat Riwayat Scan RFID.');

        return $user;
    }

    private function buildHistoryQuery(Request $request, $user)
    {
        $query = RfidScan::with(['userProfile.wilayahRt', 'rt', 'deviceToken'])
            ->forUser($user);

        // Filter RT opsional khusus Super Admin / Admin RW
        if (! $user->isAdminRt() && $request->filled('rt_id')) {
            if ($request->input('rt_id') === 'unregistered') {
                $query->whereNull('user_profile_id');
            } else {
                $query->where('rt_id', (int) $request->input('rt_id'));
            }
        }

        return $query;
    }

    private function formatHistoryItem(RfidScan $scan): array
    {
        $namaWarga = $scan->nama_warga
            ?: (optional($scan->userProfile)->nama_lengkap ?: 'Tidak terdaftar');
        $isRegistered = (bool) ($scan->user_profile_id || ($scan->nama_warga && $scan->nama_warga !== 'Tidak terdaftar'));
        $rtKode = optional($scan->rt)->kode_rt
            ?? optional(optional($scan->userProfile)->wilayahRt)->kode_rt;

        $buzzer = strtoupper((string) ($scan->buzzer_signal ?: ($scan->access_granted ? 'GRANTED' : 'DENIED')));
        $statusLabel = $scan->access_granted
            ? 'Diizinkan'
            : ($buzzer === 'PENDING' ? 'Pending' : 'Ditolak');

        $fungsi = $scan->fungsi ?: 'belum_dikonfigurasi';
        $fungsiLabel = match ($fungsi) {
            'gerbang' => 'Gerbang Portal',
            'bank_sampah' => 'Bank Sampah',
            'presensi' => 'Presensi Ronda',
            'perekaman' => 'Perekaman Kartu',
            default => 'Belum Dikonfigurasi',
        };

        return [
            'id' => $scan->id,
            'waktu' => $scan->scanned_at ? $scan->scanned_at->format('d/m/Y H:i:s') : '-',
            'waktu_relatif' => $scan->scanned_at ? $scan->scanned_at->diffForHumans() : '',
            'uid' => $scan->formatted_uid ?: $scan->uid,
            'uid_raw' => $scan->uid,
            'card_type' => $scan->card_type ?: (optional($scan->userProfile)->jenis_kartu ?: 'Kartu RFID'),
            'sak' => $scan->sak,
            'atqa' => $scan->atqa,
            'uid_size' => $scan->uid_size,
            'nama_warga' => $namaWarga,
            'is_registered' => $isRegistered,
            'rt_kode' => $rtKode,
            'perangkat' => $scan->device_id ?: (optional($scan->deviceToken)->name ?: 'ESP32-RC522'),
            'perangkat_token' => optional($scan->deviceToken)->name,
            'fungsi' => $fungsi,
            'fungsi_label' => $fungsiLabel,
            'access_granted' => (bool) $scan->access_granted,
            'buzzer_signal' => $buzzer,
            'status_label' => $statusLabel,
            'access_message' => $scan->access_message ?: '',
        ];
    }
}
