<?php 
session_start();
include "Connect.php";


// Fetch published notices
$res = $conn->query("SELECT * FROM notices WHERE is_published=1 ORDER BY created_at DESC");

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>News & Notices</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html,body { height:100%; font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif; background:#f5f7fa; color:#12303f; }

    /* Header */
    header {
      background: white;
      color: white;
      padding: 15px 17px;
      position: relative;
      top: 0;
      z-index: 1000;
    }

    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: white;
    }

    .logo { width: 200px; height: auto; }

    .nav-links {
      list-style: none;
      display: flex;
      gap: 25px;
    }

    .nav-links a {
      color: #062a50;
      font-size: large;
      text-decoration: none;
      font-weight: 700;
      transition: color 0.3s;
      padding: 8px 12px;
      border-radius: 6px;
    }

    .nav-links a:hover {
      background: #062a50;
      color: white;
      border-radius: 20px;
    }

    /* Hamburger */
    .hamburger { display: none; flex-direction: column; cursor: pointer; }
    .hamburger span {
      height: 3px;
      width: 25px;
      background: #002147;
      margin: 4px 0;
      border-radius: 2px;
      transition: 0.3s;
    }

    @media (max-width: 768px) {
      .nav-links {
        display: none;
        flex-direction: column;
        background: #f9f9f9;
        position: absolute;
        top: 60px;
        right: 20px;
        width: 200px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 15px;
      }

      .nav-links.active { display: flex; }

      .nav-links a {
        padding: 10px;
        display: block;
        text-align: left;
        margin: 5px 0;
        color: #002147;
        font-weight: bold;
      }

      .hamburger { display: flex; }
    }

    /* Layout */
    .app { display: flex; height: calc(100vh - 64px); }

    .sidebar {
      width: 240px;
      background-color: #002147;
      padding: 22px 14px;
      box-shadow: inset -1px 0 0 rgba(243, 240, 240, 0.904);
      overflow: auto;
    }

    .main { flex: 1; padding: 22px; overflow: auto; }

    .user { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }

    .avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: #eceff3ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 20px;
    }

    .user-info { color: white; line-height: 1.4; }
    .user-info .name { font-weight: 700; }
    .user-info .emp-id { font-size: 12px; opacity: 0.85; }

    .menu a {
      display: block;
      padding: 10px;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }

    .menu a:hover,
    .menu a.active {
      color: black;
      background: #ccd8e6;
    }

    .profile-card { display: flex; gap: 18px; background: #eef7fb; border-radius: 6px; padding: 16px 18px; align-items: center; margin-bottom: 20px; }

    .wrap { max-width: 900px; margin: 0 auto; }
    .card {
      background: #eef6f9;
      border-radius: 16px;
      border: 1px solid #dde3ed;
      padding: 14px;
      margin-bottom: 14px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .title { font-weight: 700; font-size: 18px; }
    .meta { font-size: 13px; color: #666; margin-top: 6px; }
    .attachment { margin-top: 8px; }
    .small { font-size: 13px; color: #666; }

    footer {
      position: relative;
      background: #002147;
      color: white;
      text-align: center;
      padding: 5px;
    }

    .sidebar-separator {
      border: 0;
      height: 1px;
      background-color: rgba(255, 255, 255, 0.3);
      margin: 16px 0;
    }
    /* ===========================
   📱 MOBILE RESPONSIVE + SIDEBAR TOGGLE
   =========================== */
/* ====================== SIDEBAR TOGGLE (MOBILE) ====================== */
@media (max-width: 768px) {
  .app {
    position: relative;
  }

  /* Hide sidebar by default */
  .sidebar {
    position: fixed;
    top: 0;
    left: -250px;
    height: 100%;
    width: 240px;
    background-color: #002147;
    transition: left 0.3s ease;
    z-index: 1000;
  }

  /* When active (toggled) */
  .sidebar.active {
    left: 0;
  }

  /* Add overlay behind sidebar */
  .overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 999;
  }

  .overlay.active {
    display: block;
  }

  /* Main content shifts below header */
  .main {
    padding: 16px;
  }

  /* Center profile card and make it fit */
  .profile-card {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    margin: 0 auto 20px auto;
    width: 90%;
  }

  /* Responsive table scroll */
  .card table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}

  </style>
</head>

<body>
<header>
  <nav class="navbar">
    <img src="logo.png" alt="WorkFusion Logo" class="logo" />
    <div class="nav-links" id="navLinks">
      <a href="home1.html">Home</a>
      <a href="about.html">About</a>
      <a href="services.html">Services</a>
      <a href="login.php">Login</a>
      <a href="signup.php">Sign Up</a>
      <a href="contact.html">Contact</a>
    </div>
    <div class="hamburger" id="hamburger">
      <span></span><span></span><span></span>
    </div>
  </nav>
</header>

<div class="app">
  <aside class="sidebar">
    <div class="user">
      <div class="avatar">
        <?php echo isset($_SESSION['full_name']) ? strtoupper(substr($_SESSION['full_name'], 0, 1)) : '?'; ?>
      </div>
      <div class="user-info">
        <div class="name"><?php echo $_SESSION['full_name'] ?? 'Guest'; ?></div>
        <div class="emp-id">Employee ID: <?php echo $_SESSION['emp_id'] ?? 'N/A'; ?></div>
      </div>
    </div>

    <hr class="sidebar-separator">

    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
   <nav class="menu">
  <a href="employee.php" class="<?= $current_page=='employee.php' ? 'active' : '' ?>">Dashboard</a>
  <a href="employee_worktimetracking.php" class="<?= $current_page=='employee_worktimetracking.php' ? 'active' : '' ?>">Attendance & Work Time</a>
  <a href="employee_leave.php" class="<?= $current_page=='employee_leave.php' ? 'active' : '' ?>">Leave Request</a>
  <a href="employee_payroll.php" class="<?= $current_page=='employee_payroll.php' ? 'active' : '' ?>">Payslips</a>
  <a href="employee_tasks_projects.php" class="<?= $current_page=='employee_tasks_projects.php' ? 'active' : '' ?>">Tasks & Projects</a>
  <a href="emp_portal.php" class="<?= $current_page=='emp_portal.php' ? 'active' : '' ?>">Meetings</a>
  <a href="employee_news.php" class="<?= $current_page=='employee_news.php' ? 'active' : '' ?>">News & Notices</a>
  <a href="employee_profile.php" class="<?= $current_page=='employee_profile.php' ? 'active' : '' ?>">Employee Info</a>
  <a href="logout.php">Log-out</a>
</nav>
  </aside>
  <div class="overlay" id="overlay"></div>


  <main class="main">
    <div class="content">
      <div class="profile-card">
        <div class="photo">
          <?php
          $photo_file = $_SESSION['photo'] ?? ($user['photo'] ?? '');
          $photo_path = 'uploads/profile/' . $photo_file;
          if (!empty($photo_file) && file_exists($photo_path)) {
              echo '<img src="'.$photo_path.'" style="width:88px;height:88px;border-radius:50%;object-fit:cover;">';
          } else {
              echo '<img src="default-avatar.png" style="width:88px;height:88px;border-radius:50%;object-fit:cover;">';
          }
          ?>
        </div>
        <div class="info">
          <h2><?php echo $_SESSION['full_name'] ?? 'Guest'; ?> (<?php echo $_SESSION['emp_id']; ?>)</h2>
          <div style="display:flex;gap:32px;margin-top:8px">
            <div>
              <h4>Email</h4>
              <div><?php echo $_SESSION['email']; ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="wrap">
        <center><h1>News & Notices</h1></center><br>

        <?php while($row = $res->fetch_assoc()): 
          $created = new DateTime($row['created_at']);
          $is_new = (new DateTime())->diff($created)->days < 3;
        ?>
          <div class="card">
            <div class="title">
              <?= htmlspecialchars($row['title']) ?>
            </div>
<div class="meta">
  <?php
    $posted_by_text = '';

    // Handle admin
    if (isset($row['posted_by_role']) && strtolower($row['posted_by_role']) === 'admin') {
        $admin_name = htmlspecialchars($row['posted_by_fullname'] ?? 'Administrator');
        $posted_by_text = "Admin ({$admin_name})";
    }
    // Handle employee
    elseif (!empty($row['posted_by_fullname'])) {
        $posted_by_text = htmlspecialchars($row['posted_by_fullname']);
    }
    // Fallback
    else {
        $posted_by_text = htmlspecialchars($row['posted_by'] ?? 'Unknown');
    }
  ?>
  Posted by <?= $posted_by_text ?> — <?= htmlspecialchars($row['created_at']) ?>
</div>

            <div style="margin-top:10px"><?= nl2br(htmlspecialchars($row['description'])) ?></div>
            <?php if(!empty($row['attachment'])): ?>
              <div class="attachment">
                <a href="<?= htmlspecialchars($row['attachment']) ?>" target="_blank"><?= htmlspecialchars(basename($row['attachment'])) ?></a>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>

        <?php if ($res->num_rows == 0): ?>
          <p class="small">No notices published yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const hamburger = document.getElementById("hamburger");
  const sidebar = document.querySelector(".sidebar");
  const overlay = document.getElementById("overlay");

  // Toggle sidebar open/close
  hamburger.addEventListener("click", function() {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
  });

  // Close sidebar when clicking overlay
  overlay.addEventListener("click", function() {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
  });
});
</script>

<?php
// ===== SIMPLE NOTIFICATION ON NEW EVENTS (only once per session) =====

// Check if notice notification already shown this session
if (empty($_SESSION['notice_alert_shown'])) {
    $latest_notice = $conn->query("SELECT title, created_at FROM notices WHERE is_published=1 ORDER BY created_at DESC LIMIT 1");
    if ($latest_notice && $row = $latest_notice->fetch_assoc()) {
        $created = new DateTime($row['created_at']);
        $now = new DateTime();
        $diff_days = $now->diff($created)->days;

        // Show alert if new notice within 2 days
        if ($diff_days < 2) {
            $message = "📰 New Notice Added by Admin: " . addslashes($row['title']);
            echo "<script>alert('{$message}');</script>";

            // Set session flag so it only shows once
            $_SESSION['notice_alert_shown'] = true;
        }
    }
}
?>
<footer><p>2025 WorkFusion. All rights reserved.</p></footer>


</body>
</html>
