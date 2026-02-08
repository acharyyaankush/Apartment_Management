<?php
session_start();

// Fix database connection path
require_once __DIR__ . '/../config/database.php';

// Add this to functions.php if not already there
function getPDOConnection() {
    try {
        $host = 'localhost';
        $dbname = 'apartment_management';
        $username = 'root';
        $password = 'Ankush12345@#';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Login function WITHOUT hashing
function login($email, $password) {
    global $pdo;
    
    if (!$pdo) {
        die("Database connection failed");
    }
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = 'admin';
        return true;
    }
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Redirect if not logged in
function checkAuth() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

// Add member function
function addMember($name, $email, $phone, $apartment_no, $image = null, $maintenance_fee = 0) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO members (name, email, phone, apartment_no, image, maintenance_fee) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $phone, $apartment_no, $image, $maintenance_fee]);
}

// Get all members
function getMembers() {
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    $stmt = $pdo->query("SELECT * FROM members ORDER BY id ASC");
    return $stmt->fetchAll();
}

// Get member by ID
function getMemberById($id) {
    global $pdo;
    
    if (!$pdo) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Update the updateMember function
function updateMember($id, $name, $email, $phone, $apartment_no, $image = null, $maintenance_fee = 0) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    if ($image) {
        $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, phone = ?, apartment_no = ?, image = ?, maintenance_fee = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $phone, $apartment_no, $image, $maintenance_fee, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, phone = ?, apartment_no = ?, maintenance_fee = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $phone, $apartment_no, $maintenance_fee, $id]);
    }
}

// Delete member
function deleteMember($id) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    // Get member image to delete file
    $member = getMemberById($id);
    if ($member && $member['image']) {
        $imagePath = __DIR__ . '/../uploads/' . $member['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    return $stmt->execute([$id]);
}

// Image upload function
function uploadImage($file) {
    $uploadDir = __DIR__ . '/../uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return null;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return null;
}

// Add expense
function addExpense($title, $amount, $description, $date) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO expenses (title, amount, description, expense_date) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$title, $amount, $description, $date]);
}

// Get all expenses
function getExpenses() {
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    // Changed to ORDER BY id DESC to show newest first
    $stmt = $pdo->query("SELECT * FROM expenses ORDER BY id ASC");
    return $stmt->fetchAll();
}


// Add maintenance payment with due calculation
function addMaintenance($member_id, $title, $amount, $due_amount, $previous_due, $month_year, $payment_date = null) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    // Calculate remaining due (cannot be negative)
    $total_due = $due_amount + $previous_due;
    $remaining_due = $total_due - $amount;
    if ($remaining_due < 0) {
        $remaining_due = 0;
    }
    
    $stmt = $pdo->prepare("INSERT INTO maintenance (member_id, title, amount, due_amount, previous_due, remaining_due, month_year, payment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$member_id, $title, $amount, $due_amount, $previous_due, $remaining_due, $month_year, $payment_date]);
}

// Update maintenance record
function updateMaintenance($id, $member_id, $title, $amount, $due_amount, $previous_due, $month_year, $payment_date = null) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    // Calculate remaining due (cannot be negative)
    $total_due = $due_amount + $previous_due;
    $remaining_due = $total_due - $amount;
    if ($remaining_due < 0) {
        $remaining_due = 0;
    }
    
    $stmt = $pdo->prepare("UPDATE maintenance SET member_id = ?, title = ?, amount = ?, due_amount = ?, previous_due = ?, remaining_due = ?, month_year = ?, payment_date = ? WHERE id = ?");
    return $stmt->execute([$member_id, $title, $amount, $due_amount, $previous_due, $remaining_due, $month_year, $payment_date, $id]);
}

// Get all maintenance records with member names
function getMaintenance() {
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    $stmt = $pdo->query("
        SELECT m.*, mem.name as member_name, mem.apartment_no, mem.maintenance_fee 
        FROM maintenance m 
        LEFT JOIN members mem ON m.member_id = mem.id 
        ORDER BY m.month_year ASC, m.created_at ASC
    ");
    return $stmt->fetchAll();
}

// Get maintenance record by ID
function getMaintenanceById($id) {
    global $pdo;
    
    if (!$pdo) {
        return null;
    }
    
    $stmt = $pdo->prepare("
        SELECT m.*, mem.name as member_name, mem.apartment_no, mem.maintenance_fee 
        FROM maintenance m 
        LEFT JOIN members mem ON m.member_id = mem.id 
        WHERE m.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Delete maintenance record
function deleteMaintenance($id) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    $stmt = $pdo->prepare("DELETE FROM maintenance WHERE id = ?");
    return $stmt->execute([$id]);
}

// Get member's previous due amount (unpaid balance from previous months)
function getPreviousDue($member_id, $current_month_year) {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    // Get the last maintenance record for this member before current month
    $stmt = $pdo->prepare("
        SELECT remaining_due 
        FROM maintenance 
        WHERE member_id = ? 
        AND month_year != ?
        ORDER BY created_at ASC 
        LIMIT 1
    ");
    $stmt->execute([$member_id, $current_month_year]);
    $result = $stmt->fetch();
    
    return $result ? $result['remaining_due'] : 0;
}

// Get member's monthly maintenance fee from members table
function getMemberMonthlyFee($member_id) {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    $stmt = $pdo->prepare("SELECT maintenance_fee FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $result = $stmt->fetch();
    
    return $result ? $result['maintenance_fee'] : 0;
}

// Get current month maintenance summary for a member
function getCurrentMonthMaintenance($member_id, $month_year) {
    global $pdo;
    
    if (!$pdo) {
        return null;
    }
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(amount) as total_paid, 
            MAX(due_amount) as monthly_due,
            MAX(previous_due) as previous_due,
            MAX(remaining_due) as current_remaining
        FROM maintenance 
        WHERE member_id = ? 
        AND month_year = ?
    ");
    $stmt->execute([$member_id, $month_year]);
    return $stmt->fetch();
}

// Get member due information (for AJAX call)
function getMemberDueInfo($member_id, $month_year) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Get member info
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    
    if (!$member) {
        return ['success' => false, 'message' => 'Member not found'];
    }
    
    // Get monthly fee directly from member record
    $monthly_fee = $member['maintenance_fee'] ?? 0;
    
    // Get previous due
    $previous_due = getPreviousDue($member_id, $month_year);
    
    // Get current month summary
    $current_summary = getCurrentMonthMaintenance($member_id, $month_year);
    
    return [
        'success' => true,
        'member' => [
            'id' => $member['id'],
            'name' => $member['name'],
            'email' => $member['email'],
            'phone' => $member['phone'],
            'apartment_no' => $member['apartment_no'],
            'maintenance_fee' => $monthly_fee
        ],
        'monthly_fee' => $monthly_fee,
        'previous_due' => $previous_due,
        'current_summary' => $current_summary
    ];
}


// Add fixed expense
function addFixedExpense($title, $amount, $description) {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO fixed_expenses (title, amount, description) VALUES (?, ?, ?)");
    return $stmt->execute([$title, $amount, $description]);
}

// Get all fixed expenses
function getFixedExpenses() {
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    $stmt = $pdo->query("SELECT * FROM fixed_expenses ORDER BY id ASC");
    return $stmt->fetchAll();
}
//for Dashboard
function getTotalMaintenanceCollection() {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(amount), 0) as total FROM maintenance
    ");
    $result = $stmt->fetch();
    
    return $result ? $result['total'] : 0;
}
// Calculate total funds (Maintenance Collection - Expenses)
function getTotalFunds() {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    // Get total maintenance collection
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM maintenance");
    $maintenance_result = $stmt->fetch();
    $total_maintenance = $maintenance_result ? $maintenance_result['total'] : 0;
    
    // Get total expenses
    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses");
    $expenses_result = $stmt->fetch();
    $total_expenses = $expenses_result ? $expenses_result['total'] : 0;
    
    // Calculate total funds: Maintenance - Expenses
    $total_funds = $total_maintenance - $total_expenses;
    
    return $total_funds;
}

function getMonthlySummary($limit = 6, $year = null) {
    $pdo = getPDOConnection();
    
    if ($year === null) {
        $year = date('Y');
    }
    
    // Get recent months (even if no data)
    $months = [];
    for ($i = 0; $i < $limit; $i++) {
        $date = date('Y-m', strtotime("-$i months"));
        $month = date('m', strtotime($date));
        $year_num = date('Y', strtotime($date));
        
        if ($year_num == $year) {
            $months[] = $month;
        }
    }
    
    $result = [];
    foreach ($months as $month) {
        $month_year = $month . '-' . $year;
        
        // Get expenses
        $sql_exp = "SELECT COALESCE(SUM(amount), 0) as total 
                   FROM expenses 
                   WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?";
        $stmt = $pdo->prepare($sql_exp);
        $stmt->execute([$month, $year]);
        $expenses = (float)$stmt->fetchColumn();
        
        // Get maintenance - ADJUST COLUMN NAMES IF NEEDED
        $sql_maint = "SELECT COALESCE(SUM(amount), 0) as total 
                     FROM maintenance 
                     WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?";
        $stmt = $pdo->prepare($sql_maint);
        $stmt->execute([$month, $year]);
        $maintenance = (float)$stmt->fetchColumn();
        
        $result[] = [
            'month_year' => $month_year,
            'total_expenses' => $expenses,
            'total_maintenance' => $maintenance,
            'month_funds' => $maintenance - $expenses
        ];
    }
    
    return $result;
}

// Get current month expenses total
function getCurrentMonthExpenses() {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    $current_month = date('m');
    $current_year = date('Y');
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?
    ");
    $stmt->execute([$current_month, $current_year]);
    $result = $stmt->fetch();
    
    return $result ? $result['total'] : 0;
}

// Get current month maintenance collection
function getCurrentMonthMaintenanceCollection() {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    $current_month = date('m-Y');
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM maintenance 
        WHERE month_year = ?
    ");
    $stmt->execute([$current_month]);
    $result = $stmt->fetch();
    
    return $result ? $result['total'] : 0;
}

// Get current month funds (Maintenance - Expenses for current month)
function getCurrentMonthFunds() {
    global $pdo;
    
    if (!$pdo) {
        return 0;
    }
    
    $current_month = date('m');
    $current_year = date('Y');
    $current_month_str = date('m-Y');
    
    // Get current month expenses
    $exp_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM expenses 
        WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?
    ");
    $exp_stmt->execute([$current_month, $current_year]);
    $expenses_result = $exp_stmt->fetch();
    $current_month_expenses = $expenses_result ? $expenses_result['total'] : 0;
    
    // Get current month maintenance
    $maint_stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM maintenance 
        WHERE month_year = ?
    ");
    $maint_stmt->execute([$current_month_str]);
    $maintenance_result = $maint_stmt->fetch();
    $current_month_maintenance = $maintenance_result ? $maintenance_result['total'] : 0;
    
    // Calculate current month funds
    $current_month_funds = $current_month_maintenance - $current_month_expenses;
    
    return $current_month_funds;
}

// Function to get admin details
function getAdminDetails($admin_id) {
    $pdo = getPDOConnection();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            // Set default values for missing fields
            $defaults = [
                'email' => '',
                'phone' => '',
                'address' => '',
                'status' => 'active',
                'role' => 'user', // Default role
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'last_login' => null
            ];
            
            foreach ($defaults as $key => $value) {
                if (!isset($admin[$key]) || $admin[$key] === null) {
                    $admin[$key] = $value;
                }
            }
        }
        
        return $admin;
    } catch (PDOException $e) {
        error_log("getAdminDetails error: " . $e->getMessage());
        return null;
    }
}
?>