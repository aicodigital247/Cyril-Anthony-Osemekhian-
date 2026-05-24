<?php
/**
 * BETELITE - Prediction Marketplace Archive
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

$filter = $_GET['tab'] ?? 'all';

$sql = "SELECT p.*, pr.display_name, pr.badge, pr.accuracy_rate 
        FROM be_predictions p 
        JOIN be_predictors pr ON p.predictor_id = pr.id ";

if ($filter === 'free') {
    $sql .= " WHERE p.price = 0.00 ";
} elseif ($filter === 'vip') {
    $sql .= " WHERE p.is_vip = 1 ";
}

$sql .= " ORDER BY p.created_at DESC";
$res_preds = mysqli_query($conn, $sql);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <!-- Title Area -->
    <div class="space-y-2">
         <span class="text-electricGreen text-xs font-mono font-bold tracking-widest uppercase">Expert Selection Center</span>
         <h1 class="font-display font-bold text-2xl md:text-4xl text-white">VIP Tipster Marketplace</h1>
         <p class="text-xs text-mutedText max-w-xl">Browse expert tipping bundles. Every slip lists exact live matches, recommended bet markets, historical tipster win-rates, and real-time community reviews.</p>
    </div>

    <!-- Sticky Sub-segment navigation filtering -->
    <div class="flex flex-wrap justify-between items-center gap-4 border-b border-borderSl pb-4">
         <div class="flex gap-2">
              <a href="marketplace.php?tab=all" class="px-4 py-1.5 rounded-lg text-xs font-semibold no-underline transition-all <?php echo ($filter === 'all') ? 'bg-electricGreen text-darkBg' : 'bg-slate-900 border border-slate-800 text-slate-300 hover:text-white'; ?>">
                     All Slips
              </a>
              <a href="marketplace.php?tab=free" class="px-4 py-1.5 rounded-lg text-xs font-semibold no-underline transition-all <?php echo ($filter === 'free') ? 'bg-electricGreen text-darkBg' : 'bg-slate-900 border border-slate-800 text-slate-300 hover:text-white'; ?>">
                     Free Accumulators
              </a>
              <a href="marketplace.php?tab=vip" class="px-4 py-1.5 rounded-lg text-xs font-semibold no-underline transition-all <?php echo ($filter === 'vip') ? 'bg-electricGreen text-darkBg' : 'bg-slate-900 border border-slate-800 text-slate-300 hover:text-white'; ?>">
                     🏆 Premium VIP Slips
              </a>
         </div>
    </div>

    <!-- Active Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
         <?php if (mysqli_num_rows($res_preds) > 0): ?>
             <?php while($pred = mysqli_fetch_assoc($res_preds)): ?>
                 <!-- Card Element -->
                 <div class="glass-card p-6 flex flex-col justify-between h-96 relative <?php echo $pred['is_vip'] ? 'border-amber-500/40' : ''; ?>">
                     <div>
                         <!-- Header info line -->
                         <div class="flex justify-between items-center mb-4">
                              <span class="text-[10px] font-mono font-bold uppercase tracking-wider bg-slate-800 text-mutedText px-2 py-0.5 rounded-full">
                                    <?php echo $pred['confidence']; ?>% Probability
                              </span>
                              <span class="text-sm font-bold text-electricGreen font-mono">
                                   <?php echo ($pred['price'] > 0) ? '₦' . number_format($pred['price'], 2) : 'FREE'; ?>
                              </span>
                         </div>

                         <!-- Content Title -->
                         <h3 class="font-display font-semibold text-lg text-white mb-2 leading-snug hover:text-electricGreen">
                             <?php echo $pred['title']; ?>
                         </h3>
                         
                         <!-- Descriptions -->
                         <p class="text-xs text-mutedText line-clamp-4 leading-relaxed mt-2 mb-4">
                             <?php echo $pred['description'] ?: 'Complete high confidence multi-league accumulator slip thoroughly analyzed and compiled by our veteran sportsbook trader. High probability outcomes.'; ?>
                         </p>
                     </div>

                     <div class="space-y-3.5 mt-auto">
                         <!-- Core Match selections placeholder indicator -->
                         <div class="bg-slate-950/45 p-3 rounded-xl border border-borderSl/85 flex justify-between items-center text-xs">
                              <div class="flex items-center gap-2 text-slate-300">
                                   <i data-lucide="info" class="w-4 h-4 text-electricGreen"></i>
                                   <span>Inside: 2 Selected Events</span>
                              </div>
                              <span class="font-mono text-[10px] text-mutedText bg-slate-800 px-1.5 py-0.5 rounded">Prem League</span>
                         </div>

                         <!-- Profile & CTA actions -->
                         <div class="flex items-center justify-between border-t border-slate-800 pt-3.5">
                              <div class="flex items-center gap-2">
                                   <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?php echo urlencode($pred['display_name']); ?>" class="w-8 h-8 rounded-full border border-slate-700" alt="avatar">
                                   <div>
                                       <p class="text-xs font-bold text-white mb-0">@<?php echo $pred['display_name']; ?></p>
                                        <p class="text-[9px] text-emerald-400 mt-1"><?php echo $pred['accuracy_rate']; ?>% Accuracy Rate</p>
                                   </div>
                              </div>
                              
                              <!-- AJAX Cart Loader Integration -->
                              <?php if (isset($_SESSION['user_id'])): ?>
                                  <button onclick="addToCart(<?php echo $pred['id']; ?>)" class="btn bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold py-2 px-3.5 rounded-lg flex items-center gap-1.5 animate-all border-none">
                                       <i data-lucide="plus" class="w-3.5 h-3.5"></i> Buy Tip
                                  </button>
                              <?php else: ?>
                                  <a href="login.php" class="btn btn-outline-secondary text-xs font-medium py-2 px-3 border-slate-700 text-slate-300 hover:text-white rounded-lg flex items-center gap-1.5 no-underline">
                                       <i data-lucide="lock" class="w-3.5 h-3.5"></i> Lock
                                  </a>
                              <?php endif; ?>
                         </div>
                     </div>
                 </div>
             <?php endwhile; ?>
         <?php else: ?>
             <div class="glass-card p-12 text-center col-span-3 text-mutedText">
                  <i data-lucide="sparkles" class="w-12 h-12 text-mutedText mx-auto mb-3 opacity-30"></i>
                  <p class="text-sm font-semibold">No active betting tips match the selected filters.</p>
                  <p class="text-xs text-mutedText mt-1">Check back soon for freshly formulated expert predictions.</p>
             </div>
         <?php endif; ?>
    </div>

</main>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
