<?php
/**
 * BETELITE - API Sign Up handler
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'HTTP Method not allowed. Use POST.'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$username = trim(strtolower($input['username'] ?? ''));
$email = trim(strtolower($input['email'] ?? ''));
$password = $input['password'] ?? '';
$full_name = trim($input['full_name'] ?? '');
$role = $input['role'] ?? 'user';

if (empty($username) || empty($email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Required coordinates (username, email, password) are absent.'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Valid email formatting is required.'
    ]);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password must consist of at least 6 characters.'
    ]);
    exit();
}

// Uniqueness checks
$sql_check = "SELECT id FROM be_users WHERE username = ? OR email = ? LIMIT 1";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'The username or email is already registered on BETELITE.'
    ]);
    exit();
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

mysqli_begin_transaction($conn);

try {
    $sql_ins = "INSERT INTO be_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
    $stmt_ins = mysqli_prepare($conn, $sql_ins);
    mysqli_stmt_bind_param($stmt_ins, "sssss", $username, $email, $password_hash, $full_name, $role);
    mysqli_stmt_execute($stmt_ins);
    $userId = mysqli_insert_id($conn);

    // Give 10,000 NGN welcome practice balance
    $starting_balance = 10000.00;
    $sql_wallet = "INSERT INTO be_wallets (user_id, balance) VALUES (?, ?)";
    $stmt_w = mysqli_prepare($conn, $sql_wallet);
    mysqli_stmt_bind_param($stmt_w, "id", $userId, $starting_balance);
    mysqli_stmt_execute($stmt_w);

    // Save transaction logs
    $ref = "WELCOME-DEP-" . bin2hex(random_bytes(5));
    $sql_log = "INSERT INTO be_transactions (wallet_id, amount, type, status, reference, payment_method, description) 
                VALUES ((SELECT id FROM be_wallets WHERE user_id = ?), ?, 'deposit', 'completed', ?, 'System Promo', 'Free welcome practice balance for evaluation')";
    $stmt_l = mysqli_prepare($conn, $sql_log);
    mysqli_stmt_bind_param($stmt_l, "idss", $userId, $starting_balance, $ref);
    mysqli_stmt_execute($stmt_l);

    // Predictor studio profile initialization
    if ($role === 'predictor') {
        $sql_pred = "INSERT INTO be_predictors (user_id, display_name, bio) VALUES (?, ?, 'Professional Sportsbook Prediction Specialist.')";
        $stmt_pr = mysqli_prepare($conn, $sql_pred);
        $display_name = $full_name ?: $username;
        mysqli_stmt_bind_param($stmt_pr, "is", $userId, $display_name);
        mysqli_stmt_execute($stmt_pr);
    }

    mysqli_commit($conn);
    echo json_encode([
        'status' => 'success',
        'message' => 'Account created successfully with a free ₦10,000 Welcome Bonus!'
    ]);
    exit();
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        'status' => 'error',
        'message' => 'Critical DB error during registration setup: ' . $e->getMessage()
    ]);
    exit();
}
