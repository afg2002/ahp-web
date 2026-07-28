<?php
$criteriaList = dbGetAllCriteria();
$stats = dbGetCriteriaStats();
$editItem = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId) $editItem = dbGetCriteria($editId);
?>
<!-- Admin Criteria -->
<div class="fade-in max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-display text-ink">Manajemen Kriteria</h1>
            <p class="text-sm text-ink-muted mt-1"><?= $stats['total'] ?> total · <?= $stats['active'] ?> aktif</p>
        </div>
        <div class="flex gap-2">
            <a href="export.php?type=criteria" class="btn-secondary text-sm">CSV</a>
            <a href="report.php?type=criteria" target="_blank" class="btn-secondary text-sm">PDF</a>
            <a href="?page=admin-dashboard" class="btn-ghost text-sm">← Admin</a>
        </div>
    </div>

    <!-- Create/Edit Form -->
    <div class="card border-border mb-6">
        <h2 class="text-sm font-semibold text-ink mb-4"><?= $editItem ? 'Edit Kriteria' : 'Tambah Kriteria Baru' ?></h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="<?= $editItem ? 'admin_update_criteria' : 'admin_create_criteria' ?>">
            <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Kode</label>
                <input type="text" name="code" required class="input-field text-sm font-mono" value="<?= $editItem ? htmlspecialchars($editItem['code']) : '' ?>" placeholder="C07">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Nama Kriteria</label>
                <input type="text" name="name" required class="input-field text-sm" value="<?= $editItem ? htmlspecialchars($editItem['name']) : '' ?>">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Deskripsi</label>
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
                <a href="?page=admin-criteria" class="btn-ghost text-sm py-2.5">Batal</a>
                <?php else: ?>
                <button type="submit" class="btn-primary text-sm py-2.5 cursor-pointer">Tambah</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Criteria Table -->
    <div class="card border-border">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Kode</th>
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Nama</th>
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Deskripsi</th>
                        <th class="text-center pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Status</th>
                        <th class="text-right pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($criteriaList as $c): ?>
                    <tr class="hover:bg-paper transition-colors">
                        <td class="py-2.5 pr-4 font-mono text-xs font-bold text-teal"><?= htmlspecialchars($c['code']) ?></td>
                        <td class="py-2.5 pr-4 font-medium text-ink"><?= htmlspecialchars($c['name']) ?></td>
                        <td class="py-2.5 pr-4 text-ink-muted text-xs"><?= htmlspecialchars($c['description'] ?? '-') ?></td>
                        <td class="py-2.5 text-center">
                            <span class="badge text-[10px] <?= $c['is_active'] ? 'bg-teal-xlight text-teal-dark border-teal-light' : 'bg-rose-lighter text-rose border-rose-light' ?>">
                                <?= $c['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td class="py-2.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="?page=admin-criteria&edit=<?= $c['id'] ?>" class="p-1.5 text-ink-light hover:text-teal transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" class="m-0" onsubmit="return confirm('Hapus kriteria <?= htmlspecialchars($c['name']) ?>?')">
                                    <input type="hidden" name="action" value="admin_delete_criteria">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="p-1.5 text-ink-light hover:text-rose transition-colors cursor-pointer" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($criteriaList)): ?>
                    <tr><td colspan="5" class="py-8 text-center text-ink-muted text-sm">Belum ada data kriteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
