<?php
/**
 * BETELITE - Paystack payment webhook or simulated redirect handler
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";
require_once __DIR__ . "/../config/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('verify_csrf')) {
        verify_csrf();
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        die("🚨 Error: Unauthorized session.");
    }

    $amount = (float)($_POST['amount'] ?? 0);
    $gateway = sanitize($_POST['gateway'] ?? 'Paystack');

    if ($amount < 100) {
        die("🚨 Error: Minimum deposit standard value is ₦100.00");
    }

    mysqli_begin_transaction($conn);
    try {
        // Fetch current wallet ID
        $sql_w = "SELECT id FROM be_wallets WHERE user_id = ? LIMIT 1";
        $st_w = mysqli_prepare($conn, $sql_w);
        mysqli_stmt_bind_param($st_w, "i", $userId);
        mysqli_stmt_execute($st_w);
        $wallet = mysqli_fetch_assoc(mysqli_stmt_get_result($st_w));
        $walletId = $wallet['id'];

        // Credit wallet
        $sql_up = "UPDATE be_wallets SET balance = balance + ? WHERE id = ?";
        $st_up = mysqli_prepare($conn, $sql_up);
        mysqli_stmt_bind_param($st_up, "di", $amount, $walletId);
        mysqli_stmt_execute($st_up);

        // Save transaction
        $ref = "TXN-DEP-" . bin2hex(random_bytes(5));
        log_transaction($conn, $walletId, $amount, 'deposit', $ref, $gateway, 'Funded wallet via direct inline portal payment.', 'completed');

        // Log deposit record
        $sql_dp = "INSERT INTO be_deposits (user_id, amount, payment_method, reference, status) VALUES (?, ?, ?, ?, 'completed')";
        $st_dp = mysqli_prepare($conn, $sql_dp);
        mysqli_stmt_bind_param($st_dp, "idss", $userId, $amount, $gateway, $ref);
        mysqli_stmt_execute($st_dp);

        mysqli_commit($conn);
        
        // Redirect back to wallet page with success flag
        header("Location: ../wallet.php?success=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        die("🚨 Error: payment processing failure. " . $e->getMessage());
    }
} else {
    header("Location: ../wallet.php");
    exit();
}
