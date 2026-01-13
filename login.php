<?php
include('server.php');
include("Connect.php"); // your DB connection file

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login - BMS</title>
<link rel="stylesheet" href="style.css" />
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
        <div class="login-box">
            <h2>Login</h2>
            <p>Welcome back to your account!</p>

            <form action="login.php" method="POST">

                <label for="userId">Mobile / Email Id  </label>
                <input type="text" name="email" placeholder="Enter mobile/email" required />

                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Enter password" required />
                <a href="forgot_pass.php" class="forgot-link">Forgot password?</a>


                <button type="submit" class="login-btn"  name="login_user">Login</button>

                <p class="register">New on WorkFusion? <a href="admin_signup.php">Admin</a><span>/</span><a href="signup.php">Employee</a></p>
            </form>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
