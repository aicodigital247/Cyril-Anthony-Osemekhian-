<?php
/**
 * BETELITE - Secure Login Panel
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
    
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    if (empty($username_or_email) || empty($password)) {
        $error = "Please fill in all empty fields.";
    } else {
        // Prepare mysqli statement
        $sql = "SELECT id, username, email, password_hash, role, status FROM be_users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if ($user['status'] === 'suspended') {
                 $error = "Your account has been suspended by our regulatory anti-fraud dashboard.";
            } else if (password_verify($password, $user['password_hash'])) {
                 // Regenerate session ID for high security
                 session_regenerate_id(true);
                 $_SESSION['user_id'] = $user['id'];
                 $_SESSION['username'] = $user['username'];
                 $_SESSION['user_email'] = $user['email'];
                 $_SESSION['user_role'] = $user['role'];
                 
                 header("Location: dashboard.php");
                 exit();
            } else {
                 $error = "Invalid password. Double check and try again.";
            }
        } else {
            $error = "No user found with those credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BETELITE - Sportsbook Member Login</title>
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
        .glass-input { background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(30, 41, 59, 1); color: #ffffff; border-radius: 10px; width: 100%; padding: 10px 14px;}
        .glass-input:focus { border-color: #00FF88 !important; outline: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center items-center px-4">

    <div class="w-full max-w-md space-y-6">
        <!-- Logo -->
        <div class="text-center">
            <a href="index.php" class="inline-flex items-center gap-2 font-display font-bold text-2xl text-white tracking-widest no-underline">
                <span class="text-electricGreen">🏆</span> BET<span class="text-electricGreen">ELITE</span>
            </a>
            <p class="text-xs text-slate-400 mt-1">Nigeria Premium Sports Prediction Marketplace</p>
        </div>

        <div class="glass-card p-8 space-y-6 shadow-2xl">
            <h2 class="font-display font-bold text-xl text-white tracking-tight">Access VIP Account</h2>
            
            <?php if (!empty($error)): ?>
                <div class="p-3 bg-red-900/30 border border-red-500/40 text-red-400 rounded-lg text-xs font-semibold">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                
                <div>
                     <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Username or Email</label>
                     <input type="text" name="username_or_email" class="glass-input" placeholder="e.g. punter1 X" required>
                </div>

                <div>
                     <label class="block text-xs font-semibold uppercase text-slate-400 mb-1">Secure Password</label>
                     <input type="password" name="password" class="glass-input" placeholder="••••••••" required>
                </div>

                <div class="flex justify-between items-center text-xs">
                    <label class="flex items-center gap-1.5 text-slate-400">
                        <input type="checkbox" class="rounded bg-slate-800 border-slate-700 text-electricGreen focus:ring-0"> Remember Session
                    </label>
                    <a href="#" class="text-electricGreen hover:underline no-underline">Forgot Password?</a>
                </div>

                <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-[#00e177] text-darkBg font-bold rounded-xl text-center shadow-lg transition-all">
                    Sign In Securely
                </button>
            </form>

            <div class="border-t border-slate-800 pt-4 text-center">
                <p class="text-xs text-slate-400">
                    New to BETELITE? <a href="register.php" class="text-electricGreen font-semibold hover:underline no-underline">Create VIP Account</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Initialize icons -->
    <script>lucide.createIcons();</script>
</body>
</html>
