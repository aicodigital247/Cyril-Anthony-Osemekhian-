<?php
/**
 * BETELITE - User Orders and Ticket History Catalog
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

require_auth();

$userId = $_SESSION['user_id'];

// Get order history
$sql = "SELECT o.*, p.title, p.price, pr.display_name, pr.accuracy_rate 
        FROM be_orders o 
        JOIN be_predictions p ON o.prediction_id = p.id 
        JOIN be_predictors pr ON p.predictor_id = pr.id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res_orders = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-6xl mx-auto px-4 py-8 space-y-8 flex-grow">
    <div class="space-y-2">
         <span class="text-electricGreen text-xs font-mono font-bold tracking-widest uppercase">Safe Purchases System</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Purchase Invoice Logs</h1>
         <p class="text-xs text-mutedText">Track historical purchase tickets, verification fees paid, and find instant access buttons to inspect unlocked tips.</p>
    </div>

    <!-- Active Grid -->
    <div class="glass-card overflow-hidden">
         <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-300">
                   <thead class="bg-slate-900/50 text-slate-400 border-b border-borderSl">
                        <tr>
                             <th class="p-3">Order ID / Date</th>
                             <th class="p-3">Prediction Title</th>
                             <th class="p-3">Compiled By</th>
                             <th class="p-3 font-mono">Unlock Cost</th>
                             <th class="p-3 text-right">Option</th>
                        </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-800/40">
                        <?php if (mysqli_num_rows($res_orders) > 0): ?>
                            <?php while ($order = mysqli_fetch_assoc($res_orders)): ?>
                                <tr class="hover:bg-slate-900/10">
                                     <td class="p-3 font-mono">
                                          <p class="font-bold text-white">#BEO-<?php echo $order['id']; ?></p>
                                          <span class="text-[10px] text-mutedText"><?php echo date("M j, Y - H:i", strtotime($order['created_at'])); ?></span>
                                     </td>
                                     <td class="p-3 font-semibold text-slate-100 max-w-xs truncate"><?php echo $order['title']; ?></td>
                                     <td class="p-3">
                                          <p class="font-semibold text-white">@<?php echo $order['display_name']; ?></p>
                                          <span class="text-[9px] text-emerald-400"><?php echo $order['accuracy_rate']; ?>% Accuracy</span>
                                     </td>
                                     <td class="p-3 font-mono text-electricGreen font-bold">₦<?php echo number_format($order['amount_paid'], 2); ?></td>
                                     <td class="p-3 text-right">
                                          <a href="dashboard.php" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-electricGreen hover:text-darkBg text-electricGreen text-[11px] font-bold rounded transition-all no-underline">
                                               <i data-lucide="eye" class="w-3.5 h-3.5"></i> Inspect Slip
                                          </a>
                                     </td>
                                 </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                 <td colspan="5" class="p-12 text-center text-mutedText space-y-3">
                                      <i data-lucide="folder-open" class="w-12 h-12 text-mutedText opacity-20 mx-auto"></i>
                                      <p class="text-sm">You haven't bought any premium sportsbook prediction slips yet.</p>
                                      <p class="text-xs text-mutedText mt-1">Visit our expert tipster marketplace to find winning slips.</p>
                                      <a href="marketplace.php" class="inline-block mt-3 px-5 py-2 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl no-underline">Browse Marketplace</a>
                                 </td>
                            </tr>
                        <?php endif; ?>
                   </tbody>
              </table>
         </div>
    </div>
</main>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
