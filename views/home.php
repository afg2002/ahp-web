<!-- Home Page -->
<div class="fade-in max-w-4xl mx-auto">

    <!-- ═══ HERO ═══ -->
    <div class="text-center py-8 sm:py-16 slide-up">
        <!-- Decorative mark -->
        <div class="inline-flex items-center justify-center mb-8 relative">
            <div class="w-20 h-20 bg-ink flex items-center justify-center relative overflow-hidden">
                <span class="text-white font-display text-3xl italic leading-none relative z-10">A</span>
                <div class="absolute bottom-0 right-0 w-5 h-5 bg-gold"></div>
            </div>
        </div>

        <h1 class="text-4xl sm:text-5xl md:text-6xl font-display text-ink leading-[1.1] mb-5 tracking-tight">
            AHP<br>
            <span class="italic text-teal">Calculator</span>
        </h1>

        <p class="text-base sm:text-lg text-ink-muted max-w-2xl mx-auto mb-8 leading-relaxed">
            Sistem Pendukung Keputusan menggunakan metode
            <span class="font-semibold text-ink">Analytical Hierarchy Process</span> —
            menentukan prioritas dan mengambil keputusan terbaik secara objektif.
        </p>

        <div class="flex items-center justify-center gap-4">
            <a href="?page=step1"
               class="btn-primary text-sm sm:text-base px-8 py-3 sm:px-10 sm:py-4">
                Mulai Analisis Baru
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <a href="?page=about"
               class="btn-secondary text-sm sm:text-base px-6 py-3 sm:px-8 sm:py-4">
                Pelajari AHP
            </a>
        </div>

        <?php if (!empty($_SESSION['ahp']['goal']) || !empty($_SESSION['ahp']['criteria'])): ?>
        <div class="mt-8 p-4 bg-teal-lighter border border-teal-light max-w-xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3 text-left">
            <div>
                <div class="text-xs uppercase tracking-wider text-teal font-bold mb-0.5">Sesi Aktif Ditemukan</div>
                <div class="text-sm font-semibold text-ink"><?= htmlspecialchars($_SESSION['ahp']['goal'] ?: 'Analisis Tanpa Judul') ?></div>
                <div class="text-xs text-ink-muted"><?= count($_SESSION['ahp']['criteria'] ?? []) ?> kriteria, <?= count($_SESSION['ahp']['alternatives'] ?? []) ?> alternatif</div>
            </div>
            <a href="?page=step1" class="btn-primary text-xs py-2 px-4 whitespace-nowrap">
                Lanjutkan Analisis →
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- ═══ HOW IT WORKS ═══ -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-px bg-border mt-16 mb-16">
        <div class="card-hover card rounded-none border-0 p-6 sm:p-8 bg-surface">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-8 h-8 bg-teal flex items-center justify-center text-white text-xs font-bold">01</span>
                <span class="text-xs uppercase tracking-widest text-ink-muted font-medium">Setup</span>
            </div>
            <h3 class="text-lg font-display text-ink mb-2">Tentukan Masalah</h3>
            <p class="text-sm text-ink-muted leading-relaxed">
                Definisikan tujuan keputusan, pilih kriteria penilaian, dan daftarkan alternatif pilihan yang akan dievaluasi.
            </p>
        </div>

        <div class="card-hover card rounded-none border-0 p-6 sm:p-8 bg-surface">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-8 h-8 bg-gold flex items-center justify-center text-white text-xs font-bold">02</span>
                <span class="text-xs uppercase tracking-widest text-ink-muted font-medium">Bandingkan</span>
            </div>
            <h3 class="text-lg font-display text-ink mb-2">Perbandingan Berpasangan</h3>
            <p class="text-sm text-ink-muted leading-relaxed">
                Lakukan perbandingan berpasangan antar kriteria dan alternatif menggunakan skala kepentingan Saaty 1–9.
            </p>
        </div>

        <div class="card-hover card rounded-none border-0 p-6 sm:p-8 bg-surface">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-8 h-8 bg-rose flex items-center justify-center text-white text-xs font-bold">03</span>
                <span class="text-xs uppercase tracking-widest text-ink-muted font-medium">Hasil</span>
            </div>
            <h3 class="text-lg font-display text-ink mb-2">Ranking & Analisis</h3>
            <p class="text-sm text-ink-muted leading-relaxed">
                Dapatkan bobot prioritas, uji konsistensi (CR), dan perankingan final alternatif terbaik secara otomatis.
            </p>
        </div>
    </div>

    <!-- ═══ USE CASES ═══ -->
    <div class="mb-16">
        <div class="flex items-center gap-4 mb-8">
            <span class="w-10 h-px bg-border"></span>
            <h2 class="text-sm font-semibold uppercase tracking-widest text-ink-muted">Contoh Penggunaan</h2>
            <span class="flex-1 h-px bg-border"></span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-border">
            <?php
            $useCases = [
                ['icon' => '🏢', 'title' => 'Pemilihan Vendor', 'desc' => 'Harga, kualitas, layanan'],
                ['icon' => '👥', 'title' => 'Rekrutmen', 'desc' => 'Evaluasi kandidat'],
                ['icon' => '📍', 'title' => 'Pemilihan Lokasi', 'desc' => 'Lokasi bisnis terbaik'],
                ['icon' => '📊', 'title' => 'Prioritas Proyek', 'desc' => 'Dampak & urgensi'],
            ];
            foreach ($useCases as $case):
            ?>
            <div class="card-hover card rounded-none border-0 p-5 sm:p-6 bg-surface">
                <span class="text-2xl block mb-2"><?= $case['icon'] ?></span>
                <h3 class="text-sm font-semibold text-ink"><?= $case['title'] ?></h3>
                <p class="text-xs text-ink-muted mt-1"><?= $case['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═══ SAATY SCALE REFERENCE ═══ -->
    <details class="card border-border group mb-8">
        <summary class="flex items-center gap-3 cursor-pointer text-sm font-bold text-ink uppercase tracking-wider">
            <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform">+</span>
            Skala Perbandingan Saaty
        </summary>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border">
                <span class="font-mono font-semibold">1</span>
                <span class="text-ink-muted">Equal Importance</span>
            </div>
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border">
                <span class="font-mono font-semibold">3</span>
                <span class="text-ink-muted">Moderate Importance</span>
            </div>
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border">
                <span class="font-mono font-semibold">5</span>
                <span class="text-ink-muted">Strong Importance</span>
            </div>
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border">
                <span class="font-mono font-semibold">7</span>
                <span class="text-ink-muted">Very Strong Importance</span>
            </div>
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border">
                <span class="font-mono font-semibold">9</span>
                <span class="text-ink-muted">Extreme Importance</span>
            </div>
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border">
                <span class="font-mono font-semibold">2,4,6,8</span>
                <span class="text-ink-muted">Intermediate Values</span>
            </div>
            <div class="flex justify-between px-4 py-2.5 bg-paper border border-border col-span-1 sm:col-span-2">
                <span class="font-mono font-semibold">1/3, 1/5, dst.</span>
                <span class="text-ink-muted">Reciprocals (Less Important)</span>
            </div>
        </div>
    </details>
</div>
