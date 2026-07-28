<?php
$criteriaIds = array_keys($_SESSION['ahp']['criteria']);
$criteriaLabels = $_SESSION['ahp']['criteria'];
$altIds = array_keys($_SESSION['ahp']['alternatives']);
$altLabels = $_SESSION['ahp']['alternatives'];

$currentCriterion = $_GET['criterion'] ?? ($criteriaIds[0] ?? '');
if (!isset($criteriaLabels[$currentCriterion])) {
    $currentCriterion = $criteriaIds[0] ?? '';
}

$n = count($altIds);

if ($n < 2):
?>
<div class="fade-in max-w-2xl mx-auto text-center py-12">
    <p class="text-ink-muted mb-4">Minimal 2 alternatif untuk melakukan perbandingan.</p>
    <a href="?page=step3" class="btn-primary">Tambah alternatif</a>
</div>
<?php
return;
endif;
?>
<!-- Step 5: Pairwise Alternatives -->
<div class="fade-in max-w-3xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <div class="card border-border slide-up">
        <div class="flex items-center gap-4 mb-6">
            <span class="w-10 h-10 bg-rose flex items-center justify-center text-white text-sm font-bold">05</span>
            <div>
                <h2 class="text-xl font-display text-ink">Perbandingan Alternatif</h2>
                <p class="text-sm text-ink-muted">
                    Berdasarkan kriteria: <strong><?= htmlspecialchars($criteriaLabels[$currentCriterion] ?? '') ?></strong>
                </p>
            </div>
        </div>

        <!-- Criterion tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach ($criteriaIds as $cid):
                $isDone = isset($_SESSION['ahp']['pairwise_alternatives'][$cid]);
                $isActive = $cid === $currentCriterion;
            ?>
            <a href="?page=step5&criterion=<?= $cid ?>"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium border transition-all duration-200
                      <?= $isActive ? 'bg-teal text-white border-teal' : ($isDone ? 'bg-teal-xlight text-teal-dark border-teal-light' : 'bg-surface text-ink-muted border-border hover:border-ink-muted') ?>">
                <?php if ($isDone): ?>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <?php endif; ?>
                <?= htmlspecialchars($criteriaLabels[$cid]) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Scale reference -->
        <details class="mb-6 bg-paper border border-border group">
            <summary class="flex items-center gap-2 px-4 py-3 text-sm cursor-pointer text-ink-muted hover:text-ink font-medium transition-colors">
                <span class="w-5 h-5 border border-ink-muted flex items-center justify-center text-xs group-open:rotate-45 transition-transform">+</span>
                Lihat Skala Perbandingan
            </summary>
            <div class="px-4 pb-4 grid grid-cols-1 sm:grid-cols-2 gap-1 text-xs">
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">1</span><span class="text-ink-muted">Sama baik</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">3</span><span class="text-ink-muted">Sedikit lebih baik</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">5</span><span class="text-ink-muted">Lebih baik</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">7</span><span class="text-ink-muted">Jauh lebih baik</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border"><span class="font-mono font-medium">9</span><span class="text-ink-muted">Mutlak lebih baik</span></div>
                <div class="flex justify-between px-3 py-1.5 bg-surface border border-border col-span-1 sm:col-span-2"><span class="font-mono font-medium">1/3, 1/5, dst.</span><span class="text-ink-muted">Kebalikan (kurang baik)</span></div>
            </div>
        </details>

        <!-- Instruction -->
        <div class="bg-rose-lighter border border-rose-light/50 px-5 py-4 mb-6">
            <p class="text-sm text-rose text-center">
                Berdasarkan kriteria <strong>"<?= htmlspecialchars($criteriaLabels[$currentCriterion]) ?>"</strong>,
                bandingkan alternatif mana yang lebih baik dan seberapa besar perbedaannya.
            </p>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="save_pairwise_alternatives">
            <input type="hidden" name="criteria_id" value="<?= $currentCriterion ?>">

            <!-- Pairwise Table -->
            <div class="overflow-x-auto -mx-2">
                <div class="min-w-[500px] px-2">
                    <div class="space-y-px bg-border">
                        <?php for ($i = 0; $i < $n; $i++):
                            for ($j = $i + 1; $j < $n; $j++):
                                $key = $currentCriterion . '_' . $altIds[$i] . '_' . $altIds[$j];
                        ?>
                        <div class="flex items-center gap-3 px-4 py-3 bg-surface">
                            <div class="flex-1 min-w-0 text-right">
                                <span class="text-sm font-medium text-ink"><?= htmlspecialchars($altLabels[$altIds[$i]]) ?></span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs text-ink-light whitespace-nowrap">lebih baik</span>
                                <select name="<?= $key ?>" class="input-field text-sm py-1.5 text-center min-w-[80px]" required>
                                    <?php foreach ($saatyValues as $val): ?>
                                    <option value="<?= $val ?>" <?= $val === '1' ? 'selected' : '' ?>><?= $val ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-xs text-ink-light whitespace-nowrap">lebih baik</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-ink"><?= htmlspecialchars($altLabels[$altIds[$j]]) ?></span>
                            </div>
                        </div>
                        <?php endfor; endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
                <a href="?page=step4" class="text-sm text-ink-muted hover:text-teal transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn-primary cursor-pointer">
                    <?php
                    $isLastCriterion = true;
                    foreach ($criteriaIds as $cid) {
                        if ($cid !== $currentCriterion && !isset($_SESSION['ahp']['pairwise_alternatives'][$cid])) {
                            $isLastCriterion = false;
                            break;
                        }
                    }
                    ?>
                    <?php if ($isLastCriterion): ?>
                        Lihat Hasil
                    <?php else: ?>
                        Simpan & Lanjut
                    <?php endif; ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
