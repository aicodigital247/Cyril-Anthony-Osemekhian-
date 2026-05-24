<?php
/**
 * BETELITE - Tipster Ratings and Reviews API Endpoint
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $predictorId = (int)($_GET['predictor_id'] ?? 0);
    
    if ($predictorId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide predictor ID.']);
        exit();
    }

    $sql = "SELECT r.*, u.username, u.full_name FROM be_ratings r 
            JOIN be_users u ON r.user_id = u.id 
            WHERE r.predictor_id = ? 
            ORDER BY r.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $predictorId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $reviews = [];
    $avg_rating = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }

    if (count($reviews) > 0) {
        $avg_rating = array_sum(array_column($reviews, 'rating')) / count($reviews);
    }

    echo json_encode([
        'status' => 'success',
        'predictor_id' => $predictorId,
        'average_rating' => round($avg_rating, 1),
        'count' => count($reviews),
        'reviews' => $reviews
    ]);
    exit();
} elseif ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.']);
        exit();
    }

    verify_csrf();

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $userId = $_SESSION['user_id'];
    $predictorId = (int)($input['predictor_id'] ?? 0);
    $rating = (int)($input['rating'] ?? 5);
    $review = trim($input['review'] ?? '');

    if ($predictorId <= 0 || $rating < 1 || $rating > 5) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid evaluation ratings data fields.']);
        exit();
    }

    // Insert or update ratings
    $sql = "INSERT INTO be_ratings (user_id, predictor_id, rating, review) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiis", $userId, $predictorId, $rating, $review);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Review logged successfully. Your evaluation helps elevate BetElite standards!'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save review into database.'
        ]);
    }
    exit();
}
