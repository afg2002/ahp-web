<?php
$results = $_SESSION['ahp']['results'] ?? [];
$criteriaPriorities = $results['criteria']['priorities'] ?? [];
$criteriaResult = $results['criteria'] ?? [];
$altPriorities = $results['alternatives'] ?? [];
$globalPriorities = $results['globalPriorities'] ?? [];
$criteriaLabels = $_SESSION['ahp']['criteria_labels'] ?? [];
$altLabels = $_SESSION['ahp']['alternative_labels'] ?? [];
$goal = $_SESSION['ahp']['goal'] ?? '';

$ranked = getRankedAlternatives($_SESSION['ahp']);
$criteriaIds = array_keys($_SESSION['ahp']['criteria']);
$altIds = array_keys($_SESSION['ahp']['alternatives']);
?>

<!-- Results Page -->
<div class="fade-in max-w-4xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <!-- Header -->
    <div class="text-center mb-10 slide-up">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-ink relative overflow-hidden mb-5">
            <svg class="w-8 h-8 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <div class="absolute bottom-0 right-0 w-4 h-4 bg-teal"></div>
        </div>
        <h1 class="text-3xl font-display text-ink">Hasil Analisis</h1>
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

    <!-- ═══ 1. FINAL RANKING ═══ -->
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
                    $medals = ['#1', '#2', '#3'];
                    $rankBg = ['bg-teal-xlight', 'bg-gold-light', 'bg-rose-lighter'];
                    $rankBorder = ['border-teal', 'border-gold', 'border-rose'];
                    $rankText = ['text-teal', 'text-gold', 'text-rose'];
                ?>
                <div class="flex items-center gap-3 px-4 py-3 bg-surface <?= $index < 3 ? $rankBg[$index] . ' border-l-2 ' . $rankBorder[$index] : '' ?>">
                    <span class="w-8 h-8 border border-border flex items-center justify-center font-mono text-xs font-bold text-ink-muted flex-shrink-0">
                        <?= $index + 1 ?>
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-ink truncate"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="text-xs <?= $index < 3 ? $rankText[$index] : 'text-ink-muted' ?> font-medium">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

        <!-- ═══ 2. CRITERIA PRIORITIES ═══ -->
        <div class="card border-border slide-up">
            <div class="flex items-center gap-3 mb-5">
                <span class="w-7 h-7 bg-teal flex items-center justify-center text-white text-xs font-bold">02</span>
                <h2 class="text-base font-display text-ink">Bobot Kriteria</h2>
            </div>
            <div>
                <canvas id="criteriaChart" height="200"></canvas>
            </div>

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
                        <span class="text-xs text-ink-muted font-mono">RI (n=<?= $criteriaResult['n'] ?? count($criteriaPriorities) ?>)</span>
                        <p class="font-semibold text-ink text-sm mt-0.5 font-mono"><?= formatNumber($criteriaResult['ri'] ?? 0) ?></p>
                    </div>
                    <div class="bg-paper border border-border p-3">
                        <span class="text-xs text-ink-muted font-mono">CR</span>
                        <p class="font-semibold text-sm mt-0.5 font-mono <?= ($criteriaResult['consistent'] ?? false) ? 'text-teal' : 'text-rose' ?>">
                            <?= formatNumber($criteriaResult['cr'] ?? 0) ?>
                        </p>
                    </div>
                </div>
                <?php if ($criteriaResult['consistent'] ?? false): ?>
                <div class="bg-teal-xlight border border-teal-light p-3 mt-3">
                    <p class="text-xs text-teal-dark font-medium">✓ CR &lt; 0.1 — Perbandingan kriteria konsisten</p>
                </div>
                <?php else: ?>
                <div class="bg-rose-lighter border border-rose-light p-3 mt-3">
                    <p class="text-xs text-rose font-medium">✗ CR &ge; 0.1 — Sebaiknya ulangi perbandingan kriteria</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ═══ 3. DETAIL SCORES ═══ -->
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
                            <?php foreach ($criteriaIds as $cid): ?>
                            <th class="text-center pb-2 font-semibold text-ink-muted text-xs px-2 font-mono">
                                <?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?>
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
                                $score = $altPriorities[$cid][$aIdx] ?? 0;
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

    <!-- ═══ 4. PAIRWISE MATRIX ═══ -->
    <div class="card border-border mb-8">
        <details class="group">
            <summary class="flex items-center gap-3 cursor-pointer">
                <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform flex-shrink-0">+</span>
                <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Matriks Perbandingan Kriteria</h3>
            </summary>
            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-paper border-b border-border">
                            <th class="text-left px-3 py-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Kriteria</th>
                            <?php foreach ($criteriaIds as $cid): ?>
                            <th class="text-center px-3 py-2 font-semibold text-ink-muted text-xs font-mono"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></th>
                            <?php endforeach; ?>
                            <th class="text-center px-3 py-2 font-semibold text-teal text-xs uppercase tracking-wider">Priority</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php foreach ($criteriaIds as $i => $cid): ?>
                        <tr>
                            <td class="px-3 py-2 font-medium text-ink text-sm"><?= htmlspecialchars($criteriaLabels[$cid] ?? $cid) ?></td>
                            <?php foreach ($criteriaIds as $j => $cid2):
                                $val = $_SESSION['ahp']['pairwise_criteria'][$i][$j] ?? 1;
                            ?>
                            <td class="text-center px-3 py-2 font-mono text-sm <?= $i === $j ? 'bg-paper text-ink-lighter' : 'text-ink' ?>">
                                <?= $val >= 1 ? number_format($val, 2) : number_format($val, 4) ?>
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

    <!-- ═══ 5. ACTIONS ═══ -->
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
                backgroundColor: function(ctx) {
                        return ctx.dataIndex === 0 ? 'rgba(26, 92, 90, 0.85)' : colors[ctx.dataIndex % colors.length];
                    },
                    borderColor: function(ctx) {
                        return ctx.dataIndex === 0 ? 'rgb(26, 92, 90)' : borders[ctx.dataIndex % borders.length];
                    },
                borderWidth: 1.5,
                borderRadius: 2,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => ctx.parsed.x.toFixed(2) + '%' }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(231, 229, 228, 0.5)' },
                    ticks: { callback: v => v + '%', font: { family: 'JetBrains Mono' } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit', size: 11 } }
                }
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
                borderColor: borders.slice(0, <?= count($criteriaPriorities) ?>),
                borderWidth: 1.5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { family: 'Outfit', size: 11 }
                    }
                },
                tooltip: {
                    callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed.toFixed(2) + '%' }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>
