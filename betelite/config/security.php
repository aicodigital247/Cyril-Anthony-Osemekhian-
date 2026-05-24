<?php
/**
 * BETELITE - Security Middleware
 * Dedicated functions to sanitize data, validate user role authorization, and prevent CSRF/XSS injections.
 */

// Generate a secure session token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * XSS Sanitizer for basic inputs
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Clean data for safe SQL Insertion (Optional fallback since we strictly mandatePrepared Statements)
 */
function escape($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

/**
 * Generate hidden input for forms
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">';
}

/**
 * Verify form token
 */
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            header('HTTP/1.1 403 Forbidden');
            die('🚨 Security Violation: CSRF Token Mismatch. Please refresh the page and try again.');
        }
    }
}

/**
 * Middleware: Require Authentication
 */
function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

/**
 * Middleware: Require Predictor Role
 */
function require_predictor() {
    require_auth();
    if ($_SESSION['user_role'] !== 'predictor' && $_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "dashboard.php?error=unauthorized_predictor");
        exit();
    }
}

/**
 * Middleware: Require Admin Role
 */
function require_admin() {
    require_auth();
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: " . BASE_URL . "dashboard.php?error=unauthorized_admin");
        exit();
    }
}
