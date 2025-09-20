<?php
require_once 'security.php';
require_once 'two_factor_auth.php';
include 'db.php';

// Check if user is in 2FA verification state
if (!isset($_SESSION['pending_2fa_user']) || !isset($_SESSION['pending_2fa_role'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['pending_2fa_user'];
$role = $_SESSION['pending_2fa_role'];
$error = '';

$twoFA = new TwoFactorAuth($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitizeInput($_POST['verification_code']);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error = "Security token validation failed. Please try again.";
    } elseif (empty($code)) {
        $error = "Please enter the verification code.";
    } elseif ($twoFA->verify2FA($username, $code)) {
        // 2FA verification successful
        $_SESSION['user_id'] = $_SESSION['pending_2fa_user_id'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        $_SESSION['login_time'] = time();
        $_SESSION['2fa_verified'] = true;
        
        // Clear pending 2FA session data
        unset($_SESSION['pending_2fa_user']);
        unset($_SESSION['pending_2fa_role']);
        unset($_SESSION['pending_2fa_user_id']);
        
        // Log successful 2FA verification
        $stmt = $mysqli->prepare("INSERT INTO security_events (event_type, severity, username, ip_address, user_agent, description) VALUES ('login_attempt', 'low', ?, ?, ?, '2FA verification successful')");
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param("sss", $username, $ip, $user_agent);
        $stmt->execute();
        
        // Redirect based on role
        if ($role === "admin") {
            header("Location: admin.php");
            exit();
        } elseif ($role === "provider") {
            header("Location: provider-dashboard.php");
            exit();
        } else {
            header("Location: user-dashboard.php");
            exit();
        }
    } else {
        $error = "Invalid verification code. Please try again.";
        
        // Log failed 2FA attempt
        $stmt = $mysqli->prepare("INSERT INTO security_events (event_type, severity, username, ip_address, user_agent, description) VALUES ('login_attempt', 'medium', ?, ?, ?, '2FA verification failed')");
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param("sss", $username, $ip, $user_agent);
        $stmt->execute();
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - SLATE System</title>
    
    <!-- Universal Dark Mode Styles -->
    <?php include 'includes/dark-mode-styles.php'; ?>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: white;
            line-height: 1.6;
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .verification-container {
            width: 100%;
            max-width: 400px;
            background: rgba(31, 42, 56, 0.8);
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 0.625rem 1.875rem rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .verification-container img {
            width: 4rem;
            height: auto;
            margin-bottom: 1rem;
        }

        .verification-container h2 {
            margin-bottom: 1rem;
            color: #ffffff;
            font-size: 1.5rem;
        }

        .verification-container p {
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            color: #ff6b6b;
            font-size: 0.875rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .verification-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .code-input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            color: white;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 0.2rem;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: #00c6ff;
            box-shadow: 0 0 0 0.125rem rgba(0, 198, 255, 0.2);
        }

        .code-input::placeholder {
            color: rgba(160, 160, 160, 0.8);
            letter-spacing: normal;
        }

        .verify-button {
            padding: 0.75rem;
            background: linear-gradient(to right, #0072ff, #00c6ff);
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .verify-button:hover {
            background: linear-gradient(to right, #0052cc, #009ee3);
            transform: translateY(-0.125rem);
            box-shadow: 0 0.3125rem 0.9375rem rgba(0, 0, 0, 0.2);
        }

        .verify-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .backup-link {
            margin-top: 1rem;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .backup-link a {
            color: #00c6ff;
            text-decoration: none;
            font-weight: 500;
        }

        .backup-link a:hover {
            text-decoration: underline;
        }

        .security-info {
            background: rgba(0, 198, 255, 0.1);
            border: 1px solid rgba(0, 198, 255, 0.3);
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .spinner {
            display: none;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        footer {
            text-align: center;
            padding: 1.25rem;
            background: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }

        @media (max-width: 30rem) {
            .main-container {
                padding: 1rem;
            }
            
            .verification-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="verification-container">
            <img src="slatelogo.png" alt="SLATE Logo">
            <h2>Two-Factor Authentication</h2>
            <p>Enter the 6-digit code from your authenticator app to complete login.</p>
            
            <div class="security-info">
                <strong>Security Notice:</strong> This additional step helps protect your account from unauthorized access.
            </div>
            
            <!-- Error Message -->
            <div id="errorMessage" class="error-message">
                <span id="errorText"></span>
            </div>
            
            <form class="verification-form" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="text" 
                       name="verification_code" 
                       id="verification_code" 
                       class="code-input"
                       placeholder="000000" 
                       maxlength="6" 
                       pattern="[0-9]{6}"
                       autocomplete="one-time-code"
                       required>
                <button type="submit" id="verifyButton" class="verify-button">
                    <span class="spinner" id="spinner"></span>
                    <span id="buttonText">Verify Code</span>
                </button>
            </form>
            
            <div class="backup-link">
                Lost your device? <a href="#" onclick="showBackupForm()">Use backup code</a>
            </div>
            
            <div class="backup-link" style="margin-top: 0.5rem;">
                <a href="login.php">← Back to Login</a>
            </div>
        </div>
    </div>

    <footer>
        &copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.
    </footer>

    <script>
        // Add current year to footer
        document.getElementById('currentYear').textContent = new Date().getFullYear();
        
        // Show error message if present
        <?php if (!empty($error)): ?>
        document.getElementById('errorText').textContent = '<?php echo addslashes($error); ?>';
        document.getElementById('errorMessage').classList.add('show');
        <?php endif; ?>
        
        // Auto-focus on code input
        document.getElementById('verification_code').focus();
        
        // Only allow numbers in verification code
        document.getElementById('verification_code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            document.getElementById('errorMessage').classList.remove('show');
        });
        
        // Handle form submission
        document.querySelector('.verification-form').addEventListener('submit', function(e) {
            const button = document.getElementById('verifyButton');
            const spinner = document.getElementById('spinner');
            const buttonText = document.getElementById('buttonText');
            
            button.disabled = true;
            spinner.style.display = 'inline-block';
            buttonText.textContent = 'Verifying...';
        });
        
        // Show backup code form
        function showBackupForm() {
            const codeInput = document.getElementById('verification_code');
            codeInput.placeholder = 'Backup Code';
            codeInput.maxLength = 6;
            codeInput.value = '';
            codeInput.focus();
            
            document.querySelector('.verification-container p').textContent = 'Enter one of your backup codes to complete login.';
        }
        
        // Auto-submit when 6 digits entered
        document.getElementById('verification_code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                // Small delay to show the complete code
                setTimeout(() => {
                    this.form.submit();
                }, 500);
            }
        });
    </script>
</body>
</html>
