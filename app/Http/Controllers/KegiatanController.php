<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Kegiatan;
use App\Models\KegiatanPeserta;
use App\Models\Rt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('profile');
        $bulan = (int) $request->input('bulan', date('n'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $status = $request->input('status');
        $search = $request->input('q');
        $cakupan = $request->input('cakupan') ?: $request->input('tingkat');

        $baseQuery = Kegiatan::with(['rt', 'rw', 'pembuat', 'peserta.user.profile'])
            ->withCount('peserta')
            ->latest('tanggal_mulai');

        if (! $this->isRw($user)) {
            $rtId = $this->getRtId($user);
            $rwId = $user->profile?->rw_id;
            $baseQuery->where(function ($q) use ($rtId, $rwId) {
                $q->where('cakupan', 'rw')
                    ->orWhere(fn ($sub) => $sub->where('cakupan', 'rt')->where('rt_id', $rtId));
            });
        }

        if ($cakupan) {
            $baseQuery->where('cakupan', $cakupan);
        }

        if ($search) {
            $kw = '%' . $search . '%';
            $baseQuery->where(function ($q) use ($kw) {
                $q->where('judul', 'like', $kw)
                  ->orWhere('deskripsi', 'like', $kw)
                  ->orWhere('lokasi', 'like', $kw);
            });
        }

        if ($status) {
            $baseQuery->where('status', $status);
        }

        // 1. Kegiatan Bulan Ini (Berdasarkan filter bulan & tahun terpilih)
        $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endOfMonth = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $kegiatanBulanIni = (clone $baseQuery)
            ->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
            ->get();

        // 2. Kegiatan Bulan Mendatang
        $kegiatanBulanMendatang = (clone $baseQuery)
            ->where('tanggal_mulai', '>', $endOfMonth)
            ->get();

        // 3. Kegiatan Bulan Sebelumnya / Arsip
        $kegiatanBulanSebelumnya = (clone $baseQuery)
            ->where('tanggal_mulai', '<', $startOfMonth)
            ->get();

        // Full filtered paginated list
        $allKegiatans = (clone $baseQuery)->paginate(12)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($allKegiatans);
        }

        return view('layanan.kegiatan', [
            'kegiatanBulanIni' => $kegiatanBulanIni,
            'kegiatanBulanMendatang' => $kegiatanBulanMendatang,
            'kegiatanBulanSebelumnya' => $kegiatanBulanSebelumnya,
            'allKegiatans' => $allKegiatans,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'status' => $status,
            'search' => $search,
            'canManage' => $this->canCreate($user),
            'isRw' => $this->isRw($user),
            'isRt' => $this->isRt($user),
            'rts' => $this->isRw($user) ? Rt::activeRt()->orderBy('kode_rt')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user()->loadMissing('profile.role');
        abort_unless($this->canCreate($user), 403);
        $data = $this->validated($request);
        $data = $this->applyScope($data, $user);
        $data += ['created_by' => $user->id, 'creator_role' => optional($user->role)->nama_role];

        $kegiatan = Kegiatan::create($data);

        AuditLog::record(
            action: 'kegiatan_create',
            entityType: 'kegiatan',
            entityId: $kegiatan->id,
            deskripsi: "Kegiatan baru '{$kegiatan->judul}' dibuat oleh {$user->name}",
            rtId: $kegiatan->rt_id,
            userId: $user->id
        );

        return $this->respond($request, $kegiatan, 'Kegiatan berhasil dibuat.', 201);
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $user = $request->user()->loadMissing('profile.role');
        abort_unless($this->canEdit($user, $kegiatan), 403);
        $data = $this->applyScope($this->validated($request), $user, $kegiatan);
        $kegiatan->update($data);

        AuditLog::record(
            action: 'kegiatan_update',
            entityType: 'kegiatan',
            entityId: $kegiatan->id,
            deskripsi: "Data kegiatan '{$kegiatan->judul}' diperbarui oleh {$user->name}",
            rtId: $kegiatan->rt_id,
            userId: $user->id
        );

        return $this->respond($request, $kegiatan->fresh(), 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kegiatan $kegiatan)
    {
        $user = $request->user()->loadMissing('profile');
        abort_unless($this->canEdit($user, $kegiatan), 403);
        $kegiatan->update(['status' => 'dibatalkan']);

        AuditLog::record(
            action: 'kegiatan_batal',
            entityType: 'kegiatan',
            entityId: $kegiatan->id,
            deskripsi: "Status kegiatan '{$kegiatan->judul}' diubah menjadi Dibatalkan oleh {$user->name}",
            rtId: $kegiatan->rt_id,
            userId: $user->id
        );

        return back()->with('success', "Kegiatan '{$kegiatan->judul}' berhasil dibatalkan.");
    }

    public function softDeleteCancelled(Request $request, Kegiatan $kegiatan)
    {
        $user = $request->user()->loadMissing('profile');
        abort_unless($this->canEdit($user, $kegiatan), 403, 'Anda tidak memiliki hak untuk menghapus kegiatan ini.');
        abort_unless($kegiatan->status === 'dibatalkan', 422, 'Hanya kegiatan yang berstatus Dibatalkan yang dapat dihapus dari daftar.');

        $kegiatan->update(['deleted_by' => $user->id]);
        $kegiatan->delete();

        AuditLog::record(
            action: 'soft_delete_kegiatan',
            entityType: 'kegiatan',
            entityId: $kegiatan->id,
            deskripsi: "Kegiatan '{$kegiatan->judul}' (Dibatalkan) dihapus dengan soft delete oleh {$user->name} pada " . now()->format('d/m/Y H:i'),
            rtId: $kegiatan->rt_id,
            userId: $user->id
        );

        return back()->with('success', "Kegiatan '{$kegiatan->judul}' berhasil dihapus dan dicatat dalam audit trail.");
    }

    public function daftar(Request $request, Kegiatan $kegiatan)
    {
        $actor = $request->user()->loadMissing('profile.kartuKeluarga');
        abort_unless(in_array($kegiatan->status, ['dibuka', 'berlangsung']), 422, 'Pendaftaran belum dibuka.');
        $targetId = (int) $request->input('user_id', $actor->id);
        $this->assertCanRegister($actor, $targetId);
        abort_if($kegiatan->kuota_peserta && $kegiatan->peserta()->count() >= $kegiatan->kuota_peserta && ! $kegiatan->peserta()->where('user_id', $targetId)->exists(), 422, 'Kuota peserta sudah penuh.');

        $peserta = KegiatanPeserta::firstOrCreate(['kegiatan_id' => $kegiatan->id, 'user_id' => $targetId], ['status' => 'terdaftar']);
        return $this->respond($request, $peserta->load('user.profile'), 'Pendaftaran kegiatan berhasil disimpan.', 201);
    }

    public function batalDaftar(Request $request, Kegiatan $kegiatan)
    {
        $actor = $request->user()->loadMissing('profile.kartuKeluarga');
        $targetId = (int) $request->input('user_id', $actor->id);
        $this->assertCanRegister($actor, $targetId);
        KegiatanPeserta::where(['kegiatan_id' => $kegiatan->id, 'user_id' => $targetId])->delete();
        return $this->respond($request, $kegiatan, 'Pendaftaran kegiatan dibatalkan.');
    }

    public function peserta(Request $request, Kegiatan $kegiatan)
    {
        abort_unless($this->canViewPeserta($request->user()->loadMissing('profile'), $kegiatan), 403);
        $peserta = $kegiatan->peserta()->with('user.profile')->get();
        return $request->expectsJson() ? response()->json($peserta) : view('layanan.kegiatan-peserta', compact('kegiatan', 'peserta'));
    }

    public function updatePeserta(Request $request, Kegiatan $kegiatan, User $user)
    {
        abort_unless($this->canViewPeserta($request->user()->loadMissing('profile'), $kegiatan), 403);
        $data = $request->validate(['status' => ['required', 'in:terdaftar,hadir,tidak_hadir']]);
        $peserta = KegiatanPeserta::where(['kegiatan_id' => $kegiatan->id, 'user_id' => $user->id])->firstOrFail();
        $peserta->update($data);
        return $this->respond($request, $peserta, 'Status kehadiran diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'], 'deskripsi' => ['nullable', 'string'],
            'kategori' => ['required', 'in:kerja_bakti,sosial,keagamaan,olahraga,rapat,lainnya'],
            'cakupan' => ['required', 'in:rt,rw'], 'rt_id' => ['nullable', 'exists:rts,id'],
            'tanggal_mulai' => ['required', 'date'], 'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi' => ['required', 'string', 'max:255'], 'kuota_peserta' => ['nullable', 'integer', 'min:1'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'status' => ['required', 'in:draft,dibuka,berlangsung,selesai,dibatalkan'],
        ]);
    }

    private function applyScope(array $data, User $user, ?Kegiatan $current = null): array
    {
        if ($this->isRt($user) && ! $this->isRw($user)) {
            $data['cakupan'] = 'rt';
            $data['rt_id'] = $this->getRtId($user);
            $data['rw_id'] = $user->profile?->rw_id ?? 1;
            abort_unless($data['rt_id'], 422, 'Akun RT belum terhubung ke wilayah RT.');
        } else {
            $data['rw_id'] = $user->profile?->rw_id ?? $current?->rw_id ?? 1;
            if ($data['cakupan'] === 'rw') { $data['rt_id'] = null; }
            if ($data['cakupan'] === 'rt') {
                $rt = Rt::findOrFail($data['rt_id']);
                abort_unless($rt->rw_id === $data['rw_id'] || $rt->rw_id === null, 422, 'RT harus berada di RW yang sama.');
            }
        }
        return $data;
    }

    private function canCreate(User $user): bool { return $this->isRw($user) || $this->isRt($user); }
    private function isRw(User $user): bool { return $user->isAdminRw(); }
    private function isRt(User $user): bool { return $user->isAdminRt(); }
    private function getRtId(User $user): ?int { return $user->managedRtId() ?? $user->rt_id ?? $user->profile?->rt_id; }
    private function canEdit(User $user, Kegiatan $kegiatan): bool {
        if ($this->isRw($user)) return true;
        if ($this->isRt($user)) {
            return $kegiatan->cakupan === 'rt' && (int) $kegiatan->rt_id === (int) $this->getRtId($user);
        }
        return false;
    }
    private function canViewPeserta(User $user, Kegiatan $kegiatan): bool {
        if ($this->isRw($user)) return true;
        if ($this->isRt($user)) {
            return $kegiatan->cakupan === 'rt' && (int) $kegiatan->rt_id === (int) $this->getRtId($user);
        }
        return false;
    }

    private function assertCanRegister(User $actor, int $targetId): void
    {
        if ($targetId === $actor->id) return;
        $kk = $actor->profile?->kartuKeluarga;
        abort_unless($kk && $kk->kepala_keluarga_user_id === $actor->id && $kk->anggota()->where('user_id', $targetId)->exists(), 403);
    }

    private function respond(Request $request, $data, string $message, int $status = 200)
    {
        return $request->expectsJson() ? response()->json($data, $status) : redirect()->route('kegiatan.index')->with('success', $message);
    }
}
