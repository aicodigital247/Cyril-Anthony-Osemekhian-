<?php
/**
 * BETELITE - Sportsbook Marketplace Homepage
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

// Fetch active predictions
$sql_preds = "SELECT p.*, pr.display_name, pr.badge, pr.accuracy_rate 
              FROM be_predictions p 
              JOIN be_predictors pr ON p.predictor_id = pr.id 
              ORDER BY p.created_at DESC LIMIT 6";
$res_preds = mysqli_query($conn, $sql_preds);

// Fetch live matches
$sql_matches = "SELECT * FROM be_matches WHERE match_status = 'Live' LIMIT 2";
$res_matches = mysqli_query($conn, $sql_matches);

// Fetch upcoming matches
$sql_upcoming = "SELECT * FROM be_matches WHERE match_status = 'Upcoming' ORDER BY start_datetime ASC LIMIT 3";
$res_upcoming = mysqli_query($conn, $sql_upcoming);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-12 flex-grow">
    
    <!-- Hero Slider Promo Banner -->
    <section class="glass-card relative overflow-hidden p-8 md:p-12 flex flex-col md:flex-row justify-between items-center gap-6" style="background: radial-gradient(circle at top right, rgba(0, 255, 136, 0.15), rgba(15, 23, 42, 0.95));">
        <div class="space-y-4 max-w-xl text-center md:text-left">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-electricGreen/10 border border-electricGreen/30 text-electricGreen text-xs font-semibold rounded-full uppercase tracking-wider">
                <i data-lucide="award" class="w-3.5 h-3.5"></i> Elite Nigeria Predictor Network
            </span>
            <h1 class="font-display font-bold text-3xl md:text-5xl text-white tracking-tight leading-none leading-xs">
                Unlock 90%+ Accuracy <span class="text-electricGreen">VIP Sports Slips</span>
            </h1>
            <p class="text-sm text-mutedText leading-relaxed">
                Connect with verified high-yield sports traders. No long stories, just deep analysis, secure wallet transfers, and pure green slips. Stake like a king.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 pt-2 justify-center md:justify-start">
                <a href="marketplace.php" class="px-6 py-3 bg-electricGreen hover:bg-greenHover text-darkBg text-sm font-semibold rounded-xl text-center transition-all no-underline">
                    Browse Marketplace
                </a>
                <a href="live.php" class="px-6 py-3 bg-darkSec border border-borderSl hover:border-slate-700 text-sm font-semibold rounded-xl text-center transition-all no-underline text-white flex items-center justify-center gap-2">
                    <span class="live-pulse"></span> View Live scoreboards
                </a>
            </div>
        </div>
        <div class="hidden md:block relative w-72 h-44">
            <div class="absolute inset-0 bg-gradient-to-tr from-electricGreen to-vipGold opacity-20 blur-2xl rounded-full"></div>
            <div class="glass-card p-4 relative z-10 border-electricGreen/30 space-y-3">
                <div class="flex justify-between items-center text-xs text-mutedText">
                    <span class="uppercase font-mono">Verified Slip #2049</span>
                    <span class="text-electricGreen font-bold flex items-center gap-1">WON <i data-lucide="check-circle" class="w-3.5 h-3.5"></i></span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span>Arsenal Over 1.5 Goals</span>
                        <span class="font-mono text-mutedText">@1.45</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span>Real Madrid Match Win</span>
                        <span class="font-mono text-mutedText">@1.85</span>
                    </div>
                </div>
                <hr class="border-borderSl">
                <div class="flex justify-between items-center">
                    <span class="text-xs">Accumulated Odds</span>
                    <span class="font-mono text-electricGreen font-bold text-sm">2.68 x</span>
                </div>
            </div>
        </div>
    </section>

    <!-- LIVE MATCHES CENTER -->
    <section class="space-y-4">
        <div class="flex justify-between items-end">
            <h2 class="font-display font-medium text-xl text-white flex items-center gap-2">
                <span class="live-pulse"></span> LIVE SCORECENTER
            </h2>
            <a href="live.php" class="text-xs text-electricGreen hover:underline no-underline">view all live scores & commentary</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (mysqli_num_rows($res_matches) > 0): ?>
                <?php while($match = mysqli_fetch_assoc($res_matches)): ?>
                    <div class="glass-card p-4 relative match-card live-active bg-gradient-to-r from-slate-900 via-slate-900 to-emerald-950/20">
                        <div class="flex justify-between items-center text-xs text-mutedText mb-3">
                            <span class="font-semibold uppercase tracking-widest text-emerald-400 font-mono"><?php echo $match['league']; ?></span>
                            <span class="flex items-center gap-1 text-electricGreen font-mono font-bold">
                                <span class="live-pulse"></span> Live <?php echo $match['match_time']; ?>'
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <div class="space-y-3 w-5/12">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold truncate text-white"><?php echo $match['home_team']; ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold truncate text-white"><?php echo $match['away_team']; ?></span>
                                </div>
                            </div>
                            <div class="w-2/12 flex flex-col items-center justify-center font-bold text-lg font-mono text-electricGreen bg-slate-950/50 py-2 rounded-xl border border-borderSl">
                                <div><?php echo $match['home_score']; ?></div>
                                <div class="text-xs text-mutedText border-t border-borderSl w-full text-center mt-1 pt-1">-</div>
                                <div><?php echo $match['away_score']; ?></div>
                            </div>
                            <div class="w-4/12 text-right space-y-1">
                                <p class="text-[10px] text-mutedText uppercase tracking-wider">Possession</p>
                                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden flex">
                                    <div class="bg-electricGreen h-full" style="width: <?php echo $match['possession_home']; ?>%;"></div>
                                    <div class="bg-slate-600 h-full" style="width: <?php echo $match['possession_away']; ?>%;"></div>
                                </div>
                                <div class="flex justify-between font-mono text-[10px] text-mutedText mt-1">
                                    <span><?php echo $match['possession_home']; ?>%</span>
                                    <span><?php echo $match['possession_away']; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- Simulated Match state in case MySQL is unmigrated -->
                <div class="glass-card p-6 text-center text-mutedText col-span-2">
                    <p class="text-sm">There are no matches currently playing live.</p>
                    <p class="text-xs text-mutedText mt-1">Check the upcoming cards grid below.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- PREMIUM SLIPS MARKETPLACE PREVIEW -->
    <section class="space-y-6">
        <div class="flex justify-between items-end">
            <div>
                <h2 class="font-display font-semibold text-xl text-white">🔥 FEATURED EXPERT TIPS & SLIPS</h2>
                <p class="text-xs text-mutedText">Verified analytical prediction slips available with instant wallet purchase.</p>
            </div>
            <a href="marketplace.php" class="text-xs text-electricGreen hover:underline no-underline">browse full marketplace</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if (mysqli_num_rows($res_preds) > 0): ?>
                <?php while($pred = mysqli_fetch_assoc($res_preds)): ?>
                    <div class="glass-card p-5 flex flex-col justify-between h-80 <?php echo $pred['is_vip'] ? 'border-amber-500/40' : ''; ?>">
                        <div>
                            <!-- Header Info -->
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-slate-800 text-mutedText">
                                        <?php echo $pred['confidence']; ?>% Conf
                                    </span>
                                    <?php if ($pred['is_vip']): ?>
                                        <span class="text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded bg-amber-500/20 text-vipGold border border-amber-500/30">
                                            VIP VIP
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-xs font-semibold text-electricGreen">
                                    <?php echo ($pred['price'] > 0) ? '₦' . number_format($pred['price'], 0) : 'FREE'; ?>
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="font-display font-semibold text-base text-white line-clamp-2 hover:text-electricGreen transition-colors mb-2">
                                <a href="marketplace.php" class="no-underline text-white"><?php echo $pred['title']; ?></a>
                            </h3>
                            <p class="text-xs text-mutedText line-clamp-3 mb-4">
                                <?php echo $pred['description'] ?: 'Complete high confidence multi-league accumulator slip thoroughly analyzed and compiled by our veteran sportsbook trader.'; ?>
                            </p>
                        </div>

                        <!-- Footer actions with predictor avatar -->
                        <div class="border-t border-borderSl pt-3 mt-auto flex justify-between items-center">
                            <div class="flex items-center gap-20">
                                <div class="flex items-center gap-2">
                                    <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?php echo urlencode($pred['display_name']); ?>" class="w-8 h-8 rounded-full border border-slate-700" alt="avatar">
                                    <div>
                                        <p class="text-xs font-bold text-white leading-none">@<?php echo $pred['display_name']; ?></p>
                                        <p class="text-[9px] text-emerald-400 mt-1"><?php echo $pred['accuracy_rate']; ?>% Accuracy</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Simple add button -->
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button onclick="addToCart(<?php echo $pred['id']; ?>)" class="p-2 bg-slate-800 hover:bg-electricGreen hover:text-darkBg text-electricGreen rounded-lg transition-all">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="p-2 bg-slate-800 hover:bg-electricGreen hover:text-darkBg text-electricGreen rounded-lg transition-all">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="glass-card p-6 text-center text-mutedText col-span-3">
                    <p class="text-sm">No featured predictions available yet. Complete predictor profiles are loading active odds bundles.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
