# AHP Calculator

**Sistem Pendukung Keputusan dengan Analytical Hierarchy Process**

Aplikasi web berbasis PHP native untuk membantu pengambil keputusan dalam menentukan prioritas menggunakan metode Analytical Hierarchy Process (AHP).

## Fitur

- ✅ Input Goal / Tujuan Keputusan
- ✅ Input Kriteria Penilaian
- ✅ Input Alternatif Pilihan
- ✅ Pairwise Comparison antar Kriteria (Skala Saaty 1-9)
- ✅ Pairwise Comparison antar Alternatif per Kriteria
- ✅ Perhitungan Priority Vector (Eigenvector)
- ✅ Consistency Check (CR, CI, RI)
- ✅ Perankingan Global Alternatif
- ✅ Visualisasi Bar Chart
- ✅ Print-friendly Results
- ✅ Informasi & Panduan AHP

## Persyaratan

- PHP 8.x
- Web Browser modern (Chrome, Firefox, Edge, Safari)

## Instalasi & Menjalankan

1. Clone atau download repository ini:
   ```bash
   cd ahp-web
   ```

2. Jalankan dengan PHP built-in server:
   ```bash
   php -S localhost:8000
   ```

3. Buka browser dan akses:
   ```
   http://localhost:8000
   ```

## Struktur File

```
ahp-web/
├── index.php           # Router utama
├── config.php          # Konfigurasi (skala Saaty, RI table)
├── functions.php       # AHP calculation engine
├── views/
│   ├── header.php      # Layout head & navbar
│   ├── footer.php      # Layout footer & scripts
│   ├── home.php        # Landing page
│   ├── _progress.php   # Progress indicator
│   ├── step1.php       # Input goal
│   ├── step2.php       # Input kriteria
│   ├── step3.php       # Input alternatif
│   ├── step4.php       # Pairwise kriteria
│   ├── step5.php       # Pairwise alternatif
│   ├── results.php     # Hasil perhitungan
│   └── about.php       # Info AHP
├── prd.md              # Product Requirements Document
└── README.md           # Dokumentasi
```

## Cara Penggunaan

1. **Tentukan Goal** — Masukkan nama keputusan yang akan diambil
2. **Input Kriteria** — Tambahkan faktor-faktor penilaian (min. 2)
3. **Input Alternatif** — Tambahkan pilihan yang akan dievaluasi (min. 2)
4. **Pairwise Kriteria** — Bandingkan kepentingan antar kriteria
5. **Pairwise Alternatif** — Bandingkan alternatif untuk setiap kriteria
6. **Lihat Hasil** — Dapatkan bobot prioritas, consistency check, dan ranking

## Teknologi

- **Backend:** PHP 8.x (native, no framework)
- **Frontend:** Tailwind CSS v4 (Play CDN)
- **Storage:** PHP Session (no database required)
- **Font:** Inter via Google Fonts

## Metode AHP

AHP menggunakan skala perbandingan 1-9 (Saaty Scale):
- 1 = Sama penting
- 3 = Sedikit lebih penting
- 5 = Lebih penting
- 7 = Sangat penting
- 9 = Mutlak lebih penting
- 2,4,6,8 = Nilai tengah

Perhitungan:
1. Normalisasi matriks pairwise
2. Hitung priority vector (eigenvector)
3. Hitung lambda max
4. Hitung Consistency Index (CI)
5. Hitung Consistency Ratio (CR)
6. CR < 0.1 = konsisten ✓

## Lisensi

MIT License — bebas digunakan untuk keperluan belajar dan komersial.
