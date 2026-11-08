<?php

namespace App\Http\Controllers;

use App\Models\Aspirasi;
use App\Models\AspirasiLampiran;
use App\Models\AspirasiTanggapan;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class AspirasiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->loadMissing('profile.kartuKeluarga');
        $isRw = $this->isRw($user);
        $isRt = $this->isRt($user);
        $canManage = $isRw || $isRt;

        $query = Aspirasi::with(['pelapor.profile', 'lampirans', 'tanggapans.user', 'rt'])->latest();
        $this->scope($query, $user);

        $aspirasis = $query
            ->when($request->kategori, fn ($q, $v) => $q->where('kategori', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->get();

        $aspirasis->each(fn ($a) => $this->hideIdentity($a, $user));

        if ($request->expectsJson()) {
            return response()->json($aspirasis);
        }

        $rekap = [
            'total' => $aspirasis->count(),
            'baru' => $aspirasis->where('status', 'terkirim')->count(),
            'diproses' => $aspirasis->whereIn('status', ['diproses', 'ditanggapi', 'ditindaklanjuti'])->count(),
            'dieskalasi' => $aspirasis->where('status', 'dieskalasi')->count(),
            'selesai' => $aspirasis->where('status', 'selesai')->count(),
        ];

        return view('layanan.aspirasi', [
            'aspirasis' => $aspirasis,
            'canManage' => $canManage,
            'isRt' => $isRt,
            'isRw' => $isRw,
            'rekap' => $rekap,
            'statusFilter' => $request->status,
            'kategoriFilter' => $request->kategori,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user()->loadMissing('profile');
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kategori' => 'required|in:infrastruktur,kebersihan,keamanan,sosial,keluhan_pelayanan,usulan,lainnya',
            'anonim' => 'nullable|boolean',
            'lokasi' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto.*' => 'nullable|image|max:4096',
        ]);

        $rtId = $this->getRtId($user);

        $a = Aspirasi::create($data + [
            'user_id' => $user->id,
            'rt_id' => $rtId,
            'rw_id' => $user->profile?->rw_id,
            'kartu_keluarga_id' => $user->profile?->kartu_keluarga_id,
            'anonim' => $request->boolean('anonim'),
            'status' => 'terkirim',
        ]);

        foreach ($request->file('foto', []) as $file) {
            AspirasiLampiran::create([
                'aspirasi_id' => $a->id,
                'file_path' => $file->store('aspirasi', 'public'),
                'file_type' => $file->getClientMimeType(),
            ]);
        }

        // Notify RT Head
        if ($rtId) {
            $rtUsers = User::whereHas('role', fn ($r) => $r->whereIn('nama_role', ['ketua_rt', 'admin']))->get();
            foreach ($rtUsers as $rtUser) {
                if ($rtUser->managedRtId() === $rtId) {
                    Notifikasi::create([
                        'user_id' => $rtUser->id,
                        'from_user_id' => $user->id,
                        'tipe' => 'aspirasi',
                        'judul' => 'Aspirasi Warga Baru',
                        'pesan' => "Aspirasi baru masuk dari warga: '{$a->judul}' (Kategori: " . ucfirst($a->kategori) . ").",
                        'dibaca' => false,
                    ]);
                }
            }
        }

        return $this->respond($request, $a, 'Aspirasi warga berhasil dikirim dan masuk antrean pengelolaan RT.', 201);
    }

    public function show(Request $request, Aspirasi $aspirasi)
    {
        $user = $request->user()->loadMissing('profile.kartuKeluarga');
        abort_unless($this->canSee($user, $aspirasi), 403);
        $aspirasi->load(['pelapor.profile', 'lampirans', 'tanggapans.user']);
        $this->hideIdentity($aspirasi, $user);

        return $request->expectsJson() ? response()->json($aspirasi) : view('layanan.aspirasi-detail', compact('aspirasi'));
    }

    public function tanggapan(Request $request, Aspirasi $aspirasi)
    {
        $user = $request->user()->loadMissing('profile');
        abort_unless($this->canManage($user, $aspirasi), 403);

        $data = $request->validate([
            'isi' => 'required|string',
            'status' => 'nullable|in:terkirim,diproses,ditanggapi,ditindaklanjuti,dieskalasi,selesai,ditolak',
        ]);

        $t = AspirasiTanggapan::create([
            'aspirasi_id' => $aspirasi->id,
            'user_id' => $user->id,
            'isi' => $data['isi'],
        ]);

        $updateData = ['assigned_to' => $user->id];
        if (! empty($data['status'])) {
            $updateData['status'] = $data['status'];
            if (in_array($data['status'], ['selesai', 'ditolak'], true)) {
                $updateData['resolved_at'] = now();
            }
        } elseif ($aspirasi->status === 'terkirim') {
            $updateData['status'] = 'ditanggapi';
        }

        $aspirasi->update($updateData);

        // Notify pelapor
        if ($aspirasi->user_id) {
            Notifikasi::create([
                'user_id' => $aspirasi->user_id,
                'from_user_id' => $user->id,
                'tipe' => 'aspirasi',
                'judul' => 'Respon Resmi Aspirasi',
                'pesan' => "Pengurus memberikan tanggapan resmi pada aspirasi Anda '{$aspirasi->judul}': {$data['isi']}",
                'dibaca' => false,
            ]);
        }

        return $this->respond($request, $t, 'Tanggapan resmi RT berhasil dikirim ke warga.', 201);
    }

    public function status(Request $request, Aspirasi $aspirasi)
    {
        $user = $request->user()->loadMissing('profile');
        abort_unless($this->canManage($user, $aspirasi), 403);

        $data = $request->validate([
            'status' => 'required|in:terkirim,diproses,ditanggapi,ditindaklanjuti,dieskalasi,selesai,ditolak',
            'solusi' => 'nullable|string',
        ]);

        $aspirasi->update($data + [
            'assigned_to' => $user->id,
            'resolved_at' => in_array($data['status'], ['selesai', 'ditolak']) ? now() : null,
        ]);

        // Notify Warga
        if ($aspirasi->user_id) {
            Notifikasi::create([
                'user_id' => $aspirasi->user_id,
                'from_user_id' => $user->id,
                'tipe' => 'aspirasi',
                'judul' => 'Status Aspirasi Diperbarui',
                'pesan' => "Status aspirasi Anda '{$aspirasi->judul}' kini: " . strtoupper(str_replace('_', ' ', $data['status'])) . ($data['solusi'] ? ". Solusi/Catatan: {$data['solusi']}" : ''),
                'dibaca' => false,
            ]);
        }

        return $this->respond($request, $aspirasi->fresh(), 'Status aspirasi berhasil diperbarui.');
    }

    public function eskalasi(Request $request, Aspirasi $aspirasi)
    {
        $user = $request->user()->loadMissing('profile');
        abort_unless($this->isRt($user) || $this->isRw($user), 403);
        abort_unless($this->canManage($user, $aspirasi), 403);

        $data = $request->validate([
            'target' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $aspirasi->update([
            'status' => 'dieskalasi',
            'escalated_at' => now(),
            'escalation_target' => $data['target'] ?? ($this->isRt($user) ? 'RW' : 'Instansi terkait'),
            'solusi' => $data['catatan'] ?? $aspirasi->solusi,
        ]);

        // Notify RW
        $rwUsers = User::whereHas('role', fn ($r) => $r->whereIn('nama_role', ['admin', 'rw', 'admin_rw']))->get();
        foreach ($rwUsers as $rwUser) {
            Notifikasi::create([
                'user_id' => $rwUser->id,
                'from_user_id' => $user->id,
                'tipe' => 'aspirasi',
                'judul' => 'Aspirasi Diteruskan ke RW',
                'pesan' => "Ketua RT {$user->getRtKode()} meneruskan aspirasi warga '{$aspirasi->judul}' untuk penanganan RW.",
                'dibaca' => false,
            ]);
        }

        return $this->respond($request, $aspirasi->fresh(), 'Aspirasi berhasil diteruskan ke tingkat RW.');
    }

    public function rekap(Request $request)
    {
        $user = $request->user()->loadMissing('profile');
        abort_unless($this->isRw($user) || $this->isRt($user), 403);

        $q = Aspirasi::query();
        if ($this->isRt($user) && ! $this->isRw($user)) {
            $q->where('rt_id', $this->getRtId($user));
        }

        $items = $q->get();
        $data = [
            'total' => $items->count(),
            'per_kategori' => $items->countBy('kategori'),
            'per_status' => $items->countBy('status'),
            'rata_rata_jam_selesai' => round($items->filter(fn ($a) => $a->resolved_at)->avg(fn ($a) => $a->created_at->diffInMinutes($a->resolved_at)) / 60, 1),
        ];

        return response()->json($data);
    }

    private function scope($q, $u)
    {
        if ($this->isRw($u)) {
            return;
        }

        if ($this->isRt($u)) {
            $rtId = $this->getRtId($u);
            $q->where(function ($sub) use ($rtId) {
                $sub->where('rt_id', $rtId)
                    ->orWhereHas('pelapor', fn ($p) => $p->where('rt_id', $rtId)->orWhereHas('profile', fn ($pr) => $pr->where('rt_id', $rtId)));
            });
            return;
        }

        $kk = $u->profile?->kartu_keluarga_id;
        $q->where(fn ($x) => $x->where('user_id', $u->id)->orWhere('kartu_keluarga_id', $kk));
    }

    private function canSee($u, $a)
    {
        if ($this->isRw($u)) {
            return true;
        }

        if ($this->isRt($u)) {
            return (int) $a->rt_id === (int) $this->getRtId($u);
        }

        return $a->user_id === $u->id || ($u->profile?->kartu_keluarga_id && $a->kartu_keluarga_id === $u->profile->kartu_keluarga_id);
    }

    private function canManage($u, $a)
    {
        return ($this->isRw($u) || $this->isRt($u)) && $this->canSee($u, $a);
    }

    private function isRw($u)
    {
        return $u->isAdminRw();
    }

    private function isRt($u)
    {
        return $u->isAdminRt();
    }

    private function getRtId($u)
    {
        return $u->managedRtId() ?? $u->rt_id ?? $u->profile?->rt_id;
    }

    private function hideIdentity($a, $u)
    {
        if ($a->anonim && $this->isRt($u) && ! $this->isRw($u)) {
            $a->setRelation('pelapor', null);
            $a->makeHidden(['user_id', 'kartu_keluarga_id']);
        }
    }

    private function respond($r, $data, $message, $status = 200)
    {
        return $r->expectsJson() ? response()->json($data, $status) : redirect()->route('aspirasi.index')->with('success', $message);
    }
}
