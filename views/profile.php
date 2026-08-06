<?php
requireLogin();
$user = getCurrentUser();
$username = $user['username'] ?? $_SESSION['username'] ?? '';
$email = $user['email'] ?? '';
$role = $user['role'] ?? $_SESSION['user_role'] ?? 'user';
$lastLogin = $user['last_login'] ?? null;
$createdAt = $user['created_at'] ?? null;
?>

<div class="fade-in max-w-4xl mx-auto py-6">
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-teal flex items-center justify-center text-white text-lg font-bold">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-2xl font-display text-ink">Pengaturan Profil</h1>
                <p class="text-xs text-ink-muted">Kelola informasi akun dan kata sandi Anda</p>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="mb-6 px-4 py-3 border text-sm flex items-center justify-between <?= $_SESSION['flash_type'] === 'error' ? 'bg-rose-lighter border-rose-light text-rose' : 'bg-teal-lighter border-teal-light text-teal-dark' ?>">
        <span><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Overview Card -->
        <div class="card border-border md:col-span-1">
            <div class="text-center pb-6 border-b border-border">
                <div class="w-20 h-20 bg-teal/10 border-2 border-teal text-teal font-display text-3xl flex items-center justify-center mx-auto mb-3">
                    <?= strtoupper(substr($username, 0, 1)) ?>
                </div>
                <h2 class="text-lg font-bold text-ink truncate"><?= htmlspecialchars($username) ?></h2>
                <p class="text-xs text-ink-muted truncate mb-2"><?= htmlspecialchars($email) ?></p>
                <span class="inline-block px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider <?= $role === 'super_admin' ? 'bg-gold-light text-gold-dark border border-gold/30' : 'bg-teal-xlight text-teal-dark border border-teal/20' ?>">
                    <?= $role === 'super_admin' ? 'Super Admin' : 'Pengguna' ?>
                </span>
            </div>

            <div class="pt-4 space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-border/50">
                    <span class="text-ink-muted">Status Akun:</span>
                    <span class="font-medium text-teal">Aktif ✓</span>
                </div>
                <div class="flex justify-between py-1 border-b border-border/50">
                    <span class="text-ink-muted">Login Terakhir:</span>
                    <span class="font-mono text-ink-muted"><?= $lastLogin ? date('d/m/Y H:i', strtotime($lastLogin)) : '-' ?></span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-ink-muted">Terdaftar Sejak:</span>
                    <span class="font-mono text-ink-muted"><?= $createdAt ? date('d/m/Y', strtotime($createdAt)) : '-' ?></span>
                </div>
            </div>
        </div>

        <!-- Edit Profile & Password Forms -->
        <div class="md:col-span-2 space-y-6">
            <!-- Edit Info Form -->
            <div class="card border-border">
                <h2 class="text-base font-display text-ink mb-4 pb-2 border-b border-border flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Informasi Akun
                </h2>

                <form method="POST" action="?page=profile">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required minlength="3" class="input-field w-full text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-ink-muted mb-1">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="input-field w-full text-sm">
                        </div>

                        <div class="pt-4 border-t border-border mt-6">
                            <h3 class="text-sm font-semibold text-ink mb-3">Keamanan & Ubah Password</h3>
                            <p class="text-xs text-ink-muted mb-4">Biarkan kosong jika tidak ingin mengubah password saat ini.</p>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-ink-muted mb-1">Password Saat Ini (Wajib jika ubah password)</label>
                                    <input type="password" name="current_password" placeholder="••••••••" class="input-field w-full text-sm">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-ink-muted mb-1">Password Baru (min. 6 karakter)</label>
                                        <input type="password" name="new_password" placeholder="••••••••" minlength="6" class="input-field w-full text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-ink-muted mb-1">Konfirmasi Password Baru</label>
                                        <input type="password" name="confirm_password" placeholder="••••••••" minlength="6" class="input-field w-full text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="btn-primary cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
