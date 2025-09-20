<?php
require_once '../config/external-api-config.php';
require_once '../auth.php';

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
parse_str($_SERVER['QUERY_STRING'], $params);
$id = $params['id'] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                $response = $api->getServicePoint($id);
            } else {
                $response = $api->getServicePoints();
            }
            $result = handleExternalAPIResponse($response, 'Failed to fetch service points');
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            $response = $api->createServicePoint($data);
            $result = handleExternalAPIResponse($response, 'Failed to create service point');
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Service Point ID is required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                exit;
            }
            $response = $api->updateServicePoint($id, $data);
            $result = handleExternalAPIResponse($response, 'Failed to update service point');
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Service Point ID is required']);
                exit;
            }
            $response = $api->deleteServicePoint($id);
            $result = handleExternalAPIResponse($response, 'Failed to delete service point');
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
    error_log('Service Points API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error. Please try again later.']);
}


