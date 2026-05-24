<?php
/**
 * BETELITE - Mobile Sticky Bottom Navigation
 */
?>
<div class="md:hidden fixed bottom-0 left-0 right-0 z-50 glass-nav border-t border-borderSl flex justify-around items-center py-2.5">
    <a href="index.php" class="flex flex-col items-center justify-center text-center no-underline text-xs <?php echo ($current_page === 'index.php') ? 'text-electricGreen' : 'text-mutedText hover:text-white'; ?>">
        <i data-lucide="home" class="w-5 h-5 mb-1"></i>
        <span>Home</span>
    </a>
    <a href="marketplace.php" class="flex flex-col items-center justify-center text-center no-underline text-xs <?php echo ($current_page === 'marketplace.php') ? 'text-electricGreen' : 'text-mutedText hover:text-white'; ?>">
        <i data-lucide="sparkles" class="w-5 h-5 mb-1"></i>
        <span>VIP Tips</span>
    </a>
    <a href="live.php" class="relative flex flex-col items-center justify-center text-center no-underline text-xs <?php echo ($current_page === 'live.php') ? 'text-electricGreen' : 'text-mutedText hover:text-white'; ?>">
        <span class="absolute top-0 right-1 w-2.5 h-2.5 bg-electricGreen rounded-full animate-ping"></span>
        <i data-lucide="activity" class="w-5 h-5 mb-1"></i>
        <span>Live</span>
    </a>
    <a href="wallet.php" class="flex flex-col items-center justify-center text-center no-underline text-xs <?php echo ($current_page === 'wallet.php') ? 'text-electricGreen' : 'text-mutedText hover:text-white'; ?>">
        <i data-lucide="wallet" class="w-5 h-5 mb-1"></i>
        <span>Wallet</span>
    </a>
    <a href="dashboard.php" class="flex flex-col items-center justify-center text-center no-underline text-xs <?php echo ($current_page === 'dashboard.php') ? 'text-electricGreen' : 'text-mutedText hover:text-white'; ?>">
        <i data-lucide="user" class="w-5 h-5 mb-1"></i>
        <span>Betslip</span>
    </a>
</div>
