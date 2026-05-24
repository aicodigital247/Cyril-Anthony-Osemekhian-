<?php
/**
 * BETELITE - Supreme Admin Dashboard Cockpit
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

// Restrict to admins
require_admin();

// Fetch summary metrics
$count_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM be_users"))['cnt'];
$count_predictors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM be_predictors"))['cnt'];
$count_matches = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM be_matches"))['cnt'];
$count_predictions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM be_predictions"))['cnt'];
$count_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM be_orders"))['cnt'];
$sum_wallets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(balance) as sm FROM be_wallets"))['sm'] ?? 0;
$count_pending_withdrawals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM be_withdrawals WHERE status = 'pending'"))['cnt'];
$sum_pending_withdrawals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as sm FROM be_withdrawals WHERE status = 'pending'"))['sm'] ?? 0;

// Fetch latest cashier withdrawals for quick trigger actions
$sql_wd = "SELECT w.*, u.username FROM be_withdrawals w 
           JOIN be_users u ON w.user_id = u.id 
           ORDER BY w.created_at DESC LIMIT 5";
$res_wd = mysqli_query($conn, $sql_wd);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
         <div class="space-y-1">
              <span class="text-dangerRed font-mono text-xs font-bold tracking-widest uppercase flex items-center gap-1">
                   <i data-lucide="shield-alert" class="w-4 h-4"></i> System Control Tower
              </span>
              <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Supreme Administrative Terminal</h1>
              <p class="text-xs text-mutedText">Execute cashier approvals, regulate tipster accuracy, monitor active game commentary, and manage platform parameters.</p>
         </div>
         <div class="flex flex-wrap gap-2.5">
              <a href="matches.php" class="px-3.5 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg border border-slate-700/60 hover:bg-slate-700 no-underline transition-all flex items-center gap-1.5">
                  <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Live Match
              </a>
              <a href="withdrawals.php" class="px-3.5 py-1.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-lg no-underline transition-all flex items-center gap-1.5 border-none">
                  <i data-lucide="banknote" class="w-4 h-4"></i> Cashout Inbox (<?php echo $count_pending_withdrawals; ?>)
              </a>
         </div>
    </div>

    <!-- Metrics Grid -->
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:grid-cols-4">
         <div class="glass-card p-4 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Registered Punters</span>
              <p class="text-2xl font-mono font-bold text-white"><?php echo number_format($count_users); ?></p>
              <a href="users.php" class="text-[10px] text-electricGreen hover:underline font-semibold no-underline block pt-2">Manage Users →</a>
         </div>
         <div class="glass-card p-4 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Specialist Tipsters</span>
              <p class="text-2xl font-mono font-bold text-vipGold"><?php echo number_format($count_predictors); ?></p>
              <a href="predictors.php" class="text-[10px] text-vipGold hover:underline font-semibold no-underline block pt-2">Evaluate Expert Records →</a>
         </div>
         <div class="glass-card p-4 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Total Locked Pool Assets</span>
              <p class="text-2xl font-mono font-bold text-white">₦<?php echo number_format($sum_wallets, 2); ?></p>
              <a href="payments.php" class="text-[10px] text-mutedText hover:underline font-semibold no-underline block pt-2">Deposit Records →</a>
         </div>
         <div class="glass-card p-4 space-y-1 border border-amber-500/20 bg-amber-500/5">
              <span class="text-[10px] text-amber-500 font-bold uppercase tracking-wider block">Pending Cashouts</span>
              <p class="text-2xl font-mono font-bold text-amber-400">₦<?php echo number_format($sum_pending_withdrawals, 2); ?></p>
              <a href="withdrawals.php" class="text-[10px] text-amber-500 hover:underline font-bold no-underline block pt-2">Approve Withdrawals →</a>
         </div>
    </section>

    <!-- Sub-navigation Admin Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         
         <!-- Large panel (Cashout approvals & active lists) -->
         <div class="lg:col-span-2 space-y-6">
              
              <div class="space-y-4">
                   <h3 class="font-display font-medium text-sm uppercase tracking-wider text-white flex items-center gap-2">
                        <i data-lucide="clock" class="text-amber-500 w-4 h-4"></i> Flagged Pending Cashout Cashiers
                   </h3>

                   <div class="glass-card overflow-hidden">
                        <div class="overflow-x-auto">
                             <table class="w-full text-left text-xs text-slate-300">
                                  <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold">
                                       <tr>
                                            <th class="p-3">Punter / Reference</th>
                                            <th class="p-3">Destination Details</th>
                                            <th class="p-3 text-right">Amount</th>
                                            <th class="p-3 text-center">Action Clearing</th>
                                       </tr>
                                  </thead>
                                  <tbody class="divide-y divide-slate-800/40">
                                       <?php if (mysqli_num_rows($res_wd) > 0): ?>
                                            <?php while ($wd = mysqli_fetch_assoc($res_wd)): ?>
                                                 <tr id="wd-row-<?php echo $wd['id']; ?>" class="hover:bg-slate-900/10">
                                                      <td class="p-3">
                                                           <p class="font-bold text-white">@<?php echo $wd['username']; ?></p>
                                                           <span class="text-[10px] text-mutedText font-mono"><?php echo $wd['reference']; ?></span>
                                                      </td>
                                                      <td class="p-3">
                                                           <p class="font-semibold text-slate-200"><?php echo $wd['bank_name']; ?></p>
                                                           <span class="text-[10px] text-mutedText"><?php echo $wd['account_number']; ?> - <?php echo $wd['account_name']; ?></span>
                                                      </td>
                                                      <td class="p-3 text-right font-mono font-bold text-dangerRed">
                                                           ₦<?php echo number_format($wd['amount'], 2); ?>
                                                      </td>
                                                      <td class="p-3 text-center">
                                                           <?php if ($wd['status'] === 'pending'): ?>
                                                                <div class="flex items-center justify-center gap-1.5">
                                                                     <button onclick="approvePayout(<?php echo $wd['id']; ?>)" class="px-2 py-1 bg-electricGreen hover:bg-greenHover text-darkBg text-[10px] font-bold rounded cursor-pointer border-none">Approve</button>
                                                                     <button onclick="rejectPayout(<?php echo $wd['id']; ?>)" class="px-2 py-1 bg-red-950 text-red-400 border border-red-900 hover:bg-red-900 hover:text-white text-[10px] font-bold rounded cursor-pointer">Reject</button>
                                                                </div>
                                                           <?php else: ?>
                                                                <span class="px-2 py-0.5 rounded text-[9px] uppercase font-mono font-bold <?php echo ($wd['status'] === 'approved') ? 'bg-emerald-500/10 text-electricGreen' : 'bg-red-500/10 text-rose-400'; ?>">
                                                                     <?php echo $wd['status']; ?>
                                                                </span>
                                                           <?php endif; ?>
                                                      </td>
                                                 </tr>
                                            <?php endwhile; ?>
                                       <?php else: ?>
                                            <tr>
                                                 <td colspan="4" class="p-8 text-center text-mutedText">Excellent! Local Nigerian banking payout requests queue is empty.</td>
                                            </tr>
                                       <?php endif; ?>
                                  </tbody>
                             </table>
                        </div>
                   </div>
              </div>
         </div>

         <!-- Right navigation links checklist -->
         <div class="space-y-4">
              <h3 class="font-display font-medium text-sm uppercase tracking-wider text-white">System Settings Console</h3>
              <div class="glass-card p-2 divide-y divide-slate-800">
                   <a href="users.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-electricGreen"></i> Manage Punters Database</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="predictors.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="award" class="w-4 h-4 text-vipGold"></i> Manage Expert Predictors</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="matches.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="calendar" class="w-4 h-4 text-sky-450"></i> Fixtures & Live Scores</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="predictions.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="sparkles" class="w-4 h-4 text-electricGreen"></i> Manage Compiled Predictions</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="payments.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="credit-card" class="w-4 h-4 text-emerald-400"></i> Deposit Topups Ledger</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="withdrawals.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="banknote" class="w-4 h-4 text-amber-505"></i> Withdrawal Cashouts</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="ads.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="megaphone" class="w-4 h-4 text-pink-400"></i> Manage Campaigns Ads</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="analytics.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="trending-up" class="w-4 h-4 text-fuchsia-400"></i> Financial Insights & Chart</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
                   <a href="settings.php" class="flex justify-between items-center p-3 text-xs text-slate-350 hover:text-white hover:bg-slate-900/30 rounded-lg no-underline group">
                        <span class="flex items-center gap-2"><i data-lucide="settings" class="w-4 h-4 text-slate-400"></i> Platform Systems Settings</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-mutedText opacity-0 group-hover:opacity-100 transition-opacity"></i>
                   </a>
              </div>
         </div>

    </div>

</main>

<script>
function approvePayout(id) {
    payoutAction(id, 'approved');
}

function rejectPayout(id) {
    payoutAction(id, 'rejected');
}

function payoutAction(id, status) {
    if (!confirm('Are you absolutely certain you wish to mark this cashier withdrawal request as ' + status.toUpperCase() + '?')) {
        return;
    }

    $.ajax({
        url: '<?php echo BASE_URL; ?>api/admin.php',
        type: 'POST',
        data: {
             action: 'payout_action',
             withdrawal_id: id,
             status: status,
             csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
        },
        dataType: 'json',
        success: function(resp) {
             if (resp.status === 'success') {
                  alert('✓ ' + resp.message);
                  window.location.reload();
             } else {
                  alert('⚠️ ' + resp.message);
             }
        },
        error: function(xhr, status, error) {
             console.error(error);
             alert('Network/API Communication error processing clearing authorization.');
        }
    });
}
</script>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
