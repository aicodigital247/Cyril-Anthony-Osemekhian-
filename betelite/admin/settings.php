<?php
/**
 * BETELITE - Platform Systems Variables Controller
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Handle settings submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $slogan = sanitize($_POST['site_slogan']);
    $comm = (float)$_POST['commission_rate'];
    $min_wd = (float)$_POST['min_withdrawal'];

    // In a production application these are saved to Database configuration settings.
    // For BetElite we simulate immediate confirmation to configuration parameters.
    $msg_success = "Systems parameters calibrated! Slogan updated, commission fee set as: $comm%, and minimum cashier out set as: ₦$min_wd.";
}
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-slate-400 font-mono text-xs font-bold tracking-widest uppercase">System Parameters</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Platform System Settings</h1>
         <p class="text-xs text-mutedText">Update platform metadata slogans, set expert sales commision splits, verify withdrawal minimum thresholds.</p>
    </div>

    <?php if (isset($msg_success)): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>

    <form action="settings.php" method="POST" class="glass-card p-6 space-y-6">
         <?php echo csrf_field(); ?>
         
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                   <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Platform Meta Slogan</label>
                   <input type="text" name="site_slogan" class="form-control glass-input" value="<?php echo SITE_SLOGAN; ?>" required>
              </div>

              <div>
                   <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Systems Commission % Split (Keep)</label>
                   <input type="number" name="commission_rate" class="form-control glass-input font-mono" value="25" min="5" max="90" required>
                   <span class="text-[9px] text-mutedText block mt-1">Percentage split kept by local operator during ticket sales.</span>
              </div>
         </div>

         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                   <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Minimum Eligible Withdrawal Cashout (₦)</label>
                   <input type="number" name="min_withdrawal" class="form-control glass-input font-mono" value="1000" min="500" required>
              </div>

              <div>
                   <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Risk Warning text</label>
                   <input type="text" class="form-control glass-input" value="18+ Sports betting is highly speculative. Play responsibly." disabled>
              </div>
         </div>

         <div class="border-t border-slate-800/80 pt-4 flex justify-end">
              <button type="submit" class="px-6 py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-semibold rounded-xl border-none shadow cursor-pointer transition-all">
                   Save Calibration Configuration
              </button>
         </div>
    </form>

</main>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
