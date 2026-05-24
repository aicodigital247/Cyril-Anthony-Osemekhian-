<?php
/**
 * BETELITE - Deposit Ledger Audits Desk
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Fetch deposit records
$sql = "SELECT d.*, u.username FROM be_deposits d 
        JOIN be_users u ON d.user_id = u.id 
        ORDER BY d.created_at DESC";
$res_dep = mysqli_query($conn, $sql);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-emerald-400 font-mono text-xs font-bold tracking-widest uppercase">System Inflow Ledger</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Deposit Transactions Directory</h1>
         <p class="text-xs text-mutedText">Review all funded deposit orders completed securely via simulated payment terminals.</p>
    </div>

    <div class="glass-card overflow-hidden">
         <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-350">
                   <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold">
                        <tr>
                             <th class="p-3">Reference ID</th>
                             <th class="p-3">Punter Username</th>
                             <th class="p-3">Selected Gateway</th>
                             <th class="p-3">Timestamp Date</th>
                             <th class="p-3">Financial Status</th>
                             <th class="p-3 text-right">Funded Amount</th>
                        </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-800/40 font-mono">
                        <?php if (mysqli_num_rows($res_dep) > 0): ?>
                             <?php while ($d = mysqli_fetch_assoc($res_dep)): ?>
                                  <tr class="hover:bg-slate-900/10">
                                       <td class="p-3 font-bold text-white"><?php echo $d['reference']; ?></td>
                                       <td class="p-3 text-slate-200 font-sans font-bold">@<?php echo $d['username']; ?></td>
                                       <td class="p-3 text-mutedText"><?php echo $d['payment_method']; ?></td>
                                       <td class="p-3 text-mutedText font-sans"><?php echo format_date_human($d['created_at']); ?></td>
                                       <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-emerald-500/10 text-electricGreen border border-emerald-500/20">
                                                 <?php echo $d['status']; ?>
                                            </span>
                                       </td>
                                       <td class="p-3 text-right font-black text-electricGreen">
                                            ₦<?php echo number_format($d['amount'], 2); ?>
                                       </td>
                                  </tr>
                             <?php endwhile; ?>
                        <?php else: ?>
                             <tr>
                                  <td colspan="6" class="p-8 text-center text-mutedText font-sans">Payments system ledger has no transaction indices yet.</td>
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
