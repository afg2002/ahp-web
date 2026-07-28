<?php
$criteriaIds = array_keys($_SESSION['ahp']['criteria']);
$criteriaLabels = $_SESSION['ahp']['criteria'];
$n = count($criteriaIds);

if ($n < 2):
?>
<div class="fade-in max-w-2xl mx-auto text-center py-12">
    <p class="text-ink-muted mb-4">Minimal 2 kriteria untuk melakukan perbandingan.</p>
    <a href="?page=step2" class="btn-primary">Tambah kriteria</a>
</div>
<?php
return;
endif;
?>
<!-- Step 4: Pairwise Criteria -->
<div class="fade-in max-w-3xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <div class="card border-border slide-up">
        <div class="flex items-center gap-4 mb-6">
            <span class="w-10 h-10 bg-gold flex items-center justify-center text-white text-sm font-bold">04</span>
            <div>
                <h2 class="text-xl font-display text-ink">Perbandingan Kriteria</h2>
                <p class="text-sm text-ink-muted">Seberapa penting kriteria satu dibanding yang lain?</p>
            </div>
        </div>

        <!-- Goal context -->
        <?php if (!empty($_SESSION['ahp']['goal'])): ?>
        <div class="bg-paper border border-border px-4 py-3 mb-6 flex items-center gap-2">
            <svg class="w-4 h-4 text-teal flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="text-sm text-ink">
                <span class="font-semibold">Goal:</span> <?= htmlspecialchars($_SESSION['ahp']['goal']) ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Scale reference -->
        <details class="mb-6 bg-paper border border-border group">
            <summary class="flex items-center gap-2 px-4 py-3 text-sm cursor-pointer text-ink-muted hover:text-ink font-medium transition-colors">
                <span class="w-5 h-5 border border-ink-muted flex items-center justify-center text-xs group-open:rotate-45 transition-transform">+</span>
                Lihat Skala Perbandingan
            </summary>
            <div class="px-4 pb-4 grid grid-cols-1 sm:grid-cols-2 gap-1 text-xs">
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">1</span><span class="text-ink-muted">Sama penting</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">3</span><span class="text-ink-muted">Sedikit lebih penting</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">5</span><span class="text-ink-muted">Lebih penting</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">7</span><span class="text-ink-muted">Sangat penting</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">9</span><span class="text-ink-muted">Mutlak lebih penting</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border col-span-1 sm:col-span-2"><span class="font-mono font-medium">1/3, 1/5, dst.</span><span class="text-ink-muted">Kebalikan (kurang penting)</span></div>
            </div>
        </details>

        <!-- Instruction -->
        <div class="bg-gold-light border border-gold-light/50 px-5 py-4 mb-6">
            <p class="text-sm text-gold-dark text-center">
                <?php if ($n === 2): ?>
                    Bandingkan kedua kriteria di bawah ini.
                <?php else: ?>
                    Untuk setiap pasangan, tentukan <strong>kriteria mana yang lebih penting</strong> dan seberapa besar tingkat kepentingannya.
                <?php endif; ?>
            </p>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="save_pairwise_criteria">

            <!-- Pairwise Table -->
            <div class="overflow-x-auto -mx-2">
                <div class="min-w-[500px] px-2">
                    <div class="space-y-px bg-border">
                        <?php for ($i = 0; $i < $n; $i++):
                            for ($j = $i + 1; $j < $n; $j++):
                                $key = $criteriaIds[$i] . '_' . $criteriaIds[$j];
                        ?>
                        <div class="flex items-center gap-3 px-4 py-3 bg-surface">
                            <div class="flex-1 min-w-0 text-right">
                                <span class="text-sm font-medium text-ink"><?= htmlspecialchars($criteriaLabels[$criteriaIds[$i]]) ?></span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs text-ink-light whitespace-nowrap">lebih penting</span>
                                <select name="<?= $key ?>" class="input-field text-sm py-1.5 text-center min-w-[80px]" required>
                                    <?php foreach ($saatyValues as $val): ?>
                                    <option value="<?= $val ?>" <?= $val === '1' ? 'selected' : '' ?>><?= $val ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-xs text-ink-light whitespace-nowrap">lebih penting</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-ink"><?= htmlspecialchars($criteriaLabels[$criteriaIds[$j]]) ?></span>
                            </div>
                        </div>
                        <?php endfor; endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
                <a href="?page=step3" class="text-sm text-ink-muted hover:text-teal transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn-primary cursor-pointer">
                    Lanjut ke Alternatif
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
