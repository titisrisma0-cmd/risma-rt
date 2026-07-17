<?php

namespace App\Http\Controllers;

use App\Models\AbsensiRonda;
use App\Models\Aspirasi;
use App\Models\AspirasiKomentar;
use App\Models\AspirasiLampiran;
use App\Models\Balita;
use App\Models\Iuran;
use App\Models\JadwalPosyandu;
use App\Models\JadwalRonda;
use App\Models\JenisSurat;
use App\Models\Kas;
use App\Models\Kegiatan;
use App\Models\KontakDarurat;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\PemeriksaanBalita;
use App\Models\PengajuanSurat;
use App\Models\Pengeluaran;
use App\Models\PetugasRonda;
use App\Models\ProdukUmkm;
use App\Models\RsvpKegiatan;
use App\Models\Rt;
use App\Models\Umkm;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LayananMasyarakatController extends Controller
{
    public function umkm(Request $request)
    {
        $this->ensureDemoUmkmCategories();
        $isPengurus = Auth::user()->isAdminRw();
        $produkKelola = ProdukUmkm::with('umkm.pemilik')
            ->when(! $isPengurus, fn ($query) => $query->whereHas('umkm', fn ($umkm) => $umkm->where('user_id', Auth::id())))
            ->latest()
            ->get();

        $produk = ProdukUmkm::with('umkm.pemilik')
            ->where('status', 'aktif')
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->filled('q'), function ($q) use ($request) {
                $keyword = '%' . $request->q . '%';

                $q->where(function ($query) use ($keyword) {
                    $query->where('nama_produk', 'like', $keyword)
                        ->orWhere('deskripsi', 'like', $keyword)
                        ->orWhereHas('umkm', fn ($umkm) => $umkm->where('nama_usaha', 'like', $keyword));
                });
            })
            ->latest()
            ->get();

        $kategoriProduk = collect(['Kuliner', 'Jasa', 'Jahit', 'Sembako', 'Kerajinan', 'Laundry', 'Servis'])
            ->merge(ProdukUmkm::query()->select('kategori')->whereNotNull('kategori')->distinct()->pluck('kategori'))
            ->filter()
            ->unique()
            ->values();

        return view('layanan.umkm', [
            'isPengurus' => $isPengurus,
            'produk' => $produk,
            'kategoriProduk' => $kategoriProduk,
            'tokoSaya' => Umkm::with('produk')->where('user_id', Auth::id())->first(),
            'produkSaya' => $produkKelola,
            'produkPending' => $isPengurus ? ProdukUmkm::with('umkm.pemilik')->where('status', 'pending')->latest()->get() : collect(),
        ]);
    }

    public function storeUmkm(Request $request)
    {
        $validated = $request->validate([
            'nama_usaha' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'alamat' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]);

        Umkm::updateOrCreate(['user_id' => Auth::id()], $validated + ['status' => 'aktif']);

        return back()->with('success', 'Toko Saya berhasil disimpan.');
    }

    public function storeProdukUmkm(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $umkm = Umkm::firstOrCreate(
            ['user_id' => Auth::id()],
            ['nama_usaha' => 'Toko ' . Auth::user()->name, 'kategori' => 'Umum', 'status' => 'aktif']
        );

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('umkm', 'public');
        }

        $umkm->produk()->create($validated + ['status' => 'pending']);

        return back()->with('success', 'Produk berhasil dikirim dan menunggu moderasi pengurus.');
    }

    public function moderateProdukUmkm(Request $request, ProdukUmkm $produk)
    {
        abort_unless(Auth::user()->isAdminRw(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:aktif,ditolak,nonaktif'],
            'catatan_moderasi' => ['nullable', 'string'],
        ]);

        $produk->update($validated + [
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Status produk UMKM berhasil diperbarui.');
    }

    public function updateProdukUmkm(Request $request, ProdukUmkm $produk)
    {
        $this->authorizeProdukUmkm($produk);
        $validated = $request->validate($this->produkUmkmRules());

        if ($request->hasFile('foto')) {
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }
            $validated['foto'] = $request->file('foto')->store('umkm', 'public');
        }

        if (! Auth::user()->isAdminRw()) {
            $validated['status'] = 'pending';
            $validated['moderated_by'] = null;
            $validated['moderated_at'] = null;
            $validated['catatan_moderasi'] = null;
        }

        $produk->update($validated);

        return back()->with('success', Auth::user()->isAdminRw()
            ? 'Produk berhasil diperbarui.'
            : 'Produk berhasil diperbarui dan dikirim ulang untuk moderasi.');
    }

    public function destroyProdukUmkm(ProdukUmkm $produk)
    {
        $this->authorizeProdukUmkm($produk);

        if ($produk->foto) {
            Storage::disk('public')->delete($produk->foto);
        }
        $produk->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function authorizeProdukUmkm(ProdukUmkm $produk): void
    {
        $isOwner = $produk->umkm()->where('user_id', Auth::id())->exists();
        abort_unless($isOwner || Auth::user()->isAdminRw(), 403);
    }

    private function produkUmkmRules(): array
    {
        return [
            'nama_produk' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function surat()
    {
        $this->ensureDefaultJenisSurat();
        $isPengurus = Auth::user()->hasRole('admin');

        return view('layanan.surat', [
            'isPengurus' => $isPengurus,
            'jenisSurats' => JenisSurat::where('status', true)->orderBy('nama')->get(),
            'pengajuanSaya' => PengajuanSurat::with('jenisSurat')->where('user_id', Auth::id())->latest()->get(),
            'suratMasuk' => $isPengurus ? PengajuanSurat::with(['user.profile', 'jenisSurat'])->latest()->get() : collect(),
        ]);
    }

    public function storeSurat(Request $request)
    {
        $validated = $request->validate([
            'jenis_surat_id' => ['required', 'exists:jenis_surats,id'],
            'keperluan' => ['required', 'string'],
            'berkas_pendukung' => ['required', 'array', 'min:1'],
            'berkas_pendukung.*' => ['image', 'max:2048'],
        ], [
            'berkas_pendukung.required' => 'Upload minimal satu foto berkas pendukung seperti KTP atau KK.',
            'berkas_pendukung.*.image' => 'Berkas pendukung harus berupa gambar.',
        ]);

        $berkas = [];
        foreach ($request->file('berkas_pendukung', []) as $file) {
            $berkas[] = $file->store('surat', 'public');
        }

        PengajuanSurat::create([
            'user_id' => Auth::id(),
            'jenis_surat_id' => $validated['jenis_surat_id'],
            'nomor_pengajuan' => 'SUR-' . now()->format('YmdHis') . '-' . Auth::id(),
            'keperluan' => $validated['keperluan'],
            'berkas_pendukung' => $berkas,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pengajuan surat berhasil dikirim.');
    }

    public function updateSurat(Request $request, PengajuanSurat $pengajuanSurat)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:diproses,ditolak,siap_diambil,selesai'],
            'catatan' => ['nullable', 'string'],
        ]);

        $data = $validated;
        if (in_array($validated['status'], ['siap_diambil', 'selesai'], true) && ! $pengajuanSurat->nomor_surat) {
            $data['nomor_surat'] = 'RW/SUR/' . now()->format('Y/m') . '/' . str_pad((string) $pengajuanSurat->id, 4, '0', STR_PAD_LEFT);
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();
        }

        $pengajuanSurat->update($data);

        return back()->with('success', 'Status surat berhasil diperbarui.');
    }

    public function cetakSurat(PengajuanSurat $pengajuanSurat)
    {
        abort_unless(Auth::user()->hasRole('admin') || $pengajuanSurat->user_id === Auth::id(), 403);
        abort_unless($pengajuanSurat->nomor_surat, 404);
        $pengajuanSurat->load(['user.profile', 'jenisSurat', 'approver']);

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(30);
            }

            $nomorDokumen = $pengajuanSurat->nomor_surat ?: $pengajuanSurat->nomor_pengajuan;
            $namaFile = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $nomorDokumen), '-_.') . '.pdf';
            $logoPath = public_path('images/rw-smart-logo.png');
            $logoData = is_file($logoPath)
                ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath))
                : null;

            return Pdf::loadView('layanan.surat-pdf', [
                'surat' => $pengajuanSurat,
                'logoData' => $logoData,
            ])
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', false)
                ->download($namaFile);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'PDF surat gagal dibuat. Silakan coba kembali atau hubungi admin apabila masalah berlanjut.'
            );
        }
    }

    public function posyandu()
    {
        $isPengurus = Auth::user()->hasRole('admin');

        return view('layanan.posyandu', [
            'isPengurus' => $isPengurus,
            'jadwals' => JadwalPosyandu::latest('tanggal_mulai')->get(),
            'balitas' => $isPengurus
                ? Balita::with(['orangTua', 'pemeriksaan' => fn ($query) => $query->orderBy('tanggal_periksa')])->orderBy('nama')->get()
                : Balita::with(['orangTua', 'pemeriksaan' => fn ($query) => $query->orderBy('tanggal_periksa')])->where('orang_tua_user_id', Auth::id())->orderBy('nama')->get(),
            'semuaBalita' => Balita::orderBy('nama')->get(),
            'wargas' => $this->wargaQuery()->orderBy('name')->get(),
            'rts' => Rt::orderBy('kode_rt')->get(),
        ]);
    }

    public function storeBalita(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        Balita::create($request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:balitas,nik'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'orang_tua_user_id' => ['nullable', 'exists:users,id'],
            'catatan_kesehatan' => ['nullable', 'string'],
        ]));

        return back()->with('success', 'Data balita berhasil ditambahkan.');
    }

    public function storeJadwalPosyandu(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $jadwal = JadwalPosyandu::create($request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'lokasi' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'rt_id' => ['nullable', 'exists:rts,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]) + ['created_by' => Auth::id()]);

        $this->notifyWarga('Jadwal Posyandu Baru', $jadwal->judul . ' pada ' . $jadwal->tanggal_mulai->format('d/m/Y H:i'));

        return back()->with('success', 'Jadwal posyandu berhasil dibuat dan notifikasi dikirim.');
    }

    public function storePemeriksaanBalita(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'balita_id' => ['required', 'exists:balitas,id'],
            'jadwal_posyandu_id' => ['nullable', 'exists:jadwal_posyandus,id'],
            'tanggal_periksa' => ['required', 'date'],
            'berat_kg' => ['nullable', 'numeric', 'min:0'],
            'tinggi_cm' => ['nullable', 'numeric', 'min:0'],
            'lingkar_kepala_cm' => ['nullable', 'numeric', 'min:0'],
            'imunisasi_sudah' => ['nullable', 'string'],
            'imunisasi_belum' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['imunisasi_sudah'] = $this->splitList($validated['imunisasi_sudah'] ?? null);
        $validated['imunisasi_belum'] = $this->splitList($validated['imunisasi_belum'] ?? null);

        PemeriksaanBalita::create($validated + ['petugas_id' => Auth::id()]);

        return back()->with('success', 'Hasil pemeriksaan balita berhasil disimpan.');
    }

    public function ronda()
    {
        $this->ensureDefaultKontakDarurat();
        $isPengurus = Auth::user()->hasRole('admin');
        $jadwals = JadwalRonda::with(['petugas.user', 'absensi.user'])
            ->latest('tanggal')
            ->limit(30)
            ->get();

        return view('layanan.ronda', [
            'isPengurus' => $isPengurus,
            'kontaks' => KontakDarurat::where('status', true)->orderBy('kategori')->get(),
            'jadwals' => $jadwals,
            'jadwalSaya' => $jadwals->filter(fn ($jadwal) => $jadwal->petugas->contains('user_id', Auth::id()))->values(),
            'wargas' => $this->wargaQuery()->orderBy('name')->get(),
            'panicReports' => $isPengurus
                ? \App\Models\PanicReport::with('user')->latest()->limit(20)->get()
                : \App\Models\PanicReport::with('user')->where('user_id', Auth::id())->latest()->get(),
            'activePanicReports' => \App\Models\PanicReport::with('user')->where('status', 'aktif')->latest()->limit(5)->get(),
        ]);
    }

    public function storeJadwalRonda(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        DB::transaction(function () use ($request) {
            $validated = $request->validate([
                'mode' => ['nullable', 'in:manual,acak'],
                'tanggal' => ['required', 'date'],
                'jumlah_hari' => ['nullable', 'integer', 'min:1', 'max:31'],
                'jumlah_petugas' => ['nullable', 'integer', 'min:1', 'max:10'],
                'shift' => ['required', 'string', 'max:255'],
                'lokasi_pos' => ['nullable', 'string', 'max:255'],
                'petugas' => ['nullable', 'array'],
                'petugas.*' => ['exists:users,id'],
            ]);

            $mode = $validated['mode'] ?? 'manual';
            $jumlahHari = $mode === 'acak' ? (int) ($validated['jumlah_hari'] ?? 7) : 1;
            $jumlahPetugas = (int) ($validated['jumlah_petugas'] ?? 3);
            $poolPetugas = collect($validated['petugas'] ?? []);

            if ($mode === 'acak' && $poolPetugas->isEmpty()) {
                $poolPetugas = $this->wargaQuery()->pluck('id');
            }

            for ($index = 0; $index < $jumlahHari; $index++) {
                $jadwal = JadwalRonda::create([
                    'tanggal' => \Carbon\Carbon::parse($validated['tanggal'])->addDays($index),
                    'shift' => $validated['shift'],
                    'lokasi_pos' => $validated['lokasi_pos'] ?? 'Pos Ronda',
                    'created_by' => Auth::id(),
                    'status' => 'aktif',
                ]);

                $petugasTerpilih = $mode === 'acak'
                    ? $poolPetugas->shuffle()->take($jumlahPetugas)
                    : $poolPetugas;

                foreach ($petugasTerpilih as $userId) {
                    PetugasRonda::firstOrCreate([
                        'jadwal_ronda_id' => $jadwal->id,
                        'user_id' => $userId,
                    ]);
                }
            }
        });

        return back()->with('success', 'Jadwal ronda berhasil dibuat.');
    }

    public function storeAbsensiRonda(Request $request)
    {
        abort_unless(Auth::user()->hasRole('admin'), 403);

        AbsensiRonda::updateOrCreate(
            $request->validate([
                'jadwal_ronda_id' => ['required', 'exists:jadwal_rondas,id'],
                'user_id' => ['required', 'exists:users,id'],
            ]),
            [
                'status' => $request->validate(['status' => ['required', 'in:hadir,izin,alpa']])['status'],
                'waktu_absen' => now(),
                'recorded_by' => Auth::id(),
                'catatan' => $request->input('catatan'),
            ]
        );

        return back()->with('success', 'Absensi ronda berhasil disimpan.');
    }

    public function storePanicReport(Request $request)
    {
        \App\Models\PanicReport::create([
            'user_id' => Auth::id(),
            'judul' => 'Panic Button',
            'deskripsi' => $request->input('deskripsi', 'Laporan darurat dari warga.'),
            'status' => 'aktif',
        ]);

        $this->notifyWarga('Panic Button Aktif', 'Ada laporan darurat dari warga sekitar. Mohon waspada.');

        return back()->with('success', 'Panic Button terkirim ke pengurus dan warga sekitar.');
    }

    public function kegiatanWarga()
    {
        $isPengurus = $this->isPengurus();
        $kegiatans = Kegiatan::with('rsvps')->latest('tanggal_mulai')->get();

        return view('layanan.kegiatan-warga', [
            'isPengurus' => $isPengurus,
            'kegiatans' => $kegiatans,
            'direktori' => $this->wargaQuery()->with('profile')->orderBy('name')->get(),
        ]);
    }

    public function storeKegiatan(Request $request)
    {
        abort_unless($this->isPengurus(), 403);

        Kegiatan::create($request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]) + ['status' => 'terbit', 'created_by' => Auth::id()]);

        return back()->with('success', 'Agenda kegiatan berhasil dibuat.');
    }

    public function rsvpKegiatan(Request $request, Kegiatan $kegiatan)
    {
        RsvpKegiatan::updateOrCreate(
            ['kegiatan_id' => $kegiatan->id, 'user_id' => Auth::id()],
            $request->validate(['status' => ['required', 'in:hadir,tidak_hadir,izin']])
        );

        return back()->with('success', 'RSVP kegiatan berhasil disimpan.');
    }

    public function aspirasi()
    {
        $isPengurus = $this->isPengurus();

        return view('layanan.aspirasi', [
            'isPengurus' => $isPengurus,
            'aspirasis' => $isPengurus
                ? Aspirasi::with(['pelapor', 'lampirans', 'komentars'])->latest()->get()
                : Aspirasi::with(['lampirans', 'komentars'])->where('user_id', Auth::id())->latest()->get(),
        ]);
    }

    public function storeAspirasi(Request $request)
    {
        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:255'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'anonim' => ['nullable', 'boolean'],
            'foto.*' => ['nullable', 'image', 'max:2048'],
        ]);

        $aspirasi = Aspirasi::create([
            'user_id' => Auth::id(),
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'anonim' => $request->boolean('anonim'),
            'status' => 'pending',
        ]);

        foreach ($request->file('foto', []) as $file) {
            AspirasiLampiran::create([
                'aspirasi_id' => $aspirasi->id,
                'file_path' => $file->store('aspirasi', 'public'),
                'file_type' => $file->getClientMimeType(),
            ]);
        }

        return back()->with('success', 'Aspirasi/pengaduan berhasil dikirim.');
    }

    public function updateAspirasi(Request $request, Aspirasi $aspirasi)
    {
        abort_unless($this->isPengurus(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,diproses,selesai,ditolak'],
            'solusi' => ['nullable', 'string'],
            'komentar' => ['nullable', 'string'],
        ]);

        $aspirasi->update([
            'status' => $validated['status'],
            'solusi' => $validated['solusi'] ?? $aspirasi->solusi,
            'assigned_to' => Auth::id(),
            'resolved_at' => $validated['status'] === 'selesai' ? now() : $aspirasi->resolved_at,
        ]);

        if (! empty($validated['komentar'])) {
            AspirasiKomentar::create([
                'aspirasi_id' => $aspirasi->id,
                'user_id' => Auth::id(),
                'komentar' => $validated['komentar'],
            ]);
        }

        return back()->with('success', 'Aduan warga berhasil diperbarui.');
    }

    public function kasKeuangan()
    {
        $isPengurus = $this->isPengurus();
        $totalPemasukan = Kas::where('jenis', 'masuk')->sum('nominal');
        $totalPengeluaran = Pengeluaran::sum('nominal');

        return view('layanan.kas-keuangan', [
            'isPengurus' => $isPengurus,
            'saldoKas' => $totalPemasukan - $totalPengeluaran,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'iurans' => Iuran::where('status', 'aktif')->orderBy('nama_iuran')->get(),
            'pembayaranSaya' => Pembayaran::with('iuran')->where('user_id', Auth::id())->latest()->get(),
            'pembayaranPending' => $isPengurus ? Pembayaran::with(['user', 'iuran'])->where('status', 'pending')->latest()->get() : collect(),
            'pengeluaranTerbaru' => Pengeluaran::latest()->limit(10)->get(),
        ]);
    }

    private function isPengurus(): bool
    {
        return Auth::check() && in_array(optional(Auth::user()->role)->nama_role, [
            'admin', 'bendahara', 'ketua_rt', 'ketua_rw', 'petugas', 'sekretaris',
        ], true);
    }

    private function wargaQuery()
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', 'warga'));
    }

    private function ensureDefaultJenisSurat(): void
    {
        collect([
            ['kode' => 'DOM', 'nama' => 'Surat Pengantar Domisili'],
            ['kode' => 'KK', 'nama' => 'Pengantar KK'],
            ['kode' => 'SKTM', 'nama' => 'Keterangan Tidak Mampu'],
        ])->each(fn ($item) => JenisSurat::firstOrCreate(['kode' => $item['kode']], $item + ['status' => true]));
    }

    private function ensureDefaultKontakDarurat(): void
    {
        collect([
            ['nama' => 'Polsek Terdekat', 'kategori' => 'Polsek', 'nomor_telepon' => '110'],
            ['nama' => 'Pemadam Kebakaran', 'kategori' => 'Pemadam', 'nomor_telepon' => '113'],
            ['nama' => 'Ambulans', 'kategori' => 'Ambulans', 'nomor_telepon' => '119'],
        ])->each(fn ($item) => KontakDarurat::firstOrCreate(['kategori' => $item['kategori']], $item + ['status' => true]));
    }

    private function ensureDemoUmkmCategories(): void
    {
        if (ProdukUmkm::query()->exists()) {
            return;
        }
    }

    private function splitList(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function notifyWarga(string $judul, string $pesan): void
    {
        $this->wargaQuery()->pluck('id')->each(function ($userId) use ($judul, $pesan) {
            Notifikasi::create([
                'user_id' => $userId,
                'from_user_id' => Auth::id(),
                'tipe' => 'layanan',
                'judul' => $judul,
                'pesan' => $pesan,
                'dibaca' => false,
            ]);
        });
    }
}
