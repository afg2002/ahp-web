<!-- About Page -->
<div class="fade-in max-w-3xl mx-auto">

    <!-- Header -->
    <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-ink relative overflow-hidden mb-5">
            <span class="text-white font-display text-2xl italic relative z-10">AHP</span>
            <div class="absolute bottom-0 right-0 w-4 h-4 bg-gold"></div>
        </div>
        <h1 class="text-3xl font-display text-ink">Analytical Hierarchy Process</h1>
        <p class="text-sm text-ink-muted mt-2">Metode Pengambilan Keputusan Multi-Kriteria</p>
    </div>

    <!-- What is AHP -->
    <div class="card border-border mb-6">
        <h2 class="text-lg font-display text-ink mb-4">Apa itu AHP?</h2>
        <div class="space-y-3 text-sm text-ink leading-relaxed">
            <p>
                <strong>Analytical Hierarchy Process (AHP)</strong> adalah metode pengambilan keputusan
                multi-kriteria yang dikembangkan oleh <strong>Dr. Thomas L. Saaty</strong> pada tahun 1970-an.
                Metode ini membantu memecahkan masalah kompleks dengan menyusunnya ke dalam struktur hierarki
                dan memberikan nilai numerik untuk setiap pertimbangan subjektif.
            </p>
            <p>
                AHP banyak digunakan dalam berbagai bidang seperti manajemen bisnis, pemilihan vendor,
                rekrutmen karyawan, penentuan prioritas proyek, dan analisis kebijakan.
            </p>
        </div>
    </div>

    <!-- Steps -->
    <div class="card border-border mb-6">
        <h2 class="text-lg font-display text-ink mb-6">Langkah-Langkah AHP</h2>

        <div class="space-y-6">
            <?php
            $steps = [
                ['num' => '01', 'title' => 'Mendefinisikan Masalah & Tujuan', 'desc' => 'Tentukan goal/keputusan yang akan diambil, kriteria penilaian, dan alternatif pilihan.'],
                ['num' => '02', 'title' => 'Membangun Struktur Hierarki', 'desc' => 'Susun masalah dalam struktur hierarki: Goal → Kriteria → Alternatif.'],
                ['num' => '03', 'title' => 'Perbandingan Berpasangan', 'desc' => 'Bandingkan setiap pasangan kriteria dan alternatif menggunakan skala Saaty 1–9.'],
                ['num' => '04', 'title' => 'Menghitung Priority Vector', 'desc' => 'Normalisasi matriks dan hitung eigenvector untuk mendapatkan bobot prioritas.'],
                ['num' => '05', 'title' => 'Uji Konsistensi', 'desc' => 'Hitung Consistency Ratio (CR). Jika CR &lt; 0.1, perbandingan dianggap konsisten.'],
                ['num' => '06', 'title' => 'Perankingan Final', 'desc' => 'Hitung prioritas global dan urutkan alternatif berdasarkan skor tertinggi.'],
            ];
            foreach ($steps as $s):
            ?>
            <div class="flex gap-4">
                <span class="w-10 h-10 bg-teal flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    <?= $s['num'] ?>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-ink"><?= $s['title'] ?></h3>
                    <p class="text-sm text-ink-muted mt-1"><?= $s['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Saaty Scale -->
    <div class="card border-border mb-6">
        <h2 class="text-lg font-display text-ink mb-4">Skala Saaty (1–9)</h2>
        <p class="text-sm text-ink-muted mb-4">Skala fundamental yang digunakan untuk menilai tingkat kepentingan relatif:</p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-paper border-b border-border">
                        <th class="text-left px-4 py-2 font-semibold text-ink text-xs uppercase tracking-wider">Intensitas</th>
                        <th class="text-left px-4 py-2 font-semibold text-ink text-xs uppercase tracking-wider">Definisi</th>
                        <th class="text-left px-4 py-2 font-semibold text-ink text-xs uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr><td class="px-4 py-2 font-mono font-semibold">1</td><td class="px-4 py-2">Equal Importance</td><td class="px-4 py-2 text-ink-muted">Kedua elemen sama pentingnya</td></tr>
                    <tr><td class="px-4 py-2 font-mono font-semibold">3</td><td class="px-4 py-2">Moderate Importance</td><td class="px-4 py-2 text-ink-muted">Satu elemen sedikit lebih penting</td></tr>
                    <tr><td class="px-4 py-2 font-mono font-semibold">5</td><td class="px-4 py-2">Strong Importance</td><td class="px-4 py-2 text-ink-muted">Satu elemen lebih penting</td></tr>
                    <tr><td class="px-4 py-2 font-mono font-semibold">7</td><td class="px-4 py-2">Very Strong Importance</td><td class="px-4 py-2 text-ink-muted">Satu elemen sangat penting</td></tr>
                    <tr><td class="px-4 py-2 font-mono font-semibold">9</td><td class="px-4 py-2">Extreme Importance</td><td class="px-4 py-2 text-ink-muted">Satu elemen mutlak lebih penting</td></tr>
                    <tr><td class="px-4 py-2 font-mono font-semibold">2,4,6,8</td><td class="px-4 py-2">Intermediate Values</td><td class="px-4 py-2 text-ink-muted">Nilai tengah antara dua skala</td></tr>
                    <tr><td class="px-4 py-2 font-mono font-semibold">Resiprokal</td><td class="px-4 py-2">Kebalikan</td><td class="px-4 py-2 text-ink-muted">Jika A:B = 3, maka B:A = ⅓</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RI Table -->
    <div class="card border-border mb-6">
        <h2 class="text-lg font-display text-ink mb-4">Random Index (RI)</h2>
        <p class="text-sm text-ink-muted mb-4">Nilai RI digunakan untuk menghitung Consistency Ratio (CR):</p>

        <div class="grid grid-cols-5 sm:grid-cols-10 gap-px bg-border">
            <?php foreach ($riTable as $n => $ri): ?>
            <div class="bg-paper p-2.5 text-center">
                <div class="text-base font-display text-teal"><?= $n ?></div>
                <div class="text-xs text-ink-muted font-mono"><?= number_format($ri, 2) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Formulas -->
    <div class="card border-border mb-6">
        <h2 class="text-lg font-display text-ink mb-4">Rumus Perhitungan</h2>

        <div class="space-y-4">
            <div class="bg-paper border border-border p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-ink mb-2">1. Normalized Matrix</h3>
                <p class="text-xs text-ink-muted mb-1">Setiap cell dibagi dengan jumlah kolomnya</p>
                <code class="font-mono text-sm text-teal">a'<sub>ij</sub> = a<sub>ij</sub> / Σ a<sub>kj</sub></code>
            </div>
            <div class="bg-paper border border-border p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-ink mb-2">2. Priority Vector (Eigenvector)</h3>
                <p class="text-xs text-ink-muted mb-1">Rata-rata baris dari matriks ternormalisasi</p>
                <code class="font-mono text-sm text-teal">w<sub>i</sub> = (1/n) × Σ a'<sub>ij</sub></code>
            </div>
            <div class="bg-paper border border-border p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-ink mb-2">3. Consistency Index</h3>
                <code class="font-mono text-sm text-teal">CI = (λ<sub>max</sub> − n) / (n − 1)</code>
            </div>
            <div class="bg-paper border border-border p-4">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-ink mb-2">4. Consistency Ratio</h3>
                <code class="font-mono text-sm text-teal">CR = CI / RI</code>
                <p class="text-xs text-ink-muted mt-1">Konsisten jika <strong class="text-teal">CR &lt; 0.1</strong></p>
            </div>
        </div>
    </div>

    <!-- References -->
    <div class="card border-border">
        <h2 class="text-lg font-display text-ink mb-4">Referensi</h2>
        <ul class="space-y-2 text-sm">
            <li>
                <a href="https://en.wikipedia.org/wiki/Analytic_hierarchy_process" target="_blank"
                   class="link">Wikipedia — Analytic Hierarchy Process</a>
            </li>
            <li>
                <a href="https://www.sciencedirect.com/topics/engineering/analytic-hierarchy-process" target="_blank"
                   class="link">ScienceDirect — AHP Overview</a>
            </li>
            <li>
                <span class="text-ink-muted">Saaty, T.L. (2008). "Decision making with the analytic hierarchy process". <em>International Journal of Services Sciences</em>.</span>
            </li>
        </ul>
    </div>
</div>
