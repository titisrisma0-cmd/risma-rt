<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Kas;
use App\Models\Kegiatan;
use App\Models\PengajuanSurat;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\KartuKeluarga;
use App\Models\RtAudit;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Role;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RtController extends Controller
{
    public function index()
    {
        $this->ensureAdminRw();

        $rts = Rt::activeRt()
            ->with([
                'rw',
                'ketua.profile',
                'ketua.role',
                'kartuKeluargas',
                'wargaProfiles:id,rt_id,user_id,kartu_keluarga_id,no_kk,nik,nama_lengkap,jenis_kelamin',
                'wargaProfiles.user:id,name',
                'wargaProfiles.kartuKeluarga.kepalaKeluarga:id,name',
            ])
            ->withCount('wargaProfiles')
            ->orderBy('rw_id')->orderBy('kode_rt')->get();

        $rts->each(function (Rt $rt) {
            $keluarga = $rt->wargaProfiles
                ->groupBy(fn ($profile) => blank($profile->no_kk)
                    ? 'PERSON:'.($profile->nik ?: $profile->user_id)
                    : trim($profile->no_kk))
                ->map(function ($anggota, $noKk) {
                    $first = $anggota->first();

                    return [
                        'no_kk' => str_starts_with($noKk, 'PERSON:') ? '-' : $noKk,
                        'kepala_keluarga' => $first->kartuKeluarga?->kepalaKeluarga?->name
                            ?: $first->nama_lengkap
                            ?: $first->user?->name,
                        'anggota' => $anggota
                            ->map(fn ($profile) => $profile->nama_lengkap ?: $profile->user?->name)
                            ->filter()->sort()->values(),
                    ];
                })->sortBy('no_kk')->values();

            $kkCount = $rt->kartuKeluargas->count();
            $rt->setAttribute('keluarga_count', $kkCount > 0 ? $kkCount : $keluarga->count());
            $rt->setAttribute('keluarga', $keluarga);
        });

        return view('rts.index', [
            'rts' => $rts,
            'rws' => Rw::orderBy('kode_rw')->get(),
        ]);
    }

    public function show(Rt $rt)
    {
        $this->ensureAdminRw();

        $rt->load([
            'rw',
            'ketua.profile',
            'ketua.role',
            'kartuKeluargas.kepalaKeluarga',
            'kartuKeluargas.anggota.user',
            'wargaProfiles.user',
        ]);

        // 1. Profil RT & Ringkasan Statistik
        $stats = $rt->getStatistikKependudukan();
        $totalKk = $stats['total_kk'];
        $totalWarga = $stats['total_jiwa'];
        $totalLaki = $stats['laki_laki'];
        $totalPerempuan = $stats['perempuan'];
        $totalBelumDiketahui = $stats['belum_diketahui'];

        // 2. Keuangan RT
        $pemasukanKas = (float) Kas::where('rt_id', $rt->id)->where('jenis', 'masuk')->sum('nominal');
        $pengeluaranKas = (float) Kas::where('rt_id', $rt->id)->where('jenis', 'keluar')->sum('nominal');
        $saldoKas = $pemasukanKas - $pengeluaranKas;
        $recentKas = Kas::where('rt_id', $rt->id)->latest('tanggal')->take(10)->get();
        $recentPembayaran = Pembayaran::with(['user', 'iuran'])
            ->whereHas('user.profile', fn($q) => $q->where('rt_id', $rt->id))
            ->where('status', 'lunas')
            ->latest('tanggal_bayar')
            ->take(10)
            ->get();
        $recentPengeluaran = Pengeluaran::where('rt_id', $rt->id)->latest('tanggal')->take(10)->get();
        $auditBulanIni = RtAudit::where('rt_id', $rt->id)->where('tahun', date('Y'))->where('bulan', date('n'))->first();

        // 3. Kegiatan RT
        $kegiatanRt = Kegiatan::where('rt_id', $rt->id)->latest('tanggal_mulai')->take(10)->get();

        // 4. Layanan RT
        $layananSurat = PengajuanSurat::with(['user', 'jenisSurat'])
            ->where('rt_id', $rt->id)
            ->latest()
            ->take(10)
            ->get();

        // 5. Riwayat Aktivitas / Audit Trail
        $aktivitas = AuditLog::with('user')
            ->where('rt_id', $rt->id)
            ->latest()
            ->take(20)
            ->get();

        return view('rts.show', compact(
            'rt', 'totalKk', 'totalWarga', 'totalLaki', 'totalPerempuan', 'totalBelumDiketahui',
            'pemasukanKas', 'pengeluaranKas', 'saldoKas', 'recentKas',
            'recentPembayaran', 'recentPengeluaran', 'auditBulanIni',
            'kegiatanRt', 'layananSurat', 'aktivitas'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureAdminRw();
        $data = $request->validate([
            'rw_id' => 'required|exists:rws,id',
            'kode_rt' => 'required|string|max:10',
            'nama_rt' => 'nullable|string|max:255',
        ]);
        $request->validate(['kode_rt' => 'unique:rts,kode_rt,NULL,id,rw_id,'.$data['rw_id']]);
        Rt::create($data);

        return back()->with('success', 'RT berhasil ditambahkan.');
    }

    public function update(Request $request, Rt $rt)
    {
        $this->ensureAdminRw();
        $data = $request->validate([
            'rw_id' => 'required|exists:rws,id',
            'kode_rt' => 'required|string|max:10|unique:rts,kode_rt,'.$rt->id.',id,rw_id,'.$request->input('rw_id'),
            'nama_rt' => 'nullable|string|max:255',
        ]);
        $rt->update($data);

        return back()->with('success', 'RT berhasil diperbarui.');
    }

    public function destroy(Rt $rt)
    {
        $this->ensureAdminRw();
        abort_if($rt->wargaProfiles()->exists(), 422, 'RT yang masih memiliki warga tidak dapat dihapus.');
        $rt->delete();

        return back()->with('success', 'RT berhasil dihapus.');
    }

    public function storeKetua(Request $request, Rt $rt)
    {
        $this->ensureAdminRw();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'no_hp' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        $kodeRt = str_pad($rt->kode_rt, 2, '0', STR_PAD_LEFT);
        $roleRt = Role::firstOrCreate(['nama_role' => 'rt_' . $kodeRt]);

        DB::transaction(function () use ($validated, $rt, $roleRt) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $roleRt->id,
                'rt_id' => $rt->id,
            ]);

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'role_id' => $roleRt->id,
                    'rt_id' => $rt->id,
                    'rw_id' => $rt->rw_id,
                    'nama_lengkap' => $validated['name'],
                    'no_hp' => $validated['no_hp'] ?? null,
                    'status' => 'aktif',
                ]
            );

            $rt->update(['ketua_user_id' => $user->id]);
        });

        return back()->with('success', 'Akun Ketua RT ' . $rt->kode_rt . ' berhasil dibuat.');
    }

    public function updateKetua(Request $request, Rt $rt, User $user)
    {
        $this->ensureAdminRw();
        abort_unless((int) $rt->ketua_user_id === (int) $user->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'no_hp' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama_lengkap' => $validated['name'],
                'no_hp' => $validated['no_hp'] ?? null,
            ]
        );

        return back()->with('success', 'Data Ketua RT ' . $rt->kode_rt . ' berhasil diperbarui.');
    }

    public function toggleKetuaStatus(Rt $rt, User $user)
    {
        $this->ensureAdminRw();
        abort_unless((int) $rt->ketua_user_id === (int) $user->id, 404);

        $profile = $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['nama_lengkap' => $user->name, 'status' => 'aktif']
        );

        $newStatus = $profile->status === 'aktif' ? 'nonaktif' : 'aktif';
        $profile->update(['status' => $newStatus]);

        return back()->with('success', 'Status akun Ketua RT ' . $rt->kode_rt . ' berhasil diubah menjadi ' . $newStatus . '.');
    }

    public function resetKetuaPassword(Request $request, Rt $rt, User $user)
    {
        $this->ensureAdminRw();
        abort_unless((int) $rt->ketua_user_id === (int) $user->id, 404);

        $validated = $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password Ketua RT ' . $rt->kode_rt . ' berhasil direset.');
    }

    private function ensureAdminRw(): void
    {
        abort_unless(Auth::user()->isAdminRw(), 403);
    }
}
