<?php
session_start();
include "Connect.php";

// ---------------- Fetch Departments ----------------
$departments = [];
$dept_res = $conn->query("SELECT DISTINCT department FROM users");
while($row = $dept_res->fetch_assoc()){
    $departments[] = $row['department'];
}

// ---------------- Fetch Employees Attendance ----------------
$attendance_data = [];
$sql = "SELECT a.*, u.full_name, u.department,
        (SELECT GROUP_CONCAT(CONCAT(task_name,' (', IF(is_completed=1,'Completed','Pending'), ')') SEPARATOR ', ')
         FROM daily_tasks t 
         WHERE t.emp_id = u.id AND t.task_date = a.date) as daily_tasks
        FROM attendance a
        JOIN users u ON a.emp_id = u.id
        ORDER BY a.date DESC";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()){
    $attendance_data[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WorkFusion Admin Portal</title>
<style>
/* Simple reset */
* { box-sizing: border-box; margin: 0; padding: 0; }
html,body{ height:100%; font-family:Inter, 'Segoe UI', Roboto, Arial, sans-serif; background:#f5f7fa;color:#12303f}

/* Top header */
header { background: white; padding: 15px 17px; position: relative; top: 0; z-index: 1000; }
.navbar { display: flex; align-items: center; justify-content: space-between; background: white; position: relative; }
.logo { width: 200px; height: auto; border-color: black; }
.nav-links { list-style: none; display: flex; gap: 25px; }
.nav-links a { color: #062a50; font-size: large; text-decoration: none; font-weight: 700; transition: color 0.3s ; padding: 8px 12px; border-radius: 6px; }
.nav-links a:hover { background: #062a50; color: white; border-radius: 20px; }

/* Hamburger */
.hamburger { display: none; flex-direction: column; cursor: pointer; }
.hamburger span { height: 3px; width: 25px; background: #002147; margin: 4px 0; border-radius: 2px; transition: 0.3s; }

/* Mobile styles */
@media (max-width: 768px) {
  .nav-links { display: none; flex-direction: column; background: #f9f9f9; position: absolute; top: 60px; right: 20px; width: 200px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; padding: 15px; z-index: 1000; }
  .nav-links.active { display: flex; }
  .nav-links a { padding: 10px; display: block; text-align: left; margin: 5px 0; color: #002147; font-weight: bold; text-decoration: none; }
  .hamburger { display: flex; }
}

/* Layout */
.app { display: flex; height: calc(100vh - 64px); }
.sidebar { width: 240px; background-color: #002147; padding: 22px 14px; box-shadow: inset -1px 0 0 rgba(243, 240, 240, 0.904); overflow: auto; }
.main { flex: 1; padding: 22px; overflow: auto; }

.user { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
.avatar { width: 50px; height: 50px; border-radius: 50%; background: #eceff3ff; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 20px; }
.user-info { color: white; line-height: 1.4; }
.user-info .name { font-weight: 700; }
.user-info .admin-id { font-size: 12px; opacity: 0.85; }

.menu a { display: block; padding: 10px; color: white; text-decoration: none; border-radius: 4px; }
.menu a:hover, .menu a.active { color: black; background: #ccd8e6; }

.sidebar-separator { border: 0; height: 1px; background-color: rgba(255, 255, 255, 0.3); margin: 16px 0; }

.profile-card{display:flex;gap:18px;background: #eef7fb;border-radius:6px;padding:16px 18px;margin-bottom:18px;align-items:center}

/* Attendance Card */
.attendance-card { background: white; padding: 24px 28px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); margin-top: 10px; transition: all 0.3s ease; }
.attendance-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }

/* Filter Bar */
.filter-bar { display: flex; flex-wrap: wrap; gap: 14px; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.filter-bar input { padding: 10px 100px; border-radius: 8px; border: 1px solid #ccc; background: #f9fafc; color: #12303f; font-size: 15px; outline: none; transition: 0.3s ease; min-width: 270px; }
.filter-bar select { padding: 10px 14px; border-radius: 8px; border: 1px solid #ccc; background: #f9fafc; color: #12303f; font-size: 15px; outline: none; transition: 0.3s ease; }
.filter-bar input:focus, .filter-bar select:focus { border-color: #002147; box-shadow: 0 0 5px rgba(0,33,71,0.25); background: #eef4ff; }
.filter-bar button { background: #002147; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: background 0.3s ease, transform 0.2s; }
.filter-bar button:hover { background: #004080; transform: scale(1.03); }

/* Table Wrapper */
.table-wrapper { overflow-x: auto; }

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

.status-present { color:green; font-weight:bold; }
.status-absent { color:red; font-weight:bold; }
.status-half { color:orange; font-weight:bold; }

/* Footer */
footer { position: relative; bottom: 0%; width: 100%; text-align: center; padding: 2px; background: #002147; color: white; }

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
      <a href="home1.html">Home</a>
      <a href="about.html">About</a>
      <a href="services.html">Services</a>
      <a href="login.html">Login</a>
      <a href="signup.html">Sign Up</a>
      <a href="contact.html">Contact Us</a>
  </div>
  <div class="hamburger" id="hamburger">
      <span></span>
      <span></span>
      <span></span>
  </div>
</nav>
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

<div class="app">
<aside class="sidebar" id="sidebar">
<div class="user">
  <div class="avatar">
    <?php $name = $_SESSION['admin_name']; echo strtoupper(substr($name, 0, 1)); ?>
  </div>
  <div class="user-info">
    <div class="name"><?php echo $_SESSION['admin_name']; ?></div>
    <div class="admin-id">Admin ID: <?php echo $_SESSION['admin_id']; ?></div>
  </div>
</div>
<hr class="sidebar-separator">
<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
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

<div class="main">
<div class="filter-bar">
  <input type="text" id="empIdInput" placeholder="🔍Search by Employee ID">
  <select id="deptFilter">
    <option value="all">All Departments</option>
    <?php foreach($departments as $dept): ?>
        <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
    <?php endforeach; ?>
  </select>
  <button onclick="printEmployee()">Print Employee Attendance</button>
</div>

<div class="attendance-card">
  <center> <h2>Employee Attendance</h2></center>
  <br>
  <div class="table-wrapper">
    <table id="attendanceTable">
      <thead>
        <tr>
          <th>Employee ID</th>
          <th>Name</th>
          <th>Department</th>
          <th>Date</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Location</th>
          <th>Status</th>
          <th>Daily Tasks</th>
        </tr>
      </thead>
      <tbody>
  <?php foreach($attendance_data as $row): ?>
  <tr data-department="<?= htmlspecialchars($row['department']) ?>" data-empid="<?= htmlspecialchars($row['emp_id']) ?>">
    <td><?= htmlspecialchars($row['emp_id']) ?></td>
    <td><?= htmlspecialchars($row['full_name']) ?></td>
    <td><?= htmlspecialchars($row['department']) ?></td>
    <td><?= htmlspecialchars($row['date']) ?></td>
    <td><?= htmlspecialchars($row['checkin_time']) ?></td>
    <td><?= htmlspecialchars($row['checkout_time']) ?></td>

    <!-- ✅ Updated Location Column -->
   <td>
  <?php 
    // If both latitude and longitude exist, create map link
    if (!empty($row['latitude']) && !empty($row['longitude'])):
        $lat = htmlspecialchars($row['latitude']);
        $lon = htmlspecialchars($row['longitude']);
        $map_url = "https://www.google.com/maps?q={$lat},{$lon}";
  ?>
      <a href="<?= $map_url ?>" target="_blank"
         style="background:#002147;color:white;padding:5px 10px;border-radius:6px;text-decoration:none;font-weight:600;">
         View Location
      </a>
  <?php else: ?>
      <span style="color:#777;">Not Available</span>
  <?php endif; ?>
</td>

    <td class="status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
      <?= htmlspecialchars($row['status']) ?>
    </td>
    <td><?= htmlspecialchars($row['daily_tasks'] ?: 'No Task') ?></td>
  </tr>
  <?php endforeach; ?>
</tbody>

    </table>
  </div>
</div>
</div>
</main>
</div>

<script>
const tableRows = document.querySelectorAll('#attendanceTable tbody tr');
const empIdInput = document.getElementById('empIdInput');
const deptFilter = document.getElementById('deptFilter');

function filterTable() {
    const empId = empIdInput.value.toLowerCase();
    const dept = deptFilter.value.toLowerCase();
    tableRows.forEach(row => {
        const rowDept = row.getAttribute('data-department').toLowerCase();
        const rowEmpId = row.getAttribute('data-empid').toLowerCase();
        if ((dept === 'all' || dept === rowDept) && (empId === '' || rowEmpId.includes(empId))) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
empIdInput.addEventListener('input', filterTable);
deptFilter.addEventListener('change', filterTable);

function printEmployee() {
    const empId = empIdInput.value.trim().toLowerCase();
    const dept = deptFilter.value.toLowerCase();

    if (empId === "" && (dept === "all" || dept === "")) {
        alert("Please enter an Employee ID or select a Department to print.");
        return;
    }

    let title = "<h2>Employee Attendance Report</h2>";
    if (empId !== "") title += "<p><strong>Employee ID:</strong> " + empId + "</p>";
    if (dept !== "all") title += "<p><strong>Department:</strong> " + dept + "</p>";

    let printContent = title + `
        <table border="1" cellspacing="0" cellpadding="8" style="border-collapse:collapse;width:100%;margin-top:10px">
          <tr style="background:#002147;color:white;">
            <th>Employee ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Date</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Location</th>
            <th>Status</th>
            <th>Daily Tasks</th>
          </tr>
    `;

    let hasRows = false;
    tableRows.forEach(row => {
        const rowDept = row.getAttribute('data-department').toLowerCase();
        const rowEmpId = row.getAttribute('data-empid').toLowerCase();

        if ((empId && rowEmpId.includes(empId)) || (dept !== "all" && rowDept === dept)) {
            hasRows = true;
            printContent += `
              <tr>
                <td>${row.cells[0].textContent}</td>
                <td>${row.cells[1].textContent}</td>
                <td>${row.cells[2].textContent}</td>
                <td>${row.cells[3].textContent}</td>
                <td>${row.cells[4].textContent}</td>
                <td>${row.cells[5].textContent}</td>
                <td>${row.cells[6].textContent}</td>
                <td>${row.cells[7].textContent}</td>
                <td>${row.cells[8].textContent}</td>
              
              </tr>
            `;
        }
    });

    printContent += "</table>";

    if (!hasRows) { alert("No records found for the given filters."); return; }

    const newWin = window.open('', '', 'width=1000,height=600');
    newWin.document.write(`
        <html>
        <head>
          <title>Attendance Report</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 20px; color:#002147;}
            h2 { text-align:center; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #aaa; padding: 8px; text-align:center; }
            th { background-color: #002147; color: white; }
            tr:nth-child(even){background:#f5f7fa;}
          </style>
        </head>
        <body>${printContent}</body>
        </html>
    `);
    newWin.document.close();
    newWin.print();
}
</script>

<footer>
    <p>2025 WorkFusion. All rights reserved.</p>
</footer>

</body>
</html>
