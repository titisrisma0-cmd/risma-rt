<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Iuran;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Models\Pengeluaran;
use App\Models\Notifikasi;
use App\Models\Rt;
use App\Models\UserProfile;
use App\Models\Kegiatan;
use App\Models\Aspirasi;
use App\Models\PengajuanSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Dashboard Khusus Ketua RT
        if ($user && $user->isAdminRt()) {
            return $this->rtDashboard($user);
        }

        // 2. Dashboard Khusus Warga
        if ($user && $user->isWarga()) {
            return $this->wargaDashboard($user);
        }

        // 3. Dashboard RW / Admin / Bendahara
        return $this->rwDashboard($user);
    }

    private function wargaDashboard($user)
    {
        $user->loadMissing(['profile.wilayahRt', 'profile.kartuKeluarga.anggota']);
        $rtKode = $user->getRtKode() ?? optional(optional($user->profile)->wilayahRt)->kode_rt ?? '01';
        $kartuKeluarga = $user->kartu_keluarga;

        $tagihanPending = Pembayaran::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $totalBayarLunas = Pembayaran::where('user_id', $user->id)
            ->where('status', 'lunas')
            ->sum('jumlah_bayar');

        $riwayatPembayaran = Pembayaran::with('iuran')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $suratSaya = PengajuanSurat::with('jenisSurat')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $pengumumanRt = Kegiatan::where(function ($q) use ($user) {
            $rtId = optional($user->profile)->rt_id;
            $q->where('cakupan', 'rw')
              ->orWhere(fn ($sub) => $sub->where('cakupan', 'rt')->where('rt_id', $rtId));
        })
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        $notifikasis = Notifikasi::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.warga', compact(
            'user',
            'rtKode',
            'kartuKeluarga',
            'tagihanPending',
            'totalBayarLunas',
            'riwayatPembayaran',
            'suratSaya',
            'pengumumanRt',
            'notifikasis'
        ));
    }

    private function rtDashboard($user)
    {
        $rtId = $user->managedRtId();
        $rt = $user->managedRt ?? Rt::find($rtId);
        $rtKode = $user->getRtKode() ?? ($rt ? $rt->kode_rt : '01');
        $namaRt = $rt ? ($rt->nama_rt ?: 'RT ' . $rtKode) : 'RT ' . $rtKode;

        // Kartu Statistik RT (Konsisten & Akurat)
        $stats = Rt::getStatistikKependudukanWilayah($rtId);
        $totalKk = $stats['total_kk'];
        $totalWarga = $stats['total_jiwa'];
        $totalLakiLaki = $stats['laki_laki'];
        $totalPerempuan = $stats['perempuan'];
        $totalBelumDiketahui = $stats['belum_diketahui'];

        $totalIuranTerkumpul = Pembayaran::where('status', 'lunas')
            ->whereHas('user.profile', fn ($q) => $q->where('rt_id', $rtId))
            ->sum('jumlah_bayar');

        $totalTunggakan = Pembayaran::whereIn('status', ['pending', 'unpaid', 'menunggu'])
            ->whereHas('user.profile', fn ($q) => $q->where('rt_id', $rtId))
            ->sum('jumlah_bayar');

        $kasMasukRt = Kas::where('rt_id', $rtId)
            ->where('jenis', 'masuk')
            ->sum('nominal');

        $kasKeluarRt = Pengeluaran::where('rt_id', $rtId)
            ->sum('nominal');

        $saldoKasRt = $kasMasukRt - $kasKeluarRt;

        // Pengumuman dari RW
        $pengumumanRw = Kegiatan::where('cakupan', 'rw')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Aktivitas Pembayaran Terbaru RT
        $recentPembayaran = Pembayaran::with(['user', 'iuran'])
            ->whereHas('user.profile', fn ($q) => $q->where('rt_id', $rtId))
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Agenda Kegiatan RT
        $agendaKegiatan = Kegiatan::where('cakupan', 'rt')
            ->where('rt_id', $rtId)
            ->orderBy('tanggal_mulai', 'desc')
            ->limit(5)
            ->get();

        // Informasi Warga RT
        $wargaTerbaru = User::whereHas('role', fn ($q) => $q->where('nama_role', 'warga'))
            ->whereHas('profile', fn ($q) => $q->where('rt_id', $rtId))
            ->with('profile')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Aspirasi dan Pengaduan Warga RT
        $aspirasiWarga = Aspirasi::where('rt_id', $rtId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pengeluaran Kas RT Terbaru
        $recentPengeluaran = Pengeluaran::where('rt_id', $rtId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Notifikasi User
        $notifikasis = Notifikasi::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.rt', compact(
            'user',
            'rt',
            'rtId',
            'rtKode',
            'namaRt',
            'totalKk',
            'totalWarga',
            'totalLakiLaki',
            'totalPerempuan',
            'totalBelumDiketahui',
            'totalIuranTerkumpul',
            'totalTunggakan',
            'saldoKasRt',
            'kasMasukRt',
            'kasKeluarRt',
            'pengumumanRw',
            'recentPembayaran',
            'agendaKegiatan',
            'wargaTerbaru',
            'aspirasiWarga',
            'recentPengeluaran',
            'notifikasis'
        ));
    }

    private function rwDashboard($user)
    {
        // 1. Statistik Utama Wilayah RW
        $totalRtAktif = Rt::activeRt()->count();

        $statsRw = Rt::getStatistikKependudukanWilayah(null);
        $totalKk = $statsRw['total_kk'];
        $totalWarga = $statsRw['total_jiwa'];
        $totalLakiLaki = $statsRw['laki_laki'];
        $totalPerempuan = $statsRw['perempuan'];
        $totalBelumDiketahui = $statsRw['belum_diketahui'];
        
        $totalPemasukan = Kas::where('jenis', 'masuk')->sum('nominal');
        $totalPengeluaran = Pengeluaran::sum('nominal');
        $saldoKas = $totalPemasukan - $totalPengeluaran;

        $laporanMenungguAudit = \App\Models\RtAudit::where('status', 'belum_diperiksa')
            ->where('tahun', date('Y'))
            ->where('bulan', date('n'))
            ->count();

        $pengajuanMenungguRw = PengajuanSurat::where('status', 'diteruskan_ke_rw')->count();

        $kegiatanRwTerdekat = Kegiatan::where('cakupan', 'rw')
            ->where('tanggal_mulai', '>=', now()->toDateString())
            ->orderBy('tanggal_mulai')
            ->first();

        // 2. Grafik Pemasukan vs Pengeluaran Bulanan
        $chartData = [];
        $expenseChartData = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = Kas::where('jenis', 'masuk')
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', date('Y'))
                ->sum('nominal');
            $chartData[] = $total;

            $expenseTotal = Pengeluaran::whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', date('Y'))
                ->sum('nominal');
            $expenseChartData[] = $expenseTotal;
        }
        
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        // 3. Notifikasi
        $notifikasis = collect();
        if (Auth::check()) {
            $notifikasis = Notifikasi::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // 4. Monitoring Wilayah Cepat: RT 01 sampai RT 08
        $rts = Rt::activeRt()->with(['ketua.profile'])->orderBy('kode_rt')->get();
        $rtStats = $rts->map(function ($rt) {
            $stats = $rt->getStatistikKependudukan();

            $kasMasuk = Kas::where('rt_id', $rt->id)->where('jenis', 'masuk')->sum('nominal');
            $kasKeluar = Pengeluaran::where('rt_id', $rt->id)->sum('nominal');
            $saldoKasRt = $kasMasuk - $kasKeluar;

            $auditTerakhir = \App\Models\RtAudit::where('rt_id', $rt->id)
                ->orderBy('tahun', 'desc')
                ->orderBy('bulan', 'desc')
                ->first();

            return (object) [
                'id' => $rt->id,
                'kode_rt' => $rt->kode_rt,
                'nama_rt' => $rt->nama_rt ?: 'RT ' . $rt->kode_rt,
                'ketua_nama' => optional($rt->ketua)->name ?: 'Belum Ada',
                'ketua_hp' => optional(optional($rt->ketua)->profile)->no_hp ?? '-',
                'total_kk' => $stats['total_kk'],
                'total_warga' => $stats['total_jiwa'],
                'laki_laki' => $stats['laki_laki'],
                'perempuan' => $stats['perempuan'],
                'belum_diketahui' => $stats['belum_diketahui'],
                'saldo_kas' => $saldoKasRt,
                'audit' => $auditTerakhir,
            ];
        });
        
        return view('dashboard', compact(
            'totalRtAktif', 'totalKk', 'totalWarga', 'totalLakiLaki',
            'totalPerempuan', 'totalBelumDiketahui', 'totalPemasukan',
            'totalPengeluaran', 'saldoKas', 'laporanMenungguAudit',
            'pengajuanMenungguRw', 'kegiatanRwTerdekat',
            'chartData', 'expenseChartData', 'bulanLabels', 'rtStats'
        ))->with('notifikasis', $notifikasis);
    }
}
