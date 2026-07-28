<?php
$analyses = [];
$error = null;

try {
    if ($dbReady) {
        $analyses = isLoggedIn() ? dbGetUserAnalyses($_SESSION['user_id']) : [];
    } else {
        $error = 'Database belum diinisialisasi. Jalankan <a href="setup.php" class="link">setup.php</a> terlebih dahulu.';
    }
} catch (Exception $e) {
    $error = 'Error memuat data: ' . $e->getMessage();
}

$totalAnalyses = count($analyses);
$completedAnalyses = 0;
foreach ($analyses as $a) {
    if ($a['status'] === 'completed') $completedAnalyses++;
}
?>
<!-- Dashboard -->
<div class="fade-in max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-display text-ink">Dashboard</h1>
            <p class="text-sm text-ink-muted mt-1">Riwayat analisis AHP yang tersimpan</p>
        </div>
        <a href="?page=step1" class="btn-primary text-sm whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Analisis Baru
        </a>
    </div>

    <?php if ($error): ?>
    <div class="bg-gold-light border border-gold-light/50 p-6 text-center">
        <p class="text-sm text-gold-dark"><?= $error ?></p>
        <a href="setup.php" class="btn-primary text-sm mt-4 inline-flex items-center gap-2">Setup Database</a>
    </div>

    <?php elseif (empty($analyses)): ?>
    <!-- Empty state -->
    <div class="card border-border text-center py-16">
        <div class="w-16 h-16 bg-paper border border-border flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-ink mb-2">Belum Ada Analisis</h3>
        <p class="text-sm text-ink-muted mb-6">Belum ada data analisis yang tersimpan. Mulai analisis baru untuk menyimpan hasil.</p>
        <a href="?page=step1" class="btn-primary text-sm">Mulai Analisis Baru</a>
    </div>

    <?php else: ?>
    <!-- Stats -->
    <div class="grid grid-cols-2 gap-px bg-border mb-6">
        <div class="card border-0 rounded-none p-5 bg-surface text-center">
            <div class="text-2xl font-display text-teal"><?= $totalAnalyses ?></div>
            <div class="text-xs text-ink-muted uppercase tracking-wider font-medium mt-1">Total Analisis</div>
        </div>
        <div class="card border-0 rounded-none p-5 bg-surface text-center">
            <div class="text-2xl font-display text-teal"><?= $completedAnalyses ?></div>
            <div class="text-xs text-ink-muted uppercase tracking-wider font-medium mt-1">Selesai</div>
        </div>
    </div>

    <!-- Analysis List -->
    <div class="space-y-px bg-border">
        <?php foreach ($analyses as $a): ?>
        <div class="card border-0 rounded-none p-5 bg-surface card-hover">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-sm font-semibold text-ink truncate">
                            <?= htmlspecialchars($a['goal']) ?>
                        </h3>
                        <span class="badge <?= $a['status'] === 'completed' ? 'bg-teal-xlight text-teal-dark border-teal-light' : 'bg-gold-light text-gold-dark border-gold-light' ?>">
                            <?= $a['status'] === 'completed' ? 'Selesai' : 'Draft' ?>
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-muted">
                        <span><?= date('d M Y, H:i', strtotime($a['created_at'])) ?></span>
                        <span><?= intval($a['alt_count']) ?> alternatif</span>
                        <?php if (!empty($a['top_alternative'])): ?>
                        <span>Terbaik: <strong class="text-teal"><?= htmlspecialchars($a['top_alternative']) ?></strong>
                            (<?= isset($a['top_score']) ? number_format($a['top_score'] * 100, 1) : 0 ?>%)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <!-- View -->
                    <a href="?page=view&id=<?= $a['id'] ?>"
                       class="p-2 text-ink-light hover:text-teal hover:bg-teal-xlight transition-all"
                       title="Lihat Detail">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <!-- Load -->
                    <?php if ($a['status'] === 'completed'): ?>
                    <form method="POST" class="m-0">
                        <input type="hidden" name="action" value="load_analysis">
                        <input type="hidden" name="analysis_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="p-2 text-ink-light hover:text-teal hover:bg-teal-xlight transition-all cursor-pointer" title="Muat ke Session">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </form>
                    <?php endif; ?>
                    <!-- Delete -->
                    <form method="POST" class="m-0" onsubmit="return confirm('Hapus analisis ini? Data tidak bisa dikembalikan.')">
                        <input type="hidden" name="action" value="delete_analysis">
                        <input type="hidden" name="analysis_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="p-2 text-ink-light hover:text-rose hover:bg-rose-lighter transition-all cursor-pointer" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
