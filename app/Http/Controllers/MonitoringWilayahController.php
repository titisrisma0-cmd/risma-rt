<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Kegiatan;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\Rt;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringWilayahController extends Controller
{
    public function wargaPerRt(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $rts = Rt::activeRt()->withCount('kartuKeluargas')->orderBy('kode_rt')->get();
        $selectedRt = null;
        $wargas = collect();
        $stats = null;

        if ($request->filled('rt_id')) {
            $selectedRt = Rt::find($request->rt_id);
            if ($selectedRt) {
                $stats = $selectedRt->getStatistikKependudukan();
                $query = UserProfile::with(['user', 'kartuKeluarga'])
                    ->where('rt_id', $selectedRt->id);

                if ($request->filled('q')) {
                    $search = '%' . $request->q . '%';
                    $query->where(function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', $search)
                            ->orWhere('nik', 'like', $search)
                            ->orWhere('no_kk', 'like', $search)
                            ->orWhere('alamat', 'like', $search);
                    });
                }

                $wargas = $query->latest()->paginate(20)->withQueryString();
            }
        }

        return view('monitoring.warga', compact('user', 'rts', 'selectedRt', 'wargas', 'stats'));
    }

    public function kegiatanPerRt(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $rts = Rt::activeRt()->orderBy('kode_rt')->get();
        $query = Kegiatan::with(['rt', 'pembuat'])->latest('tanggal_mulai');

        if ($request->filled('rt_id')) {
            $query->where('rt_id', $request->rt_id);
        }

        $kegiatans = $query->paginate(15)->withQueryString();

        return view('monitoring.kegiatan', compact('user', 'rts', 'kegiatans'));
    }

    public function layananPerRt(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $rts = Rt::orderBy('kode_rt')->get();
        $query = PengajuanSurat::with(['rt', 'user.profile', 'jenisSurat'])->latest();

        if ($request->filled('rt_id')) {
            $query->where('rt_id', $request->rt_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuans = $query->paginate(20)->withQueryString();

        return view('monitoring.layanan', compact('user', 'rts', 'pengajuans'));
    }

    public function pengajuanRw(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw(), 403);

        $statusFilter = $request->query('status');

        $query = PengajuanSurat::with(['rt', 'user.profile', 'jenisSurat', 'rtApprover', 'rwApprover']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        } else {
            // Tampilkan seluruh pengajuan yang butuh otorisasi RW atau telah dieskalasi ke RW
            $query->where(function ($q) {
                $q->whereIn('status', ['menunggu_rw', 'menunggu_persetujuan_rw'])
                  ->orWhere('kebutuhan_rw', true);
            });
        }

        // Prioritaskan pengajuan yang masih menunggu keputusan RW di urutan teratas
        $pengajuans = $query->orderByRaw("CASE WHEN status IN ('menunggu_rw', 'menunggu_persetujuan_rw') THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'total' => PengajuanSurat::where(function ($q) {
                $q->whereIn('status', ['menunggu_rw', 'menunggu_persetujuan_rw'])
                  ->orWhere('kebutuhan_rw', true);
            })->count(),
            'menunggu' => PengajuanSurat::whereIn('status', ['menunggu_rw', 'menunggu_persetujuan_rw'])->count(),
            'disetujui' => PengajuanSurat::where('kebutuhan_rw', true)->where('status', 'disetujui')->count(),
            'ditolak' => PengajuanSurat::where('kebutuhan_rw', true)->where('status', 'ditolak')->count(),
        ];

        return view('monitoring.persetujuan-rw', compact('user', 'pengajuans', 'counts', 'statusFilter'));
    }

    public function approvePengajuanRw(Request $request, PengajuanSurat $pengajuanSurat)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw(), 403);

        // Validasi status idempotency: jika pengajuan sudah memiliki status final, tolak proses perubahan status
        if (in_array($pengajuanSurat->status, ['disetujui', 'ditolak', 'selesai', 'siap_diambil'])) {
            return back()->with('error', 'Pengajuan ini sudah diputuskan sebelumnya.');
        }

        // Pastikan hanya status menunggu_rw atau menunggu_persetujuan_rw yang bisa diproses
        if (!in_array($pengajuanSurat->status, ['menunggu_rw', 'menunggu_persetujuan_rw'])) {
            return back()->with('error', 'Pengajuan ini belum siap untuk diputuskan oleh RW.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak'],
            'catatan_rw' => ['nullable', 'string', 'max:1000'],
        ]);

        $statusAkhir = $validated['status'];
        $nomorSurat = $pengajuanSurat->nomor_surat;
        if ($statusAkhir === 'disetujui' && empty($nomorSurat)) {
            $nomorSurat = 'RW/SUR/' . now()->format('Y/m') . '/' . str_pad((string) $pengajuanSurat->id, 4, '0', STR_PAD_LEFT);
        }

        $pengajuanSurat->update([
            'status' => $statusAkhir,
            'catatan_rw' => $validated['catatan_rw'] ?? null,
            'rw_approved_by' => $user->id,
            'rw_approved_at' => now(),
            'approved_by' => $user->id,
            'approved_at' => now(),
            'nomor_surat' => $nomorSurat,
        ]);

        // Audit log
        AuditLog::record(
            action: 'approval_rw_' . $statusAkhir,
            entityType: 'pengajuan_surat',
            entityId: $pengajuanSurat->id,
            deskripsi: "RW memproses permohonan surat {$pengajuanSurat->nomor_pengajuan} menjadi " . strtoupper($statusAkhir) . ". Catatan: " . ($validated['catatan_rw'] ?: '-'),
            rtId: $pengajuanSurat->rt_id,
            userId: $user->id
        );

        // Notify citizen
        Notifikasi::create([
            'user_id' => $pengajuanSurat->user_id,
            'from_user_id' => $user->id,
            'tipe' => 'surat',
            'judul' => 'Keputusan Persetujuan RW',
            'pesan' => "Permohonan surat {$pengajuanSurat->nomor_pengajuan} telah diputuskan oleh Pengurus RW: " . strtoupper($statusAkhir) . ". " . ($validated['catatan_rw'] ? "Catatan: {$validated['catatan_rw']}" : ''),
            'dibaca' => false,
        ]);

        // Notify Ketua RT
        if ($pengajuanSurat->rt && $pengajuanSurat->rt->ketua_user_id) {
            Notifikasi::create([
                'user_id' => $pengajuanSurat->rt->ketua_user_id,
                'from_user_id' => $user->id,
                'tipe' => 'surat',
                'judul' => 'Hasil Eskalasi RW',
                'pesan' => "Pengajuan warga {$pengajuanSurat->nomor_pengajuan} ({$pengajuanSurat->user?->name}) telah ditindaklanjuti RW dengan status: " . strtoupper($statusAkhir),
                'dibaca' => false,
            ]);
        }

        return back()->with('success', "Permohonan surat {$pengajuanSurat->nomor_pengajuan} berhasil diproses dengan status: " . strtoupper($statusAkhir) . ".");
    }
}
