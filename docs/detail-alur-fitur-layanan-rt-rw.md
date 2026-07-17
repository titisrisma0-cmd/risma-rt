# Detail Alur Fitur Sistem Informasi & Layanan Masyarakat RT/RW

## Prinsip Navigasi

- Warga selalu masuk ke tampilan layanan pribadi: saldo, riwayat, status pengajuan, jadwal, dan tombol aksi sederhana.
- Admin/Pengurus masuk ke tampilan operasional: input data, validasi, moderasi, monitoring, laporan, dan pengaturan.
- Ketua RT dibatasi data RT-nya. Ketua RW melihat agregat lintas RT. Admin/Bendahara/Sekretaris dapat mengelola semua data sesuai tugas.

## Struktur Menu Utama

- Dashboard
- Bank Sampah
- UMKM Warga
- Surat Menyurat
- Posyandu
- Keamanan / Ronda
- Kegiatan & Warga
- Aspirasi & Pengaduan
- Kas & Keuangan
- Laporan
- Pengaturan

## 1. Bank Sampah

### Warga Klik

Tampilan:

- Kartu ringkasan: Total Saldo, Total Berat Sampah, Setoran Bulan Ini.
- Katalog harga sampah per kg: Plastik, Kertas, Logam, dan jenis lain.
- Tabel riwayat setoran: tanggal, jenis sampah, berat, nominal saldo, status.
- Tombol `Tarik Saldo`.

Alur:

1. Warga membuka Bank Sampah.
2. Sistem mengambil `saldo_sampahs`, `jenis_sampahs`, dan `setoran_sampahs` milik user.
3. Jika klik Tarik Saldo, warga mengisi nominal dan metode pencairan.
4. Data masuk ke `penarikan_saldo_sampahs` dengan status `pending`.
5. Warga memantau status pencairan.

### Admin/Pengurus Klik

Tampilan:

- Form input setoran: pilih warga, pilih jenis sampah, input berat.
- Preview otomatis: berat x harga per kg = saldo.
- Tabel update harga sampah.
- Tabel validasi pencairan saldo warga.

Alur:

1. Admin memilih warga dan jenis sampah.
2. Sistem mengambil harga dari `jenis_sampahs`.
3. Admin input berat, sistem menghitung `total_saldo`.
4. Simpan ke `setoran_sampahs`, lalu update `saldo_sampahs`.
5. Pada pencairan, admin approve/tolak dan saldo warga dikurangi jika disetujui/dibayar.

Database:

- `jenis_sampahs`
- `saldo_sampahs`
- `setoran_sampahs`
- `penarikan_saldo_sampahs`

Model:

- `JenisSampah`
- `SaldoSampah`
- `SetoranSampah`
- `PenarikanSaldoSampah`

## 2. UMKM Warga

### Warga Klik

Tampilan:

- Marketplace lokal berupa grid produk/jasa.
- Filter kategori: Kuliner, Jasa, Jahit, Sembako, Kerajinan, dan lain-lain.
- Kolom pencarian.
- Tombol WhatsApp pada setiap produk.
- Tab `Toko Saya`: profil toko, daftar produk, tambah/edit/nonaktifkan produk.

Alur:

1. Warga melihat produk dengan status `aktif`.
2. Warga mencari/filter kategori.
3. Klik WhatsApp membuka link `https://wa.me/{nomor}`.
4. Warga pemilik toko masuk `Toko Saya`.
5. Produk baru tersimpan dengan status `pending` sebelum tampil publik.

### Admin/Pengurus Klik

Tampilan:

- Tabel moderasi produk/toko pending.
- Preview foto, deskripsi, harga, kontak penjual.
- Tombol Approve, Tolak, Nonaktifkan.
- Kolom catatan moderasi.

Alur:

1. Admin membuka daftar `umkms` dan `produk_umkms` status `pending`.
2. Admin mengecek kelayakan produk.
3. Jika aman, status jadi `aktif`.
4. Jika ditolak, status jadi `ditolak` dan catatan dikirim ke warga.

Database:

- `umkms`
- `produk_umkms`

Model:

- `Umkm`
- `ProdukUmkm`

## 3. Surat Menyurat

### Warga Klik

Tampilan:

- Kartu status: total pengajuan, pending, diproses, siap diambil.
- Tabel riwayat pengajuan surat.
- Tombol `Ajukan Surat Baru`.
- Form jenis surat: Domisili, Pengantar KK, Keterangan Tidak Mampu.
- Upload berkas: KTP, KK, dokumen pendukung.

Alur:

1. Warga memilih jenis surat dari `jenis_surats`.
2. Warga mengisi keperluan dan form dinamis.
3. Sistem membuat `nomor_pengajuan`.
4. Status awal `pending`.
5. Warga memantau status: Pending, Diproses, Siap Diambil, Selesai.

### Admin/Pengurus Klik

Tampilan:

- Inbox surat masuk.
- Detail data warga, keperluan, dan lampiran.
- Tombol setujui/tolak.
- Input catatan.
- Tombol `Cetak Surat`.

Alur:

1. Admin/RT membuka pengajuan pada lingkup wilayahnya.
2. Admin verifikasi lampiran.
3. Jika setuju, sistem membuat `nomor_surat` otomatis.
4. Sistem generate PDF dari template resmi RT/RW.
5. Status menjadi `siap_diambil` atau `selesai`.

Database:

- `jenis_surats`
- `pengajuan_surats`

Model:

- `JenisSurat`
- `PengajuanSurat`

## 4. Posyandu

### Warga Klik

Tampilan:

- Kalender jadwal posyandu bulanan.
- Kartu profil anak/balita.
- Grafik/tabel BB, TB, lingkar kepala.
- Daftar imunisasi sudah/belum.
- Tips parenting harian.

Alur:

1. Warga membuka Posyandu.
2. Sistem menampilkan jadwal `jadwal_posyandus`.
3. Sistem menampilkan balita yang terkait KK/orang tua.
4. Grafik berasal dari `pemeriksaan_balitas`.
5. Notifikasi muncul jika ada jadwal baru.

### Admin/Pengurus Klik

Tampilan:

- Manajemen data balita.
- Form input penimbangan bulanan.
- Form jadwal posyandu baru.
- Rekap peserta dan status imunisasi.

Alur:

1. Petugas membuat jadwal posyandu.
2. Sistem mengirim notifikasi ke warga terkait.
3. Saat kegiatan, petugas memilih anak, input BB/TB/imunisasi.
4. Grafik tumbuh kembang otomatis ter-update.

Database:

- `balitas`
- `jadwal_posyandus`
- `pemeriksaan_balitas`

Model:

- `Balita`
- `JadwalPosyandu`
- `PemeriksaanBalita`

## 5. Keamanan / Ronda

### Warga Klik

Tampilan:

- Jadwal ronda pribadi dan warga satu RT.
- Kontak darurat: Polsek, Pemadam, Ambulans, Ketua RT/RW.
- Tombol besar `Panic Button`.
- Riwayat laporan darurat.

Alur:

1. Warga melihat jadwal dari `jadwal_rondas` dan `petugas_rondas`.
2. Warga dapat klik nomor kontak darurat.
3. Jika Panic Button ditekan, sistem membuat `panic_reports`.
4. Notifikasi dikirim ke warga sekitar dan pengurus.
5. Admin menandai status ditangani/selesai.

### Admin/Pengurus Klik

Tampilan:

- Kalender jadwal ronda.
- Generator jadwal acak/manual.
- Daftar petugas per malam.
- Form absensi ronda.
- Rekap hadir, izin, alpa.

Alur:

1. Admin membuat jadwal mingguan/bulanan.
2. Admin memilih petugas manual atau sistem mengacak warga aktif.
3. Saat ronda, admin/petugas input absensi.
4. Rekap kedisiplinan warga bisa dilihat per periode.

Database:

- `kontak_darurats`
- `jadwal_rondas`
- `petugas_rondas`
- `absensi_rondas`
- `panic_reports`

Model:

- `KontakDarurat`
- `JadwalRonda`
- `PetugasRonda`
- `AbsensiRonda`
- `PanicReport`

## 6. Kegiatan & Warga

### Warga Klik

Tampilan:

- Daftar kegiatan terdekat.
- Detail kegiatan: waktu, lokasi, deskripsi.
- Tombol RSVP: Hadir, Tidak Hadir, Izin.
- Direktori warga: nama dan nomor rumah saja.

Alur:

1. Warga melihat kegiatan status `terbit`.
2. Warga klik RSVP.
3. Sistem menyimpan atau memperbarui `rsvp_kegiatans`.
4. Direktori warga hanya mengambil data aman dari `user_profiles`.

### Admin/Pengurus Klik

Tampilan:

- Master warga: tambah/edit/hapus, KK, status rumah, RT/RW.
- Form agenda kegiatan.
- Monitoring RSVP: persentase hadir, tidak hadir, izin.
- Ekspor daftar hadir.

Alur:

1. Admin mengelola wilayah, KK, dan profil warga.
2. Admin membuat kegiatan.
3. Warga menerima informasi dan RSVP.
4. Admin memantau kehadiran dan tindak lanjut.

Database:

- `rws`
- `rts`
- `kartu_keluargas`
- `user_profiles`
- `kegiatans`
- `rsvp_kegiatans`

Model:

- `Rw`
- `Rt`
- `KartuKeluarga`
- `UserProfile`
- `Kegiatan`
- `RsvpKegiatan`

## 7. Aspirasi & Pengaduan

### Warga Klik

Tampilan:

- Form laporan: judul, kategori, deskripsi, foto kejadian.
- Toggle anonim/tampilkan nama.
- Timeline status laporan.
- Daftar laporan pribadi.

Alur:

1. Warga membuat laporan.
2. Sistem menyimpan ke `aspirasis`.
3. Foto tersimpan ke `aspirasi_lampirans`.
4. Warga memantau status Pending, Diproses, Selesai.
5. Balasan admin tampil di timeline.

### Admin/Pengurus Klik

Tampilan:

- Kanban/tabel aduan: Pending, Diproses, Selesai, Ditolak.
- Detail aduan dan lampiran.
- Form balasan/solusi.
- Tombol ubah status.

Alur:

1. Admin membuka ruang kendali aduan.
2. Admin assign petugas atau menangani sendiri.
3. Admin mengubah status dan memberi komentar.
4. Saat selesai, isi solusi dan timestamp `resolved_at`.

Database:

- `aspirasis`
- `aspirasi_lampirans`
- `aspirasi_komentars`

Model:

- `Aspirasi`
- `AspirasiLampiran`
- `AspirasiKomentar`

## 8. Kas & Keuangan

### Warga Klik

Tampilan:

- Dashboard transparansi kas.
- Total kas saat ini.
- Grafik pemasukan vs pengeluaran bulan ini.
- Form upload bukti transfer iuran.
- Riwayat pembayaran pribadi.

Alur:

1. Warga melihat saldo dari `kas` dan `pengeluarans`.
2. Warga memilih iuran.
3. Upload bukti transfer ke `pembayarans`.
4. Status awal `pending`.
5. Jika bendahara approve, status menjadi `lunas` dan masuk ke `kas`.

### Admin/Pengurus Klik

Tampilan:

- Validasi pembayaran pending.
- Form input pengeluaran: nominal, keterangan, nota.
- Rekap kas masuk/keluar.
- Export laporan PDF/Excel.

Alur:

1. Bendahara melihat pembayaran pending.
2. Klik approve untuk mengubah pembayaran menjadi lunas.
3. Sistem membuat record `kas` jenis masuk.
4. Pengeluaran dicatat ke `pengeluarans`.
5. Laporan bulanan diekspor dari gabungan `kas`, `pembayarans`, dan `pengeluarans`.

Database:

- `iurans`
- `pembayarans`
- `kas`
- `pengeluarans`
- `laporans`

Model:

- `Iuran`
- `Pembayaran`
- `Kas`
- `Pengeluaran`
- `Laporan`

## Matriks Hak Akses Ringkas

| Modul            | Warga                     | Ketua RT           | Ketua RW                            | Admin/Pengurus                       |
| ---------------- | ------------------------- | ------------------ | ----------------------------------- | ------------------------------------ |
| Bank Sampah      | Lihat saldo, tarik saldo  | Monitor RT         | Monitor RW                          | Input setoran, harga, validasi tarik |
| UMKM             | Marketplace, Toko Saya    | Monitor produk RT  | Monitor lintas RT                   | Moderasi                             |
| Surat            | Ajukan, pantau status     | Validasi RT        | Monitor/approve level RW bila perlu | Kelola jenis, cetak PDF              |
| Posyandu         | Jadwal, rekam anak        | Monitor RT         | Monitor RW                          | Input jadwal/pemeriksaan             |
| Ronda            | Jadwal, kontak, panic     | Kelola RT          | Overview RW                         | Kelola jadwal dan absensi            |
| Kegiatan & Warga | RSVP, direktori terbatas  | Kelola kegiatan RT | Kelola kegiatan RW                  | Master data penuh                    |
| Aspirasi         | Buat dan pantau laporan   | Tindak lanjut RT   | Overview RW                         | Ruang kendali penuh                  |
| Kas              | Transparansi, bayar iuran | Laporan RT         | Laporan RW                          | Validasi, pengeluaran, export        |
