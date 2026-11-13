# FITUR: PELAYANAN SURAT-MENYURAT DIGITAL
**File Dokumentasi:** `docs/surat-menyurat.md`  
**Modul Sistem:** E-Government Lingkungan, Administrasi Persuratan & Pengantar Berjenjang  
**Hak Akses:** Warga (Pemohon), Ketua RT (Verifikator Tingkat 1), Ketua RW (Verifikator Tingkat 2)  

---

## 1. Deskripsi & Nilai Bisnis Fitur

Fitur **Pelayanan Surat-Menyurat Digital** mendigitalkan birokrasi permohonan surat pengantar lingkungan yang selama ini memakan waktu dan mengharuskan warga mendatangi kediaman pengurus secara tatap muka. Warga dapat mengajukan berbagai jenis surat pengantar dari ponsel secara 24/7, mengunggah dokumen persyaratan, dan memantau progres persetujuan berjenjang dari Ketua RT hingga Ketua RW secara *real-time*.

### Jenis Surat yang Dilayani:
1. **Surat Pengantar Domisili** (Kependudukan / Kantor Kelurahan).
2. **Surat Pengantar SKCK** (Surat Keterangan Catatan Kepolisian).
3. **Surat Keterangan Tidak Mampu (SKTM)** (Bantuan Pendidikan & Kesehatan).
4. **Surat Pengantar Pembuatan KK / KTP Baru**.
5. **Surat Keterangan Usaha (SKU)** (Persyaratan Modal Bank / Izin Usaha).
6. **Surat Keterangan Kematian / Kelahiran Lingkungan**.

---

## 2. Alur Proses Bisnis Berjenjang (Multi-Tier Workflow)

```mermaid
flowchart TD
    W1["Warga Memilih Jenis Surat di Web"] --> W2["Mengisi Keperluan & Mengunggah Lampiran (KTP/KK)"]
    W2 --> W3["Sistem Menerbitkan Nomor Pengajuan Unik"]
    W3 --> W4["Status: 'diajukan' (Masuk Inbox Ketua RT)"]
    
    W4 --> RT1{"Ketua RT Meninjau Berkas"}
    RT1 -->|Ditolak| RT2["Status: 'ditolak' & Warga Mendapat Alasan"]
    RT1 -->|Disetujui| RT3{"Apakah Perlu Pengesahan RW?"}
    
    RT3 -->|Tingkat RT Cukup| RT4["Terbitkan Nomor Surat Resmi RT"]
    RT4 --> RT5["Status: 'siap_diambil' / 'selesai'"]
    RT5 --> DownloadPDF["Warga Unduh Surat Format PDF"]
    
    RT3 -->|Butuh Pengesahan RW| RW1["Klik 'Teruskan ke RW' (Status: 'diteruskan_rw')"]
    RW1 --> RW2{"Ketua RW Memeriksa Pengajuan"}
    RW2 -->|Ditolak RW| RW3["Kembalikan ke RT / Status 'ditolak'"]
    RW2 -->|Disetujui RW| RW4["Terbitkan Nomor Surat Resmi RW"]
    RW4 --> RT5
```

---

## 3. Diagram UML

### A. Sequence Diagram: Alur Pengajuan Surat Berjenjang RT ke RW

```mermaid
sequenceDiagram
    autonumber
    actor Warga as Warga Pemohon
    participant Web as Antarmuka Portal Warga
    participant Ctrl as LayananMasyarakatController
    participant RtCtrl as RtVerifikasiController
    participant RwCtrl as MonitoringWilayahController
    participant DB as Basis Data MariaDB
    actor RT as Ketua RT
    actor RW as Ketua RW

    Warga->>Web: Pilih Jenis Surat, Isi Keperluan & Upload Syarat
    Web->>Ctrl: POST /layanan/surat
    Ctrl->>DB: INSERT pengajuan_surats (status='diajukan')
    Ctrl-->>Warga: Flash "Pengajuan berhasil, menunggu persetujuan RT"

    RT->>Web: Akses /rt/verifikasi/permohonan
    Web->>RtCtrl: GET /rt/verifikasi/permohonan
    RtCtrl->>DB: Query pengajuan_surats WHERE rt_id = auth()->rt_id
    RtCtrl-->>RT: Tampilkan antrean permohonan surat warga RT

    alt Surat Hanya Memerlukan RT
        RT->>Web: Klik 'Setujui & Terbitkan'
        Web->>Ctrl: PATCH /layanan/surat/{id} (status='selesai')
        Ctrl->>DB: UPDATE pengajuan_surats SET nomor_surat=GENERATE, status='selesai'
    else Surat Memerlukan Pengesahan RW
        RT->>Web: Klik 'Teruskan ke RW'
        Web->>RtCtrl: POST /rt/verifikasi/{id}/teruskan-rw
        RtCtrl->>DB: UPDATE pengajuan_surats SET status='diteruskan_rw'
        RtCtrl-->>RT: Surat diteruskan ke antrean pengawasan RW
        
        RW->>Web: Buka /pengawasan/persetujuan
        Web->>RwCtrl: GET /pengawasan/persetujuan
        RwCtrl-->>RW: Tampilkan antrean surat eskalasi seluruh RT
        RW->>Web: Klik 'Approve Pengajuan RW'
        Web->>RwCtrl: POST /pengawasan/persetujuan/{id}
        RwCtrl->>DB: UPDATE pengajuan_surats SET approved_by_rw, status='selesai'
    end

    Warga->>Web: Buka Menu 'Riwayat Surat' & Klik 'Cetak PDF'
    Web->>Ctrl: GET /layanan/surat/{id}/cetak
    Ctrl->>Ctrl: DomPDF Compile View + Stempel Digital & QR Verification
    Ctrl-->>Warga: Unduh File PDF Resmi
```

### B. State Diagram: Siklus Status Pengajuan Surat

```mermaid
stateDiagram-v2
    [*] --> Diajukan: Warga Submit Permohonan
    Diajukan --> DiprosesRT: Dilihat oleh Ketua RT
    Diajukan --> Ditolak: Ditolak oleh RT (Berkas Tidak Lengkap)
    DiprosesRT --> DiteruskanRW: Butuh Pengesahan Tingkat RW
    DiprosesRT --> SiapDiambil: Disahkan Tingkat RT
    DiteruskanRW --> SiapDiambil: Disahkan Tingkat RW
    DiteruskanRW --> Ditolak: Ditolak oleh Pengurus RW
    SiapDiambil --> Selesai: PDF Dicetak / Dokumen Fisik Diambil
    Selesai --> [*]
```

---

## 4. Skema Database & Model Terkait

### Tabel Utama:
1. **`jenis_surats`**: Master jenis dokumen dan template persuratan (`id`, `kode`, `nama`, `template`, `persyaratan_json`, `status`).
2. **`pengajuan_surats`**: Log dan berkas permohonan surat (`id`, `user_id`, `rt_id`, `jenis_surat_id`, `nomor_pengajuan`, `nomor_surat`, `keperluan`, `data_form_json`, `lampiran`, `status`, `catatan_rt`, `catatan_rw`, `approved_by_rt`, `approved_by_rw`, `approved_at_rt`, `approved_at_rw`, `file_pdf`).

### Relasi Model:
- `PengajuanSurat belongsTo(User::class)`
- `PengajuanSurat belongsTo(JenisSurat::class)`
- `PengajuanSurat belongsTo(Rt::class)`

---

## 5. Rute & Controller Endpoints

| Method | Endpoint URI | Controller & Action | Deskripsi Hak Akses |
|:---|:---|:---|:---|
| `GET` | `/layanan/surat` | `LayananMasyarakatController@surat` | Antarmuka riwayat & formulir pengajuan warga |
| `POST` | `/layanan/surat` | `LayananMasyarakatController@storeSurat` | Penyimpanan draf pengajuan baru oleh warga |
| `PATCH` | `/layanan/surat/{id}` | `LayananMasyarakatController@updateSurat` | Approval/reject langsung oleh RT |
| `GET` | `/layanan/surat/{id}/cetak`| `LayananMasyarakatController@cetakSurat` | Kompilasi PDF resmi melalui DomPDF |
| `GET` | `/rt/verifikasi/permohonan`| `RtVerifikasiController@permohonan` | Meja kerja verifikasi surat Ketua RT |
| `POST` | `/rt/verifikasi/{id}/teruskan-rw`| `RtVerifikasiController@teruskanKeRw` | Eskalasi persetujuan surat ke tingkat RW |
| `GET` | `/pengawasan/persetujuan` | `MonitoringWilayahController@pengajuanRw` | Antrean surat lintas RT di meja Ketua RW |
| `POST` | `/pengawasan/persetujuan/{id}` | `MonitoringWilayahController@approvePengajuanRw` | Pengesahan akhir surat oleh Ketua RW |

---

## 6. Pertimbangan DevOps, Cetak PDF & Keabsahan Digital

1. **Otomasi Pembuatan Nomor Surat (Automatic Running Number)**:
   - Nomor surat disusun dengan format standar kearsipan pemerintah lingkungan:
     `{NOMOR_URUT}/RT{KODE_RT}/RW{KODE_RW}/{BULAN_ROMAWI}/{TAHUN}` (contoh: `042/RT03/RW05/XI/2026`).
2. **Kompilasi PDF Andal (Headless DomPDF)**:
   - Template surat menggunakan font standar Helvetica/Times untuk meminimalkan beban rendering memori server (`memory_limit = 256M`).
   - Dokumen PDF disisipkan kode QR verifikasi yang mengarah ke URL publik untuk membuktikan keaslian dokumen tanpa risiko pemalsuan fisik stempel.
