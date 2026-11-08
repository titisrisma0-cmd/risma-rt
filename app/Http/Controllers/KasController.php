<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kas;
use App\Models\Pengeluaran;
use App\Models\Rt;
use Illuminate\Support\Facades\Auth;

class KasController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $currentRt = $user->managedRt ?? Rt::find($rtId);
            $namaKas = 'Kas ' . ($currentRt ? ($currentRt->nama_rt ?: 'RT ' . $user->getRtKode()) : 'RT');

            $kasMasuk = Kas::where('jenis', 'masuk')
                ->where('rt_id', $rtId)
                ->orderBy('tanggal', 'desc')
                ->get();
            
            $kasKeluar = Pengeluaran::with('creator')
                ->where('rt_id', $rtId)
                ->orderBy('tanggal', 'desc')
                ->get();
            
            $saldoMasuk = Kas::where('jenis', 'masuk')->where('rt_id', $rtId)->sum('nominal');
            $saldoKeluar = Pengeluaran::where('rt_id', $rtId)->sum('nominal');
            $saldoAkhir = $saldoMasuk - $saldoKeluar;
            $rts = collect();
        } else {
            $namaKas = 'Kas RW';
            $rts = Rt::orderBy('kode_rt')->get();

            $queryMasuk = Kas::where('jenis', 'masuk');
            $queryKeluar = Pengeluaran::with('creator');

            // Filter per RT jika diminta oleh RW/Bendahara
            if ($request->filled('rt_id')) {
                $queryMasuk->where('rt_id', $request->rt_id);
                $queryKeluar->where('rt_id', $request->rt_id);
                $rtFilter = Rt::find($request->rt_id);
                if ($rtFilter) {
                    $namaKas = 'Kas ' . ($rtFilter->nama_rt ?: 'RT ' . $rtFilter->kode_rt);
                }
            }

            $kasMasuk = $queryMasuk->orderBy('tanggal', 'desc')->get();
            $kasKeluar = $queryKeluar->orderBy('tanggal', 'desc')->get();

            $saldoMasuk = (clone $queryMasuk)->sum('nominal');
            $saldoKeluar = (clone $queryKeluar)->sum('nominal');
            $saldoAkhir = $saldoMasuk - $saldoKeluar;
        }
        
        return view('kas.index', compact(
            'kasMasuk',
            'kasKeluar',
            'saldoMasuk',
            'saldoKeluar',
            'saldoAkhir',
            'namaKas',
            'rts'
        ));
    }
}
