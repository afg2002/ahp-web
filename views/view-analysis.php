<?php
$analysisId = intval($_GET['id'] ?? 0);
$analysis = null;
$error = null;

try {
    if ($dbReady && $analysisId > 0) {
        $analysis = dbGetAnalysis($analysisId);
        if (!$analysis) {
            $error = 'Analisis tidak ditemukan.';
        } elseif ($analysis['user_id'] != $_SESSION['user_id']) {
            $error = 'Anda tidak memiliki akses ke analisis ini.';
            $analysis = null;
        }
    } else {
        $error = 'Database tidak tersedia atau ID tidak valid.';
    }
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>
<div class="fade-in max-w-4xl mx-auto">
    <?php if ($error): ?>
    <div class="card border-border text-center py-12">
        <p class="text-rose"><?= htmlspecialchars($error) ?></p>
        <a href="?page=dashboard" class="btn-ghost text-sm mt-4 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
    <?php elseif ($analysis):
        $resultData = null;
        $criteriaPriorities = [];
        $globalPriorities = [];
        $alternativeLabels = [];
        $criteriaLabels = [];
        $ranking = [];
        $criteriaCodes = ['C01','C02','C03','C04','C05','C06'];

        foreach ($analysis['comparisons'] as $comp) {
            if ($comp['type'] === 'results' && $comp['result_data']) {
                $resultData = json_decode($comp['result_data'], true);
                break;
            }
        }

        if ($resultData) {
            $criteriaPriData = $resultData['criteria_priorities'] ?? [];
            // Handle legacy double-encoded data
            if (is_string($criteriaPriData)) {
                $criteriaPriData = json_decode($criteriaPriData, true) ?? [];
            }
            $criteriaPriorities = $criteriaPriData['priorities'] ?? [];
            
            $globalPriorities = $resultData['global_priorities'] ?? [];
            if (is_string($globalPriorities)) {
                $globalPriorities = json_decode($globalPriorities, true) ?? [];
            }
            
            $alternativeLabels = $resultData['alternative_labels'] ?? [];
            $criteriaLabels = $resultData['criteria_labels'] ?? [];
            
            $ranking = $resultData['ranking'] ?? [];
            if (is_string($ranking)) {
                $ranking = json_decode($ranking, true) ?? [];
            }
        }
    ?>

    <!-- Back link -->
    <a href="?page=dashboard" class="inline-flex items-center gap-1 text-sm text-ink-muted hover:text-teal transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Dashboard
    </a>

    <!-- Analysis header -->
    <div class="card border-border slide-up mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-xl font-display text-ink"><?= htmlspecialchars($analysis['goal']) ?></h1>
                <div class="flex flex-wrap gap-4 text-sm text-ink-muted mt-2">
                    <span><?= date('d M Y, H:i', strtotime($analysis['created_at'])) ?></span>
                    <span><?= count($analysis['alternatives_list']) ?> alternatif</span>
                    <?php if ($analysis['client_name']): ?>
                    <span>👤 <?= htmlspecialchars($analysis['client_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="generate_pdf.php?source=db&id=<?= $analysis['id'] ?>" target="_blank"
                   class="btn-secondary text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export PDF
                </a>
                <form method="POST">
                    <input type="hidden" name="action" value="load_analysis">
                    <input type="hidden" name="analysis_id" value="<?= $analysis['id'] ?>">
                    <button type="submit" class="btn-secondary text-sm cursor-pointer">Muat & Edit</button>
                </form>
                <form method="POST" onsubmit="return confirm('Hapus analisis ini?')">
                    <input type="hidden" name="action" value="delete_analysis">
                    <input type="hidden" name="analysis_id" value="<?= $analysis['id'] ?>">
                    <button type="submit" class="btn-danger text-sm cursor-pointer">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card border-border">
            <h2 class="text-sm font-display text-ink mb-4">🏆 Ranking Alternatif</h2>
            <?php if (!empty($ranking)): ?>
            <canvas id="viewRankChart" height="200"></canvas>
            <?php else: ?>
            <p class="text-sm text-ink-muted">Belum ada hasil ranking.</p>
            <?php endif; ?>
        </div>
        <div class="card border-border">
            <h2 class="text-sm font-display text-ink mb-4">⚖️ Bobot Kriteria</h2>
            <?php if (!empty($criteriaPriorities)): ?>
            <canvas id="viewCritChart" height="200"></canvas>
            <?php else: ?>
            <p class="text-sm text-ink-muted">Belum ada data bobot kriteria.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alternatives list -->
    <div class="card border-border mt-6">
        <h2 class="text-sm font-display text-ink mb-4">📌 Daftar Alternatif</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-px bg-border">
            <?php foreach ($analysis['alternatives_list'] as $alt): ?>
            <div class="flex items-center gap-3 px-4 py-3 bg-surface">
                <span class="w-8 h-8 bg-paper border border-border flex items-center justify-center text-xs font-bold text-ink-muted">
                    <?= strtoupper(substr($alt['name'], 0, 1)) ?>
                </span>
                <span class="text-sm font-medium text-ink"><?= htmlspecialchars($alt['name']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Comparison details -->
    <details class="card border-border mt-6 group">
        <summary class="flex items-center gap-3 cursor-pointer">
            <span class="w-6 h-6 border border-ink flex items-center justify-center text-xs group-open:rotate-45 transition-transform flex-shrink-0">+</span>
            <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Detail Perbandingan</h3>
        </summary>
        <div class="mt-6 space-y-4">
            <?php foreach ($analysis['comparisons'] as $comp):
                if ($comp['type'] === 'results') continue;
            ?>
            <div class="bg-paper border border-border p-4">
                <h4 class="text-xs font-semibold text-ink mb-2">
                    <?= $comp['type'] === 'criteria' ? '⚖️ Perbandingan Kriteria' : '🔄 Perbandingan Alternatif — ' . htmlspecialchars($comp['criterion_code'] ?? '') ?>
                </h4>
                <?php $pairData = json_decode($comp['pairwise_data'], true); if ($pairData): ?>
                <pre class="text-xs text-ink-muted font-mono whitespace-pre-wrap"><?= json_encode($pairData, JSON_PRETTY_PRINT) ?></pre>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </details>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const colors = [
            'rgba(26, 92, 90, 0.75)', 'rgba(180, 83, 9, 0.75)',
            'rgba(190, 18, 60, 0.75)', 'rgba(120, 113, 108, 0.6)',
            'rgba(168, 162, 158, 0.6)', 'rgba(214, 211, 209, 0.6)',
            'rgba(41, 37, 36, 0.5)',
        ];

        <?php if (!empty($ranking)): ?>
        new Chart(document.getElementById('viewRankChart'), {
            type: 'bar',
            data: {
                labels: [<?php foreach ($ranking as $r): ?>'<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>',<?php endforeach; ?>],
                datasets: [{
                    label: 'Skor (%)',
                    data: [<?php foreach ($ranking as $r): ?><?= number_format($r['score'] * 100, 2) ?>,<?php endforeach; ?>],
                    backgroundColor: colors,
                    borderColor: colors.map(c => c.replace('0.75', '1').replace('0.6', '1').replace('0.5', '1')),
                    borderWidth: 1.5,
                    borderRadius: 2,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: 'rgba(231, 229, 228, 0.5)' }, ticks: { callback: v => v + '%', font: { family: 'JetBrains Mono' } } },
                    y: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
                }
            }
        });
        <?php endif; ?>

        <?php if (!empty($criteriaPriorities)): ?>
        new Chart(document.getElementById('viewCritChart'), {
            type: 'doughnut',
            data: {
                labels: [<?php foreach ($criteriaPriorities as $idx => $p): ?>'<?= htmlspecialchars($criteriaLabels[$criteriaCodes[$idx] ?? ''] ?? 'Kriteria', ENT_QUOTES) ?>',<?php endforeach; ?>],
                datasets: [{
                    data: [<?php foreach ($criteriaPriorities as $p): ?><?= number_format($p * 100, 2) ?>,<?php endforeach; ?>],
                    backgroundColor: colors,
                    borderWidth: 1.5,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Outfit', size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed.toFixed(2) + '%' } }
                }
            }
        });
        <?php endif; ?>
    });
    </script>

    <?php endif; ?>
</div>
