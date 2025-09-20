<?php
// Enhanced Security configuration and headers
require_once 'db.php';

// Set security headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self'; frame-src https://www.google.com;");
    
    // Prevent caching of sensitive pages
    if (basename($_SERVER['PHP_SELF']) === 'login.php' || basename($_SERVER['PHP_SELF']) === 'auth.php') {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    // Additional security headers
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// CSRF protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting for login attempts
function checkLoginRateLimit($username) {
    $attempts_file = 'login_attempts.json';
    $max_attempts = 5;
    $lockout_time = 900; // 15 minutes
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    } else {
        $attempts = [];
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = $ip . '_' . $username;
    
    if (isset($attempts[$key])) {
        if ($attempts[$key]['count'] >= $max_attempts && 
            (time() - $attempts[$key]['time']) < $lockout_time) {
            return false; // Still locked out
        } elseif ((time() - $attempts[$key]['time']) >= $lockout_time) {
            unset($attempts[$key]); // Reset after lockout period
        }
    }
    
    return true;
}

function recordLoginAttempt($username, $success) {
    $attempts_file = 'login_attempts.json';
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = $ip . '_' . $username;
    
    if (file_exists($attempts_file)) {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    } else {
        $attempts = [];
    }
    
    if ($success) {
        unset($attempts[$key]); // Clear attempts on successful login
    } else {
        if (!isset($attempts[$key])) {
            $attempts[$key] = ['count' => 0, 'time' => time()];
        }
        $attempts[$key]['count']++;
        $attempts[$key]['time'] = time();
    }
    
    file_put_contents($attempts_file, json_encode($attempts));
}

// Input sanitization
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Enhanced login security functions
function checkAccountLockout($username) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT lockout_until, failed_attempts FROM user_security WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['lockout_until'] && strtotime($row['lockout_until']) > time()) {
            return ['locked' => true, 'until' => $row['lockout_until']];
        }
        return ['locked' => false, 'attempts' => $row['failed_attempts']];
    }
    
    return ['locked' => false, 'attempts' => 0];
}

function recordLoginAttemptDB($username, $success, $ip_address = null) {
    global $mysqli;
    
    if (!$ip_address) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    $max_attempts = 5;
    $lockout_duration = 900; // 15 minutes
    
    if ($success) {
        // Reset failed attempts on successful login
        $stmt = $mysqli->prepare("UPDATE user_security SET failed_attempts = 0, lockout_until = NULL, last_login = NOW() WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        // Log successful login
        $stmt = $mysqli->prepare("INSERT INTO login_logs (username, ip_address, success, user_agent, created_at) VALUES (?, ?, 1, ?, NOW())");
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param("sss", $username, $ip_address, $user_agent);
        $stmt->execute();
    } else {
        // Check if user security record exists
        $stmt = $mysqli->prepare("SELECT id, failed_attempts FROM user_security WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $new_attempts = $row['failed_attempts'] + 1;
            $lockout_until = null;
            
            if ($new_attempts >= $max_attempts) {
                $lockout_until = date('Y-m-d H:i:s', time() + $lockout_duration);
            }
            
            $stmt = $mysqli->prepare("UPDATE user_security SET failed_attempts = ?, lockout_until = ?, last_failed_login = NOW() WHERE username = ?");
            $stmt->bind_param("iss", $new_attempts, $lockout_until, $username);
            $stmt->execute();
        } else {
            // Create new security record
            $stmt = $mysqli->prepare("INSERT INTO user_security (username, failed_attempts, last_failed_login, created_at) VALUES (?, 1, NOW(), NOW())");
            $stmt->bind_param("s", $username);
            $stmt->execute();
        }
        
        // Log failed login attempt
        $stmt = $mysqli->prepare("INSERT INTO login_logs (username, ip_address, success, user_agent, created_at) VALUES (?, ?, 0, ?, NOW())");
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param("sss", $username, $ip_address, $user_agent);
        $stmt->execute();
    }
}

// IP-based rate limiting
function checkIPRateLimit($ip_address = null) {
    global $mysqli;
    
    if (!$ip_address) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    $time_window = 300; // 5 minutes
    $max_attempts = 10;
    
    $stmt = $mysqli->prepare("SELECT COUNT(*) as attempts FROM login_logs WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND) AND success = 0");
    $stmt->bind_param("si", $ip_address, $time_window);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['attempts'] < $max_attempts;
}

// Device fingerprinting
function getDeviceFingerprint() {
    $components = [
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
        $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        $_SERVER['HTTP_ACCEPT'] ?? ''
    ];
    
    return hash('sha256', implode('|', $components));
}

// Suspicious activity detection
function detectSuspiciousActivity($username, $ip_address = null) {
    global $mysqli;
    
    if (!$ip_address) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    $suspicious = false;
    $reasons = [];
    
    // Check for multiple IPs for same user in short time
    $stmt = $mysqli->prepare("SELECT COUNT(DISTINCT ip_address) as ip_count FROM login_logs WHERE username = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['ip_count'] > 3) {
        $suspicious = true;
        $reasons[] = 'Multiple IP addresses';
    }
    
    // Check for rapid login attempts
    $stmt = $mysqli->prepare("SELECT COUNT(*) as attempts FROM login_logs WHERE username = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['attempts'] > 5) {
        $suspicious = true;
        $reasons[] = 'Rapid login attempts';
    }
    
    return ['suspicious' => $suspicious, 'reasons' => $reasons];
}

// Password strength validation
function validatePasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    return $errors;
}

// Session security (less aggressive for dashboard access)
function secureSession() {
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes instead of 5
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    // Check session timeout (2 hours instead of 1)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
    
    // Only validate fingerprint on login pages, not dashboard pages
    $current_page = basename($_SERVER['PHP_SELF']);
    $sensitive_pages = ['login.php', 'verify_2fa.php', 'setup_2fa.php'];
    
    if (in_array($current_page, $sensitive_pages)) {
        // Validate session fingerprint only on sensitive pages
        $current_fingerprint = getDeviceFingerprint();
        if (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] !== $current_fingerprint) {
            session_destroy();
            header('Location: login.php?security=1');
            exit();
        }
        $_SESSION['fingerprint'] = $current_fingerprint;
    }
}

// Clean old security records
function cleanupSecurityRecords() {
    global $mysqli;
    
    // Clean old login logs (keep 30 days)
    $mysqli->query("DELETE FROM login_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    // Reset expired lockouts
    $mysqli->query("UPDATE user_security SET failed_attempts = 0, lockout_until = NULL WHERE lockout_until < NOW()");
}

// Initialize security
function initializeSecurity() {
    // Start secure session
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
    
    // Set security headers
    setSecurityHeaders();
    
    // Secure existing sessions
    if (isset($_SESSION['user_id'])) {
        secureSession();
    }
    
    // Cleanup old records (run occasionally)
    if (rand(1, 100) === 1) {
        cleanupSecurityRecords();
    }
}

// Set security headers on all pages and initialize security
initializeSecurity();
?>
