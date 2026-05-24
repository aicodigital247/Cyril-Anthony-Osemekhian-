<?php
/**
 * BETELITE - Predictors Compiled Slip Archives
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

// Get historical slips compiled
$sql = "SELECT * FROM be_predictions WHERE predictor_id = ? ORDER BY created_at DESC";
$st_sl = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($st_sl, "i", $predictorId);
mysqli_stmt_execute($st_sl);
$res_slips = mysqli_stmt_get_result($st_sl);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-vipGold font-mono text-xs font-bold tracking-widest uppercase">Expert Forecasting Archives</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">My Compiled Slips Workbook</h1>
         <p class="text-xs text-mutedText">Review overall accuracy catalogs, monitor active coupons, and see total accumulated slip sales.</p>
    </div>

    <!-- Active Grid lists -->
    <div class="glass-card overflow-hidden">
         <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-350">
                   <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold font-display">
                        <tr>
                             <th class="p-3">Prediction Title</th>
                             <th class="p-3">Calibration Description</th>
                             <th class="p-3">Price Fee</th>
                             <th class="p-3">Confidence Ratio</th>
                             <th class="p-3">Creation Date</th>
                             <th class="p-3 text-right">Evaluation Status</th>
                        </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-800/40">
                        <?php if (mysqli_num_rows($res_slips) > 0): ?>
                             <?php while ($s = mysqli_fetch_assoc($res_slips)): ?>
                                  <tr class="hover:bg-slate-900/10">
                                       <td class="p-3 font-bold text-white"><?php echo $s['title']; ?></td>
                                       <td class="p-3 max-w-sm truncate text-mutedText"><?php echo $s['description'] ?: 'No analysis written.'; ?></td>
                                       <td class="p-3 font-mono font-bold text-electricGreen">
                                            <?php echo ($s['price'] == 0.00) ? 'FREE' : '₦' . number_format($s['price'], 2); ?>
                                       </td>
                                       <td class="p-3 font-mono text-slate-200"><?php echo $s['confidence']; ?>% accuracy match</td>
                                       <td class="p-3 text-mutedText"><?php echo format_date_human($s['created_at']); ?></td>
                                       <td class="p-3 text-right">
                                            <span class="px-2.5 py-1 rounded text-[10px] uppercase font-bold <?php echo ($s['status'] === 'Won') ? 'bg-emerald-500/10 text-electricGreen' : (($s['status'] === 'Lost') ? 'bg-red-500/10 text-rose-400' : 'bg-slate-800 text-slate-300'); ?>">
                                                 <?php echo $s['status']; ?>
                                            </span>
                                       </td>
                                  </tr>
                             <?php endwhile; ?>
                        <?php else: ?>
                             <tr>
                                  <td colspan="6" class="p-8 text-center text-mutedText">You have not compiled any prediction slips yet.</td>
                             </tr>
                        <?php endif; ?>
                   </tbody>
              </table>
         </div>
    </div>

</main>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
