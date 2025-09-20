<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/external-api-config.php';
require_once '../auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $api = getExternalCoreAPI();
    
    // Try to get dashboard stats from external API first
    $response = $api->getDashboardStats();
    
    if ($response['success']) {
        // Use external API data if available
        echo json_encode($response['data']);
    } else {
        // Fallback: calculate stats from individual API calls
        $stats = [
            'totalRoutes' => 0,
            'servicePoints' => 0, 
            'coverageArea' => 0,
            'efficiencyScore' => 0,
            'status' => 'success'
        ];

        // Get routes data
        $routesResponse = $api->getRoutes();
        if ($routesResponse['success'] && isset($routesResponse['data'])) {
            $routes = $routesResponse['data'];
            $activeRoutes = array_filter($routes, function($route) {
                return isset($route['status']) && $route['status'] === 'Active';
            });
            $stats['totalRoutes'] = count($activeRoutes);
            
            // Calculate coverage area
            $totalDistance = 0;
            foreach ($activeRoutes as $route) {
                if (isset($route['distance'])) {
                    $totalDistance += (float)$route['distance'];
                }
            }
            $stats['coverageArea'] = round($totalDistance * 2, 1); // Estimate: distance * 2km corridor
            
            // Calculate efficiency score
            $totalRoutes = count($routes);
            $activeCount = count($activeRoutes);
            $operationalRatio = $totalRoutes > 0 ? ($activeCount / $totalRoutes) * 100 : 0;
            
            // Simple efficiency calculation based on operational ratio
            $stats['efficiencyScore'] = round($operationalRatio * 0.8, 1); // 80% weight on operational status
        }

        // Get service points data
        $pointsResponse = $api->getServicePoints();
        if ($pointsResponse['success'] && isset($pointsResponse['data'])) {
            $points = $pointsResponse['data'];
            $activePoints = array_filter($points, function($point) {
                return isset($point['status']) && $point['status'] === 'Active';
            });
            $stats['servicePoints'] = count($activePoints);
        }

        echo json_encode($stats);
    }

} catch (Exception $e) {
    error_log('Dashboard Stats API Error: ' . $e->getMessage());
    
    // Return default stats on error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to fetch dashboard statistics from external system',
        'totalRoutes' => 0,
        'servicePoints' => 0,
        'coverageArea' => 0,
        'efficiencyScore' => 0
    ]);
}
?>
