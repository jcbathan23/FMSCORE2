<?php
// Update this path based on where you move the file
// If moving to a subfolder: require_once '../auth.php';
// If moving to a different location: require_once 'path/to/auth.php';
require_once 'auth.php';

// Allow users and admins to access this page
if (!isset($_SESSION['user_id'])) {
    // Update login path if needed
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$isAdmin = ($userRole === 'admin');
$isUser = ($userRole === 'user');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- Universal Dark Mode Styles -->
  <!-- Update this path based on where you move the file -->
  <!-- If moving to a subfolder: <?php include '../includes/dark-mode-styles.php'; ?> -->
  <?php include 'includes/dark-mode-styles.php'; ?>
  <title>Rate & Tariff | CORE II</title>
  <style>
    :root {
      --sidebar-width: 280px;
      --primary-color: #4e73df;
      --secondary-color: #f8f9fc;
      --dark-bg: #1a1a2e;
      --dark-card: #16213e;
      --text-light: #f8f9fa;
      --text-dark: #212529;
      --success-color: #1cc88a;
      --info-color: #36b9cc;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
      --border-radius: 0.75rem;
      --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    /* Modern Loading Screen */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(180deg, rgba(44, 62, 80, 0.95) 0%, rgba(52, 73, 94, 0.98) 100%);
      backdrop-filter: blur(20px);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dark-mode .loading-overlay {
      background: linear-gradient(135deg, rgba(26, 26, 46, 0.95) 0%, rgba(22, 33, 62, 0.98) 100%);
    }

    .loading-overlay.show {
      opacity: 1;
      visibility: visible;
    }

    .loading-container {
      text-align: center;
      position: relative;
    }

    .loading-logo {
      width: 80px;
      height: 80px;
      margin-bottom: 2rem;
      animation: logoFloat 3s ease-in-out infinite;
    }

    .loading-spinner {
      width: 60px;
      height: 60px;
      border: 3px solid rgba(102, 126, 234, 0.2);
      border-top: 3px solid #667eea;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1.5rem;
      position: relative;
    }

    .loading-spinner::before {
      content: '';
      position: absolute;
      top: -3px;
      left: -3px;
      right: -3px;
      bottom: -3px;
      border: 3px solid transparent;
      border-top: 3px solid rgba(102, 126, 234, 0.4);
      border-radius: 50%;
      animation: spin 1.5s linear infinite reverse;
    }

    .loading-text {
      font-size: 1.2rem;
      font-weight: 600;
      color: #667eea;
      margin-bottom: 0.5rem;
      opacity: 0;
      animation: textFadeIn 0.5s ease-out 0.3s forwards;
    }

    .loading-subtext {
      font-size: 0.9rem;
      color: #6c757d;
      opacity: 0;
      animation: textFadeIn 0.5s ease-out 0.6s forwards;
    }

    .dark-mode .loading-text {
      color: #667eea;
    }

    .dark-mode .loading-subtext {
      color: #adb5bd;
    }

    .loading-progress {
      width: 200px;
      height: 4px;
      background: rgba(102, 126, 234, 0.2);
      border-radius: 2px;
      margin: 1rem auto 0;
      overflow: hidden;
      position: relative;
    }

    .loading-progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #667eea, #764ba2);
      border-radius: 2px;
      width: 0%;
      animation: progressFill 2s ease-in-out infinite;
    }

    .loading-dots {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    .loading-dot {
      width: 8px;
      height: 8px;
      background: #667eea;
      border-radius: 50%;
      animation: dotPulse 1.4s ease-in-out infinite both;
    }

    .loading-dot:nth-child(2) {
      animation-delay: 0.2s;
    }

    .loading-dot:nth-child(3) {
      animation-delay: 0.4s;
    }

    /* Loading Animations */
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    @keyframes logoFloat {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }

    @keyframes textFadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes progressFill {
      0% { width: 0%; }
      50% { width: 70%; }
      100% { width: 100%; }
    }

    @keyframes dotPulse {
      0%, 80%, 100% { 
        transform: scale(0.8);
        opacity: 0.5;
      }
      40% { 
        transform: scale(1);
        opacity: 1;
      }
    }

    body {
      font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
      overflow-x: hidden;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      color: var(--text-dark);
      transition: all 0.3s;
      min-height: 100vh;
    }

    body.dark-mode {
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      color: var(--text-light);
    }

    /* Modern Sidebar */
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      color: white;
      padding: 0;
      transition: all 0.3s ease;
      z-index: 1000;
      transform: translateX(0);
      box-shadow: 4px 0 20px rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
    }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

    .sidebar .logo {
      padding: 2rem 1.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      background: rgba(255,255,255,0.05);
      backdrop-filter: blur(10px);
    }

    .sidebar .logo img {
      max-width: 100%;
      height: auto;
      filter: brightness(1.1);
    }

    .system-name {
      padding: 1rem 1.5rem;
      font-size: 1.1rem;
      font-weight: 700;
      color: rgba(255,255,255,0.95);
      text-align: center;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 1.5rem;
      background: rgba(255,255,255,0.03);
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .sidebar-nav {
      padding: 0 1rem;
    }

    .sidebar-nav .nav-item {
      margin-bottom: 0.5rem;
    }

    .sidebar-nav .nav-link {
      display: flex;
      align-items: center;
      color: rgba(255,255,255,0.8);
      padding: 1rem 1.25rem;
      text-decoration: none;
      border-radius: 0.75rem;
      transition: all 0.3s ease;
      font-weight: 500;
      border: 1px solid transparent;
      position: relative;
      overflow: hidden;
    }

    .sidebar-nav .nav-link::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      transition: left 0.5s;
    }

    .sidebar-nav .nav-link:hover::before {
      left: 100%;
    }

    .sidebar-nav .nav-link:hover {
      background: rgba(255,255,255,0.1);
      color: white;
      border-color: rgba(255,255,255,0.2);
      transform: translateX(5px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .sidebar-nav .nav-link.active {
      background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
      color: white;
      border-color: rgba(255,255,255,0.3);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .sidebar-nav .nav-link i {
      margin-right: 0.75rem;
      font-size: 1.1rem;
      width: 20px;
      text-align: center;
    }
    .sidebar-nav .nav-link .peso-icon { display: inline-block; margin-right: 0.6rem; font-size: 1.1rem; width: 20px; text-align: center; font-weight: 700; }

    .admin-feature {
      background: rgba(0,0,0,0.1);
      border-left: 3px solid rgba(255,255,255,0.3);
    }

    .admin-feature:hover {
      background: rgba(0,0,0,0.2);
      border-left-color: rgba(255,255,255,0.6);
    }

    .provider-feature {
      background: rgba(0,0,0,0.1);
      border-left: 4px solid rgba(255,255,255,0.4);
    }

    .provider-feature:hover {
      background: rgba(0,0,0,0.2);
      border-left-color: rgba(255,255,255,0.8);
    }

    .user-feature {
      background: rgba(0,123,255,0.1);
      border-left: 3px solid rgba(0,123,255,0.5);
    }

    .user-feature:hover {
      background: rgba(0,123,255,0.2);
      border-left-color: rgba(0,123,255,0.8);
    }

    .sidebar-footer {
      position: absolute;
      bottom: 0;
      width: 100%;
      padding: 1rem;
      border-top: 1px solid rgba(255,255,255,0.1);
      background: rgba(0,0,0,0.1);
      backdrop-filter: blur(10px);
    }

    .sidebar-footer .nav-link {
      justify-content: center;
      padding: 0.75rem;
      border-radius: 0.5rem;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-footer .nav-link:hover {
      background: rgba(255,255,255,0.1);
      border-color: rgba(255,255,255,0.2);
    }

    .sidebar-footer .nav-link i {
      margin-right: 0;
    }

    /* Enhanced transitions */
    .sidebar.transitioning {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Hover effects for better UX */
    .sidebar-nav .nav-link:active {
      transform: scale(0.98);
      transition: transform 0.1s ease;
    }

    /* Main Content */
    .content {
      margin-left: var(--sidebar-width);
      padding: 2rem;
      transition: all 0.3s ease;
      min-height: 100vh;
    }

    .content.expanded {
      margin-left: 0;
    }

    /* Header */
    .header {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
      padding: 1.5rem 2rem;
      border-radius: var(--border-radius);
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      border: 1px solid rgba(255,255,255,0.2);
    }

    .dark-mode .header {
      background: rgba(44, 62, 80, 0.9);
      color: var(--text-light);
      border: 1px solid rgba(255,255,255,0.1);
    }

    .hamburger {
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0.75rem;
      border-radius: 0.5rem;
      transition: all 0.3s;
      background: rgba(0,0,0,0.05);
    }

    .hamburger:hover {
      background: rgba(0,0,0,0.1);
    }

    .system-title {
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 2.2rem;
      font-weight: 800;
    }

    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .card {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: var(--border-radius);
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      padding: 2rem;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--gradient-primary);
    }

    .card:nth-child(2)::before { background: var(--gradient-success); }
    .card:nth-child(3)::before { background: var(--gradient-info); }
    .card:nth-child(4)::before { background: var(--gradient-warning); }

    .dark-mode .card {
      background: rgba(44, 62, 80, 0.9);
      color: var(--text-light);
      border: 1px solid rgba(255,255,255,0.1);
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      background: var(--gradient-primary);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .card:nth-child(2) .stat-value { background: var(--gradient-success); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .card:nth-child(3) .stat-value { background: var(--gradient-info); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .card:nth-child(4) .stat-value { background: var(--gradient-warning); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

    /* Form Section */
    .form-section {
      background-color: white;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      margin-bottom: 1.5rem;
      display: none;
    }

    .dark-mode .form-section {
      background-color: var(--dark-card);
      color: var(--text-light);
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }

    .dark-mode .form-group input,
    .dark-mode .form-group select,
    .dark-mode .form-group textarea {
      background-color: #2a3a5a;
      border-color: #3a4b6e;
      color: var(--text-light);
    }

    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    /* Buttons */
    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background-color: #3a5bc7;
    }

    .btn-secondary {
      background-color: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .btn-success {
      background-color: var(--success-color);
      color: white;
    }

    .btn-info {
      background-color: var(--info-color);
      color: white;
    }

    .btn-warning {
      background-color: var(--warning-color);
      color: white;
    }

    .btn-danger {
      background-color: var(--danger-color);
      color: white;
    }

    .toggle-form-btn {
      background-color: var(--primary-color);
      color: white;
      margin-bottom: 1.5rem;
    }

    .toggle-form-btn:hover {
      background-color: #3a5bc7;
    }

    /* Table Section */
    .table-section {
      background-color: white;
      padding: 1.5rem;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      margin-bottom: 1.5rem;
    }

    .dark-mode .table-section {
      background-color: var(--dark-card);
      color: var(--text-light);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    .dark-mode th,
    .dark-mode td {
      border-bottom-color: #3a4b6e;
    }

    thead {
      background-color: var(--primary-color);
      color: white;
    }

    .action-buttons {
      display: flex;
      gap: 0.5rem;
    }

    /* Modern Calculator Section */
    .calculator-section {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 252, 0.95) 100%);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      padding: 2rem;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
    }

    .calculator-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #667eea 100%);
      background-size: 200% 100%;
      animation: gradientShift 3s ease-in-out infinite;
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .dark-mode .calculator-section {
      background: linear-gradient(135deg, rgba(44, 62, 80, 0.95) 0%, rgba(52, 73, 94, 0.95) 100%);
      color: var(--text-light);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .calculator-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .calculator-header h3 {
      font-size: 1.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
    }

    .calculator-subtitle {
      color: #6c757d;
      font-size: 0.95rem;
      margin: 0;
    }

    .dark-mode .calculator-subtitle {
      color: #adb5bd;
    }

    .calculator-form {
      background: rgba(255, 255, 255, 0.7);
      border-radius: 15px;
      padding: 1.5rem;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .dark-mode .calculator-form {
      background: rgba(22, 33, 62, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .calculator-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .input-group-modern {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .modern-label {
      font-weight: 600;
      color: #374151;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .dark-mode .modern-label {
      color: #d1d5db;
    }

    .calculator-input {
      padding: 0.875rem 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: white;
      font-weight: 500;
    }

    .calculator-input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      transform: translateY(-1px);
    }

    .dark-mode .calculator-input {
      background-color: #374151;
      border-color: #4b5563;
      color: var(--text-light);
    }

    .dark-mode .calculator-input:focus {
      border-color: #667eea;
      background-color: #4b5563;
    }

    .modern-select {
      cursor: pointer;
    }

    .input-with-unit {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-unit {
      position: absolute;
      right: 1rem;
      color: #6b7280;
      font-weight: 600;
      font-size: 0.9rem;
      pointer-events: none;
    }

    .time-input-group {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 0.5rem;
    }

    .time-input {
      text-align: center;
    }

    .time-unit-select {
      font-size: 0.9rem;
    }

    .checkbox-group {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .checkbox-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 8px;
      transition: background-color 0.2s ease;
    }

    .checkbox-item:hover {
      background-color: rgba(102, 126, 234, 0.05);
    }

    .checkbox-item input[type="checkbox"] {
      display: none;
    }

    .checkmark {
      width: 20px;
      height: 20px;
      border: 2px solid #d1d5db;
      border-radius: 4px;
      position: relative;
      transition: all 0.3s ease;
    }

    .checkbox-item input[type="checkbox"]:checked + .checkmark {
      background-color: #667eea;
      border-color: #667eea;
    }

    .checkbox-item input[type="checkbox"]:checked + .checkmark::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 12px;
      font-weight: bold;
    }

    .calculator-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1rem;
    }

    .btn-calculate {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-calculate:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-reset {
      background: #6c757d;
      color: white;
      border: none;
      padding: 1rem 1.5rem;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-reset:hover {
      background: #5a6268;
      transform: translateY(-1px);
    }

    .calculation-result-modern {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      border-radius: 15px;
      padding: 1.5rem;
      margin-top: 2rem;
      color: white;
      box-shadow: 0 10px 30px rgba(17, 153, 142, 0.3);
    }

    .result-header h4 {
      margin: 0 0 1rem 0;
      font-size: 1.3rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .result-content {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .result-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 0;
    }

    .result-label {
      font-weight: 500;
      opacity: 0.9;
    }

    .result-value {
      font-weight: 700;
      font-size: 1.1rem;
    }

    .result-divider {
      height: 2px;
      background: rgba(255, 255, 255, 0.3);
      margin: 0.5rem 0;
      border-radius: 1px;
    }

    .result-total {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0 0.5rem 0;
      border-top: 2px solid rgba(255, 255, 255, 0.2);
    }

    .total-label {
      font-size: 1.2rem;
      font-weight: 700;
    }

    .total-value {
      font-size: 1.8rem;
      font-weight: 800;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Header Controls */
    .header-controls {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    /* Theme Toggle */
    .theme-toggle-container {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .theme-switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }

    .theme-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      transition: .4s;
      border-radius: 34px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        box-shadow: 4px 0 20px rgba(0,0,0,0.3);
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      .content {
        margin-left: 0;
        padding: 1rem;
      }

      .dashboard-cards { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    }

    @media (max-width: 576px) {
      .sidebar {
        width: 100%;
        max-width: 320px;
      }

      .dashboard-cards { grid-template-columns: 1fr; }
      .header { flex-direction: column; gap: 1rem; text-align: center; }
    }
  </style>
</head>
<body>
  <!-- Modern Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-container">
      <!-- Update logo path based on where you move the file -->
      <!-- If moving to a subfolder: src="../slatelogo.png" -->
      <img src="slatelogo.png" alt="SLATE Logo" class="loading-logo">
      <div class="loading-spinner"></div>
      <div class="loading-text" id="loadingText">Loading...</div>
      <div class="loading-subtext" id="loadingSubtext">Please wait while we prepare rate and tariff data</div>
      <div class="loading-progress">
        <div class="loading-progress-bar"></div>
      </div>
      <div class="loading-dots">
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
        <div class="loading-dot"></div>
      </div>
    </div>
  </div>

  <!-- Update this path based on where you move the file -->
  <!-- If moving to a subfolder: <?php include '../includes/sidebar.php'; ?> -->
  <?php include 'includes/sidebar.php'; ?>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <div>
        <h1>Rate & Tariff Management <span class="system-title">| CORE II </span></h1>
      </div>
      <div class="header-controls">
        <?php if ($isAdmin): ?>
          <a href="admin.php" class="btn btn-outline-primary btn-sm me-2">
            <i class="bi bi-shield-lock"></i>
            Admin
          </a>
        <?php endif; ?>
        <div class="theme-toggle-container">
          <span class="theme-label">Dark Mode</span>
          <label class="theme-switch">
            <input type="checkbox" id="themeToggle">
            <span class="slider"></span>
          </label>
        </div>
      </div>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <h3>Active Tariffs</h3>
        <div class="stat-value" id="activeTariffs">0</div>
        <div class="stat-label">Current rates</div>
      </div>

      <div class="card">
        <h3>Total Revenue</h3>
        <div class="stat-value" id="totalRevenue">₱0</div>
        <div class="stat-label">This month</div>
      </div>

      <div class="card">
        <h3>Service Categories</h3>
        <div class="stat-value" id="serviceCategories">0</div>
        <div class="stat-label">Pricing tiers</div>
      </div>

      <div class="card">
        <h3>Rate Changes</h3>
        <div class="stat-value" id="rateChanges">0</div>
        <div class="stat-label">This quarter</div>
      </div>
    </div>

    <div class="calculator-section">
      <div class="calculator-header">
        <h3><i class="bi bi-calculator"></i> Advanced Rate Calculator</h3>
        <p class="calculator-subtitle">Get accurate pricing estimates with detailed breakdown</p>
      </div>
      
      <div class="calculator-form">
        <div class="calculator-grid">
          <div class="input-group-modern">
            <label for="serviceType" class="modern-label">
              <i class="bi bi-gear"></i> Service Type
            </label>
            <select id="serviceType" class="calculator-input modern-select">
              <option value="">Select Service Type</option>
              <option value="transport">🚛 Transport & Delivery</option>
              <option value="logistics">📦 Logistics & Warehousing</option>
              <option value="maintenance">🔧 Maintenance & Repair</option>
              <option value="security">🛡️ Security Services</option>
              <option value="technology">💻 Technology Support</option>
            </select>
          </div>
          
          <div class="input-group-modern">
            <label for="distance" class="modern-label">
              <i class="bi bi-geo-alt"></i> Distance
            </label>
            <div class="input-with-unit">
              <input type="number" id="distance" class="calculator-input" placeholder="0.0" step="0.1" min="0">
              <span class="input-unit">km</span>
            </div>
          </div>
          
          <div class="input-group-modern">
            <label for="timeValue" class="modern-label">
              <i class="bi bi-clock"></i> Duration
            </label>
            <div class="time-input-group">
              <input type="number" id="timeValue" class="calculator-input time-input" placeholder="0" step="1" min="0">
              <select id="timeUnit" class="calculator-input time-unit-select">
                <option value="minutes">Minutes</option>
                <option value="hours" selected>Hours</option>
              </select>
            </div>
          </div>
          
          <div class="input-group-modern">
            <label for="priority" class="modern-label">
              <i class="bi bi-speedometer2"></i> Priority Level
            </label>
            <select id="priority" class="calculator-input modern-select">
              <option value="standard">⭐ Standard (1.0x)</option>
              <option value="express">⚡ Express (1.5x)</option>
              <option value="urgent">🚨 Urgent (2.0x)</option>
            </select>
          </div>
          
          <div class="input-group-modern">
            <label for="vehicleType" class="modern-label">
              <i class="bi bi-truck"></i> Vehicle Type
            </label>
            <select id="vehicleType" class="calculator-input modern-select">
              <option value="small">🏍️ Motorcycle/Small</option>
              <option value="medium" selected>🚐 Van/Medium</option>
              <option value="large">🚛 Truck/Large</option>
              <option value="specialized">🚚 Specialized Vehicle</option>
            </select>
          </div>
          
          <div class="input-group-modern">
            <label for="additionalServices" class="modern-label">
              <i class="bi bi-plus-circle"></i> Additional Services
            </label>
            <div class="checkbox-group">
              <label class="checkbox-item">
                <input type="checkbox" id="insurance" value="insurance">
                <span class="checkmark"></span>
                Insurance Coverage
              </label>
              <label class="checkbox-item">
                <input type="checkbox" id="packaging" value="packaging">
                <span class="checkmark"></span>
                Special Packaging
              </label>
              <label class="checkbox-item">
                <input type="checkbox" id="tracking" value="tracking">
                <span class="checkmark"></span>
                Real-time Tracking
              </label>
            </div>
          </div>
        </div>
        
        <div class="calculator-actions">
          <button id="calculateBtn" class="btn-calculate">
            <i class="bi bi-calculator"></i>
            Calculate Rate
          </button>
          <button id="resetBtn" class="btn-reset">
            <i class="bi bi-arrow-clockwise"></i>
            Reset
          </button>
        </div>
      </div>
      
      <div id="calculationResult" class="calculation-result-modern" style="display: none;">
        <div class="result-header">
          <h4><i class="bi bi-receipt"></i> Rate Breakdown</h4>
        </div>
        <div class="result-content">
          <div class="result-item">
            <span class="result-label">Base Rate:</span>
            <span class="result-value" id="baseRateDisplay">₱0.00</span>
          </div>
          <div class="result-item">
            <span class="result-label">Distance Charge:</span>
            <span class="result-value" id="distanceChargeDisplay">₱0.00</span>
          </div>
          <div class="result-item">
            <span class="result-label">Time Charge:</span>
            <span class="result-value" id="timeChargeDisplay">₱0.00</span>
          </div>
          <div class="result-item">
            <span class="result-label">Vehicle Multiplier:</span>
            <span class="result-value" id="vehicleMultiplierDisplay">1.0x</span>
          </div>
          <div class="result-item">
            <span class="result-label">Priority Multiplier:</span>
            <span class="result-value" id="priorityMultiplierDisplay">1.0x</span>
          </div>
          <div class="result-item">
            <span class="result-label">Additional Services:</span>
            <span class="result-value" id="additionalServicesDisplay">₱0.00</span>
          </div>
          <div class="result-divider"></div>
          <div class="result-total">
            <span class="total-label">Total Estimated Rate:</span>
            <span class="total-value" id="totalRateDisplay">₱0.00</span>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3><?php echo $isUser ? 'Rate & Tariff Information' : 'Tariff Management'; ?></h3>
      <div class="d-flex gap-2">
        <?php if ($isUser): ?>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userTariffModal" onclick="openUserAddModal()">
            <i class="bi bi-plus-circle"></i> Submit New Rate Request
          </button>
        <?php endif; ?>
      </div>
    </div>


    <div class="table-section">
      <div class="table-responsive">
        <table id="tariffsTable" class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tariff Name</th>
              <th>Service Category</th>
              <th>Base Rate</th>
              <th>Per KM</th>
              <th>Per Hour</th>
              <th>Priority Multiplier</th>
              <th>Status</th>
              <?php if ($isAdmin): ?>
                <th>Type</th>
                <th>Submitted By</th>
              <?php endif; ?>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="tariffsTableBody">
            <!-- Tariff data will be loaded here -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tariff Modal -->
  <div class="modal fade" id="tariffModal" tabindex="-1" aria-labelledby="tariffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tariffModalLabel">Add New Tariff</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="tariffForm">
            <input type="hidden" id="tariffId">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="tariffName" class="form-label">Tariff Name *</label>
                  <input type="text" class="form-control" id="tariffName" name="tariffName" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="serviceCategory" class="form-label">Service Category *</label>
                  <select class="form-select" id="serviceCategory" name="serviceCategory" required>
                    <option value="">Select Category</option>
                    <option value="Transport">Transport</option>
                    <option value="Logistics">Logistics</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Security">Security</option>
                    <option value="Technology">Technology</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="baseRate" class="form-label">Base Rate (PHP) *</label>
                  <input type="number" class="form-control" id="baseRate" name="baseRate" step="0.01" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="perKmRate" class="form-label">Per Kilometer Rate (PHP/km) *</label>
                  <input type="number" class="form-control" id="perKmRate" name="perKmRate" step="0.01" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="perHourRate" class="form-label">Per Hour Rate (PHP/hour) *</label>
                  <input type="number" class="form-control" id="perHourRate" name="perHourRate" step="0.01" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="priorityMultiplier" class="form-label">Priority Multiplier *</label>
                  <select class="form-select" id="priorityMultiplier" name="priorityMultiplier" required>
                    <option value="1.0">Standard (1.0x)</option>
                    <option value="1.5">Express (1.5x)</option>
                    <option value="2.0">Urgent (2.0x)</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="tariffStatus" class="form-label">Status *</label>
                  <select class="form-select" id="tariffStatus" name="tariffStatus" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Draft">Draft</option>
                    <option value="Expired">Expired</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="effectiveDate" class="form-label">Effective Date *</label>
                  <input type="date" class="form-control" id="effectiveDate" name="effectiveDate" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="expiryDate" class="form-label">Expiry Date *</label>
                  <input type="date" class="form-control" id="expiryDate" name="expiryDate" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="mb-3">
                  <label for="tariffNotes" class="form-label">Notes</label>
                  <textarea class="form-control" id="tariffNotes" name="tariffNotes" rows="3"></textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveTariff()">Save Tariff</button>
        </div>
      </div>
    </div>
  </div>



  <!-- View Tariff Modal -->
  <div class="modal fade" id="viewTariffModal" tabindex="-1" aria-labelledby="viewTariffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewTariffModalLabel">Tariff Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <p><strong>ID:</strong> <span id="viewTariffId"></span></p>
              <p><strong>Name:</strong> <span id="viewTariffName"></span></p>
              <p><strong>Category:</strong> <span id="viewTariffCategory"></span></p>
              <p><strong>Base Rate:</strong> <span id="viewTariffBaseRate"></span></p>
            </div>
            <div class="col-md-6">
              <p><strong>Per KM Rate:</strong> <span id="viewTariffPerKm"></span></p>
              <p><strong>Per Hour Rate:</strong> <span id="viewTariffPerHour"></span></p>
              <p><strong>Priority Multiplier:</strong> <span id="viewTariffMultiplier"></span></p>
              <p><strong>Status:</strong> <span id="viewTariffStatus"></span></p>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <p><strong>Notes:</strong></p>
              <p id="viewTariffNotes"></p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete tariff <strong id="deleteTariffName"></strong>?</p>
          <p class="text-danger">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete Tariff</button>
        </div>
      </div>
    </div>
  </div>

  <!-- User Tariff Submission Modal -->
  <div class="modal fade" id="userTariffModal" tabindex="-1" aria-labelledby="userTariffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="userTariffModalLabel">
            <i class="bi bi-plus-circle me-2"></i>Submit Rate & Tariff Request
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Notice:</strong> Your submission will be reviewed by administrators and added to the system once approved.
          </div>
          <form id="userTariffForm">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="userTariffName" class="form-label">Service/Tariff Name *</label>
                  <input type="text" class="form-control" id="userTariffName" name="userTariffName" required
                         placeholder="e.g., Express Delivery Service">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="userServiceCategory" class="form-label">Service Category *</label>
                  <select class="form-select" id="userServiceCategory" name="userServiceCategory" required>
                    <option value="">Select Category</option>
                    <option value="Transport">Transport</option>
                    <option value="Logistics">Logistics</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Security">Security</option>
                    <option value="Technology">Technology</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="userBaseRate" class="form-label">Proposed Base Rate (PHP) *</label>
                  <input type="number" class="form-control" id="userBaseRate" name="userBaseRate" 
                         step="0.01" min="0" required placeholder="0.00">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="userPerKmRate" class="form-label">Per Kilometer Rate (PHP/km)</label>
                  <input type="number" class="form-control" id="userPerKmRate" name="userPerKmRate" 
                         step="0.01" min="0" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="userPerHourRate" class="form-label">Per Hour Rate (PHP/hour)</label>
                  <input type="number" class="form-control" id="userPerHourRate" name="userPerHourRate" 
                         step="0.01" min="0" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="userPriorityLevel" class="form-label">Priority Level *</label>
                  <select class="form-select" id="userPriorityLevel" name="userPriorityLevel" required>
                    <option value="1.0">Standard (1.0x)</option>
                    <option value="1.5">Express (1.5x)</option>
                    <option value="2.0">Urgent (2.0x)</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="userServiceArea" class="form-label">Service Area</label>
                  <input type="text" class="form-control" id="userServiceArea" name="userServiceArea" 
                         placeholder="e.g., Metro Manila, Nationwide">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="mb-3">
                  <label for="userJustification" class="form-label">Justification/Reason for Request *</label>
                  <textarea class="form-control" id="userJustification" name="userJustification" 
                            rows="4" required placeholder="Please explain why this rate/tariff is needed and how it benefits the service..."></textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="mb-3">
                  <label for="userAdditionalNotes" class="form-label">Additional Notes</label>
                  <textarea class="form-control" id="userAdditionalNotes" name="userAdditionalNotes" 
                            rows="2" placeholder="Any additional information or special requirements..."></textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary" onclick="submitUserTariff()">
            <i class="bi bi-send me-1"></i>Submit Request
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Update API paths based on where you move the file
    // If moving to a subfolder: const API_BASE = '../api/tariffs.php';
    const API_BASE = 'api/tariffs.php';
    // Shared data sources (wired to external core via proxy endpoints)
    const SHIPMENTS_API = 'api/shipments.php';
    const CONSOLIDATIONS_API = 'api/consolidations.php';
    const BLS_API = 'api/bls.php';

    // Optional helpers to read shared operational data; not invoked by default
    async function fetchShipments() {
      const res = await fetch(SHIPMENTS_API);
      if (!res.ok) throw new Error('Failed to fetch shipments');
      return await res.json();
    }

    async function fetchConsolidations() {
      const res = await fetch(CONSOLIDATIONS_API);
      if (!res.ok) throw new Error('Failed to fetch consolidations');
      return await res.json();
    }

    async function fetchBLs() {
      const res = await fetch(BLS_API);
      if (!res.ok) throw new Error('Failed to fetch BLs');
      return await res.json();
    }

    let tariffs = [];
    let currentTariffId = null;
    let isEditMode = false;

    // Initialize the application
    document.addEventListener('DOMContentLoaded', function() {
      showLoading('Initializing Rate & Tariff...', 'Loading tariff data and pricing components');
      
      // Simulate loading time for better UX
      setTimeout(() => {
        initializeEventListeners();
        applyStoredTheme();
        fetchTariffs(); // This will call loadAllData() internally
        
        // Hide loading after everything is ready
        setTimeout(() => {
          hideLoading();
        }, 500);
      }, 1500);
    });

    async function fetchTariffs() {
      try {
        const res = await fetch(API_BASE);
        const data = await res.json();
        tariffs = Array.isArray(data) ? data.map(dbToUiTariff) : [];
        await loadAllData();
        updateDashboardStats();
      } catch (e) {
        showNotification('Failed to load tariffs', 'danger');
      }
    }

    function dbToUiTariff(row) {
      return {
        id: parseInt(row.id),
        name: row.name,
        category: row.category,
        baseRate: parseFloat(row.base_rate),
        perKmRate: parseFloat(row.per_km_rate),
        perHourRate: parseFloat(row.per_hour_rate),
        priorityMultiplier: parseFloat(row.priority_multiplier),
        status: row.status,
        effectiveDate: row.effective_date,
        expiryDate: row.expiry_date,
        notes: row.notes || ''
      };
    }

    function uiToDbTariff(ui) {
      return {
        name: ui.name,
        category: ui.category,
        baseRate: ui.baseRate,
        perKmRate: ui.perKmRate,
        perHourRate: ui.perHourRate,
        priorityMultiplier: ui.priorityMultiplier,
        status: ui.status,
        effectiveDate: ui.effectiveDate,
        expiryDate: ui.expiryDate,
        notes: ui.notes || ''
      };
    }

    function initializeEventListeners() {
      // Theme toggle
      document.getElementById('themeToggle').addEventListener('change', function() {
        document.body.classList.toggle('dark-mode', this.checked);
        localStorage.setItem('theme', this.checked ? 'dark' : 'light');
      });

      // Enhanced sidebar toggle with smooth animations
      document.getElementById('hamburger').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        // Add smooth transition class
        sidebar.classList.add('transitioning');
        setTimeout(() => {
          sidebar.classList.remove('transitioning');
        }, 300);
      });

      // Close sidebar when clicking outside on mobile
      document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.getElementById('hamburger');
        
        if (window.innerWidth <= 992 && 
            !sidebar.contains(e.target) && 
            !hamburger.contains(e.target) &&
            !sidebar.classList.contains('collapsed')) {
          sidebar.classList.add('collapsed');
        }
      });

      // Active link management
      const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
      navLinks.forEach(link => {
        link.addEventListener('click', function() {
          navLinks.forEach(l => l.classList.remove('active'));
          this.classList.add('active');
        });
      });

      // Add input validation and formatting
      const numberInputs = document.querySelectorAll('input[type="number"]');
      numberInputs.forEach(input => {
        input.addEventListener('input', function() {
          if (this.value < 0) {
            this.value = 0;
          }
        });
      });

      // Add real-time calculation on input change
      const calculatorInputs = document.querySelectorAll('#serviceType, #distance, #timeValue, #timeUnit, #priority, #vehicleType, #insurance, #packaging, #tracking');
      calculatorInputs.forEach(input => {
        input.addEventListener('change', function() {
          // Auto-calculate if all required fields are filled
          const serviceType = document.getElementById('serviceType').value;
          if (serviceType) {
            calculateRate();
          }
        });
      });
    }

    function applyStoredTheme() {
      const stored = localStorage.getItem('theme');
      const isDark = stored === 'dark';
      document.body.classList.toggle('dark-mode', isDark);
      const toggle = document.getElementById('themeToggle');
      if (toggle) toggle.checked = isDark;
    }

    // Enhanced logout function with proper fade animation
    function confirmLogout() {
      // Inject fade styles if not already present
      if (!document.getElementById('swal-fade-styles')) {
        const fadeStyles = document.createElement('style');
        fadeStyles.id = 'swal-fade-styles';
        fadeStyles.textContent = `
          .swal2-animate-show {
            animation: swal2-fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
          }
          .swal2-animate-hide {
            animation: swal2-fadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
          }
          .swal2-backdrop-show {
            animation: swal2-backdropFadeIn 0.3s ease-out !important;
          }
          .swal2-backdrop-hide {
            animation: swal2-backdropFadeOut 0.3s ease-in !important;
          }
          
          @keyframes swal2-fadeIn {
            0% {
              transform: scale(0.6) translateY(-50px);
              opacity: 0;
            }
            50% {
              transform: scale(1.05) translateY(-10px);
              opacity: 0.8;
            }
            80% {
              transform: scale(0.98) translateY(2px);
              opacity: 0.95;
            }
            100% {
              transform: scale(1) translateY(0);
              opacity: 1;
            }
          }
          
          @keyframes swal2-fadeOut {
            0% {
              transform: scale(1) translateY(0);
              opacity: 1;
            }
            50% {
              transform: scale(1.05) translateY(-5px);
              opacity: 0.7;
            }
            100% {
              transform: scale(0.7) translateY(20px);
              opacity: 0;
            }
          }
          
          @keyframes swal2-backdropFadeIn {
            0% {
              opacity: 0;
              backdrop-filter: blur(0px);
            }
            100% {
              opacity: 1;
              backdrop-filter: blur(4px);
            }
          }
          
          @keyframes swal2-backdropFadeOut {
            0% {
              opacity: 1;
              backdrop-filter: blur(4px);
            }
            100% {
              opacity: 0;
              backdrop-filter: blur(0px);
            }
          }
        `;
        document.head.appendChild(fadeStyles);
      }

      Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to log out of CORE II System?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-box-arrow-right me-2"></i> Yes, Log Out',
        cancelButtonText: '<i class="bi bi-x-circle me-2"></i> Cancel',
        reverseButtons: true,
        showClass: { 
          popup: 'swal2-animate-show',
          backdrop: 'swal2-backdrop-show'
        },
        hideClass: { 
          popup: 'swal2-animate-hide',
          backdrop: 'swal2-backdrop-hide'
        },
        customClass: {
          popup: 'swal2-popup-custom',
          backdrop: 'swal2-backdrop-custom',
          container: 'swal2-container-custom'
        },
        didOpen: () => {
          const popup = Swal.getPopup();
          const backdrop = Swal.getContainer();
          
          if (popup) {
            popup.classList.add('swal2-animate-in');
            popup.style.animation = 'swal2-fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-in');
            backdrop.style.animation = 'swal2-backdropFadeIn 0.3s ease-out';
          }
        },
        willClose: () => {
          const popup = Swal.getPopup();
          const backdrop = Swal.getContainer();
          
          if (popup) {
            popup.classList.add('swal2-animate-out');
            popup.style.animation = 'swal2-fadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-out');
            backdrop.style.animation = 'swal2-backdropFadeOut 0.3s ease-in';
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'auth.php?logout=1';
        }
      });
    }

    async function loadAllData() {
      const tbody = document.getElementById('tariffsTableBody');
      tbody.innerHTML = ''; // Clear existing data to prevent duplication
      
      const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
      const isUser = <?php echo $isUser ? 'true' : 'false'; ?>;
      const currentUserId = <?php echo $_SESSION['user_id'] ?? 0; ?>;

      // Load regular tariffs first
      tariffs.forEach(tariff => {
        const row = document.createElement('tr');
        let adminCols = '';
        if (isAdmin) {
          adminCols = `
            <td><span class="badge bg-primary">Official</span></td>
            <td><span class="text-muted">System</span></td>
          `;
        }
        
        row.innerHTML = `
          <td>${tariff.id}</td>
          <td>${tariff.name}</td>
          <td>${tariff.category}</td>
          <td>₱${tariff.baseRate.toFixed(2)}</td>
          <td>₱${tariff.perKmRate.toFixed(2)}</td>
          <td>₱${tariff.perHourRate.toFixed(2)}</td>
          <td>${Number(tariff.priorityMultiplier).toFixed(1)}x</td>
          <td><span class="badge ${getStatusBadgeClass(tariff.status)}">${tariff.status}</span></td>
          ${adminCols}
          <td>
            <div class="action-buttons">
              <button class="btn btn-sm btn-info" onclick="viewTariff(${tariff.id})" title="View Details">
                <i class="bi bi-eye"></i>
              </button>
              ${isAdmin ? `
                <button class="btn btn-sm btn-primary" onclick="editTariff(${tariff.id})" title="Edit Tariff">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="duplicateTariff(${tariff.id})" title="Duplicate Tariff">
                  <i class="bi bi-files"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteTariff(${tariff.id})" title="Delete Tariff">
                  <i class="bi bi-trash"></i>
                </button>
              ` : ''}
            </div>
          </td>
        `;
        tbody.appendChild(row);
      });
      
      // Load user submissions separately - ONLY FOR ADMINS
      if (isAdmin) {
        try {
          // Update API path based on where you move the file
          const response = await fetch('api/user-tariff-submissions.php');
          
          if (response.ok) {
            const submissions = await response.json();
            
            if (submissions && Array.isArray(submissions)) {
              // Filter out approved submissions - they become official tariffs
              const displayableSubmissions = submissions.filter(submission => 
                submission.status !== 'Approved' || submission.status === 'Approved'
              );
              
              displayableSubmissions.forEach(submission => {
              const row = document.createElement('tr');
              let adminCols = '';
              if (isAdmin) {
                adminCols = `
                  <td><span class="badge bg-info">Submission</span></td>
                  <td>${submission.submitted_by_username || 'Unknown'}</td>
                `;
              }
              
              const isOwner = submission.submitted_by_user_id == currentUserId;
              
              row.innerHTML = `
                <td>S${submission.id}</td>
                <td>${submission.name}</td>
                <td>${submission.category}</td>
                <td>₱${parseFloat(submission.base_rate || 0).toFixed(2)}</td>
                <td>₱${parseFloat(submission.per_km_rate || 0).toFixed(2)}</td>
                <td>₱${parseFloat(submission.per_hour_rate || 0).toFixed(2)}</td>
                <td>${parseFloat(submission.priority_multiplier || 1).toFixed(1)}x</td>
                <td><span class="badge ${getSubmissionStatusClass(submission.status)}">${submission.status}</span></td>
                ${adminCols}
                <td>
                  <div class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="viewSubmissionDetails('${submission.id}')" title="View Details">
                      <i class="bi bi-eye"></i>
                    </button>
                    ${isAdmin && submission.status === 'Pending Review' ? `
                      <button class="btn btn-sm btn-success" onclick="approveSubmission(${submission.id})" title="Approve">
                        <i class="bi bi-check-circle"></i>
                      </button>
                      <button class="btn btn-sm btn-warning" onclick="rejectSubmission(${submission.id})" title="Reject">
                        <i class="bi bi-x-circle"></i>
                      </button>
                    ` : ''}
                    ${(isAdmin || (isOwner && submission.status === 'Pending Review')) ? `
                      <button class="btn btn-sm btn-danger" onclick="${isAdmin ? 'deleteSubmissionAdmin' : 'cancelSubmission'}(${submission.id})" title="${isAdmin ? 'Delete' : 'Cancel'}">
                        <i class="bi bi-trash"></i>
                      </button>
                    ` : ''}
                  </div>
                </td>
              `;
              tbody.appendChild(row);
              });
            }
          } else {
            console.warn('Failed to load submissions or empty response');
          }
        } catch (error) {
          console.error('Error loading submissions:', error);
        }
      }
    }

    function getStatusBadgeClass(status) {
      switch(status) {
        case 'Active': return 'bg-success';
        case 'Inactive': return 'bg-secondary';
        case 'Draft': return 'bg-warning text-dark';
        case 'Expired': return 'bg-danger';
        default: return 'bg-secondary';
      }
    }

    function updateDashboardStats() {
      const activeTariffs = tariffs.filter(t => t.status === 'Active').length;
      const totalRevenue = tariffs.reduce((sum, t) => sum + t.baseRate, 0);
      const serviceCategories = new Set(tariffs.map(t => t.category)).size;
      const rateChanges = 3; // Sample data

      document.getElementById('activeTariffs').textContent = activeTariffs;
      document.getElementById('totalRevenue').textContent = `₱${totalRevenue.toLocaleString()}`;
      document.getElementById('serviceCategories').textContent = serviceCategories;
      document.getElementById('rateChanges').textContent = rateChanges;
    }

    function openAddModal() {
      isEditMode = false;
      currentTariffId = null;
      document.getElementById('tariffModalLabel').textContent = 'Add New Tariff';
      document.getElementById('tariffForm').reset();
      document.getElementById('tariffId').value = '';
    }

    function viewTariff(id) {
      const tariff = tariffs.find(t => t.id === id);
      if (!tariff) return;

      document.getElementById('viewTariffId').textContent = tariff.id;
      document.getElementById('viewTariffName').textContent = tariff.name;
      document.getElementById('viewTariffCategory').textContent = tariff.category;
      document.getElementById('viewTariffBaseRate').textContent = `₱${tariff.baseRate.toFixed(2)}`;
      document.getElementById('viewTariffPerKm').textContent = `₱${tariff.perKmRate.toFixed(2)}`;
      document.getElementById('viewTariffPerHour').textContent = `₱${tariff.perHourRate.toFixed(2)}`;
      document.getElementById('viewTariffMultiplier').textContent = `${Number(tariff.priorityMultiplier).toFixed(1)}x`;
      document.getElementById('viewTariffStatus').textContent = tariff.status;
      document.getElementById('viewTariffNotes').textContent = tariff.notes || 'No notes available';

      const viewModal = new bootstrap.Modal(document.getElementById('viewTariffModal'));
      viewModal.show();
    }

    function editTariff(id) {
      const tariff = tariffs.find(t => t.id === id);
      if (!tariff) return;

      isEditMode = true;
      currentTariffId = id;
      document.getElementById('tariffModalLabel').textContent = 'Edit Tariff';
      
      // Populate form fields
      document.getElementById('tariffId').value = tariff.id;
      document.getElementById('tariffName').value = tariff.name;
      document.getElementById('serviceCategory').value = tariff.category;
      document.getElementById('baseRate').value = tariff.baseRate;
      document.getElementById('perKmRate').value = tariff.perKmRate;
      document.getElementById('perHourRate').value = tariff.perHourRate;
      document.getElementById('priorityMultiplier').value = Number(tariff.priorityMultiplier).toFixed(1);
      document.getElementById('tariffStatus').value = tariff.status;
      document.getElementById('effectiveDate').value = tariff.effectiveDate;
      document.getElementById('expiryDate').value = tariff.expiryDate;
      document.getElementById('tariffNotes').value = tariff.notes || '';

      const modal = new bootstrap.Modal(document.getElementById('tariffModal'));
      modal.show();
    }

    async function duplicateTariff(id) {
      const tariff = tariffs.find(t => t.id === id);
      if (!tariff) {
        showNotification('Tariff not found for duplication', 'warning');
        return;
      }
      
      const payload = uiToDbTariff({
        ...tariff,
        name: tariff.name + ' (Copy)',
        status: 'Draft'
      });
      
      console.log('Duplicating tariff:', payload);
      
      try {
        const response = await fetch(API_BASE, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        console.log('Duplicate response:', result);
        
        if (response.ok) {
          await fetchTariffs();
          showNotification('Tariff duplicated successfully!', 'success');
        } else {
          showNotification(result.error || 'Failed to duplicate tariff', 'danger');
        }
      } catch (e) {
        console.error('Duplicate error:', e);
        showNotification('Failed to duplicate tariff: ' + e.message, 'danger');
      }
    }

    function deleteTariff(id) {
      const tariff = tariffs.find(t => t.id === id);
      if (!tariff) return;

      document.getElementById('deleteTariffName').textContent = tariff.name;
      currentTariffId = id;

      const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
      deleteModal.show();
    }

    async function confirmDelete() {
      if (!currentTariffId) {
        showNotification('No tariff selected for deletion', 'warning');
        return;
      }
      
      try {
        console.log('Deleting tariff ID:', currentTariffId);
        const response = await fetch(`${API_BASE}?id=${currentTariffId}`, { method: 'DELETE' });
        const result = await response.json();
        
        console.log('Delete response:', result);
        
        if (response.ok) {
          await fetchTariffs();
          const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
          deleteModal.hide();
          showNotification('Tariff deleted successfully!', 'success');
        } else {
          showNotification(result.error || 'Failed to delete tariff', 'danger');
        }
      } catch (e) {
        console.error('Delete error:', e);
        showNotification('Failed to delete tariff: ' + e.message, 'danger');
      }
    }

    async function saveTariff() {
      const form = document.getElementById('tariffForm');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const formData = new FormData(form);
      const tariffData = {
        name: formData.get('tariffName'),
        category: formData.get('serviceCategory'),
        baseRate: parseFloat(formData.get('baseRate')) || 0,
        perKmRate: parseFloat(formData.get('perKmRate')) || 0,
        perHourRate: parseFloat(formData.get('perHourRate')) || 0,
        priorityMultiplier: parseFloat(formData.get('priorityMultiplier')) || 1.0,
        status: formData.get('tariffStatus'),
        effectiveDate: formData.get('effectiveDate'),
        expiryDate: formData.get('expiryDate'),
        notes: formData.get('tariffNotes') || ''
      };
      
      // Additional validation
      if (!tariffData.name || !tariffData.category || !tariffData.status || !tariffData.effectiveDate || !tariffData.expiryDate) {
        showNotification('Please fill in all required fields', 'warning');
        return;
      }
      
      if (tariffData.baseRate < 0 || tariffData.perKmRate < 0 || tariffData.perHourRate < 0) {
        showNotification('Rates cannot be negative', 'warning');
        return;
      }

      console.log('Saving tariff data:', tariffData);
      console.log('Converted data:', uiToDbTariff(tariffData));

      try {
        let response;
        if (isEditMode && currentTariffId) {
          console.log('Updating tariff ID:', currentTariffId);
          response = await fetch(`${API_BASE}?id=${currentTariffId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(uiToDbTariff(tariffData))
          });
        } else {
          console.log('Creating new tariff');
          response = await fetch(API_BASE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(uiToDbTariff(tariffData))
          });
        }

        const result = await response.json();
        console.log('API Response:', result);

        if (response.ok) {
          showNotification(isEditMode ? 'Tariff updated successfully!' : 'Tariff added successfully!', 'success');
          await fetchTariffs();
          const modal = bootstrap.Modal.getInstance(document.getElementById('tariffModal'));
          modal.hide();
        } else {
          console.error('API Error:', result);
          showNotification(result.error || 'Failed to save tariff', 'danger');
        }
      } catch (e) {
        console.error('Save error:', e);
        showNotification('Failed to save tariff: ' + e.message, 'danger');
      }
    }

    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      
      document.body.appendChild(notification);
      
      // Auto remove after 3 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 3000);
    }

    // Enhanced Rate calculator functionality
    document.getElementById('calculateBtn').addEventListener('click', calculateRate);
    document.getElementById('resetBtn').addEventListener('click', resetCalculator);

    function calculateRate() {
      const serviceType = document.getElementById('serviceType').value;
      const distance = parseFloat(document.getElementById('distance').value) || 0;
      const timeValue = parseFloat(document.getElementById('timeValue').value) || 0;
      const timeUnit = document.getElementById('timeUnit').value;
      const priority = document.getElementById('priority').value;
      const vehicleType = document.getElementById('vehicleType').value;
      
      // Get additional services
      const insurance = document.getElementById('insurance').checked;
      const packaging = document.getElementById('packaging').checked;
      const tracking = document.getElementById('tracking').checked;

      if (!serviceType) {
        showNotification('Please select a service type', 'warning');
        return;
      }

      // Convert time to hours for calculation
      let timeInHours = timeValue;
      if (timeUnit === 'minutes') {
        timeInHours = timeValue / 60;
      }

      // Enhanced rate calculation logic with more accurate pricing
      let baseRate = 0;
      let perKmRate = 0;
      let perHourRate = 0;
      let priorityMultiplier = 1.0;
      let vehicleMultiplier = 1.0;

      // Service type rates
      switch(serviceType) {
        case 'transport':
          baseRate = 150;
          perKmRate = 8.5;
          perHourRate = 75;
          break;
        case 'logistics':
          baseRate = 200;
          perKmRate = 12.0;
          perHourRate = 120;
          break;
        case 'maintenance':
          baseRate = 300;
          perKmRate = 5.5;
          perHourRate = 180;
          break;
        case 'security':
          baseRate = 400;
          perKmRate = 7.0;
          perHourRate = 150;
          break;
        case 'technology':
          baseRate = 250;
          perKmRate = 4.0;
          perHourRate = 200;
          break;
      }

      // Priority multipliers
      switch(priority) {
        case 'express':
          priorityMultiplier = 1.5;
          break;
        case 'urgent':
          priorityMultiplier = 2.0;
          break;
        default:
          priorityMultiplier = 1.0;
      }

      // Vehicle type multipliers
      switch(vehicleType) {
        case 'small':
          vehicleMultiplier = 0.8;
          break;
        case 'medium':
          vehicleMultiplier = 1.0;
          break;
        case 'large':
          vehicleMultiplier = 1.4;
          break;
        case 'specialized':
          vehicleMultiplier = 1.8;
          break;
      }

      // Calculate base charges
      const distanceCharge = distance * perKmRate;
      const timeCharge = timeInHours * perHourRate;
      
      // Additional services charges
      let additionalServicesCharge = 0;
      if (insurance) additionalServicesCharge += baseRate * 0.15; // 15% of base rate
      if (packaging) additionalServicesCharge += 50; // Fixed ₱50
      if (tracking) additionalServicesCharge += 25; // Fixed ₱25

      // Calculate subtotal before multipliers
      const subtotal = baseRate + distanceCharge + timeCharge + additionalServicesCharge;
      
      // Apply multipliers
      const totalRate = subtotal * vehicleMultiplier * priorityMultiplier;
      
      // Display detailed breakdown
      displayCalculationResult({
        baseRate,
        distanceCharge,
        timeCharge: timeInHours * perHourRate,
        vehicleMultiplier,
        priorityMultiplier,
        additionalServicesCharge,
        totalRate
      });
    }

    function displayCalculationResult(breakdown) {
      document.getElementById('baseRateDisplay').textContent = `₱${breakdown.baseRate.toFixed(2)}`;
      document.getElementById('distanceChargeDisplay').textContent = `₱${breakdown.distanceCharge.toFixed(2)}`;
      document.getElementById('timeChargeDisplay').textContent = `₱${breakdown.timeCharge.toFixed(2)}`;
      document.getElementById('vehicleMultiplierDisplay').textContent = `${breakdown.vehicleMultiplier.toFixed(1)}x`;
      document.getElementById('priorityMultiplierDisplay').textContent = `${breakdown.priorityMultiplier.toFixed(1)}x`;
      document.getElementById('additionalServicesDisplay').textContent = `₱${breakdown.additionalServicesCharge.toFixed(2)}`;
      document.getElementById('totalRateDisplay').textContent = `₱${breakdown.totalRate.toFixed(2)}`;
      
      // Show result with animation
      const resultDiv = document.getElementById('calculationResult');
      resultDiv.style.display = 'block';
      resultDiv.style.opacity = '0';
      resultDiv.style.transform = 'translateY(20px)';
      
      setTimeout(() => {
        resultDiv.style.transition = 'all 0.5s ease';
        resultDiv.style.opacity = '1';
        resultDiv.style.transform = 'translateY(0)';
      }, 100);
    }

    function resetCalculator() {
      // Reset all form fields
      document.getElementById('serviceType').value = '';
      document.getElementById('distance').value = '';
      document.getElementById('timeValue').value = '';
      document.getElementById('timeUnit').value = 'hours';
      document.getElementById('priority').value = 'standard';
      document.getElementById('vehicleType').value = 'medium';
      document.getElementById('insurance').checked = false;
      document.getElementById('packaging').checked = false;
      document.getElementById('tracking').checked = false;
      
      // Hide result
      const resultDiv = document.getElementById('calculationResult');
      resultDiv.style.transition = 'all 0.3s ease';
      resultDiv.style.opacity = '0';
      resultDiv.style.transform = 'translateY(-20px)';
      
      setTimeout(() => {
        resultDiv.style.display = 'none';
      }, 300);
      
      showNotification('Calculator reset successfully', 'info');
    }

    // Loading Utility Functions
    function showLoading(text = 'Loading...', subtext = 'Please wait') {
      const overlay = document.getElementById('loadingOverlay');
      const loadingText = document.getElementById('loadingText');
      const loadingSubtext = document.getElementById('loadingSubtext');
      
      if (loadingText) loadingText.textContent = text;
      if (loadingSubtext) loadingSubtext.textContent = subtext;
      
      overlay.classList.add('show');
    }

    function hideLoading() {
      const overlay = document.getElementById('loadingOverlay');
      overlay.classList.remove('show');
    }

    function showPageTransition(text = 'Loading...', url = null) {
      showLoading(text, 'Preparing to navigate...');
      
      // Navigate after a short delay for smooth transition
      setTimeout(() => {
        if (url) {
          window.location.href = url;
        }
      }, 800);
    }

    // User Submission Functions
    let userSubmissions = [];

    function openUserAddModal() {
      document.getElementById('userTariffForm').reset();
      const modal = new bootstrap.Modal(document.getElementById('userTariffModal'));
      modal.show();
    }

    async function submitUserTariff() {
      const form = document.getElementById('userTariffForm');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const formData = new FormData(form);
      const submissionData = {
        name: formData.get('userTariffName'),
        category: formData.get('userServiceCategory'),
        baseRate: parseFloat(formData.get('userBaseRate')),
        perKmRate: parseFloat(formData.get('userPerKmRate')) || 0,
        perHourRate: parseFloat(formData.get('userPerHourRate')) || 0,
        priorityMultiplier: parseFloat(formData.get('userPriorityLevel')),
        serviceArea: formData.get('userServiceArea') || '',
        justification: formData.get('userJustification'),
        notes: formData.get('userAdditionalNotes') || '',
        status: 'Pending Review',
        submittedByUserId: <?php echo $_SESSION['user_id'] ?? 0; ?>,
        submittedByUsername: '<?php echo $_SESSION["username"] ?? "Unknown"; ?>'
      };
      
      console.log('Submitting user tariff with data:', submissionData);

      try {
        // Update API path based on where you move the file
        const response = await fetch('api/user-tariff-submissions.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(submissionData)
        });

        const result = await response.json();
        
        console.log('API Response:', response.status, response.statusText);
        console.log('API Result:', result);
        
        if (response.ok) {
          // Close modal
          const modal = bootstrap.Modal.getInstance(document.getElementById('userTariffModal'));
          modal.hide();
          
          // Show success notification
          showNotification('Your rate & tariff request has been submitted successfully! It will be reviewed by administrators.', 'success');
          
          // Reload all data to show the new submission
          await fetchTariffs(); // This will reload both tariffs and submissions
        } else {
          console.error('API Error Response:', result);
          let errorMessage = 'Failed to submit your request.';
          
          if (result.errors && typeof result.errors === 'object') {
            const errorFields = Object.keys(result.errors);
            errorMessage += ` Issues with: ${errorFields.join(', ')}`;
          } else if (result.error) {
            errorMessage += ` Error: ${result.error}`;
          }
          
          showNotification(errorMessage + ' Please try again.', 'danger');
        }
        
      } catch (error) {
        console.error('Submission error:', error);
        showNotification('Network error: Failed to submit your request. Please check your connection and try again.', 'danger');
      }
    }

    function getSubmissionStatusClass(status) {
      switch(status) {
        case 'Pending Review': return 'bg-warning text-dark';
        case 'Approved': return 'bg-success';
        case 'Rejected': return 'bg-danger';
        case 'Under Review': return 'bg-info';
        default: return 'bg-secondary';
      }
    }

    async function viewSubmissionDetails(submissionId) {
      try {
        const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`);
        const submission = await response.json();
        
        if (!response.ok || !submission) {
          showNotification('Failed to load submission details', 'danger');
          return;
        }
        
        Swal.fire({
          title: submission.name,
          html: `
            <div class="text-start">
              <div class="row mb-3">
                <div class="col-6"><strong>ID:</strong></div>
                <div class="col-6">S${submission.id}</div>
              </div>
              <div class="row mb-3">
                <div class="col-6"><strong>Category:</strong></div>
                <div class="col-6">${submission.category}</div>
              </div>
              <div class="row mb-3">
                <div class="col-6"><strong>Status:</strong></div>
                <div class="col-6"><span class="badge ${getSubmissionStatusClass(submission.status)}">${submission.status}</span></div>
              </div>
              <div class="row mb-3">
                <div class="col-6"><strong>Base Rate:</strong></div>
                <div class="col-6">₱${parseFloat(submission.base_rate).toFixed(2)}</div>
              </div>
              <div class="row mb-3">
                <div class="col-6"><strong>Per KM Rate:</strong></div>
                <div class="col-6">₱${parseFloat(submission.per_km_rate).toFixed(2)}</div>
              </div>
              <div class="row mb-3">
                <div class="col-6"><strong>Per Hour Rate:</strong></div>
                <div class="col-6">₱${parseFloat(submission.per_hour_rate).toFixed(2)}</div>
              </div>
              <div class="row mb-3">
                <div class="col-6"><strong>Priority Level:</strong></div>
                <div class="col-6">${parseFloat(submission.priority_multiplier).toFixed(1)}x</div>
              </div>
              ${submission.service_area ? `
                <div class="row mb-3">
                  <div class="col-6"><strong>Service Area:</strong></div>
                  <div class="col-6">${submission.service_area}</div>
                </div>
              ` : ''}
              <div class="mb-3">
                <strong>Justification:</strong><br>
                ${submission.justification}
              </div>
              ${submission.notes ? `
                <div class="mb-3">
                  <strong>Additional Notes:</strong><br>
                  ${submission.notes}
                </div>
              ` : ''}
              <div class="text-muted small">
                <strong>Submitted:</strong> ${new Date(submission.created_at).toLocaleString()} by ${submission.submitted_by_username || 'Unknown'}
              </div>
            </div>
          `,
          width: '600px',
          confirmButtonText: 'Close',
          confirmButtonColor: '#6c757d'
        });
      } catch (error) {
        console.error('Error loading submission details:', error);
        showNotification('Failed to load submission details', 'danger');
      }
    }

    function cancelSubmission(submissionId) {
      Swal.fire({
        title: 'Cancel Submission?',
        text: 'Are you sure you want to cancel this tariff request?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Cancel',
        cancelButtonText: 'Keep It'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`, {
              method: 'DELETE'
            });
            
            if (response.ok) {
              // Reload display
              await fetchTariffs();
              showNotification('Submission cancelled successfully.', 'info');
            } else {
              showNotification('Failed to cancel submission. Please try again.', 'danger');
            }
          } catch (error) {
            console.error('Cancel error:', error);
            showNotification('Failed to cancel submission. Please try again.', 'danger');
          }
        }
      });
    }

    function getSubmissionStatusClass(status) {
      switch(status) {
        case 'Pending Review': return 'bg-warning text-dark';
        case 'Approved': return 'bg-success';
        case 'Rejected': return 'bg-danger';
        case 'Under Review': return 'bg-info';
        default: return 'bg-secondary';
      }
    }

    // Admin Functions for User Submissions
    let allUserSubmissions = [];

    async function viewSubmissionDetailsAdmin(submissionId) {
      try {
        const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`);
        const submission = await response.json();
        
        if (!response.ok || !submission) {
          showNotification('Failed to load submission details', 'danger');
          return;
        }
      
      Swal.fire({
        title: submission.name,
        html: `
          <div class="text-start">
            <div class="row mb-3">
              <div class="col-6"><strong>ID:</strong></div>
              <div class="col-6">${submission.id}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Category:</strong></div>
              <div class="col-6">${submission.category}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Status:</strong></div>
              <div class="col-6"><span class="badge ${getSubmissionStatusClass(submission.status)}">${submission.status}</span></div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Base Rate:</strong></div>
              <div class="col-6">₱${parseFloat(submission.base_rate).toFixed(2)}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Per KM Rate:</strong></div>
              <div class="col-6">₱${parseFloat(submission.per_km_rate).toFixed(2)}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Per Hour Rate:</strong></div>
              <div class="col-6">₱${parseFloat(submission.per_hour_rate).toFixed(2)}</div>
            </div>
            <div class="row mb-3">
              <div class="col-6"><strong>Priority Level:</strong></div>
              <div class="col-6">${parseFloat(submission.priority_multiplier).toFixed(1)}x</div>
            </div>
            ${submission.service_area ? `
              <div class="row mb-3">
                <div class="col-6"><strong>Service Area:</strong></div>
                <div class="col-6">${submission.service_area}</div>
              </div>
            ` : ''}
            <div class="mb-3">
              <strong>Justification:</strong><br>
              ${submission.justification}
            </div>
            ${submission.notes ? `
              <div class="mb-3">
                <strong>Additional Notes:</strong><br>
                ${submission.notes}
              </div>
            ` : ''}
            <div class="text-muted small">
              <strong>Submitted:</strong> ${new Date(submission.created_at).toLocaleString()} by ${submission.submitted_by_username}
            </div>
          </div>
        `,
        width: '600px',
        showCancelButton: submission.status === 'Pending Review',
        confirmButtonText: 'Close',
        confirmButtonColor: '#6c757d',
        cancelButtonText: 'Approve',
        cancelButtonColor: '#28a745'
      }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
          approveSubmission(submissionId);
        }
      });
      } catch (error) {
        console.error('Error loading submission details:', error);
        showNotification('Failed to load submission details', 'danger');
      }
    }

    async function approveSubmission(submissionId) {
      try {
        const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            statusOnly: true,
            status: 'Approved',
            reviewNotes: 'Approved by administrator'
          })
        });
        
        const result = await response.json();
        
        if (response.ok) {
          if (result.new_tariff_id) {
            showNotification(
              `Submission approved successfully! New official tariff created (ID: ${result.new_tariff_id})`, 
              'success'
            );
          } else {
            showNotification('Submission approved successfully!', 'success');
          }
          await fetchTariffs(); // Reload both tariffs and submissions
        } else {
          showNotification(result.error || 'Failed to approve submission.', 'danger');
        }
      } catch (error) {
        console.error('Approve error:', error);
        showNotification('Failed to approve submission.', 'danger');
      }
    }

    async function rejectSubmission(submissionId) {
      const { value: reason } = await Swal.fire({
        title: 'Reject Submission',
        input: 'textarea',
        inputLabel: 'Reason for rejection (optional)',
        inputPlaceholder: 'Please provide a reason for rejecting this submission...',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Cancel'
      });
      
      if (reason !== undefined) {
        try {
          const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              statusOnly: true,
              status: 'Rejected',
              reviewNotes: reason || 'Rejected by administrator'
            })
          });
          
          if (response.ok) {
            showNotification('Submission rejected successfully.', 'info');
            await fetchTariffs();
          } else {
            showNotification('Failed to reject submission.', 'danger');
          }
        } catch (error) {
          console.error('Reject error:', error);
          showNotification('Failed to reject submission.', 'danger');
        }
      }
    }

    async function deleteSubmissionAdmin(submissionId) {
      try {
        const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`);
        const submission = await response.json();
        
        if (!response.ok || !submission) {
          showNotification('Failed to load submission details', 'danger');
          return;
        }
        
        Swal.fire({
          title: 'Delete Submission?',
          text: `Are you sure you want to delete the submission "${submission.name}" by ${submission.submitted_by_username || 'Unknown'}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const response = await fetch(`api/user-tariff-submissions.php?id=${submissionId}`, {
              method: 'DELETE'
            });
            
            if (response.ok) {
              showNotification('Submission deleted successfully.', 'info');
              await fetchTariffs();
            } else {
              showNotification('Failed to delete submission.', 'danger');
            }
          } catch (error) {
            console.error('Delete error:', error);
            showNotification('Failed to delete submission.', 'danger');
          }
        }
      });
      } catch (error) {
        console.error('Error in deleteSubmissionAdmin:', error);
        showNotification('Failed to load submission for deletion.', 'danger');
      }
    }
  </script>
</body>
</html>
