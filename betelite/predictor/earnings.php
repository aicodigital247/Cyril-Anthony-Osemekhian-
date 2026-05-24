<?php
/**
 * BETELITE - Predictors Expert Sales Commissions Statement
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_predictor();

$userId = $_SESSION['user_id'];

// Get predictor profile details
$sql_pr = "SELECT id FROM be_predictors WHERE user_id = ? LIMIT 1";
$st_pr = mysqli_prepare($conn, $sql_pr);
mysqli_stmt_bind_param($st_pr, "i", $userId);
mysqli_stmt_execute($st_pr);
$pred = mysqli_fetch_assoc(mysqli_stmt_get_result($st_pr));
$predictorId = $pred['id'];

// Get sales earnings from purchases (orders of predictions compiled by this predictor!)
$sql = "SELECT o.*, u.username, p.title, p.price 
        FROM be_orders o 
        JOIN be_predictions p ON o.prediction_id = p.id 
        JOIN be_users u ON o.user_id = u.id 
        WHERE p.predictor_id = ? 
        ORDER BY o.created_at DESC";
$st = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($st, "i", $predictorId);
mysqli_stmt_execute($st);
$res_sales = mysqli_stmt_get_result($st);

$total_sales = 0;
$total_commissions = 0;
$sales_list = [];

while ($row = mysqli_fetch_assoc($res_sales)) {
    $total_sales += (float)$row['amount_paid'];
    // Predictor gets 75% commission split of sales, platform takes 25% administrative fee!
    $comm = (float)$row['amount_paid'] * 0.75;
    $total_commissions += $comm;
    $row['commission'] = $comm;
    $sales_list[] = $row;
}
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-electricGreen font-mono text-xs font-bold tracking-widest uppercase">Expert Commissions ledger</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Sales & Sales Commissions</h1>
         <p class="text-xs text-mutedText">Monitor real-time purchase unlocks on compiled slips and check accrued 75/25 split commission credits.</p>
    </div>

    <!-- Stats -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
         <div class="glass-card p-5 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Gross Sales Value</span>
              <p class="text-3xl font-mono font-black text-white">₦<?php echo number_format($total_sales, 2); ?></p>
              <span class="text-[9px] text-mutedText">Gross funds paid by unlockers</span>
         </div>
         <div class="glass-card p-5 space-y-1 border border-emerald-500/20 bg-emerald-500/5">
              <span class="text-[10px] text-electricGreen font-bold uppercase tracking-wider block">Net Expert Earnings (75% Split)</span>
              <p class="text-3xl font-mono font-black text-electricGreen">₦<?php echo number_format($total_commissions, 2); ?></p>
              <span class="text-[9px] text-electricGreen">Accrued automatically to your dispatcher wallet</span>
         </div>
         <div class="glass-card p-5 space-y-1">
              <span class="text-[10px] text-mutedText font-semibold uppercase tracking-wider block">Platform Commission Kept (25% split)</span>
              <p class="text-3xl font-mono font-black text-slate-400">₦<?php echo number_format(($total_sales * 0.25), 2); ?></p>
              <span class="text-[9px] text-mutedText">System administrative and processing split</span>
         </div>
    </section>

    <!-- Detailed Ledger lists -->
    <div class="space-y-4">
         <h3 class="font-display font-medium text-sm text-white uppercase tracking-wider">Unlocks Receipts Directory</h3>

         <div class="glass-card overflow-hidden">
              <div class="overflow-x-auto">
                   <table class="w-full text-left text-xs text-slate-350">
                        <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold font-display">
                             <tr>
                                  <th class="p-3">Ref Invoice ID</th>
                                  <th class="p-3">Buyer Username</th>
                                  <th class="p-3">Prediction Slip Target</th>
                                  <th class="p-3 font-mono">Unlock Cost</th>
                                  <th class="p-3 font-mono text-right">Earning Commission</th>
                             </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 font-mono">
                             <?php if (!empty($sales_list)): ?>
                                  <?php foreach($sales_list as $sl): ?>
                                       <tr class="hover:bg-slate-900/10">
                                            <td class="p-3 font-bold text-white">INV-SLP-<?php echo $sl['id']; ?></td>
                                            <td class="p-3 font-sans font-semibold text-slate-200">@<?php echo $sl['username']; ?></td>
                                            <td class="p-3 font-sans text-slate-350"><?php echo $sl['title']; ?></td>
                                            <td class="p-3 text-slate-300">₦<?php echo number_format($sl['price'], 2); ?></td>
                                            <td class="p-3 text-right font-black text-electricGreen">
                                                 +₦<?php echo number_format($sl['commission'], 2); ?>
                                            </td>
                                       </tr>
                                  <?php endforeach; ?>
                             <?php else: ?>
                                  <tr class="font-sans">
                                       <td colspan="5" class="p-8 text-center text-mutedText">Nobody has unlocked your premium tickets yet. Keep compiling High Accuracy coupons to attract buyers.</td>
                                  </tr>
                             <?php endif; ?>
                        </tbody>
                   </table>
              </div>
         </div>
    </div>

</main>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
