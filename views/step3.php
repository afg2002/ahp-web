<?php
$alternatives = $_SESSION['ahp']['alternatives'] ?? [];
?>
<!-- Step 3: Alternatives -->
<div class="fade-in max-w-2xl mx-auto">
    <?php include __DIR__ . '/_progress.php'; ?>

    <div class="card border-border slide-up">
        <div class="flex items-center gap-4 mb-6">
            <span class="w-10 h-10 bg-teal flex items-center justify-center text-white text-sm font-bold">03</span>
            <div>
                <h2 class="text-xl font-display text-ink">Masukkan Alternatif</h2>
                <p class="text-sm text-ink-muted">Apa saja pilihan yang akan dievaluasi?</p>
            </div>
        </div>

        <!-- Add new alternative -->
        <form method="POST" class="flex gap-3 mb-6">
            <input type="hidden" name="action" value="add_alternative">
            <input type="text"
                   name="new_alt_name"
                   placeholder="<?= empty($alternatives) ? 'Contoh: Vendor A, Vendor B, Vendor C' : 'Tambah alternatif...' ?>"
                   class="input-field flex-1"
                   autocomplete="off">
            <button type="submit" class="btn-secondary text-sm px-5 flex items-center gap-2 cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah
            </button>
        </form>

        <!-- Existing Alternatives -->
        <?php if (!empty($alternatives)): ?>
        <div class="space-y-px bg-border mb-6">
            <?php foreach ($alternatives as $id => $name): ?>
            <div class="flex items-center gap-3 px-4 py-3 bg-surface group">
                <span class="w-8 h-8 bg-paper border border-border flex items-center justify-center font-mono text-xs font-bold text-ink-muted">
                    <?= strtoupper(substr($id, 1)) ?>
                </span>
                <span class="flex-1 text-sm font-medium text-ink"><?= htmlspecialchars($name) ?></span>
                <form method="POST" class="m-0 opacity-0 group-hover:opacity-100 transition-opacity">
                    <input type="hidden" name="action" value="delete_alternative">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button type="submit" class="text-ink-light hover:text-rose p-1 cursor-pointer transition-colors" title="Hapus">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Save & Continue -->
        <form method="POST" class="pt-6 border-t border-border">
            <input type="hidden" name="action" value="save_alternatives">
            <?php foreach ($alternatives as $id => $name): ?>
                <input type="hidden" name="alt_names[<?= $id ?>]" value="<?= htmlspecialchars($name) ?>">
            <?php endforeach; ?>

            <div class="flex items-center justify-between">
                <a href="?page=step2" class="text-sm text-ink-muted hover:text-teal transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <button type="submit" class="btn-primary cursor-pointer" <?= empty($alternatives) ? 'disabled' : '' ?>>
                    Lanjut ke Pairwise
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Tips -->
    <div class="bg-teal-xlight border border-teal-light/50 p-5 mt-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-teal flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-teal-dark">
                <strong>Tips:</strong> Minimal 2 alternatif. Beri nama yang jelas agar mudah dibandingkan.
            </p>
        </div>
    </div>
</div>
