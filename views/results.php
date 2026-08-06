<?php
/**
 * Results Page — Menampilkan seluruh tahapan AHP:
 * Matriks perbandingan, normalisasi, bobot, λmax, CI/CR,
 * matriks alternatif per kriteria, prioritas global, dan ranking.
 */
$results            = $_SESSION['ahp']['results'] ?? [];
$criteriaResult     = $results['criteria'] ?? [];
$criteriaPriorities = $criteriaResult['priorities'] ?? [];
$altPriorities      = $results['alternatives'] ?? [];
$globalPriorities   = $results['globalPriorities'] ?? [];
$criteriaLabels     = $_SESSION['ahp']['criteria_labels'] ?? [];
$altLabels          = $_SESSION['ahp']['alternative_labels'] ?? [];
$goal               = $_SESSION['ahp']['goal'] ?? '';
$ranked             = getRankedAlternatives($_SESSION['ahp']);
$criteriaIds        = array_keys($_SESSION['ahp']['criteria'] ?? []);
$altIds             = array_keys($_SESSION['ahp']['alternatives'] ?? []);
$n                  = count($criteriaIds);
$nAlt               = count($altIds);
$criteriaMatrix     = $_SESSION['ahp']['pairwise_criteria'] ?? [];
$normalizedCriteria = $criteriaResult['normalized'] ?? [];
$weightedSumCriteria = $criteriaResult['weightedSum'] ?? [];
$consVecCriteria    = $criteriaResult['consistencyVector'] ?? [];
$isConsistent       = $criteriaResult['consistent'] ?? false;

// Criteria codes
$criteriaCodes = ['C01','C02','C03','C04','C05','C06','C07','C08','C09','C10'];

// Helper: format comparison value (whole numbers as-is, else decimal)
function fmtValR($v) {
    if ($v == 0) return '0';
    if (abs($v - round($v)) < 0.0001) return number_format($v, 0);
    return number_format($v, 4);
}
?>

<div class="fade-in max-w-5xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <!-- Header -->
    <div class="text-center mb-8 slide-up">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-ink relative overflow-hidden mb-5">
            <svg class="w-8 h-8 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <div class="absolute bottom-0 right-0 w-4 h-4 bg-teal"></div>
        </div>
        <h1 class="text-3xl font-display text-ink">Hasil Analisis AHP</h1>
        <?php if ($goal): ?>
        <p class="text-sm text-ink-muted mt-2"><?= htmlspecialchars($goal) ?></p>
        <?php endif; ?>
    </div>

    <?php if (empty($criteriaPriorities) || empty($globalPriorities)): ?>
    <div class="card border-border text-center py-12">
        <p class="text-ink-muted mb-4">Belum ada hasil yang bisa ditampilkan.</p>
        <a href="?page=step4" class="btn-primary">Mulai Perbandingan</a>
    </div>
    <?php else: ?>

    <!-- ═══════════════════════════════════════════
         01 — RANKING AKHIR
    ═══════════════════════════════════════════ -->
    <div class="card border-border slide-up mb-8">
        <div class="flex items-center gap-3 mb-6">
            <span class="w-8 h-8 bg-gold flex items-center justify-center text-white text-xs font-bold">01</span>
            <h2 class="text-lg font-display text-ink">Ranking Alternatif</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Chart -->
            <div>
                <canvas id="rankingChart" height="240"></canvas>
            </div>
            <!-- Score list -->
            <div class="space-y-px bg-border">
                <?php foreach ($ranked as $index => $item):
                    $rankBg  = ['bg-teal-xlight', 'bg-gold-light', 'bg-rose-lighter'];
                    $rankBdr = ['border-teal', 'border-gold', 'border-rose'];
                    $rankTxt = ['text-teal', 'text-gold', 'text-rose'];
                ?>
                <div class="flex items-center gap-3 px-4 py-3 bg-surface <?= $index < 3 ? $rankBg[$index] . ' border-l-2 ' . $rankBdr[$index] : '' ?>">
                    <span class="w-8 h-8 border border-border flex items-center justify-center font-mono text-xs font-bold text-ink-muted flex-shrink-0">
                        <?= $index + 1 ?>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-ink truncate"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="text-xs <?= $index < 3 ? $rankTxt[$index] : 'text-ink-muted' ?> font-medium">
                            <?= formatPriority($item['score']) ?>
                        </div>
                    </div>
                    <div class="font-mono text-lg font-bold <?= $index === 0 ? 'text-teal' : 'text-ink-lighter' ?>">
                        #<?= $index + 1 ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- ═══ 02 — BOBOT KRITERIA ═══ -->
        <div class="card border-border slide-up">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-7 h-7 bg-teal flex items-center justify-center text-white text-xs font-bold">02</span>
                <h2 class="text-base font-display text-ink">Bobot Kriteria</h2>
            </div>
            <canvas id="criteriaChart" height="200"></canvas>

            <?php if ($criteriaResult): ?>
            <div class="mt-6 pt-5 border-t border-border">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-ink-muted mb-3">Consistency Check</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">λmax</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['lambdaMax'] ?? 0) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">CI</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['ci'] ?? 0) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">RI (n=<?= $criteriaResult['n'] ?? $n ?>)</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['ri'] ?? 0) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">CR</span>
                        <p class="font-semibold text-sm mt-0.5 font-mono <?= $isConsistent ? 'text-teal' : 'text-rose' ?>">
                            <?= formatNumber($criteriaResult['cr'] ?? 0) ?>
                        </p>
                    </div>
                </div>
                <?php if ($isConsistent): ?>
                <div class="bg-teal-xlight border border-teal-light p-3 mt-3">
                    <p class="text-xs text-teal-dark font-medium">✓ CR &lt; 0.1 — Perbandingan kriteria konsisten</p>
                </div>
                <?php else: ?>
                <div class="bg-rose-lighter border border-rose-light p-3 mt-3">
                    <p class="text-xs text-rose font-medium">✗ CR ≥ 0.1 — Sebaiknya ulangi perbandingan kriteria</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ═══ 03 — SKOR PER KRITERIA ═══ -->
        <div class="card border-border slide-up">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-7 h-7 bg-gold flex items-center justify-center text-white text-xs font-bold">03</span>
                <h2 class="text-base font-display text-ink">Skor per Kriteria</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="text-left pb-2 font-semibold text-ink text-xs uppercase tracking-wider">Alternatif</th>
                            <?php foreach ($criteriaIds as $cIdx => $cid): ?>
                            <th class="text-center pb-2 font-semibold text-ink-muted text-xs px-2 font-mono">
                                <?= $criteriaCodes[$cIdx] ?? ('C' . str_pad($cIdx+1,2,'0',STR_PAD_LEFT)) ?>
                            </th>
                            <?php endforeach; ?>
                            <th class="text-right pb-2 font-semibold text-ink text-xs uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($altIds as $aIdx => $aid): ?>
                        <tr class="hover:bg-paper transition-colors">
                            <td class="py-2.5 pr-4 text-sm font-medium text-ink"><?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                            <?php $rowTotal = 0; ?>
                            <?php foreach ($criteriaIds as $cIdx => $cid):
                                $score  = $altPriorities[$cid][$aIdx] ?? 0;
                                $weight = $criteriaPriorities[$cIdx] ?? 0;
                                $rowTotal += $score * $weight;
                            ?>
                            <td class="text-center py-2.5 text-sm text-ink-muted font-mono"><?= formatPriority($score) ?></td>
                            <?php endforeach; ?>
                            <td class="text-right py-2.5 text-sm font-bold text-teal font-mono"><?= formatPriority($rowTotal) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="border-t-2 border-ink bg-paper">
                            <td class="py-2.5 pr-4 text-xs font-semibold text-ink uppercase tracking-wider">Bobot</td>
                            <?php foreach ($criteriaIds as $cIdx => $cid): ?>
                            <td class="text-center py-2.5 font-mono text-sm font-bold text-teal">
                                <?= formatPriority($criteriaPriorities[$cIdx] ?? 0) ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="text-right py-2.5 font-mono text-sm font-semibold text-ink">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bg-paper border border-border p-3 mt-4">
                <p class="text-xs text-ink-muted">Skor tiap alternatif dikalikan dengan bobot kriteria untuk mendapatkan prioritas global.</p>
            </div>
        </div>
    </div>

    <!-- ═══ 04 — MATRIKS PERBANDINGAN KRITERIA ═══ -->
    <div class="card border-border mb-6">
        <details class="group">
            <summary class="flex items-center gap-3 cursor-pointer list-none">
                <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform flex-shrink-0">+</span>
                <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Matriks Perbandingan Berpasangan Kriteria</h3>
            </summary>
            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-paper border-b border-border">
                            <th class="text-left px-3 py-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Kriteria</th>
                            <?php foreach ($criteriaIds as $cIdx => $cid): ?>
                            <th class="text-center px-3 py-2 font-semibold text-ink-muted text-xs font-mono">
                                <?= $criteriaCodes[$cIdx] ?? ('C' . str_pad($cIdx+1,2,'0',STR_PAD_LEFT)) ?>
                            </th>
                            <?php endforeach; ?>
                            <th class="text-center px-3 py-2 font-semibold text-teal text-xs">Priority</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($criteriaIds as $i => $cid): ?>
                        <tr>
                            <td class="px-3 py-2 font-medium text-ink text-sm"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></td>
                            <?php foreach ($criteriaIds as $j => $cid2):
                                $val = $criteriaMatrix[$i][$j] ?? 1;
                            ?>
                            <td class="text-center px-3 py-2 font-mono text-sm <?= $i === $j ? 'bg-paper text-ink-lighter' : 'text-ink' ?>">
                                <?= fmtValR($val) ?>
                            </td>
                            <?php endforeach; ?>
                            <td class="text-center px-3 py-2 font-bold text-teal font-mono text-sm">
                                <?= formatPriority($criteriaPriorities[$i] ?? 0) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
    </div>

    <!-- ═══ 05 — NORMALISASI MATRIKS KRITERIA ═══ -->
    <div class="card border-border mb-6">
        <details class="group">
            <summary class="flex items-center gap-3 cursor-pointer list-none">
                <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform flex-shrink-0">+</span>
                <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Normalisasi Matriks & Bobot Prioritas Kriteria</h3>
            </summary>
            <div class="mt-5">
                <p class="text-xs text-ink-muted mb-4">Setiap sel dibagi jumlah kolomnya. Bobot = rata-rata baris matriks ternormalisasi.</p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-paper border-b border-border">
                                <th class="text-left px-3 py-2 font-semibold text-ink-muted text-xs">Kriteria</th>
                                <?php foreach ($criteriaIds as $cIdx => $cid): ?>
                                <th class="text-center px-3 py-2 font-semibold text-ink-muted text-xs font-mono">
                                    <?= $criteriaCodes[$cIdx] ?? ('C'.str_pad($cIdx+1,2,'0',STR_PAD_LEFT)) ?>
                                </th>
                                <?php endforeach; ?>
                                <th class="text-center px-3 py-2 font-semibold text-gold text-xs">Jumlah Baris</th>
                                <th class="text-center px-3 py-2 font-semibold text-teal text-xs">Bobot (Wi)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($criteriaIds as $i => $cid):
                                $rowNorm = $normalizedCriteria[$i] ?? [];
                                $rowSum  = !empty($rowNorm) ? array_sum($rowNorm) : 0;
                                $wi      = $criteriaPriorities[$i] ?? 0;
                            ?>
                            <tr class="hover:bg-paper transition-colors">
                                <td class="px-3 py-2 font-medium text-ink text-sm"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></td>
                                <?php foreach ($criteriaIds as $j => $cid2):
                                    $nv = $rowNorm[$j] ?? 0;
                                ?>
                                <td class="text-center px-3 py-2 font-mono text-xs text-ink-muted"><?= number_format($nv, 4) ?></td>
                                <?php endforeach; ?>
                                <td class="text-center px-3 py-2 font-mono text-sm font-semibold text-gold"><?= number_format($rowSum, 4) ?></td>
                                <td class="text-center px-3 py-2 font-mono text-sm font-bold text-teal"><?= number_format($wi, 4) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- λmax, CI, CR detail table -->
                <div class="mt-5 overflow-x-auto">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-ink-muted mb-3">Weighted Sum Vector & Consistency Vector</h4>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-paper border-b border-border">
                                <th class="text-left px-3 py-2 text-xs text-ink-muted">Kriteria</th>
                                <th class="text-center px-3 py-2 text-xs text-ink-muted">Bobot (Wi)</th>
                                <th class="text-center px-3 py-2 text-xs text-ink-muted">Weighted Sum</th>
                                <th class="text-center px-3 py-2 text-xs text-gold">Consistency Vector</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($criteriaIds as $i => $cid):
                                $wi  = $criteriaPriorities[$i] ?? 0;
                                $ws  = $weightedSumCriteria[$i] ?? 0;
                                $cv  = $consVecCriteria[$i] ?? 0;
                            ?>
                            <tr>
                                <td class="px-3 py-2 text-sm text-ink"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></td>
                                <td class="text-center px-3 py-2 font-mono text-sm text-teal"><?= number_format($wi, 4) ?></td>
                                <td class="text-center px-3 py-2 font-mono text-sm"><?= number_format($ws, 4) ?></td>
                                <td class="text-center px-3 py-2 font-mono text-sm text-gold"><?= number_format($cv, 4) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">λmax</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['lambdaMax'] ?? 0, 6) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">CI</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['ci'] ?? 0, 6) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">RI (n=<?= $criteriaResult['n'] ?? $n ?>)</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['ri'] ?? 0, 4) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">CR</span>
                        <p class="font-semibold text-sm mt-0.5 font-mono <?= $isConsistent ? 'text-teal' : 'text-rose' ?>">
                            <?= formatNumber($criteriaResult['cr'] ?? 0, 6) ?>
                        </p>
                    </div>
                </div>
            </div>
        </details>
    </div>

    <!-- ═══ 06 — MATRIKS ALTERNATIF PER KRITERIA ═══ -->
    <div class="card border-border mb-6">
        <details class="group">
            <summary class="flex items-center gap-3 cursor-pointer list-none">
                <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform flex-shrink-0">+</span>
                <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Matriks Perbandingan Alternatif per Kriteria</h3>
            </summary>
            <div class="mt-5 space-y-8">
                <?php foreach ($criteriaIds as $cIdx => $cid):
                    $cCode     = $criteriaCodes[$cIdx] ?? ('C'.str_pad($cIdx+1,2,'0',STR_PAD_LEFT));
                    $cName     = $criteriaLabels[$cid] ?? $cid;
                    $altMatrix = $_SESSION['ahp']['pairwise_alternatives'][$cid] ?? [];

                    // Column sums
                    $altColSums = array_fill(0, $nAlt, 0);
                    for ($j = 0; $j < $nAlt; $j++) {
                        for ($i = 0; $i < $nAlt; $i++) {
                            $altColSums[$j] += $altMatrix[$i][$j] ?? 1;
                        }
                    }
                    // Normalize
                    $altNorm = [];
                    for ($i = 0; $i < $nAlt; $i++) {
                        for ($j = 0; $j < $nAlt; $j++) {
                            $altNorm[$i][$j] = $altColSums[$j] > 0 ? ($altMatrix[$i][$j] ?? 1) / $altColSums[$j] : 0;
                        }
                    }
                    // Weights
                    $altWeights = [];
                    for ($i = 0; $i < $nAlt; $i++) {
                        $altWeights[$i] = $nAlt > 0 ? array_sum($altNorm[$i]) / $nAlt : 0;
                    }
                ?>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-teal mb-3 pb-2 border-b border-border">
                        <?= $cCode ?> — <?= htmlspecialchars($cName) ?>
                    </h4>

                    <!-- Matriks asli -->
                    <p class="text-xs text-ink-muted mb-2">Matriks Perbandingan:</p>
                    <div class="overflow-x-auto mb-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-paper border-b border-border">
                                    <th class="text-left px-3 py-1.5 text-xs text-ink-muted">Alternatif</th>
                                    <?php foreach ($altIds as $aIdx => $aid): ?>
                                    <th class="text-center px-2 py-1.5 text-xs text-ink-muted font-mono">A<?= $aIdx + 1 ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-center px-2 py-1.5 text-xs text-ink-muted">Jml Kolom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <?php foreach ($altIds as $i => $aid): ?>
                                <tr>
                                    <td class="px-3 py-1.5 text-xs text-ink">A<?= $i + 1 ?>. <?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                                    <?php foreach ($altIds as $j => $aid2):
                                        $v = $altMatrix[$i][$j] ?? 1;
                                    ?>
                                    <td class="text-center px-2 py-1.5 font-mono text-xs <?= $i === $j ? 'bg-paper text-ink-lighter' : '' ?>"><?= fmtValR($v) ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-center px-2 py-1.5 text-xs text-ink-lighter">—</td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="bg-paper border-t border-border">
                                    <td class="px-3 py-1.5 text-xs font-semibold text-ink-muted">Jml Kolom</td>
                                    <?php foreach ($altColSums as $cs): ?>
                                    <td class="text-center px-2 py-1.5 font-mono text-xs font-semibold text-ink"><?= number_format($cs, 3) ?></td>
                                    <?php endforeach; ?>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Normalisasi + bobot -->
                    <p class="text-xs text-ink-muted mb-2">Normalisasi & Bobot Prioritas:</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-paper border-b border-border">
                                    <th class="text-left px-3 py-1.5 text-xs text-ink-muted">Alternatif</th>
                                    <?php foreach ($altIds as $aIdx => $aid): ?>
                                    <th class="text-center px-2 py-1.5 text-xs text-ink-muted font-mono">A<?= $aIdx + 1 ?></th>
                                    <?php endforeach; ?>
                                    <th class="text-center px-2 py-1.5 text-xs text-gold">Jumlah</th>
                                    <th class="text-center px-2 py-1.5 text-xs text-teal">Bobot (Wi)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <?php foreach ($altIds as $i => $aid):
                                    $rowSum = array_sum($altNorm[$i]);
                                ?>
                                <tr>
                                    <td class="px-3 py-1.5 text-xs text-ink">A<?= $i + 1 ?>. <?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                                    <?php foreach ($altIds as $j => $aid2): ?>
                                    <td class="text-center px-2 py-1.5 font-mono text-xs"><?= number_format($altNorm[$i][$j], 4) ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-center px-2 py-1.5 font-mono text-xs font-semibold text-gold"><?= number_format($rowSum, 4) ?></td>
                                    <td class="text-center px-2 py-1.5 font-mono text-sm font-bold text-teal"><?= number_format($altWeights[$i], 4) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Priorities from stored result -->
                    <div class="mt-2 text-xs text-ink-muted">
                        Bobot prioritas (dari hasil simpan sesi):
                        <?php foreach ($altIds as $aIdx => $aid):
                            $stored = $altPriorities[$cid][$aIdx] ?? 0;
                        ?>
                        A<?= $aIdx + 1 ?>: <span class="font-mono font-semibold text-teal"><?= number_format($stored, 4) ?></span><?= $aIdx < count($altIds) - 1 ? ' · ' : '' ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </details>
    </div>

    <!-- ═══ 07 — PRIORITAS GLOBAL ═══ -->
    <div class="card border-border mb-8">
        <details class="group" open>
            <summary class="flex items-center gap-3 cursor-pointer list-none">
                <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform flex-shrink-0">+</span>
                <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Penentuan Prioritas Global Alternatif</h3>
            </summary>
            <div class="mt-5 overflow-x-auto">
                <p class="text-xs text-ink-muted mb-4">Skor global = Σ (Bobot Kriteria × Bobot Alternatif per Kriteria)</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-paper border-b border-border">
                            <th class="text-left px-3 py-2 text-xs text-ink-muted">Alternatif</th>
                            <?php foreach ($criteriaIds as $cIdx => $cid):
                                $wi = $criteriaPriorities[$cIdx] ?? 0;
                            ?>
                            <th class="text-center px-3 py-2 text-xs text-ink-muted font-mono">
                                <?= $criteriaCodes[$cIdx] ?? ('C'.str_pad($cIdx+1,2,'0',STR_PAD_LEFT)) ?>
                                <br><span class="text-teal">(<?= number_format($wi, 3) ?>)</span>
                            </th>
                            <?php endforeach; ?>
                            <th class="text-center px-3 py-2 text-xs text-gold font-semibold">Skor Global</th>
                            <th class="text-center px-3 py-2 text-xs text-ink-muted">Rank</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php
                        // Build rank map
                        $globalScoreMap = [];
                        foreach ($altIds as $aIdx => $aid) {
                            $total = 0;
                            foreach ($criteriaIds as $cIdx => $cid) {
                                $total += ($criteriaPriorities[$cIdx] ?? 0) * ($altPriorities[$cid][$aIdx] ?? 0);
                            }
                            $globalScoreMap[$aid] = $total;
                        }
                        $sortedMap = $globalScoreMap;
                        arsort($sortedMap);
                        $rankMap = [];
                        $r = 1;
                        foreach ($sortedMap as $aid => $sc) { $rankMap[$aid] = $r++; }

                        $rankBg2 = ['rank-1' => 'bg-teal-xlight', 'rank-2' => 'bg-gold-light', 'rank-3' => 'bg-rose-lighter'];
                        foreach ($altIds as $aIdx => $aid):
                            $rankNum = $rankMap[$aid];
                            $rowBg = $rankNum <= 3 ? array_values($rankBg2)[$rankNum - 1] : '';
                            $globalScore = $globalScoreMap[$aid];
                        ?>
                        <tr class="hover:bg-paper transition-colors <?= $rowBg ?>">
                            <td class="px-3 py-2 text-sm font-medium text-ink"><?= htmlspecialchars($altLabels[$aid] ?? $aid) ?></td>
                            <?php foreach ($criteriaIds as $cIdx => $cid):
                                $s = $altPriorities[$cid][$aIdx] ?? 0;
                            ?>
                            <td class="text-center px-3 py-2 font-mono text-xs text-ink-muted"><?= number_format($s, 4) ?></td>
                            <?php endforeach; ?>
                            <td class="text-center px-3 py-2 font-mono font-bold text-gold"><?= formatPriority($globalScore) ?></td>
                            <td class="text-center px-3 py-2 font-mono font-bold text-ink-muted">#<?= $rankNum ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-ink bg-paper">
                            <td class="px-3 py-2 text-xs font-semibold text-ink-muted uppercase tracking-wider">Bobot Wi</td>
                            <?php foreach ($criteriaPriorities as $wi): ?>
                            <td class="text-center px-3 py-2 font-mono text-xs font-bold text-teal"><?= number_format($wi, 4) ?></td>
                            <?php endforeach; ?>
                            <td class="text-center px-3 py-2 font-mono text-xs font-bold text-teal">100%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </details>
    </div>

    <!-- ═══ 08 — ACTIONS ═══ -->
    <div class="border-t border-border pt-8 mt-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap gap-3">
                <form method="POST">
                    <input type="hidden" name="action" value="reset">
                    <button type="submit" onclick="return confirm('Mulai analisis baru?')" class="btn-secondary text-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Analisis Baru
                    </button>
                </form>
                <a href="generate_pdf.php?source=session" target="_blank" class="btn-secondary text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export PDF
                </a>
                <?php if ($dbReady && !empty($_SESSION['ahp']['results'])):
                    $savedId = $_SESSION['ahp']['saved_analysis_id'] ?? null;
                ?>
                <form method="POST">
                    <input type="hidden" name="action" value="save_to_db">
                    <button type="submit" class="btn-primary text-sm cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <?= $savedId ? 'Update' : 'Simpan ke Database' ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <a href="?page=dashboard" class="text-sm text-ink-muted hover:text-teal transition-colors">Lihat Semua Analisis Tersimpan →</a>
        </div>
    </div>

    <?php endif; ?>
</div>

<!-- ═══ CHART.JS ═══ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = [
        'rgba(26, 92, 90, 0.75)',
        'rgba(180, 83, 9, 0.75)',
        'rgba(190, 18, 60, 0.75)',
        'rgba(120, 113, 108, 0.6)',
        'rgba(168, 162, 158, 0.6)',
        'rgba(214, 211, 209, 0.6)',
        'rgba(41, 37, 36, 0.5)',
    ];
    const borders = colors.map(c => c.replace('0.75', '1').replace('0.6', '1').replace('0.5', '1'));

    <?php if (!empty($ranked)): ?>
    new Chart(document.getElementById('rankingChart'), {
        type: 'bar',
        data: {
            labels: [<?php foreach ($ranked as $item): ?>'<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>',<?php endforeach; ?>],
            datasets: [{
                label: 'Skor Prioritas (%)',
                data: [<?php foreach ($ranked as $item): ?><?= number_format($item['score'] * 100, 2) ?>,<?php endforeach; ?>],
                backgroundColor: function(ctx) { return ctx.dataIndex === 0 ? 'rgba(26,92,90,0.85)' : colors[ctx.dataIndex % colors.length]; },
                borderColor:     function(ctx) { return ctx.dataIndex === 0 ? 'rgb(26,92,90)' : borders[ctx.dataIndex % borders.length]; },
                borderWidth: 1.5, borderRadius: 2,
            }]
        },
        options: {
            indexAxis: 'y', responsive: true,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.parsed.x.toFixed(2) + '%' } } },
            scales: {
                x: { beginAtZero: true, max: 100, grid: { color: 'rgba(231,229,228,0.5)' }, ticks: { callback: v => v + '%', font: { family: 'JetBrains Mono' } } },
                y: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
            }
        }
    });
    <?php endif; ?>

    <?php if (!empty($criteriaPriorities)): ?>
    new Chart(document.getElementById('criteriaChart'), {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($criteriaIds as $cid): ?>'<?= htmlspecialchars($criteriaLabels[$cid] ?? $cid, ENT_QUOTES) ?>',<?php endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($criteriaPriorities as $p): ?><?= number_format($p * 100, 2) ?>,<?php endforeach; ?>],
                backgroundColor: colors.slice(0, <?= count($criteriaPriorities) ?>),
                borderColor:     borders.slice(0, <?= count($criteriaPriorities) ?>),
                borderWidth: 1.5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { family: 'Outfit', size: 11 } } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed.toFixed(2) + '%' } }
            }
        }
    });
    <?php endif; ?>
});
</script>
