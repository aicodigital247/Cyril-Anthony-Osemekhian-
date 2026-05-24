<?php
/**
 * BETELITE - Ad Campaigns Coordination Panel
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Handle New Campaign Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_ad') {
    verify_csrf();
    $title = sanitize($_POST['title']);
    $banner = sanitize($_POST['banner_url']);
    $target = sanitize($_POST['target_url']);
    $pos = sanitize($_POST['position']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    $sql = "INSERT INTO be_ads (title, banner_url, target_url, position, start_date, end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $title, $banner, $target, $pos, $start, $end);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg_success = "Banners campaign launched successfully!";
    } else {
        $msg_error = "Could not publish campaign.";
    }
}

// Fetch campaigns
$res_ads = mysqli_query($conn, "SELECT * FROM be_ads ORDER BY created_at DESC");
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
         <div class="space-y-1">
              <span class="text-pink-400 font-mono text-xs font-bold tracking-widest uppercase">Promotional Campaign Tower</span>
              <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Banner & Conversion Campaigns</h1>
              <p class="text-xs text-mutedText">Launch conversion-focused banners and place custom call-to-actions directly into user dashboards.</p>
         </div>
         <button type="button" data-bs-toggle="modal" data-bs-target="#createAdModal" class="px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white text-xs font-bold rounded-xl transition-all border-none flex items-center gap-1.5 cursor-pointer shadow">
              <i data-lucide="megaphone" class="w-4 h-4"></i> Deploy Banners Campaign
         </button>
    </div>

    <?php if (isset($msg_success)): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>
    <?php if (isset($msg_error)): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-rose-450 rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         <div class="lg:col-span-2 space-y-4">
              <h3 class="font-display font-medium text-sm text-white uppercase tracking-wider">Active & Scheduled Banners</h3>
              
              <div class="space-y-4">
                   <?php if (mysqli_num_rows($res_ads) > 0): ?>
                        <?php while ($ad = mysqli_fetch_assoc($res_ads)): ?>
                             <div class="glass-card p-4 space-y-3 border border-pink-500/10 bg-pink-500/5">
                                  <div class="flex justify-between text-xs font-bold">
                                       <span class="text-pink-400 uppercase tracking-widest">POSITION: <?php echo $ad['position']; ?></span>
                                       <span class="px-2 py-0.5 rounded text-[10px] bg-slate-800 text-slate-300 capitalize"><?php echo $ad['status']; ?></span>
                                  </div>
                                  <h4 class="text-sm font-bold text-white"><?php echo $ad['title']; ?></h4>
                                  <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-850 truncate font-mono text-[10px] text-mutedText">
                                       Link: <a href="<?php echo $ad['target_url']; ?>" target="_blank" class="text-electricGreen underline"><?php echo $ad['target_url']; ?></a>
                                  </div>
                                  <div class="text-[10px] text-mutedText">
                                       Campaign Validity: <?php echo $ad['start_date']; ?> to <?php echo $ad['end_date']; ?>
                                  </div>
                             </div>
                        <?php endwhile; ?>
                   <?php else: ?>
                        <div class="glass-card p-12 text-center text-mutedText">No promotions placed inside table. Fallback promo loaded.</div>
                   <?php endif; ?>
              </div>
         </div>

         <!-- Context notes -->
         <div class="space-y-4 text-xs text-mutedText leading-relaxed">
              <h3 class="font-display font-medium text-sm text-white uppercase tracking-wider">Campaign Placement FAQ</h3>
              <div class="glass-card p-5 space-y-3">
                   <p class="font-bold text-white">How position targets display:</p>
                   <p><strong>header</strong>: Displays prominent slides directly above marketplace listings inside Marketplace.</p>
                   <p><strong>dashboard</strong>: Shows high conversion custom widgets inside user slips dashboards.</p>
              </div>
         </div>
    </div>

</main>

<!-- Deploy Ad Modal -->
<div class="modal fade" id="createAdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content bg-slate-900 border border-slate-800 text-white rounded-2xl">
              <div class="modal-header border-slate-800">
                   <h5 class="modal-title font-display font-bold text-sm uppercase flex items-center gap-2">
                        <i data-lucide="megaphone" class="text-pink-400"></i> Deploy Conversion Campaign
                   </h5>
                   <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="ads.php" method="POST">
                   <?php echo csrf_field(); ?>
                   <input type="hidden" name="action" value="create_ad">
                   <div class="modal-body space-y-4">
                        <div>
                             <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Campaign Headline Title</label>
                             <input type="text" name="title" class="form-control glass-input" placeholder="Join VIP Expert Telegram Chat Room!" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Position Target</label>
                                  <select name="position" class="form-control glass-input cursor-pointer font-bold">
                                       <option value="header">Header Banner</option>
                                       <option value="dashboard">Dashboard Sidebar Widget</option>
                                  </select>
                             </div>
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Action URL Target</label>
                                  <input type="text" name="target_url" class="form-control glass-input" placeholder="https://t.me/betelite..." required>
                             </div>
                        </div>
                        <div>
                             <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Vector Logo Seed or Banner URL</label>
                             <input type="text" name="banner_url" class="form-control glass-input" placeholder="https://api.dicebear.com/7.x..." required value="https://api.dicebear.com/7.x/identicon/svg?seed=betelitepromo">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Start Date</label>
                                  <input type="date" name="start_date" class="form-control glass-input" required value="<?php echo date('Y-m-d'); ?>">
                             </div>
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Expiration Date</label>
                                  <input type="date" name="end_date" class="form-control glass-input" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                             </div>
                        </div>
                   </div>
                   <div class="modal-footer border-slate-800">
                        <button type="submit" class="w-full py-2.5 bg-pink-500 hover:bg-pink-600 text-white text-xs font-bold rounded-xl border-none shadow">
                             Deploy Promo Slide
                        </button>
                   </div>
              </form>
         </div>
    </div>
</div>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
