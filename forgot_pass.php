<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once('Connect.php');

if (isset($_POST['reset'])) {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE mobile_email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp = rand(100000,999999); // 6-digit OTP
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;
        $_SESSION['otp_expire'] = time() + 600; // 10 minutes

        // PHPMailer setup
      /*   $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'singhk43148@gmail.com'; // change to your Gmail
            $mail->Password   = 'jqlo essi pwtd vzwy';    // Gmail App password
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = 465;

            $mail->setFrom('singhk43148@gmail.com', 'WorkFusion');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body    = "Your OTP is <b>$otp</b>. Valid for 10 minutes.";

            $mail->send();
            echo "OTP sent to your email!";
            header("Location: verify_otp.php");
            exit();
        } catch (Exception $e) {
            echo "Mailer Error: ".$mail->ErrorInfo;
        }
    } else {
        echo "No account found with this email!";
    } */
   $mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'singhk43148@gmail.com'; // your Gmail
    $mail->Password   = 'baum olsu htmt qrvl';    // App password generated in Gmail
    $mail->SMTPSecure = 'ssl';                  // or 'tls'
    $mail->Port       = 465;                    // 465 for SSL, 587 for TLS

    $mail->setFrom('singhk43148@gmail.com', 'WorkFusion');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your OTP for Password Reset';
    $mail->Body    = "Your OTP is <b>$otp</b>. Valid for 10 minutes.";

    $mail->send();
    echo "OTP sent to your email!";
    header("Location: verify_otp.php");
    exit();
} catch (Exception $e) {
    echo "Mailer Error: ".$mail->ErrorInfo;
}
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
      background: #0056b3;
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
    <h2>Forgot Password</h2>
    <p>Enter your registered Email</p>
    <form action="" method="POST">
      <input type="email" name="email" placeholder="Enter your Email" required>
     
      <button type="submit" name="reset">Send OTP</button>
</form>
 <div class="back-link">
      <a href="login.php">Back to Login</a>
    </div>
  </div>
</body>
</html>