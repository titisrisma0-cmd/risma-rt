<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MetodePembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MetodePembayaranController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403, 'Akses terbatas untuk Pengurus RW dan Bendahara.');

        $metodes = MetodePembayaran::orderBy('kategori')->orderBy('nama')->get();

        return view('rw.metode-pembayaran.index', compact('user', 'metodes'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'in:ewallet,qris,bank,tunai'],
            'nama_penerima' => ['nullable', 'string', 'max:150'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'instruksi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $kode = Str::slug($validated['nama'], '_');
        $originalKode = $kode;
        $counter = 1;
        while (MetodePembayaran::where('kode', $kode)->exists()) {
            $kode = "{$originalKode}_{$counter}";
            $counter++;
        }

        $qrPath = null;
        if ($request->hasFile('qr_image')) {
            $qrPath = $request->file('qr_image')->store('metode_pembayaran', 'public');
        }

        $metode = MetodePembayaran::create([
            'kode' => $kode,
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'nama_penerima' => $validated['nama_penerima'] ?? null,
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'instruksi' => $validated['instruksi'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'qr_image' => $qrPath,
            'created_by' => $user->id,
        ]);

        AuditLog::record(
            action: 'tambah_metode_pembayaran',
            entityType: 'metode_pembayaran',
            entityId: $metode->id,
            deskripsi: "Pengurus {$user->name} menambahkan metode pembayaran {$metode->nama} ({$metode->kategori})",
            userId: $user->id
        );

        return redirect()->route('rw.metode-pembayaran.index')->with('success', "Metode pembayaran {$metode->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, MetodePembayaran $metodePembayaran)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'in:ewallet,qris,bank,tunai'],
            'nama_penerima' => ['nullable', 'string', 'max:150'],
            'nomor_rekening' => ['nullable', 'string', 'max:100'],
            'instruksi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $updateData = [
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'],
            'nama_penerima' => $validated['nama_penerima'] ?? null,
            'nomor_rekening' => $validated['nomor_rekening'] ?? null,
            'instruksi' => $validated['instruksi'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('qr_image')) {
            if ($metodePembayaran->qr_image && Storage::disk('public')->exists($metodePembayaran->qr_image)) {
                Storage::disk('public')->delete($metodePembayaran->qr_image);
            }
            $updateData['qr_image'] = $request->file('qr_image')->store('metode_pembayaran', 'public');
        }

        $metodePembayaran->update($updateData);

        AuditLog::record(
            action: 'update_metode_pembayaran',
            entityType: 'metode_pembayaran',
            entityId: $metodePembayaran->id,
            deskripsi: "Pengurus {$user->name} memperbarui metode pembayaran {$metodePembayaran->nama}",
            userId: $user->id
        );

        return redirect()->route('rw.metode-pembayaran.index')->with('success', "Metode pembayaran {$metodePembayaran->nama} berhasil diperbarui.");
    }

    public function toggle(MetodePembayaran $metodePembayaran)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->hasRole('bendahara'), 403);

        $metodePembayaran->update([
            'is_active' => !$metodePembayaran->is_active,
        ]);

        $statusText = $metodePembayaran->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record(
            action: 'toggle_metode_pembayaran',
            entityType: 'metode_pembayaran',
            entityId: $metodePembayaran->id,
            deskripsi: "Metode pembayaran {$metodePembayaran->nama} {$statusText} oleh {$user->name}",
            userId: $user->id
        );

        return back()->with('success', "Metode pembayaran {$metodePembayaran->nama} berhasil {$statusText}.");
    }

    public function destroy(MetodePembayaran $metodePembayaran)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw(), 403);

        if ($metodePembayaran->qr_image && Storage::disk('public')->exists($metodePembayaran->qr_image)) {
            Storage::disk('public')->delete($metodePembayaran->qr_image);
        }

        $nama = $metodePembayaran->nama;
        $metodePembayaran->delete();

        return redirect()->route('rw.metode-pembayaran.index')->with('success', "Metode pembayaran {$nama} berhasil dihapus.");
    }
}
