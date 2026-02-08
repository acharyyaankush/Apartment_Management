<?php
require_once __DIR__ . '/../functions/functions.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['member_id'])) {
    $memberId = $_GET['member_id'];
    $monthYear = $_GET['month_year'] ?? date('m-Y');
    
    // Get member info
    $member = getMemberById($memberId);
    
    if ($member) {
        // Get monthly fee from member's maintenance_fee
        $monthlyFee = $member['maintenance_fee'] ?? 0;
        
        // Get previous due for this member
        $previousDue = getPreviousDue($memberId, $monthYear);
        
        // Get current month maintenance summary
        $currentSummary = getCurrentMonthMaintenance($memberId, $monthYear);
        
        echo json_encode([
            'success' => true,
            'member' => [
                'id' => $member['id'],
                'name' => $member['name'],
                'email' => $member['email'],
                'phone' => $member['phone'],
                'apartment_no' => $member['apartment_no'],
                'maintenance_fee' => $monthlyFee
            ],
            'monthly_fee' => $monthlyFee,
            'previous_due' => $previousDue,
            'current_summary' => $currentSummary
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Member not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No member ID provided'
    ]);
}
?>