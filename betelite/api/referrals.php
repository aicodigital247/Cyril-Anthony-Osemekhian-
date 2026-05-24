<?php
/**
 * BETELITE - User Affiliate & Referrals API Endpoint
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please sign in to read affiliate parameters.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];

// Get referrals registered under this user
$sql = "SELECT r.*, u.username, u.email, u.created_at as user_joined_at 
        FROM be_referrals r 
        JOIN be_users u ON r.referred_user_id = u.id 
        WHERE r.referrer_user_id = ? 
        ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$referrals = [];
$total_commission = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $total_commission += (float)$row['commission_earned'];
    $referrals[] = $row;
}

echo json_encode([
    'status' => 'success',
    'referral_code' => strtoupper($_SESSION['username']),
    'total_commission' => $total_commission,
    'count' => count($referrals),
    'referrals' => $referrals
]);
exit();
