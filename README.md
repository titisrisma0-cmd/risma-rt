# RW Smart

## Ringkasan

`RW SMART` adalah aplikasi web Laravel untuk sistem informasi RW. Aplikasi ini mengelola data warga, iuran, pembayaran, kas, pengeluaran, layanan masyarakat, dan laporan keuangan dengan antarmuka pengurus berbasis autentikasi.

## Tujuan Proyek

- Mengelola data warga dan profil kependudukan
- Menangani iuran RW dan pembayaran iuran
- Mencatat kas masuk/keluar dan pengeluaran RW
- Menghasilkan laporan keuangan periodik
- Menyediakan dashboard ringkasan untuk Admin RW

## Teknologi Utama

- PHP 8.3
- Laravel 13
- Blade + Vite
- Laravel UI (auth)
- `barryvdh/laravel-dompdf`
- `maatwebsite/excel`

## Fitur Utama

- Autentikasi pengguna dengan login dan register
- Profil pengguna lengkap dengan update data dan avatar
- CRUD data warga
- CRUD iuran dengan status aktif/nonaktif
- CRUD pembayaran dengan verifikasi dan upload bukti bayar
- Modul kas untuk pencatatan masuk/keluar
- CRUD pengeluaran RW
- Pembuatan laporan keuangan bulanan/tahunan/per-iuran

## Struktur Aplikasi

### Rute Utama

- `GET /` → Halaman welcome
- `GET /dashboard` → Dashboard admin
- `GET /profile`, `PUT /profile`, `PATCH /profile/avatar`, `PATCH /profile/kependudukan`, `PATCH /profile/password`, `DELETE /profile`
- `Route::resource('warga', WargaController::class)`
- `Route::resource('iuran', IuranController::class)`
- `PATCH /iuran/{id}/toggle-status`
- `Route::resource('pembayaran', PembayaranController::class)`
- `POST /pembayaran/{id}/verifikasi`
- `GET /get-total-bayar/{iuranId}`
- `laporan` prefix: index, generate, per-warga, show, destroy
- `GET /kas` untuk tampilan kas
- `Route::resource('pengeluaran', PengeluaranController::class)`

### Model & Relasi Utama

- `User` ↔ `UserProfile`
- `UserProfile` ↔ `Role`
- `Iuran` ↔ `Pembayaran`
- `Pembayaran` ↔ `Kas`
- `Laporan` menyimpan ringkasan periode

## Skema Database Penting

### `user_profiles`

- `user_id`, `role_id`
- `nama_lengkap`, `nik`, `no_kk`
- `alamat`, `rt`, `rw`, `kelurahan`, `kecamatan`, `kota`
- `jenis_kelamin`, `agama`, `tanggal_lahir`, `pekerjaan`
- `foto`, `foto_base64`
- `status`

### `iurans`

- `kode_iuran`
- `nama_iuran`
- `jenis_iuran` (`wajib`, `sukarela`, `khusus`)
- `nominal`
- `periode` (`bulanan`, `triwulan`, `tahunan`)
- `status`

### `pembayarans`

- `no_transaksi`
- `user_id`, `iuran_id`
- `tanggal_bayar`
- `jumlah_bayar`
- `metode_bayar` (`tunai`, `transfer`)
- `status` (`pending`, `lunas`, `gagal`)
- `bukti_bayar`
- `verified_by`, `verified_at`

### `kas`

- `tanggal`
- `jenis` (`masuk`, `keluar`)
- `nominal`
- `sumber`
- `keterangan`
- `pembayaran_id`

### `laporans`

- `kode_laporan`
- `jenis_laporan`
- `periode_awal`, `periode_akhir`
- `total_pemasukan`, `total_pengeluaran`, `saldo_akhir`
- `file_path`
- `created_by`

## Panduan Setup

1. `composer install`
2. `cp .env.example .env`
3. `php artisan key:generate`
4. `php artisan migrate`
5. `npm install`
6. `npm run build`
7. `php artisan serve`

> Jika menggunakan Laragon, sesuaikan `APP_URL` dan pastikan database sudah dibuat.

## Catatan Kode untuk AI

- Gunakan `Route::resource()` untuk CRUD standar
- Validasi input menggunakan `Request` atau `validate()`
- Konsisten dengan enum `status` pada migration dan model
- Simpan `bukti_bayar` sebagai file path atau base64 sesuai kebutuhan
- Nama blade view: `dashboard`, `profile.index`, `warga.index`, `iuran.index`, `pembayaran.index`, `laporan.index`, `pengeluaran.index`

## Prioritas Pengembangan

1. Autentikasi dan dashboard
2. CRUD warga
3. CRUD iuran dan toggle status
4. Pembayaran + verifikasi + total bayar AJAX
5. Kas dan pengeluaran
6. Laporan dan export PDF/Excel

## Development

Bagian ini menjelaskan cara menyiapkan dan menjalankan aplikasi saat mengembangkan fitur.

- Jalankan dependency PHP dan Javascript:
    - `composer install`
    - `npm install`
- Buat file environment:
    - `cp .env.example .env`
- Hasilkan kunci aplikasi:
    - `php artisan key:generate`
- Jalankan migrasi database:
    - `php artisan migrate`
- Jalankan server pengembangan:
    - `php artisan serve`
- Jalankan Vite untuk compile aset saat development:
    - `npm run dev`

## Testing

Bagian ini menjelaskan cara menjalankan pengujian agar perubahan kode tidak merusak fitur lain.

- Jalankan semua pengujian Laravel:
    - `php artisan test`
- Atau langsung gunakan PHPUnit:
    - `./vendor/bin/phpunit`
- Jika ingin membersihkan konfigurasi sebelum test:
    - `php artisan config:clear`
- Jika ada test khusus, buat di `tests/Feature` dan `tests/Unit`.

## Contoh `.env`

Isi file `.env` berikut adalah contoh konfigurasi minimum untuk menjalankan aplikasi ini secara lokal.

```env
APP_NAME="RW Smart"
APP_ENV=local
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rw_smart
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="RW Smart"
```

> Untuk Laragon, sesuaikan `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` dengan konfigurasi local Anda.

## Tips

- Fokuskan alur `auth` terlebih dahulu
- Pastikan semua form punya pesan error jelas
- Buat logika laporan terpisah agar mudah diuji

---

Jika ingin, saya bisa bantu tambah `Development` dan `Testing` lebih mendetail lagi, termasuk konfigurasi database SQLite atau export PDF/Excel.
