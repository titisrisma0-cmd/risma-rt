# Blueprint Layanan Masyarakat RT/RW

## Rekomendasi Tech Stack

- Backend: Laravel 11/12, karena repo ini sudah Laravel dan cocok untuk pemula.
- Frontend web: Blade + Bootstrap 5 + Font Awesome, lanjutkan pola UI yang sudah ada.
- Mobile: Laravel API + Flutter atau React Native ketika fitur web sudah stabil.
- Database: MySQL/MariaDB untuk Laragon lokal dan hosting murah.
- Auth/RBAC: tabel `roles`, middleware role, policy per modul.
- File/PDF: Laravel Storage untuk bukti transfer/foto produk, DomPDF untuk surat.
- Notifikasi: tabel `notifikasis` dulu, lanjut WhatsApp gateway/email saat produksi.

## Role dan Hak Akses

- Warga: lihat informasi, bayar kas, setor sampah, ajukan surat, RSVP kegiatan, kirim aspirasi.
- Ketua RT: validasi surat/aspirasi/ronda pada RT sendiri, lihat laporan RT.
- Ketua RW: overview semua RT, pengumuman global, rekap lintas RT.
- Admin: akses penuh master data, validasi kas, jadwal ronda, posyandu, laporan.

## Struktur Menu

- Dashboard
- Layanan Masyarakat
- Bank Sampah
- UMKM Warga
- Surat Menyurat
- Posyandu
- Keamanan / Ronda
- Kegiatan
- Warga
- Aspirasi
- Kas & Keuangan
- Laporan
- Pengaturan

## Skema Database Utama

### Wilayah dan Warga

- `rws`: `id`, `kode_rw`, `nama_rw`, `ketua_user_id`
- `rts`: `id`, `rw_id`, `kode_rt`, `nama_rt`, `ketua_user_id`
- `kartu_keluargas`: `id`, `rt_id`, `no_kk`, `kepala_keluarga_user_id`, `alamat`, `status_rumah`
- `user_profiles`: tambahkan `rt_id`, `rw_id`, `kk_id`, `nik`, `tanggal_lahir`, `jenis_kelamin`, `status_warga`
- Relasi: RW hasMany RT, RT hasMany KK, KK hasMany User/Profile.

### Bank Sampah

- `jenis_sampahs`: `id`, `nama`, `satuan`, `harga_per_kg`, `status`
- `setoran_sampahs`: `id`, `user_id`, `jenis_sampah_id`, `berat_kg`, `harga_per_kg`, `total_saldo`, `status`, `validated_by`, `validated_at`
- `saldo_sampahs`: `id`, `user_id`, `saldo`
- `penarikan_saldo_sampahs`: `id`, `user_id`, `nominal`, `metode`, `rekening_tujuan`, `status`, `processed_by`
- Relasi: warga menyetor banyak sampah, saldo bertambah saat setoran valid.

### UMKM

- `umkms`: `id`, `user_id`, `nama_usaha`, `kategori`, `deskripsi`, `alamat`, `kontak`, `status`
- `produk_umkms`: `id`, `umkm_id`, `nama_produk`, `deskripsi`, `harga`, `stok`, `foto`, `status`
- Relasi: satu warga dapat punya satu/banyak usaha, usaha punya banyak produk.

### Surat Menyurat

- `jenis_surats`: `id`, `kode`, `nama`, `template`, `persyaratan`
- `pengajuan_surats`: `id`, `user_id`, `jenis_surat_id`, `nomor_pengajuan`, `data_form_json`, `status`, `catatan_rt`, `approved_by_rt`, `approved_at`, `file_pdf`
- Status: `draft`, `diajukan`, `disetujui_rt`, `ditolak`, `siap_diambil`, `selesai`
- Relasi: warga mengajukan surat, RT memvalidasi sesuai `rt_id` warga.

### Posyandu

- `balitas`: `id`, `kk_id`, `nama`, `nik`, `tanggal_lahir`, `jenis_kelamin`, `nama_ibu_user_id`
- `jadwal_posyandus`: `id`, `rt_id`, `judul`, `tanggal`, `lokasi`, `keterangan`
- `pemeriksaan_balitas`: `id`, `balita_id`, `jadwal_posyandu_id`, `berat_kg`, `tinggi_cm`, `lingkar_kepala_cm`, `imunisasi`, `catatan`
- Relasi: KK punya balita, balita punya riwayat pemeriksaan.

### Keamanan / Ronda

- `jadwal_rondas`: `id`, `rt_id`, `tanggal`, `shift`, `lokasi_pos`, `status`
- `petugas_rondas`: `id`, `jadwal_ronda_id`, `user_id`, `peran`
- `absensi_rondas`: `id`, `jadwal_ronda_id`, `user_id`, `status`, `waktu_absen`, `foto_bukti`, `lokasi_lat`, `lokasi_lng`
- `panic_reports`: `id`, `user_id`, `rt_id`, `judul`, `deskripsi`, `lokasi_lat`, `lokasi_lng`, `status`, `handled_by`

### Kegiatan dan RSVP

- `kegiatans`: `id`, `rt_id`, `rw_id`, `judul`, `kategori`, `tanggal_mulai`, `tanggal_selesai`, `lokasi`, `deskripsi`, `created_by`
- `rsvp_kegiatans`: `id`, `kegiatan_id`, `user_id`, `status`, `catatan`
- Status RSVP: `hadir`, `tidak_hadir`, `mungkin`

### Aspirasi dan Pengaduan

- `aspirasis`: `id`, `user_id`, `rt_id`, `kategori`, `judul`, `isi`, `anonim`, `status`, `assigned_to`, `resolved_at`
- `aspirasi_lampirans`: `id`, `aspirasi_id`, `file_path`
- `aspirasi_komentars`: `id`, `aspirasi_id`, `user_id`, `komentar`, `is_internal`
- Status: `pending`, `diproses`, `selesai`, `ditolak`

### Kas dan Keuangan

- Gunakan tabel existing: `iurans`, `pembayarans`, `kas`, `pengeluarans`, `laporans`.
- Tambahan ideal: `kategori_kas`, `periode_iurans`, `validasi_pembayarans`.
- Alur: warga upload bukti -> bendahara validasi -> masuk `kas` jenis `masuk` -> laporan transparansi.

## Langkah Pembuatan Fitur

1. Rapikan RBAC: pastikan role `warga`, `ketua_rt`, `ketua_rw`, `admin`, `bendahara` punya middleware dan policy.
2. Tambah master wilayah: RW, RT, KK, profil warga terhubung ke RT/RW.
3. Implementasi modul per prioritas: Kas, Warga, Surat, Aspirasi, Ronda, Posyandu, Bank Sampah, UMKM.
4. Setiap modul minimal punya migration, model, controller resource, request validation, blade index/create/edit/show, dan policy.
5. Tambahkan notifikasi untuk event penting: pembayaran divalidasi, surat disetujui, jadwal ronda, jadwal posyandu.
6. Buat dashboard berbeda per role: Warga melihat layanan pribadi, RT melihat validasi RT, RW melihat overview, Admin melihat semua.
7. Tambahkan export PDF/Excel untuk surat, kas, ronda, dan laporan kegiatan.

## Contoh Kode Awal Dashboard Admin

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});
```

```php
class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalWarga' => User::whereHas('role', fn ($q) => $q->where('nama_role', 'warga'))->count(),
            'suratPending' => PengajuanSurat::where('status', 'diajukan')->count(),
            'kasMasuk' => Kas::where('jenis', 'masuk')->sum('nominal'),
            'kasKeluar' => Pengeluaran::sum('nominal'),
            'aspirasiPending' => Aspirasi::where('status', 'pending')->count(),
        ]);
    }
}
```

```blade
<div class="row g-4">
    <div class="col-md-3"><div class="stat-card">Warga: {{ $totalWarga }}</div></div>
    <div class="col-md-3"><div class="stat-card">Surat Pending: {{ $suratPending }}</div></div>
    <div class="col-md-3"><div class="stat-card">Kas Masuk: Rp {{ number_format($kasMasuk, 0, ',', '.') }}</div></div>
    <div class="col-md-3"><div class="stat-card">Aspirasi Pending: {{ $aspirasiPending }}</div></div>
</div>
```
