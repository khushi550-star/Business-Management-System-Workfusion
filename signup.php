<?php
include('server.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sign Up - BMS</title>
<link rel="stylesheet" href="signup.css" />
</head>
<body>
<div class="container">
    <div class="left-section">
        <div class="logo">
            <img src="logo.png" alt="Logo" class="logo-img" />
            <p>Empowering Business Management</p>
        </div>
    </div>
    <div class="right-section">
        <div class="signup-box">
            <h2>Sign Up</h2>
            <p>Create a new account</p>

            <form action="signup.php" method="POST">
                <?php include('error.php'); ?>

                <label for="name">Full Name</label>
                <input type="text" name="name" placeholder="Enter your name" required />

                <label for="mobile">Mobile/Email</label>
                <input type="text" name="mobile" placeholder="Enter your mobile/email" required />

                <label for="mpin">Create password</label>
                <input type="password" name="mpin" placeholder="Create a 4-digit password" required />

                <button type="submit" class="signup-btn" name="reg_user">Register</button>

                <p class="login-link">Already have an account? <a href="admin_login.php">Admin</a><span>/</span><a href="login.php">Employee</a></p>
            </form>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
