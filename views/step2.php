<?php
// Always load fresh criteria from database
$criteria = [];
if ($dbReady) {
    try {
        $dbCriteria = dbGetActiveCriteria();
        foreach ($dbCriteria as $c) {
            $id = 'c' . $c['id'];
            $criteria[$id] = $c['name'];
            $_SESSION['ahp']['criteria'][$id] = $c['name'];
            $_SESSION['ahp']['criteria_labels'][$id] = $c['name'];
        }
    } catch (Exception $e) {}
}
?>
<!-- Step 2: Kriteria -->
<div class="fade-in max-w-2xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <div class="card border-border slide-up">
        <div class="flex items-center gap-4 mb-6">
            <span class="w-10 h-10 bg-teal flex items-center justify-center text-white text-sm font-bold">02</span>
            <div>
                <h2 class="text-xl font-display text-ink">Kriteria Penilaian</h2>
                <p class="text-sm text-ink-muted">6 Kriteria tetap untuk prioritas pengurusan akta</p>
            </div>
        </div>

        <!-- Info banner -->
        <div class="bg-teal-xlight border border-teal-light px-4 py-3 mb-6 flex items-center gap-3">
            <svg class="w-5 h-5 text-teal flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-teal-dark">
                Kriteria ini sudah ditetapkan dan tidak dapat diubah. Langsung lanjut ke input alternatif.
            </p>
        </div>

        <!-- Fixed Criteria List -->
        <?php if (!empty($criteria)): ?>
        <div class="space-y-px bg-border mb-6">
            <?php
            $criteriaCodes = ['C01','C02','C03','C04','C05','C06'];
            $criteriaDescs = [
                'Prioritas berdasarkan tingkat urgensi / kedaruratan',
                'Kelengkapan persyaratan dokumen yang dibutuhkan',
                'Jenis akta yang akan diurus',
                'Nilai transaksi yang terkait dengan akta',
                'Status hubungan klien',
                'Waktu pengajuan permohonan akta',
            ];
            $i = 0; foreach ($criteria as $id => $name):
            ?>
            <div class="flex items-start gap-4 px-5 py-4 bg-surface">
                <div class="w-12 h-12 bg-paper border border-border flex items-center justify-center font-mono text-sm font-bold text-teal flex-shrink-0">
                    <?= $criteriaCodes[$i] ?>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="font-semibold text-ink text-sm"><?= htmlspecialchars($name) ?></span>
                    <p class="text-xs text-ink-muted mt-0.5"><?= $criteriaDescs[$i] ?? '' ?></p>
                </div>
                <span class="w-2 h-2 bg-teal mt-2 flex-shrink-0"></span>
            </div>
            <?php $i++; endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Continue -->
        <form method="POST" action="?page=step3" class="pt-6 border-t border-border">
            <input type="hidden" name="action" value="save_criteria">
            <?php foreach ($criteria as $id => $name): ?>
                <input type="hidden" name="criteria_names[<?= $id ?>]" value="<?= htmlspecialchars($name) ?>">
            <?php endforeach; ?>

            <div class="flex items-center justify-between">
                <a href="?page=step1" class="text-sm text-ink-muted hover:text-teal transition-colors inline-flex items-center gap-1">
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

    <!-- Reference -->
    <div class="bg-gold-light border border-gold-light/50 p-5 mt-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-gold-dark mb-3">📋 6 Kriteria Tetap</h3>
        <p class="text-sm text-gold-dark/80 mb-3">
            Kriteria ini digunakan untuk menentukan prioritas pengurusan akta di <?= htmlspecialchars(APP_INSTITUTION) ?>.
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
            <?php
            $codeList = [
                ['C01', 'Tingkat Urgensi'],
                ['C02', 'Kelengkapan Dokumen'],
                ['C03', 'Jenis Akta'],
                ['C04', 'Nilai Transaksi'],
                ['C05', 'Status Klien'],
                ['C06', 'Waktu Pengajuan'],
            ];
            foreach ($codeList as $c):
            ?>
            <div class="flex justify-between bg-white/60 px-3 py-2.5 border border-gold-light/30">
                <span class="font-semibold text-gold-dark"><?= $c[0] ?></span>
                <span class="text-gold-dark/70"><?= $c[1] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
