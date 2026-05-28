<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Iuran;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Models\Pengeluaran;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Total Pemasukan
        $totalPemasukan = Kas::where('jenis', 'masuk')->sum('nominal');
        
        // Total Pengeluaran
        $totalPengeluaran = Pengeluaran::sum('nominal');
        
        // Saldo Kas
        $saldoKas = $totalPemasukan - $totalPengeluaran;
        
        // Total Warga
        $totalWarga = User::whereHas('role', function($q) {
            $q->where('nama_role', 'warga');
        })->count();
        
        // Total Iuran Aktif
        $activeIuran = Iuran::where('status', 'aktif')
            ->orderBy('nama_iuran')
            ->orderBy('nominal')
            ->orderBy('periode')
            ->get();
        $totalIuranAktif = Iuran::dedupeCollection($activeIuran)->count();
        
        // Pembayaran Bulan Ini
        $pembayaranBulanIni = Pembayaran::whereMonth('tanggal_bayar', date('m'))
            ->whereYear('tanggal_bayar', date('Y'))
            ->where('status', 'lunas')
            ->sum('jumlah_bayar');
        
        // Grafik pemasukan dan pengeluaran per bulan
        $chartData = [];
        $expenseChartData = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = Pembayaran::whereMonth('tanggal_bayar', $bulan)
                ->whereYear('tanggal_bayar', date('Y'))
                ->where('status', 'lunas')
                ->sum('jumlah_bayar');
            $chartData[] = $total;

            $expenseTotal = Pengeluaran::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', date('Y'))
                ->sum('nominal');
            $expenseChartData[] = $expenseTotal;
        }
        
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Pembayaran Terbaru
        $recentPembayaran = Pembayaran::with(['user', 'iuran'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Statistik per iuran, gabungkan entri iuran yang identik agar tidak muncul duplikat
        $statistikIuran = Iuran::withCount(['pembayaran as total_bayar' => function($q) {
            $q->where('status', 'lunas');
        }])->orderBy('nama_iuran')->orderBy('nominal')->orderBy('periode')->get()
            ->groupBy(function ($item) {
                return implode('|', [
                    strtolower(trim($item->nama_iuran)),
                    $item->nominal,
                    $item->periode,
                ]);
            })
            ->map(function ($group) {
                $representative = $group->sortByDesc(function ($item) {
                    return $item->status === 'aktif' ? 1 : 0;
                })->first();
                $representative->total_bayar = $group->sum('total_bayar');
                return $representative;
            })
            ->values();
        
        // TOP 5 WARGA DENGAN PEMBAYARAN TERBANYAK - PAKAI QUERY RAW
        $topWarga = DB::table('users')
            ->select('users.id', 'users.name', 'users.email', DB::raw('COALESCE(SUM(pembayarans.jumlah_bayar), 0) as total_bayar'))
            ->leftJoin('pembayarans', function($join) {
                $join->on('users.id', '=', 'pembayarans.user_id')
                     ->where('pembayarans.status', '=', 'lunas');
            })
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.nama_role', 'warga')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_bayar', 'desc')
            ->limit(5)
            ->get();
        
        // Pengeluaran terbaru
        $recentPengeluaran = Pengeluaran::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Notifikasi untuk user saat ini
        $notifikasis = [];
        if (Auth::check()) {
            $notifikasis = Notifikasi::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }
        
        return view('dashboard', compact(
            'totalPemasukan', 'totalPengeluaran', 'saldoKas',
            'totalWarga', 'totalIuranAktif', 'pembayaranBulanIni',
            'chartData', 'expenseChartData', 'bulanLabels', 'recentPembayaran',
            'statistikIuran', 'topWarga', 'recentPengeluaran'
        ))->with('notifikasis', $notifikasis);
    }
}