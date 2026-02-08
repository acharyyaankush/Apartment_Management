<?php
require_once '../functions/Functions.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_fixed_expense'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    
    if (addFixedExpense($title, $amount, $description)) {
        $success = "Fixed expense added successfully!";
    } else {
        $error = "Failed to add fixed expense!";
    }
}

$fixed_expenses = getFixedExpenses();
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../style.css">
<body>
<?php include '../Navbar.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Fixed Expenses Management</h3>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Add Fixed Expense Form -->
        <div class="form-container">
            <h4>Add New Fixed Expense</h4>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Description:</label>
                        <textarea name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_fixed_expense" class="btn btn-primary">Add Fixed Expense</button>
            </form>
        </div>
        
        <!-- Fixed Expenses List -->
        <h4 style="margin-top: 2rem;">All Fixed Expenses</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fixed_expenses as $expense): ?>
                <tr>
                    <td><?php echo $expense['id']; ?></td>
                    <td><?php echo htmlspecialchars($expense['title']); ?></td>
                    <td>$<?php echo number_format($expense['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($expense['description']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($expense['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
