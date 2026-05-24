<?php
/**
 * BETELITE - Navigation Sidebar include
 */
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/functions.php";

$userId = $_SESSION['user_id'] ?? null;
?>
<aside class="hidden md:flex flex-col w-64 bg-darkSec border-r border-borderSl min-h-screen p-4 space-y-6 flex-shrink-0">
    <div class="space-y-1">
        <p class="text-[10px] text-mutedText uppercase font-bold tracking-widest px-3">Main Console</p>
        <nav class="space-y-1 flex flex-col pt-2">
            <a href="dashboard.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-electricGreen"></i>
                My VIP Slips
            </a>
            <a href="marketplace.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="sparkles" class="w-4 h-4 text-electricGreen"></i>
                Archived Marketplace
            </a>
            <a href="live.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="activity" class="w-4 h-4 text-electricGreen"></i>
                Live Scorecenter
            </a>
        </nav>
    </div>

    <div class="space-y-1 border-t border-slate-800/80 pt-4">
        <p class="text-[10px] text-mutedText uppercase font-bold tracking-widest px-3">Finances & Orders</p>
        <nav class="space-y-1 flex flex-col pt-2">
            <a href="wallet.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="wallet" class="w-4 h-4 text-electricGreen"></i>
                Secure Wallet
            </a>
            <a href="orders.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="shopping-bag" class="w-4 h-4 text-electricGreen"></i>
                Invoice Receipts
            </a>
            <a href="profile.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="user-cog" class="w-4 h-4 text-electricGreen"></i>
                My Coordinates
            </a>
        </nav>
    </div>

    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'predictor' || $_SESSION['user_role'] === 'admin')): ?>
    <div class="space-y-1 border-t border-slate-800/80 pt-4">
        <p class="text-[10px] text-warning uppercase font-bold tracking-widest px-3">Predictor Studio</p>
        <nav class="space-y-1 flex flex-col pt-2">
            <a href="predictor/dashboard.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="shield-alert" class="w-4 h-4 text-vipGold"></i>
                Studio Home
            </a>
            <a href="predictor/create_prediction.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="file-plus" class="w-4 h-4 text-vipGold"></i>
                Compile Slip
            </a>
            <a href="predictor/my_predictions.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="award" class="w-4 h-4 text-vipGold"></i>
                Active Forecasts
            </a>
            <a href="predictor/earnings.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-vipGold"></i>
                Finance Stats
            </a>
        </nav>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
    <div class="space-y-1 border-t border-slate-800/80 pt-4">
         <p class="text-[10px] text-[#ef4444] uppercase font-bold tracking-widest px-3">Supreme Admin</p>
         <nav class="space-y-1 flex flex-col pt-2">
              <a href="admin/index.php" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold rounded-lg no-underline hover:bg-slate-800 transition-colors text-white">
                   <i data-lucide="lock" class="w-4 h-4 text-dangerRed"></i>
                   Overview Grid
              </a>
         </nav>
    </div>
    <?php endif; ?>
</aside>
