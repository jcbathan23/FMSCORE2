<?php
// Debug Session Script
session_start();
require_once 'auth.php';

echo "<h2>Session Debug Information</h2>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Authentication Status:</h3>";
echo "isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "<br>";
echo "isAdmin(): " . (isAdmin() ? 'TRUE' : 'FALSE') . "<br>";
echo "isProvider(): " . (isProvider() ? 'TRUE' : 'FALSE') . "<br>";
echo "isUser(): " . (isUser() ? 'TRUE' : 'FALSE') . "<br>";

echo "<h3>Session Variables:</h3>";
echo "user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "<br>";
echo "username: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'NOT SET') . "<br>";
echo "role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'NOT SET') . "<br>";
echo "login_time: " . (isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'NOT SET') . "<br>";

echo "<h3>Test Dashboard Access:</h3>";
echo "<a href='admin.php'>Test Admin Dashboard</a><br>";
echo "<a href='user-dashboard.php'>Test User Dashboard</a><br>";
echo "<a href='modules/dashboard/dashboard.php'>Test Admin Module Dashboard</a><br>";

echo "<h3>Session Timeout Check:</h3>";
if (isset($_SESSION['login_time'])) {
    $timeout = 8 * 60 * 60; // 8 hours
    $timeElapsed = time() - $_SESSION['login_time'];
    $timeRemaining = $timeout - $timeElapsed;
    echo "Time elapsed since login: " . $timeElapsed . " seconds<br>";
    echo "Time remaining: " . $timeRemaining . " seconds<br>";
    echo "Session expires at: " . date('Y-m-d H:i:s', $_SESSION['login_time'] + $timeout) . "<br>";
    
    if ($timeElapsed > $timeout) {
        echo "<strong style='color: red;'>SESSION EXPIRED!</strong><br>";
    } else {
        echo "<strong style='color: green;'>Session is valid</strong><br>";
    }
}

echo "<h3>Quick Actions:</h3>";
echo "<a href='login.php'>Go to Login</a> | ";
echo "<a href='login.php?logout=1'>Logout</a> | ";
echo "<a href='test_dashboard_access.php'>Dashboard Test</a>";
?>
