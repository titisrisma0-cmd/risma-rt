# FITUR: POSYANDU & KESEHATAN TUMBUH KEMBANG BALITA
**File Dokumentasi:** `docs/posyandu-balita.md`  
**Modul Sistem:** Layanan Kesehatan Lingkungan, Kartu Menuju Sehat (KMS) & Imunisasi Balita  
**Hak Akses:** Kader Posyandu / Pengurus RT, Warga (Orang Tua / Ibu Balita)  

---

## 1. Deskripsi & Nilai Bisnis Fitur

Fitur **Posyandu & Kesehatan Tumbuh Kembang Balita** mendigitalkan layanan pos pelayanan terpadu di tingkat rukun tetangga. Fitur ini memfasilitasi penjadwalan imunisasi berkala, pendataan identitas balita, pencatatan hasil penimbangan bulanan (Berat Badan, Tinggi Badan, Lingkar Kepala), serta pemantauan kurva pertumbuhan anak sesuai standar antropometri Kementerian Kesehatan Republik Indonesia (KMS Digital).

### Masalah yang Diselesaikan:
- Risiko hilangnya buku fisik KMS konvensional.
- Ketidaktahuan orang tua mengenai jadwal posyandu dan vitamin A berkala.
- Deteksi dini stunting atau malnutrisi pada balita di lingkungan RT melalui grafik analitik pertumbuhan otomatis.
- Rekapitulasi pelaporan data kesehatan balita ke tingkat Puskesmas/Kelurahan secara instan.

---

## 2. Alur Proses Bisnis (Business Flow)

```mermaid
flowchart TD
    subgraph KaderFlow["Alur Kader Posyandu / Pengurus RT"]
        K1["Publikasi Jadwal Posyandu Bulanan & Imunisasi"] --> K2["Daftarkan Profil Balita Baru (NIK, Tgl Lahir, Orang Tua)"]
        K2 --> K3["Hari Posyandu: Penimbangan & Pengukuran Fisik"]
        K3 --> K4["Input Pemeriksaan: Berat (Kg), Tinggi (Cm), Lingkar Kepala (Cm)"]
        K4 --> K5["Catat Status Imunisasi (BCG, Polio, DPT, MR) & Vitamin A"]
        K5 --> K6["Sistem Menghitung Z-Score & Status Gizi Anak"]
    end

    subgraph WargaFlow["Alur Orang Tua Balita (Warga)"]
        W1["Melihat Notifikasi & Kalender Jadwal Posyandu"] --> W2["Membawa Balita ke Pos RT Sesuai Jadwal"]
        W2 --> W3["Membuka Dasbor Anak di Aplikasi I-RT"]
        W3 --> W4["Melihat Grafik Perkembangan KMS & Riwayat Imunisasi"]
    end

    K1 --> W1
    W2 --> K3
    K6 --> W4
```

---

## 3. Diagram UML

### A. Sequence Diagram: Pencatatan Hasil Pemeriksaan Posyandu

```mermaid
sequenceDiagram
    autonumber
    actor Kader as Kader Posyandu / RT
    participant Web as Antarmuka Portal Posyandu
    participant Ctrl as LayananMasyarakatController
    participant DB as MariaDB / Database
    actor Ortu as Orang Tua (Warga)

    Kader->>Web: Input Jadwal Posyandu Baru
    Web->>Ctrl: POST /layanan/posyandu/jadwal
    Ctrl->>DB: INSERT jadwal_posyandus
    Ctrl->>DB: INSERT notifikasis (Broadcast Jadwal ke Ibu Balita)
    Ctrl-->>Kader: Jadwal Berhasil Diterbitkan

    Ortu->>Web: Warga Melihat Pengumuman Jadwal
    Kader->>Web: Hari-H: Buka Form Pemeriksaan Anak
    Kader->>Web: Input BB=9.8kg, TB=78cm, LK=45cm, Imunisasi='DPT-2'
    Web->>Ctrl: POST /layanan/posyandu/pemeriksaan
    Ctrl->>DB: INSERT pemeriksaan_balitas
    Ctrl-->>Kader: Data Pemeriksaan Tersimpan

    Ortu->>Web: Akses Dasbor Posyandu Pribadi (/layanan/posyandu)
    Web->>Ctrl: GET /layanan/posyandu
    Ctrl->>DB: Query balitas WHERE kk_id = auth()->user()->kk_id
    Ctrl->>DB: Query riwayat pemeriksaan_balitas ORDER BY tanggal ASC
    Ctrl-->>Ortu: Render Grafik Pertumbuhan KMS & Catatan Kesehatan
```

### B. State Diagram: Siklus Pemantauan Balita di Posyandu

```mermaid
stateDiagram-v2
    [*] --> Terdaftar: Input Data Kelahiran Balita
    Terdaftar --> JadwalTerbit: Terjadwal Penimbangan Bulanan
    JadwalTerbit --> Diperiksa: Dilakukan Pengukuran Antropometri
    Diperiksa --> EvaluasiGizi: Sistem Menghitung Kurva KMS
    EvaluasiGizi --> GiziBaik: Berat & Tinggi Sesuai Umur
    EvaluasiGizi --> PerluPerhatian: Indikasi Berat Kurang / Stunting
    PerluPerhatian --> RujukanPuskesmas: Intervensi Makanan Tambahan (PMT)
    GiziBaik --> JadwalTerbit: Kembali Menunggu Jadwal Bulan Depan
    Diperiksa --> LulusPosyandu: Usia Balita > 5 Tahun (60 Bulan)
    LulusPosyandu --> [*]
```

---

## 4. Skema Database & Model Terkait

### Tabel Utama:
1. **`balitas`**: Identitas anak usia dini (`id`, `rt_id`, `kk_id`, `nama`, `nik`, `tanggal_lahir`, `jenis_kelamin`, `nama_ibu_user_id`, `nama_ayah`).
2. **`jadwal_posyandus`**: Kalender kegiatan posyandu (`id`, `rt_id`, `judul`, `tanggal`, `lokasi`, `keterangan`, `status`).
3. **`pemeriksaan_balitas`**: Rekam antropometri balita (`id`, `balita_id`, `jadwal_posyandu_id`, `berat_kg`, `tinggi_cm`, `lingkar_kepala_cm`, `imunisasi`, `catatan_kader`, `tanggal_periksa`).

### Relasi Model:
- `Balita belongsTo(KartuKeluarga::class)`
- `Balita hasMany(PemeriksaanBalita::class)`
- `PemeriksaanBalita belongsTo(JadwalPosyandu::class)`

---

## 5. Rute & Controller Endpoints

| Method | Endpoint URI | Controller & Action | Deskripsi Hak Akses |
|:---|:---|:---|:---|
| `GET` | `/layanan/posyandu` | `LayananMasyarakatController@posyandu` | Tampilan KMS Warga & Panel Entri Kader Posyandu |
| `POST` | `/layanan/posyandu/jadwal` | `LayananMasyarakatController@storeJadwalPosyandu`| Terbitkan agenda timbang bulanan pos RT |
| `PUT` | `/layanan/posyandu/jadwal/{id}`| `LayananMasyarakatController@updateJadwalPosyandu`| Edit waktu atau lokasi posyandu |
| `DELETE`| `/layanan/posyandu/jadwal/{id}`| `LayananMasyarakatController@destroyJadwalPosyandu`| Batalkan agenda posyandu |
| `POST` | `/layanan/posyandu/balita` | `LayananMasyarakatController@storeBalita` | Registrasi anak balita baru dalam KK |
| `POST` | `/layanan/posyandu/pemeriksaan`| `LayananMasyarakatController@storePemeriksaanBalita`| Simpan data BB, TB, LK, dan Imunisasi |

---

## 6. Pertimbangan DevOps, Grafik Visual & Kerahasiaan Data

1. **Rendering Grafik Client-Side Ringan (Chart.js)**:
   - Visualisasi kurva KMS memanfaatkan library JavaScript Chart.js yang dieksekusi di sisi peramban klien (*client-side rendering*), sehingga tidak membebani komputasi server.
2. **Isolasi Rekam Medis Anak**:
   - Data pemeriksaan antropometri anak hanya dapat diakses oleh kader wilayah RT balita tersebut dan akun orang tua yang nomor KK-nya cocok, memastikan privasi data tumbuh kembang anak terjaga.
