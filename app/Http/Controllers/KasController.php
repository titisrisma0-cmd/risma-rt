<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kas;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\Auth;

class KasController extends Controller
{
    public function index()
    {
        // Kas Masuk (Pemasukan) - HAPUS relasi yang bermasalah
        $kasMasuk = Kas::where('jenis', 'masuk')
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Kas Keluar (Pengeluaran)
        $kasKeluar = Pengeluaran::with('creator')
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Perhitungan saldo
        $saldoMasuk = Kas::where('jenis', 'masuk')->sum('nominal');
        $saldoKeluar = Pengeluaran::sum('nominal');
        $saldoAkhir = $saldoMasuk - $saldoKeluar;
        
        return view('kas.index', compact('kasMasuk', 'kasKeluar', 'saldoMasuk', 'saldoKeluar', 'saldoAkhir'));
    }
}