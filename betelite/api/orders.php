<?php
/**
 * BETELITE - User Completed Order History API
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Your session is unauthenticated.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];

$sql = "SELECT o.*, p.title, p.price, pr.display_name 
        FROM be_orders o 
        JOIN be_predictions p ON o.prediction_id = p.id 
        JOIN be_predictors pr ON p.predictor_id = pr.id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

echo json_encode([
    'status' => 'success',
    'count' => count($orders),
    'orders' => $orders
]);
exit();
