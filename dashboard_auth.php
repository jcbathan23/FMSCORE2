<?php
// Simple authentication for dashboard modules
// Include this at the top of dashboard pages instead of security.php

require_once 'auth_check.php';
require_once 'db.php';

// Check if user is logged in
if (!validateSession()) {
    header('Location: login.php?timeout=1');
    exit();
}

// Check 2FA if required
if (!is2FAVerified()) {
    header('Location: verify_2fa.php');
    exit();
}

// Get current user info for use in the page
$current_user = getCurrentUser();
$user_id = $current_user['id'];
$username = $current_user['username'];
$user_role = $current_user['role'];

// Set basic security headers (less aggressive than security.php)
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Prevent caching of dashboard pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
