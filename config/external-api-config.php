<?php
/**
 * External Core System API Configuration
 * This file contains configuration for connecting to external core systems via REST API
 */

// External Core System API Configuration
define('EXTERNAL_CORE_BASE_URL', 'https://api.external-core.com/v1'); // Update with actual external core API URL
define('EXTERNAL_CORE_API_KEY', 'your-api-key-here'); // Update with actual API key
define('EXTERNAL_CORE_TIMEOUT', 30); // Request timeout in seconds

// API Endpoints for different modules
define('EXTERNAL_ROUTES_ENDPOINT', EXTERNAL_CORE_BASE_URL . '/routes');
define('EXTERNAL_SCHEDULES_ENDPOINT', EXTERNAL_CORE_BASE_URL . '/schedules');
define('EXTERNAL_TARIFFS_ENDPOINT', EXTERNAL_CORE_BASE_URL . '/tariffs');
define('EXTERNAL_SERVICE_POINTS_ENDPOINT', EXTERNAL_CORE_BASE_URL . '/service-points');

/**
 * External API Client Class
 * Handles all communication with external core systems
 */
class ExternalCoreAPI {
    private $baseUrl;
    private $apiKey;
    private $timeout;
    
    public function __construct() {
        $this->baseUrl = EXTERNAL_CORE_BASE_URL;
        $this->apiKey = EXTERNAL_CORE_API_KEY;
        $this->timeout = EXTERNAL_CORE_TIMEOUT;
    }
    
    /**
     * Make HTTP request to external API
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => false, // Set to true in production with proper SSL
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'status_code' => $httpCode,
            'data' => $decodedResponse,
            'success' => $httpCode >= 200 && $httpCode < 300
        ];
    }
    
    // Routes API methods
    public function getRoutes() {
        return $this->makeRequest('/routes');
    }
    
    public function getRoute($id) {
        return $this->makeRequest('/routes/' . $id);
    }
    
    public function createRoute($data) {
        return $this->makeRequest('/routes', 'POST', $data);
    }
    
    public function updateRoute($id, $data) {
        return $this->makeRequest('/routes/' . $id, 'PUT', $data);
    }
    
    public function deleteRoute($id) {
        return $this->makeRequest('/routes/' . $id, 'DELETE');
    }
    
    // Schedules API methods
    public function getSchedules() {
        return $this->makeRequest('/schedules');
    }
    
    public function getSchedule($id) {
        return $this->makeRequest('/schedules/' . $id);
    }
    
    public function createSchedule($data) {
        return $this->makeRequest('/schedules', 'POST', $data);
    }
    
    public function updateSchedule($id, $data) {
        return $this->makeRequest('/schedules/' . $id, 'PUT', $data);
    }
    
    public function deleteSchedule($id) {
        return $this->makeRequest('/schedules/' . $id, 'DELETE');
    }
    
    // Tariffs API methods
    public function getTariffs() {
        return $this->makeRequest('/tariffs');
    }
    
    public function getTariff($id) {
        return $this->makeRequest('/tariffs/' . $id);
    }
    
    public function createTariff($data) {
        return $this->makeRequest('/tariffs', 'POST', $data);
    }
    
    public function updateTariff($id, $data) {
        return $this->makeRequest('/tariffs/' . $id, 'PUT', $data);
    }
    
    public function deleteTariff($id) {
        return $this->makeRequest('/tariffs/' . $id, 'DELETE');
    }
    
    // Service Points API methods
    public function getServicePoints() {
        return $this->makeRequest('/service-points');
    }
    
    public function getServicePoint($id) {
        return $this->makeRequest('/service-points/' . $id);
    }
    
    public function createServicePoint($data) {
        return $this->makeRequest('/service-points', 'POST', $data);
    }
    
    public function updateServicePoint($id, $data) {
        return $this->makeRequest('/service-points/' . $id, 'PUT', $data);
    }
    
    public function deleteServicePoint($id) {
        return $this->makeRequest('/service-points/' . $id, 'DELETE');
    }

    // Providers API methods
    public function getProviders() {
        return $this->makeRequest('/providers');
    }

    public function getProvider($id) {
        return $this->makeRequest('/providers/' . $id);
    }

    public function updateProvider($id, $data) {
        // Use PUT for full update; adjust to PATCH if external API expects partial
        return $this->makeRequest('/providers/' . $id, 'PUT', $data);
    }

    public function setProviderStatus($id, $isActive) {
        // Common pattern: PATCH /providers/{id} with { is_active: bool }
        return $this->makeRequest('/providers/' . $id, 'PATCH', [ 'is_active' => (bool)$isActive ]);
    }

    public function deleteProvider($id) {
        return $this->makeRequest('/providers/' . $id, 'DELETE');
    }

    public function getProviderStats() {
        return $this->makeRequest('/providers/stats');
    }

    // Shipments API methods
    public function getShipments() { return $this->makeRequest('/shipments'); }
    public function getShipment($id) { return $this->makeRequest('/shipments/' . $id); }
    public function createShipment($data) { return $this->makeRequest('/shipments', 'POST', $data); }
    public function updateShipment($id, $data) { return $this->makeRequest('/shipments/' . $id, 'PUT', $data); }
    public function deleteShipment($id) { return $this->makeRequest('/shipments/' . $id, 'DELETE'); }

    // Consolidations API methods
    public function getConsolidations() { return $this->makeRequest('/consolidations'); }
    public function getConsolidation($id) { return $this->makeRequest('/consolidations/' . $id); }
    public function createConsolidation($data) { return $this->makeRequest('/consolidations', 'POST', $data); }
    public function updateConsolidation($id, $data) { return $this->makeRequest('/consolidations/' . $id, 'PUT', $data); }
    public function deleteConsolidation($id) { return $this->makeRequest('/consolidations/' . $id, 'DELETE'); }

    // BLs (Bills of Lading) API methods
    public function getBLs() { return $this->makeRequest('/bls'); }
    public function getBL($id) { return $this->makeRequest('/bls/' . $id); }
    public function createBL($data) { return $this->makeRequest('/bls', 'POST', $data); }
    public function updateBL($id, $data) { return $this->makeRequest('/bls/' . $id, 'PUT', $data); }
    public function deleteBL($id) { return $this->makeRequest('/bls/' . $id, 'DELETE'); }

    // Dashboard stats from external system
    public function getDashboardStats() {
        return $this->makeRequest('/dashboard/stats');
    }
    
    // Real-time tracking data
    public function getRealTimeData($type = 'all') {
        return $this->makeRequest('/real-time/' . $type);
    }

    // Health check
    public function health() {
        return $this->makeRequest('/health', 'GET');
    }
}

/**
 * Helper function to get ExternalCoreAPI instance
 */
function getExternalCoreAPI() {
    static $instance = null;
    if ($instance === null) {
        $instance = new ExternalCoreAPI();
    }
    return $instance;
}

/**
 * Helper function to handle API responses consistently
 */
function handleExternalAPIResponse($response, $defaultErrorMessage = 'External API error') {
    if (!$response['success']) {
        $errorMessage = $defaultErrorMessage;
        if (isset($response['data']['message'])) {
            $errorMessage = $response['data']['message'];
        } elseif (isset($response['data']['error'])) {
            $errorMessage = $response['data']['error'];
        }
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'status_code' => $response['status_code']
        ];
    }
    
    return [
        'success' => true,
        'data' => $response['data'],
        'status_code' => $response['status_code']
    ];
}

/**
 * Check if external API is available
 */
function isExternalAPIAvailable() {
    try {
        $api = getExternalCoreAPI();
        $response = $api->health();
        return $response['success'];
    } catch (Exception $e) {
        error_log('External API availability check failed: ' . $e->getMessage());
        return false;
    }
}

?>
