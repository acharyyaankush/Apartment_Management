<?php
require_once __DIR__ . '/../functions/functions.php';
checkAuth();

$success = '';
$error = '';

// Get current month-year
$current_month_year = date('m-Y');
$current_month = date('F Y');

// Handle Add Maintenance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_maintenance'])) {
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $due_amount = $_POST['due_amount'];
    $previous_due = $_POST['previous_due'];
    $payment_date = $_POST['payment_date'];
    
    // Generate title automatically
    $member = getMemberById($member_id);
    $title = $member ? 'Maintenance Payment - ' . $member['name'] . ' (Apt: ' . $member['apartment_no'] . ')' : 'Maintenance Payment';
    
    // Use current month-year
    $month_year = $current_month_year;
    
    if (addMaintenance($member_id, $title, $amount, $due_amount, $previous_due, $month_year, $payment_date)) {
        $success = "Maintenance payment recorded successfully!";
        header("Location: Maintenance.php");
        exit();
    } else {
        $error = "Failed to record maintenance payment!";
    }
}

// Handle Update Maintenance
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_maintenance'])) {
    $id = $_POST['maintenance_id'];
    $member_id = $_POST['member_id'];
    $amount = $_POST['amount'];
    $due_amount = $_POST['due_amount'];
    $previous_due = $_POST['previous_due'];
    $payment_date = $_POST['payment_date'];
    
    // Generate title automatically
    $member = getMemberById($member_id);
    $title = $member ? 'Maintenance Payment - ' . $member['name'] . ' (Apt: ' . $member['apartment_no'] . ')' : 'Maintenance Payment';
    
    // Use existing month_year from the record
    $existing_record = getMaintenanceById($id);
    $month_year = $existing_record ? $existing_record['month_year'] : $current_month_year;
    
    if (updateMaintenance($id, $member_id, $title, $amount, $due_amount, $previous_due, $month_year, $payment_date)) {
        $success = "Maintenance record updated successfully!";
       header("Location: Maintenance.php");
        exit();
    } else {
        $error = "Failed to update maintenance record!";
    }
}

// Handle Delete Maintenance
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteMaintenance($id)) {
        $success = "Maintenance record deleted successfully!";
        header("Location: maintenance.php");
        exit();
    } else {
        $error = "Failed to delete maintenance record!";
    }
}

$maintenance = getMaintenance();
$members = getMembers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style.css">

</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Maintenance Management</h3>
            <div class="month-display">Current Month: <?php echo $current_month; ?></div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Add Maintenance Button -->
        <button class="btn-add-member" onclick="openAddModal()">
            + Record Maintenance Payment
        </button>
        
        <!-- Maintenance List -->
        <h4>All Maintenance Records</h4>
        <?php if (empty($maintenance)): ?>
            <div class="alert alert-error">No maintenance records found.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Apartment</th>
                        <th>Month</th>
                        <th>Monthly Due</th>
                        <th>Previous Due</th>
                        <th>Paid Now</th>
                        <th>Remaining Due</th>
                        <th>Payment Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenance as $record): 
                        $month_year = $record['month_year'];
                        $month = substr($month_year, 0, 2);
                        $year = substr($month_year, 3);
                        $display_month = date('F Y', mktime(0, 0, 0, $month, 1, $year));
                    ?>
                    <tr>
                        <td><?php echo $record['id']; ?></td>
                        <td><?php echo $record['member_name'] ? htmlspecialchars($record['member_name']) : 'N/A'; ?></td>
                        <td><?php echo $record['apartment_no'] ? htmlspecialchars($record['apartment_no']) : 'N/A'; ?></td>
                        <td><?php echo $display_month; ?></td>
                        <td>$<?php echo number_format($record['due_amount'], 2); ?></td>
                        <td>$<?php echo number_format($record['previous_due'], 2); ?></td>
                        <td>$<?php echo number_format($record['amount'], 2); ?></td>
                        <td>
                            <span class="<?php echo $record['remaining_due'] > 0 ? 'due-positive' : 'due-zero'; ?>">
                                $<?php echo number_format($record['remaining_due'], 2); ?>
                            </span>
                        </td>
                        <td><?php echo $record['payment_date'] ? date('M j, Y', strtotime($record['payment_date'])) : 'N/A'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="openEditModal(<?php echo $record['id']; ?>)">Edit</button>
                                <a href="?delete=<?php echo $record['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this maintenance record?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add Maintenance Modal -->
<div id="addMaintenanceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record Maintenance Payment</h3>
            <button class="close-btn" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="POST" action="" id="addMaintenanceForm">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Member:</label>
                    <select name="member_id" id="add_member_id" required onchange="loadMemberDueInfo(this.value)">
                        <option value="">Select a member</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['name']) . ' - Apt: ' . htmlspecialchars($member['apartment_no']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Monthly Due Amount ($):</label>
                    <input type="number" name="due_amount" id="add_due_amount" step="0.01" min="0" required readonly>
                </div>
                <div class="form-group">
                    <label>Previous Due ($):</label>
                    <input type="number" name="previous_due" id="add_previous_due" step="0.01" min="0" required readonly>
                </div>
                <div class="form-group">
                    <label>Pay Now ($):</label>
                    <input type="number" name="amount" id="add_amount" step="0.01" min="0" required oninput="calculateRemainingDue()">
                </div>
                <div class="form-group">
                    <label>Remaining Due After Payment ($):</label>
                    <input type="number" name="remaining_due" id="add_remaining_due" step="0.01" min="0" required readonly>
                </div>
                <div class="form-group">
                    <label>Payment Date:</label>
                    <input type="date" name="payment_date" id="add_payment_date" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" name="add_maintenance" class="btn-primary">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Maintenance Modal -->
<div id="editMaintenanceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Maintenance Payment</h3>
            <button class="close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="" id="editMaintenanceForm">
            <input type="hidden" name="maintenance_id" id="edit_maintenance_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Select Member:</label>
                    <select name="member_id" id="edit_member_id" required onchange="loadMemberDueInfoEdit(this.value)">
                        <option value="">Select a member</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['id']; ?>">
                                <?php echo htmlspecialchars($member['name']) . ' - Apt: ' . htmlspecialchars($member['apartment_no']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Monthly Due Amount ($):</label>
                    <input type="number" name="due_amount" id="edit_due_amount" step="0.01" min="0" required readonly>
                </div>
                <div class="form-group">
                    <label>Previous Due ($):</label>
                    <input type="number" name="previous_due" id="edit_previous_due" step="0.01" min="0" required readonly>
                </div>
                <div class="form-group">
                    <label>Pay Now ($):</label>
                    <input type="number" name="amount" id="edit_amount" step="0.01" min="0" required oninput="calculateRemainingDueEdit()">
                </div>
                <div class="form-group">
                    <label>Remaining Due After Payment ($):</label>
                    <input type="number" name="remaining_due" id="edit_remaining_due" step="0.01" min="0" required readonly>
                </div>
                <div class="form-group">
                    <label>Payment Date:</label>
                    <input type="date" name="payment_date" id="edit_payment_date" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_maintenance" class="btn-primary">Update Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('addMaintenanceModal').style.display = 'block';
    // Set today's date as default
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    document.getElementById('add_payment_date').value = formattedDate;
    // Clear all fields
    document.getElementById('add_member_id').selectedIndex = 0;
    document.getElementById('add_due_amount').value = '';
    document.getElementById('add_previous_due').value = '';
    document.getElementById('add_amount').value = '';
    document.getElementById('add_remaining_due').value = '';
}

function closeAddModal() {
    document.getElementById('addMaintenanceModal').style.display = 'none';
    document.getElementById('addMaintenanceForm').reset();
}

function openEditModal(maintenanceId) {
    loadMaintenanceData(maintenanceId);
}

function closeEditModal() {
    document.getElementById('editMaintenanceModal').style.display = 'none';
    if (window.history.replaceState && window.location.search.includes('edit=')) {
        const newUrl = window.location.pathname + (window.location.search ? window.location.search.replace(/edit=\d+/, '') : '');
        window.history.replaceState({}, document.title, newUrl);
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addMaintenanceModal');
    const editModal = document.getElementById('editMaintenanceModal');
    
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

// Load member due info for add form
function loadMemberDueInfo(memberId) {
    if (!memberId) return;
    
    // Use current month-year
    const monthYear = '<?php echo $current_month_year; ?>';
    
    fetch('../ajax/get_member_due.php?member_id=' + memberId + '&month_year=' + monthYear)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const previousDue = data.previous_due || 0;
                const monthlyFee = data.monthly_fee || 0;
                
                // Set monthly due and previous due
                document.getElementById('add_due_amount').value = parseFloat(monthlyFee).toFixed(2);
                document.getElementById('add_previous_due').value = parseFloat(previousDue).toFixed(2);
                
                // Auto-calculate remaining due
                calculateRemainingDue();
            } else {
                console.error('Error loading member data:', data.message);
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading member due info:', error);
            alert('Error loading member information. Please check console for details.');
        });
}

// Load member due info for edit form
function loadMemberDueInfoEdit(memberId) {
    if (!memberId) return;
    
    // Use current month-year
    const monthYear = '<?php echo $current_month_year; ?>';
    
    fetch('../ajax/get_member_due.php?member_id=' + memberId + '&month_year=' + monthYear)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const previousDue = data.previous_due || 0;
                const monthlyFee = data.monthly_fee || 0;
                
                // Set monthly due and previous due
                document.getElementById('edit_due_amount').value = parseFloat(monthlyFee).toFixed(2);
                document.getElementById('edit_previous_due').value = parseFloat(previousDue).toFixed(2);
                
                // Auto-calculate remaining due
                calculateRemainingDueEdit();
            } else {
                console.error('Error loading member data:', data.message);
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading member due info:', error);
            alert('Error loading member information. Please check console for details.');
        });
}

// Calculate remaining due for add form
function calculateRemainingDue() {
    const monthlyDue = parseFloat(document.getElementById('add_due_amount').value) || 0;
    const previousDue = parseFloat(document.getElementById('add_previous_due').value) || 0;
    const payNow = parseFloat(document.getElementById('add_amount').value) || 0;
    const totalDue = monthlyDue + previousDue;
    const remainingDue = totalDue - payNow;
    
    // Set remaining due (minimum 0)
    const finalRemaining = remainingDue > 0 ? remainingDue : 0;
    document.getElementById('add_remaining_due').value = finalRemaining.toFixed(2);
}

// Calculate remaining due for edit form
function calculateRemainingDueEdit() {
    const monthlyDue = parseFloat(document.getElementById('edit_due_amount').value) || 0;
    const previousDue = parseFloat(document.getElementById('edit_previous_due').value) || 0;
    const payNow = parseFloat(document.getElementById('edit_amount').value) || 0;
    const totalDue = monthlyDue + previousDue;
    const remainingDue = totalDue - payNow;
    
    // Set remaining due (minimum 0)
    const finalRemaining = remainingDue > 0 ? remainingDue : 0;
    document.getElementById('edit_remaining_due').value = finalRemaining.toFixed(2);
}

// Load maintenance data for editing
function loadMaintenanceData(maintenanceId) {
    console.log('Loading maintenance data for ID:', maintenanceId);
    
    const url = '../ajax/get_maintenance.php?id=' + maintenanceId;
    console.log('Fetch URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                if (data.success) {
                    const record = data.maintenance;
                    console.log('Maintenance record:', record);
                    
                    // Populate form fields
                    document.getElementById('edit_maintenance_id').value = record.id;
                    document.getElementById('edit_member_id').value = record.member_id || '';
                    document.getElementById('edit_due_amount').value = record.due_amount || 0;
                    document.getElementById('edit_previous_due').value = record.previous_due || 0;
                    document.getElementById('edit_amount').value = record.amount || 0;
                    document.getElementById('edit_remaining_due').value = record.remaining_due || 0;
                    document.getElementById('edit_payment_date').value = record.payment_date || '';
                    
                    // Show the modal
                    document.getElementById('editMaintenanceModal').style.display = 'block';
                    
                    // Update URL without page reload
                    if (window.history.replaceState) {
                        const newUrl = window.location.pathname + '?edit=' + maintenanceId;
                        window.history.replaceState({}, document.title, newUrl);
                    }
                } else {
                    console.error('API error:', data.message);
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text was:', text);
                alert('Error parsing server response. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading maintenance data: ' + error.message);
        });
}

// Auto-calculate when pay amount changes
document.getElementById('add_amount').addEventListener('input', calculateRemainingDue);
document.getElementById('edit_amount').addEventListener('input', calculateRemainingDueEdit);

// Auto-close modals after successful form submission
<?php if ($success): ?>
document.addEventListener('DOMContentLoaded', function() {
    closeAddModal();
    closeEditModal();
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>