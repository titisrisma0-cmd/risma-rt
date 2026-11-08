<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RtVerifikasiController extends Controller
{
    public function permohonan(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt(), 403, 'Hanya Ketua RT yang dapat mengakses menu verifikasi.');

        $rtId = $user->managedRtId();
        $search = $request->input('q');

        $baseQuery = PengajuanSurat::with(['user.profile', 'jenisSurat', 'approver', 'rwApprover'])
            ->where('rt_id', $rtId)
            ->when($search, function ($q) use ($search) {
                $kw = '%' . $search . '%';
                $q->where(function ($sub) use ($kw) {
                    $sub->where('nomor_pengajuan', 'like', $kw)
                        ->orWhere('nomor_surat', 'like', $kw)
                        ->orWhere('keperluan', 'like', $kw)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $kw)->orWhereHas('profile', fn ($p) => $p->where('nik', 'like', $kw)));
                });
            });

        // 7 Tabs
        // 1. Permohonan Baru (pending)
        $permohonanBaru = (clone $baseQuery)->where('status', 'pending')->latest()->get();

        // 2. Menunggu Verifikasi RT (menunggu_rt)
        $menungguRt = (clone $baseQuery)->where('status', 'menunggu_rt')->latest()->get();

        // 3. Sedang Diproses (diproses / diproses_rt)
        $sedangDiproses = (clone $baseQuery)->whereIn('status', ['diproses', 'diproses_rt'])->latest()->get();

        // 4. Diteruskan ke RW (menunggu_rw / menunggu_persetujuan_rw / kebutuhan_rw)
        $diteruskanRw = (clone $baseQuery)->where(function ($q) {
            $q->whereIn('status', ['menunggu_rw', 'menunggu_persetujuan_rw'])
              ->orWhere('kebutuhan_rw', true);
        })->latest()->get();

        // 5. Disetujui (siap_diambil / disetujui)
        $disetujui = (clone $baseQuery)->whereIn('status', ['siap_diambil', 'disetujui'])->latest()->get();

        // 6. Ditolak
        $ditolak = (clone $baseQuery)->where('status', 'ditolak')->latest()->get();

        // 7. Selesai
        $selesai = (clone $baseQuery)->where('status', 'selesai')->latest()->get();

        // Riwayat Persetujuan Grouped by Bulan dan Tahun (Accordion)
        $riwayatSemua = (clone $baseQuery)
            ->whereIn('status', ['siap_diambil', 'disetujui', 'ditolak', 'selesai'])
            ->orderByDesc('created_at')
            ->get();

        $riwayatGrouped = $riwayatSemua->groupBy(function ($item) {
            return $item->created_at->isoFormat('MMMM Y');
        });

        $activeTab = $request->input('tab', 'baru');

        return view('rt.verifikasi.permohonan', compact(
            'user',
            'permohonanBaru',
            'menungguRt',
            'sedangDiproses',
            'diteruskanRw',
            'disetujui',
            'ditolak',
            'selesai',
            'riwayatGrouped',
            'activeTab',
            'search'
        ));
    }

    public function diteruskan(Request $request)
    {
        // Redirect to unified module tab diteruskan
        return redirect()->route('rt.verifikasi.permohonan', ['tab' => 'diteruskan']);
    }

    public function riwayat(Request $request)
    {
        // Redirect to unified module tab riwayat
        return redirect()->route('rt.verifikasi.permohonan', ['tab' => 'riwayat']);
    }

    public function teruskanKeRw(Request $request, PengajuanSurat $pengajuanSurat)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt(), 403);
        abort_unless((int) $pengajuanSurat->rt_id === (int) $user->managedRtId(), 403);

        $validated = $request->validate([
            'catatan_rt' => ['required', 'string', 'max:1000'],
        ]);

        $pengajuanSurat->update([
            'status' => 'menunggu_rw',
            'kebutuhan_rw' => true,
            'target_level' => 'rw',
            'catatan_rt' => $validated['catatan_rt'],
            'rt_approved_by' => $user->id,
            'rt_approved_at' => now(),
        ]);

        AuditLog::record(
            action: 'eskalasi_rw',
            entityType: 'pengajuan_surat',
            entityId: $pengajuanSurat->id,
            deskripsi: "Ketua RT meneruskan permohonan surat {$pengajuanSurat->nomor_pengajuan} ke RW. Catatan RT: {$validated['catatan_rt']}",
            rtId: $pengajuanSurat->rt_id,
            userId: $user->id
        );

        $rwUsers = User::whereHas('role', fn ($r) => $r->whereIn('nama_role', ['admin', 'rw', 'admin_rw']))->get();
        foreach ($rwUsers as $rwUser) {
            Notifikasi::create([
                'user_id' => $rwUser->id,
                'from_user_id' => $user->id,
                'tipe' => 'surat',
                'judul' => 'Pengajuan Diteruskan ke RW',
                'pesan' => "Ketua RT {$user->getRtKode()} meneruskan permohonan surat ({$pengajuanSurat->nomor_pengajuan}) untuk persetujuan RW. Catatan: {$validated['catatan_rt']}",
                'dibaca' => false,
            ]);
        }

        Notifikasi::create([
            'user_id' => $pengajuanSurat->user_id,
            'from_user_id' => $user->id,
            'tipe' => 'surat',
            'judul' => 'Permohonan Diteruskan ke RW',
            'pesan' => "Permohonan surat Anda telah diverifikasi oleh Ketua RT dan saat ini diteruskan ke tingkat RW untuk persetujuan.",
            'dibaca' => false,
        ]);

        return back()->with('success', 'Permohonan berhasil diteruskan ke tingkat RW.');
    }
}
