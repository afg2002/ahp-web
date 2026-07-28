    </main>

    <!-- ═══════════════════════════════════════════
         FOOTER
         ═══════════════════════════════════════════ -->
    <footer class="border-t border-border mt-16 sm:mt-24 bg-white/50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-ink/10 flex items-center justify-center">
                        <span class="text-ink/50 font-display text-sm italic">A</span>
                    </div>
                    <p class="text-sm text-ink-muted">
                        &copy; <?= date('Y') ?> AHP Calculator
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs text-ink-muted">
                    <span>Sistem Pendukung Keputusan</span>
                    <span class="w-px h-3 bg-border"></span>
                    <span>Metode AHP</span>
                    <span class="w-px h-3 bg-border"></span>
                    <span>PHP Native</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            const firstInput = document.querySelector('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select');
            if (firstInput) setTimeout(() => firstInput.focus(), 150);

            // Smooth scroll
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Animate progress bars on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const bars = document.querySelectorAll('.animate-bar');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const bar = entry.target;
                        const width = bar.dataset.width || '0%';
                        bar.style.width = width;
                        observer.unobserve(bar);
                    }
                });
            }, { threshold: 0.1 });
            bars.forEach(bar => observer.observe(bar));
        });
    </script>
</body>
</html>
