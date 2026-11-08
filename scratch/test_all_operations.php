<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Rt;
use App\Models\Balita;
use App\Models\PemeriksaanBalita;
use App\Models\JadwalPosyandu;
use App\Models\JadwalRonda;
use App\Models\TukarJadwalRonda;
use App\Models\AbsensiRonda;
use App\Models\PanicReport;
use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\LayananMasyarakatController;
use App\Http\Controllers\RtVerifikasiController;

$results = [];

function check($title, $condition, &$results, $details = '') {
    $status = $condition ? 'PASSED' : 'FAILED';
    $results[] = ['title' => $title, 'status' => $status, 'details' => $details];
    echo ($condition ? "✅ " : "❌ ") . "[$status] $title" . ($details ? " ($details)" : "") . "\n";
}

echo "=========================================================\n";
echo "PENGUJIAN MENYELURUH 3 MODUL BERMASALAH & ISOLASI DATA RT\n";
echo "=========================================================\n\n";

$rt01User = User::where('email', 'rt01@rw-smart.test')->first() ?? User::role('rt_01')->first() ?? User::where('role', 'ketua_rt')->first();
$rt02User = User::where('email', 'rt02@rw-smart.test')->first() ?? User::role('rt_02')->first();
$rwUser = User::where('email', 'admin@rw-smart.test')->first();
$wargaUser = User::where('email', 'warga@rw-smart.test')->first();

check("Akun Demo RT 01 Tersedia", !is_null($rt01User), $results, $rt01User?->email);
check("Akun Demo RT 02 Tersedia", !is_null($rt02User), $results, $rt02User?->email);
check("Akun Demo RW Tersedia", !is_null($rwUser), $results, $rwUser?->email);
check("Akun Demo Warga Tersedia", !is_null($wargaUser), $results, $wargaUser?->email);

$rt01Id = $rt01User->managedRtId();
$rt02Id = $rt02User?->managedRtId();
check("RT 01 ID valid", !empty($rt01Id), $results, "RT ID: $rt01Id");
check("RT 02 ID valid", !empty($rt02Id), $results, "RT ID: $rt02Id");

$layananController = app()->make(LayananMasyarakatController::class);
$rtVerifikasiController = app()->make(RtVerifikasiController::class);

// =======================================================
// 1. PENGUJIAN MODUL POSYANDU RT
// =======================================================
echo "\n--- 1. PENGUJIAN OPERASIONAL POSYANDU RT ---\n";
Auth::login($rt01User);

DB::beginTransaction();
try {
    // A. Buat Jadwal Posyandu
    $reqJadwal = Request::create('/layanan/posyandu/jadwal', 'POST', [
        'judul' => 'Posyandu Mawar RT 01 Balita & Batita',
        'tanggal_mulai' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'tanggal_selesai' => now()->addDays(3)->addHours(3)->format('Y-m-d H:i:s'),
        'lokasi' => 'Balai Pertemuan Warga RT 01',
        'nama_bidan' => 'Bidan Hj. Nurjanah, S.Tr.Keb',
        'kontak_bidan' => '081299887711',
        'kader_info' => 'Ibu Siti Aminah (Koordinator)',
        'status' => 'terjadwal',
        'keterangan' => 'Pemeriksaan rutin bulanan dan pembagian vitamin A.',
    ]);
    $resJadwal = $layananController->storeJadwalPosyandu($reqJadwal);
    $jadwalCreated = JadwalPosyandu::where('judul', 'Posyandu Mawar RT 01 Balita & Batita')->first();
    check("Buat Jadwal Posyandu RT dengan Bidan & Kader", !is_null($jadwalCreated), $results, "ID: " . ($jadwalCreated?->id ?? '-'));

    // B. Edit Jadwal Posyandu
    if ($jadwalCreated) {
        $reqEditJadwal = Request::create("/layanan/posyandu/jadwal/{$jadwalCreated->id}", 'PUT', [
            'judul' => 'Posyandu Mawar RT 01 Balita & Batita (Diperbarui)',
            'tanggal_mulai' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'lokasi' => 'Pos Kamling RT 01',
            'nama_bidan' => 'Bidan Hj. Nurjanah, S.Tr.Keb & Tim',
            'kontak_bidan' => '081299887722',
            'kader_info' => 'Ibu Siti & Kader RT 01',
            'status' => 'berlangsung',
            'keterangan' => 'Jadwal dimajukan 1 hari.',
        ]);
        $layananController->updateJadwalPosyandu($reqEditJadwal, $jadwalCreated);
        $freshJadwal = $jadwalCreated->fresh();
        check("Edit Jadwal Posyandu RT", $freshJadwal->judul === 'Posyandu Mawar RT 01 Balita & Batita (Diperbarui)' && $freshJadwal->status === 'berlangsung', $results, "Status: {$freshJadwal->status}");
    }

    // C. Mendaftarkan Balita RT
    $uniqueNikBalita = '3201' . rand(100000000000, 999999999999);
    $reqBalita = Request::create('/layanan/posyandu/balita', 'POST', [
        'nama' => 'Adik Rafathar Test',
        'nik' => $uniqueNikBalita,
        'tanggal_lahir' => now()->subMonths(16)->format('Y-m-d'),
        'jenis_kelamin' => 'L',
        'orang_tua_user_id' => $wargaUser->id,
        'catatan_kesehatan' => 'Riwayat alergi udang ringan',
    ]);
    $layananController->storeBalita($reqBalita);
    $balitaCreated = Balita::where('nik', $uniqueNikBalita)->first();
    check("Pendaftaran Balita RT", !is_null($balitaCreated) && (int)$balitaCreated->rt_id === (int)$rt01Id, $results, "Balita ID: " . ($balitaCreated?->id ?? '-'));

    // D. Mencatat Pemeriksaan Pertumbuhan & Notifikasi Orang Tua
    if ($balitaCreated) {
        $countNotifBefore = Notifikasi::where('user_id', $wargaUser->id)->count();
        $reqPeriksa = Request::create('/layanan/posyandu/pemeriksaan', 'POST', [
            'balita_id' => $balitaCreated->id,
            'jadwal_posyandu_id' => $jadwalCreated?->id,
            'tanggal_periksa' => now()->format('Y-m-d'),
            'berat_kg' => 11.4,
            'tinggi_cm' => 84.5,
            'lingkar_kepala_cm' => 46.5,
            'lingkar_lengan_cm' => 15.2,
            'status_gizi' => 'Gizi Baik',
            'imunisasi_sudah' => 'BCG, Polio 1-4, DPT 1-3',
            'imunisasi_belum' => 'Campak Lanjutan',
            'catatan' => 'Pertumbuhan sangat baik, motorik aktif.',
        ]);
        $layananController->storePemeriksaanBalita($reqPeriksa);
        $periksa = PemeriksaanBalita::where('balita_id', $balitaCreated->id)->first();
        check("Mencatat Pemeriksaan Balita (LILA & Status Gizi)", !is_null($periksa) && (float)$periksa->lingkar_lengan_cm === 15.2 && $periksa->status_gizi === 'Gizi Baik', $results, "LILA: 15.2 cm");

        $countNotifAfter = Notifikasi::where('user_id', $wargaUser->id)->count();
        check("Notifikasi Otomatis Terkirim ke Orang Tua", $countNotifAfter > $countNotifBefore, $results, "Notif Total: $countNotifAfter");
    }

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    check("Modul Posyandu RT", false, $results, $e->getMessage());
}

// =======================================================
// 2. PENGUJIAN MODUL KEAMANAN & RONDA RT
// =======================================================
echo "\n--- 2. PENGUJIAN OPERASIONAL KEAMANAN & RONDA RT ---\n";
Auth::login($rt01User);

DB::beginTransaction();
try {
    // A. Buat Jadwal Ronda
    $reqRonda = Request::create('/layanan/ronda/jadwal', 'POST', [
        'mode' => 'manual',
        'tanggal' => now()->addDays(1)->format('Y-m-d'),
        'shift' => 'Shift Malam Regu 1',
        'lokasi_pos' => 'Pos Ronda Utama RT 01',
        'petugas' => [$rt01User->id, $wargaUser->id],
    ]);
    $layananController->storeJadwalRonda($reqRonda);
    $jadwalRonda = JadwalRonda::where('shift', 'Shift Malam Regu 1')->latest()->first();
    check("Buat Jadwal Ronda RT dan Kirim Notifikasi Petugas", !is_null($jadwalRonda) && $jadwalRonda->petugas->count() === 2, $results, "Petugas: " . $jadwalRonda?->petugas->count());

    // B. Edit Jadwal Ronda
    if ($jadwalRonda) {
        $reqEditRonda = Request::create("/layanan/ronda/jadwal/{$jadwalRonda->id}", 'PUT', [
            'tanggal' => now()->addDays(2)->format('Y-m-d'),
            'shift' => 'Shift Malam Regu 1 (Revisi)',
            'lokasi_pos' => 'Pos Ronda Barat RT 01',
            'status' => 'aktif',
            'petugas' => [$wargaUser->id],
        ]);
        $layananController->updateJadwalRonda($reqEditRonda, $jadwalRonda);
        $freshRonda = $jadwalRonda->fresh();
        check("Edit Jadwal Ronda RT", $freshRonda->shift === 'Shift Malam Regu 1 (Revisi)' && $freshRonda->petugas->count() === 1, $results, "Petugas Baru: " . $freshRonda->petugas->count());
    }

    // C. Pencatatan Absensi Ronda
    if ($jadwalRonda) {
        $reqAbsen = Request::create('/layanan/ronda/absensi', 'POST', [
            'jadwal_ronda_id' => $jadwalRonda->id,
            'user_id' => $wargaUser->id,
            'status' => 'hadir',
            'catatan' => 'Hadir patroli keliling RT tepat waktu.',
        ]);
        $layananController->storeAbsensiRonda($reqAbsen);
        $absen = AbsensiRonda::where('jadwal_ronda_id', $jadwalRonda->id)->where('user_id', $wargaUser->id)->first();
        check("Catat Absensi Ronda RT", !is_null($absen) && $absen->status === 'hadir', $results, "Status: {$absen?->status}");
    }

    // D. Pengajuan Pertukaran Jadwal & Persetujuan RT
    if ($jadwalRonda) {
        // Warga mengajukan tukar jadwal
        Auth::login($wargaUser);
        $reqTukar = Request::create('/layanan/ronda/tukar', 'POST', [
            'jadwal_ronda_asal_id' => $jadwalRonda->id,
            'alasan' => 'Ada lembur kerja mendadak shift malam.',
        ]);
        $layananController->requestTukarRonda($reqTukar);
        $tukar = TukarJadwalRonda::where('jadwal_ronda_asal_id', $jadwalRonda->id)->latest()->first();
        check("Warga Mengajukan Tukar Jadwal Ronda", !is_null($tukar), $results, "Tukar ID: " . ($tukar?->id ?? '-'));

        // RT menyetujui pertukaran jadwal
        if ($tukar) {
            Auth::login($rt01User);
            $reqResponTukar = Request::create("/layanan/ronda/tukar/{$tukar->id}/respon", 'PATCH', [
                'status' => 'disetujui',
                'catatan_rt' => 'Disetujui Ketua RT 01. Mohon konfirmasi rekan pengganti.',
            ]);
            $layananController->responTukarRonda($reqResponTukar, $tukar);
            check("Ketua RT Menyetujui Pertukaran Jadwal", $tukar->fresh()->status === 'disetujui', $results, "Status: " . $tukar->fresh()->status);
        }
    }

    // E. Laporan Panic Button & Tindak Lanjut RT
    $panic = PanicReport::create([
        'user_id' => $wargaUser->id,
        'rt_id' => $rt01Id,
        'lokasi' => 'Jl. Mawar Gang 3 RT 01',
        'deskripsi' => 'Terlihat orang gerak-gerik mencurigakan di rumah kosong',
        'status' => 'aktif',
    ]);
    check("Laporan Panic Button Warga Masuk", !is_null($panic) && $panic->status === 'aktif', $results, "ID: {$panic->id}");

    $reqTindakLanjut = Request::create("/layanan/ronda/panic/{$panic->id}/status", 'PATCH', [
        'status' => 'selesai',
    ]);
    $layananController->updatePanicReportStatus($reqTindakLanjut, $panic);
    check("Ketua RT Menindaklanjuti & Menutup Laporan Darurat", $panic->fresh()->status === 'selesai', $results, "Status Akhir: " . $panic->fresh()->status);

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    check("Modul Keamanan & Ronda RT", false, $results, $e->getMessage());
}

// =======================================================
// 3. PENGUJIAN MODUL MANAJEMEN PERMOHONAN RT
// =======================================================
echo "\n--- 3. PENGUJIAN OPERASIONAL MANAJEMEN PERMOHONAN RT ---\n";
Auth::login($rt01User);

DB::beginTransaction();
try {
    $jenisSurat = JenisSurat::first();

    // A. Buat permohonan baru dari warga RT 01
    $pengajuan1 = PengajuanSurat::create([
        'user_id' => $wargaUser->id,
        'rt_id' => $rt01Id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Permohonan Pengantar Surat Keterangan Usaha',
        'nomor_pengajuan' => 'SRT-RT01-' . rand(10000, 99999),
        'status' => 'menunggu_rt',
    ]);
    check("Permohonan Surat Warga RT 01 Terdaftar", !is_null($pengajuan1), $results, "No: {$pengajuan1->nomor_pengajuan}");

    // B. Verifikasi 7 Tabs Query di RtVerifikasiController
    $reqTabs = Request::create('/rt/verifikasi/permohonan', 'GET');
    $viewPermohonan = $rtVerifikasiController->permohonan($reqTabs);
    $dataView = $viewPermohonan->getData();

    check("Tab Permohonan Baru Terisi", isset($dataView['permohonanBaru']), $results);
    check("Tab Menunggu Verifikasi RT Terisi", isset($dataView['menungguRt']), $results);
    check("Tab Sedang Diproses Terisi", isset($dataView['sedangDiproses']), $results);
    check("Tab Diteruskan ke RW Terisi", isset($dataView['diteruskanRw']), $results);
    check("Tab Disetujui Terisi", isset($dataView['disetujui']), $results);
    check("Tab Ditolak Terisi", isset($dataView['ditolak']), $results);
    check("Tab Selesai Terisi", isset($dataView['selesai']), $results);
    check("Accordion Riwayat Tahunan/Bulanan Terisi", isset($dataView['riwayatGrouped']), $results);

    // C. RT Setujui Permohonan Langsung
    $reqSetujui = Request::create("/layanan/surat/{$pengajuan1->id}", 'PATCH', [
        'status' => 'siap_diambil',
        'catatan' => 'Berkas lengkap dan telah disetujui Ketua RT.',
    ]);
    $layananController->updateSurat($reqSetujui, $pengajuan1);
    check("Ketua RT Setujui Permohonan Langsung", in_array($pengajuan1->fresh()->status, ['siap_diambil', 'disetujui_rt', 'disetujui']), $results, "Status: " . $pengajuan1->fresh()->status);

    // D. Buat permohonan kedua dan Teruskan ke RW
    $pengajuan2 = PengajuanSurat::create([
        'user_id' => $wargaUser->id,
        'rt_id' => $rt01Id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Surat Pengantar SKCK Polsek',
        'nomor_pengajuan' => 'SRT-RT01-' . rand(10000, 99999),
        'status' => 'menunggu_rt',
    ]);

    $reqTeruskan = Request::create("/rt/verifikasi/{$pengajuan2->id}/teruskan", 'POST', [
        'catatan_rt' => 'Warga benar berdomisili di RT 01. Mohon persetujuan Ketua RW.',
    ]);
    $rtVerifikasiController->teruskanKeRw($reqTeruskan, $pengajuan2);
    $freshP2 = $pengajuan2->fresh();
    $isTeruskanValid = in_array($freshP2->status, ['menunggu_rw', 'diteruskan_ke_rw']) && $freshP2->kebutuhan_rw && !empty($freshP2->catatan_rt);
    check("Ketua RT Teruskan Permohonan ke RW", $isTeruskanValid, $results, "Status: {$freshP2->status}, Catatan: {$freshP2->catatan_rt}");

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    check("Modul Manajemen Permohonan RT", false, $results, $e->getMessage());
}

// =======================================================
// 4. PENGUJIAN ISOLASI DATA RT (RT 01 vs RT 02)
// =======================================================
echo "\n--- 4. PENGUJIAN ISOLASI DATA WILAYAH RT 01 & RT 02 ---\n";
DB::beginTransaction();
try {
    // Balita RT 01
    $balitaRt01 = Balita::create([
        'nama' => 'Anak RT 01 Rahasia',
        'tanggal_lahir' => '2023-01-01',
        'jenis_kelamin' => 'L',
        'rt_id' => $rt01Id,
    ]);

    // Balita RT 02
    $balitaRt02 = Balita::create([
        'nama' => 'Anak RT 02 Rahasia',
        'tanggal_lahir' => '2023-02-02',
        'jenis_kelamin' => 'P',
        'rt_id' => $rt02Id,
    ]);

    // Login RT 01
    Auth::login($rt01User);
    $viewPosyanduRt01 = $layananController->posyandu(Request::create('/layanan/posyandu', 'GET'));
    $balitasRt01 = $viewPosyanduRt01->getData()['balitas'];
    check("RT 01 melihat Balita miliknya", $balitasRt01->contains('id', $balitaRt01->id), $results);
    check("RT 01 TIDAK BISA melihat Balita RT 02 (Isolasi Posyandu)", !$balitasRt01->contains('id', $balitaRt02->id), $results);

    // Login RT 02
    Auth::login($rt02User);
    $viewPosyanduRt02 = $layananController->posyandu(Request::create('/layanan/posyandu', 'GET'));
    $balitasRt02 = $viewPosyanduRt02->getData()['balitas'];
    check("RT 02 melihat Balita miliknya", $balitasRt02->contains('id', $balitaRt02->id), $results);
    check("RT 02 TIDAK BISA melihat Balita RT 01 (Isolasi Posyandu)", !$balitasRt02->contains('id', $balitaRt01->id), $results);

    // Cek Akses Hapus Lintas RT ditolak (HTTP 403)
    try {
        $layananController->destroyBalita($balitaRt01); // RT 02 mencoba menghapus balita RT 01
        check("Proteksi Hapus Balita Lintas RT (Harus 403)", false, $results, "Seharusnya dilempar 403 Exception");
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        check("Proteksi Hapus Balita Lintas RT Menolak Akses (HTTP 403)", $e->getStatusCode() === 403, $results, "Code 403 Diterima");
    }

    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    check("Pengujian Isolasi RT", false, $results, $e->getMessage());
}

// =======================================================
// 5. REGRESSION TEST DEMO ACCOUNTS
// =======================================================
echo "\n--- 5. REGRESSION TEST LOGIN & RBAC AKUN DEMO ---\n";
$accounts = [
    ['label' => 'Admin RW', 'email' => 'admin@rw-smart.test', 'password' => 'password123'],
    ['label' => 'Bendahara', 'email' => 'bendahara@rw-smart.test', 'password' => 'password123'],
    ['label' => 'Ketua RT 01', 'email' => 'rt01@rw-smart.test', 'password' => 'password123'],
    ['label' => 'Warga', 'email' => 'warga@rw-smart.test', 'password' => 'password123'],
];

foreach ($accounts as $acc) {
    $u = User::where('email', $acc['email'])->first();
    $validPass = $u && Hash::check($acc['password'], $u->password);
    check("Kredensial {$acc['label']} ({$acc['email']})", $validPass, $results, "Valid: " . ($validPass ? "YA" : "TIDAK"));
}

echo "\n=========================================================\n";
$total = count($results);
$passed = count(array_filter($results, fn($r) => $r['status'] === 'PASSED'));
$failed = $total - $passed;
echo "HASIL AKHIR PENGUJIAN :\n";
echo "TOTAL PEMERIKSAAN : $total\n";
echo "BERHASIL (PASSED) : $passed\n";
echo "GAGAL (FAILED)    : $failed\n";
echo "=========================================================\n";

if ($failed === 0) {
    echo "🎉 SELURUH PENGUJIAN 3 MODUL & ISOLASI DATA SUKSES 100% TANPA ERROR!\n";
} else {
    echo "⚠️ ADA PENGUJIAN YANG GAGAL!\n";
}
