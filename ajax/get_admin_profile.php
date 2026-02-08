<?php
// ajax/get_admin_profile.php
require_once '../functions/functions.php';
checkAuth();

// Get current admin details
$admin_id = $_SESSION['admin_id'];
$admin = getAdminDetails($admin_id);

if (!$admin) {
    echo '<div class="error">Admin not found</div>';
    exit;
}

// Set default values
$admin['email'] = $admin['email'] ?? '';
$admin['phone'] = $admin['phone'] ?? '';
$admin['address'] = $admin['address'] ?? '';
$admin['status'] = $admin['status'] ?? 'active';
$admin['role'] = $admin['role'] ?? 'user';
$admin['created_at'] = $admin['created_at'] ?? date('Y-m-d H:i:s');
$admin['updated_at'] = $admin['updated_at'] ?? date('Y-m-d H:i:s');
$admin['last_login'] = $admin['last_login'] ?? null;
?>

<form class="profile-form" id="adminProfileForm">
    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
    <input type="hidden" name="current_role" value="<?php echo $admin['role']; ?>">
    
    <div id="errorMessage" class="error-message"></div>
    <div id="successMessage" class="success-message"></div>
    
    <div class="form-group">
        <label for="name">Full Name *</label>
        <input type="text" id="name" name="name" class="form-control" 
               value="<?php echo htmlspecialchars($admin['name']); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control" 
               value="<?php echo htmlspecialchars($admin['email']); ?>">
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-control" 
                   value="<?php echo htmlspecialchars($admin['phone']); ?>">
        </div>
        
        <div class="form-group">
            <label for="status">Account Status</label>
            <div class="select-wrapper">
                <select id="status" name="status" class="form-control">
                    <option value="active" <?php echo $admin['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $admin['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($admin['address']); ?></textarea>
    </div>
    
    <?php if ($_SESSION['admin_role'] === 'admin'): ?>
    <div class="role-section">
        <h3>Role Management</h3>
        
        <div class="form-group">
            <label for="role">Admin Role *</label>
            <div class="select-wrapper">
                <select id="role" name="role" class="form-control" >
                    <option value="admin" <?php echo $admin['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                    <option value="user" <?php echo $admin['role'] === 'user' ? 'selected' : ''; ?>>Regular User</option>
                </select>
            </div>
            <div class="info-text">
                Changing role from Admin to User will log you out immediately
            </div>
        </div>
        
    </div>
    <?php else: ?>
    <input type="hidden" name="role" value="<?php echo $admin['role']; ?>">
    <?php endif; ?>
    
    <div class="button-group">
        <button type="button" class="btn btn-cancel" onclick="closeAdminProfileModal()">Cancel</button>
        <button class="btn btn-update">Update Profile</button>
    </div>
    
    <!-- <div class="timestamp">
        <div><strong>Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($admin['created_at'])); ?></div>
        <div><strong>Last Updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($admin['updated_at'])); ?></div>
        <?php if (!empty($admin['last_login'])): ?>
        <div><strong>Last Login:</strong> <?php echo date('F j, Y, g:i a', strtotime($admin['last_login'])); ?></div>
        <?php endif; ?>
    </div> -->
</form>

<script>
// Form submission handler

document.getElementById('adminProfileForm').addEventListener('submit', function (e) {
    e.preventDefault();
    update(this);
});

function update(){
    
    console.log(adminProfileForm,108)
    /*
    const formData = new FormData(this);
    const currentRole = formData.get('current_role');
    const newRole = formData.get('role');
    const roleChanging = (currentRole === 'admin' && newRole === 'user');
    
    // Show confirmation if demoting admin to user
    if (roleChanging) {
        if (!confirm('⚠️ WARNING: Changing role from Admin to User will:\n\n1. Log you out immediately\n2. Redirect to login page\n3. You will lose all admin privileges\n\nAre you sure you want to continue?')) {
            return;
        }
    }
    
    // Show loading
    const submitBtn = this.querySelector('.btn-update');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = 'Updating...';
    submitBtn.disabled = true;
    
    // Submit form via AJAX
    fetch('update_admin_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    
    .then(data => {
        console.log(135,data)
        if (data.success) {
            if (data.role_changed) {
                // Show success message
                alert('Profile updated successfully! Your role has been changed from Admin to User.');
                // Redirect to login page
                //window.location.href = './Login.php';
            } else {
                // Show success message
                alert('Profile updated successfully!');
                // Redirect to dashboard
                //window.location.href = './Dashboard.php';
            }
        } else {
            showMessage('error', data.message);
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        showMessage('error', 'An error occurred. Please try again.');
        console.error('Error:', error);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
    */
}

// function showRoleWarning() {
//     const currentRole = document.querySelector('input[name="current_role"]').value;
//     const newRole = document.getElementById('role').value;
//     const warning = document.getElementById('roleWarning');
    
//     if (currentRole === 'admin' && newRole === 'user') {
//         warning.classList.add('show');
//     } else {
//         warning.classList.remove('show');
//     }
// }

function showMessage(type, message) {
    const errorDiv = document.getElementById('errorMessage');
    const successDiv = document.getElementById('successMessage');
    
    if (type === 'error') {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
    } else {
        successDiv.textContent = message;
        successDiv.style.display = 'block';
        errorDiv.style.display = 'none';
    }
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        errorDiv.style.display = 'none';
        successDiv.style.display = 'none';
    }, 5000);
}

// Initialize role warning if needed
<?php if ($_SESSION['admin_role'] === 'admin'): ?>
// showRoleWarning();
<?php endif; ?>
</script>