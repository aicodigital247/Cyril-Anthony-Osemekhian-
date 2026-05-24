<?php
/**
 * BETELITE - Premium Predictions Evaluation Desk
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Handle Prediction Status Resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_prediction') {
    verify_csrf();
    $pred_id = (int)$_POST['prediction_id'];
    $status = sanitize($_POST['status']); // 'Won', 'Lost', 'Cancelled'

    mysqli_begin_transaction($conn);
    try {
        // Update Prediction
        $sql = "UPDATE be_predictions SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $pred_id);
        mysqli_stmt_execute($stmt);

        // Fetch Predictor ID to recalibrate accuracy rating
        $sql_f = "SELECT predictor_id FROM be_predictions WHERE id = ? LIMIT 1";
        $stmt_f = mysqli_prepare($conn, $sql_f);
        mysqli_stmt_bind_param($stmt_f, "i", $pred_id);
        mysqli_stmt_execute($stmt_f);
        $p_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_f));
        $predictorId = $p_row['predictor_id'];

        // Recalibrate accuracy: count Won predictions out of total completed ones
        $sql_won = "SELECT COUNT(*) as cnt FROM be_predictions WHERE predictor_id = ? AND status = 'Won'";
        $st_won = mysqli_prepare($conn, $sql_won);
        mysqli_stmt_bind_param($st_won, "i", $predictorId);
        mysqli_stmt_execute($st_won);
        $won_cnt = mysqli_fetch_assoc(mysqli_stmt_get_result($st_won))['cnt'];

        $sql_tot = "SELECT COUNT(*) as cnt FROM be_predictions WHERE predictor_id = ? AND status IN ('Won', 'Lost')";
        $st_tot = mysqli_prepare($conn, $sql_tot);
        mysqli_stmt_bind_param($st_tot, "i", $predictorId);
        mysqli_stmt_execute($st_tot);
        $tot_cnt = mysqli_fetch_assoc(mysqli_stmt_get_result($st_tot))['cnt'];

        $new_rate = ($tot_cnt > 0) ? round(($won_cnt / $tot_cnt) * 100) : 80;

        $sql_up_rate = "UPDATE be_predictors SET accuracy_rate = ? WHERE id = ?";
        $st_up = mysqli_prepare($conn, $sql_up_rate);
        mysqli_stmt_bind_param($st_up, "ii", $new_rate, $predictorId);
        mysqli_stmt_execute($st_up);

        mysqli_commit($conn);
        $msg_success = "Betting slip resolved as " . strtoupper($status) . ". Predictor accuracy refreshed to $new_rate%!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg_error = "Failed to resolve predicting slip status: " . $e->getMessage();
    }
}

// Fetch all predictions with expert displays
$sql = "SELECT p.*, pr.display_name, pr.badge FROM be_predictions p 
        JOIN be_predictors pr ON p.predictor_id = pr.id 
        ORDER BY p.created_at DESC";
$res_predictions = mysqli_query($conn, $sql);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-dangerRed font-mono text-xs font-bold tracking-widest uppercase">Expert Slips Clearance Deck</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Predictions & Tickets Regulation</h1>
         <p class="text-xs text-mutedText">Verify submitted expert slips, view selections, evaluate outcomes to credit predictors accuracy tiers automatically.</p>
    </div>

    <?php if (isset($msg_success)): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>
    <?php if (isset($msg_error)): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-dangerRed rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <div class="glass-card overflow-hidden">
         <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-350">
                   <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold">
                        <tr>
                             <th class="p-3">Prediction Slip Details</th>
                             <th class="p-3">Expert Compiler</th>
                             <th class="p-3">Purchase Valuation</th>
                             <th class="p-3">Confidence Ratio</th>
                             <th class="p-3">Current Status</th>
                             <th class="p-3 text-center">Outcome Clearance Action</th>
                        </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-800/40">
                        <?php if (mysqli_num_rows($res_predictions) > 0): ?>
                             <?php while ($p = mysqli_fetch_assoc($res_predictions)): ?>
                                  <tr class="hover:bg-slate-900/10">
                                       <td class="p-3">
                                            <p class="font-bold text-white text-sm"><?php echo $p['title']; ?></p>
                                            <p class="text-[10px] text-mutedText mt-0.5"><?php echo $p['description']; ?></p>
                                       </td>
                                       <td class="p-3 text-vipGold font-semibold">@<?php echo $p['display_name']; ?></td>
                                       <td class="p-3 font-mono font-bold text-white">
                                            <?php echo ($p['price'] == 0.00) ? '<span class="text-electricGreen">FREE</span>' : '₦' . number_format($p['price'], 2); ?>
                                       </td>
                                       <td class="p-3 font-mono font-bold text-slate-200"><?php echo $p['confidence']; ?>%</td>
                                       <td class="p-3">
                                            <span class="px-2.5 py-1 rounded text-[10px] uppercase font-bold <?php echo ($p['status'] === 'Won') ? 'bg-emerald-500/10 text-electricGreen border border-emerald-500/20' : (($p['status'] === 'Lost') ? 'bg-red-500/10 text-rose-400 border border-red-500/20' : 'bg-slate-800 text-slate-450'); ?>">
                                                 <?php echo $p['status']; ?>
                                            </span>
                                       </td>
                                       <td class="p-3 text-center">
                                            <?php if ($p['status'] === 'Active'): ?>
                                                 <form action="predictions.php" method="POST" class="flex items-center justify-center gap-1.5">
                                                      <?php echo csrf_field(); ?>
                                                      <input type="hidden" name="action" value="resolve_prediction">
                                                      <input type="hidden" name="prediction_id" value="<?php echo $p['id']; ?>">
                                                      
                                                      <button type="submit" name="status" value="Won" class="px-2 py-1 bg-electricGreen hover:bg-greenHover text-darkBg text-[10px] font-bold rounded cursor-pointer border-none shadow">Won</button>
                                                      <button type="submit" name="status" value="Lost" class="px-2 py-1 bg-red-955 border border-red-900 hover:bg-red-900 text-red-400 hover:text-white text-[10px] font-bold rounded cursor-pointer transition-all">Lost</button>
                                                      <button type="submit" name="status" value="Cancelled" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-white text-[10px] font-bold rounded cursor-pointer border-none">Cancel</button>
                                                 </form>
                                            <?php else: ?>
                                                 <span class="text-mutedText italic text-[10px]">Settled & final</span>
                                            <?php endif; ?>
                                       </td>
                                  </tr>
                             <?php endwhile; ?>
                        <?php else: ?>
                             <tr>
                                  <td colspan="6" class="p-8 text-center text-mutedText">Prediction database has no compiled tickets under review.</td>
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
