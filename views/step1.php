<?php
$goal = $_SESSION['ahp']['goal'] ?? DEFAULT_GOAL;
?>
<!-- Step 1: Goal -->
<div class="fade-in max-w-2xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <div class="card border-border slide-up">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <span class="w-10 h-10 bg-teal flex items-center justify-center text-white text-sm font-bold">01</span>
            <div>
                <h2 class="text-xl font-display text-ink">Tentukan Goal</h2>
                <p class="text-sm text-ink-muted">Apa keputusan yang ingin Anda ambil?</p>
            </div>
        </div>

        <!-- Institution badge -->
        <div class="bg-teal-xlight border border-teal-light px-4 py-3 mb-6 flex items-center gap-3">
            <svg class="w-5 h-5 text-teal flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="text-sm font-medium text-teal-dark"><?= htmlspecialchars(APP_INSTITUTION) ?></span>
        </div>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="action" value="save_goal">

            <div>
                <label for="goal" class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">
                    Nama Goal / Keputusan
                </label>
                <input type="text"
                       id="goal"
                       name="goal"
                       value="<?= htmlspecialchars($goal) ?>"
                       placeholder="Contoh: Prioritas Pengurusan Akta di Widya Corietania Basri"
                       class="input-field text-lg"
                       required
                       autocomplete="off">
                <p class="text-xs text-ink-muted mt-2">Tujuan utama dari analisis keputusan prioritas pengurusan akta.</p>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary">
                    Lanjut ke Kriteria
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Tips -->
    <div class="bg-gold-light border border-gold-light/50 p-5 mt-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-gold flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-xs text-gold-dark font-semibold uppercase tracking-wider mb-1">Skripsi</p>
                <p class="text-sm text-gold-dark">
                    Sistem Pendukung Keputusan untuk Prioritas Pengurusan Akta
                    di <?= htmlspecialchars(APP_INSTITUTION) ?> menggunakan metode AHP.
                </p>
            </div>
        </div>
    </div>
</div>
