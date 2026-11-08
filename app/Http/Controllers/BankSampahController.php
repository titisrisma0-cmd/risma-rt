<?php

namespace App\Http\Controllers;

use App\Models\JenisSampah;
use App\Models\JadwalBankSampah;
use App\Models\Notifikasi;
use App\Models\PenarikanSaldoSampah;
use App\Models\SaldoSampah;
use App\Models\SetoranSampah;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BankSampahController extends Controller
{
    public function index(Request $request)
    {
        $schemaReady = $this->schemaReady();
        $isPengurus = $this->isPengurus();
        $user = Auth::user();

        if (! $schemaReady) {
            return view('bank-sampah.index', [
                'schemaReady' => false,
                'isPengurus' => $isPengurus,
            ]);
        }

        $this->ensureDefaultJenisSampah();

        $jenisSampahs = JenisSampah::orderBy('nama')->get();

        if ($isPengurus) {
            $isRt = $user->isAdminRt();
            $rtId = $user->managedRtId();

            $wargaUserIds = $isRt && $rtId
                ? User::where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $rtId));
                })->pluck('id')
                : null;

            $jadwalsQuery = JadwalBankSampah::with('rt')->orderBy('waktu', 'desc');
            if ($isRt && $rtId) {
                $jadwalsQuery->where(function ($q) use ($rtId) {
                    $q->where('rt_id', $rtId)->orWhereNull('rt_id');
                });
            }
            $jadwals = $jadwalsQuery->get();

            $setoranQuery = SetoranSampah::with(['user.profile', 'jenisSampah']);
            if ($isRt && $wargaUserIds) {
                $setoranQuery->whereIn('user_id', $wargaUserIds);
            }

            $setoranTerbaru = (clone $setoranQuery)->latest()->limit(20)->get();
            $setoranPending = (clone $setoranQuery)->where('status', 'pending')->latest()->get();

            $penarikanQuery = PenarikanSaldoSampah::with(['user.profile', 'processor']);
            if ($isRt && $wargaUserIds) {
                $penarikanQuery->whereIn('user_id', $wargaUserIds);
            }
            $penarikanList = (clone $penarikanQuery)->latest()->limit(20)->get();
            $penarikanPending = (clone $penarikanQuery)->where('status', 'pending')->latest()->get();

            $validSetoranQuery = SetoranSampah::where('status', 'valid');
            if ($isRt && $wargaUserIds) {
                $validSetoranQuery->whereIn('user_id', $wargaUserIds);
            }

            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $rekap = [
                'berat' => (clone $validSetoranQuery)->sum('berat_kg'),
                'nilai' => (clone $validSetoranQuery)->sum('total_saldo'),
                'warga_count' => (clone $validSetoranQuery)->distinct('user_id')->count('user_id'),
                'berat_bulan_ini' => (clone $validSetoranQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('berat_kg'),
                'nilai_bulan_ini' => (clone $validSetoranQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_saldo'),
            ];

            // List of wargas with balances for RT
            $wargaBalances = SaldoSampah::with(['user.profile'])
                ->when($isRt && $wargaUserIds, fn ($q) => $q->whereIn('user_id', $wargaUserIds))
                ->where('saldo', '>', 0)
                ->orderByDesc('saldo')
                ->limit(20)
                ->get();

            return view('bank-sampah.index', [
                'schemaReady' => true,
                'isPengurus' => true,
                'isRt' => $isRt,
                'rtId' => $rtId,
                'jenisSampahs' => $jenisSampahs,
                'wargas' => $this->wargaQuery()->when($isRt && $rtId, fn ($q) => $q->where(fn ($sub) => $sub->where('rt_id', $rtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $rtId))))->orderBy('name')->get(),
                'setoranTerbaru' => $setoranTerbaru,
                'setoranPending' => $setoranPending,
                'penarikanList' => $penarikanList,
                'penarikanPending' => $penarikanPending,
                'wargaBalances' => $wargaBalances,
                'jadwals' => $jadwals,
                'rekap' => $rekap,
            ]);
        }

        $saldo = SaldoSampah::firstOrCreate(
            ['user_id' => Auth::id()],
            ['saldo' => 0, 'total_berat_kg' => 0]
        );

        $rtId = $user->managedRtId() ?? $user->profile?->rt_id;
        $jadwals = JadwalBankSampah::with('rt')
            ->where(function ($q) use ($rtId) {
                if ($rtId) {
                    $q->where('rt_id', $rtId)->orWhereNull('rt_id');
                }
            })
            ->where('aktif', true)
            ->orderBy('waktu')
            ->get();

        return view('bank-sampah.index', [
            'schemaReady' => true,
            'isPengurus' => false,
            'jenisSampahs' => $jenisSampahs,
            'saldo' => $saldo,
            'riwayatSetoran' => SetoranSampah::with('jenisSampah')
                ->where('user_id', Auth::id())
                ->latest()
                ->get(),
            'riwayatPenarikan' => PenarikanSaldoSampah::where('user_id', Auth::id())
                ->latest()
                ->get(),
            'jadwals' => $jadwals,
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

        return redirect()->route('bank-sampah.index')->with('success', 'Setoran dikirim dan menunggu verifikasi petugas RT.');
    }

    public function verifySetoran(Request $request, SetoranSampah $setoran)
    {
        abort_unless($this->isPengurus(), 403);
        $data = $request->validate([
            'status' => ['required', 'in:valid,ditolak'],
            'catatan' => ['nullable', 'string'],
        ]);

        abort_if($setoran->status !== 'pending', 422, 'Setoran sudah diverifikasi sebelumnya.');

        $user = Auth::user();
        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $setoranUserRt = $setoran->user?->managedRtId() ?? $setoran->user?->profile?->rt_id;
            if ($rtId && $setoranUserRt && (int) $rtId !== (int) $setoranUserRt) {
                abort(403, 'Anda hanya dapat memverifikasi setoran warga RT Anda.');
            }
        }

        DB::transaction(function () use ($setoran, $data) {
            $setoran->update($data + ['validated_by' => Auth::id(), 'validated_at' => now()]);

            if ($data['status'] === 'valid') {
                $saldo = SaldoSampah::firstOrCreate(
                    ['user_id' => $setoran->user_id],
                    ['saldo' => 0, 'total_berat_kg' => 0]
                );
                $saldo->increment('saldo', $setoran->total_saldo);
                $saldo->increment('total_berat_kg', $setoran->berat_kg);
            }

            Notifikasi::create([
                'user_id' => $setoran->user_id,
                'from_user_id' => Auth::id(),
                'tipe' => 'bank_sampah',
                'judul' => 'Setoran sampah ' . ($data['status'] === 'valid' ? 'disetujui' : 'ditolak'),
                'pesan' => $data['status'] === 'valid'
                    ? 'Saldo Bank Sampah Anda bertambah Rp ' . number_format($setoran->total_saldo, 0, ',', '.') . " ({$setoran->berat_kg} kg {$setoran->jenisSampah?->nama})"
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
        $user = Auth::user();

        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'waktu' => ['required', 'date'],
            'lokasi' => ['required', 'string', 'max:255'],
            'petugas_nama' => ['nullable', 'string', 'max:255'],
            'petugas_kontak' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:terjadwal,berlangsung,selesai,ditunda'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : ($request->input('rt_id') ?? $user->managedRtId());

        $jadwal = JadwalBankSampah::create($data + [
            'rt_id' => $rtId,
            'status' => $data['status'] ?? 'terjadwal',
            'aktif' => true,
        ]);

        // Broadcast notification to citizens in the RT
        if ($rtId) {
            $wargaIds = User::where(function ($q) use ($rtId) {
                $q->where('rt_id', $rtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $rtId));
            })->pluck('id');

            foreach ($wargaIds as $wId) {
                Notifikasi::create([
                    'user_id' => $wId,
                    'from_user_id' => $user->id,
                    'tipe' => 'bank_sampah',
                    'judul' => 'Jadwal Bank Sampah Baru',
                    'pesan' => "Jadwal pengumpulan sampah: {$jadwal->judul} pada " . Carbon::parse($jadwal->waktu)->isoFormat('dddd, D MMMM Y HH:mm') . " di {$jadwal->lokasi}.",
                    'dibaca' => false,
                ]);
            }
        }

        return back()->with('success', 'Jadwal pengumpulan sampah berhasil ditambahkan dan disiarkan ke warga.');
    }

    public function updateStatusJadwal(Request $request, JadwalBankSampah $jadwal)
    {
        abort_unless($this->isPengurus(), 403);
        $validated = $request->validate([
            'status' => ['required', 'in:terjadwal,berlangsung,selesai,ditunda'],
        ]);

        $jadwal->update(['status' => $validated['status']]);

        return back()->with('success', 'Status jadwal pengumpulan berhasil diperbarui.');
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

        return redirect()->route('bank-sampah.index')->with('success', 'Pengajuan pencairan saldo berhasil dikirim ke pengurus RT.');
    }

    public function processWithdrawal(Request $request, PenarikanSaldoSampah $penarikan)
    {
        abort_unless($this->isPengurus(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak,dibayar'],
            'catatan' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        if ($user->isAdminRt()) {
            $rtId = $user->managedRtId();
            $penarikanUserRt = $penarikan->user?->managedRtId() ?? $penarikan->user?->profile?->rt_id;
            if ($rtId && $penarikanUserRt && (int) $rtId !== (int) $penarikanUserRt) {
                abort(403, 'Anda hanya dapat memproses pencairan warga RT Anda.');
            }
        }

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

            Notifikasi::create([
                'user_id' => $penarikan->user_id,
                'from_user_id' => Auth::id(),
                'tipe' => 'bank_sampah',
                'judul' => 'Pencairan Saldo Bank Sampah ' . ucfirst($validated['status']),
                'pesan' => $validated['status'] === 'dibayar'
                    ? 'Pencairan saldo Rp ' . number_format($penarikan->nominal, 0, ',', '.') . ' telah dibayarkan.'
                    : ($validated['status'] === 'disetujui'
                        ? 'Pengajuan pencairan saldo Rp ' . number_format($penarikan->nominal, 0, ',', '.') . ' disetujui, menunggu pembayaran.'
                        : 'Pengajuan pencairan ditolak: ' . ($validated['catatan'] ?? '-')),
                'dibaca' => false,
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
            ['nama' => 'Plastik Botol/Gelas Bersih', 'kategori' => 'Anorganik', 'harga_per_kg' => 3000],
            ['nama' => 'Kardus & Kertas Bekas', 'kategori' => 'Anorganik', 'harga_per_kg' => 1800],
            ['nama' => 'Besi & Logam Campur', 'kategori' => 'Anorganik', 'harga_per_kg' => 5500],
            ['nama' => 'Minyak Jelantah', 'kategori' => 'Limbah', 'harga_per_kg' => 6000],
            ['nama' => 'Elektronik Kecil / Kabel', 'kategori' => 'B3 / E-Waste', 'harga_per_kg' => 8000],
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
        ], true) || ($user && ($user->isAdminRt() || $user->isAdminRw()));
    }

    private function wargaQuery()
    {
        return User::whereHas('role', function ($query) {
            $query->where('nama_role', 'warga');
        });
    }
}
