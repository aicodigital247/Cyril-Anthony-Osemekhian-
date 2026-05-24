<?php
/**
 * BETELITE - User Wallet & Transactions Panel
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

require_auth();

$userId = $_SESSION['user_id'];
$balance = get_wallet_balance($conn, $userId);

// Fetch transaction history
$sql_txns = "SELECT t.*, w.currency FROM be_transactions t 
             JOIN be_wallets w ON t.wallet_id = w.id 
             WHERE w.user_id = ? 
             ORDER BY t.created_at DESC";
$stmt = mysqli_prepare($conn, $sql_txns);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res_txns = mysqli_stmt_get_result($stmt);

// Handle Mock Deposit action
$msg_success = ''; $msg_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mock_deposit') {
    verify_csrf();
    $amount = (float) $_POST['amount'];
    if ($amount <= 0) {
        $msg_error = "Please post a positive deposit denomination.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // Update balance
            $sql_up = "UPDATE be_wallets SET balance = balance + ? WHERE user_id = ?";
            $st_up = mysqli_prepare($conn, $sql_up);
            mysqli_stmt_bind_param($st_up, "di", $amount, $userId);
            mysqli_stmt_execute($st_up);

            // Fetch current wallet ID
            $sql_w = "SELECT id FROM be_wallets WHERE user_id = ?";
            $st_w = mysqli_prepare($conn, $sql_w);
            mysqli_stmt_bind_param($st_w, "i", $userId);
            mysqli_stmt_execute($st_w);
            $w_row = mysqli_fetch_assoc(mysqli_stmt_get_result($st_w));
            $walletId = $w_row['id'];

            // Save transaction record
            $ref = "TXN-DEP-" . bin2hex(random_bytes(5));
            log_transaction($conn, $walletId, $amount, 'deposit', $ref, 'Paystack', 'Deposited credit via simulated payment screen.');

            mysqli_commit($conn);
            $msg_success = "₦" . number_format($amount, 2) . " successfully credited via simulated Paystack Gateway!";
            // Refresh view balance
            $balance = get_wallet_balance($conn, $userId);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $msg_error = "Billing failure. Please try again.";
        }
    }
}
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-2">
         <span class="text-electricGreen text-xs font-mono font-bold tracking-widest uppercase">Fintech Integration Suite</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">My BETELITE Secure Wallet</h1>
         <p class="text-xs text-mutedText max-w-xl">Fund account using debit cards, check recent receipts, or initiate immediate earnings withdraws directly to your local Nigerian commercial bank details.</p>
    </div>

    <?php if ($msg_success): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>
    <?php if ($msg_error): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-red-500 rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         <!-- Left card: Current valuation slider & quick CTA buttons -->
         <div class="glass-card p-6 bg-gradient-to-br from-indigo-950/20 to-slate-900 border border-slate-800 space-y-6 flex flex-col justify-between">
              <div class="space-y-2">
                   <p class="text-[10px] text-mutedText uppercase tracking-widest font-bold">Total Valued Holdings</p>
                   <h2 class="font-mono font-bold text-3xl md:text-4xl text-electricGreen">
                       <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($balance, 2); ?>
                   </h2>
                   <p class="text-[9px] text-mutedText">Current currency configured standard: NGN Naira</p>
              </div>

              <div class="space-y-3">
                   <!-- Manual funding trigger -->
                   <button type="button" data-bs-toggle="modal" data-bs-target="#quickMockDepositModal" class="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl text-center transition-all flex items-center justify-center gap-2 border-none">
                        <i data-lucide="plus" class="w-4 h-4"></i> Fund Account (Simulate)
                   </button>
                   
                   <button type="button" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl text-center transition-all flex items-center justify-center gap-2 border-none">
                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i> Withdraw Earnings
                   </button>
              </div>
         </div>

         <!-- Right cards lists: Transaction summary receipt table -->
         <div class="lg:col-span-2 space-y-4">
              <h3 class="font-display font-semibold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                   <i data-lucide="history" class="text-electricGreen w-4 h-4"></i> Billing & Receipt History
              </h3>

              <div class="glass-card overflow-hidden">
                   <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                             <thead class="bg-slate-900/50 text-slate-400 border-b border-borderSl">
                                  <tr>
                                       <th class="p-3">Reference / Date</th>
                                       <th class="p-3">Type</th>
                                       <th class="p-3">Payment Method</th>
                                       <th class="p-3 text-right">Amount</th>
                                  </tr>
                             </thead>
                             <tbody class="divide-y divide-slate-800/40">
                                  <?php if (mysqli_num_rows($res_txns) > 0): ?>
                                      <?php while ($txn = mysqli_fetch_assoc($res_txns)): ?>
                                          <tr class="hover:bg-slate-900/10">
                                               <td class="p-3">
                                                    <p class="font-mono font-bold text-white"><?php echo $txn['reference']; ?></p>
                                                    <span class="text-[10px] text-mutedText"><?php echo format_date_human($txn['created_at']); ?></span>
                                               </td>
                                               <td class="p-3 capitalize text-teal-400">
                                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-emerald-500/10 <?php echo ($txn['type'] === 'withdrawal') ? 'bg-red-500/10 text-rose-400' : ''; ?>">
                                                        <?php echo $txn['type']; ?>
                                                    </span>
                                               </td>
                                               <td class="p-3 text-mutedText font-mono"><?php echo $txn['payment_method']; ?></td>
                                               <td class="p-3 text-right font-mono font-bold <?php echo ($txn['type'] === 'withdrawal' || $txn['type'] === 'purchase') ? 'text-dangerRed' : 'text-electricGreen'; ?>">
                                                    <?php echo ($txn['type'] === 'withdrawal' || $txn['type'] === 'purchase') ? '-' : '+'; ?>₦<?php echo number_format($txn['amount'], 2); ?>
                                               </td>
                                          </tr>
                                      <?php endwhile; ?>
                                  <?php else: ?>
                                      <tr>
                                           <td colspan="4" class="p-8 text-center text-mutedText">No wallet activity logged. Fund your balance using the deposit panel first.</td>
                                      </tr>
                                  <?php endif; ?>
                             </tbody>
                        </table>
                   </div>
              </div>
         </div>
    </div>
</main>

<!-- In-page Manual Mock Deposit Modal specifically because we cannot open paystack live widgets in Sandbox iframes! -->
<div class="modal fade" id="quickMockDepositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white rounded-2xl">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title font-display font-semibold text-sm uppercase flex items-center gap-2">
                    <i data-lucide="help-circle" class="text-electricGreen"></i> Simulated Paystack Credit Card Deposit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="wallet.php" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="mock_deposit">
                <div class="modal-body space-y-4">
                     <div>
                          <label class="block text-xs font-semibold uppercase text-mutedText mb-1">Deposit Amount (₦)</label>
                          <input type="number" name="amount" class="form-control glass-input" value="5000" min="500" required>
                     </div>
                     <p class="text-[10px] text-mutedText">Because you are testing the platform inside Google AI Studio, this triggers an immediate credit update with artificial values so you don't need real credit cards.</p>
                </div>
                <div class="modal-footer border-slate-800">
                     <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg font-bold rounded-xl border-none">
                          Approve Simulated Deposit
                     </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
