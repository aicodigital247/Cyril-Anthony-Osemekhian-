<?php
/**
 * BETELITE - API Sign In handler
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

$username_or_email = trim($input['username_or_email'] ?? '');
$password = $input['password'] ?? '';

if (empty($username_or_email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please fill in all credentials.'
    ]);
    exit();
}

$sql = "SELECT id, username, email, password_hash, role, status FROM be_users WHERE username = ? OR email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    if ($user['status'] === 'suspended') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Your account has been suspended by our regulatory anti-fraud dashboard.'
        ]);
        exit();
    } elseif (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Session authorized successfully.',
            'redirect' => 'dashboard.php',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
        exit();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid password. Double check and try again.'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No user found with those coordinates.'
    ]);
    exit();
}
