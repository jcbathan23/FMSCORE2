<?php
/**
 * Test Report Generation
 * Quick test to verify PDF report functionality
 */

session_start();

// Set test session for admin access
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test PDF Report Generation - CORE II</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .test-title {
            color: #4e73df;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .test-btn {
            margin: 0.5rem;
            min-width: 200px;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-ready { background-color: #28a745; }
        .status-testing { background-color: #ffc107; }
        .status-error { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container test-container">
        <div class="test-card">
            <h1 class="test-title">
                <i class="bi bi-file-earmark-pdf"></i> 
                PDF Report Generation Test
            </h1>
            <p class="text-muted">Test the PDF report generation functionality for the CORE II admin dashboard.</p>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h5><span class="status-indicator status-ready"></span>Admin Dashboard Reports</h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary test-btn" onclick="testReport('summary')">
                            <i class="bi bi-file-text"></i> Summary Report
                        </button>
                        <button class="btn btn-info test-btn" onclick="testReport('users')">
                            <i class="bi bi-people"></i> Users Report
                        </button>
                        <button class="btn btn-warning test-btn" onclick="testReport('analytics')">
                            <i class="bi bi-graph-up"></i> Analytics Report
                        </button>
                        <button class="btn btn-success test-btn" onclick="testReport('comprehensive')">
                            <i class="bi bi-file-earmark-pdf-fill"></i> Comprehensive Report
                        </button>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h5><span class="status-indicator status-ready"></span>Supplier Management Reports</h5>
                    <div class="d-grid gap-2">
                        <button class="btn btn-secondary test-btn" onclick="testReport('suppliers')">
                            <i class="bi bi-building"></i> Suppliers Report
                        </button>
                        <button class="btn btn-outline-primary test-btn" onclick="testSupplierReport('summary')">
                            <i class="bi bi-file-text"></i> Supplier Summary
                        </button>
                        <button class="btn btn-outline-success test-btn" onclick="testSupplierReport('compliance')">
                            <i class="bi bi-shield-check"></i> Compliance Report
                        </button>
                        <button class="btn btn-outline-info test-btn" onclick="testSupplierReport('detailed')">
                            <i class="bi bi-file-earmark-spreadsheet"></i> Detailed Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="test-card">
            <h4 class="test-title">
                <i class="bi bi-info-circle"></i> 
                Test Instructions
            </h4>
            <ul class="list-unstyled">
                <li><i class="bi bi-check-circle text-success"></i> Click any report button above to test generation</li>
                <li><i class="bi bi-check-circle text-success"></i> Reports will open in a new window</li>
                <li><i class="bi bi-check-circle text-success"></i> Use Ctrl+P in the report window to print or save as PDF</li>
                <li><i class="bi bi-check-circle text-success"></i> Check browser console for any errors</li>
            </ul>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-lightbulb"></i>
                <strong>Tip:</strong> If popups are blocked, you'll see a warning. Please allow popups for this site.
            </div>
        </div>
        
        <div class="test-card">
            <h4 class="test-title">
                <i class="bi bi-gear"></i> 
                Quick Access Links
            </h4>
            <div class="row">
                <div class="col-md-6">
                    <a href="modules/dashboard/dashboard.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-speedometer2"></i> Admin Dashboard
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="service-provider.php" class="btn btn-outline-success w-100 mb-2">
                        <i class="bi bi-building"></i> Supplier Management
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Test report generation functions
        function testReport(reportType) {
            showLoadingAlert('Generating ' + reportType + ' report...');
            
            const reportUrl = 'api/generate-report.php?type=' + reportType + '&format=html';
            const reportWindow = window.open(reportUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            
            setTimeout(() => {
                Swal.close();
                
                if (reportWindow) {
                    Swal.fire({
                        title: 'Test Successful!',
                        text: 'Report opened in new window. Check if content loads correctly.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Popup Blocked',
                        text: 'Please allow popups and try again.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            }, 1000);
        }
        
        function testSupplierReport(reportType) {
            showLoadingAlert('Generating supplier ' + reportType + ' report...');
            
            const reportUrl = 'api/generate-report.php?type=suppliers&format=html';
            const reportWindow = window.open(reportUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            
            setTimeout(() => {
                Swal.close();
                
                if (reportWindow) {
                    Swal.fire({
                        title: 'Test Successful!',
                        text: 'Supplier report opened in new window. Check if content loads correctly.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Popup Blocked',
                        text: 'Please allow popups and try again.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            }, 1000);
        }
        
        function showLoadingAlert(message) {
            Swal.fire({
                title: 'Testing Report Generation',
                text: message,
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Show welcome message
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'PDF Report Test Ready!',
                text: 'Click any report button to test the PDF generation functionality.',
                icon: 'info',
                confirmButtonText: 'Start Testing',
                timer: 3000
            });
        });
    </script>
</body>
</html>
