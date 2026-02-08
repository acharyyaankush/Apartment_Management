<?php
require_once __DIR__ . '/../functions/functions.php';
checkAuth();

$success = '';
$error = '';

// Handle Add Member
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_member'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $apartment_no = $_POST['apartment_no'];
    $maintenance_fee = $_POST['maintenance_fee'] ?? 0;
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
    }
    
    if (addMember($name, $email, $phone, $apartment_no, $image, $maintenance_fee)) {
        $success = "Member added successfully!";
        // Redirect to clear any previous edit parameters
        header("Location: members.php?success=added");
        exit();
    } else {
        $error = "Failed to add member!";
    }
}

// Handle Edit Member
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_member'])) {
    $id = $_POST['member_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $apartment_no = $_POST['apartment_no'];
    $maintenance_fee = $_POST['maintenance_fee'] ?? 0;
    
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = uploadImage($_FILES['image']);
        // Delete old image if new one is uploaded
        $old_member = getMemberById($id);
        if ($old_member && $old_member['image'] && $image) {
            $oldImagePath = __DIR__ . '/../uploads/' . $old_member['image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }
    
    if (updateMember($id, $name, $email, $phone, $apartment_no, $image, $maintenance_fee)) {
        $success = "Member updated successfully!";
        // Redirect to clear edit parameters
        header("Location: members.php?success=updated");
        exit();
    } else {
        $error = "Failed to update member!";
    }
}

// Handle Delete Member
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteMember($id)) {
        $success = "Member deleted successfully!";
        header("Location: members.php?success=deleted");
        exit();
    } else {
        $error = "Failed to delete member!";
    }
}

// Handle success messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success = "Member added successfully!";
            break;
        case 'updated':
            $success = "Member updated successfully!";
            break;
        case 'deleted':
            $success = "Member deleted successfully!";
            break;
    }
}

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
            <h3>Members Management</h3>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Add Member Button -->
        <button class="btn-add-member" onclick="openAddModal()">
            + Add New Member
        </button>
        
        <!-- Members List -->
        <h4>All Members</h4>
        <?php if (empty($members)): ?>
            <div class="alert alert-error">No members found.</div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Apartment No</th>
                        <th>Maintenance Fee</th>
                        <th>Join Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo $member['id']; ?></td>
                        <td>
                            <?php if ($member['image']): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($member['image']); ?>" alt="Profile Image" class="current-image">
                            <?php else: ?>
                                <div class="no-image-placeholder">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['phone']); ?></td>
                        <td><?php echo htmlspecialchars($member['apartment_no']); ?></td>
                        <td>$<?php echo number_format($member['maintenance_fee'] ?? 0, 2); ?></td>
                        <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-edit" onclick="openEditModal(<?php echo $member['id']; ?>)">Edit</button>
                                <a href="?delete=<?php echo $member['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Add Member Modal -->
<div id="addMemberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Member</h3>
            <button class="close-btn" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="addMemberForm">
            <div class="form-grid">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Apartment No:</label>
                    <input type="text" name="apartment_no" required>
                </div>
                <div class="form-group">
                    <label>Maintenance Fee ($):</label>
                    <input type="number" name="maintenance_fee" step="0.01" min="0" value="0" required>
                </div>
                <div class="form-group full-width">
                    <label>Profile Image:</label>
                    <input type="file" name="image" accept="image/*">
                    <small class="form-text">Allowed types: JPG, JPEG, PNG, GIF. Max size: 2MB</small>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" name="add_member" class="btn-primary">Add Member</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Member Modal -->
<div id="editMemberModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Member</h3>
            <button class="close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data" id="editMemberForm">
            <input type="hidden" name="member_id" id="edit_member_id">
            <div class="form-grid">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone" id="edit_phone" required>
                </div>
                <div class="form-group">
                    <label>Apartment No:</label>
                    <input type="text" name="apartment_no" id="edit_apartment_no" required>
                </div>
                <div class="form-group">
                    <label>Maintenance Fee ($):</label>
                    <input type="number" name="maintenance_fee" id="edit_maintenance_fee" step="0.01" min="0" required>
                </div>
                <div class="form-group full-width">
                    <label>Profile Image:</label>
                    <input type="file" name="image" accept="image/*">
                    <div class="image-preview" id="currentImagePreview"></div>
                    <small class="form-text">Allowed types: JPG, JPEG, PNG, GIF. Max size: 2MB</small>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" name="update_member" class="btn-primary">Update Member</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('addMemberModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('addMemberModal').style.display = 'none';
    // Clear the form when closing
    document.getElementById('addMemberForm').reset();
}

function openEditModal(memberId) {
    loadMemberData(memberId);
}

function closeEditModal() {
    document.getElementById('editMemberModal').style.display = 'none';
    // Clear URL parameters without page reload
    if (window.history.replaceState && window.location.search.includes('edit=')) {
        const newUrl = window.location.pathname + (window.location.search ? window.location.search.replace(/edit=\d+/, '') : '');
        window.history.replaceState({}, document.title, newUrl);
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addMemberModal');
    const editModal = document.getElementById('editMemberModal');
    
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

function loadMemberData(memberId) {
    fetch('../ajax/get_member.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_member_id').value = data.member.id;
                document.getElementById('edit_name').value = data.member.name;
                document.getElementById('edit_email').value = data.member.email;
                document.getElementById('edit_phone').value = data.member.phone;
                document.getElementById('edit_apartment_no').value = data.member.apartment_no;
                document.getElementById('edit_maintenance_fee').value = data.member.maintenance_fee || 0;
                
                // Show current image if exists
                const imagePreview = document.getElementById('currentImagePreview');
                if (data.member.image) {
                    imagePreview.innerHTML = `
                        <small>Current Image:</small><br>
                        <img src="../uploads/${data.member.image}" alt="Current Image" class="current-image">
                    `;
                } else {
                    imagePreview.innerHTML = '<small>No current image</small>';
                }
                
                document.getElementById('editMemberModal').style.display = 'block';
                
                // Update URL without page reload
                if (window.history.replaceState) {
                    const newUrl = window.location.pathname + '?edit=' + memberId;
                    window.history.replaceState({}, document.title, newUrl);
                }
            } else {
                alert('Error loading member data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading member data:', error);
            alert('Error loading member data');
        });
}

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