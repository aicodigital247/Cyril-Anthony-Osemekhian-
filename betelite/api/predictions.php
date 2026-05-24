<?php
/**
 * BETELITE - Predictions and Selections API Endpoint
 * Outputs analytical prediction bundles as structural JSON databases
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $predictionId = (int)($_GET['id'] ?? 0);
    
    if ($predictionId > 0) {
        // Fetch specific slip
        $sql = "SELECT p.*, pr.display_name, pr.accuracy_rate FROM be_predictions p 
                JOIN be_predictors pr ON p.predictor_id = pr.id 
                WHERE p.id = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $predictionId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $pred = mysqli_fetch_assoc($res);

        if (!$pred) {
            echo json_encode(['status' => 'error', 'message' => 'Slip not found.']);
            exit();
        }

        // Check ownership or if free to reveal items
        $unlocked = ($pred['price'] == 0.00);
        if (!$unlocked && isset($_SESSION['user_id'])) {
            $unlocked = has_purchased_prediction($conn, $_SESSION['user_id'], $predictionId);
        }

        $items = [];
        if ($unlocked) {
            $sql_items = "SELECT pi.*, m.home_team, m.away_team, m.league, m.match_status, m.home_score, m.away_score 
                          FROM be_prediction_items pi
                          JOIN be_matches m ON pi.match_id = m.id 
                          WHERE pi.prediction_id = ?";
            $st_itm = mysqli_prepare($conn, $sql_items);
            mysqli_stmt_bind_param($st_itm, "i", $predictionId);
            mysqli_stmt_execute($st_itm);
            $res_items = mysqli_stmt_get_result($st_itm);
            while ($it = mysqli_fetch_assoc($res_items)) {
                $items[] = $it;
            }
        }

        echo json_encode([
            'status' => 'success',
            'unlocked' => $unlocked,
            'prediction' => $pred,
            'selections' => $items
        ]);
        exit();
    } else {
        // Fetch all predictions catalog
        $sql = "SELECT p.*, pr.display_name, pr.badge, pr.accuracy_rate 
                FROM be_predictions p 
                JOIN be_predictors pr ON p.predictor_id = pr.id 
                ORDER BY p.created_at DESC";
        $res = mysqli_query($conn, $sql);
        $predictions = [];
        while($r = mysqli_fetch_assoc($res)) {
            $predictions[] = $r;
        }

        echo json_encode([
            'status' => 'success',
            'count' => count($predictions),
            'predictions' => $predictions
        ]);
        exit();
    }
} elseif ($method === 'POST') {
    // Compile a prediction slip (requires predictor or admin role!)
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'predictor' && $_SESSION['user_role'] !== 'admin')) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized creator profile level. Please register a Predictor Studio account.'
        ]);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $title = trim($input['title'] ?? '');
    $description = trim($input['description'] ?? '');
    $price = (float)($input['price'] ?? 0.00);
    $confidence = (int)($input['confidence'] ?? 80);
    $is_vip = ($price > 0.00) ? 1 : 0;
    
    // Multi-selected matches
    $matches_selections = $input['selections'] ?? []; // Array of {match_id, market, odds}

    if (empty($title) || empty($matches_selections)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please provide compiled slip title and at least one match selection.'
        ]);
        exit();
    }

    // Get predictor profile ID
    $userId = $_SESSION['user_id'];
    $sql_pr = "SELECT id FROM be_predictors WHERE user_id = ? LIMIT 1";
    $stmt_pr = mysqli_prepare($conn, $sql_pr);
    mysqli_stmt_bind_param($stmt_pr, "i", $userId);
    mysqli_stmt_execute($stmt_pr);
    $res_pr = mysqli_stmt_get_result($stmt_pr);
    $pred_profile = mysqli_fetch_assoc($res_pr);

    if (!$pred_profile) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Predictor profile details not found. Contact administration'
        ]);
        exit();
    }

    $predictorId = $pred_profile['id'];

    mysqli_begin_transaction($conn);
    try {
        // Insert prediction
        $sql_pred = "INSERT INTO be_predictions (predictor_id, title, description, price, confidence, is_vip, status) 
                     VALUES (?, ?, ?, ?, ?, ?, 'Active')";
        $stmt_p = mysqli_prepare($conn, $sql_pred);
        mysqli_stmt_bind_param($stmt_p, "issdii", $predictorId, $title, $description, $price, $confidence, $is_vip);
        mysqli_stmt_execute($stmt_p);
        $predictionId = mysqli_insert_id($conn);

        // Insert items
        foreach ($matches_selections as $sel) {
            $m_id = (int)$sel['match_id'];
            $market = sanitize($sel['market']);
            $odds = (float)$sel['odds'];

            $sql_item = "INSERT INTO be_prediction_items (prediction_id, match_id, market, odds, status) 
                         VALUES (?, ?, ?, ?, 'Pending')";
            $stmt_i = mysqli_prepare($conn, $sql_item);
            mysqli_stmt_bind_param($stmt_i, "iisd", $predictionId, $m_id, $market, $odds);
            mysqli_stmt_execute($stmt_i);
        }

        // Update total forecasts counter
        $sql_up_p = "UPDATE be_predictors SET total_predictions = total_predictions + 1 WHERE id = ?";
        $stmt_up_p = mysqli_prepare($conn, $sql_up_p);
        mysqli_stmt_bind_param($stmt_up_p, "i", $predictorId);
        mysqli_stmt_execute($stmt_up_p);

        mysqli_commit($conn);
        echo json_encode([
            'status' => 'success',
            'id' => $predictionId,
            'message' => 'Premium betting slip compiled and cataloged onto Tipster Marketplace!'
        ]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode([
            'status' => 'error',
            'message' => 'Transactional error processing slip compilation: ' . $e->getMessage()
        ]);
    }
    exit();
}
