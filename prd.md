# Product Requirements Document (PRD)
## AHP Calculator — Sistem Pendukung Keputusan dengan Metode Analytical Hierarchy Process

---

## 1. Pendahuluan

### 1.1 Tujuan
Membangun aplikasi web berbasis PHP native untuk membantu pengambil keputusan dalam menentukan prioritas menggunakan metode **Analytical Hierarchy Process (AHP)**. Aplikasi ini memungkinkan user memasukkan kriteria, alternatif, dan melakukan perbandingan berpasangan untuk mendapatkan perankingan akhir yang objektif.

### 1.2 Ruang Lingkup
- Aplikasi web sederhana (single-page / multi-step form)
- PHP native (tanpa framework)
- Tailwind CSS v4 untuk styling
- Penyimpanan data menggunakan session (tanpa database)
- Perhitungan AHP mencakup: pairwise comparison, priority vector, consistency check

### 1.3 Pengguna
- **Decision Maker** — pengguna yang ingin mengambil keputusan berbasis multi-kriteria
- Cocok untuk: pemilihan vendor, pemilihan karyawan terbaik, pemilihan lokasi, prioritas proyek, dll.

---

## 2. Fitur Utama (Epics)

### EPIC-1: Setup Masalah
| ID | Fitur | Deskripsi | Prioritas |
|----|-------|-----------|-----------|
| F-1 | Input Goal | User memasukkan nama keputusan/goal | P0 |
| F-2 | Input Kriteria | User menambahkan kriteria penilaian (min 2) | P0 |
| F-3 | Input Alternatif | User menambahkan alternatif pilihan (min 2) | P0 |

### EPIC-2: Perbandingan Berpasangan (Pairwise Comparison)
| ID | Fitur | Deskripsi | Prioritas |
|----|-------|-----------|-----------|
| F-4 | Pairwise Kriteria | Perbandingan berpasangan antar kriteria menggunakan skala Saaty 1-9 | P0 |
| F-5 | Pairwise Alternatif | Perbandingan berpasangan antar alternatif untuk setiap kriteria | P0 |
| F-6 | Skala Interaktif | Dropdown dengan label verbal (e.g., "Moderately more important") | P0 |
| F-7 | Matriks Simetris | Input hanya upper triangle, lower triangle otomatis terisi resiprokal | P0 |

### EPIC-3: Perhitungan & Hasil
| ID | Fitur | Deskripsi | Prioritas |
|----|-------|-----------|-----------|
| F-8 | Prioritas Kriteria | Eigenvector normalized untuk bobot kriteria | P0 |
| F-9 | Prioritas Lokal | Bobot alternatif untuk setiap kriteria | P0 |
| F-10 | Prioritas Global | Perankingan final alternatif | P0 |
| F-11 | Consistency Check | CR (Consistency Ratio) untuk pairwise comparison | P0 |
| F-12 | Visualisasi Chart | Bar chart untuk prioritas global | P1 |
| F-13 | Export Hasil | Print-friendly results | P2 |

### EPIC-4: Informasi
| ID | Fitur | Deskripsi | Prioritas |
|----|-------|-----------|-----------|
| F-14 | About AHP | Penjelasan metode AHP dan skala Saaty | P1 |
| F-15 | Tooltips | Informasi bantuan di setiap langkah | P1 |

---

## 3. Alur Pengguna (User Flow)

```
Start → Input Goal → Input Kriteria → Input Alternatif
    → Pairwise Comparison (Kriteria)
    → Pairwise Comparison (Alternatif per Kriteria)
    → Lihat Hasil & Ranking
```

**Detail Flow:**
1. **Step 1:** User memasukkan nama keputusan (goal)
2. **Step 2:** User menambahkan daftar kriteria (nama + bobot opsional)
3. **Step 3:** User menambahkan daftar alternatif
4. **Step 4:** User melakukan pairwise comparison antar kriteria (membandingkan kepentingan)
5. **Step 5:** Untuk setiap kriteria, user melakukan pairwise comparison antar alternatif
6. **Step 6:** Sistem menampilkan hasil: bobot kriteria, skor alternatif, ranking, dan consistency ratio

---

## 4. Arsitektur Teknis

### 4.1 Stack
| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.x (native) |
| Frontend | HTML5, Tailwind CSS v4 (Play CDN) |
| Storage | PHP Session + MySQL (MariaDB 8.4 via Laragon) |
| Chart | CSS bar chart animation |
| Font | Inter / system-ui |

### 4.2 Struktur File
```
/ahp-web/
├── prd.md                   # Dokumen PRD
├── index.php                # Router utama
├── config.php               # Konfigurasi (skala Saaty, RI table, DB)
├── functions.php            # AHP calculation engine
├── database.php             # Database connection class (singleton)
├── db_helpers.php           # Database helper functions
├── setup.php                # Database initialization & seed
├── views/
│   ├── header.php           # Layout head & navbar
│   ├── footer.php           # Layout footer & scripts
│   ├── home.php             # Landing page
│   ├── _progress.php        # Progress step indicator
│   ├── step1.php            # Input goal
│   ├── step2.php            # Input kriteria (loaded from DB)
│   ├── step3.php            # Input alternatif
│   ├── step4.php            # Pairwise kriteria
│   ├── step5.php            # Pairwise alternatif
│   ├── results.php          # Hasil perhitungan + save to DB
│   ├── dashboard.php        # Daftar analisis tersimpan
│   ├── view-analysis.php    # Detail analisis lama
│   └── about.php            # Info AHP
├── README.md                # Dokumentasi
```

### 4.3 Database Schema
**Database:** `ahp_calculator` (MySQL 8.4 via Laragon)

**Tables:**
- `criteria` — Kriteria tetap (6 kriteria skripsi)
- `analyses` — Sesi analisis AHP (goal, status, timestamp)
- `alternatives` — Alternatif per analisis
- `comparisons` — Data pairwise comparison & hasil (JSON)

### 4.4 Data Flow
```
Session (active analysis):
$_SESSION['ahp'] = [
    'goal'        => 'string',
    'criteria'    => ['id' => 'name', ...],  // Loaded from DB
    'alternatives'=> ['id' => 'name', ...],
    'pairwise_criteria' => [[matrix n×n]],
    'pairwise_alternatives' => [
        'criterion_id' => [[matrix m×m]],
    ],
    'results' => [...],
    'saved_analysis_id' => int|null,  // Saved to DB
];
```

---

## 5. Algoritma AHP

### 5.1 Skala Saaty
| Nilai | Definisi | Keterangan |
|-------|----------|------------|
| 1 | Equal importance | Sama penting |
| 3 | Moderate importance | Sedikit lebih penting |
| 5 | Strong importance | Lebih penting |
| 7 | Very strong importance | Sangat penting |
| 9 | Extreme importance | Mutlak lebih penting |
| 2,4,6,8 | Intermediate | Nilai tengah |
| Resiprokal | Kebalikan | 1/3, 1/5, dst. |

### 5.2 Langkah Perhitungan
1. **Matriks Perbandingan Berpasangan** — Buat matriks n×n
2. **Normalisasi** — Bagi setiap sel dengan jumlah kolom
3. **Priority Vector** — Rata-rata baris dari matriks ternormalisasi
4. **Weighted Sum Vector** — Kalikan matriks asli × priority vector
5. **Consistency Vector** — Weighted sum ÷ priority vector
6. **λmax** — Rata-rata consistency vector
7. **CI** — (λmax - n) / (n - 1)
8. **CR** — CI / RI (Random Index)
9. **Konsisten jika** CR < 0.1

### 5.3 Random Index (RI)
| n | RI |
|---|----|
| 1 | 0.00 |
| 2 | 0.00 |
| 3 | 0.58 |
| 4 | 0.90 |
| 5 | 1.12 |
| 6 | 1.24 |
| 7 | 1.32 |
| 8 | 1.41 |
| 9 | 1.45 |
|10 | 1.49 |

---

## 6. Non-Functional Requirements

| Kategori | Requirement |
|----------|-------------|
| Performance | Halaman load < 2 detik |
| Usability | Flow jelas dengan progress indicator |
| Responsive | Mobile-friendly (Tailwind responsive) |
| Compatibility | Chrome, Firefox, Safari, Edge |
| Accessibility | Label form, kontras warna cukup |

---

## 7. Mockup Halaman (ASCII)

```
+------------------------------------------+
| 🎯 AHP Calculator    [About] [New]       |
+------------------------------------------+
|  ○ Step 1: Goal                          |
|  ● Step 2: Criteria  ← Active           |
|  ○ Step 3: Alternatives                 |
|  ○ Step 4: Pairwise                     |
|  ○ Step 5: Alternatives                 |
|  ○ Step 6: Results                      |
+------------------------------------------+
|                                          |
|  📋 Nama Kriteria                        |
|  +----------------------------------+   |
|  | Harga                    [Hapus] |   |
|  | Kualitas                 [Hapus] |   |
|  | Pelayanan                [Hapus] |   |
|  +----------------------------------+   |
|  [Tambah Kriteria]    [Lanjut →]       |
+------------------------------------------+
```

---

## 8. Definisi Selesai (Definition of Done)

- [x] Semua input form dapat digunakan
- [x] Perhitungan AHP benar (eigenvector, consistency)
- [x] Tampilan responsif dengan Tailwind CSS v4
- [x] Consistency ratio (CR) ditampilkan dengan indikator valid/invalid
- [x] Ranking alternatif ditampilkan dengan bar chart
- [x] About page menjelaskan metode AHP
- [x] Data tersimpan dalam session (tidak hilang di refresh)
- [x] Aplikasi berjalan di PHP built-in server
