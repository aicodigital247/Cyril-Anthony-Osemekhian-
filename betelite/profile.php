<?php
/**
 * BETELITE - User Profile settings
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

require_auth();

$userId = $_SESSION['user_id'];
$msg_error = ''; $msg_success = '';

// Fetch fresh details
$sql = "SELECT id, username, email, full_name, phone, role, status FROM be_users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $full_name = trim(sanitize($_POST['full_name']));
    $phone = trim(sanitize($_POST['phone']));
    $email = trim(strtolower(sanitize($_POST['email'])));
    $new_password = $_POST['new_password'];
    
    if (empty($email)) {
        $msg_error = "Email address cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg_error = "Please provide a valid email structure.";
    } else {
        // Update user core info
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $msg_error = "New password must be at least 6 characters.";
            } else {
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $sql_up = "UPDATE be_users SET full_name = ?, phone = ?, email = ?, password_hash = ? WHERE id = ?";
                $st_up = mysqli_prepare($conn, $sql_up);
                mysqli_stmt_bind_param($st_up, "ssssi", $full_name, $phone, $email, $password_hash, $userId);
                mysqli_stmt_execute($st_up);
                $msg_success = "Profile and password updated successfully!";
            }
        } else {
            $sql_up = "UPDATE be_users SET full_name = ?, phone = ?, email = ? WHERE id = ?";
            $st_up = mysqli_prepare($conn, $sql_up);
            mysqli_stmt_bind_param($st_up, "sssi", $full_name, $phone, $email, $userId);
            mysqli_stmt_execute($st_up);
            $msg_success = "Profile updated successfully!";
        }
        
        if (empty($msg_error)) {
            // Refresh variables
            $_SESSION['user_email'] = $email;
            
            $sql = "SELECT id, username, email, full_name, phone, role, status FROM be_users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($res);
        }
    }
}
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-8 flex-grow">
    <div class="space-y-2">
         <span class="text-electricGreen text-xs font-mono font-bold tracking-widest uppercase">Member Hub</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Profile Control Room</h1>
         <p class="text-xs text-mutedText">Keep your contact coordinates pristine, update authentication passwords, and monitor system registration flags.</p>
    </div>

    <?php if ($msg_success): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>
    <?php if ($msg_error): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-red-400 rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
         <!-- Left card: profile summary -->
         <div class="glass-card p-6 flex flex-col items-center justify-center text-center space-y-4">
              <div class="relative">
                   <img src="https://api.dicebear.com/7.x/pixel-art/svg?seed=<?php echo urlencode($user['username']); ?>" class="w-24 h-24 rounded-full border-2 border-electricGreen" alt="avatar">
                   <div class="absolute bottom-0 right-0 bg-electricGreen text-darkBg p-1.5 rounded-full border border-darkBg">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                   </div>
              </div>
              <div>
                   <h3 class="font-display font-bold text-white text-lg">@<?php echo $user['username']; ?></h3>
                   <p class="text-xs text-mutedText uppercase font-mono tracking-wider mt-1"><?php echo $user['role']; ?> tier</p>
              </div>
              <div class="w-full border-t border-slate-800 pt-3 flex justify-around text-xs font-mono">
                   <div>
                        <span class="text-mutedText uppercase text-[9px] block">Status</span>
                        <span class="text-electricGreen font-bold"><?php echo strtoupper($user['status']); ?></span>
                   </div>
                   <div>
                        <span class="text-mutedText uppercase text-[9px] block">Member ID</span>
                        <span class="text-white font-bold">#EM-<?php echo $user['id']; ?></span>
                   </div>
              </div>
         </div>

         <!-- Right area form -->
         <div class="md:col-span-2">
              <div class="glass-card p-6">
                   <h3 class="font-display font-bold text-sm text-white uppercase tracking-wider mb-4 border-b border-slate-800 pb-3 flex items-center gap-2">
                        <i data-lucide="user-cog" class="text-electricGreen"></i> Settings Coordinates
                   </h3>
                   <form action="profile.php" method="POST" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                             <div>
                                  <label class="block text-xs font-semibold text-mutedText uppercase mb-1">Full Identity Name</label>
                                  <input type="text" name="full_name" class="glass-input w-full text-xs" value="<?php echo htmlspecialchars($user['full_name']); ?>" placeholder="Anthony Cyril" required>
                             </div>
                             <div>
                                  <label class="block text-xs font-semibold text-mutedText uppercase mb-1">Mobile Contact Phone</label>
                                  <input type="text" name="phone" class="glass-input w-full text-xs" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="+2347012345678">
                             </div>
                        </div>

                        <div>
                             <label class="block text-xs font-semibold text-mutedText uppercase mb-1">Electronic Mail Coordinates</label>
                             <input type="email" name="email" class="glass-input w-full text-xs" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="e.g. email@betelite.com" required>
                        </div>

                        <hr class="border-slate-800 my-4">

                        <div>
                             <label class="block text-xs font-semibold text-electricGreen uppercase mb-1">Update Security Password (Optional)</label>
                             <input type="password" name="new_password" class="glass-input w-full text-xs" placeholder="•••••••• Leave blank to keep current password">
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-[#00e177] text-darkBg font-bold rounded-xl transition-all border-none text-xs">
                             Save Settings Updates
                        </button>
                   </form>
              </div>
         </div>
    </div>
</main>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
