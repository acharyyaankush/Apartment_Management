<?php
// ajax/update_admin_profile.php
require_once '../functions/functions.php';
checkAuth();

$pdo = getPDOConnection();

// Get POST data
$admin_id = $_POST['admin_id'] ?? $_SESSION['admin_id'];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? null;
$phone = $_POST['phone'] ?? null;
$address = $_POST['address'] ?? null;
$role = $_POST['role'] ?? 'user';
$status = $_POST['status'] ?? 'active';
$current_role = $_POST['current_role'] ?? 'user';

// Validate required fields
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

// Check if role is changing from admin to user
$role_changed = ($current_role === 'admin' && $role === 'user');

// Prepare update query
$sql = "UPDATE admins SET 
        name = ?, 
        email = ?, 
        phone = ?, 
        address = ?, 
        role = ?, 
        status = ?,
        updated_at = NOW()
        WHERE id = ?";

$stmt = $pdo->prepare($sql);
$success = $stmt->execute([$name, $email, $phone, $address, $role, $status, $admin_id]);

if ($success) {
    // Update session variables
    $_SESSION['admin_name'] = $name;
    $_SESSION['admin_role'] = $role;
    
    $response = [
        'success' => true, 
        'message' => 'Profile updated successfully.',
        'role_changed' => $role_changed
    ];
    
    // If role changed from admin to user, destroy session
    if ($role_changed) {
        // Destroy the session immediately
        session_destroy();
    }
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
}

exit;
?>