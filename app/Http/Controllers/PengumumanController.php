<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Notifikasi;
use App\Models\Pengumuman;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Pengumuman::with(['rt', 'pembuat']);

        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $query->where(function ($q) use ($rtId) {
                $q->where('target_audience', 'semua_rt')
                  ->orWhere('target_audience', 'seluruh_warga')
                  ->orWhere(fn ($sub) => $sub->whereIn('target_audience', ['rt_tertentu', 'warga_rt'])->where('rt_id', $rtId));
            })->where('status', 'published');
        } elseif ($user->isWarga()) {
            $rtId = optional($user->profile)->rt_id;
            $query->where(function ($q) use ($rtId) {
                $q->where('target_audience', 'seluruh_warga')
                  ->orWhere(fn ($sub) => $sub->where('target_audience', 'warga_rt')->where('rt_id', $rtId));
            })->where('status', 'published');
        } else {
            // RW / Admin can see all and filter
            if ($request->filled('kategori')) {
                $query->where('kategori', $request->kategori);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        }

        $pengumumans = $query->latest('tanggal_publikasi')->paginate(12)->withQueryString();
        $rts = Rt::activeRt()->orderBy('kode_rt')->get();

        return view('pengumuman.index', compact('pengumumans', 'user', 'rts'));
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403, 'Hanya pengurus RW yang dapat membuat pengumuman resmi.');

        $rts = Rt::activeRt()->orderBy('kode_rt')->get();

        return view('pengumuman.create', compact('user', 'rts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'isi' => ['required', 'string'],
            'target_audience' => ['required', 'in:semua_rt,rt_tertentu,seluruh_warga,warga_rt'],
            'rt_id' => ['nullable', 'required_if:target_audience,rt_tertentu,warga_rt', 'exists:rts,id'],
            'tanggal_publikasi' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published,arsip'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $tanggalPublikasi = $validated['tanggal_publikasi'] ? \Carbon\Carbon::parse($validated['tanggal_publikasi']) : now();

        $pengumuman = Pengumuman::create([
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'isi' => $validated['isi'],
            'target_audience' => $validated['target_audience'],
            'rt_id' => in_array($validated['target_audience'], ['rt_tertentu', 'warga_rt']) ? $validated['rt_id'] : null,
            'tanggal_publikasi' => $tanggalPublikasi,
            'status' => $validated['status'],
            'lampiran' => $lampiranPath,
            'created_by' => $user->id,
        ]);

        // Audit Trail
        AuditLog::record(
            action: 'pengumuman_create',
            entityType: 'pengumuman',
            entityId: $pengumuman->id,
            deskripsi: "Pengurus RW membuat pengumuman: \"{$pengumuman->judul}\" target: {$pengumuman->target_label}",
            rtId: $pengumuman->rt_id,
            userId: $user->id
        );

        // Jika status published, broadcast notifikasi ke target
        if ($pengumuman->status === 'published') {
            $this->broadcastNotification($pengumuman, $user);
        }

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman resmi RW berhasil dibuat dan dipublikasikan.');
    }

    public function show(Pengumuman $pengumuman)
    {
        $pengumuman->load(['rt', 'pembuat']);
        $user = Auth::user();

        return view('pengumuman.show', compact('pengumuman', 'user'));
    }

    public function edit(Pengumuman $pengumuman)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $rts = Rt::activeRt()->orderBy('kode_rt')->get();

        return view('pengumuman.edit', compact('pengumuman', 'user', 'rts'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:50'],
            'isi' => ['required', 'string'],
            'target_audience' => ['required', 'in:semua_rt,rt_tertentu,seluruh_warga,warga_rt'],
            'rt_id' => ['nullable', 'required_if:target_audience,rt_tertentu,warga_rt', 'exists:rts,id'],
            'tanggal_publikasi' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published,arsip'],
            'lampiran' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        if ($request->hasFile('lampiran')) {
            if ($pengumuman->lampiran) {
                Storage::disk('public')->delete($pengumuman->lampiran);
            }
            $pengumuman->lampiran = $request->file('lampiran')->store('pengumuman', 'public');
        }

        $pengumuman->judul = $validated['judul'];
        $pengumuman->kategori = $validated['kategori'];
        $pengumuman->isi = $validated['isi'];
        $pengumuman->target_audience = $validated['target_audience'];
        $pengumuman->rt_id = in_array($validated['target_audience'], ['rt_tertentu', 'warga_rt']) ? $validated['rt_id'] : null;
        if ($validated['tanggal_publikasi']) {
            $pengumuman->tanggal_publikasi = \Carbon\Carbon::parse($validated['tanggal_publikasi']);
        }
        $pengumuman->status = $validated['status'];
        $pengumuman->save();

        // Audit Trail
        AuditLog::record(
            action: 'pengumuman_update',
            entityType: 'pengumuman',
            entityId: $pengumuman->id,
            deskripsi: "Pengurus RW memperbarui pengumuman: \"{$pengumuman->judul}\"",
            rtId: $pengumuman->rt_id,
            userId: $user->id
        );

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $judul = $pengumuman->judul;
        if ($pengumuman->lampiran) {
            Storage::disk('public')->delete($pengumuman->lampiran);
        }

        $pengumuman->delete();

        // Audit Trail
        AuditLog::record(
            action: 'pengumuman_delete',
            entityType: 'pengumuman',
            deskripsi: "Pengurus RW menghapus pengumuman: \"{$judul}\"",
            userId: $user->id
        );

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function broadcastNotification(Pengumuman $pengumuman, User $sender)
    {
        $recipients = collect();

        if ($pengumuman->target_audience === 'semua_rt') {
            $rtKetuaIds = Rt::whereNotNull('ketua_user_id')->pluck('ketua_user_id');
            $recipients = User::whereIn('id', $rtKetuaIds)->get();
        } elseif ($pengumuman->target_audience === 'rt_tertentu' && $pengumuman->rt_id) {
            $rt = Rt::find($pengumuman->rt_id);
            if ($rt && $rt->ketua_user_id) {
                $recipients = User::where('id', $rt->ketua_user_id)->get();
            }
        } elseif ($pengumuman->target_audience === 'seluruh_warga') {
            $recipients = User::whereHas('role', fn ($r) => $r->where('nama_role', 'warga'))->take(100)->get();
        } elseif ($pengumuman->target_audience === 'warga_rt' && $pengumuman->rt_id) {
            $recipients = User::whereHas('role', fn ($r) => $r->where('nama_role', 'warga'))
                ->where('rt_id', $pengumuman->rt_id)
                ->take(100)
                ->get();
        }

        foreach ($recipients as $recipient) {
            Notifikasi::create([
                'user_id' => $recipient->id,
                'from_user_id' => $sender->id,
                'tipe' => 'pengumuman',
                'judul' => 'Pengumuman RW: ' . $pengumuman->judul,
                'pesan' => \Illuminate\Support\Str::limit($pengumuman->isi, 120),
                'dibaca' => false,
            ]);
        }
    }
}
