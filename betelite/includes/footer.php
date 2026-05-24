<?php
/**
 * BETELITE - Footer Include
 */
?>
<!-- Footer Navigation Info Section for Larger screens -->
<footer class="mt-auto border-t border-borderSl bg-darkSec/50 py-8 px-4 mb-16 md:mb-0">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
        <div>
            <h5 class="font-display font-semibold text-lg text-white tracking-wider flex items-center justify-center md:justify-start gap-2">
                <span class="text-electricGreen">🏆</span> BETELITE
            </h5>
            <p class="text-xs text-mutedText mt-1"><?php echo SITE_SLOGAN; ?></p>
        </div>
        <div class="flex gap-6 text-sm">
            <a href="marketplace.php" class="text-mutedText hover:text-white transition-colors">Predictions</a>
            <a href="live.php" class="text-mutedText hover:text-white transition-colors">Live Scorecenter</a>
            <a href="wallet.php" class="text-mutedText hover:text-white transition-colors">My Wallet</a>
            <a href="profile.php" class="text-mutedText hover:text-white transition-colors">VIP Tiers</a>
        </div>
        <div class="text-xs text-mutedText">
         © 2026 BETELITE. Licensed in Nigeria with simulated high variance sportsbook prediction pools.
        </div>
    </div>
</footer>

<!-- Modals & Quick slip slideups loaded dynamically -->
<?php include_once __DIR__ . "/modals.php"; ?>

<!-- Mobile Sticky Bottom Navigation -->
<?php include_once __DIR__ . "/bottomnav.php"; ?>

<!-- Bootstrap 5 Bundle with Popper JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Initialize Lucide Icons -->
<script>
  lucide.createIcons();
</script>

<!-- Global Script handler to handle cart counters and balance updates dynamically -->
<script src="assets/js/app.js"></script>

</body>
</html>
