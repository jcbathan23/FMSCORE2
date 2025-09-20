<?php
// Authentication check for dashboard modules
// This file should be included at the top of protected pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

// Function to check user role
function hasRole($required_role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_role = $_SESSION['role'];
    
    // Admin can access everything
    if ($user_role === 'admin') {
        return true;
    }
    
    // Check specific role
    return $user_role === $required_role;
}

// Function to require login
function requireLogin($redirect_to = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect_to");
        exit();
    }
}

// Function to require specific role
function requireRole($required_role, $redirect_to = 'login.php') {
    if (!hasRole($required_role)) {
        if (!isLoggedIn()) {
            header("Location: $redirect_to");
        } else {
            // User is logged in but doesn't have required role
            $dashboard = getDashboardForRole($_SESSION['role']);
            header("Location: $dashboard");
        }
        exit();
    }
}

// Function to get appropriate dashboard for user role
function getDashboardForRole($role) {
    switch ($role) {
        case 'admin':
            return 'admin.php';
        case 'provider':
            return 'provider-dashboard.php';
        case 'user':
        default:
            return 'user-dashboard.php';
    }
}

// Function to check session validity (less aggressive than security.php)
function validateSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check session timeout (2 hours instead of 1)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
        session_destroy();
        return false;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Function to refresh session (extend timeout)
function refreshSession() {
    if (isLoggedIn()) {
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID occasionally for security (every 30 minutes)
        if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// Function to get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role']
    ];
}

// Function to check if 2FA is verified (if enabled)
function is2FAVerified() {
    // If 2FA is not enabled for this session, consider it verified
    if (!isset($_SESSION['pending_2fa_user'])) {
        return true;
    }
    
    // Check if 2FA verification is complete
    return isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'] === true;
}

// Auto-refresh session on page load
if (isLoggedIn()) {
    refreshSession();
}
?>
