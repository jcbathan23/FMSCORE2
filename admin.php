<?php
/**
 * Admin Dashboard - Module Loader
 * CORE II - Admin Dashboard System
 * 
 * This file now loads the modular dashboard system
 * All functionality has been moved to modules/dashboard/
 */

// Include authentication (session_start is already called in auth.php)
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Only admin users can access this page
if (!isAdmin()) {
    header('Location: login.php?error=access_denied');
    exit();
}
// security.php is not needed here as auth.php provides basic security

// Define constants for the dashboard module
define('DASHBOARD_MODULE', true);
define('ADMIN_DASHBOARD_ACCESS', true);

// Redirect to the new dashboard module
header('Location: modules/dashboard/dashboard.php');
exit();
?>
