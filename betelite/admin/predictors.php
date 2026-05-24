<?php
/**
 * BETELITE - Predictors Verification Desk
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Handle Accuracy manual adjustments for administrative validation checks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_accuracy'])) {
    verify_csrf();
    $p_id = (int)$_POST['predictor_id'];
    $acc_rate = (int)$_POST['accuracy_rate'];
    $badge = sanitize($_POST['badge']);

    $sql = "UPDATE be_predictors SET accuracy_rate = ?, badge = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $acc_rate, $badge, $p_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg_success = "Accuracy rating adjusted to $acc_rate% safely.";
    } else {
        $msg_error = "Failed to update record.";
    }
}

// Fetch experts
$sql = "SELECT p.*, u.username, u.email FROM be_predictors p 
        JOIN be_users u ON p.user_id = u.id 
        ORDER BY p.accuracy_rate DESC";
$res_predictors = mysqli_query($conn, $sql);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-vipGold font-mono text-xs font-bold tracking-widest uppercase">Verified Tipster Specialists</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Expert Predictions Directorate</h1>
         <p class="text-xs text-mutedText">Regulate listed expert accuracy telemetry metrics, update badges (Gold, Diamond, Elite), and check total premium slip commissions.</p>
    </div>

    <?php if (isset($msg_success)): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>
    <?php if (isset($msg_error)): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-rose-450 rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6">
         <div class="glass-card overflow-hidden">
              <div class="overflow-x-auto">
                   <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold">
                             <tr>
                                  <th class="p-3">Professional Display Profile</th>
                                  <th class="p-3">Account Reference</th>
                                  <th class="p-3">Total Predictions</th>
                                  <th class="p-3">Subscribers Fee</th>
                                  <th class="p-3">Verification Badge / Tier</th>
                                  <th class="p-3">Current Accuracy Rate</th>
                                  <th class="p-3 text-center">Adjustment Registry</th>
                             </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40">
                             <?php if (mysqli_num_rows($res_predictors) > 0): ?>
                                  <?php while ($p = mysqli_fetch_assoc($res_predictors)): ?>
                                       <tr class="hover:bg-slate-900/10">
                                            <td class="p-3 font-semibold text-white">
                                                 <p class="text-sm font-bold">@<?php echo $p['username']; ?> (<?php echo $p['display_name']; ?>)</p>
                                                 <p class="text-[10px] text-mutedText font-sans max-w-sm truncate mt-0.5"><?php echo $p['bio']; ?></p>
                                            </td>
                                            <td class="p-3 text-mutedText font-mono uppercase text-[10px]">ID: PRD-<?php echo $p['id']; ?></td>
                                            <td class="p-3 font-mono text-slate-200"><?php echo $p['total_predictions']; ?> Slips</td>
                                            <td class="p-3 font-mono font-semibold text-white">₦<?php echo number_format($p['subscription_price'], 2); ?>/mo</td>
                                            <td class="p-3">
                                                 <span class="px-2.5 py-1 rounded text-xs uppercase font-bold bg-amber-500/10 text-vipGold flex items-center gap-1.5 w-fit border border-amber-500/20">
                                                      <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                                                      <?php echo $p['badge'] ?: 'Specialist'; ?>
                                                 </span>
                                            </td>
                                            <td class="p-3 font-mono font-black text-electricGreen">
                                                 <?php echo $p['accuracy_rate']; ?>% Win Rate
                                            </td>
                                            <td class="p-3 text-center">
                                                 <form action="predictors.php" method="POST" class="flex items-center justify-center gap-2">
                                                      <?php echo csrf_field(); ?>
                                                      <input type="hidden" name="set_accuracy" value="1">
                                                      <input type="hidden" name="predictor_id" value="<?php echo $p['id']; ?>">
                                                      <input type="number" name="accuracy_rate" class="form-control bg-slate-950 border border-slate-800 rounded font-mono p-1 text-xs w-16 text-center text-white" value="<?php echo $p['accuracy_rate']; ?>" min="0" max="100">
                                                      <select name="badge" class="bg-slate-950 border border-slate-800 text-xs py-1 px-1.5 rounded text-white cursor-pointer font-semibold">
                                                           <option value="Elite" <?php echo ($p['badge'] === 'Elite') ? 'selected' : ''; ?>>Elite</option>
                                                           <option value="Gold Expert" <?php echo ($p['badge'] === 'Gold Expert') ? 'selected' : ''; ?>>Gold Expert</option>
                                                           <option value="Supremacy" <?php echo ($p['badge'] === 'Supremacy') ? 'selected' : ''; ?>>Supremacy</option>
                                                           <option value="Accurate" <?php echo ($p['badge'] === 'Accurate') ? 'selected' : ''; ?>>Accurate</option>
                                                      </select>
                                                      <button type="submit" class="p-1 px-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-[10px] font-bold rounded cursor-pointer border-none shadow">Submit</button>
                                                 </form>
                                            </td>
                                       </tr>
                                  <?php endwhile; ?>
                             <?php else: ?>
                                  <tr>
                                       <td colspan="7" class="p-8 text-center text-mutedText">No expert predictors found. Promote punters to experts using the punter customer page.</td>
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
