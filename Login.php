<?php
include ('functions/Functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (login($email, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password!";
    }
}

if (isLoggedIn()) {
    header("Location: Dashboard.php");
    exit();
}
?>

<?php include 'header.php'; ?>

<div class="login-container">
    <form class="login-form" method="POST" action="">
        <h2>Admin Login</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>

