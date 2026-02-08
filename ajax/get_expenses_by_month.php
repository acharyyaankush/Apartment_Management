<?php
require_once __DIR__ . '/../functions/functions.php';
header('Content-Type: application/json');

checkAuth();

$month = $_GET['month'] ?? date('Y-m');
$year = date('Y', strtotime($month . '-01'));
$month_num = date('m', strtotime($month . '-01'));

try {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM expenses 
        WHERE YEAR(expense_date) = ? AND MONTH(expense_date) = ?
        ORDER BY expense_date ASC
    ");
    $stmt->execute([$year, $month_num]);
    $expenses = $stmt->fetchAll();
    
    // Calculate total
    $total = 0;
    foreach ($expenses as $expense) {
        $total += $expense['amount'];
    }
    
    echo json_encode([
        'success' => true,
        'month' => $month,
        'expenses' => $expenses,
        'total' => $total,
        'count' => count($expenses)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>