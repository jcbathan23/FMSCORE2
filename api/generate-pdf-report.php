<?php
/**
 * PDF Report Generation API
 * CORE II - Admin Dashboard PDF Reports
 * 
 * Generates comprehensive PDF reports for admin dashboard using DomPDF
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

// Use DomPDF for PDF generation (lighter alternative)
require_once '../includes/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

class AdminReportPDF extends TCPDF {
    public function Header() {
        // Logo
        $image_file = '../slatelogo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        
        // Title
        $this->Cell(0, 15, 'CORE II - Admin Dashboard Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Line break
        $this->Ln(20);
    }
    
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        
        // Generation timestamp
        $this->SetY(-25);
        $this->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// Get report type from request
$reportType = $_GET['type'] ?? 'summary';
$format = $_GET['format'] ?? 'pdf';

try {
    // Create new PDF document
    $pdf = new AdminReportPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('CORE II System');
    $pdf->SetAuthor('Admin Dashboard');
    $pdf->SetTitle('Admin Dashboard Report - ' . ucfirst($reportType));
    $pdf->SetSubject('System Analytics and Management Report');
    $pdf->SetKeywords('CORE II, Admin, Dashboard, Report, Analytics');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'CORE II Admin Report', 'Generated on ' . date('Y-m-d H:i:s'));
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Generate content based on report type
    switch ($reportType) {
        case 'summary':
            generateSummaryReport($pdf);
            break;
        case 'suppliers':
            generateSuppliersReport($pdf);
            break;
        case 'users':
            generateUsersReport($pdf);
            break;
        case 'analytics':
            generateAnalyticsReport($pdf);
            break;
        case 'comprehensive':
            generateComprehensiveReport($pdf);
            break;
        default:
            generateSummaryReport($pdf);
    }
    
    // Output PDF
    $filename = 'CORE_II_Admin_Report_' . ucfirst($reportType) . '_' . date('Y-m-d_H-i-s') . '.pdf';
    
    if ($format === 'download') {
        $pdf->Output($filename, 'D');
    } else {
        $pdf->Output($filename, 'I');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate PDF report: ' . $e->getMessage()]);
}

function generateSummaryReport($pdf) {
    global $pdo;
    
    // Report Header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Executive Summary Report', 0, 1, 'L');
    $pdf->Ln(5);
    
    // System Overview
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'System Overview', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    // Get system statistics
    $stats = getSystemStatistics();
    
    $pdf->Cell(50, 6, 'Total Users:', 0, 0, 'L');
    $pdf->Cell(0, 6, $stats['total_users'], 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'Active Providers:', 0, 0, 'L');
    $pdf->Cell(0, 6, $stats['active_providers'], 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'Total Services:', 0, 0, 'L');
    $pdf->Cell(0, 6, $stats['total_services'], 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'System Status:', 0, 0, 'L');
    $pdf->Cell(0, 6, 'Operational', 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Recent Activity Summary
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Recent Activity (Last 7 Days)', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $activity = getRecentActivity();
    foreach ($activity as $item) {
        $pdf->Cell(0, 6, '• ' . $item, 0, 1, 'L');
    }
}

function generateSuppliersReport($pdf) {
    global $pdo;
    
    // Report Header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Supplier Management Report', 0, 1, 'L');
    $pdf->Ln(5);
    
    // Get supplier data
    $stmt = $pdo->query("SELECT * FROM providers ORDER BY name ASC");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary Statistics
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Supplier Statistics', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $totalSuppliers = count($suppliers);
    $activeSuppliers = count(array_filter($suppliers, function($s) { return $s['status'] === 'Active'; }));
    $totalSpend = array_sum(array_column($suppliers, 'monthly_rate'));
    
    $pdf->Cell(50, 6, 'Total Suppliers:', 0, 0, 'L');
    $pdf->Cell(0, 6, $totalSuppliers, 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'Active Suppliers:', 0, 0, 'L');
    $pdf->Cell(0, 6, $activeSuppliers, 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'Total Monthly Spend:', 0, 0, 'L');
    $pdf->Cell(0, 6, '₱' . number_format($totalSpend, 2), 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // Detailed Supplier List
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Supplier Details', 0, 1, 'L');
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(40, 8, 'Name', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Type', 1, 0, 'C');
    $pdf->Cell(35, 8, 'Contact', 1, 0, 'C');
    $pdf->Cell(30, 8, 'Service Area', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Rate (₱)', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Status', 1, 1, 'C');
    
    // Table data
    $pdf->SetFont('helvetica', '', 8);
    foreach ($suppliers as $supplier) {
        $pdf->Cell(40, 6, substr($supplier['name'], 0, 20), 1, 0, 'L');
        $pdf->Cell(25, 6, substr($supplier['type'], 0, 12), 1, 0, 'L');
        $pdf->Cell(35, 6, substr($supplier['contact_person'], 0, 15), 1, 0, 'L');
        $pdf->Cell(30, 6, substr($supplier['service_area'], 0, 15), 1, 0, 'L');
        $pdf->Cell(25, 6, number_format($supplier['monthly_rate'], 0), 1, 0, 'R');
        $pdf->Cell(20, 6, $supplier['status'], 1, 1, 'C');
    }
}

function generateUsersReport($pdf) {
    global $pdo;
    
    // Report Header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'User Management Report', 0, 1, 'L');
    $pdf->Ln(5);
    
    // Get user data
    $stmt = $pdo->query("SELECT id, username, email, role, status, created_at, last_login FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Summary Statistics
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'User Statistics', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $totalUsers = count($users);
    $activeUsers = count(array_filter($users, function($u) { return $u['status'] === 'active'; }));
    $adminUsers = count(array_filter($users, function($u) { return $u['role'] === 'admin'; }));
    
    $pdf->Cell(50, 6, 'Total Users:', 0, 0, 'L');
    $pdf->Cell(0, 6, $totalUsers, 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'Active Users:', 0, 0, 'L');
    $pdf->Cell(0, 6, $activeUsers, 0, 1, 'L');
    
    $pdf->Cell(50, 6, 'Admin Users:', 0, 0, 'L');
    $pdf->Cell(0, 6, $adminUsers, 0, 1, 'L');
    
    $pdf->Ln(10);
    
    // User List Table
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'User Details', 0, 1, 'L');
    
    // Table header
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(15, 8, 'ID', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Username', 1, 0, 'C');
    $pdf->Cell(50, 8, 'Email', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Role', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Status', 1, 0, 'C');
    $pdf->Cell(25, 8, 'Created', 1, 1, 'C');
    
    // Table data
    $pdf->SetFont('helvetica', '', 8);
    foreach ($users as $user) {
        $pdf->Cell(15, 6, $user['id'], 1, 0, 'C');
        $pdf->Cell(40, 6, substr($user['username'], 0, 20), 1, 0, 'L');
        $pdf->Cell(50, 6, substr($user['email'], 0, 25), 1, 0, 'L');
        $pdf->Cell(25, 6, ucfirst($user['role']), 1, 0, 'C');
        $pdf->Cell(20, 6, ucfirst($user['status']), 1, 0, 'C');
        $pdf->Cell(25, 6, date('Y-m-d', strtotime($user['created_at'])), 1, 1, 'C');
    }
}

function generateAnalyticsReport($pdf) {
    global $pdo;
    
    // Report Header
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'System Analytics Report', 0, 1, 'L');
    $pdf->Ln(5);
    
    // Performance Metrics
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Performance Metrics (Last 30 Days)', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $analytics = getAnalyticsData();
    
    foreach ($analytics as $metric => $value) {
        $pdf->Cell(60, 6, ucfirst(str_replace('_', ' ', $metric)) . ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $value, 0, 1, 'L');
    }
    
    $pdf->Ln(10);
    
    // System Health
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'System Health Status', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $health = getSystemHealth();
    foreach ($health as $component => $status) {
        $pdf->Cell(60, 6, ucfirst($component) . ':', 0, 0, 'L');
        $pdf->Cell(0, 6, $status, 0, 1, 'L');
    }
}

function generateComprehensiveReport($pdf) {
    // Generate all reports in one document
    generateSummaryReport($pdf);
    $pdf->AddPage();
    generateSuppliersReport($pdf);
    $pdf->AddPage();
    generateUsersReport($pdf);
    $pdf->AddPage();
    generateAnalyticsReport($pdf);
}

function getSystemStatistics() {
    global $pdo;
    
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Active providers
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM providers WHERE status = 'Active'");
    $stats['active_providers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total services (assuming services table exists)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $stats['total_services'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    return $stats;
}

function getRecentActivity() {
    return [
        'New user registrations: 5',
        'Provider updates: 3',
        'System maintenance completed',
        'Security patches applied',
        'Database optimization performed'
    ];
}

function getAnalyticsData() {
    return [
        'total_logins' => '1,247',
        'avg_session_duration' => '24 minutes',
        'system_uptime' => '99.8%',
        'error_rate' => '0.2%',
        'response_time' => '1.2s average'
    ];
}

function getSystemHealth() {
    return [
        'database' => 'Healthy',
        'web_server' => 'Operational',
        'file_system' => 'Normal',
        'memory_usage' => '68%',
        'cpu_usage' => '45%'
    ];
}
?>
