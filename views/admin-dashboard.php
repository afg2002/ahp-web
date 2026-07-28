<?php
$userStats = dbGetUserStats();
$criteriaStats = dbGetCriteriaStats();
$altStats = dbGetGlobalAltStats();
$analysisStats = dbGetAnalysisStats();
$recentAnalyses = $analysisStats['recent'] ?? [];
?>
<!-- Admin Dashboard -->
<div class="fade-in max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-display text-ink">Admin Dashboard</h1>
            <p class="text-sm text-ink-muted mt-1">Overview sistem AHP Calculator</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-px bg-border mb-8">
        <div class="card border-0 rounded-none p-5 bg-surface text-center">
            <div class="text-2xl font-display text-teal"><?= $userStats['total'] ?></div>
            <div class="text-xs text-ink-muted uppercase tracking-wider font-medium mt-1">Total Users</div>
            <div class="text-[10px] text-ink-light mt-1"><?= $userStats['admins'] ?> admin · <?= $userStats['regular'] ?> user</div>
        </div>
        <div class="card border-0 rounded-none p-5 bg-surface text-center">
            <div class="text-2xl font-display text-gold"><?= $criteriaStats['total'] ?></div>
            <div class="text-xs text-ink-muted uppercase tracking-wider font-medium mt-1">Kriteria</div>
            <div class="text-[10px] text-ink-light mt-1"><?= $criteriaStats['active'] ?> aktif</div>
        </div>
        <div class="card border-0 rounded-none p-5 bg-surface text-center">
            <div class="text-2xl font-display text-rose"><?= $altStats['total'] ?></div>
            <div class="text-xs text-ink-muted uppercase tracking-wider font-medium mt-1">Alternatif Global</div>
            <div class="text-[10px] text-ink-light mt-1"><?= $altStats['active'] ?> aktif</div>
        </div>
        <div class="card border-0 rounded-none p-5 bg-surface text-center">
            <div class="text-2xl font-display text-teal"><?= $analysisStats['total'] ?></div>
            <div class="text-xs text-ink-muted uppercase tracking-wider font-medium mt-1">Total Analisis</div>
            <div class="text-[10px] text-ink-light mt-1"><?= $analysisStats['completed'] ?> selesai</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex flex-wrap gap-3 mb-8">
        <a href="?page=admin-users" class="btn-secondary text-sm">👥 Kelola Users</a>
        <a href="?page=admin-criteria" class="btn-secondary text-sm">📋 Kelola Kriteria</a>
        <a href="?page=admin-alternatives" class="btn-secondary text-sm">📌 Kelola Alternatif</a>
        <a href="?page=dashboard" class="btn-secondary text-sm">📊 Lihat Semua Analisis</a>
    </div>

    <!-- Analysis Stats by Month -->
    <?php if (!empty($analysisStats['by_month'])): ?>
    <div class="card border-border mb-6">
        <h2 class="text-sm font-display text-ink mb-4">Analisis per Bulan</h2>
        <div class="space-y-2">
            <?php foreach (array_reverse($analysisStats['by_month']) as $m): 
                $maxCount = max(array_column($analysisStats['by_month'], 'count'));
                $width = $maxCount > 0 ? ($m['count'] / $maxCount) * 100 : 0;
            ?>
            <div class="flex items-center gap-3">
                <span class="text-xs text-ink-muted w-16 font-mono"><?= $m['month'] ?></span>
                <div class="flex-1 h-5 bg-paper border border-border relative">
                    <div class="h-full bg-teal/60 animate-bar" data-width="<?= $width ?>%" style="width:0%"></div>
                </div>
                <span class="text-xs font-mono text-ink font-semibold w-8 text-right"><?= $m['count'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Analyses -->
    <div class="card border-border">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-display text-ink">Analisis Terbaru</h2>
            <div class="flex gap-3">
                <a href="export.php?type=analyses" class="text-xs text-teal hover:underline">CSV</a>
                <a href="report.php?type=analyses" target="_blank" class="text-xs text-teal hover:underline">PDF</a>
            </div>
        </div>
        <?php if (empty($recentAnalyses)): ?>
        <p class="text-sm text-ink-muted py-4 text-center">Belum ada analisis.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Goal</th>
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">User</th>
                        <th class="text-center pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Status</th>
                        <th class="text-right pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($recentAnalyses as $a): ?>
                    <tr class="hover:bg-paper transition-colors">
                        <td class="py-2.5 pr-4 font-medium text-ink text-sm truncate max-w-[200px]"><?= htmlspecialchars($a['goal']) ?></td>
                        <td class="py-2.5 pr-4 text-ink-muted text-sm"><?= htmlspecialchars($a['username'] ?? '-') ?></td>
                        <td class="py-2.5 text-center">
                            <span class="badge text-[10px] <?= $a['status'] === 'completed' ? 'bg-teal-xlight text-teal-dark border-teal-light' : 'bg-gold-light text-gold-dark border-gold-light' ?>">
                                <?= $a['status'] ?>
                            </span>
                        </td>
                        <td class="py-2.5 text-right text-ink-muted text-xs font-mono"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
