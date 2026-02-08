<?php
require_once __DIR__ . '/../functions/functions.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['id'])) {
    $maintenanceId = $_GET['id'];
    
    // Get maintenance record
    $maintenance = getMaintenanceById($maintenanceId);
    
    if ($maintenance) {
        echo json_encode([
            'success' => true,
            'maintenance' => $maintenance
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Maintenance record not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No maintenance ID provided'
    ]);
}
?>