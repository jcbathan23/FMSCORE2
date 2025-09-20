<?php
require_once __DIR__ . '/../config/external-api-config.php';
require_once __DIR__ . '/../auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
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
                $response = $api->getSchedule($id);
            } else {
                $response = $api->getSchedules();
            }
            $result = handleExternalAPIResponse($response, 'Failed to fetch schedules');
            break;

        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            // Validate required fields
            $errors = validate_schedule($body);
            if ($errors) {
                http_response_code(422);
                echo json_encode(['errors' => $errors]);
                exit;
            }
            
            $response = $api->createSchedule($body);
            $result = handleExternalAPIResponse($response, 'Failed to create schedule');
            break;

        case 'PUT':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Schedule ID is required']);
                exit;
            }
            
            $body = json_decode(file_get_contents('php://input'), true);
            if (!$body) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            
            // Validate required fields
            $errors = validate_schedule($body);
            if ($errors) {
                http_response_code(422);
                echo json_encode(['errors' => $errors]);
                exit;
            }
            
            $response = $api->updateSchedule($id, $body);
            $result = handleExternalAPIResponse($response, 'Failed to update schedule');
            break;

        case 'DELETE':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Schedule ID is required']);
                exit;
            }
            
            $response = $api->deleteSchedule($id);
            $result = handleExternalAPIResponse($response, 'Failed to delete schedule');
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
    error_log('Schedules API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error. Please try again later.']);
}

/**
 * Validate schedule data
 */
function validate_schedule(array $body): array {
    $errors = [];
    $required = ['name','route','vehicleType','departure','arrival','frequency','status','startDate','endDate','capacity'];
    
    foreach ($required as $field) {
        if (!isset($body[$field]) || $body[$field] === '') {
            $errors[$field] = 'Required';
        }
    }
    
    if (isset($body['capacity']) && !is_numeric($body['capacity'])) {
        $errors['capacity'] = 'Numeric value required';
    }
    
    return $errors;
}

?>


