# FITUR: ASPIRASI, PENGADUAN & E-LAPOR WARGA
**File Dokumentasi:** `docs/aspirasi-pengaduan.md`  
**Modul Sistem:** Partisipasi Publik, Kanal Pengaduan Masalah Lingkungan & Eskalasi Bertingkat  
**Hak Akses:** Warga (Pelapor), Ketua RT (Penanggap Tahap 1), Ketua RW (Penanggap Eskalasi)  

---

## 1. Deskripsi & Nilai Bisnis Fitur

Fitur **Aspirasi, Pengaduan & E-Lapor Warga** menyediakan saluran formal, transparan, dan terukur bagi warga untuk menyampaikan saran, keluhan fasilitas umum (jalan berlubang, lampu penerangan jalan mati, saluran mampet, tumpukan sampah liar), maupun gesekan sosial. Fitur ini dilengkapi opsi pelaporan anonim (*whistleblower protection*), diskusi dua arah, dan mekanisme eskalasi penanganan dari tingkat RT ke tingkat RW.

### Masalah yang Diselesaikan:
- Keluhan warga yang tercecer atau tenggelam di grup WhatsApp informal.
- Rasa enggan warga melapor karena takut diintimidasi (diatasi dengan mode pelaporan anonim).
- Ketiadaan *Service Level Agreement* (SLA) dan histori pemantauan progres tindak lanjut masalah lingkungan.
- Hambatan birokrasi ketika masalah RT membutuhkan intervensi dana atau kewenangan RW.

---

## 2. Alur Proses Bisnis & Eskalasi (Escalation Flow)

```mermaid
flowchart TD
    W1["Warga Menemukan Masalah / Punya Aspirasi"] --> W2["Buka Form E-Lapor: Judul, Kategori, Uraian, Foto"]
    W2 --> W3{"Pilih Mode Identitas"}
    W3 -->|Anonim| W4["Nama Disamarkan Menjadi 'Warga Anonim'"]
    W3 -->|Tampilkan Nama| W5["Gunakan Identitas Asli Profil"]
    
    W4 --> Submit["Kirim Laporan (Status: 'pending')"]
    W5 --> Submit
    
    Submit --> RTInbox["Muncul di Meja Kerja Ketua RT Wilayah Terkait"]
    RTInbox --> RTAction{"Ketua RT Meninjau Laporan"}
    
    RTAction -->|Ditolak / SPAM| Reject["Set Status 'ditolak' & Beri Penjelasan"]
    RTAction -->|Dapat Ditangani RT| ProcessRT["Set Status 'diproses' & Tindak Lanjuti"]
    RTAction -->|Skala Besar / Lintas RT| EskalasiRW["Klik 'Eskalasi ke RW' (Status: 'eskalasi_rw')"]
    
    ProcessRT --> ResolveRT["Unggah Foto Bukti Solusi -> Set Status 'selesai'"]
    
    EskalasiRW --> RWInbox["Masuk Meja Pengawasan Ketua RW"]
    RWInbox --> ProcessRW["Pengurus RW Menangani Masalah Bersama"]
    ProcessRW --> ResolveRW["Set Status 'selesai' & Catat Solusi Akhir"]
```

---

## 3. Diagram UML

### A. Sequence Diagram: Pelaporan Masalah, Tanggapan & Eskalasi ke RW

```mermaid
sequenceDiagram
    autonumber
    actor Warga as Warga Pelapor
    participant Web as Antarmuka Portal Warga
    participant Ctrl as AspirasiController
    participant DB as MariaDB / Database
    actor RT as Ketua RT
    actor RW as Ketua RW

    Warga->>Web: Input Judul, Kategori, Foto Bukti & Centang 'Anonim'
    Web->>Ctrl: POST /aspirasi
    Ctrl->>DB: INSERT aspirasis (status='pending', is_anonim=true)
    Ctrl->>DB: INSERT aspirasi_lampirans (File Foto)
    Ctrl-->>Warga: Flash "Laporan Anda berhasil dikirim ke pengurus RT"

    RT->>Web: Buka Meja Laporan Warga (/aspirasi)
    Web->>Ctrl: GET /aspirasi
    Ctrl->>DB: Query aspirasis WHERE rt_id = auth()->rt_id
    Ctrl-->>RT: Tampilkan antrean aduan warga

    RT->>Web: Berikan Komentar Tanggapan
    Web->>Ctrl: POST /aspirasi/{id}/tanggapan (komentar)
    Ctrl->>DB: INSERT aspirasi_tanggapans
    Ctrl-->>Warga: Notifikasi tanggapan baru dari Ketua RT

    alt Masalah Memerlukan Intervensi RW
        RT->>Web: Klik "Eskalasi ke RW" (Kategori Infrastruktur Jalan RW)
        Web->>Ctrl: PATCH /aspirasi/{id}/eskalasi
        Ctrl->>DB: UPDATE aspirasis SET status='eskalasi_rw'
        Ctrl-->>RT: Laporan resmi diteruskan ke meja Ketua RW
        
        RW->>Web: Buka /aspirasi (Filter Eskalasi RW)
        RW->>Web: Tandai Selesai setelah Perbaikan Lapangan
        Web->>Ctrl: PATCH /aspirasi/{id}/status (status='selesai', bukti_solusi)
        Ctrl->>DB: UPDATE aspirasis SET status='selesai', resolved_at=NOW
        Ctrl-->>Warga: Notifikasi Masalah Telah Tuntas Diselesaikan
    end
```

### B. State Diagram: Siklus Penanganan Aspirasi Lingkungan

```mermaid
stateDiagram-v2
    [*] --> Pending: Warga Mengirim Laporan
    Pending --> Diproses: RT Memvalidasi & Menugaskan Penanganan
    Pending --> Ditolak: Laporan Tidak Masuk Akal / Fiktif
    Diproses --> EskalasiRW: Butuh Kebijakan Tingkat Rukun Warga
    EskalasiRW --> DiprosesRW: RW Menindaklanjuti
    Diproses --> Selesai: Masalah Selesai Ditangani di RT
    DiprosesRW --> Selesai: Masalah Selesai Ditangani di RW
    Selesai --> [*]
```

---

## 4. Skema Database & Model Terkait

### Tabel Utama:
1. **`aspirasis`**: Data pokok pengaduan (`id`, `user_id`, `rt_id`, `rw_id`, `kategori`, `judul`, `deskripsi`, `anonim`, `status`, `assigned_to`, `resolved_at`, `created_at`).
2. **`aspirasi_lampirans`**: File foto barang bukti kejadian (`id`, `aspirasi_id`, `file_path`, `tipe`).
3. **`aspirasi_tanggapans`**: Jejak percakapan dan klarifikasi (`id`, `aspirasi_id`, `user_id`, `isi_tanggapan`, `is_internal`).

### Model Terkait:
- `App\Models\Aspirasi`: Relasi `belongsTo(User::class)`, `belongsTo(Rt::class)`, `hasMany(AspirasiLampiran::class)`, `hasMany(AspirasiTanggapan::class)`.

---

## 5. Rute & Controller Endpoints

| Method | Endpoint URI | Controller & Action | Deskripsi Hak Akses |
|:---|:---|:---|:---|
| `GET` | `/aspirasi` | `AspirasiController@index` | Dasbor aspirasi (warga & antrean pengurus) |
| `POST` | `/aspirasi` | `AspirasiController@store` | Pengiriman aspirasi/aduan baru oleh warga |
| `GET` | `/aspirasi/{id}` | `AspirasiController@show` | Detail masalah, timeline status & lampiran foto |
| `POST` | `/aspirasi/{id}/tanggapan`| `AspirasiController@tanggapan` | Tambah pesan komentar/tanggapan solusi |
| `PATCH` | `/aspirasi/{id}/status` | `AspirasiController@status` | Update status (Pending, Diproses, Selesai, Tolak)|
| `PATCH` | `/aspirasi/{id}/eskalasi`| `AspirasiController@eskalasi` | Pengurus RT meneruskan laporan ke tingkat RW |
| `GET` | `/aspirasi/rekap` | `AspirasiController@rekap` | Rekapitulasi analitik pengaduan warga bulanan |

---

## 6. Pertimbangan DevOps, Anonimitas & Sanitasi Berkas

1. **Jaminan Kerahasiaan Whistleblower**:
   - Jika kolom `anonim = true`, controller menyamarkan identitas pelapor sebelum data dirender ke antarmuka publik (`$aspirasi->user->name = 'Warga Lingkungan (Anonim)'`), sehingga nama pelapor terlindungi secara mutlak.
2. **Validasi File Bukti Kejadian**:
   - Berkas lampiran foto difilter hanya menerima MIME `image/jpeg`, `image/png`, atau `image/webp` dengan ukuran maksimum 3 MB untuk mencegah injeksi skrip berbahaya (*malicious script upload*).
