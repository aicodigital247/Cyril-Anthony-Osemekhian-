<?php
/**
 * BETELITE - Helper Functions
 * General procedural core utilities for checking odds, earnings, tickets, accuracy and VIP active access levels.
 */

require_once __DIR__ . "/database.php";

/**
 * Fetch wallet balance for a user
 */
function get_wallet_balance($conn, $userId) {
    $sql = "SELECT balance FROM be_wallets WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        return (float) $row['balance'];
    }
    
    // Create automatic wallet if missing
    $sql_create = "INSERT INTO be_wallets (user_id, balance) VALUES (?, 0.00)";
    $stmt_c = mysqli_prepare($conn, $sql_create);
    mysqli_stmt_bind_param($stmt_c, "i", $userId);
    mysqli_stmt_execute($stmt_c);
    return 0.00;
}

/**
 * Check if the user has purchased a specific premium prediction
 */
function has_purchased_prediction($conn, $userId, $predictionId) {
    if (!$userId) return false;
    
    // Admins and the predictor who made it view it dynamically for free
    $sql_role = "SELECT role, (SELECT p.id FROM be_predictors p WHERE p.user_id = be_users.id) as predictor_id 
                 FROM be_users WHERE id = ?";
    $stmt_r = mysqli_prepare($conn, $sql_role);
    mysqli_stmt_bind_param($stmt_r, "i", $userId);
    mysqli_stmt_execute($stmt_r);
    $res_r = mysqli_stmt_get_result($stmt_r);
    if ($user = mysqli_fetch_assoc($res_r)) {
        if ($user['role'] === 'admin') return true;
        
        $sql_pred = "SELECT predictor_id FROM be_predictions WHERE id = ?";
        $stmt_p = mysqli_prepare($conn, $sql_pred);
        mysqli_stmt_bind_param($stmt_p, "i", $predictionId);
        mysqli_stmt_execute($stmt_p);
        $res_p = mysqli_stmt_get_result($stmt_p);
        if ($pred = mysqli_fetch_assoc($res_p)) {
            if ($user['predictor_id'] == $pred['predictor_id']) {
                return true;
            }
        }
    }

    // Check order database table
    $sql = "SELECT id FROM be_orders WHERE user_id = ? AND prediction_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $predictionId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return (mysqli_num_rows($result) > 0);
}

/**
 * Check if user is actively subscribed to a Predictor's VIP access right now
 */
function is_subscribed_to_predictor($conn, $userId, $predictorId) {
    if (!$userId) return false;
    $sql = "SELECT id FROM be_subscriptions 
            WHERE user_id = ? AND predictor_id = ? AND end_date > NOW() AND status = 'active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $userId, $predictorId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return (mysqli_num_rows($result) > 0);
}

/**
 * Log a financial transaction
 */
function log_transaction($conn, $walletId, $amount, $type, $reference, $method, $desc, $status = 'completed') {
    $sql = "INSERT INTO be_transactions (wallet_id, amount, type, reference, payment_method, description, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "idsssss", $walletId, $amount, $type, $reference, $method, $desc, $status);
    return mysqli_stmt_execute($stmt);
}

/**
 * Format timestamp nicely into human duration
 */
function format_date_human($datetimeString) {
    $time = strtotime($datetimeString);
    $diff = time() - $time;
    if ($diff < 60) {
        return "Just now";
    } elseif ($diff < 3600) {
        return round($diff / 60) . " mins ago";
    } elseif ($diff < 86400) {
        return round($diff / 3600) . " hours ago";
    } else {
        return date("M j, Y - H:i", $time);
    }
}
