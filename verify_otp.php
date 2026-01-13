<?php
session_start();

if (isset($_POST['otp'])) {
    if ($_POST['otp'] == $_SESSION['otp'] && time() < $_SESSION['otp_expire']) {
        header("Location: update_pass.php");
        exit();
    } else {
        echo "Invalid or expired OTP!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update Password - BMS</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    body {
      background: #062b53; /* light grey background */
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: #fff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 350px;
    }
    h2 {
      margin-bottom: 8px;
      font-size: 24px;
      color: #062b53;
    }
    p {
      margin-bottom: 20px;
      color: #666;
      font-size: 14px;
    }
    input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    button {
      width: 100%;
      padding: 12px;
      margin-top: 15px;
      border: none;
      border-radius: 6px;
      background: #007bff;
      color: white;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s ease;
    }
    button:hover {
       background: #07284bff;
      transform: translateY(-2px);
    }
    .back-link {
      margin-top: 15px;
      text-align: center;
      font-size: 14px;
    }
    .back-link a {
      color: #007bff;
      text-decoration: none;
    }
    .back-link a:hover {
      color: #062b53;
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Enter OTP</h2>
    <p>Enter your OTP below</p>
    <form action="" method="POST">
      <input type="Text" name="otp" placeholder="Enter OTP" required>
     
      <button type="submit">Verify OTP</button>
</form>
 <div class="back-link">
      <a href="login.php">Back to Login</a>
    </div>
  </div>
</body>
</html>

