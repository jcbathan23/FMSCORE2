<?php
// Login Test Script - Use this to test the login flow
session_start();
require_once 'auth.php';

echo "<h2>🔐 Login Flow Test</h2>";

if (isLoggedIn()) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ You are logged in!</h3>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($_SESSION['username']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($_SESSION['role']) . "</p>";
    echo "<p><strong>User ID:</strong> " . htmlspecialchars($_SESSION['user_id']) . "</p>";
    echo "</div>";
    
    echo "<h3>🎯 Test Dashboard Access:</h3>";
    
    // Test based on role
    if ($_SESSION['role'] === 'admin') {
        echo "<p>As an admin, you should be able to access:</p>";
        echo "<ul>";
        echo "<li><a href='admin.php' target='_blank'>Admin Dashboard</a> (should redirect to modules/dashboard/dashboard.php)</li>";
        echo "<li><a href='modules/dashboard/dashboard.php' target='_blank'>Admin Module Dashboard</a> (direct access)</li>";
        echo "<li><a href='user-dashboard.php' target='_blank'>User Dashboard</a> (admins can access)</li>";
        echo "<li><a href='provider-dashboard.php' target='_blank'>Provider Dashboard</a> (admins can access)</li>";
        echo "</ul>";
    } elseif ($_SESSION['role'] === 'provider') {
        echo "<p>As a provider, you should be able to access:</p>";
        echo "<ul>";
        echo "<li><a href='provider-dashboard.php' target='_blank'>Provider Dashboard</a></li>";
        echo "<li><a href='schedules.php' target='_blank'>Schedules</a></li>";
        echo "<li><a href='service-network.php' target='_blank'>Service Network</a></li>";
        echo "<li><a href='service-provider.php' target='_blank'>Service Provider</a></li>";
        echo "</ul>";
    } elseif ($_SESSION['role'] === 'user') {
        echo "<p>As a user, you should be able to access:</p>";
        echo "<ul>";
        echo "<li><a href='user-dashboard.php' target='_blank'>User Dashboard</a></li>";
        echo "<li><a href='schedules.php' target='_blank'>Schedules</a></li>";
        echo "<li><a href='service-network.php' target='_blank'>Service Network</a></li>";
        echo "<li><a href='service-provider.php' target='_blank'>Service Provider</a></li>";
        echo "</ul>";
    }
    
    echo "<h3>🔧 Debugging Tools:</h3>";
    echo "<ul>";
    echo "<li><a href='debug_session.php' target='_blank'>Session Debug Info</a></li>";
    echo "<li><a href='test_dashboard_access.php' target='_blank'>Dashboard Access Test</a></li>";
    echo "</ul>";
    
    echo "<h3>🚪 Logout:</h3>";
    echo "<a href='login.php?logout=1' style='background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Logout</a>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ You are not logged in</h3>";
    echo "<p>Please login first to test dashboard access.</p>";
    echo "</div>";
    
    echo "<h3>🔑 Login Options:</h3>";
    echo "<a href='login.php' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
}

echo "<hr>";
echo "<h3>📊 Current Session Status:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Session Variable</th><th>Value</th></tr>";
echo "<tr><td>user_id</td><td>" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "</td></tr>";
echo "<tr><td>username</td><td>" . (isset($_SESSION['username']) ? $_SESSION['username'] : 'Not set') . "</td></tr>";
echo "<tr><td>role</td><td>" . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . "</td></tr>";
echo "<tr><td>login_time</td><td>" . (isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'Not set') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<h3>🔍 Authentication Function Results:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Function</th><th>Result</th></tr>";
echo "<tr><td>isLoggedIn()</td><td>" . (isLoggedIn() ? '✅ TRUE' : '❌ FALSE') . "</td></tr>";
echo "<tr><td>isAdmin()</td><td>" . (isAdmin() ? '✅ TRUE' : '❌ FALSE') . "</td></tr>";
echo "<tr><td>isProvider()</td><td>" . (isProvider() ? '✅ TRUE' : '❌ FALSE') . "</td></tr>";
echo "<tr><td>isUser()</td><td>" . (isUser() ? '✅ TRUE' : '❌ FALSE') . "</td></tr>";
echo "</table>";
?>
