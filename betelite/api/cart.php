<?php
/**
 * BETELITE - AJAX Cart API Endpoint
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";
require_once __DIR__ . "/../config/functions.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Your session has expired or you are anonymous. Please sign in to buy predictions.'
    ]);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $predictionId = (int)($_POST['prediction_id'] ?? 0);
        
        if ($predictionId <= 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid predictive slip specified.'
            ]);
            exit();
        }

        // 1. Check if they already own it
        if (has_purchased_prediction($conn, $userId, $predictionId)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'You already purchased this slip! It is already active on your tickets panel.'
            ]);
            exit();
        }

        // 2. Check if already in cart
        $sql_check = "SELECT id FROM be_cart WHERE user_id = ? AND prediction_id = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ii", $userId, $predictionId);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($res_check) > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Item already sitting in your cart. Check your cart to check out.'
            ]);
            exit();
        }

        // 3. Insert into Cart
        $sql_ins = "INSERT INTO be_cart (user_id, prediction_id) VALUES (?, ?)";
        $stmt_ins = mysqli_prepare($conn, $sql_ins);
        mysqli_stmt_bind_param($stmt_ins, "ii", $userId, $predictionId);
        
        if (mysqli_stmt_execute($stmt_ins)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Slip successfully added to cart. Finish checkout to unlock.'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Could not insert item into cart table.'
            ]);
        }
        exit();
    }
}

echo json_encode([
    'status' => 'error',
    'message' => 'Unsupported API method request.'
]);
exit();
