<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

function sendResponse($data) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['success' => false, 'message' => 'Method not allowed']);
}

// Use shared DB connection to avoid divergence and ensure consistent config
require_once __DIR__ . '/../db.php';

if (!isset($mysqli) || !$mysqli instanceof mysqli || $mysqli->connect_error) {
    sendResponse(['success' => false, 'message' => 'Database connection failed']);
}

// Ensure base database exists (in case connection was made without DB)
@$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
@$mysqli->select_db(DB_NAME);
if ($mysqli->error) {
    sendResponse(['success' => false, 'message' => 'Database selection failed']);
}

// Inputs
$role = $_POST['role'] ?? '';
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $email === '' || $password === '' || $role === '') {
    sendResponse(['success' => false, 'message' => 'All required fields must be filled']);
}

if (strlen($password) < 6) {
    sendResponse(['success' => false, 'message' => 'Password must be at least 6 characters long']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(['success' => false, 'message' => 'Invalid email format']);
}

if (!in_array($role, ['user', 'provider'], true)) {
    sendResponse(['success' => false, 'message' => 'Invalid role specified']);
}

try {
    // Ensure tables exist
    $createUsersTable = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        role ENUM('admin', 'user', 'provider') NOT NULL DEFAULT 'user',
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$mysqli->query($createUsersTable)) {
        sendResponse(['success' => false, 'message' => 'Failed to create users table']);
    }

    $mysqli->begin_transaction();

    // Check username exists without get_result (avoid mysqlnd dependency)
    $checkUsername = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    if (!$checkUsername) {
        $mysqli->rollback();
        sendResponse(['success' => false, 'message' => 'Database error (prepare username)']);
    }
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    $checkUsername->store_result();
    if ($checkUsername->num_rows > 0) {
        $mysqli->rollback();
        sendResponse(['success' => false, 'message' => 'Username already exists']);
    }

    // Check email exists
    $checkEmail = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    if (!$checkEmail) {
        $mysqli->rollback();
        sendResponse(['success' => false, 'message' => 'Database error (prepare email)']);
    }
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        $mysqli->rollback();
        sendResponse(['success' => false, 'message' => 'Email already exists']);
    }

    // Insert user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertUser = $mysqli->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)");
    if (!$insertUser) {
        $mysqli->rollback();
        sendResponse(['success' => false, 'message' => 'Failed to prepare user insert']);
    }
    $insertUser->bind_param("ssss", $username, $passwordHash, $email, $role);
    if (!$insertUser->execute()) {
        $mysqli->rollback();
        sendResponse(['success' => false, 'message' => 'Failed to create user account']);
    }
    $userId = $mysqli->insert_id;

    if ($role === 'user') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $company = trim($_POST['company'] ?? '');
        if ($firstName === '' || $lastName === '' || $phone === '') {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'First name, last name, and phone are required']);
        }
        $createProfiles = "CREATE TABLE IF NOT EXISTS user_profiles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            company VARCHAR(255) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$mysqli->query($createProfiles)) {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to create profiles table']);
        }
        $insertProfile = $mysqli->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, phone, company) VALUES (?, ?, ?, ?, ?)");
        if (!$insertProfile) {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to prepare profile insert']);
        }
        $insertProfile->bind_param("issss", $userId, $firstName, $lastName, $phone, $company);
        if (!$insertProfile->execute()) {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to create user profile']);
        }
    } elseif ($role === 'provider') {
        $companyName = trim($_POST['company_name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $serviceArea = trim($_POST['service_area'] ?? '');
        $serviceType = trim($_POST['service_type'] ?? '');
        $monthlyRate = floatval($_POST['monthly_rate'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        if ($companyName === '' || $contactPerson === '' || $contactPhone === '' || $serviceArea === '' || $serviceType === '') {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'All provider fields are required']);
        }
        $createProviders = "CREATE TABLE IF NOT EXISTS providers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            contact_person VARCHAR(255) NOT NULL,
            contact_email VARCHAR(255) NOT NULL,
            contact_phone VARCHAR(50) NOT NULL,
            service_area VARCHAR(255) NOT NULL,
            monthly_rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status VARCHAR(50) NOT NULL DEFAULT 'Pending',
            contract_start DATE NOT NULL,
            contract_end DATE NOT NULL,
            notes TEXT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        if (!$mysqli->query($createProviders)) {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to create providers table']);
        }
        $insertProvider = $mysqli->prepare("INSERT INTO providers (name, type, contact_person, contact_email, contact_phone, service_area, monthly_rate, status, contract_start, contract_end, notes) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), ?)");
        if (!$insertProvider) {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to prepare provider insert']);
        }
        // monthly_rate is decimal -> bind as string to avoid double precision quirks
        $monthlyRateStr = number_format($monthlyRate, 2, '.', '');
        $insertProvider->bind_param("ssssssss", $companyName, $serviceType, $contactPerson, $email, $contactPhone, $serviceArea, $monthlyRateStr, $notes);
        if (!$insertProvider->execute()) {
            $mysqli->rollback();
            sendResponse(['success' => false, 'message' => 'Failed to create provider profile']);
        }
    }

    $mysqli->commit();
    sendResponse(['success' => true, 'message' => 'Successful registration!']);
} catch (Throwable $e) {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        @$mysqli->rollback();
    }
    sendResponse(['success' => false, 'message' => 'Registration failed: unexpected error']);
}
?>
