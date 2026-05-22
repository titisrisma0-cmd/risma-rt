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
    
    // ==================== PROFILE ROUTES ====================
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');
    Route::patch('/profile/kependudukan', [ProfileController::class, 'updateKependudukan'])->name('profile.update.kependudukan');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ==================== DATA WARGA ====================
    Route::resource('warga', WargaController::class);
    
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
});