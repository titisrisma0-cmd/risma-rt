<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Laporan;
use App\Models\Kas;
use App\Models\User;
use App\Models\Iuran;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use App\Exports\PembayaranExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        $laporan = Laporan::with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $warga = User::whereHas('role', function($q) {
            $q->where('nama_role', 'warga');
        })->get();
        
        $iuran = Iuran::where('status', 'aktif')->get();
        
        return view('laporan.index', compact('laporan', 'warga', 'iuran'));
    }
    
    public function generate(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
        ]);
        
        $periodeAwal = $request->periode_awal;
        $periodeAkhir = $request->periode_akhir;
        
        $pemasukan = Kas::where('jenis', 'masuk')
            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
            ->sum('nominal');
        
        $pengeluaran = Kas::where('jenis', 'keluar')
            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
            ->sum('nominal');
        
        $saldoAkhir = $pemasukan - $pengeluaran;
        
        $kodeLaporan = 'LAP' . date('YmdHis');
        
        $laporan = Laporan::create([
            'kode_laporan' => $kodeLaporan,
            'jenis_laporan' => $request->jenis_laporan,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'total_pemasukan' => $pemasukan,
            'total_pengeluaran' => $pengeluaran,
            'saldo_akhir' => $saldoAkhir,
            'created_by' => Auth::id(),
        ]);
        
        if ($request->has('export_excel')) {
            return $this->exportExcel($periodeAwal, $periodeAkhir, $request->jenis_laporan);
        } elseif ($request->has('export_pdf')) {
            return $this->exportPdf($periodeAwal, $periodeAkhir, $request->jenis_laporan);
        }
        
        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil digenerate');
    }
    
    public function exportExcel($periodeAwal, $periodeAkhir, $jenisLaporan)
    {
        $pembayaran = Pembayaran::with(['user', 'iuran'])
            ->whereBetween('tanggal_bayar', [$periodeAwal, $periodeAkhir])
            ->where('status', 'lunas')
            ->get();
        
        return Excel::download(new LaporanExport($pembayaran, $periodeAwal, $periodeAkhir, $jenisLaporan), 
            'laporan_iuran_' . $periodeAwal . '_to_' . $periodeAkhir . '.xlsx');
    }
    
    public function exportPdf($periodeAwal, $periodeAkhir, $jenisLaporan)
    {
        $pembayaran = Pembayaran::with(['user', 'iuran'])
            ->whereBetween('tanggal_bayar', [$periodeAwal, $periodeAkhir])
            ->where('status', 'lunas')
            ->get();
        
        $pemasukan = Kas::where('jenis', 'masuk')
            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
            ->sum('nominal');
        
        $pengeluaran = Kas::where('jenis', 'keluar')
            ->whereBetween('tanggal', [$periodeAwal, $periodeAkhir])
            ->sum('nominal');
        
        $data = [
            'pembayaran' => $pembayaran,
            'periode_awal' => $periodeAwal,
            'periode_akhir' => $periodeAkhir,
            'jenis_laporan' => $jenisLaporan,
            'total_pemasukan' => $pemasukan,
            'total_pengeluaran' => $pengeluaran,
            'saldo_akhir' => $pemasukan - $pengeluaran,
        ];
        
        $pdf = Pdf::loadView('laporan.pdf', $data);
        return $pdf->download('laporan_iuran_' . $periodeAwal . '.pdf');
    }
    
    public function laporanPerWarga(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
        ]);
        
        $pembayaran = Pembayaran::with(['iuran'])
            ->where('user_id', $request->user_id)
            ->whereBetween('tanggal_bayar', [$request->periode_awal, $request->periode_akhir])
            ->where('status', 'lunas')
            ->get();
        
        $user = User::find($request->user_id);
        
        if ($request->has('export_excel')) {
            return Excel::download(new PembayaranExport($pembayaran, $user, $request->periode_awal, $request->periode_akhir),
                'laporan_warga_' . $user->name . '.xlsx');
        }
        
        return view('laporan.per_warga', compact('pembayaran', 'user', 'request'));
    }
    
    public function show($id)
    {
        $laporan = Laporan::with('creator')->findOrFail($id);
        return view('laporan.show', compact('laporan'));
    }
    
    public function destroy($id)
    {
        $laporan = Laporan::findOrFail($id);
        $laporan->delete();
        
        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil dihapus');
    }
}