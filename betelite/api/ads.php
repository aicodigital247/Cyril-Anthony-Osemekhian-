<?php
/**
 * BETELITE - Advertisements system API Endpoint
 * Outputs strict JSON format
 */
define('API_REQUEST', true);

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

header('Content-Type: application/json');

$sql = "SELECT * FROM be_ads WHERE status = 'active' AND start_date <= CURDATE() AND end_date >= CURDATE()";
$res = mysqli_query($conn, $sql);

$ads = [];
while ($row = mysqli_fetch_assoc($res)) {
    $ads[] = $row;
}

// Fallback high conversion banners
if (empty($ads)) {
    $ads[] = [
        'id' => 1,
        'title' => 'Register as Professional Tipster Pro and Keep 75% of Sales Commissions!',
        'banner_url' => 'https://api.dicebear.com/7.x/identicon/svg?seed=betelitepromo',
        'target_url' => 'register.php',
        'position' => 'header',
        'status' => 'active'
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($ads),
    'ads' => $ads
]);
exit();
