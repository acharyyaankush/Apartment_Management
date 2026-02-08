<?php require_once 'functions/functions.php'; ?>
<nav class="navbar">
    <div class="navbar-container">
        
        <div class="navbar-brand">Apartment Management</div>

        <!-- Hamburger Button -->
        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <ul class="navbar-nav" id="navMenu">
            <li><a href="/APARTMENTMANAGEMENT/Dashboard.php">Dashboard</a></li>
            <li><a href="/APARTMENTMANAGEMENT/pages/Members.php">Members</a></li>
            <li><a href="/APARTMENTMANAGEMENT/pages/Expenses.php">Expenses</a></li>
            <li><a href="/APARTMENTMANAGEMENT/pages/Maintanence.php">Maintenance</a></li>
            <li><a href="/APARTMENTMANAGEMENT/pages/Fixedexpense.php">Fixed Expenses</a></li>

            <!-- Move user info + logout inside hamburger menu -->
            <li class="mobile-user-info">
                <div class="user-name clickable" onclick="openAdminProfileModal()">
                    <?php echo $_SESSION['admin_name']; ?>
                </div>
                <div class="user-role"><?php echo $_SESSION['admin_role']; ?></div>
                <a href="/APARTMENTMANAGEMENT/logout.php" class="btn-logout">Logout</a>
            </li>
        </ul>


       <div class="user-info desktop-user-info">
            <div class="user-name clickable" onclick="openAdminProfileModal()">
                <?php echo $_SESSION['admin_name']; ?>
            </div>
            <div class="user-role"><?php echo $_SESSION['admin_role']; ?></div>
        </div>

        <a href="/APARTMENTMANAGEMENT/logout.php" class="btn-logout desktop-logout">Logout</a>

    </div>
</nav>

<!-- Admin Profile Modal -->
<div id="adminProfileModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Admin Profile</h2>
            <button class="close-modal" onclick="closeAdminProfileModal()">×</button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be loaded via AJAX -->
            <div class="loading">Loading profile...</div>
        </div>
    </div>
</div>

<script>
function openAdminProfileModal() {
    const modal = document.getElementById('adminProfileModal');
    const modalBody = document.getElementById('modalBody');
    
    // Show loading
    modalBody.innerHTML = '<div class="loading">Loading profile...</div>';
    modal.style.display = 'flex';
    
    // Load admin profile via AJAX
    fetch('ajax/get_admin_profile.php')
        .then(response => response.text())
        .then(html => {
            modalBody.innerHTML = html;
        })
        .catch(error => {
            modalBody.innerHTML = '<div class="error">Error loading profile. Please try again.</div>';
            console.error('Error:', error);
        });
}

function closeAdminProfileModal() {
    document.getElementById('adminProfileModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('adminProfileModal');
    if (event.target == modal) {
        closeAdminProfileModal();
    }
}

// Function to show messages in modal
// function showMessageInModal(type, message, roleChanged = false) {
//     const modalBody = document.getElementById('modalBody');
    
//     if (type === 'success') {
//         if (roleChanged) {
//             modalBody.innerHTML = `
//                 <div style="padding: 40px; text-align: center;">
//                     <div style="font-size: 48px; color: #27ae60; margin-bottom: 20px;">✓</div>
//                     <h3 style="color: #2c3e50; margin-bottom: 15px;">Profile Updated Successfully!</h3>
//                     <p style="color: #7f8c8d; margin-bottom: 30px;">Your role has been changed from Admin to User.</p>
//                     <p style="color: #e74c3c; font-weight: bold; margin-bottom: 30px;">
//                         You will be redirected to login page in 2 seconds...
//                     </p>
//                     <div class="loading-spinner" style="margin: 20px auto;"></div>
//                 </div>
//             `;
            
//             // Redirect to login page after 2 seconds
//             setTimeout(() => {
//                 window.location.href = 'Login.php';
//             }, 2000);
//         } else {
//             modalBody.innerHTML = `
//                 <div style="padding: 40px; text-align: center;">
//                     <div style="font-size: 48px; color: #27ae60; margin-bottom: 20px;">✓</div>
//                     <h3 style="color: #2c3e50; margin-bottom: 15px;">Profile Updated Successfully!</h3>
//                     <p style="color: #7f8c8d; margin-bottom: 30px;">Your profile has been updated.</p>
//                     <p style="color: #3498db; font-weight: bold; margin-bottom: 30px;">
//                         You will be redirected to dashboard in 2 seconds...
//                     </p>
//                     <div class="loading-spinner" style="margin: 20px auto;"></div>
//                 </div>
//             `;
            
//             // Refresh the page to update navbar and redirect to dashboard
//             setTimeout(() => {
//                 window.location.href = 'Dashboard.php';
//             }, 2000);
//         }
//     } else {
//         modalBody.innerHTML = `
//             <div style="padding: 40px; text-align: center;">
//                 <div style="font-size: 48px; color: #e74c3c; margin-bottom: 20px;">✗</div>
//                 <h3 style="color: #2c3e50; margin-bottom: 15px;">Update Failed</h3>
//                 <p style="color: #e74c3c; margin-bottom: 30px;">${message}</p>
//                 <button class="btn btn-update" onclick="openAdminProfileModal()" style="margin-top: 20px;">
//                     Try Again
//                 </button>
//             </div>
//         `;
//     }
// }
</script>