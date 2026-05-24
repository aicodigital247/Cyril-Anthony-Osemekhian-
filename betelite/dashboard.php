<?php
/**
 * BETELITE - User Tickets Dashboard
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

require_auth();

$userId = $_SESSION['user_id'];

// Get all bought premium slips / predictions or free predictions
$sql_unlocked = "SELECT p.*, pr.display_name, o.amount_paid, o.created_at as purchase_date 
                 FROM be_predictions p 
                 JOIN be_orders o ON p.id = o.prediction_id 
                 JOIN be_predictors pr ON p.predictor_id = pr.id
                 WHERE o.user_id = ? 
                 ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $sql_unlocked);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res_unlocked = mysqli_stmt_get_result($stmt);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <!-- Header banner -->
    <div class="space-y-2">
         <span class="text-electricGreen text-xs font-mono font-bold tracking-widest uppercase">Punter Control Console</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">My Unlocked VIP Bet Slips</h1>
         <p class="text-xs text-mutedText">View full details of active slips, recommended booking stakes, live updates, and direct match results feed.</p>
    </div>

    <!-- Listings Segment -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
         <div class="space-y-6">
              <h3 class="font-display font-semibold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                   <i data-lucide="ticket" class="text-electricGreen w-4 h-4"></i> Unlocked Prediction Cards
              </h3>

              <?php if (mysqli_num_rows($res_unlocked) > 0): ?>
                  <?php while ($slip = mysqli_fetch_assoc($res_unlocked)): ?>
                      <div class="glass-card p-5 space-y-4">
                           <div class="flex justify-between items-start border-b border-slate-800 pb-3">
                                <div>
                                     <h4 class="text-xs font-bold text-white"><?php echo $slip['title']; ?></h4>
                                     <p class="text-[9px] text-mutedText mt-1">Forecast by: @<?php echo $slip['display_name']; ?></p>
                                </div>
                                <span class="px-2.5 py-1 bg-emerald-500/10 text-electricGreen rounded text-[10px] font-bold uppercase tracking-widest font-mono">
                                     UNLOCKED
                                </span>
                           </div>

                           <p class="text-[11px] text-mutedText leading-relaxed"><?php echo $slip['description']; ?></p>

                           <!-- Inner Selected matches in this slip -->
                           <div class="bg-slate-950/50 rounded-xl p-3 border border-slate-850 space-y-2.5 text-xs text-slate-300">
                                <p class="text-[9px] text-mutedText font-bold uppercase tracking-wider mb-1">Recommended Event Bets</p>
                                <?php
                                    $predId = $slip['id'];
                                    $sql_items = "SELECT pi.*, m.home_team, m.away_team, m.league 
                                                  FROM be_prediction_items pi 
                                                  JOIN be_matches m ON pi.match_id = m.id 
                                                  WHERE pi.prediction_id = ?";
                                    $st_it = mysqli_prepare($conn, $sql_items);
                                    mysqli_stmt_bind_param($st_it, "i", $predId);
                                    mysqli_stmt_execute($st_it);
                                    $res_items = mysqli_stmt_get_result($st_it);
                                    
                                    if (mysqli_num_rows($res_items) > 0):
                                        while ($item = mysqli_fetch_assoc($res_items)):
                                ?>
                                    <div class="flex justify-between items-center py-1 bg-slate-900 px-3 rounded-lg">
                                         <div>
                                              <p class="font-semibold text-[11px]"><?php echo $item['home_team']; ?> vs <?php echo $item['away_team']; ?></p>
                                              <span class="text-[9px] text-mutedText uppercase font-mono"><?php echo $item['league']; ?></span>
                                         </div>
                                         <div class="text-right">
                                              <p class="font-mono font-bold text-electricGreen text-[11px]"><?php echo $item['market']; ?> (Odds <?php echo $item['odds']; ?>)</p>
                                              <span class="text-[9px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-400 uppercase font-mono font-bold"><?php echo $item['status']; ?></span>
                                         </div>
                                    </div>
                                <?php 
                                        endwhile;
                                    else:
                                ?>
                                     <p class="text-center text-[10px] text-mutedText">Individual events details pending from Predictor compile.</p>
                                <?php endif; ?>
                           </div>

                           <div class="flex justify-between items-center text-[10px] text-mutedText pt-2">
                                <span>Bought Date: <?php echo date("M j, Y - H:i", strtotime($slip['purchase_date'])); ?></span>
                                <span class="font-mono">Unlocking fee: ₦<?php echo number_format($slip['amount_paid'], 0); ?></span>
                           </div>
                      </div>
                  <?php endwhile; ?>
              <?php else: ?>
                  <div class="glass-card p-12 text-center text-mutedText space-y-3">
                       <i data-lucide="ticket-minus" class="w-12 h-12 opacity-25 mx-auto mb-1 text-mutedText"></i>
                       <p class="text-sm font-semibold">No unlocked premium forecast tickets found.</p>
                       <p class="text-xs text-mutedText">Browse prediction slips in our marketplace area and unlock VIP slips instantly.</p>
                       <a href="marketplace.php" class="inline-block mt-4 px-5 py-2 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl no-underline">Browse Marketplace</a>
                  </div>
              <?php endif; ?>
         </div>

         <!-- Right Column: Account Stats widget, referrals dashboard -->
         <div class="space-y-6">
              <h3 class="font-display font-semibold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                   <i data-lucide="award" class="text-electricGreen w-4 h-4"></i> Profile Accomplishments
              </h3>

              <div class="glass-card p-5 bg-gradient-to-tr from-slate-950 to-slate-900 border border-slate-800 space-y-4 text-center">
                   <div class="w-20 h-20 rounded-full border border-electricGreen flex items-center justify-center mx-auto text-white">
                        <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?php echo urlencode($_SESSION['username']); ?>" class="w-18 h-18 rounded-full border border-slate-700 mx-auto" alt="Avatar">
                   </div>
                   <div class="space-y-1">
                        <h4 class="font-display font-bold text-white text-base">@<?php echo $_SESSION['username']; ?></h4>
                        <p class="text-xs text-mutedText">Standard Nigerian Punter Rank</p>
                   </div>
                   <hr class="border-slate-800">
                   <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="text-center">
                             <p class="text-mutedText uppercase text-[9px] font-bold">Total Unlocked Slips</p>
                             <p class="font-mono text-electricGreen font-bold text-lg"><?php echo mysqli_num_rows($res_unlocked); ?></p>
                        </div>
                        <div class="text-center">
                             <p class="text-mutedText uppercase text-[9px] font-bold">Registered Referrals</p>
                             <p class="font-mono text-electricGreen font-bold text-lg">0</p>
                        </div>
                   </div>
              </div>

              <!-- Referral program section -->
              <div class="glass-card p-5 space-y-3">
                   <h4 class="text-xs font-bold uppercase text-white tracking-wider">Referral Earning Link</h4>
                   <p class="text-[11px] text-mutedText leading-relaxed">Earn a secure, consistent 25% lifetime commission payout on every VIP tip slip purchased by players you invite using your specialized affiliate link!</p>
                   <div class="bg-slate-950 p-2 border border-slate-850 rounded-lg flex justify-between items-center">
                        <span class="font-mono text-[9px] select-all truncate text-slate-300">https://betelite.com/register?ref=<?php echo $_SESSION['username']; ?></span>
                        <button onclick="navigator.clipboard.writeText('https://betelite.com/register?ref=<?php echo $_SESSION['username']; ?>'); alert('Copied referral link successfully!')" class="p-1 px-2.5 text-[10px] font-bold bg-slate-850 hover:bg-slate-800 text-electricGreen rounded transition-all border-none">
                             Copy
                        </button>
                   </div>
              </div>
         </div>
    </div>
</main>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
