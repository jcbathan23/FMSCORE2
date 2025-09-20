<?php
// Two-Factor Authentication Implementation
require_once 'security.php';

class TwoFactorAuth {
    private $mysqli;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    // Generate a secret key for TOTP
    public function generateSecret($length = 32) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }
    
    // Generate backup codes
    public function generateBackupCodes($count = 8) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }
        return $codes;
    }
    
    // Enable 2FA for a user
    public function enable2FA($username) {
        $secret = $this->generateSecret();
        $backupCodes = $this->generateBackupCodes();
        
        // Check if user already has 2FA record
        $stmt = $this->mysqli->prepare("SELECT id FROM user_security WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $stmt = $this->mysqli->prepare("UPDATE user_security SET two_factor_enabled = 1, two_factor_secret = ?, backup_codes = ? WHERE username = ?");
            $backupCodesJson = json_encode($backupCodes);
            $stmt->bind_param("sss", $secret, $backupCodesJson, $username);
        } else {
            // Create new record
            $stmt = $this->mysqli->prepare("INSERT INTO user_security (username, two_factor_enabled, two_factor_secret, backup_codes, created_at) VALUES (?, 1, ?, ?, NOW())");
            $backupCodesJson = json_encode($backupCodes);
            $stmt->bind_param("sss", $username, $secret, $backupCodesJson);
        }
        
        $stmt->execute();
        
        return [
            'secret' => $secret,
            'backup_codes' => $backupCodes,
            'qr_url' => $this->getQRCodeURL($username, $secret)
        ];
    }
    
    // Disable 2FA for a user
    public function disable2FA($username) {
        $stmt = $this->mysqli->prepare("UPDATE user_security SET two_factor_enabled = 0, two_factor_secret = NULL, backup_codes = NULL WHERE username = ?");
        $stmt->bind_param("s", $username);
        return $stmt->execute();
    }
    
    // Check if user has 2FA enabled
    public function is2FAEnabled($username) {
        $stmt = $this->mysqli->prepare("SELECT two_factor_enabled FROM user_security WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (bool)$row['two_factor_enabled'];
        }
        
        return false;
    }
    
    // Get user's 2FA secret
    public function getSecret($username) {
        $stmt = $this->mysqli->prepare("SELECT two_factor_secret FROM user_security WHERE username = ? AND two_factor_enabled = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['two_factor_secret'];
        }
        
        return null;
    }
    
    // Generate TOTP code (Time-based One-Time Password)
    public function generateTOTP($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        
        $secretkey = $this->base32Decode($secret);
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        $offset = ord(substr($hm, -1)) & 0x0F;
        $hashpart = substr($hm, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }
    
    // Verify TOTP code
    public function verifyTOTP($secret, $code, $discrepancy = 1) {
        $currentTimeSlice = floor(time() / 30);
        
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->generateTOTP($secret, $currentTimeSlice + $i);
            if ($calculatedCode === $code) {
                return true;
            }
        }
        
        return false;
    }
    
    // Verify backup code
    public function verifyBackupCode($username, $code) {
        $stmt = $this->mysqli->prepare("SELECT backup_codes FROM user_security WHERE username = ? AND two_factor_enabled = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $backupCodes = json_decode($row['backup_codes'], true);
            
            if (in_array($code, $backupCodes)) {
                // Remove used backup code
                $backupCodes = array_diff($backupCodes, [$code]);
                $backupCodesJson = json_encode(array_values($backupCodes));
                
                $stmt = $this->mysqli->prepare("UPDATE user_security SET backup_codes = ? WHERE username = ?");
                $stmt->bind_param("ss", $backupCodesJson, $username);
                $stmt->execute();
                
                return true;
            }
        }
        
        return false;
    }
    
    // Verify 2FA code (TOTP or backup code)
    public function verify2FA($username, $code) {
        $secret = $this->getSecret($username);
        
        if (!$secret) {
            return false;
        }
        
        // First try TOTP
        if ($this->verifyTOTP($secret, $code)) {
            return true;
        }
        
        // Then try backup code
        return $this->verifyBackupCode($username, $code);
    }
    
    // Get QR code URL for Google Authenticator
    public function getQRCodeURL($username, $secret, $issuer = 'SLATE System') {
        $url = "otpauth://totp/" . urlencode($issuer . ':' . $username) . "?secret=" . $secret . "&issuer=" . urlencode($issuer);
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url);
    }
    
    // Base32 decode function
    private function base32Decode($secret) {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));
        
        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        for ($i = 0; $i < 4; $i++){
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) return false;
        }
        $secret = str_replace('=','', $secret);
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i+8) {
            $x = "";
            if (!in_array($secret[$i], $base32charsFlipped)) return false;
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y:"";
            }
        }
        return $binaryString;
    }
}

// Email notification for 2FA events
function send2FANotification($username, $email, $event) {
    $subject = "SLATE System - Two-Factor Authentication " . ucfirst($event);
    $message = "Hello,\n\n";
    $message .= "Two-factor authentication has been " . $event . " for your SLATE System account.\n\n";
    $message .= "Username: " . $username . "\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $message .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n\n";
    $message .= "If you did not perform this action, please contact support immediately.\n\n";
    $message .= "Best regards,\nSLATE System Security Team";
    
    $headers = "From: security@slate-system.com\r\n";
    $headers .= "Reply-To: security@slate-system.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Note: Configure your mail server settings
    // mail($email, $subject, $message, $headers);
    
    // For now, log the notification
    error_log("2FA Notification: " . $event . " for " . $username);
}

// Simple 2FA setup page
function render2FASetupPage($username, $secret, $qrUrl, $backupCodes) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Two-Factor Authentication Setup</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .qr-code { text-align: center; margin: 20px 0; }
            .backup-codes { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .backup-code { font-family: monospace; font-size: 14px; margin: 5px 0; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Two-Factor Authentication Setup</h1>
        
        <h2>Step 1: Install Authenticator App</h2>
        <p>Install Google Authenticator, Authy, or any compatible TOTP app on your mobile device.</p>
        
        <h2>Step 2: Scan QR Code</h2>
        <div class="qr-code">
            <img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="QR Code">
            <p>Or manually enter this secret: <strong><?php echo htmlspecialchars($secret); ?></strong></p>
        </div>
        
        <h2>Step 3: Save Backup Codes</h2>
        <div class="warning">
            <strong>Important:</strong> Save these backup codes in a secure location. You can use them to access your account if you lose your authenticator device.
        </div>
        <div class="backup-codes">
            <?php foreach ($backupCodes as $code): ?>
                <div class="backup-code"><?php echo htmlspecialchars($code); ?></div>
            <?php endforeach; ?>
        </div>
        
        <form method="POST" action="verify_2fa_setup.php">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <h2>Step 4: Verify Setup</h2>
            <p>Enter the 6-digit code from your authenticator app:</p>
            <input type="text" name="verification_code" maxlength="6" required style="padding: 10px; font-size: 16px; width: 150px;">
            <button type="submit" class="button">Verify & Enable 2FA</button>
        </form>
    </body>
    </html>
    <?php
}
?>
