<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Laporan;
use App\Models\Kas;
use App\Models\User;
use App\Models\Iuran;
use App\Models\Rt;
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
        $user = Auth::user();

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $laporan = Laporan::with('creator')
                ->where('created_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $warga = User::whereHas('role', function ($q) {
                $q->where('nama_role', 'warga');
            })->whereHas('profile', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            })->get();
        } else {
            $laporan = Laporan::with('creator')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // RW does not manage per-citizen report directly
            $warga = collect();
        }
        
        $iuran = Iuran::dedupeCollection(
            Iuran::where('status', 'aktif')
                ->orderBy('nama_iuran')
                ->orderBy('nominal')
                ->orderBy('periode')
                ->get()
        );

        $rts = Rt::activeRt()->orderBy('kode_rt')->get();
        
        return view('laporan.index', compact('laporan', 'warga', 'iuran', 'rts', 'user'));
    }
    
    public function generate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'jenis_laporan' => 'required',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
            'rt_id' => 'nullable|exists:rts,id',
        ]);
        
        $periodeAwal = $request->periode_awal;
        $periodeAkhir = $request->periode_akhir;
        $rtId = $request->rt_id;
        
        $queryMasuk = Kas::where('jenis', 'masuk')->whereBetween('tanggal', [$periodeAwal, $periodeAkhir]);
        $queryKeluar = Kas::where('jenis', 'keluar')->whereBetween('tanggal', [$periodeAwal, $periodeAkhir]);

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $queryMasuk->where('rt_id', $rtId);
            $queryKeluar->where('rt_id', $rtId);
        } elseif ($rtId) {
            $queryMasuk->where('rt_id', $rtId);
            $queryKeluar->where('rt_id', $rtId);
        }
        
        $pemasukan = $queryMasuk->sum('nominal');
        $pengeluaran = $queryKeluar->sum('nominal');
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
            return $this->exportExcel($periodeAwal, $periodeAkhir, $request->jenis_laporan, $rtId);
        } elseif ($request->has('export_pdf')) {
            return $this->exportPdf($periodeAwal, $periodeAkhir, $request->jenis_laporan, $rtId);
        }
        
        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil dibuat.');
    }
    
    public function exportExcel($periodeAwal, $periodeAkhir, $jenisLaporan, $rtId = null)
    {
        $user = Auth::user();
        $query = Pembayaran::with(['user.profile', 'iuran'])
            ->whereBetween('tanggal_bayar', [$periodeAwal, $periodeAkhir])
            ->where('status', 'lunas');

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $query->whereHas('user.profile', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            });
        } elseif ($rtId) {
            $query->whereHas('user.profile', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            });
        }
        
        $pembayaran = $query->get();
        
        return Excel::download(new LaporanExport($pembayaran, $periodeAwal, $periodeAkhir, $jenisLaporan), 
            'laporan_' . $jenisLaporan . '_' . $periodeAwal . '_to_' . $periodeAkhir . '.xlsx');
    }
    
    public function exportPdf($periodeAwal, $periodeAkhir, $jenisLaporan, $rtId = null)
    {
        $user = Auth::user();
        $queryBayar = Pembayaran::with(['user.profile', 'iuran'])
            ->whereBetween('tanggal_bayar', [$periodeAwal, $periodeAkhir])
            ->where('status', 'lunas');

        $queryMasuk = Kas::where('jenis', 'masuk')->whereBetween('tanggal', [$periodeAwal, $periodeAkhir]);
        $queryKeluar = Kas::where('jenis', 'keluar')->whereBetween('tanggal', [$periodeAwal, $periodeAkhir]);

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $queryBayar->whereHas('user.profile', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            });
            $queryMasuk->where('rt_id', $rtId);
            $queryKeluar->where('rt_id', $rtId);
        } elseif ($rtId) {
            $queryBayar->whereHas('user.profile', function ($q) use ($rtId) {
                $q->where('rt_id', $rtId);
            });
            $queryMasuk->where('rt_id', $rtId);
            $queryKeluar->where('rt_id', $rtId);
        }

        $pembayaran = $queryBayar->get();
        $pemasukan = $queryMasuk->sum('nominal');
        $pengeluaran = $queryKeluar->sum('nominal');
        
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
        return $pdf->download('laporan_' . $jenisLaporan . '_' . $periodeAwal . '.pdf');
    }
    
    public function laporanPerWarga(Request $request)
    {
        $userAuth = Auth::user();

        $request->validate([
            'user_id' => 'required',
            'periode_awal' => 'required|date',
            'periode_akhir' => 'required|date|after_or_equal:periode_awal',
        ]);

        $targetUser = User::with('profile')->findOrFail($request->user_id);

        if ($userAuth->isAdminRt()) {
            $rtId = $userAuth->managedRtId();
            abort_if(
                (int) optional($targetUser->profile)->rt_id !== (int) $rtId,
                403,
                'Akses ditolak - Warga tidak berada di wilayah RT Anda.'
            );
        }
        
        $pembayaran = Pembayaran::with(['iuran'])
            ->where('user_id', $request->user_id)
            ->whereBetween('tanggal_bayar', [$request->periode_awal, $request->periode_akhir])
            ->where('status', 'lunas')
            ->get();
        
        $user = $targetUser;
        
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
