<?php
/**
 * BETELITE - Top Navigation Bar
 */
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/functions.php";

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $sql_cart = "SELECT COUNT(*) as cnt FROM be_cart WHERE user_id = ?";
    $stmt_c = mysqli_prepare($conn, $sql_cart);
    mysqli_stmt_bind_param($stmt_c, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt_c);
    $res_c = mysqli_stmt_get_result($stmt_c);
    if ($row_c = mysqli_fetch_assoc($res_c)) {
        $cart_count = $row_c['cnt'];
    }
}
?>
<nav class="glass-nav sticky top-0 z-40 px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-8">
        <!-- Logo -->
        <a href="index.php" class="flex items-center gap-2 font-display font-bold text-xl text-white tracking-widest no-underline">
            <span class="text-electricGreen">🏆</span> BET<span class="text-electricGreen">ELITE</span>
        </a>

        <!-- Desktop Navigation Items -->
        <div class="hidden md:flex items-center gap-6">
            <a href="<?php echo BASE_URL; ?>marketplace.php" class="text-sm font-medium no-underline text-mutedText hover:text-white transition-colors <?php echo ($current_page === 'marketplace.php') ? 'text-electricGreen border-b-2 border-electricGreen' : ''; ?>">
                Marketplace
            </a>
            <a href="<?php echo BASE_URL; ?>live.php" class="text-sm font-medium no-underline text-mutedText hover:text-white transition-colors flex items-center gap-1.5 <?php echo ($current_page === 'live.php') ? 'text-electricGreen' : ''; ?>">
                <span class="live-pulse"></span>
                Live Matches
            </a>
            <a href="<?php echo BASE_URL; ?>wallet.php" class="text-sm font-medium no-underline text-mutedText hover:text-white transition-colors <?php echo ($current_page === 'wallet.php') ? 'text-electricGreen border-b-2 border-electricGreen' : ''; ?>">
                Wallet System
            </a>
        </div>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-3">
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User online balance -->
            <a href="<?php echo BASE_URL; ?>wallet.php" class="flex items-center gap-2 px-3 py-1.5 bg-darkSec border border-borderSl rounded-full text-xs font-semibold hover:border-electricGreen transition-all no-underline text-white">
                <span class="text-mutedText"><?php echo CURRENCY_SYMBOL; ?></span>
                <span id="nav-wallet-balance" class="text-electricGreen"><?php echo number_format(get_wallet_balance($conn, $_SESSION['user_id']), 2); ?></span>
                <i data-lucide="plus-circle" class="w-4 h-4 text-electricGreen"></i>
            </a>

            <!-- Cart Badge -->
            <a href="<?php echo BASE_URL; ?>cart.php" class="relative p-2 text-mutedText hover:text-white transition-colors no-underline">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                <?php if ($cart_count > 0): ?>
                    <span id="cart-counter-badge" class="absolute -top-1 -right-1 bg-dangerRed text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">
                        <?php echo $cart_count; ?>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Quick Profile Dropdown -->
            <div class="dropdown">
                <button class="flex items-center gap-2 focus:outline-none" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?php echo urlencode($_SESSION['username']); ?>" class="w-8 h-8 rounded-full border border-borderSl hover:border-electricGreen-300" alt="Avatar">
                </button>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark bg-slate-900 border border-slate-800 p-2 rounded-xl mt-2 w-52" aria-labelledby="profileDropdown">
                    <li><h6 class="dropdown-header text-mutedText px-3 py-1 text-xs uppercase tracking-wider">Hi, @<?php echo $_SESSION['username']; ?></h6></li>
                    <li><a class="dropdown-item rounded-lg py-2 text-sm text-white hover:bg-slate-800" href="<?php echo BASE_URL; ?>dashboard.php"><i data-lucide="layout-dashboard" class="w-4 h-4 inline mr-2 text-electricGreen"></i> My Tickets</a></li>
                    
                    <?php if ($_SESSION['user_role'] === 'predictor' || $_SESSION['user_role'] === 'admin'): ?>
                        <li><hr class="dropdown-divider border-slate-800"></li>
                        <li><a class="dropdown-item rounded-lg py-2 text-sm text-white hover:bg-slate-800" href="<?php echo BASE_URL; ?>predictor/dashboard.php"><i data-lucide="shield-alert" class="w-4 h-4 inline mr-2 text-vipGold"></i> Predictor Studio</a></li>
                    <?php endif; ?>

                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li><a class="dropdown-item rounded-lg py-2 text-sm text-white hover:bg-slate-800" href="<?php echo BASE_URL; ?>admin/index.php"><i data-lucide="lock" class="w-4 h-4 inline mr-2 text-dangerRed"></i> Supreme Admin</a></li>
                    <?php endif; ?>

                    <li><hr class="dropdown-divider border-slate-800"></li>
                    <li><a class="dropdown-item rounded-lg py-2 text-sm text-dangerRed hover:bg-slate-800/55" href="<?php echo BASE_URL; ?>api/logout.php"><i data-lucide="log-out" class="w-4 h-4 inline mr-2"></i> Sign Out</a></li>
                </ul>
            </div>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>login.php" class="text-xs font-semibold py-2 px-4 border border-borderSl rounded-lg hover:border-white text-white no-underline transition-all">
                Login
            </a>
            <a href="<?php echo BASE_URL; ?>register.php" class="text-xs font-semibold py-2 px-4 bg-electricGreen hover:bg-greenHover rounded-lg text-darkBg no-underline transition-all">
                Join VIP
            </a>
        <?php endif; ?>
    </div>
</nav>
