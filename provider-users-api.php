<?php
// Provider Users API shim that proxies to External Core REST API
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/external-api-config.php';

header('Content-Type: application/json');

// Require login
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$api = getExternalCoreAPI();

function read_json() {
  $raw = file_get_contents('php://input');
  if ($raw === false || $raw === '') return [];
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}

try {
  switch ($action) {
    case 'list':
      // GET providers list
      $resp = $api->getProviders();
      if (!$resp['success']) {
        http_response_code($resp['status_code']);
        echo json_encode(['error' => 'Failed to fetch providers']);
        exit;
      }
      echo json_encode(['providers' => $resp['data']]);
      break;

    case 'stats':
      // Get provider stats if available; else compute simple stats from list
      $resp = $api->getProviderStats();
      if ($resp['success'] && is_array($resp['data'])) {
        echo json_encode($resp['data']);
        break;
      }
      // Fallback to compute from list
      $list = $api->getProviders();
      if (!$list['success'] || !is_array($list['data'])) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch stats']);
        break;
      }
      $providers = $list['data'];
      $total = count($providers);
      $active = 0;
      $areas = [];
      foreach ($providers as $p) {
        if (!empty($p['is_active'])) $active++;
        if (!empty($p['service_area'])) $areas[$p['service_area']] = true;
      }
      echo json_encode([
        'total_providers' => $total,
        'active_providers' => $active,
        'service_areas' => count($areas)
      ]);
      break;

    case 'get':
      if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        break;
      }
      $resp = $api->getProvider($id);
      if (!$resp['success']) {
        http_response_code($resp['status_code']);
        echo json_encode(['error' => 'Provider not found']);
        break;
      }
      echo json_encode(['provider' => $resp['data']]);
      break;

    case 'toggle-status':
      if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
      }
      if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        break;
      }
      $body = read_json();
      $isActive = isset($body['is_active']) ? (bool)$body['is_active'] : null;
      if ($isActive === null) {
        http_response_code(400);
        echo json_encode(['error' => 'is_active is required']);
        break;
      }
      $resp = $api->setProviderStatus($id, $isActive);
      if (!$resp['success']) {
        http_response_code($resp['status_code']);
        echo json_encode(['error' => 'Failed to update status']);
        break;
      }
      echo json_encode(['success' => true]);
      break;

    case 'update':
      if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
      }
      if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        break;
      }
      $body = read_json();
      $resp = $api->updateProvider($id, $body);
      if (!$resp['success']) {
        http_response_code($resp['status_code']);
        echo json_encode(['error' => 'Failed to update provider']);
        break;
      }
      echo json_encode(['success' => true, 'provider' => $resp['data']]);
      break;

    case 'delete':
      if ($method !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
      }
      if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        break;
      }
      $resp = $api->deleteProvider($id);
      if (!$resp['success']) {
        http_response_code($resp['status_code']);
        echo json_encode(['error' => 'Failed to delete provider']);
        break;
      }
      echo json_encode(['success' => true]);
      break;

    default:
      http_response_code(400);
      echo json_encode(['error' => 'Unknown action']);
  }
} catch (Exception $e) {
  error_log('Provider shim error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'Server error']);
}
