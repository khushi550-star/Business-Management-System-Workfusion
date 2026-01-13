<?php
session_start(); // ✅ Must be at the very top

// If not logged in, redirect
if(!isset($_SESSION['admin_id'])){
    header("Location: admin_login.php");
    exit;
}

// Include DB connection
include "Connect.php";


// Handle Approve/Reject leave
if(isset($_GET['action']) && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Fetch leave details
    $res = $conn->query("SELECT emp_id, start_date, end_date FROM leaves WHERE id=$id");
    $leave = $res->fetch_assoc();
    $emp_id = $leave['emp_id'];
    $start_date = $leave['start_date'];
    $end_date = $leave['end_date'];

    // Update leave status in leaves table
    $status_text = $action === 'approve' ? 'Approved' : 'Rejected';
    $conn->query("UPDATE leaves SET status='$status_text', notified=0 WHERE id=$id");

    // Update leave status in leaves table
$status_text = $action === 'approve' ? 'Approved' : 'Rejected';
$conn->query("UPDATE leaves SET status='$status_text', notified=0 WHERE id=$id");

// ✅ Get leave type also, so we can mark attendance correctly
$leaveTypeRes = $conn->query("SELECT leave_type FROM leaves WHERE id=$id");
$leaveRow = $leaveTypeRes->fetch_assoc();
$leave_type = $leaveRow['leave_type']; // e.g. 'Sick Leave', 'Casual Leave', etc.

// ✅ Decide attendance status
if ($action === 'approve') {
    // Approved → Mark as type of leave
    $attendance_status = $leave_type; 
} else {
    // Rejected → Employee should be considered absent (didn’t work and no valid leave)
    $attendance_status = 'Absent';
}

// ✅ Loop through all leave days
$period = new DatePeriod(
    new DateTime($start_date),
    new DateInterval('P1D'),
    (new DateTime($end_date))->modify('+1 day')
);
if($check->num_rows > 0){
    $rowAtt = $check->fetch_assoc();
    if($rowAtt['status'] != 'Present'){
        $conn->query("UPDATE attendance SET status='$attendance_status' WHERE emp_id=$emp_id AND date='$date'");
    }
} else {
    $conn->query("INSERT INTO attendance (emp_id, date, status) VALUES ($emp_id, '$date', '$attendance_status')");
}


    foreach($period as $dt){
        $date = $dt->format("Y-m-d");

        // Update if exists, else insert
        $check = $conn->query("SELECT * FROM attendance WHERE emp_id=$emp_id AND date='$date'");
        if($check->num_rows > 0){
            $conn->query("UPDATE attendance SET status='$attendance_status' WHERE emp_id=$emp_id AND date='$date'");
        } else {
            $conn->query("INSERT INTO attendance (emp_id, date, status) VALUES ($emp_id, '$date', '$attendance_status')");
        }
    }

    header("Location: admin_leave.php"); 
    exit;
}

$res = $conn->query("
  SELECT 
    leaves.*, 
    users.full_name 
  FROM leaves
  JOIN users ON leaves.emp_id = users.id
  ORDER BY leaves.id DESC
");


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Leave Portal</title>
<style>
 /* Simple reset */
    * { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0;
     }
    html,body{
      height:100%;
      font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif;
      background:#f5f7fa;color:#12303f}

    /* Top header */
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
      position: relative;
}

.logo {
  width: 200px;
  height: auto;
  border-color: black;
}

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
  transition: color 0.3s ;
  padding: 8px 12px;
  border-radius: 6px;
}

.nav-links a:hover {
  background: #062a50;
  color: white;
  border-radius: 20px;
}
    /* Hamburger */
    .hamburger {
      display: none;
      flex-direction: column;
      cursor: pointer;
    }

    .hamburger span {
      height: 3px;
      width: 25px;
      background: #002147;
      margin: 4px 0;
      border-radius: 2px;
      transition: 0.3s;
    }
    /* Mobile styles */
@media (max-width: 768px) {
  .nav-links {
    display: none;
    flex-direction: column;  /* stack vertically */
    background: #f9f9f9;
    position: absolute;
    top: 60px;
    right: 20px;
    width: 200px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 15px;
    z-index: 1000;
  }

  .nav-links.active {
    display: flex;   /* ✅ keep flex so children stack in column */
  }

  .nav-links a {
    padding: 10px;
    display: block;   /* ✅ each link takes full width */
    text-align: left;
    margin: 5px 0;
    color: #002147;
    font-weight: bold;
    text-decoration: none;
  }

  .hamburger {
    display: flex;
  }
}
    /* Layout */
   .app {
  display: flex;
  height: calc(100vh - 64px);
}

.sidebar {
  width: 240px;
  background-color: #002147;
  padding: 22px 14px;
  box-shadow: inset -1px 0 0 rgba(243, 240, 240, 0.904);
  overflow: auto;
}

.main {
  flex: 1;
  padding: 22px;
  overflow: auto;
}

    .user {
  display: flex;
  align-items: center;
  gap: 12px;  
  margin-bottom: 18px;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #eceff3ff;   /* Blue background */
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.user-info {
  color: white;
  line-height: 1.4;
}

.user-info .name {
  font-weight: 700;
}

.user-info .admin-id {
  font-size: 12px;
  opacity: 0.85;
}
.menu a {
    display: block;
    padding: 10px;
    color: white;
    font-size:30px
    text-decoration: none;
    border-radius: 4px;
}

.menu a:hover,
.menu a.active {
  color: black;
    background: #ccd8e6;
}

        .sidebar-separator {
  border: 0;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.3); /* white-ish line */
  margin: 16px 0; /* spacing between avatar and menu */
}

    /* Profile card */
    .profile-card{display:flex;gap:18px;background:#eef7fb;border-radius:6px;padding:16px 18px;margin-bottom:18px;align-items:center}

/* Table container */
.table-container{
    overflow-x:auto;
    background:white;
    padding:20px;
     border: 1px solid #dde3ed;
    border-radius:10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Table styling */

/* ============ TABLE ============ */
table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 14px;
  overflow: hidden;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 5px 25px rgba(0,0,0,0.08);
  animation: slideUp 0.6s ease;
}

th {
  background: #002147;
  color: #fff;
  padding: 15px;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  border-bottom: 2px solid rgba(255,255,255,0.3);
}

td {
  padding: 12px 10px;
  text-align: center;
  font-size: 14px;
  border-bottom: 1px solid #d1d5db;
  transition: all 0.3s ease;
}

tr:nth-child(even) {
  background-color: #eef1f6; /* muted light gray-blue */
}
tr:hover {
  background: #dde3f0; /* darker hover with bluish tint */
  transform: scale(1.005);
}
/* Status badges */
.status-pending{
    color:#e67e22;
    font-weight:bold;
}
.status-approved{
    color:#27ae60;
    font-weight:bold;
}
.status-rejected{
    color:#c0392b;
    font-weight:bold;
}

/* Action links */
a{
    text-decoration:none;
    padding:5px 10px;
    border-radius:5px;
    transition:0.3s;
    margin: 0 3px;
}
a[href*="approve"]{
    background-color:#27ae60;
    color:white;
}
a[href*="approve"]:hover{
    background-color:#219150;
}
a[href*="reject"]{
    background-color:#c0392b;
    color:white;
}
a[href*="reject"]:hover{
    background-color:#992d22;
}

/* Responsive */
@media(max-width:900px){
    table{
        font-size:12px;
    }
    th,td{
        padding:10px;
    }
}

/* Footer */
  footer {
   position: relative;
  bottom: 0%;
  width: 100%;
  text-align: center;
  padding: 2px;
  background: #002147;
  color: white;
    }
    /* ===== Mobile Sidebar & Hamburger ===== */
@media (max-width: 1024px){
  .app { flex-direction: column; }

  .main { padding: 16px; }
}

@media (max-width: 768px) {
  /* Sidebar hidden initially */
  .sidebar {
    position: fixed;
    top: 64px;
    left: -260px; /* hidden left */
    width: 240px;
    height: calc(100% - 64px);
    background-color: #002147;
    transition: left 0.3s ease;
    z-index: 9999;
    overflow-y: auto;
  }

  .sidebar.active { left: 0; }

  .hamburger { display: flex; cursor: pointer; }

  .nav-links { display: none !important; }

  .profile-card {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .profile-card .photo { margin-bottom: 12px; }
  .profile-card .info h2 { font-size: 18px; margin-bottom: 6px; }
  .profile-card .info div { font-size: 14px; }

  .attendance-card { padding: 16px; }
}
</style>
</head>
<body>
      <header>

    <nav class="navbar">
      
      <img src="logo.png" alt="WorkFusion Logo" class="logo" />
      <div class="nav-links" id="navLinks">
        <ul>
         <a href="home1.html">Home   </a>
      <a href="about.html">About   </a>
      <a href="services.html">Services   </a>
      <a href="login.html">Login   </a>
      <a href="signup.html">Sign Up   </a>
      <a href="contact.html">Contact Us</a>
    </div>
        <div class="hamburger" id="hamburger">
      <span></span>
      <span></span>
      <span></span>
    </div>
      </nav>
  
  <!-- Script -->
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
  </header>
    </div>
  </div>

   <div class="app">
    <aside class="sidebar" id="sidebar">
  <div class="user">
    <!-- Avatar circle -->
    <div class="avatar">
      <?php
        $name = $_SESSION['admin_name'];               
        $firstLetter = strtoupper(substr($name, 0, 1)); 
        echo $firstLetter;
      ?>
    </div>

    <!-- Name + ID -->
    <div class="user-info">
      <div class="name"><?php echo $_SESSION['admin_name']; ?></div>
      <div class="admin-id">Admin ID: <?php echo $_SESSION['admin_id']; ?></div>
    </div>
  </div>

 
  <hr class="sidebar-separator">

      <!-- Sidebar Menu -->
<?php
// Get current filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
           <!-- Sidebar Menu -->
    <nav class="menu">
      <a href="admin.php" class="<?= $current_page=='admin.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="admin_att_tracking.php"  class="<?= $current_page=='admin_att_tracking.php' ? 'active' : '' ?>">Attendance Tracking</a>
        <a href="admin_payroll.php"   class="<?= $current_page=='admin_payroll.php' ? 'active' : '' ?>">Payroll management</a>
        <a href="admin_projects.php"  class="<?= $current_page=='admin_projects.php' ? 'active' : '' ?>">Tasks & Projects assign</a>
        <a href="admin_portal.php"  class="<?= $current_page=='admin_portal.php' ? 'active' : '' ?>">Meetings Conducting</a>
        <a href="admin_notices.php"  class="<?= $current_page=='admin_notices.php' ? 'active' : '' ?>">Announcement & Notices</a>
        <a href="admin_leave.php"  class="<?= $current_page=='admin_leaves.php' ? 'active' : '' ?>">Leave Requests</a>
        <a href="admin_emp.php"  class="<?= $current_page=='admin_emp.php' ? 'active' : '' ?>">Employee Details</a>
        <a href="logout.php"  class="<?= $current_page=='logout.php' ? 'active' : '' ?>">Log-out</a>
      </nav>

  </aside>

     <main class="main">
      <div class="content">

        <div class="profile-card">
                   <div class="photo"><img src="m1.jpg" alt="Admin" style="width: 88px;height: 88px; border-radius: 50%;"></div>
          <div class="info">
            <h2><?php echo $_SESSION['admin_name']; ?> (<?php echo $_SESSION['admin_id']; ?>)</h2>
            <div style="display:flex;gap:32px;margin-top:8px">
              <div>
                <h4>Email</h4>
                <div><?php echo $_SESSION['admin_email']; ?></div>
              </div>
            </div>
          </div>
        </div>


<div class="table-container">
    <center><h2>Leave Requests</h2></center>
    <br>
<table>
<tr>
<th>Employee</th><th>Leave Type</th><th>Start</th><th>End</th><th>Reason</th><th>Medical File</th><th>Status</th><th>Action</th>
</tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><?= $row['leave_type'] ?></td>
<td><?= $row['start_date'] ?></td>
<td><?= $row['end_date'] ?></td>
<td><?= htmlspecialchars($row['reason']) ?></td>
<td>
<?php 
if(!empty($row['medical_file']) && file_exists($row['medical_file'])){
    echo '<a href="'.htmlspecialchars($row['medical_file']).'" target="_blank">View</a>';
} else {
    echo '-';
}
?>
</td>
<td class="status-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></td>
<td>
<?php if($row['status']=='Pending'): ?>
<a href="admin_leave.php?action=approve&id=<?= $row['id'] ?>">Approve</a>
<a href="admin_leave.php?action=reject&id=<?= $row['id'] ?>">Reject</a>
<?php else: ?>
-
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>
</main>
</div>
<footer>
    
    <p>2025 WorkFusion. All rights reserved.</p>
  </footer>
</body>
</html>
