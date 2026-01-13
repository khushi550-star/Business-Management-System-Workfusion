<?php 
session_start();
include "Connect.php";

if (isset($_POST['save_salary'])) {
    $basic = $_POST['basic_salary'];
    $medical = $_POST['medical_allowance'];
    $house = $_POST['house_rent'];
    $other = $_POST['other_allowance'];

   // Check if a row already exists
$check = $conn->query("SELECT id FROM salary_settings LIMIT 1");

if ($check->num_rows > 0) {
    // ✅ Update existing row
    $stmt = $conn->prepare("UPDATE salary_settings 
        SET basic_salary=?, medical_allowance=?, house_rent=?, other_allowance=? WHERE id=(SELECT id FROM (SELECT id FROM salary_settings LIMIT 1) AS t)");
} else {
    // ✅ Insert only first time
    $stmt = $conn->prepare("INSERT INTO salary_settings (basic_salary, medical_allowance, house_rent, other_allowance) VALUES (?, ?, ?, ?)");
}
$stmt->bind_param("dddd", $basic, $medical, $house, $other);
$stmt->execute();

echo "<script>alert('Global salary settings updated successfully!');</script>";
}


// ---------- Utility Functions ---------- //
function calculateDays($start, $end) {
    $start = new DateTime($start);
    $end = new DateTime($end);
    return $end->diff($start)->days + 1;
}
function getLeaveBreakdown($conn, $emp_id, $month, $year, $basic) {
    $per_day = $basic / 30.0;

    // --- Attendance counters --- //
    $absentDays = 0.0;
    $halfDays = 0.0;
    $casual = 0;
    $sick = 0;
    $paid = 0;
    $maternity = 0;

    // === Step 1: Attendance (ignore weekends) === //
    $att_q = "SELECT status, date FROM attendance WHERE emp_id=? AND MONTH(`date`)=? AND YEAR(`date`)=?";
    $stmtA = $conn->prepare($att_q);
    $stmtA->bind_param("iii", $emp_id, $month, $year);
    $stmtA->execute();
    $att_res = $stmtA->get_result();

    while ($r = $att_res->fetch_assoc()) {
        $dayNum = date('N', strtotime($r['date']));
        if ($dayNum >= 6) continue; // skip weekends

        $status = strtolower(trim($r['status']));
        if ($status == 'present') continue;
        elseif ($status == 'absent') $absentDays += 1;
        elseif ($status == 'half day' || $status == 'half') $halfDays += 0.5;
        elseif (strpos($status, 'casual') !== false) $casual += 1;
        elseif (strpos($status, 'sick') !== false) $sick += 1;
        elseif (strpos($status, 'paid') !== false) $paid += 1;
        elseif (strpos($status, 'matern') !== false) $maternity += 1;
    }

    // === Step 2: Combined Monthly Limit (Casual + Sick ≤ 3 per month) === //
    $monthCasualSick = $casual + $sick;
    $extraMonthlyLeave = max(0, $monthCasualSick - 3);

    // === Step 3: Yearly Leave Tracking === //
    $prevCasual = 0; 
    $prevSick = 0;
    $prevPaid = 0;
    $prevMaternity = 0;

    $leave_prev_q = "SELECT leave_type, start_date, end_date 
                     FROM leaves 
                     WHERE emp_id=? AND status='Approved'
                     AND YEAR(start_date)=? AND MONTH(start_date) < ?";
    $stmtPrev = $conn->prepare($leave_prev_q);
    $stmtPrev->bind_param("iii", $emp_id, $year, $month);
    $stmtPrev->execute();
    $resPrev = $stmtPrev->get_result();

    while ($p = $resPrev->fetch_assoc()) {
        $lt = strtolower(trim($p['leave_type']));
        $start = strtotime($p['start_date']);
        $end = strtotime($p['end_date']);
        for ($d = $start; $d <= $end; $d += 86400) {
            $dayNum = date('N', $d);
            if ($dayNum >= 6) continue;
            if (strpos($lt, 'casual') !== false) $prevCasual++;
            elseif (strpos($lt, 'sick') !== false) $prevSick++;
            elseif (strpos($lt, 'paid') !== false) $prevPaid++;
            elseif (strpos($lt, 'matern') !== false) $prevMaternity++;
        }
    }

    // === Step 4: Yearly Limits === //
    $casualLimit = 10;
    $sickLimit = 10;
    $paidLimit = 20;
    $maternityLimit = 120;

    // === Step 5: Check Excess Beyond Yearly Limit === //
    $extraCasualYear = max(0, ($prevCasual + $casual) - $casualLimit);
    $extraSickYear = max(0, ($prevSick + $sick) - $sickLimit);
    $extraPaidYear = max(0, ($prevPaid + $paid) - $paidLimit);
    $extraMaternityYear = max(0, ($maternity - $maternityLimit));

    // === Step 6: Avoid Double Counting === //
    // take maximum of monthly or yearly excess for casual/sick
    $unpaidCasualSick = max($extraMonthlyLeave, $extraCasualYear + $extraSickYear);

    // === Step 7: Total Unpaid Days === //
    $unpaidDays = 0.0;
    $unpaidDays += $absentDays;               // full salary cut
    $unpaidDays += $halfDays;                 // half salary cut
    $unpaidDays += $unpaidCasualSick;         // max of month/year excess
    $unpaidDays += $extraPaidYear;            // paid leave yearly excess
    $unpaidDays += max(0, $extraMaternityYear);

    // === Step 8: Salary Deduction === //
    $total_deduction = round($unpaidDays * $per_day, 2);

    // === Step 9: Return Breakdown === //
    return [
        "attendance_absent" => $absentDays,
        "attendance_half" => $halfDays,
        "casual" => $casual,
        "sick" => $sick,
        "paid" => $paid,
        "maternity" => $maternity,
        "extraMonthly" => $extraMonthlyLeave,
        "extraCasualYear" => $extraCasualYear,
        "extraSickYear" => $extraSickYear,
        "extraPaidYear" => $extraPaidYear,
        "extraMaternityYear" => $extraMaternityYear,
        "unpaidDays" => $unpaidDays,
        "per_day" => $per_day,
        "total_deduction" => $total_deduction
    ];
}

// ---------- Payroll Generation ---------- //
if (isset($_POST['generate_all'])) {
    $month = $_POST['month'];
    $year  = $_POST['year'];

    if (empty($month) || empty($year)) {
        echo "<script>alert('Please select both month and year!');</script>";
    } else {
        $monthNum = is_numeric($month) ? intval($month) : date('n', strtotime($month));
        $yearNum = intval($year);

        $global = $conn->query("SELECT * FROM salary_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
        $global_basic   = floatval($global['basic_salary']);
        $medical        = floatval($global['medical_allowance']);
        $house          = floatval($global['house_rent']);
        $other          = floatval($global['other_allowance']);

        $employees = $conn->query("SELECT id, full_name, basic_salary FROM users");

        while ($emp = $employees->fetch_assoc()) {
            $emp_id  = $emp['id'];
            $emp_name = $emp['full_name'];

            $basic = (!empty($emp['basic_salary']) && $emp['basic_salary'] > 0)
                ? floatval($emp['basic_salary'])
                : $global_basic;

            $total_allowances = $medical + $house + $other;
            $gross = $basic + $total_allowances;

            $break = getLeaveBreakdown($conn, $emp_id, $monthNum, $yearNum, $basic);

            $total_deduction = isset($break['total_deduction']) ? round($break['total_deduction'], 2) : 0;
            $net_pay = round($gross - $total_deduction, 2);

            $stmt = $conn->prepare("INSERT INTO payroll 
                (emp_id, month, year, basic_salary, medical_allowance, house_rent, other_allowance,
                 total_allowances, gross_salary, leave_deduction, total_deduction, net_pay, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param(
                'issddddddddd',
                $emp_id, $month, $year,
                $basic, $medical, $house, $other,
                $total_allowances, $gross,
                $total_deduction, $total_deduction, $net_pay
            );
            $stmt->execute();

            // 🔔 Add Notification: Payroll Generated (use prepared insert to be safe)
            $msg = $conn->real_escape_string("Your payroll for $month $year has been generated and is pending admin verification.");
            $conn->query("INSERT INTO notifications (emp_id, message) VALUES ($emp_id, '$msg')");
        }

        echo "<script>alert('Payroll successfully generated for all employees! Notifications sent.');</script>";
    }
}

// ---------- Verify Payroll ---------- //
if (isset($_GET['verify'])) {
    $pid = intval($_GET['verify']);

    // Update payroll status
    $conn->query("UPDATE payroll SET status='Verified' WHERE id=$pid");

    // Fetch emp info for notification
    $info = $conn->query("SELECT emp_id, month, year FROM payroll WHERE id=$pid")->fetch_assoc();
    $emp_id = $info['emp_id'];
    $month = $info['month'];
    $year = $info['year'];

    // 🔔 Add Notification: Payroll Verified
    $msg = $conn->real_escape_string("Your payslip for $month $year is verified and now available.");
    $conn->query("INSERT INTO notifications (emp_id, message) VALUES ($emp_id, '$msg')");

    echo "<script>alert('Payroll verified and notification sent to employee!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Payroll Management</title>
<style>
/* ============ GLOBAL SETTINGS ============ */


:root {
  --primary: #003366;     /* Dark Blue */
  --secondary: #001f4d;   /* Deep Navy Blue */
  --accent: #0a84ff;      /* Slightly Brighter Blue Accent */
  --bg: #e6ecf5;          /* Muted dark-blue background tint */
  --text-dark: #0f172a;
  --text-light: #64748b;
  --card-bg: white;
}

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
  font-size: 20px;
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
    .profile-card{display:flex;gap:18px;background: #eef7fb;border-radius:6px;padding:16px 18px;margin-bottom:18px;align-items:center}


/* ============ CARD CONTAINERS ============ */
.card {
  background: var(--card-bg);
  backdrop-filter: blur(12px);
  border-radius: 20px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.08),
              inset 0 0 10px rgba(255,255,255,0.3);
  padding: 28px 35px;
  margin-bottom: 35px;
  border: 1px solid rgba(255,255,255,0.5);
  transition: all 0.4s ease;
}

.card:hover {
  transform: none;
  box-shadow: 0 8px 30px rgba(0,0,0,0.08),
              inset 0 0 10px rgba(255,255,255,0.3);
}

/* ============ FILTER BAR ============ */
.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: #fff;
  padding: 16px 22px;
  border-radius: 14px;
  box-shadow: 0 8px 20px rgba(0,31,77,0.3); /* darker shadow */
  
}

.filter-bar label {
  font-weight: 600;
}

.filter-bar select {
  background: rgba(255,255,255,0.15);
  color: #01010aff;
  border: 1px solid rgba(255,255,255,0.4);
  padding: 8px 14px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}
.filter-bar select:hover {
  background: rgba(255,255,255,0.3);
  transform: scale(1.03);
}
.filter-bar button{
    background: rgba(255,255,255,0.75);
    color: var(--primary);
}
.filter-bar button:hover{
    background: #0851a5ff;
    color:  white;
}
/* ============ TABLE ============ */
table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 14px;
  overflow: hidden;
  background: rgba(255,255,255,0.95);
  box-shadow: 0 5px 25px rgba(0,0,0,0.08);
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
  background: #dde3f0;
}


/* ============ BUTTONS ============ */
button, a.btn {
  padding: 10px 18px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  border: none;
  color: #fff;
  background: #062b57ff;
  cursor: pointer;
  box-shadow: 0 3px 10px rgba(0,31,77,0.3);
  transition: all 0.3s ease;
}
button:hover, a.btn:hover {
  background: #0851a5ff;
}
button:active {
  transform: none;
}

/* ============ STATUS TAGS ============ */
.status {
  padding: 5px 12px;
  border-radius: 50px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}
.status.verified {
  background: rgba(34,197,94,0.15);
  color: #16a34a;
}
.status.pending {
  background: rgba(239,68,68,0.15);
  color: #dc2626;
}

/* ============ POPUP ============ */
.popup {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  backdrop-filter: blur(8px);
  justify-content: center;
  align-items: center;
  z-index: 9999;
}
.popup-content {
  background: #fff;
  border-radius: 18px;
  padding: 25px 35px;
  width: 420px;
  box-shadow: 0 15px 30px rgba(0,0,0,0.2);
 
}
.popup-content h3 {
  margin-top: 0;
  font-weight: 600;
  color: var(--primary);
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 6px;
}

/* ============ ANIMATIONS ============ */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ============ RESPONSIVE DESIGN ============ */
@media (max-width: 768px) {
  body { padding: 20px; }
  .filter-bar { flex-direction: column; align-items: flex-start; gap: 10px; }
  table { font-size: 12px; }
  button, select, a.btn { width: 100%; margin-top: 6px; }
  td, th { padding: 10px 6px; }
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
    .popup-content input[type="number"] {
  width: 100%;
  padding: 8px;
  margin-top: 4px;
  border: 1px solid #ccc;
  border-radius: 6px;
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

<center><h1>Payroll Management </h1></center>
<br>

<!-- 💰 Global Salary Structure -->
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin:0;">💰 Global Salary Structure</h3>
    <button type="button" class="btn" onclick="openSalaryPopup()">⚙️ Update Settings</button>

    </div>
    <?php
    $global = $conn->query("SELECT * FROM salary_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if($global){
        echo "<table>
            <tr><th>Basic</th><th>Medical</th><th>House Rent</th><th>Other allowance</th><th>Total Pay</th></tr>
            <tr>
                <td>₹ {$global['basic_salary']}</td>
                <td>₹ {$global['medical_allowance']}</td>
                <td>₹ {$global['house_rent']}</td>
                <td>₹ {$global['other_allowance']}</td>
                <td><b>₹ ".($global['basic_salary']+$global['medical_allowance']+$global['house_rent']+$global['other_allowance'])."</b></td>
            </tr>
        </table>";
    }
    ?>
</div>

<!-- 🧾 Payroll Generation -->
<div class="card">
    <h3 style="margin-top:0;">🧾 Generate Payroll</h3>
    <form method="POST" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
        <label>Select Month:</label>
        <select name="month" required>
            <option value="">--Select--</option>
            <?php 
            $months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
            foreach($months as $m){ echo "<option value='$m'>$m</option>"; }
            ?>
        </select>

        <label>Select Year:</label>
        <select name="year" required>
            <?php for ($y = date("Y"); $y >= 2020; $y--) echo "<option value='$y'>$y</option>"; ?>
        </select>

        <button type="submit" name="generate_all">Generate Payroll for All</button>
    </form>
</div>

<!-- 🔍 Filter Payroll Records -->
<div class="filter-bar">
<form method="GET" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
    <label>Month:</label>
    <select name="month_filter">
        <option value="">All</option>
        <?php foreach($months as $m) echo "<option value='$m'>$m</option>"; ?>
    </select>

    <label>Year:</label>
    <select name="year_filter">
        <option value="">All</option>
        <?php for ($y = date("Y"); $y >= 2020; $y--) echo "<option value='$y'>$y</option>"; ?>
    </select>

    <label>Employee:</label>
    <select name="emp_filter">
        <option value="">All Employees</option>
        <?php
        $users = $conn->query("SELECT id, full_name FROM users");
        while($u=$users->fetch_assoc()){
            echo "<option value='{$u['id']}'>{$u['full_name']}</option>";
        }
        ?>
    </select>
    <button type="submit">Filter Payroll</button>
</form>
</div>

<!-- 🧾 Payroll Summary (Month-wise) -->
<div class="card">
  <h3>🧾 Payroll Summary (Month-wise)</h3>

  <?php
  $where = [];
  if (!empty($_GET['year_filter'])) $where[] = "p.year='{$_GET['year_filter']}'";
  if (!empty($_GET['emp_filter'])) $where[] = "p.emp_id='{$_GET['emp_filter']}'";
  $where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

  // Fetch distinct month+year combinations
  $monthsRes = $conn->query("
    SELECT DISTINCT p.month, p.year 
    FROM payroll p 
    $where_sql 
    ORDER BY p.year DESC, 
    FIELD(p.month, 'January','February','March','April','May','June','July','August','September','October','November','December')
  ");

  if ($monthsRes->num_rows == 0) {
      echo "<p>No payroll records found.</p>";
  } else {
      while ($mRow = $monthsRes->fetch_assoc()) {
          $monthName = $mRow['month'];
          $year = $mRow['year'];

          echo "<h4 style='margin-top:20px; color:#334155;'>📅 {$monthName} {$year}</h4>";
          echo "<table>
                  <tr>
                      <th>ID</th><th>Employee</th><th>Total Salary</th>
                      <th>Salary Cut (₹)</th><th>Final Pay (₹)</th><th>Status</th><th>Action</th>
                  </tr>";

          $res = $conn->query("
              SELECT p.*, u.full_name, u.basic_salary 
              FROM payroll p 
              JOIN users u ON p.emp_id=u.id 
              WHERE p.month='{$monthName}' AND p.year='{$year}'
              ORDER BY u.full_name ASC
          ");

          if ($res->num_rows == 0) {
              echo "<tr><td colspan='7'>No records for {$monthName} {$year}.</td></tr>";
          } else {
              while ($row = $res->fetch_assoc()) {
                  $monthNum = is_numeric($row['month']) ? intval($row['month']) : date('n', strtotime($row['month']));
                  $break = getLeaveBreakdown($conn, $row['emp_id'], $monthNum, $row['year'], $row['basic_salary']);
                  $popupData = htmlspecialchars(json_encode($break), ENT_QUOTES, 'UTF-8');

                  echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['full_name']}</td>
                      <td>₹ {$row['gross_salary']}</td>
                      <td>₹ {$row['total_deduction']} 
                          <button data-breakdown='{$popupData}' onclick='showPopupData(JSON.parse(this.dataset.breakdown))'>View</button>
                      </td>
                      <td><b>₹ {$row['net_pay']}</b></td>
                      <td>{$row['status']}</td>
                      <td>";

                  echo "<form method='post' action='payslip.php' target='_blank' style='display:inline'>
                          <input type='hidden' name='payroll_id' value='{$row['id']}'>
                          <button>View Payslip</button>
                        </form> ";

                  if ($row['status'] == 'Pending') {
                      echo "<a href='?verify={$row['id']}'><button>Verify</button></a>";
                  } else {
                      echo "✅ Verified";
                  }

                  echo "</td></tr>";
              }
          }
          echo "</table>";
      }
  }
  ?>
</div>
<!-- 💰 Global Salary Popup -->
<div class="popup" id="salaryPopup">
  <div class="popup-content">
    <h3>💰 Update Global Salary Settings</h3>
    <br>
    <form method="POST">
      <label>Basic Salary:</label>
      <input type="number" name="basic_salary" required><br><br>

      <label>Medical Allowance:</label>
      <input type="number" name="medical_allowance" required><br><br>

      <label>House Rent:</label>
      <input type="number" name="house_rent" required><br><br>

      <label>Other Allowance:</label>
      <input type="number" name="other_allowance" required><br><br>

      <div style="text-align:right;">
        <button type="submit" name="save_salary">Save</button>
        <button type="button" onclick="closeSalaryPopup()">Cancel</button>
      </div>
    </form>
  </div>
</div>
<!-- Deduction Popup with Tabs -->
<div id="deductionPopup" style="
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
">
  <div style="
    background: #fff;
    width: 600px;
    max-width: 95%;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    padding: 20px;
    font-family: 'Segoe UI', sans-serif;
    animation: pop 0.3s ease-in-out;
  ">
    <h2 style="text-align:center; color:#0b3d91; margin-bottom:10px;">Deduction Summary</h2>

    <!-- Tab Buttons -->
    <div class="tab-buttons">
      <button class="tab-btn active" onclick="openTab(event, 'monthlyTab')">Monthly</button>
      <button class="tab-btn" onclick="openTab(event, 'yearlyTab')">Yearly</button>
      <button class="tab-btn" onclick="openTab(event, 'deductionTab')">Final Deduction</button>
    </div>

    <!-- Tab Content -->
    <div id="monthlyTab" class="tab-content"></div>
    <div id="yearlyTab" class="tab-content" style="display:none;"></div>
    <div id="deductionTab" class="tab-content" style="display:none;"></div>

    <button onclick="closePopup()" style="
      background-color:#dc3545;
      color:white;
      border:none;
      padding:10px;
      border-radius:6px;
      font-weight:bold;
      width:100%;
      cursor:pointer;
      margin-top:15px;
    ">Close</button>
  </div>
</div>

<style>
@keyframes pop {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.popup-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
  font-size: 14px;
}
.popup-table th {
  background: #092350ff;
  color: white;
  text-align: left;
  padding: 8px;
  text-transform: uppercase;
}
.popup-table td {
  padding: 8px;
  border: 1px solid #ddd;
}
.popup-table tr:nth-child(even) {
  background: #f9f9f9;
}
.tab-buttons {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  border-bottom: 2px solid #092350ff;
}
.tab-btn {
  flex: 1;
  background: none;
  border: none;
  padding: 10px;
  font-weight: 600;
  cursor: pointer;
  color: #092350ff;
  transition: 0.3s;
}
.tab-btn.active {
  background: #092350ff;
  color: #fff;
  border-radius: 8px 8px 0 0;
}
.tab-btn:hover {
  background: #092350ff;
  color: #fff;
}
</style>

<script>
function showPopupData(data) {
  // Monthly Table
  const monthlyHTML = `
    <table class="popup-table">
      <thead><tr><th colspan="2">📅 Monthly Leave Summary</th></tr></thead>
      <tbody>
        <tr><td>Casual Leave</td><td>${data.casual} day(s)</td></tr>
        <tr><td>Sick Leave</td><td>${data.sick} day(s)</td></tr>
        <tr><td>Paid Leave</td><td>${data.paid} day(s)</td></tr>
        <tr><td>Maternity Leave</td><td>${data.maternity} day(s)</td></tr>
        <tr><td><b>Monthly Limit</b></td><td><b>3 Leaves</b></td></tr>
        <tr><td>Total (Casual + Sick)</td><td><b>${data.casual + data.sick}</b></td></tr>
        <tr><td>Status</td>
          <td>${data.extraMonthly > 0 
            ? `<span style='color:red;'>❌ Exceeded by ${data.extraMonthly} day(s)</span>` 
            : `<span style='color:green;'>✅ Within Monthly Limit</span>`}
          </td></tr>
      </tbody>
    </table>
  `;
  // Yearly Table
  const yearlyHTML = `
    <table class="popup-table">
      <thead><tr><th colspan="2">📆 Yearly Leave Summary</th></tr></thead>
      <tbody>
        <tr><td>Casual Leave Taken</td><td>${data.extraCasualYear > 0 ? "Over Limit" : "Within Limit"}</td></tr>
        <tr><td>Sick Leave Taken</td><td>${data.extraSickYear > 0 ? "Over Limit" : "Within Limit"}</td></tr>
        <tr><td>Paid Leave Taken</td><td>${data.extraPaidYear > 0 ? "Over Limit" : "Within Limit"}</td></tr>
        <tr><td>Maternity Leave Taken</td><td>${data.extraMaternityYear > 0 ? "Over Limit" : "Within Limit"}</td></tr>
        <tr><td>Status</td>
          <td>${(data.extraCasualYear > 0 || data.extraSickYear > 0 || data.extraPaidYear > 0 || data.extraMaternityYear > 0)
            ? `<span style='color:red;'>⚠️ Yearly Limit Exceeded</span>`
            : `<span style='color:green;'>✅ Within Yearly Limits</span>`}
          </td></tr>
      </tbody>
    </table>
  `;

   
  // Deduction Table
  const deductionHTML = `
    <table class="popup-table">
      <thead><tr><th colspan="2">🧮 Final Deduction Summary</th></tr></thead>
      <tbody>
        <tr><td>Absent Days</td><td>${data.attendance_absent}</td></tr>
        <tr><td>Half Days</td><td>${data.attendance_half}</td></tr>
        <tr><td>Unpaid Days</td><td><b>${data.unpaidDays}</b></td></tr>
        <tr><td>Per Day Salary</td><td>₹${data.per_day.toFixed(2)}</td></tr>
        <tr><td><b>Total Deduction</b></td><td><b>₹${data.total_deduction.toFixed(2)}</b></td></tr>
      </tbody>
    </table>
  `;

  document.getElementById('monthlyTab').innerHTML = monthlyHTML;
  document.getElementById('yearlyTab').innerHTML = yearlyHTML;
  document.getElementById('deductionTab').innerHTML = deductionHTML;

  document.getElementById('deductionPopup').style.display = 'flex';
}

function openTab(evt, tabId) {
  const contents = document.querySelectorAll('.tab-content');
  const buttons = document.querySelectorAll('.tab-btn');

  contents.forEach(c => c.style.display = 'none');
  buttons.forEach(b => b.classList.remove('active'));

  document.getElementById(tabId).style.display = 'block';
  evt.currentTarget.classList.add('active');
}

function closePopup() {
  document.getElementById('deductionPopup').style.display = 'none';
}
</script>


<script>
function openSalaryPopup(){
  document.getElementById('salaryPopup').style.display = 'flex';
}
function closeSalaryPopup(){
  document.getElementById('salaryPopup').style.display = 'none';
}
</script>


</body>
</html>
