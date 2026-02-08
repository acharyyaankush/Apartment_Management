<?php
require_once __DIR__ . '/../functions/functions.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $memberId = $_GET['id'];
    $member = getMemberById($memberId);
    
    if ($member) {
        echo json_encode([
            'success' => true,
            'member' => $member
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