<?php
include("Connect.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE mobile_email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_email'] = $admin['mobile_email'];
            header("Location: admin.php");
            exit;
        } else {
            echo "<script>alert('Invalid Password!');</script>";
        }
    } else {
        echo "<script>alert('Admin not found!');</script>";
    }
}
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
            <h2>Admin Login</h2>
            <p>Welcome back to your account!</p>

            <form action="admin_login.php" method="POST">
                <?php include('error.php'); ?>

                <label for="userId">Mobile / Email Id  </label>
                <input type="text" name="email" placeholder="Enter mobile/email" required />

                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Enter password" required />
                <a href="forgot_pass.php" class="forgot-link">Forgot password?</a>


                <button type="submit" class="login-btn"  name="login_user">Login</button>

                <p class="register">New on WorkFusion? <a href="admin_signup.php">Admin</a><span>/</span><a href="signup.php">Employee</a></p>
            </form>
            </form>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
