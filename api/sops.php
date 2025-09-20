<?php
/**
 * CORE II - Enhanced SOP Manager REST API
 * 
 * Endpoints:
 * - Basic CRUD operations for SOPs
 * - GET /api/sops.php?action=templates - Get SOP templates
 * - GET /api/sops.php?action=version_history&id={id} - Get version history
 * - GET /api/sops.php?action=compliance_status - Get compliance metrics
 * - POST /api/sops.php?action=approve&id={id} - Approve SOP
 * - POST /api/sops.php?action=review&id={id} - Send for review
 * - GET /api/sops.php?action=dashboard_stats - Get dashboard statistics
 * - GET /api/sops.php?action=core_integration - Get CORE system integration paths
 * - GET /api/sops.php?action=module_paths - Get all module directory paths
 */

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

// Define CORE system paths
define('CORE_BASE_PATH', __DIR__ . '/..');
define('CORE_API_PATH', __DIR__);
define('CORE_MODULES_PATH', CORE_BASE_PATH . '/modules');
define('CORE_INCLUDES_PATH', CORE_BASE_PATH . '/includes');

// CORS headers for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$action = $_GET['action'] ?? '';

// Route requests based on action parameter
if ($action) {
    handleActionRequests($action, $method, $id);
} else {
    handleCrudRequests($method, $id);
}

/**
 * Handle CRUD operations for SOPs
 */
function handleCrudRequests($method, $id) {
    global $mysqli;
    
    switch ($method) {
        case 'GET':
            if ($id > 0) {
                getSingleSOP($id);
            } else {
                getAllSOPs();
            }
            break;
            
        case 'POST':
            createSOP();
            break;
            
        case 'PUT':
            if ($id <= 0) return send_json(['error' => 'Missing id'], 400);
            updateSOP($id);
            break;
            
        case 'DELETE':
            if ($id <= 0) return send_json(['error' => 'Missing id'], 400);
            deleteSOP($id);
            break;
            
        default:
            return send_json(['error' => 'Method not allowed'], 405);
    }
}

/**
 * Handle special action requests
 */
function handleActionRequests($action, $method, $id) {
    switch ($action) {
        case 'templates':
            getSOPTemplates();
            break;
            
        case 'version_history':
            if ($id <= 0) return send_json(['error' => 'Missing SOP id'], 400);
            getVersionHistory($id);
            break;
            
        case 'compliance_status':
            getComplianceStatus();
            break;
            
        case 'approve':
            if ($method !== 'POST') return send_json(['error' => 'Method not allowed'], 405);
            if ($id <= 0) return send_json(['error' => 'Missing SOP id'], 400);
            approveSOP($id);
            break;
            
        case 'review':
            if ($method !== 'POST') return send_json(['error' => 'Method not allowed'], 405);
            if ($id <= 0) return send_json(['error' => 'Missing SOP id'], 400);
            sendForReview($id);
            break;
            
        case 'dashboard_stats':
            getDashboardStats();
            break;
            
        case 'core_integration':
            getCoreIntegration();
            break;
            
        case 'module_paths':
            getModulePaths();
            break;
            
        default:
            return send_json(['error' => 'Unknown action'], 400);
    }
}

/**
 * Get single SOP with enhanced data
 */
function getSingleSOP($id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare('SELECT * FROM sops WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if (!$row) {
        return send_json(['error' => 'SOP not found'], 404);
    }
    
    // Add compliance status
    $row['compliance_status'] = calculateComplianceStatus($row);
    
    return send_json($row);
}

/**
 * Get all SOPs with filtering
 */
function getAllSOPs() {
    global $mysqli;
    
    $filters = [];
    $whereClause = '';
    
    // Build filters
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $filters[] = "status = '" . $mysqli->real_escape_string($_GET['status']) . "'";
    }
    
    if (isset($_GET['category']) && $_GET['category'] !== '') {
        $filters[] = "category = '" . $mysqli->real_escape_string($_GET['category']) . "'";
    }
    
    if (isset($_GET['department']) && $_GET['department'] !== '') {
        $filters[] = "department = '" . $mysqli->real_escape_string($_GET['department']) . "'";
    }
    
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $searchTerm = $mysqli->real_escape_string($_GET['search']);
        $filters[] = "(title LIKE '%$searchTerm%' OR purpose LIKE '%$searchTerm%' OR procedures LIKE '%$searchTerm%')";
    }
    
    if ($filters) {
        $whereClause = 'WHERE ' . implode(' AND ', $filters);
    }
    
    $orderBy = $_GET['sort'] ?? 'id';
    $orderDir = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
    
    $sql = "SELECT * FROM sops $whereClause ORDER BY $orderBy $orderDir";
    $result = $mysqli->query($sql);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['compliance_status'] = calculateComplianceStatus($row);
        $data[] = $row;
    }
    
    return send_json($data);
}

/**
 * Create new SOP
 */
function createSOP() {
    global $mysqli;
    
    $body = read_json_body();
    $errors = validate_sop($body);
    if ($errors) return send_json(['errors' => $errors], 422);
    
    $stmt = $mysqli->prepare('INSERT INTO sops (title,category,department,version,status,review_date,purpose,scope,responsibilities,procedures,equipment,safety_notes,notes,compliance_standard,workflow_type,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
    $stmt->bind_param(
        'sssssssssssssss',
        $body['title'],$body['category'],$body['department'],$body['version'],$body['status'],$body['reviewDate'],$body['purpose'],$body['scope'],$body['responsibilities'],$body['procedures'],$body['equipment']??'',$body['safetyNotes']??'',$body['notes']??'',$body['complianceStandard']??'',$body['workflowType']??''
    );
    if (!$stmt->execute()) return send_json(['error' => 'Insert failed', 'details' => $stmt->error], 500);
    return after_write($stmt->insert_id);
}

/**
 * Update existing SOP
 */
function updateSOP($id) {
    global $mysqli;
    
    $body = read_json_body();
    $errors = validate_sop($body);
    if ($errors) return send_json(['errors' => $errors], 422);
    
    $stmt = $mysqli->prepare('UPDATE sops SET title=?,category=?,department=?,version=?,status=?,review_date=?,purpose=?,scope=?,responsibilities=?,procedures=?,equipment=?,safety_notes=?,notes=?,compliance_standard=?,workflow_type=?,updated_at=NOW() WHERE id=?');
    $stmt->bind_param(
        'sssssssssssssssi',
        $body['title'],$body['category'],$body['department'],$body['version'],$body['status'],$body['reviewDate'],$body['purpose'],$body['scope'],$body['responsibilities'],$body['procedures'],$body['equipment']??'',$body['safetyNotes']??'',$body['notes']??'',$body['complianceStandard']??'',$body['workflowType']??'',$id
    );
    if (!$stmt->execute()) return send_json(['error' => 'Update failed', 'details' => $stmt->error], 500);
    return after_write($id);
}

/**
 * Delete SOP
 */
function deleteSOP($id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare('DELETE FROM sops WHERE id = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) return send_json(['error' => 'Delete failed', 'details' => $stmt->error], 500);
    return send_json(['success' => true, 'message' => 'SOP deleted successfully']);
}

function validate_sop(array $b): array {
	$e = [];
	$req = ['title','category','department','version','status','reviewDate','purpose','scope','responsibilities','procedures'];
	foreach ($req as $f) if (!isset($b[$f]) || trim((string)$b[$f]) === '') $e[$f] = 'Required';
	return $e;
}

function after_write(int $id): void {
	global $mysqli;
	$stmt = $mysqli->prepare('SELECT * FROM sops WHERE id = ?');
	$stmt->bind_param('i', $id);
	$stmt->execute();
	$row = $stmt->get_result()->fetch_assoc();
	if ($row) {
		$row['compliance_status'] = calculateComplianceStatus($row);
	}
	send_json($row, 201);
}

/**
 * Get SOP templates for different workflows
 */
function getSOPTemplates() {
    $templates = [
        'booking' => [
            'title' => 'Shipment Booking Process',
            'category' => 'Operations',
            'department' => 'Operations',
            'purpose' => 'To standardize the shipment booking process and ensure all required information is captured accurately.',
            'scope' => 'This SOP applies to all shipment booking activities within the logistics network.',
            'responsibilities' => 'Customer Service Representatives are responsible for following this procedure for all booking requests.',
            'procedures' => "1. Verify customer information and credentials\n2. Validate shipment details (origin, destination, cargo type)\n3. Check capacity availability for requested dates\n4. Calculate pricing based on current tariffs\n5. Generate booking confirmation with reference number\n6. Send confirmation to customer via email\n7. Update system with booking details\n8. Notify operations team of new booking",
            'equipment' => 'Computer system, booking software, printer, email system',
            'safetyNotes' => 'Ensure all hazardous materials are properly declared and documented according to regulations.',
            'complianceStandard' => 'iso9001',
            'workflowType' => 'booking'
        ],
        'customs' => [
            'title' => 'Customs Clearance Process',
            'category' => 'Compliance',
            'department' => 'Documentation',
            'purpose' => 'To ensure compliant and efficient customs clearance for all shipments.',
            'scope' => 'This SOP applies to all import and export customs clearance activities.',
            'responsibilities' => 'Customs brokers and documentation specialists are responsible for executing this procedure.',
            'procedures' => "1. Prepare customs documentation (commercial invoice, packing list, etc.)\n2. Submit electronic declarations to customs authorities\n3. Pay applicable duties and taxes\n4. Coordinate physical inspections if required\n5. Obtain release authorization from customs\n6. Notify warehouse and transportation teams\n7. Update shipment status in tracking system",
            'equipment' => 'Customs software, document scanner, secure payment system',
            'safetyNotes' => 'Ensure all documentation is accurate to avoid delays and penalties.',
            'complianceStandard' => 'customs',
            'workflowType' => 'customs'
        ],
        'consolidation' => [
            'title' => 'Consolidation Process',
            'category' => 'Operations',
            'department' => 'Warehouse',
            'purpose' => 'To optimize cargo consolidation while maintaining shipment integrity and traceability.',
            'scope' => 'This SOP applies to all consolidation activities in warehouse operations.',
            'responsibilities' => 'Warehouse supervisors and consolidation specialists execute this procedure.',
            'procedures' => "1. Sort shipments by destination and compatibility\n2. Verify cargo compatibility and restrictions\n3. Perform quality and security checks\n4. Create consolidation manifest with all shipment details\n5. Secure and label consolidated cargo containers\n6. Update tracking system with consolidation details\n7. Generate shipping documentation\n8. Schedule transportation pickup",
            'equipment' => 'Warehouse management system, scales, labeling equipment, security seals',
            'safetyNotes' => 'Follow proper lifting techniques and use appropriate equipment for heavy cargo.',
            'complianceStandard' => 'iso9001',
            'workflowType' => 'consolidation'
        ],
        'billing' => [
            'title' => 'Billing & Invoice Process',
            'category' => 'Finance',
            'department' => 'Billing',
            'purpose' => 'To ensure accurate and timely billing for all logistics services provided.',
            'scope' => 'This SOP applies to all billing and invoicing activities.',
            'responsibilities' => 'Billing specialists and finance team members execute this procedure.',
            'procedures' => "1. Collect all service completion confirmations\n2. Verify charges against approved tariffs\n3. Calculate additional fees and surcharges\n4. Generate detailed invoice with service breakdown\n5. Review invoice for accuracy and completeness\n6. Send invoice to customer via preferred method\n7. Update accounts receivable system\n8. Follow up on payment status as needed",
            'equipment' => 'Billing software, invoice templates, email system, payment tracking tools',
            'safetyNotes' => 'Ensure all financial data is handled securely and confidentially.',
            'complianceStandard' => 'iso27001',
            'workflowType' => 'billing'
        ]
    ];
    
    return send_json($templates);
}

/**
 * Get version history for a specific SOP
 */
function getVersionHistory($sopId) {
    // Simulate version history data since we don't have version tables yet
    $versionHistory = [
        [
            'version' => '2.0',
            'date' => date('Y-m-d'),
            'author' => 'Admin',
            'changes' => 'Current version - Updated procedures and compliance standards'
        ],
        [
            'version' => '1.1',
            'date' => '2024-01-15',
            'author' => 'Manager',
            'changes' => 'Updated safety protocols and equipment requirements'
        ],
        [
            'version' => '1.0',
            'date' => '2023-12-01',
            'author' => 'Admin',
            'changes' => 'Initial creation of SOP'
        ]
    ];
    
    return send_json($versionHistory);
}

/**
 * Get compliance status and metrics
 */
function getComplianceStatus() {
    global $mysqli;
    
    // Get overall compliance metrics
    $result = $mysqli->query('
        SELECT 
            COUNT(*) as total_sops,
            SUM(CASE WHEN status = "Active" THEN 1 ELSE 0 END) as active_sops,
            SUM(CASE WHEN status IN ("Under Review", "Pending Approval") THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN review_date < CURDATE() THEN 1 ELSE 0 END) as overdue_reviews
        FROM sops
    ');
    $metrics = $result->fetch_assoc();
    
    // Calculate compliance rate
    $complianceRate = $metrics['total_sops'] > 0 ? 
        (($metrics['active_sops'] / $metrics['total_sops']) * 100) : 0;
    
    return send_json([
        'metrics' => [
            'total_sops' => (int)$metrics['total_sops'],
            'active_sops' => (int)$metrics['active_sops'],
            'pending_review' => (int)$metrics['pending_review'],
            'overdue_reviews' => (int)$metrics['overdue_reviews'],
            'compliance_rate' => round($complianceRate, 1)
        ],
        'alerts' => [
            'overdue_reviews' => (int)$metrics['overdue_reviews'],
            'pending_approvals' => (int)$metrics['pending_review']
        ]
    ]);
}

/**
 * Approve SOP
 */
function approveSOP($id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare('UPDATE sops SET status = "Approved", updated_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        return send_json(['success' => true, 'message' => 'SOP approved successfully']);
    } else {
        return send_json(['error' => 'Failed to approve SOP'], 500);
    }
}

/**
 * Send SOP for review
 */
function sendForReview($id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare('UPDATE sops SET status = "Under Review", updated_at = NOW() WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        return send_json(['success' => true, 'message' => 'SOP sent for review successfully']);
    } else {
        return send_json(['error' => 'Failed to send SOP for review'], 500);
    }
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    global $mysqli;
    
    $result = $mysqli->query('
        SELECT 
            COUNT(*) as total_sops,
            SUM(CASE WHEN status = "Active" THEN 1 ELSE 0 END) as active_sops,
            SUM(CASE WHEN status IN ("Under Review", "Pending Approval") THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 ELSE 0 END) as recent_updates
        FROM sops
    ');
    $stats = $result->fetch_assoc();
    
    return send_json([
        'totalSOPs' => (int)$stats['total_sops'],
        'activeSOPs' => (int)$stats['active_sops'],
        'pendingReview' => (int)$stats['pending_review'],
        'recentUpdates' => (int)$stats['recent_updates']
    ]);
}

/**
 * Calculate compliance status for an SOP
 */
function calculateComplianceStatus($sop) {
    // Simple compliance calculation based on status and review date
    if ($sop['status'] === 'Active' && strtotime($sop['review_date']) > time()) {
        return ['status' => 'compliant', 'class' => 'bg-success', 'text' => 'Compliant'];
    } elseif ($sop['status'] === 'Under Review' || strtotime($sop['review_date']) <= time()) {
        return ['status' => 'review_required', 'class' => 'bg-warning text-dark', 'text' => 'Review Required'];
    } else {
        return ['status' => 'non_compliant', 'class' => 'bg-danger', 'text' => 'Non-Compliant'];
    }
}

/**
 * Get CORE system integration paths and endpoints
 */
function getCoreIntegration() {
    $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'], 2);
    
    $integration = [
        'core_system' => [
            'base_path' => CORE_BASE_PATH,
            'base_url' => $baseUrl,
            'api_path' => CORE_API_PATH,
            'modules_path' => CORE_MODULES_PATH,
            'includes_path' => CORE_INCLUDES_PATH
        ],
        'modules' => [
            'dashboard' => [
                'path' => CORE_MODULES_PATH . '/dashboard',
                'url' => $baseUrl . '/modules/dashboard/dashboard.php',
                'api' => $baseUrl . '/api/dashboard.php'
            ],
            'booking' => [
                'path' => CORE_BASE_PATH . '/booking.php',
                'url' => $baseUrl . '/booking.php',
                'api' => $baseUrl . '/api/bookings.php'
            ],
            'tracking' => [
                'path' => CORE_BASE_PATH . '/tracking.php',
                'url' => $baseUrl . '/tracking.php',
                'api' => $baseUrl . '/api/tracking.php'
            ],
            'tariffs' => [
                'path' => CORE_BASE_PATH . '/tariffs.php',
                'url' => $baseUrl . '/tariffs.php',
                'api' => $baseUrl . '/api/tariffs.php'
            ],
            'schedules' => [
                'path' => CORE_BASE_PATH . '/schedules.php',
                'url' => $baseUrl . '/schedules.php',
                'api' => $baseUrl . '/api/schedules.php'
            ],
            'service_network' => [
                'path' => CORE_BASE_PATH . '/service-network.php',
                'url' => $baseUrl . '/service-network.php',
                'api' => $baseUrl . '/api/service-network.php'
            ],
            'service_provider' => [
                'path' => CORE_BASE_PATH . '/service-provider.php',
                'url' => $baseUrl . '/service-provider.php',
                'api' => $baseUrl . '/api/service-provider.php'
            ]
        ],
        'sop_integration_points' => [
            'booking_workflow' => [
                'description' => 'SOPs integrated into shipment booking process',
                'endpoint' => $baseUrl . '/api/sops.php?action=templates&type=booking',
                'status' => 'active'
            ],
            'customs_workflow' => [
                'description' => 'SOPs for customs clearance procedures',
                'endpoint' => $baseUrl . '/api/sops.php?action=templates&type=customs',
                'status' => 'active'
            ],
            'tariff_application' => [
                'description' => 'Pricing SOPs linked to tariff system',
                'endpoint' => $baseUrl . '/tariffs.php?sop_integration=true',
                'status' => 'active'
            ],
            'tracking_updates' => [
                'description' => 'Status update SOPs for tracking system',
                'endpoint' => $baseUrl . '/tracking.php?sop_integration=true',
                'status' => 'pending'
            ]
        ],
        'api_endpoints' => [
            'sops' => $baseUrl . '/api/sops.php',
            'users' => $baseUrl . '/api/users.php',
            'bookings' => $baseUrl . '/api/bookings.php',
            'tracking' => $baseUrl . '/api/tracking.php',
            'tariffs' => $baseUrl . '/api/tariffs.php',
            'schedules' => $baseUrl . '/api/schedules.php'
        ]
    ];
    
    return send_json($integration);
}

/**
 * Get all module directory paths in the CORE system
 */
function getModulePaths() {
    $paths = [
        'system_paths' => [
            'root' => CORE_BASE_PATH,
            'api' => CORE_API_PATH,
            'modules' => CORE_MODULES_PATH,
            'includes' => CORE_INCLUDES_PATH,
            'assets' => CORE_BASE_PATH . '/assets',
            'uploads' => CORE_BASE_PATH . '/uploads'
        ],
        'core_files' => [
            'database' => CORE_BASE_PATH . '/db.php',
            'authentication' => CORE_BASE_PATH . '/auth.php',
            'configuration' => CORE_BASE_PATH . '/config.php',
            'index' => CORE_BASE_PATH . '/index.php'
        ],
        'main_modules' => [
            'sop_manager' => CORE_BASE_PATH . '/sop-manager.php',
            'booking_system' => CORE_BASE_PATH . '/booking.php',
            'tracking_system' => CORE_BASE_PATH . '/tracking.php',
            'tariff_management' => CORE_BASE_PATH . '/tariffs.php',
            'schedule_management' => CORE_BASE_PATH . '/schedules.php',
            'service_network' => CORE_BASE_PATH . '/service-network.php',
            'service_provider' => CORE_BASE_PATH . '/service-provider.php',
            'admin_panel' => CORE_BASE_PATH . '/admin.php'
        ],
        'dashboard_modules' => [
            'main_dashboard' => CORE_MODULES_PATH . '/dashboard/dashboard.php',
            'analytics' => CORE_MODULES_PATH . '/analytics',
            'reports' => CORE_MODULES_PATH . '/reports'
        ],
        'api_modules' => [
            'sops_api' => CORE_API_PATH . '/sops.php',
            'users_api' => CORE_API_PATH . '/users.php',
            'bookings_api' => CORE_API_PATH . '/bookings.php',
            'tracking_api' => CORE_API_PATH . '/tracking.php',
            'tariffs_api' => CORE_API_PATH . '/tariffs.php',
            'schedules_api' => CORE_API_PATH . '/schedules.php'
        ],
        'includes' => [
            'sidebar' => CORE_INCLUDES_PATH . '/sidebar.php',
            'header' => CORE_INCLUDES_PATH . '/header.php',
            'footer' => CORE_INCLUDES_PATH . '/footer.php',
            'dark_mode_styles' => CORE_INCLUDES_PATH . '/dark-mode-styles.php'
        ],
        'sop_workflow_integration' => [
            'booking_integration' => [
                'target_file' => CORE_BASE_PATH . '/booking.php',
                'sop_reference' => 'booking_workflow_sop',
                'integration_status' => 'active'
            ],
            'customs_integration' => [
                'target_file' => CORE_BASE_PATH . '/customs.php',
                'sop_reference' => 'customs_clearance_sop',
                'integration_status' => 'active'
            ],
            'warehouse_integration' => [
                'target_file' => CORE_BASE_PATH . '/warehouse.php',
                'sop_reference' => 'consolidation_sop',
                'integration_status' => 'pending'
            ],
            'billing_integration' => [
                'target_file' => CORE_BASE_PATH . '/billing.php',
                'sop_reference' => 'billing_process_sop',
                'integration_status' => 'pending'
            ]
        ]
    ];
    
    // Check if paths exist and add status
    foreach ($paths as $category => &$categoryPaths) {
        if (is_array($categoryPaths)) {
            foreach ($categoryPaths as $name => &$path) {
                if (is_string($path)) {
                    $path = [
                        'path' => $path,
                        'exists' => file_exists($path),
                        'readable' => is_readable($path ?? ''),
                        'writable' => is_writable($path ?? '')
                    ];
                }
            }
        }
    }
    
    return send_json($paths);
}

?>


