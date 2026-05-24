<?php
/**
 * BETELITE - System Config
 * Standard session initialization, global paths and general metadata
 */

// Start session if not already active
if (session_status() == PHP_SESSION_NONE) {
    // Session security configurations
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in live SSL production
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Global System Constants
define('SITE_NAME', 'BETELITE');
define('SITE_SLOGAN', 'VIP Sportsbook Prediction & Analytics Marketplace');
define('CURRENCY_SYMBOL', '₦');
define('CURRENCY_CODE', 'NGN');

// Payment Gateway Mock Keys (Standard API integration)
define('PAYSTACK_PUBLIC_KEY', 'pk_test_a0a1a2a3a4b5c6d7e8f90011223344');
define('PAYSTACK_SECRET_KEY', 'sk_test_102030405060708090001002003004');

// Default dynamic timezone
date_default_timezone_set('Africa/Lagos');

// Quick dynamic helper for URL routing
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol . $domainName . "/betelite/";
}
define('BASE_URL', get_base_url());
