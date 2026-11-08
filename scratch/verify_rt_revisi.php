<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserProfile;
use App\Models\KartuKeluarga;
use App\Models\Rt;
use App\Models\JadwalBankSampah;
use App\Models\BankSampah;
use App\Models\Umkm;
use App\Models\JadwalPosyandu;
use App\Models\Balita;
use App\Models\PemeriksaanBalita;
use App\Models\JadwalRonda;
use App\Models\TukarJadwalRonda;
use App\Models\PanicReport;
use App\Models\Kegiatan;
use App\Models\Aspirasi;
use App\Models\PengajuanSurat;
use App\Models\JenisSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

$results = [];

function check($label, $condition, &$results, $details = '') {
    $status = $condition ? 'PASSED' : 'FAILED';
    $results[] = [
        'label' => $label,
        'status' => $status,
        'details' => $details
    ];
    echo ($condition ? "✅ " : "❌ ") . "[$status] $label" . ($details ? " - $details" : "") . "\n";
}

echo "=== START AUDIT & VERIFIKASI REVISI BESAR MODUL RT ===\n\n";

// 1. SINKRONISASI DATA GENDER (LAKI-LAKI & PEREMPUAN)
echo "--- 1. AUDIT GENDER SINKRONISASI ---\n";
$rts = Rt::activeRt()->orderBy('kode_rt')->get();
$totalWargaAll = 0;
$totalLakiAll = 0;
$totalPerempuanAll = 0;
$totalUnknownAll = 0;

foreach ($rts as $rt) {
    $profiles = UserProfile::where('rt_id', $rt->id)->get();
    $wargaCount = $profiles->count();
    $laki = $profiles->where('jenis_kelamin', 'L')->count();
    $perempuan = $profiles->where('jenis_kelamin', 'P')->count();
    $unknown = $profiles->filter(fn($p) => !in_array($p->jenis_kelamin, ['L', 'P']))->count();

    $totalWargaAll += $wargaCount;
    $totalLakiAll += $laki;
    $totalPerempuanAll += $perempuan;
    $totalUnknownAll += $unknown;

    check("RT {$rt->kode_rt}: Total ($wargaCount) == L ($laki) + P ($perempuan)", 
        $wargaCount === ($laki + $perempuan) && $unknown === 0, 
        $results, 
        "L: $laki, P: $perempuan, Unknown: $unknown"
    );
}
check("Total Keseluruhan RT 01-08: 0 Unknown Gender", $totalUnknownAll === 0, $results, "Unknown: $totalUnknownAll");
check("Total Keseluruhan: $totalWargaAll == $totalLakiAll (L) + $totalPerempuanAll (P)", 
    $totalWargaAll === ($totalLakiAll + $totalPerempuanAll), 
    $results, 
    "Total: $totalWargaAll, L: $totalLakiAll, P: $totalPerempuanAll"
);

// 2. PENYATUAN DATA KARTU KELUARGA & DATA WARGA
echo "\n--- 2. PENYATUAN DATA KARTU KELUARGA & DATA WARGA ---\n";
$rt01 = Rt::where('kode_rt', '01')->first();
$rt01User = User::where('email', 'rt01@rw-smart.test')->first() ?? User::role('rt_01')->first() ?? User::where('role', 'ketua_rt')->first();

check("Akun Ketua RT 01 ditemukan", !is_null($rt01User), $results, $rt01User?->email);

// Test Tambah KK Baru
$testNoKK = '3201' . rand(100000000000, 999999999999);
$testNikKepala = '3201' . rand(100000000000, 999999999999);

DB::beginTransaction();
try {
    $kk = KartuKeluarga::create([
        'no_kk' => $testNoKK,
        'rt_id' => $rt01->id,
        'alamat' => 'Jl. Kenanga Test No. 10',
        'status_rumah' => 'tetap',
    ]);

    $kepala = UserProfile::create([
        'kartu_keluarga_id' => $kk->id,
        'no_kk' => $testNoKK,
        'nik' => $testNikKepala,
        'nama_lengkap' => 'Bambang Supriyadi Test',
        'jenis_kelamin' => 'L',
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '1975-08-17',
        'agama' => 'Islam',
        'pekerjaan' => 'Wiraswasta',
        'status_perkawinan' => 'kawin',
        'hub_keluarga' => 'kepala_keluarga',
        'status_kependudukan' => 'tetap',
        'rt_id' => $rt01->id,
    ]);

    $kk->update(['kepala_keluarga_user_id' => $kepala->user_id]);

    // Tambah Anggota
    $testNikAnggota = '3201' . rand(100000000000, 999999999999);
    $anggota = UserProfile::create([
        'kartu_keluarga_id' => $kk->id,
        'no_kk' => $testNoKK,
        'nik' => $testNikAnggota,
        'nama_lengkap' => 'Endang Lestari Test',
        'jenis_kelamin' => 'P',
        'tempat_lahir' => 'Semarang',
        'tanggal_lahir' => '1980-02-14',
        'agama' => 'Islam',
        'pekerjaan' => 'PNS',
        'status_perkawinan' => 'kawin',
        'hub_keluarga' => 'istri',
        'status_kependudukan' => 'tetap',
        'rt_id' => $rt01->id,
    ]);

    check("Pembuatan KK baru (16 digit) dan Kepala Keluarga", $kk->exists && $kepala->exists, $results, "KK: {$kk->no_kk}");
    check("Penambahan anggota ke KK", $anggota->exists && $anggota->kartu_keluarga_id === $kk->id, $results, "Anggota: {$anggota->nama_lengkap}");
    check("Jumlah anggota keluarga pada KK terhitung akurat", $kk->anggota()->count() === 2, $results, "Anggota: " . $kk->anggota()->count());

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Pembuatan KK & Anggota", false, $results, $e->getMessage());
}

// 3. BANK SAMPAH RT
echo "\n--- 3. MODUL BANK SAMPAH RT ---\n";
DB::beginTransaction();
try {
    $jadwalBS = JadwalBankSampah::create([
        'rt_id' => $rt01->id,
        'judul' => 'Penimbangan Sampah Rutin RT 01',
        'waktu' => now()->addDays(2),
        'lokasi' => 'Pos Ronda RT 01',
        'keterangan' => 'Penimbangan botol & kardus',
        'petugas_nama' => 'Bpk. Agus & Ibu Dewi',
        'petugas_kontak' => '081234567891',
        'status' => 'terjadwal',
    ]);

    check("Jadwal Bank Sampah RT dengan Petugas & Status", 
        $jadwalBS->exists && $jadwalBS->petugas_nama === 'Bpk. Agus & Ibu Dewi', 
        $results, 
        "Petugas: {$jadwalBS->petugas_nama}, Status: {$jadwalBS->status}"
    );

    $jadwalBS->update(['status' => 'selesai']);
    check("Perubahan status jadwal operasional Bank Sampah", $jadwalBS->fresh()->status === 'selesai', $results);

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Jadwal Bank Sampah RT", false, $results, $e->getMessage());
}

// 4. UMKM RT & RW
echo "\n--- 4. MODUL UMKM RT & RW ---\n";
DB::beginTransaction();
try {
    $umkm = Umkm::create([
        'rt_id' => $rt01->id,
        'user_id' => $rt01User->id,
        'nama_usaha' => 'Catering Berkah RT 01',
        'kategori' => 'Kuliner',
        'alamat' => 'Jl. Melati RT 01',
        'no_telepon' => '081299887766',
        'deskripsi' => 'Nasi kotak dan tumpeng mini',
        'is_active' => true,
        'cakupan' => 'rt',
    ]);

    check("Pendaftaran UMKM warga tingkat RT", $umkm->exists && $umkm->rt_id === $rt01->id, $results, "Usaha: {$umkm->nama_usaha}");

    $umkm->update(['is_active' => 0]);
    check("Toggle status publikasi UMKM (publish/unpublish)", (int)$umkm->fresh()->is_active === 0, $results);

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Pendaftaran UMKM RT", false, $results, $e->getMessage());
}

// 5. POSYANDU RT
echo "\n--- 5. MODUL POSYANDU RT ---\n";
DB::beginTransaction();
try {
    $jadwalPosyandu = JadwalPosyandu::create([
        'rt_id' => $rt01->id,
        'judul' => 'Posyandu Balita & Lansia Rutin RT 01',
        'tanggal_mulai' => now()->addDays(4)->setTime(8, 30),
        'tanggal_selesai' => now()->addDays(4)->setTime(11, 30),
        'lokasi' => 'Balai Warga RT 01',
        'keterangan' => 'Pemeriksaan rutin dan pemberian vitamin',
        'nama_bidan' => 'Bidan Anisa, Amd.Keb',
        'kontak_bidan' => '081399988877',
        'kader_info' => 'Kader Melati RT 01',
        'status' => 'terjadwal',
    ]);

    check("Jadwal Posyandu RT mencatat Bidan & Kader", 
        $jadwalPosyandu->exists && $jadwalPosyandu->nama_bidan === 'Bidan Anisa, Amd.Keb', 
        $results, 
        "Bidan: {$jadwalPosyandu->nama_bidan}"
    );

    $balita = Balita::create([
        'nik' => '3201' . rand(100000000000, 999999999999),
        'nama' => 'Balita Uji Pratama',
        'jenis_kelamin' => 'L',
        'tanggal_lahir' => now()->subMonths(18)->toDateString(),
        'nama_ibu' => 'Ibu Siti',
        'rt_id' => $rt01->id,
    ]);

    $periksa = PemeriksaanBalita::create([
        'balita_id' => $balita->id,
        'tanggal_periksa' => now()->toDateString(),
        'berat_badan_kg' => 11.0,
        'tinggi_badan_cm' => 82.0,
        'lingkar_kepala_cm' => 47.0,
        'lingkar_lengan_cm' => 15.0,
        'status_gizi' => 'gizi_baik',
        'vitamin_a' => true,
        'imunisasi' => 'DPT-3',
        'catatan' => 'Pertumbuhan normal',
    ]);

    check("Pencatatan pemeriksaan balita dengan LILA & Status Gizi", 
        $periksa->exists && $periksa->lingkar_lengan_cm == 15.0 && $periksa->status_gizi === 'gizi_baik', 
        $results, 
        "LILA: {$periksa->lingkar_lengan_cm} cm, Status Gizi: {$periksa->status_gizi}"
    );

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Posyandu RT", false, $results, $e->getMessage());
}

// 6. KEAMANAN & RONDA RT
echo "\n--- 6. MODUL KEAMANAN & RONDA RT ---\n";
DB::beginTransaction();
try {
    $jadwalRonda = JadwalRonda::firstOrCreate([
        'rt_id' => $rt01->id,
        'tanggal' => now()->toDateString(),
        'shift' => 'Malam',
    ], [
        'lokasi_pos' => 'Pos Ronda Utama RT 01',
        'status' => 'aktif',
    ]);

    $tukar = TukarJadwalRonda::create([
        'rt_id' => $rt01->id,
        'user_id' => $rt01User->id,
        'jadwal_ronda_asal_id' => $jadwalRonda->id,
        'alasan' => 'Tugas dinas keluar kota',
        'status' => 'menunggu',
    ]);

    check("Pengajuan tukar shift ronda warga", $tukar->exists, $results, "ID: {$tukar->id}");

    $tukar->update([
        'status' => 'disetujui',
        'catatan_rt' => 'Disetujui Ketua RT',
        'approved_by' => $rt01User->id,
        'approved_at' => now(),
    ]);
    check("Persetujuan tukar shift oleh RT", $tukar->fresh()->status === 'disetujui', $results);

    $panic = PanicReport::create([
        'user_id' => $rt01User->id,
        'rt_id' => $rt01->id,
        'lokasi' => 'Jl. Kenanga Blok C',
        'deskripsi' => 'Ada suara mencurigakan di belakang rumah kosong',
        'status' => 'aktif',
    ]);
    check("Laporan Panic Button masuk ke RT", $panic->exists && $panic->status === 'aktif', $results);

    $panic->update(['status' => 'selesai', 'handled_by' => $rt01User->id, 'handled_at' => now()]);
    check("Penyelesaian respon Panic Button oleh RT", $panic->fresh()->status === 'selesai', $results);

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Keamanan & Ronda RT", false, $results, $e->getMessage());
}

// 7. KEGIATAN RT
echo "\n--- 7. MODUL KEGIATAN RT ---\n";
DB::beginTransaction();
try {
    $kegiatan = Kegiatan::create([
        'rt_id' => $rt01->id,
        'judul' => 'Pembersihan Saluran Air RT 01',
        'deskripsi' => 'Kerja bakti pembersihan gorong-gorong',
        'tanggal_mulai' => now()->addDays(3),
        'tanggal_selesai' => now()->addDays(3)->addHours(3),
        'lokasi' => 'Wilayah RT 01',
        'kategori' => 'Gotong Royong',
        'cakupan' => 'rt',
        'status' => 'dibuka',
    ]);

    check("Pembuatan Kegiatan terisolasi tingkat RT", $kegiatan->exists && $kegiatan->rt_id === $rt01->id, $results);

    $kegiatan->update(['status' => 'dibatalkan']);
    $kegiatan->delete(); // Soft delete jika dibatalkan
    check("Soft delete kegiatan jika dibatalkan", $kegiatan->trashed(), $results);

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Kegiatan RT", false, $results, $e->getMessage());
}

// 8. ASPIRASI WARGA
echo "\n--- 8. MODUL ASPIRASI WARGA ---\n";
DB::beginTransaction();
try {
    $aspirasi = Aspirasi::create([
        'user_id' => $rt01User->id,
        'rt_id' => $rt01->id,
        'judul' => 'Perbaikan Portal Masuk RT 01',
        'deskripsi' => 'Engsel portal sebelah barat macet',
        'kategori' => 'Keamanan',
        'status' => 'terkirim',
    ]);

    check("Aspirasi warga masuk ke RT", $aspirasi->exists, $results);

    $aspirasi->tanggapans()->create([
        'user_id' => $rt01User->id,
        'isi' => 'Petugas ronda akan melumasi dan memperbaiki engsel besok pagi.',
    ]);
    $aspirasi->update(['status' => 'diproses']);
    check("Ketua RT memberi tanggapan resmi & ubah status", 
        $aspirasi->fresh()->status === 'diproses' && $aspirasi->tanggapans()->count() > 0, 
        $results
    );

    $aspirasi->update(['status' => 'dieskalasi', 'escalation_target' => 'rw', 'escalated_at' => now()]);
    check("Eskalasi aspirasi ke RW", $aspirasi->fresh()->status === 'dieskalasi', $results);

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Aspirasi RT", false, $results, $e->getMessage());
}

// 9. MANAJEMEN PERMOHONAN
echo "\n--- 9. MODUL MANAJEMEN PERMOHONAN ---\n";
DB::beginTransaction();
try {
    $jenisSurat = JenisSurat::first();
    $pengajuan = PengajuanSurat::create([
        'user_id' => $rt01User->id,
        'rt_id' => $rt01->id,
        'jenis_surat_id' => $jenisSurat ? $jenisSurat->id : 1,
        'keperluan' => 'Pendaftaran Sekolah',
        'nomor_pengajuan' => 'SKD-' . rand(10000, 99999),
        'status' => 'menunggu_rt',
    ]);

    check("Pengajuan surat warga terdaftar", $pengajuan->exists, $results);

    $pengajuan->update([
        'status' => 'diteruskan_ke_rw',
        'catatan_rt' => 'Dokumen lengkap, diteruskan ke Ketua RW.',
        'rt_approved_by' => $rt01User->id,
        'rt_approved_at' => now(),
    ]);
    check("Penerusan surat dari RT ke RW", 
        $pengajuan->fresh()->status === 'diteruskan_ke_rw' && !empty($pengajuan->fresh()->catatan_rt), 
        $results
    );

    DB::rollBack();
} catch (\Exception $e) {
    DB::rollBack();
    check("Manajemen Permohonan", false, $results, $e->getMessage());
}

// 10. LOGIN DEMO ACCOUNTS AUDIT
echo "\n--- 10. AUDIT LOGIN DEMO ACCOUNTS ---\n";
$demoAccounts = [
    ['role' => 'RW / Admin', 'email' => 'admin@rw-smart.test', 'password' => 'password123'],
    ['role' => 'Bendahara', 'email' => 'bendahara@rw-smart.test', 'password' => 'password123'],
    ['role' => 'Warga', 'email' => 'warga@rw-smart.test', 'password' => 'password123'],
    ['role' => 'Ketua RT 01', 'email' => 'rt01@rw-smart.test', 'password' => 'password123'],
];

foreach ($demoAccounts as $demo) {
    $u = User::where('email', $demo['email'])->first();
    $authCheck = $u && Hash::check($demo['password'], $u->password);
    check("Login {$demo['role']} ({$demo['email']})", $authCheck, $results, "Valid Hash: " . ($authCheck ? "YES" : "NO"));
}

echo "\n============================================\n";
$totalChecks = count($results);
$passedChecks = count(array_filter($results, fn($r) => $r['status'] === 'PASSED'));
$failedChecks = $totalChecks - $passedChecks;
echo "TOTAL PEMERIKSAAN : $totalChecks\n";
echo "BERHASIL (PASSED) : $passedChecks\n";
echo "GAGAL (FAILED)    : $failedChecks\n";
echo "============================================\n";

if ($failedChecks === 0) {
    echo "🎉 SELURUH MODUL DAN FITUR REVISI BESAR KETUA RT BERFUNGSI SEMPURNA!\n";
} else {
    echo "⚠️ ADA PEMERIKSAAN YANG GAGAL!\n";
}
