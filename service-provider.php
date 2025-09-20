<?php
session_start();
require_once 'auth.php';

// Allow admin, provider, and user access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];
$isAdmin = ($userRole === 'admin');
$isProvider = ($userRole === 'provider');
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
  
  <!-- Universal Logout SweetAlert -->
  <!-- Note: Logout functionality is now handled directly in the page JavaScript -->
  
  <!-- Universal Dark Mode Styles -->
  <?php include 'includes/dark-mode-styles.php'; ?>
  <title>Service Management | CORE II</title>
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

    body { font-family: 'Inter', 'Segoe UI', system-ui, sans-serif; overflow-x: hidden; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); color: var(--text-dark); transition: all 0.3s; min-height: 100vh; }

    body.dark-mode { background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); color: var(--text-light); }

    /* Modern Sidebar */
    .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); color: white; padding: 0; transition: all 0.3s ease; z-index: 1000; transform: translateX(0); box-shadow: 4px 0 20px rgba(0,0,0,0.1); backdrop-filter: blur(10px); }

    .sidebar.collapsed {
      transform: translateX(-100%);
    }

    .sidebar .logo { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); }

    .sidebar .logo img {
      max-width: 100%;
      height: auto;
      filter: brightness(1.1);
    }

    .system-name { padding: 1rem 1.5rem; font-size: 1.1rem; font-weight: 700; color: rgba(255,255,255,0.95); text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1.5rem; background: rgba(255,255,255,0.03); letter-spacing: 1px; text-transform: uppercase; }

    .sidebar-nav {
      padding: 0 1rem;
    }

    .sidebar-nav .nav-item {
      margin-bottom: 0.5rem;
    }

    .sidebar-nav .nav-link { display: flex; align-items: center; color: rgba(255,255,255,0.8); padding: 1rem 1.25rem; text-decoration: none; border-radius: 0.75rem; transition: all 0.3s ease; font-weight: 500; border: 1px solid transparent; position: relative; overflow: hidden; }
    .sidebar-nav .nav-link::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent); transition: left 0.5s; }
    .sidebar-nav .nav-link:hover::before { left: 100%; }

    .sidebar-nav .nav-link:hover { background: rgba(255,255,255,0.1); color: white; border-color: rgba(255,255,255,0.2); transform: translateX(5px); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

    .sidebar-nav .nav-link.active { background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); color: white; border-color: rgba(255,255,255,0.3); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }

    .sidebar-nav .nav-link i {
      margin-right: 0.75rem;
      font-size: 1.1rem;
      width: 20px;
      text-align: center;
    }

    .provider-feature {
      background: rgba(0,0,0,0.1);
      border-left: 4px solid rgba(255,255,255,0.4);
    }

    .provider-feature:hover {
      background: rgba(0,0,0,0.2);
      border-left-color: rgba(255,255,255,0.8);
    }

    .sidebar-footer { position: absolute; bottom: 0; width: 100%; padding: 1rem; border-top: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.1); backdrop-filter: blur(10px); }

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

    /* Main Content */
    .content { margin-left: var(--sidebar-width); padding: 2rem; transition: all 0.3s ease; min-height: 100vh; }

    .content.expanded {
      margin-left: 0;
    }

    /* Header */
    .header { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); padding: 1.5rem 2rem; border-radius: var(--border-radius); box-shadow: 0 8px 32px rgba(0,0,0,0.1); margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,0.2); }

    .dark-mode .header { background: rgba(44, 62, 80, 0.9); color: var(--text-light); border: 1px solid rgba(255,255,255,0.1); }

    .hamburger { font-size: 1.5rem; cursor: pointer; padding: 0.75rem; border-radius: 0.5rem; transition: all 0.3s; background: rgba(0,0,0,0.05); }
    .hamburger:hover { background: rgba(0,0,0,0.1); }

    .system-title { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-size: 2.2rem; font-weight: 800; }

    /* Dashboard Cards */
    .dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }

    .card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2); border-radius: var(--border-radius); box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 2rem; transition: all 0.3s; position: relative; overflow: hidden; }
    .card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .card:nth-child(2)::before { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .card:nth-child(3)::before { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .card:nth-child(4)::before { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

    .dark-mode .card { background: rgba(44, 62, 80, 0.9); color: var(--text-light); border: 1px solid rgba(255,255,255,0.1); }

    .card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }

    .stat-value { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .card:nth-child(2) .stat-value { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .card:nth-child(3) .stat-value { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .card:nth-child(4) .stat-value { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

    /* Table Section */
    .table-section { background: rgba(255,255,255,0.9); border: 1px solid rgba(255,255,255,0.2); border-radius: var(--border-radius); box-shadow: 0 8px 32px rgba(0,0,0,0.1); padding: 1.5rem; margin-bottom: 1.5rem; }
    .dark-mode .table-section { background: rgba(44, 62, 80, 0.9); color: var(--text-light); border: 1px solid rgba(255,255,255,0.1); }

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

    /* Status badges */
    .status-active { background-color: var(--success-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; }
    .status-pending { background-color: var(--warning-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; }
    .status-completed { background-color: var(--info-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; }
    .status-cancelled { background-color: var(--danger-color); color: white; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.8rem; }

    /* Provider table specific styles */
    .provider-info, .compliance-status, .contract-details, .purchase-orders, .spend-data, .delivery-terms {
      font-size: 0.9rem;
      line-height: 1.4;
    }

    .performance-score .score-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      margin-bottom: 0.25rem;
    }

    .score-circle.excellent { background-color: var(--success-color); color: white; }
    .score-circle.good { background-color: var(--info-color); color: white; }
    .score-circle.average { background-color: var(--warning-color); color: white; }
    .score-circle.poor { background-color: var(--danger-color); color: white; }

    .compliance-certified { background-color: var(--success-color); }
    .compliance-pending { background-color: var(--warning-color); }
    .compliance-expired { background-color: var(--danger-color); }

    /* Table cell padding adjustments */
    .table-section td {
      padding: 1rem 0.75rem;
      vertical-align: top;
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
      background-color: #ccc;
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
      background-color: var(--primary-color);
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    /* SweetAlert2 Animation Enhancements */
    .swal2-popup-custom {
      animation: swal2-show 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
      transform-origin: center center;
    }
    
    .swal2-popup-custom.swal2-animate-in {
      animation: swal2-show 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .swal2-popup-custom.swal2-animate-out {
      animation: swal2-hide 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .swal2-backdrop-custom {
      animation: swal2-backdrop-show 0.4s ease-out !important;
    }
    
    .swal2-backdrop-custom.swal2-backdrop-animate-in {
      animation: swal2-backdrop-show 0.4s ease-out !important;
    }
    
    .swal2-backdrop-custom.swal2-backdrop-animate-out {
      animation: swal2-backdrop-hide 0.3s ease-in !important;
    }
    
    @keyframes swal2-show {
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
    
    @keyframes swal2-hide {
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
    
    @keyframes swal2-backdrop-show {
      0% {
        opacity: 0;
        backdrop-filter: blur(0px);
      }
      100% {
        opacity: 1;
        backdrop-filter: blur(4px);
      }
    }
    
    @keyframes swal2-backdrop-hide {
      0% {
        opacity: 1;
        backdrop-filter: blur(4px);
      }
      100% {
        opacity: 0;
        backdrop-filter: blur(0px);
      }
    }
    
    /* Enhanced SweetAlert2 styling */
    .swal2-container-custom {
      z-index: 9999;
    }
    
    .swal2-popup-custom {
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
    }
    
    .dark-mode .swal2-popup-custom {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: var(--text-light);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Responsive */
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
        box-shadow: 2px 0 20px rgba(0,0,0,0.3);
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      .content {
        margin-left: 0;
      }
    }

    @media (max-width: 576px) {
      .sidebar {
        width: 100%;
        max-width: 320px;
      }
    }
  </style>
</head>
<body>
  <!-- Modern Loading Overlay -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-container">
      <img src="slatelogo.png" alt="SLATE Logo" class="loading-logo">
      <div class="loading-spinner"></div>
      <div class="loading-text" id="loadingText">Loading...</div>
      <div class="loading-subtext" id="loadingSubtext">Please wait while we prepare service data</div>
    </div>
  </div>

  <?php include 'includes/sidebar.php'; ?>

  <div class="content" id="mainContent">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <div>
        <h1>Service Management <span class="system-title">| CORE II </span></h1>
      </div>
      <div class="header-controls">
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
        <h3>Total Providers</h3>
        <div class="stat-value" id="totalProviders">0</div>
        <div class="stat-label">Registered providers</div>
      </div>

      <div class="card">
        <h3>Certified Providers</h3>
        <div class="stat-value" id="activeProviders">0</div>
        <div class="stat-label">ISO & compliance certified</div>
      </div>

      <div class="card">
        <h3>Active Contracts</h3>
        <div class="stat-value" id="serviceAreas">0</div>
        <div class="stat-label">Current agreements</div>
      </div>

      <div class="card">
        <h3>Total Spend</h3>
        <div class="stat-value" id="monthlyRevenue">₱0</div>
        <div class="stat-label">This fiscal year</div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Provider Management & Vendor Master Data</h3>
      <div class="btn-group">
        <?php if ($isAdmin): ?>
        <button class="btn btn-primary" onclick="showAddProviderModal()">
          <i class="bi bi-plus-circle"></i> Add Provider
        </button>
        <div class="btn-group" role="group">
          <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-file-earmark-pdf"></i> Export Report
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="generateSupplierReport('summary')">
              <i class="bi bi-file-text"></i> Summary Report
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="generateSupplierReport('detailed')">
              <i class="bi bi-file-earmark-spreadsheet"></i> Detailed Report
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="generateSupplierReport('compliance')">
              <i class="bi bi-shield-check"></i> Compliance Report
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" onclick="generateSupplierReport('suppliers')">
              <i class="bi bi-file-earmark-pdf-fill"></i> Full PDF Report
            </a></li>
          </ul>
        </div>
        <?php else: ?>
        <button class="btn btn-outline-success" onclick="generateSupplierReport('suppliers')">
          <i class="bi bi-file-earmark-pdf"></i> View Report
        </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-section">
      <div class="table-responsive">
        <table id="providersTable" class="table table-hover">
          <thead>
            <tr>
              <th>Provider Info</th>
              <th>Compliance Status</th>
              <th>Contract Details</th>
              <th>Performance Score</th>
              <th>Purchase Orders</th>
              <th>Historical Spend</th>
              <th>Lead Time & Delivery</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="providersTableBody">
            <!-- Provider data will be loaded here -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Provider Management Modal -->
  <div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="providerModalLabel">Add New Provider</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="providerForm">
          <div class="modal-body">
            <!-- Provider Basic Information -->
            <h6 class="mb-3"><i class="bi bi-building"></i> Provider Information</h6>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="providerName" class="form-label">Provider Name *</label>
                <input type="text" class="form-control" id="providerName" name="name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="providerType" class="form-label">Provider Type *</label>
                <select class="form-select" id="providerType" name="type" required>
                  <option value="">Select type...</option>
                  <option value="Individual">Individual</option>
                  <option value="Company">Company</option>
                  <option value="Cooperative">Cooperative</option>
                  <option value="Government Agency">Government Agency</option>
                  <option value="Manufacturer">Manufacturer</option>
                  <option value="Distributor">Distributor</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="contactPerson" class="form-label">Contact Person *</label>
                <input type="text" class="form-control" id="contactPerson" name="contact_person" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="contactEmail" class="form-label">Contact Email *</label>
                <input type="email" class="form-control" id="contactEmail" name="contact_email" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="contactPhone" class="form-label">Contact Phone *</label>
                <input type="tel" class="form-control" id="contactPhone" name="contact_phone" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="serviceArea" class="form-label">Service Area *</label>
                <input type="text" class="form-control" id="serviceArea" name="service_area" placeholder="e.g., Metro Manila" required>
              </div>
            </div>

            <!-- Compliance & Certification -->
            <hr class="my-4">
            <h6 class="mb-3"><i class="bi bi-shield-check"></i> Compliance & Certification</h6>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="isoCertified" class="form-label">ISO Certification</label>
                <select class="form-select" id="isoCertified" name="iso_certified">
                  <option value="No">No</option>
                  <option value="Yes">Yes</option>
                  <option value="Pending">Pending</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="insuranceValid" class="form-label">Insurance Status</label>
                <select class="form-select" id="insuranceValid" name="insurance_valid">
                  <option value="No">No</option>
                  <option value="Yes">Valid</option>
                  <option value="Expired">Expired</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="permits" class="form-label">Business Permits</label>
                <select class="form-select" id="permits" name="permits">
                  <option value="Valid">Valid</option>
                  <option value="Expired">Expired</option>
                  <option value="Pending">Pending</option>
                </select>
              </div>
            </div>

            <!-- Contract Details -->
            <hr class="my-4">
            <h6 class="mb-3"><i class="bi bi-file-earmark-text"></i> Contract & Agreement Details</h6>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="monthlyRate" class="form-label">Rate (₱)</label>
                <input type="number" step="0.01" class="form-control" id="monthlyRate" name="monthly_rate" placeholder="0.00">
              </div>
              <div class="col-md-4 mb-3">
                <label for="contractStart" class="form-label">Contract Start Date</label>
                <input type="date" class="form-control" id="contractStart" name="contract_start">
              </div>
              <div class="col-md-4 mb-3">
                <label for="contractEnd" class="form-label">Contract End Date</label>
                <input type="date" class="form-control" id="contractEnd" name="contract_end">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="serviceLevel" class="form-label">Service Level</label>
                <select class="form-select" id="serviceLevel" name="service_level">
                  <option value="Standard">Standard</option>
                  <option value="Premium">Premium</option>
                  <option value="Basic">Basic</option>
                  <option value="Enterprise">Enterprise</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="providerStatus" class="form-label">Status</label>
                <select class="form-select" id="providerStatus" name="status">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                  <option value="Pending">Pending</option>
                  <option value="Suspended">Suspended</option>
                </select>
              </div>
            </div>

            <!-- Delivery & Performance -->
            <hr class="my-4">
            <h6 class="mb-3"><i class="bi bi-truck"></i> Delivery & Performance Terms</h6>
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="leadTime" class="form-label">Lead Time (days)</label>
                <input type="number" class="form-control" id="leadTime" name="lead_time" placeholder="7">
              </div>
              <div class="col-md-4 mb-3">
                <label for="deliveryTerms" class="form-label">Delivery Terms</label>
                <select class="form-select" id="deliveryTerms" name="delivery_terms">
                  <option value="FOB">FOB (Free on Board)</option>
                  <option value="CIF">CIF (Cost, Insurance, Freight)</option>
                  <option value="EXW">EXW (Ex Works)</option>
                  <option value="DDP">DDP (Delivered Duty Paid)</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="experience" class="form-label">Years of Experience</label>
                <input type="number" class="form-control" id="experience" name="experience" placeholder="5">
              </div>
            </div>

            <!-- Additional Information -->
            <hr class="my-4">
            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="providerNotes" class="form-label">Additional Notes</label>
                <textarea class="form-control" id="providerNotes" name="notes" rows="3" placeholder="Additional notes, certifications, or special requirements..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success" id="saveProviderBtn">
              <i class="bi bi-check-circle"></i> Save Provider
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Provider Details Modal -->
  <div class="modal fade" id="providerDetailsModal" tabindex="-1" aria-labelledby="providerDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="providerDetailsModalLabel">Provider Details & Vendor Master Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="providerDetailsBody" style="max-height: 70vh; overflow-y: auto;">
          <!-- Provider details will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Global variables
    let currentEditingProviderId = null;
    let isEditMode = false;
    let providerModal = null;
    let providerDetailsModal = null;

    // Initialize the application
    document.addEventListener('DOMContentLoaded', function() {
      showLoading('Initializing Provider Management...', 'Loading provider data and management tools');
      
      setTimeout(() => {
        initializeEventListeners();
        initializeModals();
        applyStoredTheme();
        loadProviders();
        loadStats();
        
        setTimeout(() => {
          hideLoading();
        }, 500);
      }, 1500);
    });

    function initializeEventListeners() {
      // Theme toggle
      document.getElementById('themeToggle').addEventListener('change', function() {
        document.body.classList.toggle('dark-mode', this.checked);
        localStorage.setItem('theme', this.checked ? 'dark' : 'light');
      });

      // Sidebar toggle
      document.getElementById('hamburger').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
      });

      // Active link management
      const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
      navLinks.forEach(link => {
        link.addEventListener('click', function() {
          navLinks.forEach(l => l.classList.remove('active'));
          this.classList.add('active');
        });
      });

      // Provider form submission
      document.getElementById('providerForm').addEventListener('submit', handleProviderFormSubmit);
    }

    function initializeModals() {
      providerModal = new bootstrap.Modal(document.getElementById('providerModal'));
      providerDetailsModal = new bootstrap.Modal(document.getElementById('providerDetailsModal'));
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

    // API Functions
    async function apiRequest(url, options = {}) {
      try {
        const response = await fetch(url, {
          headers: {
            'Content-Type': 'application/json',
            ...options.headers
          },
          ...options
        });

        // Check if response is ok before trying to parse JSON
        if (!response.ok) {
          let errorMessage = `HTTP ${response.status}`;
          try {
            const errorData = await response.json();
            errorMessage = errorData.error || errorMessage;
          } catch (parseError) {
            // If JSON parsing fails, use status text
            errorMessage = response.statusText || errorMessage;
          }
          throw new Error(errorMessage);
        }

        const data = await response.json();
        return data;
      } catch (error) {
        console.error('API Error:', error);
        
        // Provide more specific error messages
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
          throw new Error('Network connection failed. Please check your internet connection.');
        } else if (error.message.includes('JSON')) {
          throw new Error('Invalid response from server. Please try again.');
        }
        
        throw error;
      }
    }

    async function loadProviders() {
      try {
        updateLoadingText('Loading provider accounts...', 'Fetching latest provider data');
        const data = await apiRequest('provider-users-api.php?action=list');
        
        if (data && data.providers) {
          renderProvidersTable(data.providers);
        } else {
          console.warn('No provider data received');
          const tbody = document.getElementById('providersTableBody');
          tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No provider data available at this time.</td></tr>';
        }
      } catch (error) {
        console.error('Load providers error:', error);
        
        // More user-friendly error handling
        const tbody = document.getElementById('providersTableBody');
        let errorMessage = 'Unable to load provider data.';
        
        if (error.message.includes('fetch')) {
          errorMessage = 'Network connection issue. Please check your internet connection and try again.';
        } else if (error.message.includes('401') || error.message.includes('Unauthorized')) {
          errorMessage = 'Session expired. Please log in again.';
          setTimeout(() => {
            window.location.href = 'login.php';
          }, 3000);
        } else if (error.message.includes('500')) {
          errorMessage = 'Server error. Please try again in a few moments.';
        }
        
        tbody.innerHTML = `
          <tr>
            <td colspan="9" class="text-center text-warning">
              <div class="d-flex flex-column align-items-center py-3">
                <i class="bi bi-exclamation-triangle-fill mb-2" style="font-size: 2rem;"></i>
                <strong>${errorMessage}</strong>
                <button class="btn btn-primary btn-sm mt-2" onclick="loadProviders(); loadStats();">
                  <i class="bi bi-arrow-clockwise"></i> Retry
                </button>
              </div>
            </td>
          </tr>
        `;
        
        // Show a less intrusive notification instead of a popup
        console.log('Provider loading failed:', error.message);
      }
    }

    async function loadStats() {
      try {
        const data = await apiRequest('provider-users-api.php?action=stats');
        if (data) {
          updateDashboardStats(data);
        } else {
          console.warn('No stats data received');
          // Set default values if no data received
          updateDashboardStats({
            total_providers: 0,
            active_providers: 0,
            service_areas: 0
          });
        }
      } catch (error) {
        console.error('Failed to load stats:', error);
        // Set default values on error to prevent UI issues
        updateDashboardStats({
          total_providers: 0,
          active_providers: 0,
          service_areas: 0
        });
      }
    }

    function renderProvidersTable(providers) {
      const tbody = document.getElementById('providersTableBody');
      tbody.innerHTML = '';

      if (!providers || providers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center">No provider data found</td></tr>';
        return;
      }

      providers.forEach(provider => {
        const row = document.createElement('tr');
        const status = provider.is_active ? 'Active' : 'Inactive';
        const complianceStatus = getComplianceStatus(provider);
        const performanceScore = calculatePerformanceScore(provider);
        const spendData = getHistoricalSpend(provider);
        
        row.innerHTML = `
          <td>
            <div class="provider-info">
              <strong>${provider.name || provider.username}</strong><br>
              <small class="text-muted">${provider.email}</small><br>
              <small class="text-muted">${provider.phone || 'N/A'}</small><br>
              <span class="badge bg-secondary">${provider.provider_type || 'Individual'}</span>
            </div>
          </td>
          <td>
            <div class="compliance-status">
              <span class="badge ${complianceStatus.class}">${complianceStatus.text}</span><br>
              <small class="text-muted">ISO: ${provider.iso_certified || 'N/A'}</small><br>
              <small class="text-muted">Insurance: ${provider.insurance_valid || 'N/A'}</small>
            </div>
          </td>
          <td>
            <div class="contract-details">
              <strong>Rate: ₱${provider.monthly_rate || '0.00'}</strong><br>
              <small class="text-muted">Start: ${formatDate(provider.contract_start) || 'N/A'}</small><br>
              <small class="text-muted">End: ${formatDate(provider.contract_end) || 'N/A'}</small><br>
              <small class="text-muted">Terms: ${provider.service_level || 'Standard'}</small>
            </div>
          </td>
          <td>
            <div class="performance-score text-center">
              <div class="score-circle ${performanceScore.class}">
                <strong>${performanceScore.score}</strong>
              </div>
              <small class="text-muted">${performanceScore.projects} projects</small>
            </div>
          </td>
          <td>
            <div class="purchase-orders">
              <strong>${provider.active_pos || 0} Active POs</strong><br>
              <small class="text-muted">Total: ${provider.total_pos || 0}</small><br>
              <small class="text-muted">Last PO: ${formatDate(provider.last_po_date) || 'N/A'}</small>
            </div>
          </td>
          <td>
            <div class="spend-data">
              <strong>₱${spendData.total}</strong><br>
              <small class="text-muted">This Month: ₱${spendData.monthly}</small><br>
              <small class="text-muted">Category: ${spendData.category}</small>
            </div>
          </td>
          <td>
            <div class="delivery-terms">
              <strong>${provider.lead_time || 'N/A'} days</strong><br>
              <small class="text-muted">${provider.delivery_terms || 'FOB'}</small><br>
              <small class="text-muted">Area: ${provider.service_area || 'N/A'}</small>
            </div>
          </td>
          <td>
            <div class="action-buttons">
              <button class="btn btn-sm btn-info" onclick="viewProvider(${provider.id})" title="View Details">
                <i class="bi bi-eye"></i>
              </button>
              <button class="btn btn-sm btn-primary" onclick="editProvider(${provider.id})" title="Edit Provider">
                <i class="bi bi-pencil"></i>
              </button>
              ${provider.is_active ? 
                '<button class="btn btn-sm btn-warning" onclick="toggleProviderStatus(' + provider.id + ', false)" title="Deactivate"><i class="bi bi-pause"></i></button>' :
                '<button class="btn btn-sm btn-success" onclick="toggleProviderStatus(' + provider.id + ', true)" title="Activate"><i class="bi bi-play"></i></button>'
              }
              <button class="btn btn-sm btn-danger" onclick="deleteProvider(${provider.id})" title="Delete Provider">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    function updateDashboardStats(stats) {
      document.getElementById('totalProviders').textContent = stats.total_providers || 0;
      document.getElementById('activeProviders').textContent = stats.active_providers || 0;
      document.getElementById('serviceAreas').textContent = stats.service_areas || 0;
      document.getElementById('monthlyRevenue').textContent = 'N/A';
    }

    // CRUD Operations
    function openAddModal() {
      isEditMode = false;
      currentEditingProviderId = null;
      document.getElementById('providerModalLabel').textContent = 'Add New Provider';
      document.getElementById('saveProviderBtn').innerHTML = '<i class="bi bi-check-circle"></i> Save Provider';
      document.getElementById('providerForm').reset();
      
      // Set default values
      const today = new Date().toISOString().split('T')[0];
      const nextYear = new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().split('T')[0];
      document.getElementById('contractStart').value = today;
      document.getElementById('contractEnd').value = nextYear;
      document.getElementById('providerStatus').value = 'Active';
      document.getElementById('serviceLevel').value = 'Standard';
      document.getElementById('deliveryTerms').value = 'FOB';
      document.getElementById('isoCertified').value = 'No';
      document.getElementById('insuranceValid').value = 'No';
      document.getElementById('permits').value = 'Valid';
      document.getElementById('leadTime').value = '7';
      
      providerModal.show();
    }

    async function editProvider(id) {
      try {
        if (!id || !Number.isInteger(Number(id))) {
          showAlert('Invalid provider ID', 'error');
          return;
        }
        
        showLoading('Loading provider details...', 'Preparing edit form');
        const data = await apiRequest(`provider-users-api.php?action=get&id=${id}`);
        
        if (!data.provider) {
          throw new Error('Provider data not found in response');
        }
        
        const provider = data.provider;
        
        isEditMode = true;
        currentEditingProviderId = id;
        document.getElementById('providerModalLabel').textContent = `Edit Provider - ${provider.username}`;
        document.getElementById('saveProviderBtn').innerHTML = '<i class="bi bi-check-circle"></i> Update Provider';
        
        // Populate form fields with provider data
        document.getElementById('providerName').value = provider.name || '';
        document.getElementById('providerType').value = provider.provider_type || '';
        document.getElementById('contactPerson').value = provider.name || '';
        document.getElementById('contactEmail').value = provider.email || '';
        document.getElementById('contactPhone').value = provider.phone || '';
        document.getElementById('serviceArea').value = provider.service_area || '';
        document.getElementById('monthlyRate').value = provider.monthly_rate || '0.00';
        document.getElementById('providerStatus').value = provider.is_active ? 'Active' : 'Inactive';
        document.getElementById('contractStart').value = provider.contract_start || '';
        document.getElementById('contractEnd').value = provider.contract_end || '';
        document.getElementById('serviceLevel').value = provider.service_level || 'Standard';
        document.getElementById('deliveryTerms').value = provider.delivery_terms || 'FOB';
        document.getElementById('isoCertified').value = provider.iso_certified || 'No';
        document.getElementById('insuranceValid').value = provider.insurance_valid || 'No';
        document.getElementById('permits').value = provider.permits || 'Valid';
        document.getElementById('leadTime').value = provider.lead_time || '7';
        document.getElementById('experience').value = provider.experience || '';
        document.getElementById('providerNotes').value = provider.description || provider.notes || '';
        
        hideLoading();
        providerModal.show();
      } catch (error) {
        hideLoading();
        console.error('Edit provider error:', error);
        showAlert('Failed to load provider details: ' + error.message, 'error');
      }
    }

    async function viewProvider(id) {
      try {
        if (!id || !Number.isInteger(Number(id))) {
          showAlert('Invalid provider ID', 'error');
          return;
        }
        
        showLoading('Loading provider details...', 'Fetching account information');
        const data = await apiRequest(`provider-users-api.php?action=get&id=${id}`);
        
        if (!data.provider) {
          throw new Error('Provider data not found in response');
        }
        
        const provider = data.provider;
        
        const complianceStatus = getComplianceStatus(provider);
        const performanceScore = calculatePerformanceScore(provider);
        const spendData = getHistoricalSpend(provider);
        
        const detailsHtml = `
          <!-- Provider Basic Information -->
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-building"></i> Provider Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <h6><strong>Provider Name:</strong></h6>
                  <p><strong>${provider.name || provider.username}</strong></p>
                </div>
                <div class="col-md-6">
                  <h6><strong>Provider Type:</strong></h6>
                  <p><span class="badge bg-info">${provider.provider_type || 'Individual'}</span></p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <h6><strong>Contact Email:</strong></h6>
                  <p><a href="mailto:${provider.email}">${provider.email}</a></p>
                </div>
                <div class="col-md-6">
                  <h6><strong>Contact Phone:</strong></h6>
                  <p>${provider.phone ? '<a href="tel:' + provider.phone + '">' + provider.phone + '</a>' : 'N/A'}</p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <h6><strong>Service Area:</strong></h6>
                  <p>${provider.service_area || 'N/A'}</p>
                </div>
                <div class="col-md-6">
                  <h6><strong>Years of Experience:</strong></h6>
                  <p>${provider.experience || 'N/A'} years</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Compliance & Certification -->
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-shield-check"></i> Compliance & Certification Status</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <h6><strong>Overall Status:</strong></h6>
                  <p><span class="badge ${complianceStatus.class}">${complianceStatus.text}</span></p>
                </div>
                <div class="col-md-4">
                  <h6><strong>ISO Certification:</strong></h6>
                  <p>${provider.iso_certified || 'N/A'}</p>
                </div>
                <div class="col-md-4">
                  <h6><strong>Insurance Status:</strong></h6>
                  <p>${provider.insurance_valid || 'N/A'}</p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <h6><strong>Business Permits:</strong></h6>
                  <p>${provider.permits || 'Valid'}</p>
                </div>
                <div class="col-md-8">
                  <h6><strong>Account Status:</strong></h6>
                  <p><span class="badge ${getStatusBadgeClass(provider.is_active ? 'Active' : 'Inactive')}">${provider.is_active ? 'Active' : 'Inactive'}</span></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Contract & Agreement Details -->
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Contract & Agreement Details</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <h6><strong>Contract Rate:</strong></h6>
                  <p><strong>₱${provider.monthly_rate || '0.00'}</strong></p>
                </div>
                <div class="col-md-4">
                  <h6><strong>Contract Start:</strong></h6>
                  <p>${formatDate(provider.contract_start) || 'N/A'}</p>
                </div>
                <div class="col-md-4">
                  <h6><strong>Contract End:</strong></h6>
                  <p>${formatDate(provider.contract_end) || 'N/A'}</p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <h6><strong>Service Level:</strong></h6>
                  <p>${provider.service_level || 'Standard'}</p>
                </div>
                <div class="col-md-6">
                  <h6><strong>Contract Validity:</strong></h6>
                  <p>${provider.contract_end ? (new Date(provider.contract_end) > new Date() ? '<span class="text-success">Valid</span>' : '<span class="text-danger">Expired</span>') : 'N/A'}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance & Delivery -->
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header">
                  <h6 class="mb-0"><i class="bi bi-graph-up"></i> Performance Score</h6>
                </div>
                <div class="card-body text-center">
                  <div class="score-circle ${performanceScore.class} mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.1rem;">
                    <strong>${performanceScore.score}</strong>
                  </div>
                  <p class="mb-1"><strong>${performanceScore.projects} Projects Completed</strong></p>
                  <small class="text-muted">Based on past performance</small>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header">
                  <h6 class="mb-0"><i class="bi bi-truck"></i> Delivery Terms</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-12">
                      <h6><strong>Lead Time:</strong></h6>
                      <p>${provider.lead_time || 'N/A'} days</p>
                    </div>
                    <div class="col-12">
                      <h6><strong>Delivery Terms:</strong></h6>
                      <p>${provider.delivery_terms || 'FOB'}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Historical Spend & Purchase Orders -->
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-currency-dollar"></i> Financial Summary</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <h6><strong>Total Historical Spend:</strong></h6>
                  <p><strong>₱${spendData.total}</strong></p>
                </div>
                <div class="col-md-3">
                  <h6><strong>Monthly Average:</strong></h6>
                  <p>₱${spendData.monthly}</p>
                </div>
                <div class="col-md-3">
                  <h6><strong>Active POs:</strong></h6>
                  <p>${provider.active_pos || 0}</p>
                </div>
                <div class="col-md-3">
                  <h6><strong>Total POs:</strong></h6>
                  <p>${provider.total_pos || 0}</p>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <h6><strong>Spend Category:</strong></h6>
                  <p>${spendData.category}</p>
                </div>
                <div class="col-md-6">
                  <h6><strong>Last PO Date:</strong></h6>
                  <p>${formatDate(provider.last_po_date) || 'N/A'}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Account Information -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0"><i class="bi bi-info-circle"></i> Account Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <h6><strong>Last Login:</strong></h6>
                  <p>${provider.last_login ? formatDateTime(provider.last_login) : 'Never'}</p>
                </div>
                <div class="col-md-6">
                  <h6><strong>Account Created:</strong></h6>
                  <p>${formatDateTime(provider.created_at)}</p>
                </div>
              </div>
              ${provider.description || provider.notes ? `<div class="row"><div class="col-md-12"><h6><strong>Additional Notes:</strong></h6><p>${provider.description || provider.notes}</p></div></div>` : ''}
            </div>
          </div>
        `;
        
        document.getElementById('providerDetailsBody').innerHTML = detailsHtml;
        hideLoading();
        providerDetailsModal.show();
      } catch (error) {
        hideLoading();
        console.error('View provider error:', error);
        showAlert('Failed to load provider details: ' + error.message, 'error');
      }
    }

    async function toggleProviderStatus(id, activate) {
      const action = activate ? 'activate' : 'deactivate';
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: `This will ${action} the provider account.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: activate ? '#28a745' : '#ffc107',
        cancelButtonColor: '#3085d6',
        confirmButtonText: `Yes, ${action} it!`,
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
            popup.style.animation = 'swal2-show 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-in');
            backdrop.style.animation = 'swal2-backdrop-show 0.4s ease-out';
          }
        },
        willClose: () => {
          const popup = Swal.getPopup();
          const backdrop = Swal.getContainer();
          
          if (popup) {
            popup.classList.add('swal2-animate-out');
            popup.style.animation = 'swal2-hide 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-out');
            backdrop.style.animation = 'swal2-backdrop-hide 0.3s ease-in';
          }
        }
      });
      
      if (result.isConfirmed) {
        try {
          await apiRequest(`provider-users-api.php?action=toggle-status&id=${id}`, {
            method: 'POST',
            body: JSON.stringify({ is_active: activate })
          });
          
          showAlert(`Provider ${activate ? 'activated' : 'deactivated'} successfully!`, 'success');
          loadProviders();
          loadStats();
        } catch (error) {
          showAlert('Failed to update provider status: ' + error.message, 'error');
        }
      }
    }

    async function deleteProvider(id) {
      const result = await Swal.fire({
        title: 'Are you sure?',
        text: "This provider account will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
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
            popup.style.animation = 'swal2-show 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-in');
            backdrop.style.animation = 'swal2-backdrop-show 0.4s ease-out';
          }
        },
        willClose: () => {
          const popup = Swal.getPopup();
          const backdrop = Swal.getContainer();
          
          if (popup) {
            popup.classList.add('swal2-animate-out');
            popup.style.animation = 'swal2-hide 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-out');
            backdrop.style.animation = 'swal2-backdrop-hide 0.3s ease-in';
          }
        }
      });
      
      if (result.isConfirmed) {
        try {
          await apiRequest(`provider-users-api.php?action=delete&id=${id}`, {
            method: 'DELETE'
          });
          
          showAlert('Provider account deleted successfully!', 'success');
          loadProviders();
          loadStats();
        } catch (error) {
          showAlert('Failed to delete provider: ' + error.message, 'error');
        }
      }
    }

    async function handleProviderFormSubmit(e) {
      e.preventDefault();
      
      const formData = new FormData(e.target);
      const data = {};
      
      for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
          data[key] = value;
        }
      }
      
      // Validate required fields for edit mode
      if (isEditMode) {
        const requiredFields = ['name', 'contact_email'];
        for (let field of requiredFields) {
          if (!data[field] || data[field].trim() === '') {
            showAlert(`${field.replace('_', ' ')} is required`, 'error');
            return;
          }
        }
        
        // Validate email format
        if (data.contact_email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.contact_email)) {
          showAlert('Please enter a valid email address', 'error');
          return;
        }
      }
      
      try {
        if (isEditMode && currentEditingProviderId) {
          // Update existing provider account
          await apiRequest(`provider-users-api.php?action=update&id=${currentEditingProviderId}`, {
            method: 'POST',
            body: JSON.stringify(data)
          });
          showAlert('Provider account updated successfully!', 'success');
        } else {
          // Creating new providers not supported through this interface
          showAlert('New provider accounts must be created through the registration system.', 'info');
          return;
        }
        
        providerModal.hide();
        loadProviders();
        loadStats();
      } catch (error) {
        showAlert('Failed to save provider: ' + error.message, 'error');
      }
    }

    // Utility Functions
    function getStatusBadgeClass(status) {
      switch(status) {
        case 'Active': return 'bg-success';
        case 'Pending': return 'bg-warning';
        case 'Inactive': return 'bg-secondary';
        case 'Suspended': return 'bg-danger';
        default: return 'bg-secondary';
      }
    }

    function getComplianceStatus(provider) {
      // Mock compliance status based on available data
      const isoCertified = provider.iso_certified === 'Yes' || provider.iso_certified === true;
      const insuranceValid = provider.insurance_valid === 'Yes' || provider.insurance_valid === true;
      
      if (isoCertified && insuranceValid) {
        return { text: 'Certified', class: 'compliance-certified' };
      } else if (isoCertified || insuranceValid) {
        return { text: 'Partial', class: 'compliance-pending' };
      } else {
        return { text: 'Pending', class: 'compliance-expired' };
      }
    }

    function calculatePerformanceScore(provider) {
      // Mock performance calculation based on available data
      const projects = provider.completed_projects || Math.floor(Math.random() * 20) + 1;
      let score = 85; // Default score
      let scoreClass = 'good';
      
      // Simulate score based on activity and experience
      if (provider.is_active && provider.experience) {
        const exp = parseInt(provider.experience) || 1;
        score = Math.min(95, 70 + exp * 2 + Math.floor(Math.random() * 10));
      }
      
      if (score >= 90) scoreClass = 'excellent';
      else if (score >= 80) scoreClass = 'good';
      else if (score >= 70) scoreClass = 'average';
      else scoreClass = 'poor';
      
      return {
        score: score + '%',
        projects: projects,
        class: scoreClass
      };
    }

    function getHistoricalSpend(provider) {
      // Mock spend data calculation
      const baseSpend = provider.monthly_rate ? parseFloat(provider.monthly_rate) * 12 : Math.floor(Math.random() * 500000) + 50000;
      const monthlySpend = baseSpend / 12;
      const category = provider.service_area || 'General Services';
      
      return {
        total: formatCurrency(baseSpend),
        monthly: formatCurrency(monthlySpend),
        category: category
      };
    }

    function formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(amount);
    }

    function formatDate(dateStr) {
      if (!dateStr) return 'N/A';
      return new Date(dateStr).toLocaleDateString();
    }

    function formatDateTime(dateStr) {
      if (!dateStr) return 'N/A';
      return new Date(dateStr).toLocaleString();
    }

    function showAlert(message, type = 'info') {
      const icon = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';
      Swal.fire({
        title: type.charAt(0).toUpperCase() + type.slice(1),
        text: message,
        icon: icon,
        timer: 3000,
        timerProgressBar: true,
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
            popup.style.animation = 'swal2-show 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-in');
            backdrop.style.animation = 'swal2-backdrop-show 0.4s ease-out';
          }
        },
        willClose: () => {
          const popup = Swal.getPopup();
          const backdrop = Swal.getContainer();
          
          if (popup) {
            popup.classList.add('swal2-animate-out');
            popup.style.animation = 'swal2-hide 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
          }
          
          if (backdrop) {
            backdrop.classList.add('swal2-backdrop-animate-out');
            backdrop.style.animation = 'swal2-backdrop-hide 0.3s ease-in';
          }
        }
      });
    }

    function showNotification(message, type = 'info') {
      showAlert(message, type);
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

    function updateLoadingText(text = 'Loading...', subtext = 'Please wait') {
      const loadingText = document.getElementById('loadingText');
      const loadingSubtext = document.getElementById('loadingSubtext');
      
      if (loadingText) loadingText.textContent = text;
      if (loadingSubtext) loadingSubtext.textContent = subtext;
    }

    // Supplier Report Generation Functions
    function generateSupplierReport(reportType) {
      // Show loading state
      Swal.fire({
        title: 'Generating Supplier Report',
        text: 'Please wait while we prepare your ' + reportType + ' report...',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      
      // Map report types to appropriate API calls
      let apiReportType = reportType;
      if (reportType === 'summary' || reportType === 'detailed' || reportType === 'compliance') {
        apiReportType = 'suppliers';
      }
      
      // Generate the report
      const reportUrl = 'api/generate-report.php?type=' + apiReportType + '&format=html';
      
      // Open report in new window
      const reportWindow = window.open(reportUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
      
      // Close loading dialog
      setTimeout(() => {
        Swal.close();
        
        if (reportWindow) {
          Swal.fire({
            title: 'Report Generated!',
            text: 'Your supplier ' + reportType + ' report has been opened in a new window. You can print or save it as PDF.',
            icon: 'success',
            confirmButtonText: 'OK',
            footer: '<small>Tip: Use Ctrl+P in the report window to print or save as PDF</small>'
          });
        } else {
          Swal.fire({
            title: 'Popup Blocked',
            text: 'Please allow popups for this site to view the report, or try again.',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
      }, 1000);
    }

    // Add provider modal function (if not already exists)
    function showAddProviderModal() {
      isEditMode = false;
      currentEditingProviderId = null;
      document.getElementById('providerModalLabel').textContent = 'Add New Provider';
      document.getElementById('saveProviderBtn').innerHTML = '<i class="bi bi-check-circle"></i> Save Provider';
      document.getElementById('providerForm').reset();
      providerModal.show();
    }
  </script>
</body>
</html>
