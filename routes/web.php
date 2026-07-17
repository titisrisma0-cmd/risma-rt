<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\IuranController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\BankSampahController;
use App\Http\Controllers\LayananMasyarakatController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\AspirasiController;
use App\Http\Controllers\RtController;
use App\Http\Controllers\CctvController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return redirect('/login#register');
})->name('register');

Auth::routes();

Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== KEGIATAN RW/RT ====================
    Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
    Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    Route::patch('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
    Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
    Route::post('/kegiatan/{kegiatan}/daftar', [KegiatanController::class, 'daftar'])->name('kegiatan.daftar');
    Route::delete('/kegiatan/{kegiatan}/daftar', [KegiatanController::class, 'batalDaftar'])->name('kegiatan.batal-daftar');
    Route::get('/kegiatan/{kegiatan}/peserta', [KegiatanController::class, 'peserta'])->name('kegiatan.peserta');
    Route::patch('/kegiatan/{kegiatan}/peserta/{user}', [KegiatanController::class, 'updatePeserta'])->name('kegiatan.peserta.update');

    // ==================== ASPIRASI ====================
    Route::get('/aspirasi/rekap', [AspirasiController::class, 'rekap'])->name('aspirasi.rekap');
    Route::get('/aspirasi', [AspirasiController::class, 'index'])->name('aspirasi.index');
    Route::post('/aspirasi', [AspirasiController::class, 'store'])->name('aspirasi.store');
    Route::get('/aspirasi/{aspirasi}', [AspirasiController::class, 'show'])->name('aspirasi.show');
    Route::post('/aspirasi/{aspirasi}/tanggapan', [AspirasiController::class, 'tanggapan'])->name('aspirasi.tanggapan');
    Route::patch('/aspirasi/{aspirasi}/status', [AspirasiController::class, 'status'])->name('aspirasi.status');
    Route::patch('/aspirasi/{aspirasi}/eskalasi', [AspirasiController::class, 'eskalasi'])->name('aspirasi.eskalasi');
    
    // ==================== PROFILE ROUTES ====================
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');
    Route::patch('/profile/kependudukan', [ProfileController::class, 'updateKependudukan'])->name('profile.update.kependudukan');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ==================== DATA WARGA ====================
    Route::resource('warga', WargaController::class);
    Route::post('warga/{id}/verify', [WargaController::class, 'verify'])->name('warga.verify');
    Route::resource('rts', RtController::class)->only(['index', 'store', 'update', 'destroy']);
    
    // ==================== IURAN ====================
    Route::resource('iuran', IuranController::class);
    Route::patch('iuran/{id}/toggle-status', [IuranController::class, 'toggleStatus'])->name('iuran.toggle-status');
    
    // ==================== PEMBAYARAN ====================
    Route::resource('pembayaran', PembayaranController::class);
    Route::post('pembayaran/{id}/verifikasi', [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    Route::get('get-total-bayar/{iuranId}', [PembayaranController::class, 'getTotalBayar'])->name('get-total-bayar');
    
    // ==================== LAPORAN ====================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::post('/generate', [LaporanController::class, 'generate'])->name('generate');
        Route::post('/per-warga', [LaporanController::class, 'laporanPerWarga'])->name('per-warga');
        Route::get('/show/{id}', [LaporanController::class, 'show'])->name('show');
        Route::delete('/{id}', [LaporanController::class, 'destroy'])->name('destroy');
    });
    
    // ==================== KAS ====================
    Route::get('kas', [KasController::class, 'index'])->name('kas.index');
    
    // ==================== PENGELUARAN ====================
    Route::resource('pengeluaran', PengeluaranController::class);

    // ==================== BANK SAMPAH ====================
    Route::prefix('bank-sampah')->name('bank-sampah.')->group(function () {
        Route::get('/', [BankSampahController::class, 'index'])->name('index');
        Route::post('/setoran', [BankSampahController::class, 'storeSetoran'])->name('setoran.store');
        Route::patch('/setoran/{setoran}/verifikasi', [BankSampahController::class, 'verifySetoran'])->name('setoran.verify');
        Route::post('/harga', [BankSampahController::class, 'updateHarga'])->name('harga.store');
        Route::post('/jadwal', [BankSampahController::class, 'storeJadwal'])->name('jadwal.store');
    });

    // ==================== LAYANAN MASYARAKAT ====================
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/umkm', [LayananMasyarakatController::class, 'umkm'])->name('umkm.index');
        Route::post('/umkm/toko-saya', [LayananMasyarakatController::class, 'storeUmkm'])->name('umkm.toko.store');
        Route::post('/umkm/produk', [LayananMasyarakatController::class, 'storeProdukUmkm'])->name('umkm.produk.store');
        Route::patch('/umkm/produk/{produk}/moderasi', [LayananMasyarakatController::class, 'moderateProdukUmkm'])->name('umkm.produk.moderate');
        Route::get('/surat', [LayananMasyarakatController::class, 'surat'])->name('surat.index');
        Route::post('/surat', [LayananMasyarakatController::class, 'storeSurat'])->name('surat.store');
        Route::patch('/surat/{pengajuanSurat}', [LayananMasyarakatController::class, 'updateSurat'])->name('surat.update');
        Route::get('/surat/{pengajuanSurat}/cetak', [LayananMasyarakatController::class, 'cetakSurat'])->name('surat.cetak');
        Route::get('/posyandu', [LayananMasyarakatController::class, 'posyandu'])->name('posyandu.index');
        Route::post('/posyandu/jadwal', [LayananMasyarakatController::class, 'storeJadwalPosyandu'])->name('posyandu.jadwal.store');
        Route::post('/posyandu/balita', [LayananMasyarakatController::class, 'storeBalita'])->name('posyandu.balita.store');
        Route::post('/posyandu/pemeriksaan', [LayananMasyarakatController::class, 'storePemeriksaanBalita'])->name('posyandu.pemeriksaan.store');
        Route::get('/ronda', [LayananMasyarakatController::class, 'ronda'])->name('ronda.index');
        Route::post('/ronda/jadwal', [LayananMasyarakatController::class, 'storeJadwalRonda'])->name('ronda.jadwal.store');
        Route::post('/ronda/absensi', [LayananMasyarakatController::class, 'storeAbsensiRonda'])->name('ronda.absensi.store');
        Route::post('/ronda/panic', [LayananMasyarakatController::class, 'storePanicReport'])->name('ronda.panic.store');
        Route::get('/ronda/cctv', [CctvController::class, 'live'])->name('ronda.cctv.live');
        Route::get('/ronda/cctv/recordings', [CctvController::class, 'recordings'])->name('ronda.cctv.recordings');
        Route::get('/ronda/cctv/manage', [CctvController::class, 'manage'])->name('ronda.cctv.manage');
        Route::post('/ronda/cctv/cameras', [CctvController::class, 'store'])->name('ronda.cctv.cameras.store');
        Route::put('/ronda/cctv/cameras/{camera}', [CctvController::class, 'update'])->name('ronda.cctv.cameras.update');
        Route::delete('/ronda/cctv/cameras/{camera}', [CctvController::class, 'destroy'])->name('ronda.cctv.cameras.destroy');
        Route::patch('/ronda/cctv/cameras/{camera}/status', [CctvController::class, 'toggleStatus'])->name('ronda.cctv.cameras.status');
    });

    // ==================== NOTIFIKASI ====================
    Route::get('notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('notifikasi/{id}/read', [\App\Http\Controllers\NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [\App\Http\Controllers\NotifikasiController::class, 'markAllRead'])->name('notifikasi.read-all');
});
