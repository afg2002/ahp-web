<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dbReady ? dbGetSetting('app_name', APP_NAME) : APP_NAME) ?> — <?= APP_TAGLINE ?></title>

    <!-- Local assets (no CDN):
         tailwindcss.js = @tailwindcss/browser@4.3.3 (global build)
         chart.umd.min.js = chart.js@4.5.1
         fonts.css = DM Serif Display + Outfit (variable) + JetBrains Mono, vendored woff2 -->
    <script src="assets/js/tailwindcss.js"></script>
    <link rel="stylesheet" href="assets/css/fonts.css">
    <script src="assets/js/chart.umd.min.js"></script>

    <style type="text/tailwindcss">
        /* ═══════════════════════════════════════════
           DESIGN SYSTEM — Warm Editorial / Academic
           ═══════════════════════════════════════════ */
        @theme {
            --font-display: 'DM Serif Display', Georgia, serif;
            --font-body: 'Outfit', 'Segoe UI', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;

            --color-paper: #F7F3EE;
            --color-ink: #292524;
            --color-ink-muted: #78716C;
            --color-ink-light: #A8A29E;
            --color-ink-lighter: #D6D3D1;

            --color-teal: #1A5C5A;
            --color-teal-dark: #134E4A;
            --color-teal-darker: #0F3D3A;
            --color-teal-light: #CCFBF1;
            --color-teal-lighter: #E6FFFA;
            --color-teal-xlight: #F0FDF9;

            --color-gold: #B45309;
            --color-gold-dark: #92400E;
            --color-gold-darker: #78350F;
            --color-gold-light: #FEF3C7;
            --color-gold-lighter: #FFFBEB;

            --color-rose: #BE123C;
            --color-rose-light: #FFE4E6;
            --color-rose-lighter: #FFF1F2;

            --color-surface: #FFFFFF;
            --color-border: #E7E5E4;
            --color-border-strong: #292524;
            --color-border-accent: #1A5C5A;

            --color-success: #1A5C5A;
            --color-warning: #B45309;
            --color-danger: #BE123C;
        }

        /* ── Base ── */
        body {
            font-family: var(--font-body);
            background-color: var(--color-paper);
            color: var(--color-ink);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Paper noise texture overlay ── */
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 9999;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.4'/%3E%3C/svg%3E");
            background-repeat: repeat;
            background-size: 256px 256px;
            opacity: 0.025;
            mix-blend-mode: multiply;
        }

        /* ── Typography ── */
        h1, h2, h3, h4, .font-display {
            font-family: var(--font-display);
        }

        /* ── Animations ── */
        .fade-in {
            animation: fadeIn 0.6s ease-out both;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .slide-up {
            animation: slideUp 0.5s ease-out both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stagger > * {
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }
        .stagger > *:nth-child(1) { animation-delay: 0.05s; }
        .stagger > *:nth-child(2) { animation-delay: 0.12s; }
        .stagger > *:nth-child(3) { animation-delay: 0.19s; }
        .stagger > *:nth-child(4) { animation-delay: 0.26s; }
        .stagger > *:nth-child(5) { animation-delay: 0.33s; }
        .stagger > *:nth-child(6) { animation-delay: 0.40s; }

        /* ── Components ── */

        /* Card */
        .card {
            background-color: var(--color-surface);
            border: 1.5px solid var(--color-border);
            padding: 1.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            border-color: var(--color-teal);
            box-shadow: 0 2px 20px rgba(26, 92, 90, 0.06);
        }

        /* Button Primary — Teal */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--color-teal);
            color: white;
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.75rem;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-primary:hover {
            background-color: var(--color-teal-dark);
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Button Secondary — Outline dark */
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: transparent;
            color: var(--color-ink);
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.75rem 1.75rem;
            border: 1.5px solid var(--color-ink);
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: var(--color-ink);
            color: white;
        }
        .btn-secondary:active {
            transform: translateY(0);
        }

        /* Button Ghost */
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: transparent;
            color: var(--color-ink-muted);
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-ghost:hover {
            color: var(--color-teal);
            background-color: var(--color-teal-xlight);
        }

        /* Button Danger */
        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: transparent;
            color: var(--color-rose);
            font-weight: 500;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border: 1.5px solid var(--color-rose-light);
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-danger:hover {
            background-color: var(--color-rose-light);
            border-color: var(--color-rose);
        }

        /* Input Field */
        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: var(--color-surface);
            border: 1.5px solid var(--color-border);
            font-family: var(--font-body);
            font-size: 0.9375rem;
            color: var(--color-ink);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .input-field:focus {
            border-color: var(--color-teal);
            box-shadow: 0 0 0 3px rgba(26, 92, 90, 0.1);
        }
        .input-field::placeholder {
            color: var(--color-ink-lighter);
        }
        select.input-field {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            padding-right: 2.25rem;
        }
        select.input-field:focus {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%231A5C5A' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-family: var(--font-body);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border: 1px solid transparent;
        }

        /* Link */
        .link {
            color: var(--color-teal);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border-bottom: 1px solid transparent;
        }
        .link:hover {
            border-bottom-color: var(--color-teal);
        }

        /* Decorative divider */
        .divider {
            height: 2px;
            background-color: var(--color-border);
            width: 100%;
        }
        .divider-accent {
            height: 2px;
            background-color: var(--color-teal);
            width: 3rem;
        }

        /* Progress bar */
        .progress-bar {
            height: 4px;
            border-radius: 2px;
            transition: width 0.6s ease-out;
            background-color: var(--color-teal);
        }

        /* Step indicator */
        .step-circle {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-body);
            font-size: 0.8125rem;
            font-weight: 600;
            border: 2px solid var(--color-border);
            color: var(--color-ink-muted);
            transition: all 0.4s;
            flex-shrink: 0;
        }
        .step-circle.active {
            border-color: var(--color-teal);
            background-color: var(--color-teal);
            color: white;
        }
        .step-circle.completed {
            border-color: var(--color-teal);
            background-color: var(--color-teal-light);
            color: var(--color-teal);
        }
        .step-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--color-ink-muted);
            transition: color 0.3s;
        }
        .step-label.active {
            color: var(--color-teal);
        }
        .step-label.completed {
            color: var(--color-ink);
        }

        /* Flash message */
        .flash-message {
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: 1.5px solid;
        }
        .flash-message.flash-success {
            background-color: var(--color-teal-lighter);
            border-color: var(--color-teal-light);
            color: var(--color-teal-dark);
        }
        .flash-message.flash-error {
            background-color: var(--color-rose-lighter);
            border-color: var(--color-rose-light);
            color: var(--color-rose);
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--color-border);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--color-ink-light);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-ink-muted);
        }
    </style>
</head>
<body>

    <!-- ═══════════════════════════════════════════
         NAVIGATION
         ═══════════════════════════════════════════ -->
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-border">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">

                <!-- Brand -->
                <?php
                $navLogoUrl = dbGetSetting('app_logo_url');
                $navLogoText = dbGetSetting('app_logo_text', 'A');
                ?>
                <a href="?page=home" class="flex items-center gap-3 group">
                    <?php if (!empty($navLogoUrl)): ?>
                    <img src="<?= htmlspecialchars($navLogoUrl) ?>" alt="Logo" class="w-10 h-10 object-contain">
                    <?php else: ?>
                    <div class="w-10 h-10 bg-ink flex items-center justify-center relative overflow-hidden">
                        <span class="text-white font-display text-xl italic leading-none relative z-10"><?= htmlspecialchars($navLogoText) ?></span>
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-gold"></div>
                    </div>
                    <?php endif; ?>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-ink leading-tight tracking-tight"><?= htmlspecialchars(dbGetSetting('app_name', APP_NAME)) ?></span>
                        <span class="text-[10px] text-ink-muted uppercase tracking-widest font-medium">Decision Support</span>
                    </div>
                </a>

                <!-- Nav Links -->
                <div class="flex items-center gap-1 sm:gap-2">

                    <!-- Dashboard -->
                    <a href="?page=dashboard"
                       class="btn-ghost text-xs sm:text-sm hidden sm:inline-flex">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Dashboard
                    </a>

                    <?php if (isSuperAdmin()): ?>
                    <a href="?page=admin-dashboard"
                       class="btn-ghost text-xs sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Admin
                    </a>
                    <?php endif; ?>

                    <!-- About -->
                    <a href="?page=about"
                       class="btn-ghost text-xs sm:text-sm hidden sm:inline-flex">
                        Tentang
                    </a>

                    <!-- Reset -->
                    <?php if (isset($_SESSION['ahp']) && !empty($_SESSION['ahp']['goal'])): ?>
                    <form method="POST" class="m-0">
                        <input type="hidden" name="action" value="reset">
                        <button type="submit" onclick="return confirm('Mulai baru? Semua data akan dihapus.')"
                                class="btn-ghost text-xs sm:text-sm text-ink-muted hover:text-rose">
                            Reset
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Auth Section -->
                    <?php if (isLoggedIn()):
                        $currentUser = getCurrentUser();
                    ?>
                    <div class="relative group ml-1 sm:ml-2">
                        <div class="flex items-center gap-2 px-3 py-1.5 border border-transparent group-hover:border-border cursor-pointer transition-all">
                            <div class="w-8 h-8 bg-teal flex items-center justify-center">
                                <span class="text-white text-xs font-semibold">
                                    <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 2)) ?>
                                </span>
                            </div>
                            <span class="text-sm font-medium text-ink hidden sm:inline">
                                <?= htmlspecialchars($currentUser['username'] ?? '-') ?>
                            </span>
                            <svg class="w-3 h-3 text-ink-muted group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <!-- Dropdown -->
                        <div class="absolute right-0 mt-1 w-48 bg-white border border-border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 translate-y-1 group-hover:translate-y-0 z-50 shadow-sm">
                            <div class="p-1.5">
                                <div class="px-3 py-2 text-xs text-ink-muted border-b border-border mb-1">
                                    <?= htmlspecialchars($currentUser['email'] ?? '') ?>
                                </div>
                                <a href="?page=profile"
                                   class="flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-teal-xlight transition-colors rounded-sm">
                                    <svg class="w-4 h-4 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profil Saya
                                </a>
                                <a href="?page=dashboard"
                                   class="flex items-center gap-2 px-3 py-2 text-sm text-ink hover:bg-teal-xlight transition-colors rounded-sm">
                                    <svg class="w-4 h-4 text-ink-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Riwayat Saya
                                </a>
                                <form method="POST" class="m-0">
                                    <input type="hidden" name="action" value="auth_logout">
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-rose hover:bg-rose-lighter transition-colors rounded-sm cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="?page=login"
                       class="btn-primary text-xs px-4 py-2 sm:px-5 sm:py-2.5">
                        Masuk
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- ═══════════════════════════════════════════
         FLASH MESSAGES (global)
         ═══════════════════════════════════════════ -->
    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-4">
        <div class="flash-message flash-<?= $_SESSION['flash_type'] === 'error' ? 'error' : 'success' ?> fade-in">
            <div class="flex items-center gap-3">
                <?php if ($_SESSION['flash_type'] === 'error'): ?>
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?php else: ?>
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?php endif; ?>
                <span><?= htmlspecialchars($_SESSION['flash_message']) ?></span>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); endif; ?>

    <!-- ═══════════════════════════════════════════
         MAIN CONTENT
         ═══════════════════════════════════════════ -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
