<?php
/**
 * BETELITE - Matches API Endpoint
 * Outputs JSON catalogs of football matches and pitch variables
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $status = $_GET['status'] ?? '';
    
    $sql = "SELECT * FROM be_matches";
    if (!empty($status)) {
        $sql .= " WHERE match_status = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $status);
    } else {
        $stmt = mysqli_prepare($conn, $sql);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $matches = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Parse comment queues safely
        $row['live_commentary'] = json_decode($row['live_commentary'], true) ?: [];
        $matches[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($matches),
        'matches' => $matches
    ]);
    exit();
} elseif ($method === 'POST') {
    // Requires admin level
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized supreme access configuration required.'
        ]);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $sport = $input['sport'] ?? 'Football';
    $league = trim($input['league'] ?? '');
    $home_team = trim($input['home_team'] ?? '');
    $away_team = trim($input['away_team'] ?? '');
    $start_datetime = $input['start_datetime'] ?? date("Y-m-d H:i:s", strtotime("+1 day"));
    $match_status = $input['match_status'] ?? 'Upcoming';

    if (empty($league) || empty($home_team) || empty($away_team)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please provide league name, home and away team parameters.'
        ]);
        exit();
    }

    $sql = "INSERT INTO be_matches (sport, league, home_team, away_team, start_datetime, match_status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $sport, $league, $home_team, $away_team, $start_datetime, $match_status);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'status' => 'success',
            'id' => mysqli_insert_id($conn),
            'message' => 'Sports game added successfully to BetElite database core.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database persistence failure.'
        ]);
    }
    exit();
}
