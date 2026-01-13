<?php
session_start();
include "Connect.php";
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit;
}

$emp_id = $_SESSION['emp_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$emp_id")->fetch_assoc();

$meetings = $conn->query("
    SELECT m.*, 
           (SELECT COUNT(*) FROM meeting_attendance ma WHERE ma.meeting_id=m.id AND ma.user_id=$emp_id) AS attended
    FROM meetings m
    INNER JOIN meeting_invites mi ON mi.meeting_id = m.id
    WHERE mi.emp_id=$emp_id
    ORDER BY m.date DESC
");

if (isset($_POST['mark_attendance'])) {
    $meeting_id = $_POST['meeting_id'];
    $check = $conn->query("SELECT * FROM meeting_attendance WHERE meeting_id=$meeting_id AND user_id=$emp_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO meeting_attendance (meeting_id, user_id, marked_at) VALUES ($meeting_id, $emp_id, NOW())");
        echo "<script>alert('✅ Attendance marked successfully!');window.location='emp_portal.php';</script>";
        exit;
    } else {
        echo "<script>alert('⚠️ Attendance already marked.');window.location='emp_portal.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Meeting Portal</title>

<style>
 * { box-sizing: border-box; margin: 0; padding: 0; }
 html,body{height:100%;font-family:Inter,'Segoe UI',Roboto,Arial,sans-serif;background:#f5f7fa;color:#12303f}

header {background:white;color:white;padding:15px 17px;position:relative;top:0;z-index:1000;}
.navbar {display:flex;align-items:center;justify-content:space-between;background:white;position:relative;}
.logo {width:200px;height:auto;}
.nav-links {list-style:none;display:flex;gap:25px;}
.nav-links a {color:#062a50;font-size:large;text-decoration:none;font-weight:700;transition:color .3s;padding:8px 12px;border-radius:6px;}
.nav-links a:hover {background:#062a50;color:white;border-radius:20px;}
.hamburger {display:none;flex-direction:column;cursor:pointer;}
.hamburger span {height:3px;width:25px;background:#002147;margin:4px 0;border-radius:2px;transition:0.3s;}
@media (max-width:768px){
  .nav-links{display:none;flex-direction:column;background:#f9f9f9;position:absolute;top:60px;right:20px;width:200px;box-shadow:0 4px 8px rgba(0,0,0,0.1);border-radius:8px;padding:15px;z-index:1000;}
  .nav-links.active{display:flex;}
  .nav-links a{padding:10px;display:block;text-align:left;margin:5px 0;color:#002147;font-weight:bold;text-decoration:none;}
  .hamburger{display:flex;}
}

.app{display:flex;height:calc(100vh - 64px);}
.sidebar{width:240px;background-color:#002147;padding:22px 14px;box-shadow:inset -1px 0 0 rgba(243,240,240,0.904);overflow:auto;}
.main{flex:1;padding:22px;overflow:auto;}

.user{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
.avatar{width:50px;height:50px;border-radius:50%;background:#eceff3ff;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:20px;}
.user-info{color:white;line-height:1.4;}
.user-info .name{font-weight:700;}
.user-info .emp-id{font-size:12px;opacity:0.85;}
.menu a{display:block;padding:10px;color:white;text-decoration:none;border-radius:4px;}
.menu a:hover,.menu a.active{color:black;background:#ccd8e6;}
.sidebar-separator{border:0;height:1px;background-color:rgba(255,255,255,0.3);margin:16px 0;}

.profile-card{display:flex;gap:18px;background:#eef7fb;border-radius:6px;padding:16px 18px;align-items:center;margin-bottom:20px;}

/* ========== MAIN CONTAINER ========== */
.container {
  max-width: 1100px;
  margin: 60px auto;
  padding: 0 20px;
}

/* ========== CARDS ========== */
.card {
  background: rgba(255,255,255,0.95);
  border-radius: 18px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.08);
  padding: 20px;
  margin-bottom: 25px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}
.card h3 {
  color: #003b82;
  margin-bottom: 10px;
}
.card p {
  color: #444;
  margin: 6px 0;
}

/* ========== STATUS BADGE ========== */
.badge {
  background: linear-gradient(90deg, #00b06b, #00d47a);
  color: white;
  padding: 5px 10px;
  font-size: 13px;
  border-radius: 20px;
  font-weight: 600;
}
.badge.pending {
  background: linear-gradient(90deg, #e74c3c, #f76754);
}

/* ========== BUTTONS ========== */
button, .btn {
  border: none;
  border-radius: 25px;
  padding: 10px 18px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}
.btn-success {
  background: linear-gradient(90deg, #00b06b, #00d47a);
  color: white;
}
.btn-success:hover {
  transform: translateY(-2px);
  background: linear-gradient(90deg, #00945d, #00bb70);
}
.btn-outline {
  border: 2px solid #003b82;
  color: #003b82;
  background: transparent;
}
.btn-outline:hover {
  background: #003b82;
  color: white;
}

/* ========== LINKS ========== */
.meeting-link a {
  color: #003b82;
  font-weight: 600;
  text-decoration: none;
}
.meeting-link a:hover {
  text-decoration: underline;
}
.file-link a {
  color: #002147;
  text-decoration: none;
  display: block;
  margin: 4px 0;
}
.file-link a:hover {
  text-decoration: underline;
  color: #0059d1;
}

/* ========== FOOTER ========== */
.footer {
  background: linear-gradient(90deg, #002147, #003b82);
  color: white;
  padding: 16px;
  text-align: center;
  font-size: 14px;
  border-top-left-radius: 25px;
  border-top-right-radius: 25px;
}

/* ========== GRID ========== */
.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 22px;
}

/* ========== ANIMATIONS ========== */
.fade-in {
  animation: fadeIn 0.7s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
footer{position:relative;background:#002147;color:white;text-align:center;padding:5px;}
/* ===== SIDEBAR MOBILE FIX ===== */
.sidebar {
    transition: transform 0.3s ease-in-out;
}

/* Default desktop */
@media (min-width: 769px) {
  .sidebar {
    transform: translateX(0);
  }
  .overlay {
    display: none !important;
  }
}

/* Mobile view */
@media (max-width: 768px) {

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    height: 100%;
    transform: translateX(-260px); /* hidden */
    z-index: 2000;
  }

  .sidebar.active {
    transform: translateX(0); /* slide in */
  }

  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1500;
    display: none;
  }

  .overlay.show {
    display: block;
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
        <?php echo isset($_SESSION['full_name']) ? strtoupper(substr($_SESSION['full_name'],0,1)) : '?'; ?>
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
          $photo_file = $_SESSION['photo'] ?? '';
          $photo_path = 'uploads/profile/' . $photo_file;
          if (!empty($photo_file) && file_exists($photo_path)) {
              echo '<img src="'.$photo_path.'" style="width:88px;height:88px;border-radius:50%;object-fit:cover;">';
          } else {
              echo '<img src="default-avatar.png" style="width:88px;height:88px;border-radius:50%;object-fit:cover;">';
          }
          ?>
        </div>
        <div class="info">
          <h2><?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo $_SESSION['emp_id']; ?>)</h2>
          <div style="display:flex;gap:32px;margin-top:8px">
            <div>
              <h4>Email</h4>
              <div><?php echo $_SESSION['email']; ?></div>
            </div>
          </div>
        </div>
      </div>

<div class="container fade-in">
  <?php if ($meetings->num_rows == 0): ?>
    <div class="card" style="text-align:center;">
      <h3>No Meeting Invites Yet</h3>
      <p>Stay tuned — your admin will schedule meetings soon.</p>
    </div>
  <?php else: ?>
    <div class="grid">
      <?php while ($m = $meetings->fetch_assoc()): ?>
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <h3><?php echo htmlspecialchars($m['title']); ?></h3>
          <span class="badge <?php echo $m['attended'] ? '' : 'pending'; ?>">
            <?php echo $m['attended'] ? 'Attended' : 'Pending'; ?>
          </span>
        </div>
        <hr style="border: none; border-top: 1px solid #ddd;">
        <p><strong>Date:</strong> <?php echo htmlspecialchars($m['date']); ?></p>
        <p><strong>Time:</strong> <?php echo htmlspecialchars($m['start_time']); ?> - <?php echo htmlspecialchars($m['end_time']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($m['description'])); ?></p>

        <?php if ($m['attended'] == 0): ?>
          <form method="POST" style="margin-top:15px;">
            <input type="hidden" name="meeting_id" value="<?php echo $m['id']; ?>">
            <center><button type="submit" name="mark_attendance" class="btn btn-success w-100">✅ Mark Attendance</button></center>
          </form>
          <p style="color:#d22;text-align:center;margin-top:10px;">Link visible after marking attendance.</p>
        <?php else: ?>
          <div class="meeting-link" style="margin-top:12px;">
            <strong>🔗 Meeting Link:</strong><br>
            <a href="<?php echo htmlspecialchars($m['meeting_link']); ?>" target="_blank">
              <?php echo htmlspecialchars($m['meeting_link']); ?>
            </a>
          </div>
          <div class="file-link" style="margin-top:12px;">
            <strong>📂 Files:</strong><br>
            <?php
            $files = $conn->query("SELECT * FROM meeting_files WHERE meeting_id=" . $m['id']);
            if ($files->num_rows > 0) {
              while ($f = $files->fetch_assoc()) {
                echo "<a href='" . $f['file_path'] . "' target='_blank'>📄 " . htmlspecialchars($f['file_name']) . "</a>";
              }
            } else {
              echo "<i>No files uploaded yet.</i>";
            }
            ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
          </main>
</div>
<script>
const hamburger = document.getElementById("hamburger");
const sidebar = document.querySelector(".sidebar");
const overlay = document.getElementById("overlay");

hamburger.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("show");

    // animate hamburger
    hamburger.classList.toggle("open");
});

overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("show");
});
</script>

<footer><p>2025 WorkFusion. All rights reserved.</p></footer>
</body>
</html>
