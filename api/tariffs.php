<?php
require_once __DIR__ . '/../config/external-api-config.php';
require_once __DIR__ . '/../auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check admin privileges for write operations
$isAdmin = ($_SESSION['role'] === 'admin');
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE']) && !$isAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin privileges required']);
    exit;
}

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$api = getExternalCoreAPI();

// Get ID from query parameters
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            if ($id && $id > 0) {
                $response = $api->getTariff($id);
            } else {
                $response = $api->getTariffs();
                
                // Filter tariffs based on user role if needed
                if (!$isAdmin && $response['success'] && isset($response['data'])) {
                    // Filter out user submissions for non-admin users
                    $filteredData = array_filter($response['data'], function($tariff) {
                        return !isset($tariff['source']) || $tariff['source'] === 'admin' || $tariff['source'] === null;
                    });
                    $response['data'] = array_values($filteredData);
                }
            }
            $result = handleExternalAPIResponse($response, 'Failed to fetch tariffs');
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            // Validate required fields
            $errors = validate_tariff($body);
            if ($errors) {
                http_response_code(422);
                echo json_encode(['errors' => $errors]);
                exit;
            }
            
            // Add source field for admin-created tariffs
            $body['source'] = 'admin';
            
            $response = $api->createTariff($body);
            $result = handleExternalAPIResponse($response, 'Failed to create tariff');
            break;

        case 'PUT':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Tariff ID is required']);
                exit;
            }
            
            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            // Validate required fields
            $errors = validate_tariff($body);
            if ($errors) {
                http_response_code(422);
                echo json_encode(['errors' => $errors]);
                exit;
            }
            
            $response = $api->updateTariff($id, $body);
            $result = handleExternalAPIResponse($response, 'Failed to update tariff');
            break;

        case 'DELETE':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Tariff ID is required']);
                exit;
            }
            
            $response = $api->deleteTariff($id);
            $result = handleExternalAPIResponse($response, 'Failed to delete tariff');
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }

    if ($result['success']) {
        http_response_code($result['status_code']);
        echo json_encode($result['data']);
    } else {
        http_response_code($result['status_code']);
        echo json_encode(['error' => $result['error']]);
    }

} catch (Exception $e) {
    error_log('Tariffs API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error. Please try again later.']);
}

/**
 * Validate tariff data
 */
function validate_tariff(array $body): array {
    $errors = [];
    $requiredStrings = ['name','category','status','effectiveDate','expiryDate'];
    
    foreach ($requiredStrings as $field) {
        if (!isset($body[$field]) || trim((string)$body[$field]) === '') {
            $errors[$field] = 'Required';
        }
    }
    
    $requiredNumbers = ['baseRate','perKmRate','perHourRate','priorityMultiplier'];
    foreach ($requiredNumbers as $field) {
        if (!isset($body[$field]) || !is_numeric($body[$field])) {
            $errors[$field] = 'Numeric value required';
        }
    }
    
    return $errors;
}

?>


