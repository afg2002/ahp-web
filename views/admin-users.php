<?php
$userList = dbGetAllUsers();
$stats = dbGetUserStats();
$editUser = null;
$editId = intval($_GET['edit'] ?? 0);
if ($editId) $editUser = dbGetUser($editId);
?>
<!-- Admin Users -->
<div class="fade-in max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-display text-ink">Manajemen Users</h1>
            <p class="text-sm text-ink-muted mt-1"><?= $stats['total'] ?> total · <?= $stats['active'] ?> aktif</p>
        </div>
        <div class="flex gap-2">
            <a href="export.php?type=users" class="btn-secondary text-sm">CSV</a>
            <a href="report.php?type=users" target="_blank" class="btn-secondary text-sm">PDF</a>
            <a href="?page=admin-dashboard" class="btn-ghost text-sm">← Admin</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-px bg-border mb-6">
        <div class="card border-0 rounded-none p-4 bg-surface text-center">
            <div class="text-lg font-display text-teal"><?= $stats['total'] ?></div>
            <div class="text-xs text-ink-muted">Total</div>
        </div>
        <div class="card border-0 rounded-none p-4 bg-surface text-center">
            <div class="text-lg font-display text-gold"><?= $stats['admins'] ?></div>
            <div class="text-xs text-ink-muted">Admin</div>
        </div>
        <div class="card border-0 rounded-none p-4 bg-surface text-center">
            <div class="text-lg font-display text-rose"><?= $stats['regular'] ?></div>
            <div class="text-xs text-ink-muted">User</div>
        </div>
    </div>

    <!-- Create/Edit Form -->
    <div class="card border-border mb-6">
        <h2 class="text-sm font-semibold text-ink mb-4"><?= $editUser ? 'Edit User' : 'Tambah User Baru' ?></h2>
        <form method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="<?= $editUser ? 'admin_update_user' : 'admin_create_user' ?>">
            <?php if ($editUser): ?><input type="hidden" name="id" value="<?= $editUser['id'] ?>"><?php endif; ?>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Username</label>
                <input type="text" name="username" required class="input-field text-sm" value="<?= $editUser ? htmlspecialchars($editUser['username']) : '' ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Email</label>
                <input type="email" name="email" required class="input-field text-sm" value="<?= $editUser ? htmlspecialchars($editUser['email']) : '' ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Password <?= $editUser ? '(kosongkan jika tidak diubah)' : '' ?></label>
                <input type="password" name="password" class="input-field text-sm" <?= $editUser ? '' : 'required' ?>>
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-1">Role</label>
                    <select name="role" class="input-field text-sm">
                        <option value="user" <?= $editUser && $editUser['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="super_admin" <?= $editUser && $editUser['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                    </select>
                </div>
                <?php if ($editUser): ?>
                <button type="submit" class="btn-primary text-sm py-2.5 cursor-pointer">Update</button>
                <a href="?page=admin-users" class="btn-ghost text-sm py-2.5">Batal</a>
                <?php else: ?>
                <button type="submit" class="btn-primary text-sm py-2.5 cursor-pointer">Tambah</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card border-border">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border">
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">ID</th>
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Username</th>
                        <th class="text-left pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Email</th>
                        <th class="text-center pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Role</th>
                        <th class="text-center pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Status</th>
                        <th class="text-right pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Terdaftar</th>
                        <th class="text-right pb-2 font-semibold text-ink-muted text-xs uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($userList as $u): ?>
                    <tr class="hover:bg-paper transition-colors">
                        <td class="py-2.5 pr-4 font-mono text-xs text-ink-muted">#<?= $u['id'] ?></td>
                        <td class="py-2.5 pr-4 font-medium text-ink">
                            <?= htmlspecialchars($u['username']) ?>
                            <?php if ($u['id'] == ($_SESSION['user_id'] ?? 0)): ?>
                            <span class="text-[10px] text-teal ml-1">(Anda)</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 pr-4 text-ink-muted text-xs"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="py-2.5 text-center">
                            <span class="badge text-[10px] <?= $u['role'] === 'super_admin' ? 'bg-gold-light text-gold-dark border-gold-light' : 'bg-teal-xlight text-teal-dark border-teal-light' ?>">
                                <?= $u['role'] === 'super_admin' ? 'Admin' : 'User' ?>
                            </span>
                        </td>
                        <td class="py-2.5 text-center">
                            <span class="w-2 h-2 inline-block rounded-full <?= $u['is_active'] ? 'bg-teal' : 'bg-ink-lighter' ?>"></span>
                        </td>
                        <td class="py-2.5 text-right text-xs text-ink-muted font-mono"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="py-2.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="?page=admin-users&edit=<?= $u['id'] ?>" class="p-1.5 text-ink-light hover:text-teal transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <?php if ($u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <form method="POST" class="m-0" onsubmit="return confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?')">
                                    <input type="hidden" name="action" value="admin_delete_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="p-1.5 text-ink-light hover:text-rose transition-colors cursor-pointer" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
