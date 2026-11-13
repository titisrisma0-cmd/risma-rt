# FITUR: UMKM WARGA & MARKETPLACE LOKAL
**File Dokumentasi:** `docs/umkm-warga.md`  
**Modul Sistem:** Pemberdayaan Ekonomi Warga, Toko Lokal & Marketplace Lingkungan  
**Hak Akses:** Warga (Penjual & Pembeli), Pengurus RT/RW (Moderator)  

---

## 1. Deskripsi & Nilai Bisnis Fitur

Fitur **UMKM Warga & Marketplace Lokal** adalah wadah promosi dan transaksi ekonomi sirkular antar-warga dalam satu lingkungan rukun tetangga dan rukun warga. Warga yang memiliki usaha rumahan (kuliner, sembako, jasa jahit, servis elektronik, kerajinan, dsb.) dapat mendaftarkan toko dan memamerkan produk dagangannya. Pembeli (tetangga) dapat langsung terhubung dengan penjual melalui tombol pemesanan cepat WhatsApp.

### Masalah yang Diselesaikan:
- Rendahnya keterpaparan produk dagangan warga kepada tetangga terdekat.
- Menghindari potongan komisi aplikasi ojek online komersial yang tinggi untuk transaksi antar-tetangga.
- Menjamin keamanan dan kenyamanan transaksi lokal dengan penjual yang sudah terverifikasi sebagai warga sah lingkungan tersebut.
- Moderasi produk oleh pengurus lingkungan guna mencegah penjualan barang terlarang atau konten tidak pantas.

---

## 2. Alur Proses Bisnis (Business Flow)

```mermaid
flowchart TD
    subgraph PenjualFlow["Alur Warga Penjual (Toko Saya)"]
        U1["Buka Menu 'Toko Saya'"] --> U2["Daftarkan Profil Toko / Usaha"]
        U2 --> U3["Unggah Produk: Foto, Nama, Deskripsi, Harga & Stok"]
        U3 --> U4["Status Produk: 'pending' (Menunggu Moderasi)"]
        U5["Menerima Pesanan Chat WhatsApp dari Warga"] --> U6["Kirim / Antar Pesanan ke Rumah Tetangga"]
    end

    subgraph PengurusFlow["Alur Pengurus RT/RW (Moderasi)"]
        M1["Menerima Notifikasi Produk Baru Masuk"] --> M2["Buka Tab 'Moderasi Produk'"]
        M2 --> M3{"Cek Kelayakan Barang & Etika Lingkungan"}
        M3 -->|Disetujui| M4["Set Status Produk: 'aktif' (Tampil di Katalog Publik)"]
        M3 -->|Ditolak| M5["Set Status: 'ditolak' & Berikan Catatan Revisi"]
    end

    subgraph PembeliFlow["Alur Warga Pembeli"]
        B1["Akses Marketplace Warga"] --> B2["Cari / Filter Kategori (Kuliner, Jasa, Sembako)"]
        B2 --> B3["Klik 'Pesan via WhatsApp'"]
        B3 --> B4["Buka Link Otomatis wa.me dengan Template Chat"]
    end

    U4 --> M1
    M4 --> B1
    B4 --> U5
```

---

## 3. Diagram UML

### A. Sequence Diagram: Pendaftaran Produk & Direct WhatsApp Order

```mermaid
sequenceDiagram
    autonumber
    actor Penjual as Warga Penjual
    participant Web as Portal Web Layanan
    participant Ctrl as LayananMasyarakatController
    participant DB as MariaDB / Database
    actor Pengurus as Pengurus RT (Moderator)
    actor Pembeli as Warga Pembeli

    Penjual->>Web: Buka 'Toko Saya' & Isi Form Produk
    Web->>Ctrl: POST /layanan/umkm/produk (Multipart Form)
    Ctrl->>Ctrl: Upload Foto ke storage/app/public/umkm
    Ctrl->>DB: INSERT produk_umkms (status='pending')
    Ctrl-->>Penjual: "Produk berhasil diajukan untuk ditinjau"

    Pengurus->>Web: Buka Panel 'Moderasi UMKM'
    Web->>Ctrl: GET /layanan/umkm
    Ctrl-->>Pengurus: Tampilkan antrean produk status 'pending'
    Pengurus->>Web: Klik 'Setujui'
    Web->>Ctrl: PATCH /layanan/umkm/produk/{id}/moderasi (status='aktif')
    Ctrl->>DB: UPDATE produk_umkms SET status='aktif'
    Ctrl-->>Pengurus: Notifikasi moderasi sukses

    Pembeli->>Web: Jelajahi Marketplace (/layanan/umkm)
    Web->>Ctrl: Query produk_umkms WHERE status='aktif'
    Ctrl-->>Pembeli: Render Katalog Grid Produk
    Pembeli->>Web: Klik Tombol "Pesan via WhatsApp"
    Web-->>Pembeli: Buka https://wa.me/{nomor_wa}?text=Halo%20saya%20tertarik%20produk%20{nama}
```

### B. State Diagram: Siklus Hidup Produk UMKM

```mermaid
stateDiagram-v2
    [*] --> Pending: Warga Mengunggah Produk Baru
    Pending --> Aktif: Disetujui Pengurus RT/RW
    Pending --> Ditolak: Ditolak (Melanggar Aturan Lingkungan)
    Ditolak --> Pending: Warga Memperbaiki & Mengunggah Ulang
    Aktif --> Nonaktif: Stok Habis / Dinonaktifkan Penjual
    Nonaktif --> Aktif: Diaktifkan Kembali oleh Penjual
    Aktif --> [*]: Dihapus oleh Pemilik Toko
```

---

## 4. Skema Database & Model Terkait

### Tabel Utama:
1. **`umkms`**: Profil entitas usaha warga (`id`, `user_id`, `nama_usaha`, `kategori`, `deskripsi`, `alamat`, `kontak`, `status`).
2. **`produk_umkms`**: Daftar item barang/jasa yang dijual (`id`, `umkm_id`, `nama_produk`, `deskripsi`, `harga`, `stok`, `foto`, `status`, `catatan_moderasi`).

### Model Terkait:
- `App\Models\Umkm`: Memiliki relasi `belongsTo(User::class)` dan `hasMany(ProdukUmkm::class)`.
- `App\Models\ProdukUmkm`: Memiliki relasi `belongsTo(Umkm::class)` dengan scope query `scopeAktif($query)`.

---

## 5. Rute & Controller Endpoints

| Method | Endpoint URI | Controller & Action | Deskripsi Hak Akses |
|:---|:---|:---|:---|
| `GET` | `/layanan/umkm` | `LayananMasyarakatController@umkm` | Katalog publik marketplace & dasbor penjual |
| `POST` | `/layanan/umkm/toko-saya` | `LayananMasyarakatController@storeUmkm` | Registrasi profil toko warga mandiri |
| `PATCH` | `/layanan/umkm/{umkm}/toggle` | `LayananMasyarakatController@toggleUmkmStatus` | Buka/tutup operasional toko |
| `POST` | `/layanan/umkm/produk` | `LayananMasyarakatController@storeProdukUmkm` | Unggah produk baru beserta foto |
| `PUT` | `/layanan/umkm/produk/{id}` | `LayananMasyarakatController@updateProdukUmkm` | Pembaruan data, harga, atau stok produk |
| `DELETE`| `/layanan/umkm/produk/{id}` | `LayananMasyarakatController@destroyProdukUmkm`| Hapus produk dari etalase |
| `PATCH` | `/layanan/umkm/produk/{id}/moderasi`| `LayananMasyarakatController@moderateProdukUmkm`| Kurasi produk (Approve/Reject) oleh RT |

---

## 6. Pertimbangan DevOps, Media & Skalabilitas

1. **Optimasi Berkas Gambar (Image Resizing & Compression)**:
   - Gambar produk diunggah dengan format WebP atau JPEG terkompresi guna menghemat bandwidth mobile user dan kapasitas disk server.
2. **Pembersihan Berkas Orphan (Storage Cleanup)**:
   - Ketika model `ProdukUmkm` dihapus (`destroyProdukUmkm`), sistem secara otomatis menghapus berkas fisik foto terkait dari `storage/app/public/umkm` untuk mencegah pemborosan storage (*orphaned file leak*).
3. **Format Kontak WhatsApp Terstandarisasi**:
   - Nomor telepon dinormalisasi ke format internasional (misal mengubah `0812xxxx` menjadi `62812xxxx`) agar tautan URL `wa.me` berfungsi sempurna pada perangkat desktop maupun smartphone.
