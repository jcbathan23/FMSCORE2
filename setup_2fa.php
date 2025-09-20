<?php
require_once 'security.php';
require_once 'two_factor_auth.php';
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$twoFA = new TwoFactorAuth($mysqli);
$error = '';
$success = '';

// Check if 2FA is already enabled
$is2FAEnabled = $twoFA->is2FAEnabled($username);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error = "Security token validation failed. Please try again.";
    } elseif ($action === 'enable') {
        if ($is2FAEnabled) {
            $error = "Two-factor authentication is already enabled.";
        } else {
            // Enable 2FA
            $result = $twoFA->enable2FA($username);
            $_SESSION['2fa_setup'] = $result;
            header('Location: setup_2fa.php?step=verify');
            exit();
        }
    } elseif ($action === 'disable') {
        if (!$is2FAEnabled) {
            $error = "Two-factor authentication is not enabled.";
        } else {
            $password = $_POST['password'] ?? '';
            
            // Verify password before disabling 2FA
            $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password_hash'])) {
                    if ($twoFA->disable2FA($username)) {
                        $success = "Two-factor authentication has been disabled.";
                        $is2FAEnabled = false;
                        
                        // Log security event
                        $stmt = $mysqli->prepare("INSERT INTO security_events (event_type, severity, username, ip_address, user_agent, description) VALUES ('password_change', 'medium', ?, ?, ?, '2FA disabled')");
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                        $stmt->bind_param("sss", $username, $ip, $user_agent);
                        $stmt->execute();
                    } else {
                        $error = "Failed to disable two-factor authentication.";
                    }
                } else {
                    $error = "Invalid password.";
                }
            }
        }
    } elseif ($action === 'verify_setup') {
        $verification_code = $_POST['verification_code'] ?? '';
        
        if (isset($_SESSION['2fa_setup']) && $twoFA->verify2FA($username, $verification_code)) {
            $success = "Two-factor authentication has been successfully enabled!";
            unset($_SESSION['2fa_setup']);
            $is2FAEnabled = true;
            
            // Log security event
            $stmt = $mysqli->prepare("INSERT INTO security_events (event_type, severity, username, ip_address, user_agent, description) VALUES ('password_change', 'low', ?, ?, ?, '2FA enabled')");
            $ip = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt->bind_param("sss", $username, $ip, $user_agent);
            $stmt->execute();
        } else {
            $error = "Invalid verification code. Please try again.";
        }
    }
}

$csrf_token = generateCSRFToken();
$step = $_GET['step'] ?? 'main';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication Setup - SLATE System</title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: white;
            line-height: 1.6;
            padding: 2rem;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(31, 42, 56, 0.8);
            border-radius: 0.75rem;
            padding: 2rem;
            box-shadow: 0 0.625rem 1.875rem rgba(0, 0, 0, 0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header img {
            width: 4rem;
            height: auto;
            margin-bottom: 1rem;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .status-enabled {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #28a745;
        }

        .status-disabled {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }

        .message {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ff6b6b;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #28a745;
        }

        .info-message {
            background: rgba(0, 198, 255, 0.1);
            border: 1px solid rgba(0, 198, 255, 0.3);
            color: rgba(255, 255, 255, 0.9);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            color: white;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00c6ff;
            box-shadow: 0 0 0 0.125rem rgba(0, 198, 255, 0.2);
        }

        .button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 1rem;
            margin-bottom: 0.5rem;
        }

        .button-primary {
            background: linear-gradient(to right, #0072ff, #00c6ff);
            color: white;
        }

        .button-primary:hover {
            background: linear-gradient(to right, #0052cc, #009ee3);
            transform: translateY(-0.125rem);
        }

        .button-danger {
            background: linear-gradient(to right, #dc3545, #c82333);
            color: white;
        }

        .button-danger:hover {
            background: linear-gradient(to right, #c82333, #bd2130);
            transform: translateY(-0.125rem);
        }

        .button-secondary {
            background: rgba(108, 117, 125, 0.8);
            color: white;
        }

        .button-secondary:hover {
            background: rgba(108, 117, 125, 1);
        }

        .qr-container {
            text-align: center;
            margin: 2rem 0;
        }

        .qr-container img {
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            background: white;
        }

        .secret-key {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            word-break: break-all;
            text-align: center;
        }

        .backup-codes {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            padding: 1rem;
            margin: 1rem 0;
        }

        .backup-code {
            font-family: monospace;
            font-size: 1.1rem;
            margin: 0.5rem 0;
            padding: 0.25rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.25rem;
            text-align: center;
        }

        .warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.5);
            color: #ffc107;
            padding: 1rem;
            border-radius: 0.375rem;
            margin: 1rem 0;
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link a {
            color: #00c6ff;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="slatelogo.png" alt="SLATE Logo">
            <h1>Two-Factor Authentication</h1>
            <div class="status-badge <?php echo $is2FAEnabled ? 'status-enabled' : 'status-disabled'; ?>">
                <?php echo $is2FAEnabled ? '✓ Enabled' : '✗ Disabled'; ?>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($step === 'main'): ?>
            <div class="message info-message">
                <strong>What is Two-Factor Authentication?</strong><br>
                Two-factor authentication (2FA) adds an extra layer of security to your account by requiring a second form of verification in addition to your password.
            </div>

            <?php if (!$is2FAEnabled): ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="enable">
                    
                    <p style="margin-bottom: 1.5rem;">
                        Enable two-factor authentication to secure your account with time-based codes from your mobile device.
                    </p>
                    
                    <button type="submit" class="button button-primary">Enable 2FA</button>
                </form>
            <?php else: ?>
                <p style="margin-bottom: 1.5rem;">
                    Two-factor authentication is currently enabled for your account. To disable it, enter your password below.
                </p>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="action" value="disable">
                    
                    <div class="form-group">
                        <label for="password">Current Password:</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    
                    <div class="warning">
                        <strong>Warning:</strong> Disabling 2FA will make your account less secure.
                    </div>
                    
                    <button type="submit" class="button button-danger">Disable 2FA</button>
                </form>
            <?php endif; ?>

        <?php elseif ($step === 'verify' && isset($_SESSION['2fa_setup'])): ?>
            <?php $setup = $_SESSION['2fa_setup']; ?>
            
            <h2>Setup Two-Factor Authentication</h2>
            
            <div class="message info-message">
                <strong>Step 1:</strong> Install an authenticator app like Google Authenticator, Authy, or Microsoft Authenticator on your mobile device.
            </div>
            
            <div class="message info-message">
                <strong>Step 2:</strong> Scan the QR code below or manually enter the secret key.
            </div>
            
            <div class="qr-container">
                <img src="<?php echo htmlspecialchars($setup['qr_url']); ?>" alt="QR Code">
            </div>
            
            <div class="secret-key">
                <strong>Secret Key:</strong><br>
                <?php echo htmlspecialchars($setup['secret']); ?>
            </div>
            
            <div class="message info-message">
                <strong>Step 3:</strong> Save these backup codes in a secure location. You can use them if you lose access to your authenticator device.
            </div>
            
            <div class="backup-codes">
                <strong>Backup Codes:</strong>
                <?php foreach ($setup['backup_codes'] as $code): ?>
                    <div class="backup-code"><?php echo htmlspecialchars($code); ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="message info-message">
                <strong>Step 4:</strong> Enter the 6-digit code from your authenticator app to verify the setup.
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="action" value="verify_setup">
                
                <div class="form-group">
                    <label for="verification_code">Verification Code:</label>
                    <input type="text" name="verification_code" id="verification_code" maxlength="6" pattern="[0-9]{6}" required>
                </div>
                
                <button type="submit" class="button button-primary">Verify & Enable 2FA</button>
                <a href="setup_2fa.php" class="button button-secondary">Cancel</a>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin.php' : ($_SESSION['role'] === 'provider' ? 'provider-dashboard.php' : 'user-dashboard.php'); ?>">← Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Only allow numbers in verification code
        const codeInput = document.getElementById('verification_code');
        if (codeInput) {
            codeInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    </script>
</body>
</html>
