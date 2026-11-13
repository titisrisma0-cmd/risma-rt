# FITUR: AGENDA KEGIATAN & RSVP WARGA
**File Dokumentasi:** `docs/kegiatan-rsvp.md`  
**Modul Sistem:** Manajemen Event Kemasyarakatan, Registrasi Peserta & Presensi Kegiatan  
**Hak Akses:** Ketua RW, Ketua RT, Panitia Kegiatan, Warga Lingkungan  

---

## 1. Deskripsi & Nilai Bisnis Fitur

Fitur **Agenda Kegiatan & RSVP Warga** mengelola seluruh siklus acara kemasyarakatan di lingkungan RT dan RW (seperti Kerja Bakti, Rapat Warga, Peringatan HUT RI, Pengajian Rutin, Senam Bersama, dan Lomba Lingkungan). Fitur ini mengeliminasi ketidakpastian estimasi jumlah warga yang akan hadir melalui sistem RSVP digital terintegrasi, mempermudah panitia dalam penganggaran konsumsi dan logistik acara.

### Masalah yang Diselesaikan:
- Pemborosan anggaran konsumsi akibat ketidaktahuan jumlah warga yang hadir.
- Rendahnya partisipasi warga karena informasi undangan kertas yang sering tercecer.
- Ketiadaan arsip presensi dan dokumentasi rekap kegiatan lingkungan masa lalu.
- Pengelolaan kepanitiaan acara yang tidak terkoordinasi antar-RT.

---

## 2. Alur Proses Bisnis (Business Flow)

```mermaid
flowchart TD
    subgraph PengurusFlow["Alur Pengurus RT/RW / Panitia Acara"]
        P1["Buat Kegiatan Baru: Judul, Waktu, Lokasi, Kategori & Kuota"] --> P2["Publikasikan Kegiatan ke Kanal Feed Warga"]
        P2 --> P3["Pantau Dasbor RSVP Real-Time (Hadir / Tidak / Izin)"]
        P3 --> P4["Ekspor Daftar Hadir untuk Estimasi Konsumsi"]
        P4 --> P5["Hari Pelaksanaan: Presensi Peserta di Lokasi Acara"]
        P5 --> P6["Arsipkan / Tandai Kegiatan Telah Selesai"]
    end

    subgraph WargaFlow["Alur Warga Lingkungan"]
        W1["Melihat Pemberitahuan Kegiatan Baru di Notifikasi / Beranda"] --> W2["Buka Halaman Detail Agenda Kegiatan"]
        W2 --> W3{"Pilih Opsi Konfirmasi RSVP"}
        W3 -->|Hadir| W4["Klik 'Hadir' (Opsional: Catatan Keluarga yang Ikut)"]
        W3 -->|Tidak Hadir| W5["Klik 'Tidak Hadir' (Beri Keterangan Izin)"]
        W4 --> W6["Jadwal Otomatis Ditambahkan ke Kalender Pribadi Warga"]
        W6 --> W7["Hadir di Lokasi & Lakukan Presensi Panitia"]
    end

    P2 --> W1
    W4 --> P3
    W5 --> P3
```

---

## 3. Diagram UML

### A. Sequence Diagram: Pembuatan Agenda & Konfirmasi Kehadiran (RSVP)

```mermaid
sequenceDiagram
    autonumber
    actor Pengurus as Pengurus RT / RW
    participant Web as Antarmuka Web
    participant Ctrl as KegiatanController
    participant DB as MariaDB / Database
    actor Warga as Warga Peserta

    Pengurus->>Web: Buka /kegiatan & Isi Form Event Baru
    Web->>Ctrl: POST /kegiatan (judul, tanggal, lokasi, kuota)
    Ctrl->>DB: INSERT kegiatans (status='terbit')
    Ctrl->>DB: INSERT notifikasis (Broadcast Agenda ke Seluruh Warga)
    Ctrl-->>Pengurus: Notifikasi "Kegiatan berhasil dipublikasikan"

    Warga->>Web: Akses Menu Kegiatan (/kegiatan)
    Web->>Ctrl: GET /kegiatan
    Ctrl->>DB: Query kegiatans aktif
    Ctrl-->>Warga: Render Kalender & Daftar Acara

    Warga->>Web: Klik "RSVP Hadir"
    Web->>Ctrl: POST /kegiatan/{id}/daftar (status='hadir')
    Ctrl->>DB: UPSERT rsvp_kegiatans / kegiatan_pesertas
    Ctrl-->>Warga: "Konfirmasi kehadiran berhasil dicatat"

    Pengurus->>Web: Pantau Rekap (/kegiatan/{id}/peserta)
    Web->>Ctrl: GET /kegiatan/{id}/peserta
    Ctrl->>DB: Aggregate COUNT(hadir), COUNT(tidak_hadir), COUNT(izin)
    Ctrl-->>Pengurus: Render Grafik Batang & Daftar Absensi
```

### B. State Diagram: Siklus Hidup Agenda Acara

```mermaid
stateDiagram-v2
    [*] --> Draf: Panitia Merancang Agenda
    Draf --> Terbit: Dipublikasikan ke Publik
    Terbit --> Berlangsung: Hari-H Pelaksanaan
    Berlangsung --> Selesai: Acara Berakhir & Laporan Ditutup
    Terbit --> Dibatalkan: Dibatalkan (Cuaca Buruk / Force Majeure)
    Dibatalkan --> SoftDeleted: Dihapus Sementara dari Feed
    Selesai --> [*]
```

---

## 4. Skema Database & Model Terkait

### Tabel Utama:
1. **`kegiatans`**: Master agenda kegiatan (`id`, `rt_id`, `rw_id`, `judul`, `kategori`, `tanggal_mulai`, `tanggal_selesai`, `lokasi`, `kuota_peserta`, `deskripsi`, `status`, `created_by`, `deleted_at`).
2. **`kegiatan_pesertas` / `rsvp_kegiatans`**: Data partisipasi warga (`id`, `kegiatan_id`, `user_id`, `status_kehadiran`, `catatan`, `waktu_presensi_lokasi`).

### Model Terkait:
- `App\Models\Kegiatan`: Menggunakan trait `SoftDeletes`, relasi `belongsTo(Rt::class)`, `belongsTo(Rw::class)`, dan `hasMany(KegiatanPeserta::class)`.
- `App\Models\KegiatanPeserta`: Relasi `belongsTo(User::class)`.

---

## 5. Rute & Controller Endpoints

| Method | Endpoint URI | Controller & Action | Deskripsi Hak Akses |
|:---|:---|:---|:---|
| `GET` | `/kegiatan` | `KegiatanController@index` | Daftar feed kegiatan terbuka & filter kategori |
| `POST` | `/kegiatan` | `KegiatanController@store` | Pembuatan acara baru oleh pengurus lingkungan |
| `PATCH` | `/kegiatan/{id}` | `KegiatanController@update` | Pembaruan informasi waktu/lokasi acara |
| `DELETE`| `/kegiatan/{id}` | `KegiatanController@destroy` | Hapus permanen kegiatan |
| `DELETE`| `/kegiatan/{id}/soft-delete`| `KegiatanController@softDeleteCancelled`| Batalkan kegiatan & sembunyikan dari feed |
| `POST` | `/kegiatan/{id}/daftar`| `KegiatanController@daftar` | Submit status RSVP hadir oleh warga |
| `DELETE`| `/kegiatan/{id}/daftar`| `KegiatanController@batalDaftar` | Pembatalan kehadiran oleh warga |
| `GET` | `/kegiatan/{id}/peserta`| `KegiatanController@peserta` | Meja panitia: Rekap dan presensi peserta |
| `PATCH` | `/kegiatan/{id}/peserta/{user}`| `KegiatanController@updatePeserta` | Update presensi manual saat registrasi ulang di lokasi |

---

## 6. Pertimbangan DevOps & Optimasi Kinerja

1. **Agregasi Cache Penghitungan RSVP**:
   - Untuk kegiatan berskala besar seluruh RW dengan ribuan peserta, query agregat `COUNT(status)` disimpan dalam cache runtime selama 60 detik (`Cache::remember("kegiatan_{$id}_stat", 60, ...)`).
2. **Audit Pembatalan Acara (Soft Delete Trait)**:
   - Pembatalan acara tidak serta merta menghapus baris dari database agar jejak riwayat logistik dan notifikasi ke warga tetap utuh (`deleted_at IS NOT NULL`).
