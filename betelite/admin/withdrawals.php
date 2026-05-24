<?php
/**
 * BETELITE - Withdrawal / Cashout Approvals Desk
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Handle Operations: Approve or Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process') {
    verify_csrf();
    $wd_id = (int)$_POST['withdrawal_id'];
    $status = sanitize($_POST['status']); // 'approved' or 'rejected'

    mysqli_begin_transaction($conn);
    try {
        $sql_wd = "SELECT * FROM be_withdrawals WHERE id = ? LIMIT 1";
        $st_wd = mysqli_prepare($conn, $sql_wd);
        mysqli_stmt_bind_param($st_wd, "i", $wd_id);
        mysqli_stmt_execute($st_wd);
        $wd_data = mysqli_fetch_assoc(mysqli_stmt_get_result($st_wd));

        if (!$wd_data) {
             throw new Exception("Withdrawal order missing.");
        }

        if ($wd_data['status'] !== 'pending') {
             throw new Exception("Withdrawal order already resolved.");
        }

        // Update withdrawal
        $sql_up = "UPDATE be_withdrawals SET status = ? WHERE id = ?";
        $st_up = mysqli_prepare($conn, $sql_up);
        mysqli_stmt_bind_param($st_up, "si", $status, $wd_id);
        mysqli_stmt_execute($st_up);

        // Update transaction references
        $sql_t = "UPDATE be_transactions SET status = ? WHERE reference = ?";
        $st_t = mysqli_prepare($conn, $sql_t);
        $tx_stat = ($status === 'approved') ? 'completed' : 'failed';
        mysqli_stmt_bind_param($st_t, "ss", $tx_stat, $wd_data['reference']);
        mysqli_stmt_execute($st_t);

        // Refund wallet balance if rejected
        if ($status === 'rejected') {
             $sql_ref = "UPDATE be_wallets SET balance = balance + ? WHERE user_id = ?";
             $st_ref = mysqli_prepare($conn, $sql_ref);
             mysqli_stmt_bind_param($st_ref, "di", $wd_data['amount'], $wd_data['user_id']);
             mysqli_stmt_execute($st_ref);
        }

        mysqli_commit($conn);
        $msg_success = "Withdrawal request finalized as: " . strtoupper($status) . " successfully!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg_error = "Failed to update record: " . $e->getMessage();
    }
}

// Fetch withdrawals
$sql = "SELECT w.*, u.username FROM be_withdrawals w 
        JOIN be_users u ON w.user_id = u.id 
        ORDER BY w.created_at DESC";
$res_wd = mysqli_query($conn, $sql);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-amber-500 font-mono text-xs font-bold tracking-widest uppercase">Safe Cashier Disbursals</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Bank Withdrawals Controller</h1>
         <p class="text-xs text-mutedText">Process pending expert cashouts and review historical local bank remittance indices.</p>
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
                             <th class="p-3">Reference / Creator</th>
                             <th class="p-3">Remittance Bank Coordinates</th>
                             <th class="p-3">Creation Date</th>
                             <th class="p-3 text-right">Requested Outflow</th>
                             <th class="p-3">Clearance Status</th>
                             <th class="p-3 text-center">Process Remittance</th>
                        </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-800/40">
                        <?php if (mysqli_num_rows($res_wd) > 0): ?>
                             <?php while ($wd = mysqli_fetch_assoc($res_wd)): ?>
                                  <tr class="hover:bg-slate-900/10">
                                       <td class="p-3 font-mono">
                                            <p class="font-bold text-white"><?php echo $wd['reference']; ?></p>
                                            <span class="text-[10px] text-mutedText font-sans">User: @<?php echo $wd['username']; ?></span>
                                       </td>
                                       <td class="p-3">
                                            <p class="font-semibold text-slate-200"><?php echo $wd['bank_name']; ?></p>
                                            <span class="text-[10px] text-mutedText font-mono"><?php echo $wd['account_number']; ?> - <?php echo $wd['account_name']; ?></span>
                                       </td>
                                       <td class="p-3 text-mutedText"><?php echo format_date_human($wd['created_at']); ?></td>
                                       <td class="p-3 text-right font-mono font-black text-dangerRed text-sm">
                                            ₦<?php echo number_format($wd['amount'], 2); ?>
                                       </td>
                                       <td class="p-3">
                                            <span class="px-2.5 py-1 rounded text-[10px] uppercase font-bold <?php echo ($wd['status'] === 'approved') ? 'bg-emerald-500/10 text-electricGreen' : (($wd['status'] === 'pending') ? 'bg-amber-500/10 text-amber-400' : 'bg-red-500/10 text-dangerRed'); ?>">
                                                 <?php echo $wd['status']; ?>
                                            </span>
                                       </td>
                                       <td class="p-3 text-center">
                                            <?php if ($wd['status'] === 'pending'): ?>
                                                 <form action="withdrawals.php" method="POST" class="flex items-center justify-center gap-1.5">
                                                      <?php echo csrf_field(); ?>
                                                      <input type="hidden" name="action" value="process">
                                                      <input type="hidden" name="withdrawal_id" value="<?php echo $wd['id']; ?>">
                                                      
                                                      <button type="submit" name="status" value="approved" class="px-2.5 py-1 bg-electricGreen hover:bg-greenHover text-darkBg text-[10px] font-bold rounded cursor-pointer border-none shadow transition-all">Approve Pay</button>
                                                      <button type="submit" name="status" value="rejected" class="px-2.5 py-1 bg-red-955 border border-red-900 hover:bg-red-900 text-red-400 hover:text-white text-[10px] font-bold rounded cursor-pointer transition-all">Reject File</button>
                                                 </form>
                                            <?php else: ?>
                                                 <span class="text-mutedText text-[10px] italic">Fully final receipt</span>
                                            <?php endif; ?>
                                       </td>
                                  </tr>
                             <?php endwhile; ?>
                        <?php else: ?>
                             <tr>
                                  <td colspan="6" class="p-8 text-center text-mutedText">Excellent! Cashout ledger is empty.</td>
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
