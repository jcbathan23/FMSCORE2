<?php
require_once __DIR__ . '/../config/external-api-config.php';
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

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
            $resp = $id ? $api->getBL($id) : $api->getBLs();
            $result = handleExternalAPIResponse($resp, 'Failed to fetch BLs');
            break;
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $resp = $api->createBL($body);
            $result = handleExternalAPIResponse($resp, 'Failed to create BL');
            break;
        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'BL ID is required']); exit; }
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $resp = $api->updateBL($id, $body);
            $result = handleExternalAPIResponse($resp, 'Failed to update BL');
            break;
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'BL ID is required']); exit; }
            $resp = $api->deleteBL($id);
            $result = handleExternalAPIResponse($resp, 'Failed to delete BL');
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }

    http_response_code($result['status_code']);
    echo json_encode($result['success'] ? $result['data'] : ['error' => $result['error']]);
} catch (Exception $e) {
    error_log('BLs proxy error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
