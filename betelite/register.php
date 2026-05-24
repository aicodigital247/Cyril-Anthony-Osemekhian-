<?php
/**
 * BETELITE - Register account
 */
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/security.php";

$error = ''; $success = '';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim(strtolower($_POST['username']));
    $email = trim(strtolower($_POST['email']));
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'] ?? 'user';

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Required credentials (username, email, password) are absent.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Valid email formatting is required.";
    } elseif (strlen($password) < 6) {
        $error = "Passwords must consist of at least 6 characters.";
    } else {
        // Double check username and email uniqueness
        $sql_check = "SELECT id FROM be_users WHERE username = ? OR email = ? LIMIT 1";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            $error = "The username or email is already registered on BETELITE.";
        } else {
            // Password hash
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Turn off autocommit to make standard wallet linkage atomic
            mysqli_begin_transaction($conn);

            try {
                // Insert User
                $sql_ins = "INSERT INTO be_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
                $stmt_ins = mysqli_prepare($conn, $sql_ins);
                mysqli_stmt_bind_param($stmt_ins, "sssss", $username, $email, $password_hash, $full_name, $role);
                mysqli_stmt_execute($stmt_ins);
                $userId = mysqli_insert_id($conn);

                // Initialize Wallet with a free practice balance of 10,000 NGN to support easy evaluation in AI Studio!
                $starting_balance = 10000.00;
                $sql_wallet = "INSERT INTO be_wallets (user_id, balance) VALUES (?, ?)";
                $stmt_w = mysqli_prepare($conn, $sql_wallet);
                mysqli_stmt_bind_param($stmt_w, "id", $userId, $starting_balance);
                mysqli_stmt_execute($stmt_w);

                // Add record log
                $ref = "WELCOME-DEP-" . bin2hex(random_bytes(5));
                $sql_log = "INSERT INTO be_transactions (wallet_id, amount, type, status, reference, payment_method, description) 
                            VALUES ((SELECT id FROM be_wallets WHERE user_id = ?), ?, 'deposit', 'completed', ?, 'System Promo', 'Free welcome practice balance for evaluation')";
                $stmt_l = mysqli_prepare($conn, $sql_log);
                mysqli_stmt_bind_param($stmt_l, "idss", $userId, $starting_balance, $ref);
                mysqli_stmt_execute($stmt_l);

                // If role is predictor, create predictor entry
                if ($role === 'predictor') {
                    $sql_pred = "INSERT INTO be_predictors (user_id, display_name, bio) VALUES (?, ?, 'Professional Sportsbook Prediction Specialist.')";
                    $stmt_pr = mysqli_prepare($conn, $sql_pred);
                    $display_name = $full_name ?: $username;
                    mysqli_stmt_bind_param($stmt_pr, "is", $userId, $display_name);
                    mysqli_stmt_execute($stmt_pr);
                }

                mysqli_commit($conn);
                $success = "Account created successfully! You received a free ₦10,000 evaluation bonus. Sign in below.";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = "Fatal Database Exception: Registration could not complete. Please retry.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BETELITE - Register VIP Account</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { darkBg: '#020617', darkSec: '#0f172a', borderSl: '#1e293b', electricGreen: '#00FF88' } } } }
    </script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #020617; color: #ffffff; }
        .glass-card { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(30, 41, 59, 0.8); border-radius: 16px; }
        .glass-input { background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(30, 41, 59, 1); color: #ffffff; border-radius: 10px; width: 100%; padding: 8px 12px;}
        .glass-input:focus { border-color: #00FF88 !important; outline: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center items-center py-10 px-4">

    <div class="w-full max-w-md space-y-4">
        <!-- Logo -->
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center gap-2 font-display font-bold text-2xl text-white tracking-widest no-underline">
                <span class="text-electricGreen">🏆</span> BET<span class="text-electricGreen">ELITE</span>
            </a>
            <p class="text-xs text-slate-400 mt-1">Join the ultimate Nigerian high-yield prediction marketplace</p>
        </div>

        <div class="glass-card p-6 space-y-4 shadow-2xl">
            <h2 class="font-display font-bold text-lg text-white tracking-tight">Register Free Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="p-3 bg-red-900/30 border border-red-500/40 text-red-400 rounded-lg text-xs font-semibold">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">
                    ✓ <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-3">
                <?php echo csrf_field(); ?>
                
                <div>
                     <label class="block text-[10px] font-semibold uppercase text-slate-400 mb-1">Full Name</label>
                     <input type="text" name="full_name" class="glass-input" placeholder="e.g. Anthony Cyril" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                         <label class="block text-[10px] font-semibold uppercase text-slate-400 mb-1">Username</label>
                         <input type="text" name="username" class="glass-input" placeholder="e.g. punter1" required>
                    </div>
                    <div>
                         <label class="block text-[10px] font-semibold uppercase text-slate-400 mb-1">Email Address</label>
                         <input type="email" name="email" class="glass-input" placeholder="punter@betelite.com" required>
                    </div>
                </div>

                <div>
                     <label class="block text-[10px] font-semibold uppercase text-slate-400 mb-1">Security Password</label>
                     <input type="password" name="password" class="glass-input" placeholder="Min 6 characters" required>
                </div>

                <div>
                     <label class="block text-[10px] font-semibold uppercase text-slate-400 mb-1">Select Account Type</label>
                     <select name="role" class="glass-input w-full text-xs">
                          <option value="user">Standard Bettor / Slip Buyer</option>
                          <option value="predictor">Expert Sportsbook Tip predictor Pro</option>
                     </select>
                </div>

                <p class="text-[10px] text-slate-400">By creating a VIP account, you approve terms and appreciate high risk elements.</p>

                <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-[#00e177] text-darkBg font-bold rounded-xl text-center shadow-lg transition-all">
                    Register & Claim ₦10K Welcome Bonus
                </button>
            </form>

            <div class="border-t border-slate-800 pt-3 text-center">
                <p class="text-xs text-slate-400">
                    Existing user? <a href="login.php" class="text-electricGreen font-semibold hover:underline no-underline">Log in securely</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Initialize icons -->
    <script>lucide.createIcons();</script>
</body>
</html>
