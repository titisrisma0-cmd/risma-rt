<?php

namespace App\Http\Controllers;

use App\Models\AbsensiRonda;
use App\Models\Aspirasi;
use App\Models\AspirasiKomentar;
use App\Models\AspirasiLampiran;
use App\Models\AuditLog;
use App\Models\Balita;
use App\Models\Iuran;
use App\Models\JadwalPosyandu;
use App\Models\JadwalRonda;
use App\Models\JenisSurat;
use App\Models\KartuKeluarga;
use App\Models\Kas;
use App\Models\Kegiatan;
use App\Models\KontakDarurat;
use App\Models\Notifikasi;
use App\Models\PanicReport;
use App\Models\Pembayaran;
use App\Models\PemeriksaanBalita;
use App\Models\PengajuanSurat;
use App\Models\Pengeluaran;
use App\Models\PetugasRonda;
use App\Models\ProdukUmkm;
use App\Models\RsvpKegiatan;
use App\Models\Rt;
use App\Models\TukarJadwalRonda;
use App\Models\Umkm;
use App\Models\User;
use App\Models\UserProfile;
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
        $user = Auth::user();
        $isRw = $user->isAdminRw();
        $isRt = $user->isAdminRt();
        $isPengurus = $isRw || $isRt;
        $myRtId = $user->managedRtId();

        $filterRt = $request->input('rt_id');

        if ($isRt) {
            $umkmsRt = Umkm::with(['pemilik.profile', 'produk'])
                ->where('rt_id', $myRtId)
                ->latest()
                ->get();
            $produkSaya = ProdukUmkm::with('umkm.pemilik')
                ->whereHas('umkm', fn ($u) => $u->where('rt_id', $myRtId))
                ->latest()
                ->get();
            $produkPending = ProdukUmkm::with('umkm.pemilik')
                ->where('status', 'pending')
                ->whereHas('umkm', fn ($u) => $u->where('rt_id', $myRtId))
                ->latest()
                ->get();
            $wargasRt = User::where(function ($q) use ($myRtId) {
                $q->where('rt_id', $myRtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $myRtId));
            })->orderBy('name')->get();
        } elseif ($isRw) {
            $umkmsRt = Umkm::with(['pemilik.profile', 'produk', 'rt'])
                ->when($filterRt, fn ($q) => $q->where('rt_id', $filterRt))
                ->latest()
                ->get();
            $produkSaya = ProdukUmkm::with('umkm.pemilik')
                ->when($filterRt, fn ($q) => $q->whereHas('umkm', fn ($u) => $u->where('rt_id', $filterRt)))
                ->latest()
                ->get();
            $produkPending = ProdukUmkm::with('umkm.pemilik')
                ->where('status', 'pending')
                ->when($filterRt, fn ($q) => $q->whereHas('umkm', fn ($u) => $u->where('rt_id', $filterRt)))
                ->latest()
                ->get();
            $wargasRt = User::orderBy('name')->get();
        } else {
            $umkmsRt = collect();
            $produkSaya = ProdukUmkm::with('umkm.pemilik')
                ->whereHas('umkm', fn ($umkm) => $umkm->where('user_id', $user->id))
                ->latest()
                ->get();
            $produkPending = collect();
            $wargasRt = collect();
        }

        // Public active catalog
        $produk = ProdukUmkm::with('umkm.pemilik')
            ->where('status', 'aktif')
            ->whereHas('umkm', fn ($u) => $u->where('status', 'aktif'))
            ->when($filterRt, fn ($q) => $q->whereHas('umkm', fn ($u) => $u->where('rt_id', $filterRt)))
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

        $kategoriProduk = collect(['Kuliner', 'Jasa', 'Jahit', 'Sembako', 'Kerajinan', 'Fashion', 'Laundry', 'Servis'])
            ->merge(ProdukUmkm::query()->select('kategori')->whereNotNull('kategori')->distinct()->pluck('kategori'))
            ->filter()
            ->unique()
            ->values();

        $umkmStatsPerRt = $isRw
            ? Umkm::select('rt_id', DB::raw('count(*) as total'))->groupBy('rt_id')->pluck('total', 'rt_id')
            : collect();

        return view('layanan.umkm', [
            'isPengurus' => $isPengurus,
            'isRt' => $isRt,
            'isRw' => $isRw,
            'myRtId' => $myRtId,
            'produk' => $produk,
            'kategoriProduk' => $kategoriProduk,
            'tokoSaya' => Umkm::with('produk')->where('user_id', $user->id)->first(),
            'produkSaya' => $produkSaya,
            'produkPending' => $produkPending,
            'umkmsRt' => $umkmsRt,
            'wargasRt' => $wargasRt,
            'rts' => Rt::orderBy('kode_rt')->get(),
            'umkmStatsPerRt' => $umkmStatsPerRt,
            'filterRt' => $filterRt,
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

        $user = Auth::user();
        $rtId = $user->managedRtId() ?? $user->rt_id ?? $user->profile?->rt_id;

        Umkm::updateOrCreate(
            ['user_id' => $user->id],
            $validated + [
                'status' => 'aktif',
                'rt_id' => $rtId,
                'cakupan' => 'rt',
            ]
        );

        return back()->with('success', 'Profil Toko Saya berhasil disimpan.');
    }

    public function storeUmkmPengurus(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw(), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nama_usaha' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'alamat' => ['nullable', 'string'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
            'cakupan' => ['nullable', 'in:rt,rw'],
        ]);

        $owner = User::findOrFail($validated['user_id']);
        $rtId = $user->isAdminRt()
            ? $user->managedRtId()
            : ($request->input('rt_id') ?? $owner->managedRtId() ?? $owner->profile?->rt_id);

        Umkm::updateOrCreate(
            ['user_id' => $validated['user_id']],
            $validated + [
                'rt_id' => $rtId,
                'status' => $validated['status'] ?? 'aktif',
                'cakupan' => $user->isAdminRw() ? ($validated['cakupan'] ?? 'rt') : 'rt',
            ]
        );

        return back()->with('success', 'Data UMKM warga berhasil disimpan oleh pengurus.');
    }

    public function toggleUmkmStatus(Request $request, Umkm $umkm)
    {
        $user = Auth::user();
        $isOwner = (int) $umkm->user_id === (int) $user->id;
        $isMyRt = $user->isAdminRt() && (int) $umkm->rt_id === (int) $user->managedRtId();
        abort_unless($isOwner || $isMyRt || $user->isAdminRw(), 403);

        $newStatus = $umkm->status === 'aktif' ? 'nonaktif' : 'aktif';
        $umkm->update(['status' => $newStatus]);

        return back()->with('success', "Status toko {$umkm->nama_usaha} diubah menjadi {$newStatus}.");
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

        $user = Auth::user();
        $rtId = $user->managedRtId() ?? $user->rt_id ?? $user->profile?->rt_id;

        $umkm = Umkm::firstOrCreate(
            ['user_id' => $user->id],
            [
                'nama_usaha' => 'Toko ' . $user->name,
                'kategori' => 'Umum',
                'status' => 'aktif',
                'rt_id' => $rtId,
                'cakupan' => 'rt',
            ]
        );

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('umkm', 'public');
        }

        $umkm->produk()->create($validated + ['status' => 'pending']);

        return back()->with('success', 'Produk berhasil dikirim dan menunggu verifikasi pengurus.');
    }

    public function moderateProdukUmkm(Request $request, ProdukUmkm $produk)
    {
        $user = Auth::user();
        $isMyRt = $user->isAdminRt() && (int) $produk->umkm?->rt_id === (int) $user->managedRtId();
        abort_unless($user->isAdminRw() || $isMyRt, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:aktif,ditolak,nonaktif'],
            'catatan_moderasi' => ['nullable', 'string'],
        ]);

        $produk->update($validated + [
            'moderated_by' => $user->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Status produk UMKM berhasil diperbarui.');
    }

    public function updateProdukUmkm(Request $request, ProdukUmkm $produk)
    {
        $this->authorizeProdukUmkm($produk);
        $validated = $request->validate($this->produkUmkmRules());
        $user = Auth::user();

        if ($request->hasFile('foto')) {
            if ($produk->foto) {
                Storage::disk('public')->delete($produk->foto);
            }
            $validated['foto'] = $request->file('foto')->store('umkm', 'public');
        }

        if (! $user->isAdminRw() && ! ($user->isAdminRt() && (int) $produk->umkm?->rt_id === (int) $user->managedRtId())) {
            $validated['status'] = 'pending';
            $validated['moderated_by'] = null;
            $validated['moderated_at'] = null;
            $validated['catatan_moderasi'] = null;
        }

        $produk->update($validated);

        return back()->with('success', 'Produk berhasil diperbarui.');
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
        $user = Auth::user();
        $isOwner = $produk->umkm()->where('user_id', $user->id)->exists();
        $isMyRt = $user->isAdminRt() && (int) $produk->umkm?->rt_id === (int) $user->managedRtId();
        abort_unless($isOwner || $isMyRt || $user->isAdminRw(), 403);
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

    public function surat(Request $request)
    {
        $this->ensureDefaultJenisSurat();
        $user = Auth::user();
        $isPengurus = $user->isAdminRw() || $user->isAdminRt();

        $suratMasuk = collect();
        if ($user->isAdminRt()) {
            $suratMasuk = PengajuanSurat::with(['user.profile', 'jenisSurat', 'rt'])
                ->where('rt_id', $user->managedRtId())
                ->latest()
                ->get();
        } elseif ($user->isAdminRw()) {
            $query = PengajuanSurat::with(['user.profile', 'jenisSurat', 'rt'])->latest();
            if ($request->filled('rt_id')) {
                $query->where('rt_id', $request->rt_id);
            }
            $suratMasuk = $query->get();
        }

        return view('layanan.surat', [
            'isPengurus' => $isPengurus,
            'isRt' => $user->isAdminRt(),
            'isRw' => $user->isAdminRw(),
            'jenisSurats' => JenisSurat::where('status', true)->orderBy('nama')->get(),
            'pengajuanSaya' => PengajuanSurat::with(['jenisSurat', 'rt'])->where('user_id', $user->id)->latest()->get(),
            'suratMasuk' => $suratMasuk,
            'rts' => $user->isAdminRw() ? Rt::orderBy('kode_rt')->get() : collect(),
        ]);
    }

    public function storeSurat(Request $request)
    {
        $user = Auth::user();
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

        $rtId = $user->managedRtId() ?? optional($user->profile)->rt_id ?? $user->rt_id;

        $pengajuan = PengajuanSurat::create([
            'user_id' => $user->id,
            'jenis_surat_id' => $validated['jenis_surat_id'],
            'rt_id' => $rtId,
            'target_level' => 'rt',
            'nomor_pengajuan' => 'SUR-' . now()->format('YmdHis') . '-' . $user->id,
            'keperluan' => $validated['keperluan'],
            'berkas_pendukung' => $berkas,
            'status' => 'pending',
        ]);

        // Notify Ketua RT
        if ($rtId) {
            $rt = Rt::find($rtId);
            if ($rt && $rt->ketua_user_id) {
                Notifikasi::create([
                    'user_id' => $rt->ketua_user_id,
                    'from_user_id' => $user->id,
                    'tipe' => 'surat',
                    'judul' => 'Permohonan Surat Baru',
                    'pesan' => "Warga {$user->name} mengajukan permohonan surat ({$pengajuan->nomor_pengajuan}) untuk diverifikasi RT.",
                    'dibaca' => false,
                ]);
            }
        }

        return back()->with('success', 'Pengajuan surat berhasil dikirim ke RT.');
    }

    public function updateSurat(Request $request, PengajuanSurat $pengajuanSurat)
    {
        $user = Auth::user();
        $isRw = $user->isAdminRw();
        $isRt = $user->isAdminRt();
        abort_unless($isRw || $isRt, 403);

        if ($isRt) {
            abort_unless($pengajuanSurat->rt_id === $user->managedRtId(), 403, 'Anda tidak berhak memproses surat di luar wilayah RT Anda.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:diproses,ditolak,siap_diambil,selesai,menunggu_rw,disetujui'],
            'catatan' => ['nullable', 'string'],
            'kebutuhan_rw' => ['nullable', 'boolean'],
        ]);

        $targetStatus = $validated['status'];
        if ($targetStatus === 'disetujui') {
            $targetStatus = 'siap_diambil';
        }

        $data = [
            'status' => $targetStatus,
            'catatan' => $validated['catatan'] ?? $pengajuanSurat->catatan,
        ];

        if ($isRt) {
            $data['catatan_rt'] = $validated['catatan'] ?? $pengajuanSurat->catatan_rt;
            $data['rt_approved_by'] = $user->id;
            $data['rt_approved_at'] = now();

            if ($targetStatus === 'menunggu_rw' || !empty($validated['kebutuhan_rw'])) {
                $data['status'] = 'menunggu_rw';
                $data['kebutuhan_rw'] = true;
                $data['target_level'] = 'rw';
            } elseif (in_array($targetStatus, ['siap_diambil', 'selesai'], true) && ! $pengajuanSurat->nomor_surat) {
                $rtKode = $user->getRtKode() ?? '01';
                $data['nomor_surat'] = 'RT' . $rtKode . '/SUR/' . now()->format('Y/m') . '/' . str_pad((string) $pengajuanSurat->id, 4, '0', STR_PAD_LEFT);
                $data['approved_by'] = $user->id;
                $data['approved_at'] = now();
            }
        } elseif ($isRw) {
            $data['catatan_rw'] = $validated['catatan'] ?? $pengajuanSurat->catatan_rw;
            $data['rw_approved_by'] = $user->id;
            $data['rw_approved_at'] = now();
            $data['approved_by'] = $user->id;
            $data['approved_at'] = now();

            if (in_array($targetStatus, ['siap_diambil', 'selesai'], true) && ! $pengajuanSurat->nomor_surat) {
                $data['nomor_surat'] = 'RW/SUR/' . now()->format('Y/m') . '/' . str_pad((string) $pengajuanSurat->id, 4, '0', STR_PAD_LEFT);
            }
        }

        $pengajuanSurat->update($data);

        // Record Audit Log
        AuditLog::record(
            action: 'surat_' . $targetStatus,
            entityType: 'pengajuan_surat',
            entityId: $pengajuanSurat->id,
            deskripsi: "Status permohonan surat {$pengajuanSurat->nomor_pengajuan} diubah menjadi {$targetStatus} oleh " . $user->name,
            rtId: $pengajuanSurat->rt_id,
            userId: $user->id
        );

        // If escalated to RW, notify RW
        if ($data['status'] === 'menunggu_rw') {
            $rwUsers = User::whereHas('role', fn ($r) => $r->whereIn('nama_role', ['admin', 'rw', 'admin_rw']))->get();
            foreach ($rwUsers as $rwUser) {
                Notifikasi::create([
                    'user_id' => $rwUser->id,
                    'from_user_id' => $user->id,
                    'tipe' => 'surat',
                    'judul' => 'Pengajuan Memerlukan Persetujuan RW',
                    'pesan' => "Permohonan surat {$pengajuanSurat->nomor_pengajuan} dari RT {$user->getRtKode()} memerlukan persetujuan RW.",
                    'dibaca' => false,
                ]);
            }
        }

        // Notify Warga
        Notifikasi::create([
            'user_id' => $pengajuanSurat->user_id,
            'from_user_id' => $user->id,
            'tipe' => 'surat',
            'judul' => 'Update Status Permohonan Surat',
            'pesan' => "Permohonan surat Anda ({$pengajuanSurat->nomor_pengajuan}) telah diperbarui menjadi: " . $pengajuanSurat->status_label . ($validated['catatan'] ? ". Catatan: {$validated['catatan']}" : ''),
            'dibaca' => false,
        ]);

        return back()->with('success', 'Status surat berhasil diperbarui.');
    }

    public function cetakSurat(PengajuanSurat $pengajuanSurat)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRw() || $user->isAdminRt() || $pengajuanSurat->user_id === $user->id, 403);
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

    public function posyandu(Request $request)
    {
        $user = Auth::user();
        $isRw = $user->isAdminRw();
        $isRt = $user->isAdminRt();
        $isPengurus = $isRw || $isRt || $user->hasRole('admin');
        $myRtId = $user->managedRtId();

        $filterRt = $request->input('rt_id');

        $kartuKeluargasQuery = KartuKeluarga::with(['kepalaKeluarga.profile', 'anggota']);
        if ($isRt) {
            $kartuKeluargasQuery->where('rt_id', $myRtId);
            $jadwals = JadwalPosyandu::with('rt')
                ->where(fn ($q) => $q->where('rt_id', $myRtId)->orWhereNull('rt_id'))
                ->latest('tanggal_mulai')
                ->get();

            $balitas = Balita::with(['orangTua.profile', 'userProfile', 'kartuKeluarga', 'pemeriksaan' => fn ($q) => $q->orderByDesc('tanggal_periksa')])
                ->where(function ($q) use ($myRtId) {
                    $q->where('rt_id', $myRtId)
                        ->orWhereHas('kartuKeluarga', fn ($kk) => $kk->where('rt_id', $myRtId))
                        ->orWhereHas('orangTua', fn ($u) => $u->where('rt_id', $myRtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $myRtId)));
                })
                ->orderBy('nama')
                ->get();

            $wargas = $this->wargaQuery()
                ->where(fn ($q) => $q->where('rt_id', $myRtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $myRtId)))
                ->orderBy('name')
                ->get();
        } elseif ($isRw) {
            if ($filterRt) {
                $kartuKeluargasQuery->where('rt_id', $filterRt);
            }
            $jadwals = JadwalPosyandu::with('rt')
                ->when($filterRt, fn ($q) => $q->where('rt_id', $filterRt))
                ->latest('tanggal_mulai')
                ->get();

            $balitas = Balita::with(['orangTua.profile', 'userProfile', 'kartuKeluarga', 'pemeriksaan' => fn ($q) => $q->orderByDesc('tanggal_periksa')])
                ->when($filterRt, function ($q) use ($filterRt) {
                    $q->where('rt_id', $filterRt)
                        ->orWhereHas('kartuKeluarga', fn ($kk) => $kk->where('rt_id', $filterRt))
                        ->orWhereHas('orangTua', fn ($u) => $u->where('rt_id', $filterRt)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $filterRt)));
                })
                ->orderBy('nama')
                ->get();

            $wargas = $this->wargaQuery()->orderBy('name')->get();
        } else {
            // Warga view (strict privacy)
            $jadwals = JadwalPosyandu::with('rt')
                ->where(function ($q) use ($myRtId) {
                    if ($myRtId) {
                        $q->where('rt_id', $myRtId)->orWhereNull('rt_id');
                    }
                })
                ->latest('tanggal_mulai')
                ->get();

            $userKkId = $user->profile?->kartu_keluarga_id ?? $user->kartuKeluarga?->id;
            $balitas = Balita::with(['orangTua', 'userProfile', 'kartuKeluarga', 'pemeriksaan' => fn ($q) => $q->orderByDesc('tanggal_periksa')])
                ->where(function ($q) use ($user, $userKkId) {
                    $q->where('orang_tua_user_id', $user->id);
                    if ($userKkId) {
                        $q->orWhere('kartu_keluarga_id', $userKkId);
                    }
                })
                ->orderBy('nama')
                ->get();

            $wargas = collect();
        }

        $kartuKeluargas = $isPengurus ? $kartuKeluargasQuery->orderBy('no_kk')->get() : collect();

        $registeredUserProfileIds = Balita::whereNotNull('user_profile_id')->pluck('user_profile_id')->toArray();
        $registeredNiks = Balita::whereNotNull('nik')->pluck('nik')->filter()->toArray();

        $kkOptions = $kartuKeluargas->map(function ($kk) use ($registeredUserProfileIds, $registeredNiks) {
            $kepalaName = $kk->nama_kepala_keluarga;
            $kepalaHp = $kk->kepalaKeluarga?->profile?->no_hp ?? $kk->kepalaKeluarga?->no_hp ?? '';
            $kepalaUserId = $kk->kepala_keluarga_user_id;

            $children = $kk->anggota->map(function ($m) use ($registeredUserProfileIds, $registeredNiks, $kepalaName, $kepalaHp, $kepalaUserId, $kk) {
                $isRegistered = in_array($m->id, $registeredUserProfileIds) || ($m->nik && in_array($m->nik, $registeredNiks));
                $parentName = $m->nama_ayah ?: ($m->nama_ibu ?: $kepalaName);
                $parentHp = $m->no_hp ?: $kepalaHp;

                return [
                    'id' => $m->id,
                    'kartu_keluarga_id' => $kk->id,
                    'no_kk' => $kk->no_kk,
                    'nama' => $m->nama_lengkap,
                    'nik' => $m->nik ?? '',
                    'tanggal_lahir' => $m->tanggal_lahir ? $m->tanggal_lahir->format('Y-m-d') : '',
                    'jenis_kelamin' => $m->jenis_kelamin ?? 'L',
                    'status_hubungan' => $m->status_hubungan_keluarga ?? 'Anak',
                    'nama_orang_tua' => $parentName,
                    'no_hp_orang_tua' => $parentHp,
                    'orang_tua_user_id' => $m->user_id ?: $kepalaUserId,
                    'is_registered' => $isRegistered,
                ];
            })->values();

            return [
                'id' => $kk->id,
                'no_kk' => $kk->no_kk,
                'kepala_keluarga' => $kepalaName,
                'alamat' => $kk->alamat,
                'children' => $children,
            ];
        });

        $stats = [
            'total_balita' => $balitas->count(),
            'laki_laki' => $balitas->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $balitas->where('jenis_kelamin', 'P')->count(),
            'jadwal_mendatang' => $jadwals->where('tanggal_mulai', '>=', now()->startOfDay())->count(),
        ];

        return view('layanan.posyandu', [
            'isPengurus' => $isPengurus,
            'isRt' => $isRt,
            'isRw' => $isRw,
            'myRtId' => $myRtId,
            'jadwals' => $jadwals,
            'balitas' => $balitas,
            'semuaBalita' => $balitas,
            'kartuKeluargas' => $kartuKeluargas,
            'kkOptions' => $kkOptions,
            'wargas' => $wargas,
            'rts' => Rt::orderBy('kode_rt')->get(),
            'stats' => $stats,
            'filterRt' => $filterRt,
        ]);
    }

    public function storeBalita(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'kartu_keluarga_id' => ['nullable', 'exists:kartu_keluargas,id'],
            'user_profile_id' => ['nullable', 'exists:user_profiles,id'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'no_hp_orang_tua' => ['nullable', 'string', 'max:50'],
            'orang_tua_user_id' => ['nullable', 'exists:users,id'],
            'catatan_kesehatan' => ['nullable', 'string'],
            'rt_id' => ['nullable', 'exists:rts,id'],
        ]);

        $myRtId = $user->isAdminRt() ? $user->managedRtId() : null;
        $profile = null;
        if (!empty($validated['user_profile_id'])) {
            $profile = UserProfile::with('kartuKeluarga')->findOrFail($validated['user_profile_id']);

            // RT isolation
            if ($user->isAdminRt()) {
                if ($profile->rt_id && (int) $profile->rt_id !== (int) $myRtId && $profile->kartuKeluarga?->rt_id !== (int) $myRtId) {
                    abort(403, 'Anak yang didaftarkan harus berada di wilayah RT Anda.');
                }
            }

            // Duplicate check by user_profile_id
            if (Balita::where('user_profile_id', $profile->id)->exists()) {
                return back()->withInput()->with('error', 'Anak ini sudah terdaftar dalam data Posyandu.');
            }
        }

        // Duplicate check by NIK
        if (!empty($validated['nik'])) {
            if (Balita::where('nik', $validated['nik'])->exists()) {
                return back()->withInput()->with('error', 'Anak dengan NIK ini sudah terdaftar dalam data Posyandu.');
            }
        }

        $kk = null;
        if (!empty($validated['kartu_keluarga_id'])) {
            $kk = KartuKeluarga::findOrFail($validated['kartu_keluarga_id']);
            if ($user->isAdminRt() && (int) $kk->rt_id !== (int) $myRtId) {
                abort(403, 'Kartu Keluarga yang dipilih berada di luar wilayah RT Anda.');
            }
        }

        $rtId = $myRtId ?: ($validated['rt_id'] ?? $kk?->rt_id ?? $profile?->rt_id);
        $kkId = $kk?->id ?? $profile?->kartu_keluarga_id;
        $orangTuaUserId = $validated['orang_tua_user_id'] ?? $kk?->kepala_keluarga_user_id ?? $profile?->user_id;

        Balita::create([
            'rt_id' => $rtId,
            'kartu_keluarga_id' => $kkId,
            'user_profile_id' => $profile?->id,
            'orang_tua_user_id' => $orangTuaUserId,
            'nama' => $validated['nama'],
            'nik' => $validated['nik'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'nama_orang_tua' => $validated['nama_orang_tua'] ?? null,
            'no_hp_orang_tua' => $validated['no_hp_orang_tua'] ?? null,
            'catatan_kesehatan' => $validated['catatan_kesehatan'] ?? null,
        ]);

        return back()->with('success', 'Data anak Posyandu berhasil didaftarkan langsung dari data Kartu Keluarga.');
    }

    public function updateBalita(Request $request, Balita $balita)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);
        if ($user->isAdminRt() && $balita->rt_id && (int) $balita->rt_id !== (int) $user->managedRtId()) {
            abort(403, 'Akses data balita RT lain ditolak.');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:balitas,nik,' . $balita->id],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'no_hp_orang_tua' => ['nullable', 'string', 'max:50'],
            'orang_tua_user_id' => ['nullable', 'exists:users,id'],
            'catatan_kesehatan' => ['nullable', 'string'],
        ]);

        if (!empty($validated['orang_tua_user_id'])) {
            $parent = User::with('profile')->find($validated['orang_tua_user_id']);
            $validated['kartu_keluarga_id'] = $parent?->profile?->kartu_keluarga_id;
        }

        $balita->update($validated);

        return back()->with('success', 'Data balita berhasil diperbarui.');
    }

    public function destroyBalita(Balita $balita)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);
        if ($user->isAdminRt() && $balita->rt_id && (int) $balita->rt_id !== (int) $user->managedRtId()) {
            abort(403, 'Akses data balita RT lain ditolak.');
        }

        $balita->pemeriksaan()->delete();
        $balita->delete();

        return back()->with('success', 'Data balita berhasil dihapus.');
    }

    public function storeJadwalPosyandu(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'lokasi' => ['required', 'string', 'max:255'],
            'nama_bidan' => ['nullable', 'string', 'max:255'],
            'kontak_bidan' => ['nullable', 'string', 'max:50'],
            'kader_info' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:terjadwal,berlangsung,selesai,ditunda,dibatalkan'],
            'keterangan' => ['nullable', 'string'],
            'rt_id' => ['nullable', 'exists:rts,id'],
        ]);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : ($validated['rt_id'] ?? null);

        $jadwal = JadwalPosyandu::create($validated + [
            'rt_id' => $rtId,
            'status' => $validated['status'] ?? 'terjadwal',
            'created_by' => $user->id,
        ]);

        if ($rtId) {
            $parentIds = Balita::where('rt_id', $rtId)->whereNotNull('orang_tua_user_id')->pluck('orang_tua_user_id')->unique();
            foreach ($parentIds as $pId) {
                Notifikasi::create([
                    'user_id' => $pId,
                    'from_user_id' => $user->id,
                    'tipe' => 'posyandu',
                    'judul' => 'Jadwal Posyandu RT',
                    'pesan' => "Posyandu: {$jadwal->judul} pada " . \Carbon\Carbon::parse($jadwal->tanggal_mulai)->isoFormat('dddd, D MMMM Y HH:mm') . " di {$jadwal->lokasi}.",
                    'dibaca' => false,
                ]);
            }
        }

        return back()->with('success', 'Jadwal Posyandu berhasil dibuat dan disiarkan ke orang tua balita.');
    }

    public function updateJadwalPosyandu(Request $request, JadwalPosyandu $jadwal)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);
        if ($user->isAdminRt() && $jadwal->rt_id && (int) $jadwal->rt_id !== (int) $user->managedRtId()) {
            abort(403, 'Akses jadwal posyandu RT lain ditolak.');
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'lokasi' => ['required', 'string', 'max:255'],
            'nama_bidan' => ['nullable', 'string', 'max:255'],
            'kontak_bidan' => ['nullable', 'string', 'max:50'],
            'kader_info' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:terjadwal,berlangsung,selesai,ditunda,dibatalkan'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $jadwal->update($validated);

        return back()->with('success', 'Jadwal Posyandu berhasil diperbarui.');
    }

    public function destroyJadwalPosyandu(JadwalPosyandu $jadwal)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);
        if ($user->isAdminRt() && $jadwal->rt_id && (int) $jadwal->rt_id !== (int) $user->managedRtId()) {
            abort(403, 'Akses jadwal posyandu RT lain ditolak.');
        }

        $jadwal->delete();

        return back()->with('success', 'Jadwal Posyandu berhasil dihapus.');
    }

    public function storePemeriksaanBalita(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'balita_id' => ['required', 'exists:balitas,id'],
            'jadwal_posyandu_id' => ['nullable', 'exists:jadwal_posyandus,id'],
            'tanggal_periksa' => ['required', 'date'],
            'berat_kg' => ['required', 'numeric', 'min:0'],
            'tinggi_cm' => ['required', 'numeric', 'min:0'],
            'lingkar_kepala_cm' => ['nullable', 'numeric', 'min:0'],
            'lingkar_lengan_cm' => ['nullable', 'numeric', 'min:0'],
            'status_gizi' => ['nullable', 'string', 'max:50'],
            'imunisasi_sudah' => ['nullable', 'string'],
            'imunisasi_belum' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['imunisasi_sudah'] = $this->splitList($validated['imunisasi_sudah'] ?? null);
        $validated['imunisasi_belum'] = $this->splitList($validated['imunisasi_belum'] ?? null);

        $pemeriksaan = PemeriksaanBalita::create($validated + ['petugas_id' => $user->id]);

        $balita = Balita::find($validated['balita_id']);
        if ($balita && $balita->orang_tua_user_id) {
            Notifikasi::create([
                'user_id' => $balita->orang_tua_user_id,
                'from_user_id' => $user->id,
                'tipe' => 'posyandu',
                'judul' => 'Hasil Pemeriksaan Posyandu',
                'pesan' => "Pemeriksaan tumbuh kembang ananda {$balita->nama} telah dicatat: Berat {$validated['berat_kg']} kg, Tinggi {$validated['tinggi_cm']} cm, Status Gizi: " . ($validated['status_gizi'] ?? 'Baik') . ".",
                'dibaca' => false,
            ]);
        }

        return back()->with('success', 'Hasil pemeriksaan dan tumbuh kembang balita berhasil disimpan.');
    }

    public function ronda(Request $request)
    {
        $this->ensureDefaultKontakDarurat();
        $user = Auth::user();
        $isRw = $user->isAdminRw();
        $isRt = $user->isAdminRt();
        $isPengurus = $isRw || $isRt || $user->hasRole('admin');
        $myRtId = $user->managedRtId();

        if ($isRt) {
            $jadwals = JadwalRonda::with(['petugas.user.profile', 'absensi.user'])
                ->where(fn ($q) => $q->where('rt_id', $myRtId)->orWhereNull('rt_id'))
                ->latest('tanggal')
                ->limit(60)
                ->get();

            $tukarJadwals = TukarJadwalRonda::with(['user', 'targetUser', 'jadwalRondaAsal', 'targetJadwalRonda'])
                ->where('rt_id', $myRtId)
                ->latest()
                ->get();

            $panicReports = PanicReport::with(['user.profile', 'handler'])
                ->where(fn ($q) => $q->where('rt_id', $myRtId)->orWhereNull('rt_id'))
                ->latest()
                ->get();

            $wargas = UserProfile::with(['user', 'kartuKeluarga', 'wilayahRt'])
                ->where(function ($q) use ($myRtId) {
                    $q->where('rt_id', $myRtId)
                      ->orWhereHas('user', fn ($u) => $u->where('rt_id', $myRtId));
                })
                ->where(fn ($q) => $q->where('status', 'aktif')->orWhereNull('status'))
                ->orderBy('nama_lengkap')
                ->get();
        } elseif ($isRw) {
            $filterRt = $request->input('rt_id');
            $jadwals = JadwalRonda::with(['petugas.user.profile', 'absensi.user'])
                ->when($filterRt, fn ($q) => $q->where('rt_id', $filterRt))
                ->latest('tanggal')
                ->limit(60)
                ->get();

            $tukarJadwals = TukarJadwalRonda::with(['user', 'targetUser', 'jadwalRondaAsal', 'targetJadwalRonda'])
                ->when($filterRt, fn ($q) => $q->where('rt_id', $filterRt))
                ->latest()
                ->get();

            $panicReports = PanicReport::with(['user.profile', 'handler'])
                ->when($filterRt, fn ($q) => $q->where('rt_id', $filterRt))
                ->latest()
                ->get();

            $wargas = UserProfile::with(['user', 'kartuKeluarga', 'wilayahRt'])
                ->when($filterRt, function ($q) use ($filterRt) {
                    $q->where(function ($sq) use ($filterRt) {
                        $sq->where('rt_id', $filterRt)
                           ->orWhereHas('user', fn ($u) => $u->where('rt_id', $filterRt));
                    });
                })
                ->where(fn ($q) => $q->where('status', 'aktif')->orWhereNull('status'))
                ->orderBy('nama_lengkap')
                ->get();
        } else {
            $jadwals = JadwalRonda::with(['petugas.user', 'absensi.user'])
                ->where(function ($q) use ($myRtId) {
                    if ($myRtId) {
                        $q->where('rt_id', $myRtId)->orWhereNull('rt_id');
                    }
                })
                ->latest('tanggal')
                ->limit(30)
                ->get();

            $tukarJadwals = TukarJadwalRonda::with(['user', 'targetUser', 'jadwalRondaAsal', 'targetJadwalRonda'])
                ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('target_user_id', $user->id))
                ->latest()
                ->get();

            $panicReports = PanicReport::with(['user.profile', 'handler'])
                ->where(function ($q) use ($myRtId) {
                    if ($myRtId) {
                        $q->where('rt_id', $myRtId)->orWhereNull('rt_id');
                    }
                })
                ->latest()
                ->limit(10)
                ->get();

            $wargas = UserProfile::with(['user', 'kartuKeluarga', 'wilayahRt'])
                ->where(function ($q) use ($myRtId) {
                    if ($myRtId) {
                        $q->where('rt_id', $myRtId)
                          ->orWhereHas('user', fn ($u) => $u->where('rt_id', $myRtId));
                    }
                })
                ->where(fn ($q) => $q->where('status', 'aktif')->orWhereNull('status'))
                ->orderBy('nama_lengkap')
                ->get();
        }

        $nowJakarta = \Carbon\Carbon::now('Asia/Jakarta');
        $todayJakarta = $nowJakarta->toDateString();
        $userProfileId = optional($user->profile)->id;

        $jadwalSaya = $jadwals->filter(function ($jadwal) use ($user, $userProfileId) {
            return $jadwal->petugas->contains(function ($p) use ($user, $userProfileId) {
                return ($p->user_id && $p->user_id === $user->id) ||
                       ($userProfileId && $p->user_profile_id === $userProfileId);
            });
        })->values();

        $activePanicReports = $panicReports->where('status', 'aktif');

        // Pisahkan jadwal hari ini, mendatang, dan riwayat (Asia/Jakarta)
        $jadwalHariIni = $jadwals->filter(fn ($j) => \Carbon\Carbon::parse($j->tanggal)->setTimezone('Asia/Jakarta')->toDateString() === $todayJakarta)->values();
        $jadwalMendatang = $jadwals->filter(fn ($j) => \Carbon\Carbon::parse($j->tanggal)->setTimezone('Asia/Jakarta')->toDateString() > $todayJakarta)->sortBy('tanggal')->values();
        $jadwalRiwayat = $jadwals->filter(fn ($j) => \Carbon\Carbon::parse($j->tanggal)->setTimezone('Asia/Jakarta')->toDateString() < $todayJakarta)->sortByDesc('tanggal')->values();

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $jadwalIdsInMonth = $jadwals->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->pluck('id');
        $absensiRecap = [
            'hadir' => AbsensiRonda::whereIn('jadwal_ronda_id', $jadwalIdsInMonth)->where('status', 'hadir')->count(),
            'izin' => AbsensiRonda::whereIn('jadwal_ronda_id', $jadwalIdsInMonth)->where('status', 'izin')->count(),
            'alpa' => AbsensiRonda::whereIn('jadwal_ronda_id', $jadwalIdsInMonth)->where('status', 'alpa')->count(),
        ];

        // Format data jadwal untuk kalender frontend interaktif
        $calendarJadwals = $jadwals->map(function ($j) use ($user, $userProfileId, $tukarJadwals) {
            $tukarReq = $tukarJadwals->firstWhere('jadwal_ronda_asal_id', $j->id);
            $hasSwapPending = $tukarReq && in_array($tukarReq->status, ['menunggu', 'menunggu_rekan', 'menunggu_rt']);
            
            $myAbsensi = $j->absensi->first(function ($a) use ($user, $userProfileId) {
                return ($a->user_id && $a->user_id === $user->id) ||
                       ($userProfileId && $a->user_profile_id === $userProfileId);
            });

            $isMine = $j->petugas->contains(function ($p) use ($user, $userProfileId) {
                return ($p->user_id && $p->user_id === $user->id) ||
                       ($userProfileId && $p->user_profile_id === $userProfileId);
            });

            return [
                'id' => $j->id,
                'tanggal' => $j->tanggal instanceof \Carbon\CarbonInterface ? $j->tanggal->format('Y-m-d') : substr((string)$j->tanggal, 0, 10),
                'shift' => ucfirst($j->shift),
                'jam_shift' => ($j->jam_mulai && $j->jam_selesai) ? "{$j->jam_mulai} - {$j->jam_selesai}" : null,
                'lokasi' => $j->lokasi_pos ?: 'Pos Ronda RT',
                'petugas' => $j->petugas->map(function ($p) {
                    return $p->nama_petugas;
                })->values()->toArray(),
                'petugas_count' => $j->petugas->count(),
                'is_mine' => $isMine,
                'has_swap_pending' => $hasSwapPending,
                'my_absensi' => $myAbsensi ? $myAbsensi->status : null,
                'status' => $j->status,
            ];
        })->values();

        return view('layanan.ronda', [
            'isPengurus' => $isPengurus,
            'isRt' => $isRt,
            'isRw' => $isRw,
            'myRtId' => $myRtId,
            'kontaks' => KontakDarurat::where('status', true)->orderBy('kategori')->get(),
            'jadwals' => $jadwals,
            'jadwalSaya' => $jadwalSaya,
            'jadwalHariIni' => $jadwalHariIni,
            'jadwalMendatang' => $jadwalMendatang,
            'jadwalRiwayat' => $jadwalRiwayat,
            'todayJakarta' => $todayJakarta,
            'tukarJadwals' => $tukarJadwals,
            'wargas' => $wargas,
            'panicReports' => $panicReports,
            'activePanicReports' => $activePanicReports,
            'absensiRecap' => $absensiRecap,
            'calendarJadwals' => $calendarJadwals,
            'rts' => Rt::orderBy('kode_rt')->get(),
        ]);
    }

    public function storeJadwalRonda(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        DB::transaction(function () use ($request, $user) {
            $validated = $request->validate([
                'mode' => ['nullable', 'in:manual,acak'],
                'tanggal' => ['required', 'date'],
                'jumlah_hari' => ['nullable', 'integer', 'min:1', 'max:31'],
                'jumlah_petugas' => ['nullable', 'integer', 'min:1', 'max:10'],
                'shift' => ['required', 'string', 'max:255'],
                'jam_mulai' => ['nullable', 'string', 'max:10'],
                'jam_selesai' => ['nullable', 'string', 'max:10'],
                'lokasi_pos' => ['nullable', 'string', 'max:255'],
                'petugas' => ['nullable', 'array'],
                'rt_id' => ['nullable', 'exists:rts,id'],
            ]);

            $rtId = $user->isAdminRt() ? $user->managedRtId() : ($validated['rt_id'] ?? null);
            $mode = $validated['mode'] ?? 'manual';
            $jumlahHari = $mode === 'acak' ? (int) ($validated['jumlah_hari'] ?? 7) : 1;
            $jumlahPetugas = (int) ($validated['jumlah_petugas'] ?? 3);
            $poolPetugas = collect($validated['petugas'] ?? []);

            if ($mode === 'acak' && $poolPetugas->isEmpty()) {
                $poolPetugas = UserProfile::where('rt_id', $rtId)
                    ->where('status', 'aktif')
                    ->where('jenis_kelamin', 'L')
                    ->pluck('id');
            }

            for ($index = 0; $index < $jumlahHari; $index++) {
                $jadwal = JadwalRonda::create([
                    'rt_id' => $rtId,
                    'tanggal' => \Carbon\Carbon::parse($validated['tanggal'])->addDays($index),
                    'shift' => $validated['shift'],
                    'jam_mulai' => $validated['jam_mulai'] ?? '22:00',
                    'jam_selesai' => $validated['jam_selesai'] ?? '02:00',
                    'lokasi_pos' => $validated['lokasi_pos'] ?? 'Pos Ronda RT',
                    'created_by' => $user->id,
                    'status' => 'aktif',
                ]);

                $petugasTerpilih = $mode === 'acak'
                    ? $poolPetugas->shuffle()->take($jumlahPetugas)
                    : $poolPetugas;

                foreach ($petugasTerpilih as $officerId) {
                    $profile = UserProfile::find($officerId);
                    $profileId = null;
                    $userId = null;

                    if ($profile) {
                        $profileId = $profile->id;
                        $userId = $profile->user_id;
                    } else {
                        $u = User::find($officerId);
                        if ($u) {
                            $userId = $u->id;
                            $profileId = optional($u->profile)->id;
                        }
                    }

                    if ($profileId || $userId) {
                        PetugasRonda::firstOrCreate([
                            'jadwal_ronda_id' => $jadwal->id,
                            'user_profile_id' => $profileId,
                            'user_id' => $userId,
                        ]);

                        if ($userId) {
                            Notifikasi::create([
                                'user_id' => $userId,
                                'from_user_id' => $user->id,
                                'tipe' => 'ronda',
                                'judul' => 'Jadwal Tugas Ronda RT',
                                'pesan' => "Anda dijadwalkan ronda pada " . $jadwal->tanggal->isoFormat('dddd, D MMMM Y') . " ({$jadwal->shift}) di {$jadwal->lokasi_pos}.",
                                'dibaca' => false,
                            ]);
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Jadwal ronda berhasil dibuat dan notifikasi tugas telah dikirim.');
    }

    public function generatePreviewRonda(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2050'],
            'jumlah_petugas' => ['required', 'integer', 'min:1', 'max:10'],
            'jumlah_shift' => ['required', 'integer', 'in:1,2'],
            'jam_mulai_1' => ['required', 'string'],
            'jam_selesai_1' => ['required', 'string'],
            'jam_mulai_2' => ['nullable', 'string'],
            'jam_selesai_2' => ['nullable', 'string'],
            'lokasi_pos' => ['nullable', 'string', 'max:255'],
            'rt_id' => ['nullable', 'exists:rts,id'],
        ]);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : ($validated['rt_id'] ?? null);
        $bulan = (int) $validated['bulan'];
        $tahun = (int) $validated['tahun'];
        $jumlahPetugas = (int) $validated['jumlah_petugas'];
        $jumlahShift = (int) $validated['jumlah_shift'];
        $lokasiPos = $validated['lokasi_pos'] ?: ('Pos Ronda RT ' . ($user->isAdminRt() ? $user->getRtKode() : ''));

        // Query eligible citizens:
        // - Laki-laki
        // - Status aktif di RT ini
        // - siap_ronda != false (null or true)
        // - Bukan lansia (>= 60) kecuali siap_ronda == true
        // - Usia >= 17 atau menikah jika tanggal_lahir ada
        $eligibleProfiles = UserProfile::with('user')
            ->where('rt_id', $rtId)
            ->where('status', 'aktif')
            ->where('jenis_kelamin', 'L')
            ->where(function ($q) {
                $q->whereNull('siap_ronda')->orWhere('siap_ronda', true);
            })
            ->get()
            ->filter(function ($profile) {
                if ($profile->tanggal_lahir) {
                    $age = $profile->age;
                    if ($age !== null && $age < 17 && !in_array(strtolower((string) $profile->status_perkawinan), ['kawin', 'menikah'])) {
                        return false;
                    }
                    if ($age !== null && $age >= 60 && $profile->siap_ronda !== true) {
                        return false;
                    }
                }
                return true;
            })
            ->values();

        if ($eligibleProfiles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada warga laki-laki dewasa yang eligible untuk jadwal ronda pada wilayah RT ini. Pastikan data kependudukan warga telah ditambahkan.',
            ], 422);
        }

        $startDate = \Carbon\Carbon::create($tahun, $bulan, 1, 0, 0, 0, 'Asia/Jakarta');
        $daysInMonth = $startDate->daysInMonth;

        // Separate priorities: Kepala Keluarga prioritized
        $kepalaKeluarga = $eligibleProfiles->filter(fn($p) => strtolower((string)$p->status_hubungan_keluarga) === 'kepala keluarga')->shuffle()->values();
        $anggotaLain = $eligibleProfiles->reject(fn($p) => strtolower((string)$p->status_hubungan_keluarga) === 'kepala keluarga')->shuffle()->values();

        $pool = $kepalaKeluarga->concat($anggotaLain)->values();

        // Track assignments and spacing
        $dutyCounts = [];
        $lastDutyDay = [];
        foreach ($pool as $p) {
            $dutyCounts[$p->id] = 0;
            $lastDutyDay[$p->id] = -999;
        }

        $jadwalList = [];
        $shiftConfigs = [
            1 => [
                'name' => 'Shift 1',
                'mulai' => $validated['jam_mulai_1'],
                'selesai' => $validated['jam_selesai_1'],
            ]
        ];

        if ($jumlahShift === 2) {
            $shiftConfigs[2] = [
                'name' => 'Shift 2',
                'mulai' => $validated['jam_mulai_2'] ?: '02:00',
                'selesai' => $validated['jam_selesai_2'] ?: '05:00',
            ];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::create($tahun, $bulan, $day, 0, 0, 0, 'Asia/Jakarta');
            $dateStr = $date->format('Y-m-d');
            $dateFormatted = $date->isoFormat('dddd, D MMMM Y');

            $assignedTonight = [];

            foreach ($shiftConfigs as $shiftNum => $shiftConf) {
                $candidates = $pool->filter(fn($p) => !in_array($p->id, $assignedTonight));

                if ($candidates->count() < $jumlahPetugas && $pool->count() < ($jumlahPetugas * $jumlahShift)) {
                    $candidates = $pool; // Fallback only when pool is smaller than required nightly slots
                }

                $sortedCandidates = $candidates->sort(function ($a, $b) use ($dutyCounts, $lastDutyDay, $day) {
                    $countA = $dutyCounts[$a->id] ?? 0;
                    $countB = $dutyCounts[$b->id] ?? 0;
                    if ($countA !== $countB) {
                        return $countA <=> $countB;
                    }
                    $lastA = $lastDutyDay[$a->id] ?? -999;
                    $lastB = $lastDutyDay[$b->id] ?? -999;
                    $restA = $day - $lastA;
                    $restB = $day - $lastB;
                    if ($restA !== $restB) {
                        return $restB <=> $restA;
                    }
                    return rand(-1, 1);
                })->values();

                $chosen = $sortedCandidates->take($jumlahPetugas);

                $petugasData = [];
                foreach ($chosen as $officer) {
                    $assignedTonight[] = $officer->id;
                    $dutyCounts[$officer->id] = ($dutyCounts[$officer->id] ?? 0) + 1;
                    $lastDutyDay[$officer->id] = $day;

                    $petugasData[] = [
                        'user_profile_id' => $officer->id,
                        'user_id' => $officer->user_id,
                        'nama' => $officer->nama_lengkap ?? optional($officer->user)->name ?? 'Warga',
                        'nik' => $officer->nik,
                        'no_hp' => $officer->no_hp,
                    ];
                }

                $jadwalList[] = [
                    'tanggal' => $dateStr,
                    'tanggal_formatted' => $dateFormatted,
                    'shift' => $shiftConf['name'],
                    'jam_mulai' => $shiftConf['mulai'],
                    'jam_selesai' => $shiftConf['selesai'],
                    'lokasi_pos' => $lokasiPos,
                    'petugas' => $petugasData,
                ];
            }
        }

        $petugasRekap = $pool->map(function ($p) use ($dutyCounts) {
            return [
                'user_profile_id' => $p->id,
                'nama' => $p->nama_lengkap ?? optional($p->user)->name,
                'is_kk' => strtolower((string)$p->status_hubungan_keluarga) === 'kepala keluarga',
                'total_tugas' => $dutyCounts[$p->id] ?? 0,
            ];
        })->sortByDesc('total_tugas')->values();

        $warning = null;
        if ($pool->count() < ($jumlahPetugas * $jumlahShift)) {
            $warning = "Perhatian: Jumlah warga laki-laki eligible di RT ini ({$pool->count()} orang) lebih sedikit dari total kebutuhan per malam (" . ($jumlahPetugas * $jumlahShift) . " petugas). Jadwal didistribusikan semaksimal mungkin.";
        }

        return response()->json([
            'success' => true,
            'meta' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'nama_bulan' => $startDate->isoFormat('MMMM Y'),
                'total_jadwal' => count($jadwalList),
                'total_eligible' => $pool->count(),
                'jumlah_petugas' => $jumlahPetugas,
                'jumlah_shift' => $jumlahShift,
                'lokasi_pos' => $lokasiPos,
                'warning' => $warning,
            ],
            'available_officers' => $pool->map(fn($p) => [
                'user_profile_id' => $p->id,
                'user_id' => $p->user_id,
                'nama' => $p->nama_lengkap ?? optional($p->user)->name,
            ])->values(),
            'petugas_rekap' => $petugasRekap,
            'jadwals' => $jadwalList,
        ]);
    }

    public function simpanJadwalOtomatis(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'jadwals' => ['required', 'array', 'min:1'],
            'jadwals.*.tanggal' => ['required', 'date'],
            'jadwals.*.shift' => ['required', 'string'],
            'jadwals.*.jam_mulai' => ['nullable', 'string'],
            'jadwals.*.jam_selesai' => ['nullable', 'string'],
            'jadwals.*.lokasi_pos' => ['nullable', 'string'],
            'jadwals.*.petugas' => ['required', 'array', 'min:1'],
            'jadwals.*.petugas.*.user_profile_id' => ['required', 'exists:user_profiles,id'],
            'jadwals.*.petugas.*.user_id' => ['nullable'],
            'rt_id' => ['nullable', 'exists:rts,id'],
        ]);

        $rtId = $user->isAdminRt() ? $user->managedRtId() : ($validated['rt_id'] ?? null);

        $savedCount = DB::transaction(function () use ($validated, $rtId, $user) {
            $count = 0;
            $notifiedUsers = [];

            foreach ($validated['jadwals'] as $item) {
                $jadwal = JadwalRonda::create([
                    'rt_id' => $rtId,
                    'tanggal' => $item['tanggal'],
                    'shift' => $item['shift'],
                    'jam_mulai' => $item['jam_mulai'] ?? '22:00',
                    'jam_selesai' => $item['jam_selesai'] ?? '02:00',
                    'lokasi_pos' => $item['lokasi_pos'] ?? 'Pos Ronda RT',
                    'created_by' => $user->id,
                    'status' => 'aktif',
                ]);

                foreach ($item['petugas'] as $pData) {
                    $profileId = $pData['user_profile_id'];
                    $profile = UserProfile::find($profileId);
                    $userId = $pData['user_id'] ?? ($profile ? $profile->user_id : null);

                    PetugasRonda::create([
                        'jadwal_ronda_id' => $jadwal->id,
                        'user_profile_id' => $profileId,
                        'user_id' => $userId,
                    ]);

                    if ($userId && !isset($notifiedUsers[$userId . '_' . $item['tanggal']])) {
                        Notifikasi::create([
                            'user_id' => $userId,
                            'from_user_id' => $user->id,
                            'tipe' => 'ronda',
                            'judul' => 'Jadwal Ronda Malam Baru',
                            'pesan' => "Anda dijadwalkan tugas ronda pada " . \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd, D MMMM Y') . " ({$jadwal->shift}, {$jadwal->jam_mulai}-{$jadwal->jam_selesai}) di {$jadwal->lokasi_pos}.",
                            'dibaca' => false,
                        ]);
                        $notifiedUsers[$userId . '_' . $item['tanggal']] = true;
                    }
                }
                $count++;
            }

            AuditLog::record(
                action: 'generate_jadwal_ronda',
                entityType: 'jadwal_ronda',
                entityId: $rtId,
                deskripsi: "Generate otomatis {$count} jadwal ronda malam oleh {$user->name}",
                rtId: $rtId,
                userId: $user->id
            );

            return $count;
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan {$savedCount} jadwal ronda ke dalam sistem.",
            ]);
        }

        return redirect()->route('layanan.ronda.index')->with('success', "Berhasil menjadwalkan {$savedCount} giliran ronda untuk warga.");
    }

    public function updateJadwalRonda(Request $request, JadwalRonda $jadwal)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);
        if ($user->isAdminRt() && $jadwal->rt_id && (int) $jadwal->rt_id !== (int) $user->managedRtId()) {
            abort(403, 'Akses jadwal ronda RT lain ditolak.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'shift' => ['required', 'string', 'max:255'],
            'lokasi_pos' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:aktif,selesai,dibatalkan'],
            'petugas' => ['nullable', 'array'],
        ]);

        $jadwal->update([
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'lokasi_pos' => $validated['lokasi_pos'] ?? 'Pos Ronda RT',
            'status' => $validated['status'] ?? $jadwal->status,
        ]);

        if (isset($validated['petugas'])) {
            PetugasRonda::where('jadwal_ronda_id', $jadwal->id)->delete();
            foreach ($validated['petugas'] as $officerId) {
                $profile = UserProfile::find($officerId);
                $profileId = null;
                $userId = null;

                if ($profile) {
                    $profileId = $profile->id;
                    $userId = $profile->user_id;
                } else {
                    $u = User::find($officerId);
                    if ($u) {
                        $userId = $u->id;
                        $profileId = optional($u->profile)->id;
                    }
                }

                if ($profileId || $userId) {
                    PetugasRonda::create([
                        'jadwal_ronda_id' => $jadwal->id,
                        'user_profile_id' => $profileId,
                        'user_id' => $userId,
                    ]);

                    if ($userId) {
                        Notifikasi::create([
                            'user_id' => $userId,
                            'from_user_id' => $user->id,
                            'tipe' => 'ronda',
                            'judul' => 'Pembaruan Jadwal Tugas Ronda RT',
                            'pesan' => "Jadwal tugas ronda Anda pada {$jadwal->tanggal->isoFormat('dddd, D MMMM Y')} ({$jadwal->shift}) telah diperbarui oleh Ketua RT.",
                            'dibaca' => false,
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Jadwal ronda berhasil diperbarui.');
    }

    public function destroyJadwalRonda(JadwalRonda $jadwal)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);
        if ($user->isAdminRt() && $jadwal->rt_id && (int) $jadwal->rt_id !== (int) $user->managedRtId()) {
            abort(403, 'Akses jadwal ronda RT lain ditolak.');
        }

        PetugasRonda::where('jadwal_ronda_id', $jadwal->id)->delete();
        AbsensiRonda::where('jadwal_ronda_id', $jadwal->id)->delete();
        $jadwal->delete();

        return back()->with('success', 'Jadwal ronda berhasil dihapus.');
    }

    public function storeAbsensiRonda(Request $request)
    {
        $user = Auth::user();
        $isPengurus = $user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin');

        $validated = $request->validate([
            'jadwal_ronda_id' => ['required', 'exists:jadwal_rondas,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:hadir,izin,alpa'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $jadwal = JadwalRonda::findOrFail($validated['jadwal_ronda_id']);

        if (! $isPengurus) {
            // Warga self-check-in / pengajuan izin
            $isAssigned = $jadwal->petugas()->where('user_id', $user->id)->exists();
            abort_unless($isAssigned, 403, 'Anda tidak terdaftar sebagai petugas pada jadwal shift ronda ini.');
            $targetUserId = $user->id;
        } else {
            $targetUserId = $validated['user_id'] ?? $user->id;
        }

        AbsensiRonda::updateOrCreate(
            [
                'jadwal_ronda_id' => $jadwal->id,
                'user_id' => $targetUserId,
            ],
            [
                'status' => $validated['status'],
                'waktu_absen' => now(),
                'recorded_by' => $user->id,
                'catatan' => $validated['catatan'] ?? null,
            ]
        );

        $msg = $validated['status'] === 'hadir'
            ? 'Presensi kehadiran ronda Anda berhasil dicatat.'
            : ($validated['status'] === 'izin' ? 'Pengajuan izin ronda berhasil disimpan dan diteruskan ke Ketua RT.' : 'Absensi ronda berhasil diperbarui.');

        return back()->with('success', $msg);
    }

    public function kirimPengingatRonda(JadwalRonda $jadwal)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $petugas = $jadwal->petugas()->with('user')->get();
        if ($petugas->isEmpty()) {
            return back()->with('error', 'Belum ada petugas warga yang ditugaskan pada jadwal ronda ini.');
        }

        $namaAnggota = $petugas->pluck('user.name')->filter()->join(', ');
        $tanggalFmt = $jadwal->tanggal->isoFormat('dddd, D MMMM Y');
        $terkirim = 0;

        foreach ($petugas as $p) {
            if (! $p->user_id) continue;

            $recent = Notifikasi::where('user_id', $p->user_id)
                ->where('tipe', 'ronda')
                ->where('created_at', '>=', now()->subMinutes(30))
                ->exists();

            if (! $recent) {
                Notifikasi::create([
                    'user_id' => $p->user_id,
                    'from_user_id' => $user->id,
                    'tipe' => 'ronda',
                    'judul' => 'Pengingat Tugas Ronda Malam RT',
                    'pesan' => "PENGINGAT RONDA: Anda bertugas pada {$tanggalFmt} ({$jadwal->shift}) di {$jadwal->lokasi_pos}. Rekan regu: {$namaAnggota}. Harap hadir dan konfirmasi absensi melalui aplikasi.",
                    'dibaca' => false,
                ]);
                $terkirim++;
            }
        }

        if ($terkirim === 0) {
            return back()->with('warning', 'Pengingat untuk petugas pada jadwal ini baru saja dikirimkan dalam 30 menit terakhir.');
        }

        return back()->with('success', "Pengingat jadwal ronda berhasil dikirimkan kepada {$terkirim} petugas regu ronda.");
    }

    public function requestTukarRonda(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'jenis_pengajuan' => ['required', 'in:tukar_rekan,minta_pengganti'],
            'jadwal_ronda_asal_id' => ['required', 'exists:jadwal_rondas,id'],
            'target_user_id' => ['nullable', 'exists:users,id', 'different:' . $user->id],
            'target_jadwal_ronda_id' => ['nullable', 'exists:jadwal_rondas,id', 'different:jadwal_ronda_asal_id'],
            'alasan' => ['required', 'string', 'max:500'],
        ]);

        $rtId = $user->managedRtId() ?? optional($user->profile)->rt_id;
        $jenis = $validated['jenis_pengajuan'];
        $status = $jenis === 'tukar_rekan' ? 'menunggu_rekan' : 'menunggu_rt';

        if ($jenis === 'tukar_rekan' && empty($validated['target_user_id'])) {
            return back()->with('error', 'Silakan tentukan rekan warga yang diajak bertukar jadwal.');
        }

        $tukar = TukarJadwalRonda::create([
            'rt_id' => $rtId,
            'user_id' => $user->id,
            'jenis_pengajuan' => $jenis,
            'jadwal_ronda_asal_id' => $validated['jadwal_ronda_asal_id'],
            'target_user_id' => $validated['target_user_id'] ?? null,
            'target_jadwal_ronda_id' => $validated['target_jadwal_ronda_id'] ?? null,
            'alasan' => $validated['alasan'],
            'status' => $status,
        ]);

        if ($jenis === 'tukar_rekan' && !empty($validated['target_user_id'])) {
            Notifikasi::create([
                'user_id' => $validated['target_user_id'],
                'from_user_id' => $user->id,
                'tipe' => 'ronda',
                'judul' => 'Permintaan Tukar Jadwal Ronda',
                'pesan' => "{$user->name} mengajak Anda bertukar jadwal ronda. Alasan: {$validated['alasan']}. Harap berikan persetujuan Anda.",
                'dibaca' => false,
            ]);
        } else {
            $rtUsers = User::whereHas('role', fn ($r) => $r->whereIn('nama_role', ['rt', 'admin']))->get();
            foreach ($rtUsers as $rtUser) {
                if ($rtUser->managedRtId() === $rtId) {
                    Notifikasi::create([
                        'user_id' => $rtUser->id,
                        'from_user_id' => $user->id,
                        'tipe' => 'ronda',
                        'judul' => 'Permohonan Pengganti Ronda Warga',
                        'pesan' => "Warga {$user->name} mengajukan permohonan penggantian jadwal ronda. Alasan: {$validated['alasan']}.",
                        'dibaca' => false,
                    ]);
                }
            }
        }

        return back()->with('success', 'Permohonan pertukaran jadwal ronda berhasil dikirimkan.');
    }

    public function responTukarRonda(Request $request, TukarJadwalRonda $tukar)
    {
        $user = Auth::user();
        $isTarget = (int) $tukar->target_user_id === (int) $user->id;
        $isRt = $user->isAdminRt() && (int) $tukar->rt_id === (int) $user->managedRtId();
        $isRw = $user->isAdminRw();

        abort_unless($isTarget || $isRt || $isRw, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:disetujui,ditolak'],
            'pengganti_user_id' => ['nullable', 'exists:users,id'],
            'catatan_rt' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($tukar, $validated, $user, $isTarget, $isRt, $isRw) {
            // Tahap 1: Rekan menyetujui / menolak ajakan bertukar
            if ($isTarget && $tukar->status === 'menunggu_rekan') {
                if ($validated['status'] === 'disetujui') {
                    $tukar->update([
                        'target_user_approved_at' => now(),
                        'status' => 'menunggu_rt',
                    ]);

                    $rtUsers = User::whereHas('role', fn ($r) => $r->whereIn('nama_role', ['rt', 'admin']))->get();
                    foreach ($rtUsers as $rtUser) {
                        if ($rtUser->managedRtId() === $tukar->rt_id) {
                            Notifikasi::create([
                                'user_id' => $rtUser->id,
                                'from_user_id' => $user->id,
                                'tipe' => 'ronda',
                                'judul' => 'Persetujuan Rekan: Tukar Shift Ronda',
                                'pesan' => "Warga {$tukar->user->name} dan {$user->name} telah sepakat bertukar jadwal ronda. Menunggu persetujuan akhir Anda.",
                                'dibaca' => false,
                            ]);
                        }
                    }

                    Notifikasi::create([
                        'user_id' => $tukar->user_id,
                        'from_user_id' => $user->id,
                        'tipe' => 'ronda',
                        'judul' => 'Rekan Menyetujui Pertukaran Shift',
                        'pesan' => "Rekan Anda ({$user->name}) telah menyetujui pertukaran jadwal. Permohonan saat ini diteruskan ke Ketua RT untuk persetujuan akhir.",
                        'dibaca' => false,
                    ]);
                } else {
                    $tukar->update([
                        'status' => 'ditolak',
                        'catatan_rt' => 'Ditolak oleh rekan: ' . ($validated['catatan_rt'] ?? '-'),
                    ]);

                    Notifikasi::create([
                        'user_id' => $tukar->user_id,
                        'from_user_id' => $user->id,
                        'tipe' => 'ronda',
                        'judul' => 'Rekan Menolak Pertukaran Shift',
                        'pesan' => "Rekan Anda ({$user->name}) menolak permohonan pertukaran jadwal ronda.",
                        'dibaca' => false,
                    ]);
                }
                return;
            }

            // Tahap 2: Ketua RT / Pengurus memberikan persetujuan akhir
            if ($isRt || $isRw) {
                if ($validated['status'] === 'disetujui') {
                    $targetUserId = $tukar->target_user_id;

                    // Jika alur minta_pengganti, RT menentukan warga pengganti
                    if ($tukar->jenis_pengajuan === 'minta_pengganti' && !empty($validated['pengganti_user_id'])) {
                        $targetUserId = (int) $validated['pengganti_user_id'];
                        $tukar->target_user_id = $targetUserId;
                    }

                    if ($targetUserId) {
                        PetugasRonda::where('jadwal_ronda_id', $tukar->jadwal_ronda_asal_id)
                            ->where('user_id', $tukar->user_id)
                            ->update(['user_id' => $targetUserId]);

                        if ($tukar->target_jadwal_ronda_id) {
                            PetugasRonda::where('jadwal_ronda_id', $tukar->target_jadwal_ronda_id)
                                ->where('user_id', $targetUserId)
                                ->update(['user_id' => $tukar->user_id]);
                        }
                    }

                    $tukar->update([
                        'status' => 'disetujui',
                        'target_user_id' => $targetUserId,
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'catatan_rt' => $validated['catatan_rt'] ?? null,
                    ]);

                    foreach (array_filter([$tukar->user_id, $targetUserId]) as $uid) {
                        Notifikasi::create([
                            'user_id' => $uid,
                            'from_user_id' => $user->id,
                            'tipe' => 'ronda',
                            'judul' => 'Pertukaran Shift Disetujui RT',
                            'pesan' => "Ketua RT telah menyetujui perubahan jadwal ronda Anda. Jadwal telah otomatis diperbarui.",
                            'dibaca' => false,
                        ]);
                    }
                } else {
                    $tukar->update([
                        'status' => 'ditolak',
                        'approved_by' => $user->id,
                        'approved_at' => now(),
                        'catatan_rt' => $validated['catatan_rt'] ?? null,
                    ]);

                    Notifikasi::create([
                        'user_id' => $tukar->user_id,
                        'from_user_id' => $user->id,
                        'tipe' => 'ronda',
                        'judul' => 'Tukar Shift Ditolak RT',
                        'pesan' => "Pengajuan tukar shift ronda Anda tidak disetujui RT: " . ($validated['catatan_rt'] ?? '-'),
                        'dibaca' => false,
                    ]);
                }
            }
        });

        return back()->with('success', 'Keputusan atas pengajuan tukar jadwal berhasil diproses.');
    }

    public function storePanicReport(Request $request)
    {
        $user = Auth::user();
        $rtId = $user->managedRtId() ?? $user->profile?->rt_id;

        $report = PanicReport::create([
            'user_id' => $user->id,
            'rt_id' => $rtId,
            'judul' => 'Panggilan Darurat (Panic Button)',
            'deskripsi' => $request->input('deskripsi', 'Sinyal darurat dikirim oleh warga: ' . $user->name),
            'status' => 'aktif',
        ]);

        if ($rtId) {
            $wargaIds = User::where(function ($q) use ($rtId) {
                $q->where('rt_id', $rtId)->orWhereHas('profile', fn ($p) => $p->where('rt_id', $rtId));
            })->pluck('id');

            foreach ($wargaIds as $wId) {
                Notifikasi::create([
                    'user_id' => $wId,
                    'from_user_id' => $user->id,
                    'tipe' => 'darurat',
                    'judul' => '🚨 SINYAL DARURAT RT ' . ($user->getRtKode() ?? ''),
                    'pesan' => "Laporan darurat dari {$user->name}: {$report->deskripsi}. Mohon pengurus dan petugas ronda segera merespon!",
                    'dibaca' => false,
                ]);
            }
        }

        return back()->with('success', '🚨 Sinyal darurat berhasil dikirimkan ke pengurus RT dan warga sekitar!');
    }

    public function updatePanicReportStatus(Request $request, PanicReport $panicReport)
    {
        $user = Auth::user();
        abort_unless($user->isAdminRt() || $user->isAdminRw() || $user->hasRole('admin'), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:aktif,baru,sedang_ditangani,ditangani,selesai,false_alarm,ditolak,diarsipkan'],
        ]);

        $status = $validated['status'];
        if ($status === 'ditangani') {
            $status = 'sedang_ditangani';
        }

        $panicReport->update([
            'status' => $status,
            'handled_by' => $user->id,
            'handled_at' => now(),
        ]);

        if ($panicReport->user_id) {
            $statusLabel = match($status) {
                'sedang_ditangani', 'ditangani' => 'Sedang Ditangani oleh Pengurus RT/RW',
                'selesai' => 'Telah Selesai Ditangani dan Lingkungan Aman',
                'ditolak' => 'Ditolak / Laporan Tidak Valid',
                'diarsipkan' => 'Telah Diarsipkan',
                default => ucfirst(str_replace('_', ' ', $status)),
            };

            \App\Models\Notifikasi::create([
                'user_id' => $panicReport->user_id,
                'from_user_id' => $user->id,
                'tipe' => 'darurat',
                'judul' => 'Update Status Laporan Darurat',
                'pesan' => "Laporan darurat \"{$panicReport->judul}\" sekarang berstatus: {$statusLabel}.",
                'dibaca' => false,
            ]);
        }

        return back()->with('success', 'Status laporan darurat berhasil diperbarui.');
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
