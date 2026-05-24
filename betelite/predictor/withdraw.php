<?php
/**
 * BETELITE - Predictors Local Bank Withdraw Panel
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_predictor();

$userId = $_SESSION['user_id'];
$balance = get_wallet_balance($conn, $userId);

// Fetch recent cashouts list
$sql = "SELECT * FROM be_withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res_wd = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-vipGold font-mono text-xs font-bold tracking-widest uppercase">Expert Cashier Disbursals</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Withdraw Specialist Earnings</h1>
         <p class="text-xs text-mutedText">Fund transfers directly into your registered local Nigerian commercial bank details.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         
         <!-- Left: Cashout Form -->
         <div class="glass-card p-6 space-y-4">
              <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider">Initiate Bank Cashout</h3>
              
              <div class="p-3 bg-slate-950 rounded-xl border border-slate-850">
                   <p class="text-[10px] text-mutedText uppercase tracking-widest font-bold">Withdrawable Balance</p>
                   <p class="text-2xl font-mono font-black text-electricGreen">₦<?php echo number_format($balance, 2); ?></p>
              </div>

              <!-- Standard form -->
              <form id="form-wd-cashout" class="space-y-4">
                   <?php echo csrf_field(); ?>
                   <input type="hidden" name="action" value="withdraw">
                   
                   <div>
                        <label class="block text-xs font-semibold text-mutedText uppercase mb-1">Cashout Amount (₦)</label>
                        <input type="number" id="wd-amount" name="amount" class="form-control glass-input font-mono" placeholder="Min 1,000 NGN" min="1000" required>
                   </div>

                   <div>
                        <label class="block text-xs font-semibold text-mutedText uppercase mb-1">Destination Commercial Bank</label>
                        <select id="wd-bank" name="bank_name" class="form-control glass-input cursor-pointer font-bold">
                             <option value="Access Bank">Access Bank Plc</option>
                             <option value="Guaranty Trust Bank (GTB)">Guaranty Trust Bank</option>
                             <option value="United Bank for Africa (UBA)">United Bank for Africa (UBA)</option>
                             <option value="Zenith Bank">Zenith Bank Plc</option>
                             <option value="Opay Digital Bank">OPay Digital Services</option>
                             <option value="PalmPay Ltd">PalmPay Mobile Banking</option>
                             <option value="Kuda Microfinance Bank">Kuda Microfinance Bank</option>
                        </select>
                   </div>

                   <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                             <label class="block text-xs font-semibold text-mutedText uppercase mb-1">NUBAN Account Number</label>
                             <input type="text" id="wd-account-number" name="account_number" class="form-control glass-input font-mono" placeholder="10 Digits account NUBAN" maxlength="10" required>
                        </div>
                        <div>
                             <label class="block text-xs font-semibold text-mutedText uppercase mb-1">Account Holder Name</label>
                             <input type="text" id="wd-account-name" name="account_name" class="form-control glass-input" placeholder="Full Registered Name" required>
                        </div>
                   </div>

                   <p class="text-[9px] text-zinc-455">Commissions are processed daily. Transactions are typically verified and completed within 1 to 4 operating hours.</p>

                   <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl border-none shadow cursor-pointer transition-all">
                        Approve Secure Payout File
                   </button>
              </form>
         </div>

         <!-- Right: Recent records table -->
         <div class="lg:col-span-2 space-y-4">
              <h3 class="font-display font-medium text-sm text-white uppercase tracking-wider">Cashout Settlements Log</h3>

              <div class="glass-card overflow-hidden">
                   <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-350">
                             <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold font-display">
                                  <tr>
                                       <th class="p-3">Reference / Date</th>
                                       <th class="p-3">Remittance Bank details</th>
                                       <th class="p-3">Earning Outflow</th>
                                       <th class="p-3 text-right">Settlement Status</th>
                                  </tr>
                             </thead>
                             <tbody class="divide-y divide-slate-800/40">
                                  <?php if (mysqli_num_rows($res_wd) > 0): ?>
                                       <?php while ($w = mysqli_fetch_assoc($res_wd)): ?>
                                            <tr class="hover:bg-slate-900/10">
                                                 <td class="p-3 font-mono">
                                                      <p class="font-bold text-white"><?php echo $w['reference']; ?></p>
                                                      <span class="text-[10px] text-mutedText font-sans"><?php echo format_date_human($w['created_at']); ?></span>
                                                 </td>
                                                 <td class="p-3">
                                                      <p class="font-semibold text-slate-200"><?php echo $w['bank_name']; ?></p>
                                                      <span class="text-[10px] text-mutedText font-mono"><?php echo $w['account_number']; ?> - <?php echo $w['account_name']; ?></span>
                                                 </td>
                                                 <td class="p-3 font-mono font-bold text-dangerRed">
                                                      ₦<?php echo number_format($w['amount'], 2); ?>
                                                 </td>
                                                 <td class="p-3 text-right">
                                                      <span class="px-2.5 py-1 rounded text-[10px] uppercase font-bold <?php echo ($w['status'] === 'approved') ? 'bg-emerald-500/10 text-electricGreen' : (($w['status'] === 'pending') ? 'bg-amber-500/10 text-amber-400 border border-amber-500/10' : 'bg-red-500/10 text-rose-400'); ?>">
                                                           <?php echo $w['status']; ?>
                                                      </span>
                                                 </td>
                                            </tr>
                                       <?php endwhile; ?>
                                  <?php else: ?>
                                       <tr>
                                            <td colspan="4" class="p-8 text-center text-mutedText">Commission ledger shows no previous cashout clearances.</td>
                                       </tr>
                                  <?php endif; ?>
                             </tbody>
                        </table>
                   </div>
              </div>
         </div>

    </div>

</main>

<script>
$(document).ready(function() {
    $('#form-wd-cashout').submit(function(e) {
         e.preventDefault();
         
         let amount = parseFloat($('#wd-amount').val());
         let bank = $('#wd-bank').val();
         let accNum = $('#wd-account-number').val().trim();
         let accName = $('#wd-account-name').val().trim();

         if (amount < 1000) {
              alert('Minimum cashout allowed on BetElite is ₦1,000.00');
              return;
         }

         if (accNum.length !== 10) {
              alert('Account number must consist of exactly 10 NUBAN digits.');
              return;
         }

         $.ajax({
              url: '<?php echo BASE_URL; ?>api/wallet.php',
              type: 'POST',
              data: {
                   action: 'withdraw',
                   amount: amount,
                   bank_name: bank,
                   account_number: accNum,
                   account_name: accName,
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
                   console.error("Cashout request fail: ", error);
                   alert('APIs exception placing cashier withdraw slips.');
              }
         });
    });
});
</script>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
