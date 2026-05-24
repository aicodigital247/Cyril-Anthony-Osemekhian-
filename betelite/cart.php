<?php
/**
 * BETELITE - Shopping Cart for Premium Predictions
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

require_auth();

$userId = $_SESSION['user_id'];
$balance = get_wallet_balance($conn, $userId);

// Handle Item Deletion
if (isset($_GET['remove'])) {
    $pred_id = (int)$_GET['remove'];
    $sql_del = "DELETE FROM be_cart WHERE user_id = ? AND prediction_id = ?";
    $stmt = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $pred_id);
    mysqli_stmt_execute($stmt);
    header("Location: cart.php");
    exit();
}

// Handle Purchase Checkout
$msg_error = ''; $msg_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    verify_csrf();
    
    // Fetch items currently in cart
    $sql_c = "SELECT prediction_id, (SELECT price FROM be_predictions WHERE id = prediction_id) as price FROM be_cart WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql_c);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $res_c = mysqli_stmt_get_result($stmt);
    
    $total_cost = 0;
    $items = [];
    while ($row = mysqli_fetch_assoc($res_c)) {
        $total_cost += (float)$row['price'];
        $items[] = $row['prediction_id'];
    }

    if (empty($items)) {
        $msg_error = "Your cart is empty.";
    } elseif ($balance < $total_cost) {
        $msg_error = "Insufficient wallet balance. You have ₦" . number_format($balance, 2) . " but the checkout requires ₦" . number_format($total_cost, 2) . ". Please top up in your Wallet view.";
    } else {
        // Atomic transaction to secure checkout
        mysqli_begin_transaction($conn);
        try {
            // Deduct from wallet
            $sql_ded = "UPDATE be_wallets SET balance = balance - ? WHERE user_id = ?";
            $st_ded = mysqli_prepare($conn, $sql_ded);
            mysqli_stmt_bind_param($st_ded, "di", $total_cost, $userId);
            mysqli_stmt_execute($st_ded);

            // Get Wallet ID
            $sql_w = "SELECT id FROM be_wallets WHERE user_id = ?";
            $st_w = mysqli_prepare($conn, $sql_w);
            mysqli_stmt_bind_param($st_w, "i", $userId);
            mysqli_stmt_execute($st_w);
            $w_row = mysqli_fetch_assoc(mysqli_stmt_get_result($st_w));
            $walletId = $w_row['id'];

            // Save Transaction Log
            $ref = "TXN-PUR-" . bin2hex(random_bytes(5));
            log_transaction($conn, $walletId, $total_cost, 'purchase', $ref, 'wallet', 'Deducted wallet points to unlock ' . count($items) . ' prediction slips.');

            // Add Order mappings
            foreach ($items as $p_id) {
                $sql_o = "INSERT IGNORE INTO be_orders (user_id, prediction_id, amount_paid) VALUES (?, ?, (SELECT price FROM be_predictions WHERE id = ?))";
                $st_o = mysqli_prepare($conn, $sql_o);
                mysqli_stmt_bind_param($st_o, "iii", $userId, $p_id, $p_id);
                mysqli_stmt_execute($st_o);
            }

            // Clear Cart
            $sql_clr = "DELETE FROM be_cart WHERE user_id = ?";
            $st_clr = mysqli_prepare($conn, $sql_clr);
            mysqli_stmt_bind_param($st_clr, "i", $userId);
            mysqli_stmt_execute($st_clr);

            mysqli_commit($conn);
            $msg_success = "Success! Premium Slips unlocked! View slips in your Dashboard.";
            $balance = get_wallet_balance($conn, $userId);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $msg_error = "Failure processing checkout: " . $e->getMessage();
        }
    }
}

// Reload Cart Items for View
$sql_items = "SELECT c.*, p.title, p.price, pr.display_name 
              FROM be_cart c 
              JOIN be_predictions p ON c.prediction_id = p.id 
              JOIN be_predictors pr ON p.predictor_id = pr.id
              WHERE c.user_id = ?";
$stmt = mysqli_prepare($conn, $sql_items);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res_items = mysqli_stmt_get_result($stmt);

$total = 0;
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-8 flex-grow">
    <div class="space-y-2">
         <span class="text-electricGreen text-xs font-mono font-bold tracking-widest uppercase">Safe Checkout Ledger</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Bet Slips Checkout</h1>
    </div>

    <?php if ($msg_success): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?> <a href="dashboard.php" class="underline text-white font-bold">Go to My Tickets</a></div>
    <?php endif; ?>
    <?php if ($msg_error): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-red-450 rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
         <!-- Left: Items list -->
         <div class="md:col-span-2 space-y-4">
              <?php if (mysqli_num_rows($res_items) > 0): ?>
                  <?php while ($item = mysqli_fetch_assoc($res_items)): $total += (float)$item['price']; ?>
                      <div class="glass-card p-4 flex justify-between items-center gap-4 bg-slate-900/40">
                           <div class="space-y-1">
                                <h3 class="text-xs font-bold text-white"><?php echo $item['title']; ?></h3>
                                <p class="text-[10px] text-mutedText">Forecast by: @<?php echo $item['display_name']; ?></p>
                           </div>
                           <div class="flex items-center gap-4">
                                <span class="text-electricGreen font-mono text-xs font-bold">₦<?php echo number_format($item['price'], 2); ?></span>
                                <a href="cart.php?remove=<?php echo $item['prediction_id']; ?>" class="text-dangerRed hover:text-red-300 p-1 bg-slate-800 rounded">
                                     <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </a>
                           </div>
                      </div>
                  <?php endwhile; ?>
              <?php else: ?>
                  <div class="glass-card p-12 text-center text-mutedText space-y-2 col-span-2">
                       <i data-lucide="shopping-cart" class="w-12 h-12 opacity-25 mx-auto mb-2 text-mutedText"></i>
                       <p class="text-sm font-semibold">All cleared out! Your shopping cart is empty.</p>
                       <p class="text-xs text-mutedText">Add premium analyses from the tipster marketplace.</p>
                       <a href="marketplace.php" class="inline-block mt-3 px-4 py-1.5 bg-electricGreen text-darkBg text-xs font-bold rounded-lg no-underline hover:bg-greenHover">Browse Marketplace</a>
                  </div>
              <?php endif; ?>
         </div>

         <!-- Right: Checkout summaries -->
         <?php if ($total > 0): ?>
             <div class="glass-card p-6 bg-slate-955 border border-slate-850 h-fit space-y-4">
                  <h4 class="text-xs font-bold uppercase tracking-wider text-mutedText">Purchase Summary</h4>
                  <div class="space-y-2 text-xs border-b border-slate-800 pb-3">
                       <div class="flex justify-between">
                            <span class="text-mutedText">Billing Slips Subtotal</span>
                            <span class="font-mono text-white">₦<?php echo number_format($total, 2); ?></span>
                       </div>
                       <div class="flex justify-between">
                            <span class="text-mutedText">Vat Commission Fee</span>
                            <span class="font-mono text-electricGreen">₦0.00 (Promo)</span>
                       </div>
                  </div>
                  <div class="flex justify-between items-center py-2">
                       <span class="text-sm font-bold text-white">Payable Grand Total</span>
                       <span class="text-electricGreen font-mono font-bold text-base">₦<?php echo number_format($total, 2); ?></span>
                  </div>
                  
                  <!-- Checkout Trigger -->
                  <form action="cart.php" method="POST">
                       <?php echo csrf_field(); ?>
                       <input type="hidden" name="checkout" value="1">
                       <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl transition-all border-none">
                            Pay Securely from Wallet
                       </button>
                  </form>
             </div>
         <?php endif; ?>
    </div>
</main>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
