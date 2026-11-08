<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Kas;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Rt;
use App\Models\RtAudit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara') || $user->isAdminRt(), 403);

        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('n'));

        // If RT, show their own RT audit status and summary
        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $rt = $user->managedRt ?? Rt::find($rtId);
            
            $audit = RtAudit::firstOrCreate(
                ['rt_id' => $rtId, 'tahun' => $tahun, 'bulan' => $bulan],
                ['status' => 'belum_diperiksa']
            );

            // Compute actual financials for this RT in the given period
            $pemasukan = (float) Kas::where('rt_id', $rtId)
                ->where('jenis', 'masuk')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal');

            $pengeluaran = (float) Kas::where('rt_id', $rtId)
                ->where('jenis', 'keluar')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal');

            $saldoKumulatif = (float) Kas::where('rt_id', $rtId)
                ->where('jenis', 'masuk')
                ->sum('nominal') - (float) Kas::where('rt_id', $rtId)
                ->where('jenis', 'keluar')
                ->sum('nominal');

            $audit->update([
                'total_pemasukan' => $pemasukan,
                'total_pengeluaran' => $pengeluaran,
                'saldo_kas' => $saldoKumulatif,
            ]);

            $auditLogs = AuditLog::with('user')
                ->where('rt_id', $rtId)
                ->latest()
                ->take(15)
                ->get();

            return view('keuangan.audit-rt', compact('user', 'rt', 'audit', 'tahun', 'bulan', 'auditLogs', 'pemasukan', 'pengeluaran', 'saldoKumulatif'));
        }

        // RW & Bendahara view: Recap per RT
        $rts = Rt::activeRt()->with('ketua.profile')->orderBy('kode_rt')->get();
        $rekapPerRt = [];

        foreach ($rts as $rt) {
            $pemasukan = (float) Kas::where('rt_id', $rt->id)
                ->where('jenis', 'masuk')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal');

            $pengeluaran = (float) Kas::where('rt_id', $rt->id)
                ->where('jenis', 'keluar')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('nominal');

            $saldoKumulatif = (float) Kas::where('rt_id', $rt->id)
                ->where('jenis', 'masuk')
                ->sum('nominal') - (float) Kas::where('rt_id', $rt->id)
                ->where('jenis', 'keluar')
                ->sum('nominal');

            $audit = RtAudit::firstOrCreate(
                ['rt_id' => $rt->id, 'tahun' => $tahun, 'bulan' => $bulan],
                [
                    'total_pemasukan' => $pemasukan,
                    'total_pengeluaran' => $pengeluaran,
                    'saldo_kas' => $saldoKumulatif,
                    'status' => 'belum_diperiksa',
                ]
            );

            // Update live numbers if status is still unverified
            if (in_array($audit->status, ['belum_diperiksa', 'sedang_diperiksa', 'perlu_perbaikan'])) {
                $audit->update([
                    'total_pemasukan' => $pemasukan,
                    'total_pengeluaran' => $pengeluaran,
                    'saldo_kas' => $saldoKumulatif,
                ]);
            }

            $rekapPerRt[] = [
                'rt' => $rt,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran,
                'saldo' => $saldoKumulatif,
                'audit' => $audit,
            ];
        }

        $totalPemasukanAll = collect($rekapPerRt)->sum('pemasukan');
        $totalPengeluaranAll = collect($rekapPerRt)->sum('pengeluaran');
        $totalSaldoAll = collect($rekapPerRt)->sum('saldo');

        $selectedRtId = $request->input('rt_id');

        // Tab 2: Iuran
        $iuranQuery = Pembayaran::with(['user.profile', 'iuran'])
            ->where('status', 'lunas')
            ->whereYear('tanggal_bayar', $tahun)
            ->whereMonth('tanggal_bayar', $bulan);
        if ($selectedRtId) {
            $iuranQuery->whereHas('user.profile', fn($q) => $q->where('rt_id', $selectedRtId));
        }
        $iuranList = $iuranQuery->latest('tanggal_bayar')->take(30)->get();

        // Tab 3: Pengeluaran
        $pengeluaranQuery = Pengeluaran::with('creator')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);
        if ($selectedRtId) {
            $pengeluaranQuery->where('rt_id', $selectedRtId);
        }
        $pengeluaranList = $pengeluaranQuery->latest('tanggal')->take(30)->get();

        // Tab 4: Kas RT
        $kasQuery = Kas::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
        if ($selectedRtId) {
            $kasQuery->where('rt_id', $selectedRtId);
        }
        $kasList = $kasQuery->latest('tanggal')->take(30)->get();

        // Tab 6: Riwayat Audit
        $auditHistory = AuditLog::with(['user', 'rt'])
            ->where(function ($q) {
                $q->where('action', 'audit_review')
                  ->orWhere('entity_type', 'rt_audit');
            })
            ->latest()
            ->take(30)
            ->get();

        return view('keuangan.audit-rw', compact(
            'user', 'tahun', 'bulan', 'rekapPerRt', 'rts',
            'totalPemasukanAll', 'totalPengeluaranAll', 'totalSaldoAll',
            'iuranList', 'pengeluaranList', 'kasList', 'auditHistory', 'selectedRtId'
        ));
    }

    public function showRt(Request $request, Rt $rt)
    {
        $user = Auth::user();
        if ($user->isAdminRt()) {
            abort_unless($rt->id === $user->managedRtId(), 403);
        } else {
            abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);
        }

        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('n'));

        $audit = RtAudit::firstOrCreate(
            ['rt_id' => $rt->id, 'tahun' => $tahun, 'bulan' => $bulan],
            ['status' => 'belum_diperiksa']
        );

        // Fetch recent transactions for this RT
        $kasList = Kas::where('rt_id', $rt->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->latest('tanggal')
            ->paginate(20);

        // Fetch audit trail for this RT
        $auditLogs = AuditLog::with('user')
            ->where('rt_id', $rt->id)
            ->latest()
            ->take(30)
            ->get();

        return view('keuangan.audit-detail-rt', compact('rt', 'audit', 'tahun', 'bulan', 'kasList', 'auditLogs', 'user'));
    }

    public function auditLog(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara') || $user->isAdminRt(), 403);

        $query = AuditLog::with(['user', 'rt']);

        if ($user->isAdminRt()) {
            $query->where('rt_id', $user->managedRtId());
        } elseif ($request->filled('rt_id')) {
            $query->where('rt_id', $request->rt_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('deskripsi', 'like', "%{$kw}%")
                  ->orWhere('action', 'like', "%{$kw}%");
            });
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $rts = Rt::activeRt()->orderBy('kode_rt')->get();
        $actors = User::select('id', 'name')->orderBy('name')->get();

        return view('keuangan.audit-logs', compact('logs', 'user', 'rts', 'actors'));
    }

    public function updateStatus(Request $request, Rt $rt)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403, 'Hanya pengurus RW yang dapat melakukan verifikasi audit.');

        $validated = $request->validate([
            'tahun' => ['required', 'integer'],
            'bulan' => ['required', 'integer', 'between:1,12'],
            'status' => ['required', 'in:belum_diperiksa,sedang_diperiksa,perlu_perbaikan,terverifikasi,ditolak'],
            'catatan_rw' => ['nullable', 'string', 'required_if:status,perlu_perbaikan', 'required_if:status,ditolak'],
        ], [
            'catatan_rw.required_if' => 'Catatan audit wajib diisi apabila status dipilih Perlu Perbaikan atau Ditolak.',
        ]);

        $audit = RtAudit::updateOrCreate(
            ['rt_id' => $rt->id, 'tahun' => $validated['tahun'], 'bulan' => $validated['bulan']],
            [
                'status' => $validated['status'],
                'catatan_rw' => $validated['catatan_rw'] ?? null,
                'audited_by' => $user->id,
                'audited_at' => now(),
            ]
        );

        // Record into audit trail
        AuditLog::record(
            action: 'audit_review',
            entityType: 'rt_audit',
            entityId: $audit->id,
            deskripsi: "Pemeriksaan audit keuangan RT {$rt->kode_rt} periode {$validated['bulan']}/{$validated['tahun']} diset ke: {$audit->status_label}. Catatan: " . ($validated['catatan_rw'] ?: '-'),
            rtId: $rt->id,
            userId: $user->id
        );

        // Notify Ketua RT
        if ($rt->ketua_user_id) {
            Notifikasi::create([
                'user_id' => $rt->ketua_user_id,
                'from_user_id' => $user->id,
                'tipe' => 'audit',
                'judul' => 'Hasil Audit Keuangan dari RW',
                'pesan' => "Audit keuangan RT {$rt->kode_rt} periode {$validated['bulan']}/{$validated['tahun']} telah diperiksa oleh RW dengan status: {$audit->status_label}. " . ($validated['catatan_rw'] ? "Catatan: {$validated['catatan_rw']}" : ''),
                'dibaca' => false,
            ]);
        }

        return back()->with('success', "Status audit RT {$rt->kode_rt} berhasil diperbarui.");
    }
}
