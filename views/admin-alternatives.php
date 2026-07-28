<?php
$altList = dbGetAllGlobalAlternatives();
$stats = dbGetGlobalAltStats();
$editItem = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId) $editItem = dbGetGlobalAlternative($editId);
?>
<!-- Admin Global Alternatives -->
<div class="fade-in max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-display text-ink">Manajemen Alternatif Global</h1>
            <p class="text-sm text-ink-muted mt-1"><?= $stats['total'] ?> total · <?= $stats['active'] ?> aktif</p>
        </div>
        <div class="flex gap-2">
            <a href="export.php?type=alternatives" class="btn-secondary text-sm">CSV</a>
            <a href="report.php?type=alternatives" target="_blank" class="btn-secondary text-sm">PDF</a>
            <a href="?page=admin-dashboard" class="btn-ghost text-sm">← Admin</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-px bg-border mb-6">
        <div class="card border-0 rounded-none p-4 bg-surface text-center">
            <div class="text-lg font-display text-teal"><?= $stats['total'] ?></div>
            <div class="text-xs text-ink-muted">Total Alternatif</div>
        </div>
        <div class="card border-0 rounded-none p-4 bg-surface text-center">
            <div class="text-lg font-display text-gold"><?= $stats['active'] ?></div>
            <div class="text-xs text-ink-muted">Aktif</div>
        </div>
    </div>

    <!-- Most Used -->
    <?php if (!empty($stats['most_used']) && $stats['most_used'][0]['usage_count'] > 0): ?>
    <div class="card border-border mb-6">
        <h2 class="text-xs font-semibold uppercase tracking-wider text-ink-muted mb-3">🔥 Paling Sering Digunakan</h2>
        <div class="space-y-2">
            <?php foreach ($stats['most_used'] as $a): ?>
            <div class="flex items-center gap-3">
                <span class="text-sm text-ink flex-1"><?= htmlspecialchars($a['name']) ?></span>
                <span class="text-xs font-mono text-ink-muted"><?= $a['usage_count'] ?>x</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Create/Edit Form -->
    <div class="card border-border mb-6">
        <h2 class="text-sm font-semibold text-ink mb-4"><?= $editItem ? 'Edit Alternatif' : 'Tambah Alternatif Baru' ?></h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="<?= $editItem ? 'admin_update_alternative' : 'admin_create_alternative' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nama Alternatif</label>
                <input type="text" name="name" required class="input-field text-sm" value="<?= $editItem ? htmlspecialchars($editItem['name']) : '' ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Deskripsi (opsional)</label>
                <input type="text" name="description" class="input-field text-sm" value="<?= $editItem ? htmlspecialchars($editItem['description'] ?? '') : '' ?>">
            </div>
            <div class="flex items-end gap-2">
                <?php if ($editItem): ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Status</label>
                    <select name="is_active" class="input-field text-sm">
                        <option value="1" <?= $editItem['is_active'] ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= !$editItem['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary text-sm py-2.5 cursor-pointer">Update</button>
                <a href="?page=admin-alternatives" class="btn-ghost text-sm py-2.5">Batal</a>
                <?php else: ?>
                <button type="submit" class="btn-primary text-sm py-2.5 cursor-pointer">Tambah</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Alternatives Table -->
    <div class="card border-border">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Nama</th>
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Deskripsi</th>
                        <th class="text-center pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Status</th>
                        <th class="text-right pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Dibuat</th>
                        <th class="text-right pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($altList as $a): ?>
                    <tr class="hover:bg-paper transition-colors">
                        <td class="py-2.5 pr-4 font-medium text-ink"><?= htmlspecialchars($a['name']) ?></td>
                        <td class="py-2.5 pr-4 text-ink-muted text-xs"><?= htmlspecialchars($a['description'] ?? '-') ?></td>
                        <td class="py-2.5 text-center">
                            <span class="badge text-[10px] <?= $a['is_active'] ? 'bg-teal-xlight text-teal-dark border-teal-light' : 'bg-rose-lighter text-rose border-rose-light' ?>">
                                <?= $a['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="py-2.5 text-right text-xs text-ink-muted font-mono"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                        <td class="py-2.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="?page=admin-alternatives&edit=<?= $a['id'] ?>" class="p-1.5 text-ink-light hover:text-teal transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" class="m-0" onsubmit="return confirm('Hapus alternatif <?= htmlspecialchars($a['name']) ?>?')">
                                    <input type="hidden" name="action" value="admin_delete_alternative">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="p-1.5 text-ink-light hover:text-rose transition-colors cursor-pointer" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($altList)): ?>
                    <tr><td colspan="5" class="py-8 text-center text-ink-muted text-sm">Belum ada data alternatif global.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
