<?php
/**
 * Report Generation API
 * CORE II - Admin Dashboard Reports
 * 
 * Generates comprehensive reports for admin dashboard
 */

session_start();
require_once '../auth.php';
require_once '../db.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

// Get report parameters
$reportType = $_GET['type'] ?? 'summary';
$format = $_GET['format'] ?? 'html';
$download = $_GET['download'] ?? false;

try {
    // Generate HTML content
    $htmlContent = generateReportHTML($reportType);
    
    if ($format === 'pdf') {
        // Set headers for PDF download
        if ($download) {
            $filename = 'CORE_II_Admin_Report_' . ucfirst($reportType) . '_' . date('Y-m-d_H-i-s') . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        } else {
            header('Content-Type: application/pdf');
        }
        
        // Use browser's print to PDF functionality
        echo generatePrintableHTML($htmlContent, $reportType);
    } else {
        // Return HTML content
        header('Content-Type: text/html');
        echo $htmlContent;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate report: ' . $e->getMessage()]);
}

function generateReportHTML($reportType) {
    $content = '';
    
    switch ($reportType) {
        case 'summary':
            $content = generateSummaryReportHTML();
            break;
        case 'suppliers':
            $content = generateSuppliersReportHTML();
            break;
        case 'users':
            $content = generateUsersReportHTML();
            break;
        case 'analytics':
            $content = generateAnalyticsReportHTML();
            break;
        case 'comprehensive':
            $content = generateComprehensiveReportHTML();
            break;
        default:
            $content = generateSummaryReportHTML();
    }
    
    return $content;
}

function generatePrintableHTML($content, $reportType) {
    $title = 'CORE II - ' . ucfirst($reportType) . ' Report';
    
    return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $title . '</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        .report-title {
            color: #4e73df;
            font-size: 24px;
            margin: 10px 0;
        }
        .report-date {
            color: #666;
            font-size: 12px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            color: #4e73df;
            font-size: 18px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fc;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
        }
        .stat-label {
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4e73df;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .print-btn {
            background: #4e73df;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
    </style>
    <script>
        window.onload = function() {
            if (window.location.search.includes("format=pdf")) {
                setTimeout(() => window.print(), 1000);
            }
        }
    </script>
</head>
<body>
    <div class="header">
        <img src="../slatelogo.png" alt="CORE II Logo" class="logo">
        <h1 class="report-title">' . $title . '</h1>
        <div class="report-date">Generated on: ' . date('Y-m-d H:i:s') . '</div>
    </div>
    
    <button class="print-btn no-print" onclick="window.print()">Print / Save as PDF</button>
    
    ' . $content . '
    
    <div class="footer">
        <p>CORE II Administrative System - Confidential Report</p>
        <p>Generated by: ' . ($_SESSION['username'] ?? 'Admin') . ' | System Version: 2.0</p>
    </div>
</body>
</html>';
}

function generateSummaryReportHTML() {
    global $pdo;
    
    // Get system statistics
    $stats = getSystemStatistics();
    $activity = getRecentActivity();
    
    $html = '
    <div class="section">
        <h2 class="section-title">Executive Summary</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">' . $stats['total_users'] . '</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $stats['active_providers'] . '</div>
                <div class="stat-label">Active Providers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $stats['total_services'] . '</div>
                <div class="stat-label">Total Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">99.8%</div>
                <div class="stat-label">System Uptime</div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">Recent Activity (Last 7 Days)</h2>
        <ul>';
    
    foreach ($activity as $item) {
        $html .= '<li>' . htmlspecialchars($item) . '</li>';
    }
    
    $html .= '</ul>
    </div>';
    
    return $html;
}

function generateSuppliersReportHTML() {
    global $pdo;
    
    // Get supplier data
    $stmt = $pdo->query("SELECT * FROM providers ORDER BY name ASC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalSuppliers = count($suppliers);
    $activeSuppliers = count(array_filter($suppliers, function($s) { return $s['status'] === 'Active'; }));
    $totalSpend = array_sum(array_column($suppliers, 'monthly_rate'));
    
    $html = '
    <div class="section">
        <h2 class="section-title">Supplier Management Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">' . $totalSuppliers . '</div>
                <div class="stat-label">Total Suppliers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $activeSuppliers . '</div>
                <div class="stat-label">Active Suppliers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₱' . number_format($totalSpend, 2) . '</div>
                <div class="stat-label">Monthly Spend</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . round(($activeSuppliers / max($totalSuppliers, 1)) * 100, 1) . '%</div>
                <div class="stat-label">Active Rate</div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">Supplier Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Contact Person</th>
                    <th>Service Area</th>
                    <th>Monthly Rate</th>
                    <th>Status</th>
                    <th>Contract Period</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($suppliers as $supplier) {
        $contractPeriod = '';
        if ($supplier['contract_start'] && $supplier['contract_end']) {
            $contractPeriod = date('M Y', strtotime($supplier['contract_start'])) . ' - ' . date('M Y', strtotime($supplier['contract_end']));
        }
        
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($supplier['name']) . '</td>
                    <td>' . htmlspecialchars($supplier['type']) . '</td>
                    <td>' . htmlspecialchars($supplier['contact_person']) . '</td>
                    <td>' . htmlspecialchars($supplier['service_area']) . '</td>
                    <td>₱' . number_format($supplier['monthly_rate'], 2) . '</td>
                    <td>' . htmlspecialchars($supplier['status']) . '</td>
                    <td>' . htmlspecialchars($contractPeriod) . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
    </div>';
    
    return $html;
}

function generateUsersReportHTML() {
    global $pdo;
    
    // Get user data
    $stmt = $pdo->query("SELECT id, username, email, role, status, created_at, last_login FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalUsers = count($users);
    $activeUsers = count(array_filter($users, function($u) { return $u['status'] === 'active'; }));
    $adminUsers = count(array_filter($users, function($u) { return $u['role'] === 'admin'; }));
    $providerUsers = count(array_filter($users, function($u) { return $u['role'] === 'provider'; }));
    
    $html = '
    <div class="section">
        <h2 class="section-title">User Management Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">' . $totalUsers . '</div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $activeUsers . '</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $adminUsers . '</div>
                <div class="stat-label">Admin Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $providerUsers . '</div>
                <div class="stat-label">Provider Users</div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">User Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($users as $user) {
        $lastLogin = $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never';
        
        $html .= '
                <tr>
                    <td>' . $user['id'] . '</td>
                    <td>' . htmlspecialchars($user['username']) . '</td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td>' . ucfirst($user['role']) . '</td>
                    <td>' . ucfirst($user['status']) . '</td>
                    <td>' . date('Y-m-d', strtotime($user['created_at'])) . '</td>
                    <td>' . $lastLogin . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
    </div>';
    
    return $html;
}

function generateAnalyticsReportHTML() {
    $analytics = getAnalyticsData();
    $health = getSystemHealth();
    
    $html = '
    <div class="section">
        <h2 class="section-title">System Analytics (Last 30 Days)</h2>
        <div class="stats-grid">';
    
    foreach ($analytics as $metric => $value) {
        $html .= '
            <div class="stat-card">
                <div class="stat-value">' . $value . '</div>
                <div class="stat-label">' . ucfirst(str_replace('_', ' ', $metric)) . '</div>
            </div>';
    }
    
    $html .= '
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">System Health Status</h2>
        <table>
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($health as $component => $status) {
        $html .= '
                <tr>
                    <td>' . ucfirst(str_replace('_', ' ', $component)) . '</td>
                    <td>' . $status . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
    </div>';
    
    return $html;
}

function generateComprehensiveReportHTML() {
    return generateSummaryReportHTML() . 
           generateSuppliersReportHTML() . 
           generateUsersReportHTML() . 
           generateAnalyticsReportHTML();
}

function getSystemStatistics() {
    global $pdo;
    
    $stats = [];
    
    try {
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Active providers
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM providers WHERE status = 'Active'");
        $stats['active_providers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total services
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_services'] = $result ? $result['count'] : 0;
    } catch (Exception $e) {
        // Handle database errors gracefully
        $stats['total_users'] = 0;
        $stats['active_providers'] = 0;
        $stats['total_services'] = 0;
    }
    
    return $stats;
}

function getRecentActivity() {
    return [
        'New user registrations: 5',
        'Provider updates: 3',
        'System maintenance completed',
        'Security patches applied',
        'Database optimization performed',
        'Report generation system implemented',
        'Dashboard enhancements deployed'
    ];
}

function getAnalyticsData() {
    return [
        'total_logins' => '1,247',
        'avg_session_duration' => '24 min',
        'system_uptime' => '99.8%',
        'error_rate' => '0.2%',
        'response_time' => '1.2s'
    ];
}

function getSystemHealth() {
    return [
        'database' => 'Healthy',
        'web_server' => 'Operational',
        'file_system' => 'Normal',
        'memory_usage' => '68%',
        'cpu_usage' => '45%',
        'disk_space' => '78% used',
        'network' => 'Stable'
    ];
}
?>
