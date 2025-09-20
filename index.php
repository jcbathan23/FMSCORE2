<?php
// index.php
// Main entry point for the SLATE application.
// Redirects users to the appropriate dashboard or login based on session state.

session_start();

// Consider the user logged in if either the legacy flags are present
// or a generic 'logged_in' boolean has been set elsewhere.
$isLoggedIn = (
    (isset($_SESSION['user_id']) && isset($_SESSION['role']))
    || (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true)
);

if ($isLoggedIn) {
    // Redirect to appropriate dashboard based on role (default to user dashboard)
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'provider') {
        header('Location: provider-dashboard.php');
    } else {
        header('Location: user-dashboard.php');
    }
    exit();
} else {
    // Not logged in: send to the root login page for this system
    header('Location: login.php');
    exit();
}
?>
