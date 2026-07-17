<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use App\Models\JadwalBankSampah;
use App\Models\Notifikasi;
use App\Models\SaldoSampah;
use App\Models\SetoranSampah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankSampahController extends Controller
{
    public function index()
    {
        $schemaReady = $this->schemaReady();
        $isPengurus = $this->isPengurus();

        if (! $schemaReady) {
            return view('bank-sampah.index', [
                'schemaReady' => false,
                'isPengurus' => $isPengurus,
            ]);
        }

        $this->ensureDefaultJenisSampah();

        $jenisSampahs = JenisSampah::orderBy('nama')->get();

        if ($isPengurus) {
            return view('bank-sampah.index', [
                'schemaReady' => true,
                'isPengurus' => true,
                'jenisSampahs' => $jenisSampahs,
                'wargas' => $this->wargaQuery()->orderBy('name')->get(),
                'setoranTerbaru' => SetoranSampah::with(['user', 'jenisSampah'])
                    ->latest()
                    ->limit(12)
                    ->get(),
                'setoranPending' => SetoranSampah::with(['user', 'jenisSampah'])->where('status', 'pending')->latest()->get(),
                'jadwals' => JadwalBankSampah::with('rt')->where('aktif', true)->orderBy('waktu')->get(),
                'rekap' => ['berat' => SetoranSampah::where('status', 'valid')->sum('berat_kg'), 'nilai' => SetoranSampah::where('status', 'valid')->sum('total_saldo')],
            ]);
        }

        $saldo = SaldoSampah::firstOrCreate(
            ['user_id' => Auth::id()],
            ['saldo' => 0, 'total_berat_kg' => 0]
        );

        return view('bank-sampah.index', [
            'schemaReady' => true,
            'isPengurus' => false,
            'jenisSampahs' => $jenisSampahs,
            'saldo' => $saldo,
            'riwayatSetoran' => SetoranSampah::with('jenisSampah')
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),
            'jadwals' => JadwalBankSampah::with('rt')->where('aktif', true)->orderBy('waktu')->get(),
        ]);
    }

    public function storeSetoran(Request $request)
    {
        $validated = $request->validate([
            'jenis_sampah_id' => ['required', 'exists:jenis_sampahs,id'],
            'berat_kg' => ['required', 'numeric', 'min:0.1'],
            'tanggal_setor' => ['required', 'date'],
            'foto_bukti' => ['nullable', 'image', 'max:4096'],
            'catatan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $jenisSampah = JenisSampah::findOrFail($validated['jenis_sampah_id']);
            $totalSaldo = $validated['berat_kg'] * $jenisSampah->harga_per_kg;

            SetoranSampah::create([
                'user_id' => Auth::id(),
                'jenis_sampah_id' => $jenisSampah->id,
                'berat_kg' => $validated['berat_kg'],
                'harga_per_kg' => $jenisSampah->harga_per_kg,
                'total_saldo' => $totalSaldo,
                'tanggal_setor' => $validated['tanggal_setor'],
                'foto_bukti' => $request->file('foto_bukti')?->store('setoran-sampah', 'public'),
                'status' => 'pending',
                'created_by' => Auth::id(),
                'catatan' => $validated['catatan'] ?? null,
            ]);
        });

        return redirect()->route('bank-sampah.index')->with('success', 'Setoran dikirim dan menunggu verifikasi petugas.');
    }

    public function verifySetoran(Request $request, SetoranSampah $setoran)
    {
        abort_unless($this->isPengurus(), 403);
        $data = $request->validate(['status' => ['required', 'in:valid,ditolak'], 'catatan' => ['nullable', 'string']]);
        abort_if($setoran->status !== 'pending', 422, 'Setoran sudah diverifikasi.');
        DB::transaction(function () use ($setoran, $data) {
            $setoran->update($data + ['validated_by' => Auth::id(), 'validated_at' => now()]);
            if ($data['status'] === 'valid') {
                $saldo = SaldoSampah::firstOrCreate(['user_id' => $setoran->user_id], ['saldo' => 0, 'total_berat_kg' => 0]);
                $saldo->increment('saldo', $setoran->total_saldo); $saldo->increment('total_berat_kg', $setoran->berat_kg);
            }
            Notifikasi::create([
                'user_id' => $setoran->user_id,
                'from_user_id' => Auth::id(),
                'tipe' => 'bank_sampah',
                'judul' => 'Setoran sampah ' . ($data['status'] === 'valid' ? 'disetujui' : 'ditolak'),
                'pesan' => $data['status'] === 'valid'
                    ? 'Saldo Bank Sampah Anda bertambah Rp ' . number_format($setoran->total_saldo, 0, ',', '.')
                    : ($data['catatan'] ?: 'Setoran belum dapat disetujui.'),
                'dibaca' => false,
            ]);
        });
        return back()->with('success', 'Setoran berhasil diverifikasi.');
    }

    public function updateHarga(Request $request)
    {
        abort_unless($this->isPengurus(), 403);

        $validated = $request->validate([
            'jenis_sampah_id' => ['nullable', 'exists:jenis_sampahs,id'],
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'harga_per_kg' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        JenisSampah::updateOrCreate(
            ['id' => $validated['jenis_sampah_id'] ?? null],
            [
                'nama' => $validated['nama'],
                'kategori' => $validated['kategori'] ?? null,
                'harga_per_kg' => $validated['harga_per_kg'],
                'status' => $request->boolean('status', true),
            ]
        );

        return redirect()->route('bank-sampah.index')->with('success', 'Harga sampah berhasil disimpan.');
    }

    public function storeJadwal(Request $request)
    {
        abort_unless($this->isPengurus(), 403);
        $data = $request->validate(['judul' => ['required', 'string', 'max:255'], 'waktu' => ['required', 'date'], 'lokasi' => ['required', 'string', 'max:255'], 'keterangan' => ['nullable', 'string']]);
        JadwalBankSampah::create($data + ['aktif' => true]);
        return back()->with('success', 'Jadwal setor/jemput berhasil ditambahkan.');
    }

    public function requestWithdrawal(Request $request)
    {
        $validated = $request->validate([
            'nominal' => ['required', 'numeric', 'min:1000'],
            'metode' => ['required', 'in:tunai,transfer'],
            'rekening_tujuan' => ['nullable', 'string', 'max:255'],
        ]);

        $saldo = SaldoSampah::firstOrCreate(
            ['user_id' => Auth::id()],
            ['saldo' => 0, 'total_berat_kg' => 0]
        );

        if ($validated['nominal'] > $saldo->saldo) {
            return back()->with('error', 'Nominal penarikan melebihi saldo Bank Sampah Anda.');
        }

        PenarikanSaldoSampah::create([
            'user_id' => Auth::id(),
            'nominal' => $validated['nominal'],
            'metode' => $validated['metode'],
            'rekening_tujuan' => $validated['rekening_tujuan'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('bank-sampah.index')->with('success', 'Pengajuan tarik saldo berhasil dikirim.');
    }

    public function processWithdrawal(Request $request, PenarikanSaldoSampah $penarikan)
    {
        abort_unless($this->isPengurus(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak,dibayar'],
            'catatan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($penarikan, $validated) {
            if (in_array($penarikan->status, ['ditolak', 'dibayar'], true)) {
                return;
            }

            if ($validated['status'] === 'dibayar') {
                $saldo = SaldoSampah::firstOrCreate(
                    ['user_id' => $penarikan->user_id],
                    ['saldo' => 0, 'total_berat_kg' => 0]
                );

                if ($penarikan->nominal > $saldo->saldo) {
                    abort(422, 'Saldo warga tidak mencukupi untuk pencairan ini.');
                }

                $saldo->decrement('saldo', $penarikan->nominal);
            }

            $penarikan->update([
                'status' => $validated['status'],
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'catatan' => $validated['catatan'] ?? null,
            ]);
        });

        return redirect()->route('bank-sampah.index')->with('success', 'Status pencairan saldo berhasil diperbarui.');
    }

    private function schemaReady(): bool
    {
        return Schema::hasTable('jenis_sampahs')
            && Schema::hasTable('saldo_sampahs')
            && Schema::hasTable('setoran_sampahs')
            && Schema::hasTable('penarikan_saldo_sampahs');
    }

    private function ensureDefaultJenisSampah(): void
    {
        if (JenisSampah::query()->exists()) {
            return;
        }

        collect([
            ['nama' => 'Plastik', 'kategori' => 'Anorganik', 'harga_per_kg' => 2000],
            ['nama' => 'Kertas', 'kategori' => 'Anorganik', 'harga_per_kg' => 1500],
            ['nama' => 'Logam', 'kategori' => 'Anorganik', 'harga_per_kg' => 5000],
        ])->each(fn ($jenis) => JenisSampah::create($jenis));
    }

    private function isPengurus(): bool
    {
        $user = Auth::user();

        return $user && in_array(optional($user->role)->nama_role, [
            'admin',
            'bendahara',
            'ketua_rt',
            'ketua_rw',
            'petugas',
            'sekretaris',
        ], true);
    }

    private function wargaQuery()
    {
        return User::whereHas('role', function ($query) {
            $query->where('nama_role', 'warga');
        });
    }
}
