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
use App\Http\Controllers\PetaWilayahController;
use App\Http\Controllers\KartuKeluargaController;
use App\Http\Controllers\AuditKeuanganController;
use App\Http\Controllers\MonitoringWilayahController;
use App\Http\Controllers\RtVerifikasiController;
use App\Http\Controllers\WargaVerifikasiController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\MetodePembayaranController;

Route::get('/', function () {
    try {
        $statRt = \App\Models\Rt::count();
        $statWarga = \App\Models\Warga::count();
        $statKk = \App\Models\KartuKeluarga::count();
        $statUmkm = \App\Models\Umkm::count();
    } catch (\Throwable $e) {
        $statRt = 8;
        $statWarga = 0;
        $statKk = 0;
        $statUmkm = 0;
    }
    return view('welcome', compact('statRt', 'statWarga', 'statKk', 'statUmkm'));
});

Route::get('/register', function () {
    return redirect('/login#register');
})->name('register');

Auth::routes(['verify' => true]);

Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/peta-wilayah', [PetaWilayahController::class, 'index'])->name('peta-wilayah.index');
    Route::get('/peta-wilayah/data', [PetaWilayahController::class, 'data'])->name('peta-wilayah.data');

    // ==================== KEGIATAN RW/RT ====================
    Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
    Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    Route::patch('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
    Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');
    Route::delete('/kegiatan/{kegiatan}/soft-delete', [KegiatanController::class, 'softDeleteCancelled'])->name('kegiatan.soft-delete');
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
    
    // ==================== DATA WARGA & KARTU KELUARGA ====================
    Route::get('kartu-keluarga/saya', [KartuKeluargaController::class, 'saya'])->name('kartu-keluarga.saya');
    Route::get('kartu-keluarga/{kartuKeluarga}/cetak', [KartuKeluargaController::class, 'cetak'])->name('kartu-keluarga.cetak');
    Route::resource('kartu-keluarga', KartuKeluargaController::class);
    Route::post('kartu-keluarga/{kartuKeluarga}/anggota', [KartuKeluargaController::class, 'storeAnggota'])->name('kartu-keluarga.anggota.store');
    Route::put('kartu-keluarga/{kartuKeluarga}/anggota/{profile}', [KartuKeluargaController::class, 'updateAnggota'])->name('kartu-keluarga.anggota.update');
    Route::post('kartu-keluarga/{kartuKeluarga}/anggota/{profile}/pindah', [KartuKeluargaController::class, 'pindahKK'])->name('kartu-keluarga.anggota.pindah');
    Route::delete('kartu-keluarga/{kartuKeluarga}/anggota/{profile}', [KartuKeluargaController::class, 'destroyAnggota'])->name('kartu-keluarga.anggota.destroy');
    Route::patch('kartu-keluarga/{kartuKeluarga}/anggota/{profile}/toggle-status', [KartuKeluargaController::class, 'toggleStatusAnggota'])->name('kartu-keluarga.anggota.toggle-status');

    Route::get('warga/mutasi', [WargaController::class, 'mutasi'])->name('warga.mutasi');
    Route::post('warga/mutasi', [WargaController::class, 'storeMutasi'])->name('warga.mutasi.store');
    Route::post('warga/mutasi/masuk', [WargaController::class, 'storeMutasiMasuk'])->name('warga.mutasi.masuk.store');
    Route::post('warga/mutasi/keluar', [WargaController::class, 'storeMutasiKeluar'])->name('warga.mutasi.keluar.store');
    Route::post('warga/mutasi/antar-rt', [WargaController::class, 'storeMutasiAntarRt'])->name('warga.mutasi.antar-rt.store');
    Route::post('warga/mutasi/{mutasi}/approve', [WargaController::class, 'approveMutasi'])->name('warga.mutasi.approve');
    Route::post('warga/mutasi/{mutasi}/reject', [WargaController::class, 'rejectMutasi'])->name('warga.mutasi.reject');
    Route::resource('warga', WargaController::class);
    Route::post('warga/{id}/verify', [WargaController::class, 'verify'])->name('warga.verify');
    Route::post('warga/{id}/reject', [WargaVerifikasiController::class, 'reject'])->name('warga.reject');
    Route::get('warga-verifikasi', [WargaVerifikasiController::class, 'index'])->name('warga.verifikasi.index');
    Route::post('warga-verifikasi/{id}/approve', [WargaVerifikasiController::class, 'approve'])->name('warga.verifikasi.approve');
    Route::post('warga-verifikasi/{id}/reject', [WargaVerifikasiController::class, 'reject'])->name('warga.verifikasi.reject');
    Route::resource('rts', RtController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::post('rts/{rt}/ketua', [RtController::class, 'storeKetua'])->name('rts.ketua.store');
    Route::put('rts/{rt}/ketua/{user}', [RtController::class, 'updateKetua'])->name('rts.ketua.update');
    Route::patch('rts/{rt}/ketua/{user}/toggle-status', [RtController::class, 'toggleKetuaStatus'])->name('rts.ketua.toggle-status');
    Route::post('rts/{rt}/ketua/{user}/reset-password', [RtController::class, 'resetKetuaPassword'])->name('rts.ketua.reset-password');

    // ==================== MONITORING & PENGAWASAN RW ====================
    Route::get('monitoring/warga', [MonitoringWilayahController::class, 'wargaPerRt'])->name('rw.monitoring.warga');
    Route::get('monitoring/kegiatan', [MonitoringWilayahController::class, 'kegiatanPerRt'])->name('rw.monitoring.kegiatan');
    Route::get('monitoring/layanan', [MonitoringWilayahController::class, 'layananPerRt'])->name('rw.monitoring.layanan');
    Route::get('pengawasan/persetujuan', [MonitoringWilayahController::class, 'pengajuanRw'])->name('rw.pengawasan.persetujuan');
    Route::post('pengawasan/persetujuan/{pengajuanSurat}', [MonitoringWilayahController::class, 'approvePengajuanRw'])->name('rw.pengawasan.approve');
    Route::get('pengawasan/laporan', [LaporanController::class, 'index'])->name('rw.pengawasan.laporan');
    Route::get('pengawasan/aktivitas', [AuditKeuanganController::class, 'auditLog'])->name('rw.pengawasan.aktivitas');
    Route::get('pengaturan-rw', [ProfileController::class, 'index'])->name('rw.pengaturan');

    // ==================== AUDIT KEUANGAN RW ====================
    Route::get('audit-keuangan', [AuditKeuanganController::class, 'index'])->name('rw.audit.index');
    Route::get('audit-keuangan/rt/{rt}', [AuditKeuanganController::class, 'showRt'])->name('rw.audit.show-rt');
    Route::post('audit-keuangan/rt/{rt}/status', [AuditKeuanganController::class, 'updateStatus'])->name('rw.audit.update-status');
    Route::get('audit-keuangan/riwayat', [AuditKeuanganController::class, 'auditLog'])->name('rw.audit.riwayat');
    Route::get('audit-keuangan/iuran', [AuditKeuanganController::class, 'index'])->name('rw.audit.iuran');
    Route::get('audit-keuangan/pengeluaran', [AuditKeuanganController::class, 'index'])->name('rw.audit.pengeluaran');
    Route::get('audit-keuangan/kas', [AuditKeuanganController::class, 'index'])->name('rw.audit.kas');

    // ==================== VERIFIKASI & PERSETUJUAN RT ====================
    Route::get('rt/verifikasi/permohonan', [RtVerifikasiController::class, 'permohonan'])->name('rt.verifikasi.permohonan');
    Route::get('rt/verifikasi/diteruskan', [RtVerifikasiController::class, 'diteruskan'])->name('rt.verifikasi.diteruskan');
    Route::get('rt/verifikasi/riwayat', [RtVerifikasiController::class, 'riwayat'])->name('rt.verifikasi.riwayat');
    Route::post('rt/verifikasi/{pengajuanSurat}/teruskan', [RtVerifikasiController::class, 'teruskanKeRw'])->name('rt.verifikasi.teruskan');
    Route::post('rt/verifikasi/{pengajuanSurat}/teruskan-rw', [RtVerifikasiController::class, 'teruskanKeRw'])->name('rt.verifikasi.teruskan-rw');
    Route::get('manajemen-permohonan', function () { return redirect()->route('rt.verifikasi.permohonan'); })->name('rt.permohonan.index');
    // ==================== PENGUMUMAN RESMI RW ====================
    Route::resource('pengumuman', PengumumanController::class);
    Route::get('rt/informasi/pengumuman', function () { return redirect()->route('pengumuman.index'); })->name('rt.informasi.pengumuman');
    Route::get('warga/pengumuman', function () { return redirect()->route('pengumuman.index'); })->name('warga.pengumuman');
    
    // ==================== IURAN ====================
    Route::resource('iuran', IuranController::class);
    Route::patch('iuran/{id}/toggle-status', [IuranController::class, 'toggleStatus'])->name('iuran.toggle-status');
    
    // ==================== PEMBAYARAN ====================
    Route::resource('pembayaran', PembayaranController::class);
    Route::post('pembayaran/{id}/verifikasi', [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
    Route::get('get-total-bayar/{iuranId}', [PembayaranController::class, 'getTotalBayar'])->name('get-total-bayar');

    // ==================== METODE PEMBAYARAN DIGITAL (RW / BENDAHARA) ====================
    Route::get('rw/metode-pembayaran', [MetodePembayaranController::class, 'index'])->name('rw.metode-pembayaran.index');
    Route::post('rw/metode-pembayaran', [MetodePembayaranController::class, 'store'])->name('rw.metode-pembayaran.store');
    Route::put('rw/metode-pembayaran/{metodePembayaran}', [MetodePembayaranController::class, 'update'])->name('rw.metode-pembayaran.update');
    Route::patch('rw/metode-pembayaran/{metodePembayaran}/toggle', [MetodePembayaranController::class, 'toggle'])->name('rw.metode-pembayaran.toggle');
    Route::delete('rw/metode-pembayaran/{metodePembayaran}', [MetodePembayaranController::class, 'destroy'])->name('rw.metode-pembayaran.destroy');
    
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
        Route::patch('/jadwal/{jadwal}/status', [BankSampahController::class, 'updateStatusJadwal'])->name('jadwal.status');
        Route::post('/penarikan', [BankSampahController::class, 'requestWithdrawal'])->name('penarikan.store');
        Route::patch('/penarikan/{penarikan}/proses', [BankSampahController::class, 'processWithdrawal'])->name('penarikan.proses');
    });

    // ==================== LAYANAN MASYARAKAT ====================
    Route::prefix('layanan')->name('layanan.')->group(function () {
        Route::get('/umkm', [LayananMasyarakatController::class, 'umkm'])->name('umkm.index');
        Route::post('/umkm/toko-saya', [LayananMasyarakatController::class, 'storeUmkm'])->name('umkm.toko.store');
        Route::post('/umkm/kelola', [LayananMasyarakatController::class, 'storeUmkmPengurus'])->name('umkm.pengurus.store');
        Route::patch('/umkm/{umkm}/toggle', [LayananMasyarakatController::class, 'toggleUmkmStatus'])->name('umkm.toggle');
        Route::post('/umkm/produk', [LayananMasyarakatController::class, 'storeProdukUmkm'])->name('umkm.produk.store');
        Route::put('/umkm/produk/{produk}', [LayananMasyarakatController::class, 'updateProdukUmkm'])->name('umkm.produk.update');
        Route::delete('/umkm/produk/{produk}', [LayananMasyarakatController::class, 'destroyProdukUmkm'])->name('umkm.produk.destroy');
        Route::patch('/umkm/produk/{produk}/moderasi', [LayananMasyarakatController::class, 'moderateProdukUmkm'])->name('umkm.produk.moderate');
        Route::get('/surat', [LayananMasyarakatController::class, 'surat'])->name('surat.index');
        Route::post('/surat', [LayananMasyarakatController::class, 'storeSurat'])->name('surat.store');
        Route::patch('/surat/{pengajuanSurat}', [LayananMasyarakatController::class, 'updateSurat'])->name('surat.update');
        Route::get('/surat/{pengajuanSurat}/cetak', [LayananMasyarakatController::class, 'cetakSurat'])->name('surat.cetak');
        Route::get('/posyandu', [LayananMasyarakatController::class, 'posyandu'])->name('posyandu.index');
        Route::post('/posyandu/jadwal', [LayananMasyarakatController::class, 'storeJadwalPosyandu'])->name('posyandu.jadwal.store');
        Route::put('/posyandu/jadwal/{jadwal}', [LayananMasyarakatController::class, 'updateJadwalPosyandu'])->name('posyandu.jadwal.update');
        Route::delete('/posyandu/jadwal/{jadwal}', [LayananMasyarakatController::class, 'destroyJadwalPosyandu'])->name('posyandu.jadwal.destroy');
        Route::post('/posyandu/balita', [LayananMasyarakatController::class, 'storeBalita'])->name('posyandu.balita.store');
        Route::put('/posyandu/balita/{balita}', [LayananMasyarakatController::class, 'updateBalita'])->name('posyandu.balita.update');
        Route::delete('/posyandu/balita/{balita}', [LayananMasyarakatController::class, 'destroyBalita'])->name('posyandu.balita.destroy');
        Route::post('/posyandu/pemeriksaan', [LayananMasyarakatController::class, 'storePemeriksaanBalita'])->name('posyandu.pemeriksaan.store');
        Route::get('/ronda', [LayananMasyarakatController::class, 'ronda'])->name('ronda.index');
        Route::post('/ronda/jadwal', [LayananMasyarakatController::class, 'storeJadwalRonda'])->name('ronda.jadwal.store');
        Route::post('/ronda/generate-preview', [LayananMasyarakatController::class, 'generatePreviewRonda'])->name('ronda.generate-preview');
        Route::post('/ronda/simpan-otomatis', [LayananMasyarakatController::class, 'simpanJadwalOtomatis'])->name('ronda.simpan-otomatis');
        Route::post('/ronda/jadwal/{jadwal}/ingatkan', [LayananMasyarakatController::class, 'kirimPengingatRonda'])->name('ronda.jadwal.ingatkan');
        Route::put('/ronda/jadwal/{jadwal}', [LayananMasyarakatController::class, 'updateJadwalRonda'])->name('ronda.jadwal.update');
        Route::delete('/ronda/jadwal/{jadwal}', [LayananMasyarakatController::class, 'destroyJadwalRonda'])->name('ronda.jadwal.destroy');
        Route::post('/ronda/absensi', [LayananMasyarakatController::class, 'storeAbsensiRonda'])->name('ronda.absensi.store');
        Route::post('/ronda/tukar', [LayananMasyarakatController::class, 'requestTukarRonda'])->name('ronda.tukar.store');
        Route::patch('/ronda/tukar/{tukar}/respon', [LayananMasyarakatController::class, 'responTukarRonda'])->name('ronda.tukar.respon');
        Route::post('/ronda/panic', [LayananMasyarakatController::class, 'storePanicReport'])->name('ronda.panic.store');
        Route::patch('/ronda/panic/{panicReport}/status', [LayananMasyarakatController::class, 'updatePanicReportStatus'])->name('ronda.panic.status');
        Route::get('/ronda/cctv', [CctvController::class, 'live'])->name('ronda.cctv.live');
        Route::get('/ronda/cctv/live', [CctvController::class, 'live'])->name('ronda.cctv.index');
        Route::get('/ronda/cctv/recordings', [CctvController::class, 'recordings'])->name('ronda.cctv.recordings');
        Route::get('/ronda/cctv/manage', [CctvController::class, 'manage'])->name('ronda.cctv.manage');
        Route::post('/ronda/cctv/cameras', [CctvController::class, 'store'])->name('ronda.cctv.cameras.store');
        Route::put('/ronda/cctv/cameras/{camera}', [CctvController::class, 'update'])->name('ronda.cctv.cameras.update');
        Route::delete('/ronda/cctv/cameras/{camera}', [CctvController::class, 'destroy'])->name('ronda.cctv.cameras.destroy');
        Route::patch('/ronda/cctv/cameras/{camera}/status', [CctvController::class, 'toggleStatus'])->name('ronda.cctv.cameras.status');
    });

    // ==================== ALIASES ROUTE OPERASIONAL RT ====================
    Route::get('/ronda', function () { return redirect()->route('layanan.ronda.index'); })->name('ronda.index');
    Route::post('/ronda/jadwal', [LayananMasyarakatController::class, 'storeJadwalRonda'])->name('ronda.jadwal.store');
    Route::post('/ronda/generate-preview', [LayananMasyarakatController::class, 'generatePreviewRonda'])->name('ronda.generate-preview');
    Route::post('/ronda/simpan-otomatis', [LayananMasyarakatController::class, 'simpanJadwalOtomatis'])->name('ronda.simpan-otomatis');
    Route::post('/ronda/jadwal/{jadwal}/ingatkan', [LayananMasyarakatController::class, 'kirimPengingatRonda'])->name('ronda.jadwal.ingatkan');
    Route::put('/ronda/jadwal/{jadwal}', [LayananMasyarakatController::class, 'updateJadwalRonda'])->name('ronda.jadwal.update');
    Route::delete('/ronda/jadwal/{jadwal}', [LayananMasyarakatController::class, 'destroyJadwalRonda'])->name('ronda.jadwal.destroy');
    Route::post('/ronda/absensi', [LayananMasyarakatController::class, 'storeAbsensiRonda'])->name('ronda.absensi.store');
    Route::post('/ronda/tukar', [LayananMasyarakatController::class, 'requestTukarRonda'])->name('ronda.tukar.store');
    Route::patch('/ronda/tukar/{tukar}/respon', [LayananMasyarakatController::class, 'responTukarRonda'])->name('ronda.tukar.respon');
    Route::post('/ronda/panic', [LayananMasyarakatController::class, 'storePanicReport'])->name('ronda.panic.store');
    Route::patch('/ronda/panic/{panicReport}/status', [LayananMasyarakatController::class, 'updatePanicReportStatus'])->name('ronda.panic.status');

    Route::get('/posyandu', function () { return redirect()->route('layanan.posyandu.index'); })->name('posyandu.index');
    Route::post('/posyandu/jadwal', [LayananMasyarakatController::class, 'storeJadwalPosyandu'])->name('posyandu.jadwal.store');
    Route::put('/posyandu/jadwal/{jadwal}', [LayananMasyarakatController::class, 'updateJadwalPosyandu'])->name('posyandu.jadwal.update');
    Route::delete('/posyandu/jadwal/{jadwal}', [LayananMasyarakatController::class, 'destroyJadwalPosyandu'])->name('posyandu.jadwal.destroy');
    Route::post('/posyandu/balita', [LayananMasyarakatController::class, 'storeBalita'])->name('posyandu.balita.store');
    Route::put('/posyandu/balita/{balita}', [LayananMasyarakatController::class, 'updateBalita'])->name('posyandu.balita.update');
    Route::delete('/posyandu/balita/{balita}', [LayananMasyarakatController::class, 'destroyBalita'])->name('posyandu.balita.destroy');
    Route::post('/posyandu/pemeriksaan', [LayananMasyarakatController::class, 'storePemeriksaanBalita'])->name('posyandu.pemeriksaan.store');

    // ==================== NOTIFIKASI ====================
    Route::get('notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('notifikasi/{id}/read', [\App\Http\Controllers\NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('notifikasi/read-all', [\App\Http\Controllers\NotifikasiController::class, 'markAllRead'])->name('notifikasi.read-all');
});
