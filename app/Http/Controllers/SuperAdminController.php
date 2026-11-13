<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\KartuKeluarga;
use App\Models\Aspirasi;
use App\Models\Kegiatan;
use App\Models\Umkm;
use App\Models\Kas;
use App\Models\AuditLog;

class SuperAdminController extends Controller
{
    /**
     * Dashboard Utama Super Admin
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $statRw = Rw::count();
        $statRt = Rt::count();
        $statWarga = UserProfile::where(function ($q) {
            $q->where('status', 'aktif')->orWhereNull('status');
        })->whereNotIn('status', ['pindah', 'meninggal'])->count();
        $statPengguna = User::count();
        $statPengurus = User::whereHas('role', function ($q) {
            $q->whereIn('nama_role', ['admin', 'rw', 'admin_rw', 'rt', 'admin_rt', 'ketua_rt', 'bendahara', 'super_admin']);
        })->count();
        $statKk = KartuKeluarga::count();
        $statAspirasi = Aspirasi::count();
        $statAspirasiPending = Aspirasi::whereIn('status', ['menunggu', 'pending'])->count();
        $statKegiatan = Kegiatan::count();
        $statUmkm = Umkm::count();
        $saldoKas = (float) (\App\Models\Kas::where('jenis', 'masuk')->sum('nominal') - \App\Models\Pengeluaran::sum('nominal'));

        $recentLogs = AuditLog::with(['user', 'rt'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $allRts = Rt::with('rw')->orderBy('kode_rt')->get();
        $allRws = Rw::orderBy('kode_rw')->get();
        $sampleWarga = User::whereHas('role', fn ($q) => $q->where('nama_role', 'warga'))
            ->with(['profile.wilayahRt'])
            ->orderBy('name')
            ->limit(30)
            ->get();

        return view('superadmin.dashboard', compact(
            'user',
            'statRw',
            'statRt',
            'statWarga',
            'statPengguna',
            'statPengurus',
            'statKk',
            'statAspirasi',
            'statAspirasiPending',
            'statKegiatan',
            'statUmkm',
            'saldoKas',
            'recentLogs',
            'allRts',
            'allRws',
            'sampleWarga'
        ));
    }

    /**
     * Beralih Peran (Role Switching) Tanpa Logout
     */
    public function switchRole(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Hanya Super Admin yang diizinkan beralih peran.');

        $validated = $request->validate([
            'mode' => 'required|in:super_admin,rw,rt,warga',
            'rt_id' => 'nullable|integer|exists:rts,id',
            'rw_id' => 'nullable|integer|exists:rws,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $mode = $validated['mode'];

        // Mode 1: Kembali ke Super Admin Asli
        if ($mode === 'super_admin') {
            session()->forget('super_admin_context');
            AuditLog::record(
                'switch_role',
                'user',
                $user->id,
                'Kembali ke mode Super Admin Asli (Akses Penuh)',
                null,
                null,
                null,
                $user->id
            );

            return redirect()->route('superadmin.dashboard')
                ->with('success', 'Kembali ke mode Super Admin. Seluruh akses penuh dan data master aktif.');
        }

        // Mode 2: Simulasi Ketua / Pengurus RW
        if ($mode === 'rw') {
            $rw = ! empty($validated['rw_id']) ? Rw::find($validated['rw_id']) : Rw::first();
            $rwKode = $rw ? $rw->kode_rw : '01';
            $label = "Ketua RW {$rwKode}";

            session([
                'super_admin_context' => [
                    'mode' => 'rw',
                    'rw_id' => $rw?->id,
                    'label' => $label,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            AuditLog::record(
                'switch_role',
                'user',
                $user->id,
                "Beralih ke mode simulasi: {$label}",
                null,
                null,
                null,
                $user->id
            );

            return redirect()->route('dashboard')
                ->with('success', "Mode Simulasi Aktif: {$label}. Dashboard dan sidebar kini menampilkan portal RW.");
        }

        // Mode 3: Simulasi Ketua RT
        if ($mode === 'rt') {
            $rtId = $validated['rt_id'] ?? null;
            $rt = $rtId ? Rt::with('rw')->find($rtId) : Rt::with('rw')->first();

            if (! $rt) {
                return redirect()->back()->with('error', 'Unit RT tidak ditemukan.');
            }

            $rwKode = optional($rt->rw)->kode_rw ?? '01';
            $label = "Ketua RT {$rt->kode_rt} / RW {$rwKode}";

            session([
                'super_admin_context' => [
                    'mode' => 'rt',
                    'rt_id' => $rt->id,
                    'rw_id' => $rt->rw_id,
                    'label' => $label,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            AuditLog::record(
                'switch_role',
                'user',
                $user->id,
                "Beralih ke mode simulasi: {$label}",
                null,
                null,
                $rt->id,
                $user->id
            );

            return redirect()->route('dashboard')
                ->with('success', "Mode Simulasi Aktif: {$label}. Seluruh fitur dibatasi pada unit RT {$rt->kode_rt}.");
        }

        // Mode 4: Simulasi Warga Biasa
        if ($mode === 'warga') {
            $targetUser = null;
            $rtId = null;
            $label = 'Warga';

            if (! empty($validated['user_id'])) {
                $targetUser = User::with('profile')->find($validated['user_id']);
                if ($targetUser) {
                    $rtId = $targetUser->managedRtId();
                    $rtKode = $targetUser->getRtKode() ?? '01';
                    $label = "Warga: {$targetUser->name} (RT {$rtKode})";
                }
            }

            if (! $targetUser) {
                $rt = ! empty($validated['rt_id']) ? Rt::find($validated['rt_id']) : Rt::first();
                $rtId = $rt?->id;
                $rtKode = $rt?->kode_rt ?? '01';
                $label = "Warga RT {$rtKode}";
            }

            session([
                'super_admin_context' => [
                    'mode' => 'warga',
                    'rt_id' => $rtId,
                    'warga_user_id' => $targetUser?->id,
                    'label' => $label,
                    'created_at' => now()->toIso8601String(),
                ],
            ]);

            AuditLog::record(
                'switch_role',
                'user',
                $user->id,
                "Beralih ke mode simulasi: {$label}",
                null,
                null,
                $rtId,
                $user->id
            );

            return redirect()->route('dashboard')
                ->with('success', "Mode Simulasi Aktif: {$label}. Anda kini menjelajah sistem sebagai Warga.");
        }

        return redirect()->back()->with('error', 'Pilihan mode tidak valid.');
    }

    /**
     * Tampilan Log Audit Sistem
     */
    public function auditLogs(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->isSuperAdmin(), 403, 'Akses khusus Super Admin.');

        $query = AuditLog::with(['user', 'rt'])->orderBy('id', 'desc');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('action', 'like', "%{$q}%")
                    ->orWhere('deskripsi', 'like', "%{$q}%")
                    ->orWhere('entity_type', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('superadmin.audit-logs', compact('logs'));
    }
}
