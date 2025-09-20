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
            $resp = $id ? $api->getConsolidation($id) : $api->getConsolidations();
            $result = handleExternalAPIResponse($resp, 'Failed to fetch consolidations');
            break;
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $resp = $api->createConsolidation($body);
            $result = handleExternalAPIResponse($resp, 'Failed to create consolidation');
            break;
        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'Consolidation ID is required']); exit; }
            $body = json_decode(file_get_contents('php://input'), true) ?: [];
            $resp = $api->updateConsolidation($id, $body);
            $result = handleExternalAPIResponse($resp, 'Failed to update consolidation');
            break;
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error' => 'Consolidation ID is required']); exit; }
            $resp = $api->deleteConsolidation($id);
            $result = handleExternalAPIResponse($resp, 'Failed to delete consolidation');
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }

    http_response_code($result['status_code']);
    echo json_encode($result['success'] ? $result['data'] : ['error' => $result['error']]);
} catch (Exception $e) {
    error_log('Consolidations proxy error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
