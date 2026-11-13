# FITUR: KAS, IURAN & KEUANGAN DIGITAL LINGKUNGAN
**File Dokumentasi:** `docs/kas-keuangan-digital.md`  
**Modul Sistem:** Tata Kelola Kas Transparan, Tagihan Iuran Warga & Multi-Channel Payment  
**Hak Akses:** Warga (Pembayar), Bendahara / Ketua RT (Verifikator Kas RT), Ketua RW (Auditor Kas RW)  

---

## 1. Deskripsi & Nilai Bisnis Fitur

Fitur **Kas, Iuran & Keuangan Digital Lingkungan** adalah pilar integritas dan akuntabilitas keuangan pada ekosistem I-RT (RISMA-RT). Menggantikan pencatatan buku kas kertas dan kuitansi sobekan konvensional yang rawan hilang atau disalahgunakan, modul ini menghadirkan penagihan otomatis iuran rutin (keamanan, kebersihan, kas RT), penerimaan pembayaran digital (QRIS, Transfer Bank), pencatatan pengeluaran dengan bukti nota fisik, serta pembukuan kas masuk/keluar secara *real-time* dan dapat diaudit secara terbuka oleh warga.

### Masalah yang Diselesaikan:
- Kecurigaan antar-warga mengenai transparansi penggunaan dana kas lingkungan.
- Tunggakan iuran yang sulit ditagih karena ketiadaan rekapitulasi digital yang rapi.
- Kerumitan bendahara dalam menyusun laporan pertanggungjawaban bulanan dan tahunan.
- Human error saat menghitung saldo kas akhir dan pencatatan pengeluaran tanpa bukti nota.

---

## 2. Alur Proses Bisnis & Transaksi Keuangan (Financial Flow)

```mermaid
flowchart TD
    subgraph IuranSetup["1. Penyiapan Pos Iuran"]
        B1["Pengurus Menetapkan Master Iuran (Nama, Jenis, Nominal, Periode)"] --> B2["Sistem Men-generate Tagihan Iuran Bulanan Warga"]
    end

    subgraph WargaBayar["2. Pembayaran oleh Warga"]
        W1["Warga Memeriksa Daftar Tagihan di Dasbor Pribadi"] --> W2["Pilih Iuran & Metode Bayar (QRIS / Rekening Bank / Tunai)"]
        W2 --> W3["Warga Transfer & Mengunggah Foto Bukti Transfer"]
        W3 --> W4["Status Pembayaran: 'pending'"]
    end

    subgraph VerifikasiKas["3. Validasi & Pembukuan Kas"]
        V1["Bendahara / RT Memeriksa Bukti Bayar di Inbox Verifikasi"] --> V2{"Validitas Bukti Transfer"}
        V2 -->|Palsu / Tidak Masuk Rekening| V3["Tolak Pembayaran (Status: 'gagal' & Beri Alasan)"]
        V2 -->|Sah & Masuk Rekening| V4["Klik 'Verifikasi' (DB Transaction Dimulai)"]
        
        V4 --> T1["Update Status Pembayaran -> 'lunas'"]
        T1 --> T2["Insert Kas Masuk (jenis='masuk', nominal, pembayaran_id)"]
        T2 --> T3["Catat Jejak ke tabel 'audit_logs'"]
        T3 --> T4["Kirim Notifikasi Kuitansi Digital ke Ponsel Warga"]
    end

    subgraph PengeluaranKas["4. Pencatatan Pengeluaran Lingkungan"]
        O1["Pengurus Beli Perlengkapan Lingkungan / Bayar Petugas Sampah"] --> O2["Input Pengeluaran: Nominal, Keterangan & Foto Kuitansi Nota"]
        O2 --> O3["Insert Kas Keluar (jenis='keluar', nominal)"]
        O3 --> O4["Saldo Kas RT Berkurang Seketika di Dasbor Publik"]
    end

    B2 --> W1
    W4 --> V1
```

---

## 3. Diagram UML

### A. Sequence Diagram: Pembayaran Iuran & Atomik Pembukuan Kas Masuk

```mermaid
sequenceDiagram
    autonumber
    actor Warga as Warga Pembayar
    participant Web as Antarmuka Keuangan Web
    participant Ctrl as PembayaranController
    participant DB as MariaDB / Database
    actor Bendahara as Bendahara / Pengurus RT

    Warga->>Web: Pilih Iuran, Pilih Metode (QRIS/Bank), Upload Bukti
    Web->>Ctrl: POST /pembayaran (iuran_id, jumlah_bayar, bukti_bayar)
    Ctrl->>DB: INSERT pembayarans (no_transaksi, status='pending')
    Ctrl-->>Warga: Flash "Pembayaran terkirim, menunggu verifikasi pengurus"

    Bendahara->>Web: Buka Menu 'Verifikasi Pembayaran'
    Web->>Ctrl: GET /pembayaran
    Ctrl-->>Bendahara: Tampilkan antrean pembayaran status 'pending'

    Bendahara->>Web: Periksa Rekening Bank & Klik 'Verifikasi'
    Web->>Ctrl: POST /pembayaran/{id}/verifikasi

    critical Database Transaction (All-or-Nothing)
        Ctrl->>DB: UPDATE pembayarans SET status='lunas', verified_by, verified_at
        Ctrl->>DB: INSERT kas (tanggal=NOW, jenis='masuk', nominal, pembayaran_id)
        Ctrl->>DB: INSERT audit_logs (aksi='verifikasi_pembayaran', user_id, detail)
        Ctrl->>DB: INSERT notifikasis (Kuitansi Digital Pembayaran Sah)
    end

    Ctrl-->>Bendahara: Flash "Pembayaran sah, kas otomatis bertambah"
    Warga->>Web: Refresh Dasbor -> Tagihan Berubah Hijau "LUNAS" & Saldo Kas Bertambah
```

### B. State Diagram: Siklus Status Tagihan Iuran

```mermaid
stateDiagram-v2
    [*] --> BelumBayar: Tagihan Terbit
    BelumBayar --> Pending: Warga Mengunggah Bukti Bayar
    Pending --> Gagal: Verifikasi Ditolak Bendahara
    Gagal --> Pending: Warga Unggah Ulang Bukti Transfer
    Pending --> Lunas: Verifikasi Disetujui (Kas Bertambah)
    Lunas --> [*]
```

---

## 4. Skema Database & Model Terkait

### Tabel Utama:
1. **`iurans`**: Master tarif iuran warga (`id`, `rt_id`, `kode_iuran`, `nama_iuran`, `jenis_iuran`, `nominal`, `periode`, `status`, `jatuh_tempo_hari`).
2. **`pembayarans`**: Transaksi setoran iuran (`id`, `no_transaksi`, `user_id`, `iuran_id`, `metode_pembayaran_id`, `tanggal_bayar`, `jumlah_bayar`, `status`, `bukti_bayar`, `verified_by`, `verified_at`).
3. **`metode_pembayarans`**: Channel pembayaran digital (`id`, `rw_id`, `nama_bank`, `nomor_rekening`, `atas_nama`, `kode_channel`, `is_active`, `logo`).
4. **`kas`**: Buku besar buku kas masuk dan keluar (`id`, `rt_id`, `tanggal`, `jenis`, `nominal`, `sumber`, `keterangan`, `pembayaran_id`, `pengeluaran_id`).
5. **`pengeluarans`**: Detail pembelanjaan lingkungan (`id`, `rt_id`, `judul`, `nominal`, `tanggal`, `keterangan`, `bukti_nota`, `created_by`).
6. **`audit_logs`**: Rekam jejak audit finansial (`id`, `user_id`, `action`, `module`, `ip_address`, `detail_json`, `created_at`).

---

## 5. Rute & Controller Endpoints

| Method | Endpoint URI | Controller & Action | Deskripsi Hak Akses |
|:---|:---|:---|:---|
| `RESOURCE` | `/iuran` | `IuranController` | Kelola master tarif iuran RT |
| `PATCH` | `/iuran/{id}/toggle-status` | `IuranController@toggleStatus` | Aktifkan/nonaktifkan pos iuran |
| `RESOURCE` | `/pembayaran` | `PembayaranController` | Transaksi pembayaran & unggah bukti transfer |
| `POST` | `/pembayaran/{id}/verifikasi`| `PembayaranController@verifikasi` | Validasi bukti bayar & pencatatan kas otomatis |
| `GET` | `/kas` | `KasController@index` | Dasbor buku kas terbuka & grafik arus kas |
| `RESOURCE` | `/pengeluaran` | `PengeluaranController` | Entri pengeluaran kas lingkungan & nota fisik |
| `RESOURCE` | `/rw/metode-pembayaran` | `MetodePembayaranController` | Master rekening bank & QRIS pengurus |
| `GET` | `/audit-keuangan` | `AuditKeuanganController@index` | Supervisi audit keuangan lintas RT oleh RW |

---

## 6. Pertimbangan DevOps & Integritas Finansial

1. **Jaminan ACID Transaksi (Atomicity, Consistency, Isolation, Durability)**:
   - Pencatatan kas masuk dari pembayaran iuran **wajib** menggunakan `DB::transaction()`. Jika pembuatan baris kas gagal, status pembayaran tidak boleh berubah menjadi `lunas`. Hal ini menjamin mutasi bank fisik selalu cocok (*recon*) dengan saldo buku kas digital.
2. **Pencegahan Nomor Transaksi Ganda (Unique Transaction Sequence)**:
   - Nomor transaksi dibuat dengan formula acak berurut: `TRX-{TAHUN}{BULAN}{HARI}-{RANDOM_HEX}` (contoh: `TRX-20261113-7F2A`) dengan constraint *unique key* di level database.
3. **Penyimpanan Nota Bukti Pengeluaran**:
   - Nota pengeluaran wajib diunggah dalam format gambar/PDF dan diarsipkan di direktori penyimpanan terisolasi untuk memudahkan inspeksi sewaktu audit rapat tahunan warga.
