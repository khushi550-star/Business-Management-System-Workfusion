<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logout</title>
</head>
<body>
<script>
  // Ask for confirmation before logout
  var confirmLogout = confirm("Are you sure you want to logout?");

  if (confirmLogout) {
    // If user clicks OK → destroy session and go to login
    <?php
      session_unset();
      session_destroy();
    ?>
    alert("You have been logged out successfully!");
    window.location.href = "login.php";
  } else {
    // If user clicks Cancel → go back to dashboard
    alert("Logout cancelled.");
    window.location.href = "employee.php"; // change to your dashboard file name
  }
</script>
</body>
</html>
