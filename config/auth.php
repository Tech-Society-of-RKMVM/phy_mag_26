<?php
/**
 * Authentication and Session Management
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the current user is logged in as an admin.
 */
function is_admin_logged_in() {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Enforce admin authentication on protected pages.
 */
function require_admin() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Generate CSRF token.
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token.
 */
function validate_csrf_token($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Alias for validate_csrf_token() — used across admin pages.
 */
function verify_csrf_token($token) {
    return validate_csrf_token($token);
}
