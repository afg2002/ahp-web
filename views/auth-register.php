<!-- Register Page -->
<div class="fade-in max-w-lg mx-auto">
    <div class="slide-up mt-4 sm:mt-8">
        <!-- Decorative header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-ink relative overflow-hidden mb-5">
                <svg class="w-7 h-7 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <div class="absolute bottom-0 right-0 w-4 h-4 bg-gold"></div>
            </div>
            <h2 class="text-3xl font-display text-ink">Daftar Akun Baru</h2>
            <p class="text-sm text-ink-muted mt-2">Buat akun untuk menyimpan riwayat analisis Anda</p>
        </div>

        <div class="card border-border">
            <form method="POST" class="space-y-5">
                <input type="hidden" name="action" value="auth_register">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">Username</label>
                    <input type="text" name="username" required minlength="3" maxlength="50"
                           class="input-field"
                           placeholder="Pilih username (min. 3 karakter)"
                           autocomplete="username">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">Email</label>
                    <input type="email" name="email" required
                           class="input-field"
                           placeholder="Masukkan email aktif"
                           autocomplete="email">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">Password</label>
                    <input type="password" name="password" required minlength="6"
                           class="input-field"
                           placeholder="Minimal 6 karakter"
                           autocomplete="new-password">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">Konfirmasi Password</label>
                    <input type="password" name="password_confirm" required minlength="6"
                           class="input-field"
                           placeholder="Ulangi password"
                           autocomplete="new-password">
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3 cursor-pointer mt-2">
                    Daftar
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-border">
                <p class="text-sm text-ink-muted text-center">
                    Sudah punya akun?
                    <a href="?page=login" class="link">Masuk di sini</a>
                </p>
            </div>

            <div class="mt-4 text-center">
                <a href="?page=home" class="text-xs text-ink-muted hover:text-teal transition-colors inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
