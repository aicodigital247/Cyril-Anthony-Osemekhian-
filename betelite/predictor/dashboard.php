<?php
/**
 * BETELITE - Predictor Studio Dashboard Cockpit
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_predictor();

$userId = $_SESSION['user_id'];

// Get predictor profile details
$sql_pr = "SELECT * FROM be_predictors WHERE user_id = ? LIMIT 1";
$st_pr = mysqli_prepare($conn, $sql_pr);
mysqli_stmt_bind_param($st_pr, "i", $userId);
mysqli_stmt_execute($st_pr);
$pred = mysqli_fetch_assoc(mysqli_stmt_get_result($st_pr));

if (!$pred) {
    echo "<main class='max-w-xl mx-auto py-12 text-center text-mutedText'><p>Failed to retrieve predictor credentials.</p></main>";
    require_once __DIR__ . "/../includes/footer.php";
    exit();
}

$predictorId = $pred['id'];

// Fetch some totals
$total_slips = $pred['total_predictions'];
$accuracy = $pred['accuracy_rate'];
$balance = get_wallet_balance($conn, $userId);

$sql_pending = "SELECT COUNT(*) as cnt FROM be_predictions WHERE predictor_id = ? AND status = 'Active'";
$st_pen = mysqli_prepare($conn, $sql_pending);
mysqli_stmt_bind_param($st_pen, "i", $predictorId);
mysqli_stmt_execute($st_pen);
$pending_slips = mysqli_fetch_assoc(mysqli_stmt_get_result($st_pen))['cnt'];

// Fetch some recent predictions
$sql_recent = "SELECT * FROM be_predictions WHERE predictor_id = ? ORDER BY created_at DESC LIMIT 5";
$st_rec = mysqli_prepare($conn, $sql_recent);
mysqli_stmt_bind_param($st_rec, "i", $predictorId);
mysqli_stmt_execute($st_rec);
$res_recent = mysqli_stmt_get_result($st_rec);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
         <div class="space-y-1">
              <span class="text-vipGold font-mono text-xs font-bold tracking-widest uppercase flex items-center gap-1">
                   <i data-lucide="award" class="w-4 h-4 text-vipGold"></i> Verified Experts Arena
              </span>
              <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Predictor Studio Cockpit</h1>
              <p class="text-xs text-mutedText">Forecast high conversion sportsbooks slips, configure premium pricing guides, and request secure cashouts.</p>
         </div>
         <div class="flex flex-wrap gap-2.5">
              <a href="create_prediction.php" class="px-3.5 py-1.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-lg border-none no-underline transition-all flex items-center gap-1.5 cursor-pointer shadow">
                  <i data-lucide="plus-circle" class="w-4 h-4"></i> Compile New Slip
              </a>
              <a href="withdraw.php" class="px-3.5 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg border border-slate-700/60 hover:bg-slate-700 no-underline transition-all flex items-center gap-1.5">
                  <i data-lucide="banknote" class="w-4 h-4"></i> Request Earnings Withdraw
              </a>
         </div>
    </div>

    <!-- Stats -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:grid-cols-4">
         <div class="glass-card p-4 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Compiler Accuracy Ratio</span>
              <p class="text-2xl font-mono font-black text-electricGreen"><?php echo $accuracy; ?>% Wins</p>
              <span class="text-[9px] text-mutedText">Evaluated from historical resolutions</span>
         </div>
         <div class="glass-card p-4 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Total Compiled Slips</span>
              <p class="text-2xl font-mono font-black text-white"><?php echo $total_slips; ?></p>
              <a href="my_predictions.php" class="text-[10px] text-electricGreen hover:underline font-semibold no-underline block pt-2">Manage Slips →</a>
         </div>
         <div class="glass-card p-4 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Withdrawable Balance</span>
              <p class="text-2xl font-mono font-black text-white">₦<?php echo number_format($balance, 2); ?></p>
              <a href="earnings.php" class="text-[10px] text-electricGreen hover:underline font-semibold no-underline block pt-2">Check Ledger Statement →</a>
         </div>
         <div class="glass-card p-4 space-y-1 border border-amber-500/20 bg-amber-500/5">
              <span class="text-[10px] text-amber-500 font-bold uppercase tracking-wider block">Pending Resolution Slips</span>
              <p class="text-2xl font-mono font-black text-amber-400"><?php echo $pending_slips; ?></p>
              <span class="text-[10px] text-amber-500">Awaiting pitch updates</span>
         </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         <!-- Left card: Compiled list -->
         <div class="lg:col-span-2 space-y-4">
              <h3 class="font-display font-medium text-sm text-white uppercase tracking-wider flex items-center gap-2">
                   <i data-lucide="sparkles" class="text-electricGreen w-4 h-4"></i> My Recent Betting Slips
              </h3>

              <div class="space-y-4">
                   <?php if (mysqli_num_rows($res_recent) > 0): ?>
                        <?php while ($r = mysqli_fetch_assoc($res_recent)): ?>
                             <div class="glass-card p-4 flex justify-between items-center gap-4 bg-slate-900/20">
                                  <div class="space-y-1.5">
                                       <h4 class="text-xs font-bold text-white"><?php echo $r['title']; ?></h4>
                                       <p class="text-[10px] text-mutedText"><?php echo $r['description']; ?></p>
                                       <span class="text-[9px] bg-slate-800 text-slate-350 px-2 py-0.5 rounded font-mono">Date: <?php echo date('Y/m/d', strtotime($r['created_at'])); ?></span>
                                  </div>
                                  <div class="flex items-center gap-4">
                                       <div class="text-right">
                                            <p class="font-mono text-xs font-bold text-electricGreen"><?php echo ($r['price'] == 0.0) ? 'FREE' : '₦' . number_format($r['price'], 2); ?></p>
                                            <span class="text-[10px] uppercase font-bold text-mutedText <?php echo ($r['status'] === 'Won') ? 'text-emerald-400' : (($r['status'] === 'Lost') ? 'text-rose-400' : ''); ?>"><?php echo $r['status']; ?></span>
                                       </div>
                                  </div>
                             </div>
                        <?php endwhile; ?>
                   <?php else: ?>
                        <div class="glass-card p-12 text-center text-mutedText">Your compiling workbook is dry. Click "Compile New Slip" above to construct your premium sports predictions.</div>
                   <?php endif; ?>
              </div>
         </div>

         <!-- Right navigation and checklists -->
         <div class="space-y-4">
              <h3 class="font-display font-medium text-sm uppercase tracking-wider text-white">Actions Index</h3>
              <div class="glass-card p-2 divide-y divide-slate-800">
                   <a href="create_prediction.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="plus-circle" class="w-4 h-4 text-electricGreen"></i> Compile Betting Slip</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="my_predictions.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="folder-kanban" class="w-4 h-4 text-vipGold"></i> View Book of Forecasts</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="earnings.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="trending-up" class="w-4 h-4 text-electricGreen"></i> Sales Commissions</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="withdraw.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="banknote" class="w-4 h-4 text-emerald-400"></i> Local Cashouts Request</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
              </div>
         </div>
    </div>

</main>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
