<!-- Login Page -->
<div class="fade-in max-w-lg mx-auto">
    <div class="slide-up mt-4 sm:mt-8">
        <!-- Decorative header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-ink relative overflow-hidden mb-5">
                <svg class="w-7 h-7 text-white relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <div class="absolute bottom-0 right-0 w-4 h-4 bg-gold"></div>
            </div>
            <h2 class="text-3xl font-display text-ink">Masuk</h2>
            <p class="text-sm text-ink-muted mt-2">Masuk untuk melihat riwayat analisis Anda</p>
        </div>

        <!-- Flash messages handled in header.php now -->

        <div class="card border-border">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="auth_login">

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">Username atau Email</label>
                    <input type="text" name="login_id" required
                           class="input-field"
                           placeholder="Masukkan username atau email"
                           autocomplete="username">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-ink mb-2">Password</label>
                    <input type="password" name="password" required
                           class="input-field"
                           placeholder="Masukkan password"
                           autocomplete="current-password">
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3 cursor-pointer">
                    Masuk
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-border">
                <p class="text-sm text-ink-muted text-center">
                    Belum punya akun?
                    <a href="?page=register" class="link">Daftar sekarang</a>
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
