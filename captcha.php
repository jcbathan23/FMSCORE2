<?php
// Session is already started by security.php

// Simple math CAPTCHA generator
function generateMathCaptcha() {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operations = ['+', '-', '*'];
    $operation = $operations[array_rand($operations)];
    
    switch ($operation) {
        case '+':
            $answer = $num1 + $num2;
            break;
        case '-':
            // Ensure positive result
            if ($num1 < $num2) {
                $temp = $num1;
                $num1 = $num2;
                $num2 = $temp;
            }
            $answer = $num1 - $num2;
            break;
        case '*':
            $answer = $num1 * $num2;
            break;
    }
    
    $_SESSION['captcha_answer'] = $answer;
    return "$num1 $operation $num2 = ?";
}

function validateCaptcha($user_answer) {
    if (!isset($_SESSION['captcha_answer'])) {
        return false;
    }
    
    $correct = $_SESSION['captcha_answer'] == $user_answer;
    unset($_SESSION['captcha_answer']); // Clear after validation
    return $correct;
}

// Generate image CAPTCHA (simple text-based)
function generateImageCaptcha() {
    $width = 120;
    $height = 40;
    $image = imagecreate($width, $height);
    
    // Colors
    $bg_color = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    $line_color = imagecolorallocate($image, 64, 64, 64);
    
    // Generate random string
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $captcha_string = '';
    for ($i = 0; $i < 5; $i++) {
        $captcha_string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    $_SESSION['captcha_text'] = $captcha_string;
    
    // Add some noise lines
    for ($i = 0; $i < 5; $i++) {
        imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
    }
    
    // Add text
    imagestring($image, 5, 25, 10, $captcha_string, $text_color);
    
    // Output image
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
}

function validateImageCaptcha($user_input) {
    if (!isset($_SESSION['captcha_text'])) {
        return false;
    }
    
    $correct = strtoupper($_SESSION['captcha_text']) === strtoupper($user_input);
    unset($_SESSION['captcha_text']); // Clear after validation
    return $correct;
}

// Handle CAPTCHA generation request
if (isset($_GET['action']) && $_GET['action'] === 'image') {
    generateImageCaptcha();
    exit;
}
?>
