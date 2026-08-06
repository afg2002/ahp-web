<?php
requireAdmin();
$settings = dbGetAllSettings();
?>

<div class="fade-in max-w-4xl mx-auto py-4">
    <!-- Admin Subnav -->
    <div class="flex items-center justify-between border-b border-border pb-4 mb-8">
        <div>
            <span class="text-xs uppercase tracking-widest text-gold font-bold">Administrator</span>
            <h1 class="text-2xl font-display text-ink">Pengaturan Aplikasi & Laporan</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="?page=admin-dashboard" class="btn-ghost text-xs">Dashboard</a>
            <a href="?page=admin-users" class="btn-ghost text-xs">Users</a>
            <a href="?page=admin-criteria" class="btn-ghost text-xs">Kriteria</a>
            <a href="?page=admin-alternatives" class="btn-ghost text-xs">Alternatif</a>
            <a href="?page=admin-settings" class="btn-primary text-xs py-1.5 px-3">Pengaturan</a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 border text-sm flex items-center justify-between <?= $_SESSION['flash_type'] === 'error' ? 'bg-rose-lighter border-rose-light text-rose' : 'bg-teal-lighter border-teal-light text-teal-dark' ?>">
        <span><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="?page=admin-settings">
        <input type="hidden" name="action" value="admin_save_settings">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- App & Institution Identity -->
            <div class="card border-border space-y-4">
                <h2 class="text-base font-display text-ink border-b border-border pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-4-8l-2-2m0 0l-2 2m2-2v6"/>
                    </svg>
                    Identitas Instansi & Logo
                </h2>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Nama Instansi / Perusahaan</label>
                    <input type="text" name="app_institution" value="<?= htmlspecialchars($settings['app_institution']) ?>" required class="input-field text-sm">
                    <p class="text-[11px] text-ink-muted mt-1">Ditampilkan pada header Kop Surat laporan cetak.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Inisial Logo (1-2 Huruf)</label>
                        <input type="text" name="app_logo_text" value="<?= htmlspecialchars($settings['app_logo_text']) ?>" maxlength="3" class="input-field text-sm font-mono text-center uppercase">
                    </div>
                    <div class="flex items-end pb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-ink-muted">Preview:</span>
                            <div class="w-9 h-9 bg-ink flex items-center justify-center relative overflow-hidden border border-border">
                                <span class="text-white font-display text-lg italic leading-none relative z-10"><?= htmlspecialchars($settings['app_logo_text']) ?></span>
                                <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-gold"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">URL Logo Gambar (Opsional)</label>
                    <input type="url" name="app_logo_url" value="<?= htmlspecialchars($settings['app_logo_url']) ?>" placeholder="https://example.com/logo.png" class="input-field text-sm">
                    <p class="text-[11px] text-ink-muted mt-1">Jika diisi, gambar ini akan menggantikan inisial huruf di laporan.</p>
                </div>
            </div>

            <!-- Report & Signature Settings -->
            <div class="card border-border space-y-4">
                <h2 class="text-base font-display text-ink border-b border-border pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Format Laporan & Tanda Tangan
                </h2>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Judul Tanda Tangan (Label Quo)</label>
                    <input type="text" name="report_signer_title" value="<?= htmlspecialchars($settings['report_signer_title']) ?>" placeholder="Hormat Kami," required class="input-field text-sm">
                    <p class="text-[11px] text-ink-muted mt-1">Contoh: <code>Hormat Kami,</code> atau <code>Pimpinan / Notaris,</code></p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Nama Penandatangan (Nama Terang)</label>
                    <input type="text" name="report_signer_name" value="<?= htmlspecialchars($settings['report_signer_name']) ?>" placeholder="Widya Corietania Basri, S.H., M.Kn." required class="input-field text-sm">
                    <p class="text-[11px] text-ink-muted mt-1">Nama yang akan tercetak di dalam kurung laporan.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Tata Letak Kop Surat Laporan</label>
                    <select name="report_header_align" class="input-field text-sm">
                        <option value="center" <?= $settings['report_header_align'] === 'center' ? 'selected' : '' ?>>Simetris Rata Tengah (Rekomendasi Resmi)</option>
                        <option value="left" <?= $settings['report_header_align'] === 'left' ? 'selected' : '' ?>>Logo Kiri + Teks Kanan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card border-border mt-6 bg-paper">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-ink-muted mb-3">Preview Tanda Tangan Laporan</h3>
            <div class="p-4 bg-surface border border-border flex justify-end">
                <div class="text-center w-64">
                    <p class="text-xs text-ink"><?= htmlspecialchars($settings['app_institution']) ?>, <?= date('d F Y') ?></p>
                    <p class="text-xs text-ink-muted mt-1 mb-14"><?= htmlspecialchars($settings['report_signer_title']) ?></p>
                    <div class="flex items-center justify-center font-semibold text-xs text-ink">
                        <span>(</span>
                        <span class="border-b border-dashed border-ink px-4 min-w-[160px] inline-block py-0.5">
                            <?= htmlspecialchars($settings['report_signer_name'] ?: '................................') ?>
                        </span>
                        <span>)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="btn-primary cursor-pointer px-6 py-3">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
