<?php
// Debug script to identify 500 error causes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Debug Information</h2>";

// Test 1: Basic PHP functionality
echo "<h3>1. PHP Version & Basic Info</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Path: " . __FILE__ . "<br>";

// Test 2: File permissions
echo "<h3>2. File Permissions</h3>";
$files_to_check = ['index.php', 'db.php', 'login.php'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "$file: EXISTS, readable: " . (is_readable($file) ? 'YES' : 'NO') . "<br>";
    } else {
        echo "$file: NOT FOUND<br>";
    }
}

// Test 3: Database connection
echo "<h3>3. Database Connection Test</h3>";
try {
    if (file_exists('db.php')) {
        require_once 'db.php';
        echo "db.php loaded successfully<br>";
        
        if (isset($db) && $db instanceof mysqli) {
            echo "Database connection: SUCCESS<br>";
            echo "MySQL Version: " . $db->server_info . "<br>";
            echo "Database Name: " . $db->database . "<br>";
        } else {
            echo "Database connection: FAILED - \$db not set or not mysqli<br>";
        }
    } else {
        echo "db.php not found<br>";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
}

// Test 4: Session functionality
echo "<h3>4. Session Test</h3>";
try {
    session_start();
    echo "Session started: SUCCESS<br>";
    echo "Session ID: " . session_id() . "<br>";
} catch (Exception $e) {
    echo "Session Error: " . $e->getMessage() . "<br>";
}

// Test 5: Required PHP extensions
echo "<h3>5. Required Extensions</h3>";
$required_extensions = ['mysqli', 'json', 'session'];
foreach ($required_extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'LOADED' : 'NOT LOADED') . "<br>";
}

// Test 6: Error log location
echo "<h3>6. Error Logging</h3>";
echo "Error Log: " . ini_get('error_log') . "<br>";
echo "Log Errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";

// Test 7: Memory and limits
echo "<h3>7. PHP Limits</h3>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";

echo "<h3>8. Recent Errors</h3>";
if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "Last Error: " . $last_error['message'] . " in " . $last_error['file'] . " on line " . $last_error['line'] . "<br>";
    } else {
        echo "No recent errors detected<br>";
    }
}

echo "<hr><p><strong>If you see this page, PHP is working. Check the sections above for any FAILED or NOT LOADED items.</strong></p>";
?>









