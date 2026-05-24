<?php
/**
 * BETELITE - Real-time Live Scores API Endpoint
 * Outputs current game scoreboard metrics as JSON for AJAX sweeps
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

$sql = "SELECT id, home_team, away_team, home_score, away_score, match_time, match_status, 
               possession_home, possession_away, shots_home, shots_away, corners_home, corners_away, 
               cards_yellow_home, cards_yellow_away, cards_red_home, cards_red_away, live_commentary 
        FROM be_matches WHERE match_status = 'Live' LIMIT 1";
$res = mysqli_query($conn, $sql);

if ($game = mysqli_fetch_assoc($res)) {
    $game['live_commentary'] = json_decode($game['live_commentary'], true) ?: [];
    echo json_encode([
        'status' => 'success',
        'live' => true,
        'match' => $game
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'live' => false,
        'message' => 'No sports games actively playing live.'
    ]);
}
exit();
