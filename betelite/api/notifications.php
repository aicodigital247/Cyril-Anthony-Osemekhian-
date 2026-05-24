<?php
/**
 * BETELITE - User System Notifications API Endpoint
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please sign in to fetch notifications.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT * FROM be_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($notifications),
        'notifications' => $notifications
    ]);
    exit();
} elseif ($method === 'POST') {
    // Mark notifications as read
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $notif_id = (int)($input['notification_id'] ?? 0);

    if ($notif_id > 0) {
        $sql = "UPDATE be_notifications SET is_read = 1 WHERE user_id = ? AND id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $userId, $notif_id);
    } else {
        $sql = "UPDATE be_notifications SET is_read = 1 WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
    }

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Notifications cleared.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to execute query.'
        ]);
    }
    exit();
}
