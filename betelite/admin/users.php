<?php
/**
 * BETELITE - User Management Admin Desk
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Fetch users
$sql_users = "SELECT u.*, w.balance FROM be_users u 
              LEFT JOIN be_wallets w ON u.id = w.user_id 
              ORDER BY u.created_at DESC";
$res_users = mysqli_query($conn, $sql_users);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-dangerRed font-mono text-xs font-bold tracking-widest uppercase">Punter Database Operations</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">BetElite Customer Registry</h1>
         <p class="text-xs text-mutedText">Review registered user profiles, fund mock balances, update access privileges or suspend fraudulent predictors.</p>
    </div>

    <div class="glass-card overflow-hidden">
         <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-300">
                   <thead class="bg-slate-900 border-b border-borderSl text-slate-400 font-semibold">
                        <tr>
                             <th class="p-3">User Profile</th>
                             <th class="p-3">Email Address</th>
                             <th class="p-3">Platform System Role</th>
                             <th class="p-3">Active Wallet holdings</th>
                             <th class="p-3">Compliance Status</th>
                             <th class="p-3 text-center">Execution Controls</th>
                        </tr>
                   </thead>
                   <tbody class="divide-y divide-slate-800/40">
                        <?php if (mysqli_num_rows($res_users) > 0): ?>
                             <?php while ($u = mysqli_fetch_assoc($res_users)): ?>
                                  <tr class="hover:bg-slate-900/10">
                                       <td class="p-3">
                                            <div class="flex items-center gap-2.5">
                                                 <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?php echo urlencode($u['username']); ?>" class="w-8 h-8 rounded-full border border-slate-750" alt="Avatar">
                                                 <div>
                                                      <p class="font-bold text-white">@<?php echo $u['username']; ?></p>
                                                      <p class="text-[10px] text-mutedText"><?php echo $u['full_name'] ?: 'No Full Name'; ?></p>
                                                 </div>
                                            </div>
                                       </td>
                                       <td class="p-3 text-slate-200"><?php echo $u['email']; ?></td>
                                       <td class="p-3">
                                            <select onchange="updateUserField(<?php echo $u['id']; ?>, 'role', this.value)" class="glass-input bg-slate-950 text-xs py-1 px-2.5 rounded border border-slate-800 text-white font-semibold cursor-pointer">
                                                 <option value="user" <?php echo ($u['role'] === 'user') ? 'selected' : ''; ?>>Punter User</option>
                                                 <option value="predictor" <?php echo ($u['role'] === 'predictor') ? 'selected' : ''; ?>>Specialist Predictor</option>
                                                 <option value="admin" <?php echo ($u['role'] === 'admin') ? 'selected' : ''; ?>>Supreme Admin</option>
                                            </select>
                                       </td>
                                       <td class="p-3 font-mono font-bold text-electricGreen">
                                            ₦<?php echo number_format($u['balance'] ?? 0, 2); ?>
                                       </td>
                                       <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[9px] uppercase font-mono font-bold <?php echo ($u['status'] === 'active') ? 'bg-emerald-500/10 text-electricGreen' : 'bg-red-500/10 text-dangerRed'; ?>">
                                                 <?php echo $u['status']; ?>
                                            </span>
                                       </td>
                                       <td class="p-3 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                 <?php if ($u['status'] === 'active'): ?>
                                                      <button onclick="updateUserField(<?php echo $u['id']; ?>, 'status', 'suspended')" class="px-2.5 py-1 bg-red-950 hover:bg-red-900 text-red-400 hover:text-white border border-red-900 text-[10px] font-bold rounded cursor-pointer transition-all">Suspend Punter</button>
                                                 <?php else: ?>
                                                      <button onclick="updateUserField(<?php echo $u['id']; ?>, 'status', 'active')" class="px-2.5 py-1 bg-emerald-950 hover:bg-emerald-900 text-electricGreen border border-emerald-800 text-[10px] font-bold rounded cursor-pointer transition-all">Reactivate Account</button>
                                                 <?php endif; ?>
                                            </div>
                                       </td>
                                  </tr>
                             <?php endwhile; ?>
                        <?php else: ?>
                             <tr>
                                  <td colspan="6" class="p-8 text-center text-mutedText">Customer registry is clean but empty.</td>
                             </tr>
                        <?php endif; ?>
                   </tbody>
              </table>
         </div>
    </div>

</main>

<script>
function updateUserField(userId, field, val) {
    if (!confirm('Are you certain you wish to update this user\'s compliance status?')) {
        return;
    }

    $.ajax({
        url: '<?php echo BASE_URL; ?>api/admin.php',
        type: 'POST',
        data: {
             action: 'manage_user',
             user_id: userId,
             field: field,
             val: val,
             csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
        },
        dataType: 'json',
        success: function(resp) {
             if (resp.status === 'success') {
                  alert('✓ Punter credentials updated!');
                  window.location.reload();
             } else {
                  alert('⚠️ ' + resp.message);
             }
        },
        error: function(xhr, status, error) {
             console.error(error);
             alert('Networking error applying adjustments.');
        }
    });
}
</script>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
