<?php
/**
 * BETELITE - Database Connection File
 * Pure PHP 8+ procedural database connector using MySQLi ONLY (PDO is strictly forbidden)
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'u123456_betelite'); // cPanel sub-user typical naming
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'B3tEl1t3_Secur3_P@ss!');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'u123456_betelite');
}

// Establish MySQLi connection with local exception safety
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    // Elegant fallback page or API-friendly error response
    if (defined('API_REQUEST') && API_REQUEST === true) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed. Please check cPanel configuration.'
        ]);
        exit();
    }
    
    die("<div style='font-family: sans-serif; background: #020617; color: #ef4444; padding: 30px; text-align: center; border-radius: 8px; margin: 50px auto; max-width: 600px; border: 1px solid #1e293b;'>
        <h2 style='color: #00FF88;'>🏆 BETELITE Initialization</h2>
        <p>Database Connection Error: " . mysqli_connect_error() . "</p>
        <p style='color: #94a3b8; font-size: 14px;'>Make sure to configure real database credentials in <code>/config/database.php</code> or import <code>/database/betelite.sql</code> in phpMyAdmin.</p>
    </div>");
}

// Force UTF8mb4
mysqli_set_charset($conn, "utf8mb4");
