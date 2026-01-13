<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bms_db";

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===== SIGNUP =====
if (isset($_POST['reg_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $mpin = mysqli_real_escape_string($conn, $_POST['mpin']);

    // Hash password
    $hashed_password = password_hash($mpin, PASSWORD_DEFAULT);

    // Check if user exists
    $check = "SELECT * FROM users WHERE mobile_email='$mobile'";
    $result = $conn->query($check);

    if ($result->num_rows > 0) {
        echo "<script>alert('User already exists!');</script>";
    } else {
        $sql = "INSERT INTO users (full_name, mobile_email, password) VALUES ('$name','$mobile','$hashed_password')";
         if ($conn->query($insert)) {
            echo "<script>alert('Signup successful! Redirecting to login...'); window.location='login.php';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// ===== LOGIN =====
if (isset($_POST['login_user'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE mobile_email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['emp_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
              $_SESSION['email'] = $user['mobile_email'];
                $_SESSION['photo'] = $user['photo'];
            header("Location: employee.php");
            exit();
       } else {
            echo "<script>alert('Invalid Password!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}
?>
