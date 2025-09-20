<?php
require_once __DIR__ . '/../config/external-api-config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

// Require login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$api = getExternalCoreAPI();

try {
    switch ($method) {
        case 'GET':
            $resp = $id ? $api->getShipment($id) : $api->getShipments();
            $result = handleExternalAPIResponse($resp, 'Failed to fetch shipments');
            break;
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $resp = $api->createShipment($body);
            $result = handleExternalAPIResponse($resp, 'Failed to create shipment');
            break;
        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'Shipment ID is required']); exit; }
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $resp = $api->updateShipment($id, $body);
            $result = handleExternalAPIResponse($resp, 'Failed to update shipment');
            break;
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'Shipment ID is required']); exit; }
            $resp = $api->deleteShipment($id);
            $result = handleExternalAPIResponse($resp, 'Failed to delete shipment');
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }

    http_response_code($result['status_code']);
    echo json_encode($result['success'] ? $result['data'] : ['error' => $result['error']]);
} catch (Exception $e) {
    error_log('Shipments proxy error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
