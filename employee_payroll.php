<?php
session_start();
include "Connect.php";



$emp_id = $_SESSION['emp_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$emp_id")->fetch_assoc();
$emp_name = $user['full_name'];
$basic_salary = $user['basic_salary'] ?? 0;


// ======= Fetch Payroll Records ======= //
$where = "WHERE p.emp_id=$emp_id";
if (!empty($_GET['year_filter'])) $where .= " AND p.year='{$_GET['year_filter']}'";
if (!empty($_GET['month_filter'])) $where .= " AND p.month='{$_GET['month_filter']}'";

$payrolls = $conn->query("
    SELECT p.*, u.full_name 
    FROM payroll p 
    JOIN users u ON p.emp_id=u.id 
    $where 
    ORDER BY p.year DESC,
    FIELD(p.month, 'January','February','March','April','May','June','July','August','September','October','November','December')
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Payroll Portal</title>
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
.container {
  max-width: 1100px;
  margin: 40px auto;
  background: #fff;
  border-radius: 12px;
  padding: 25px 30px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
h2 {
  color: #002147;
  margin-bottom: 10px;
}
/* ===== POLISHED CARD TABLE ===== */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px; /* Adds spacing to make rows look like cards */
}

tr {
  background: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border-radius: 12px;
}

th {
  background: #002147 !important;
  color: white;
  padding: 14px;
  border: none;
}

td {
  padding: 14px 12px;
  font-size: 14px;
  border: none;
}

/* first and last cell rounded corners */
tr td:first-child {
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
}
tr td:last-child {
  border-top-right-radius: 12px;
  border-bottom-right-radius: 12px;
}

/* Button alignment inside table */
td button {
  padding: 7px 12px;
  font-size: 12px;
  border-radius: 6px;
}

/* Status badge redesign */
.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
}
.status.Verified { background: #d1fae5; color: #065f46; }
.status.Pending { background: #fee2e2; color: #b91c1c; }


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


.status {
  padding: 5px 12px;
  border-radius: 50px;
  font-weight: bold;
  text-transform: uppercase;
  font-size: 12px;
}
.status.Verified { background: #d1fae5; color: #065f46; }
.status.Pending { background: #fee2e2; color: #991b1b; }
button, .btn {
  padding: 8px 14px;
  background: #002147;
  border: none;
  color: white;
  border-radius: 6px;
  cursor: pointer;
}
button:hover, .btn:hover { background: #0a5ad7; }
.filter {
  display: flex;
  gap: 10px;
  align-items: center;
  margin-bottom: 20px;
}
.filter select {
  padding: 6px 8px;
  border-radius: 6px;
  border: 1px solid #ccc;
}
.popup {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 999;
}
.popup-content {
  background: white;
  padding: 20px 25px;
  border-radius: 10px;
  width: 400px;
}
.popup-content h3 {
  color: #002147;
}
.popup-content table {
  width: 100%;
  border: none;
}
.popup-content td {
  border: none;
  text-align: left;
  padding: 5px 0;
}
.close-btn {
  background: #991b1b;
  margin-top: 10px;
}
.close-btn:hover { background: #ef4444; }
footer{position:relative;background:#002147;color:white;text-align:center;padding:5px;}
/* ===== SIDEBAR TOGGLE FOR MOBILE ===== */
.sidebar {
  width: 240px;
  background-color: #002147;
  padding: 22px 14px;
  box-shadow: inset -1px 0 0 rgba(243,240,240,0.904);
  overflow: auto;
  transition: transform 0.3s ease;
}

/* DEFAULT: Show sidebar on Desktop */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    transform: translateX(-100%); /* Hidden initially */
    z-index: 2000;
  }
  .sidebar.active {
    transform: translateX(0); /* Slide in */
  }

  /* Screen overlay */
  #overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1500;
  }
  #overlay.active {
    display: block;
  }
  /* ===== POLISHED CARD TABLE ===== */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px; /* Adds spacing to make rows look like cards */
}

tr {
  background: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border-radius: 12px;
}

th {
  background: #002147 !important;
  color: white;
  padding: 14px;
  border: none;
}

td {
  padding: 14px 12px;
  font-size: 14px;
  border: none;
}

/* first and last cell rounded corners */
tr td:first-child {
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
}
tr td:last-child {
  border-top-right-radius: 12px;
  border-bottom-right-radius: 12px;
}

/* Button alignment inside table */
td button {
  padding: 7px 12px;
  font-size: 12px;
  border-radius: 6px;
}

/* Status badge redesign */
.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
}
.status.Verified { background: #d1fae5; color: #065f46; }
.status.Pending { background: #fee2e2; color: #b91c1c; }

}
/* ===== MOBILE TABLE FIX ===== */
@media (max-width: 768px) {

  .table-container {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  table {
    min-width: 650px; /* Ensures table stays readable */
  }

  /* Prevent text overlapping */
  td, th {
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

<div id="overlay"></div>
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


<div class="container">
  <h2>Welcome, <?php echo htmlspecialchars($emp_name); ?></h2>
  <p><b>Employee ID:</b> <?php echo $emp_id; ?></p>
  
  <hr>
  <br>

  <form method="GET" class="filter">
    <label>Month:</label>
    <select name="month_filter">
      <option value="">All</option>
      <?php 
      $months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
      foreach($months as $m){ 
        $sel = (!empty($_GET['month_filter']) && $_GET['month_filter']==$m) ? 'selected' : '';
        echo "<option $sel value='$m'>$m</option>";
      }
      ?>
    </select>

    <label>Year:</label>
    <select name="year_filter">
      <option value="">All</option>
      <?php for($y=date("Y");$y>=2020;$y--){ 
        $sel = (!empty($_GET['year_filter']) && $_GET['year_filter']==$y) ? 'selected' : '';
        echo "<option $sel value='$y'>$y</option>";
      }?>
    </select>

    <button type="submit">Filter</button>
  </form>
<div class="table-container">
  <table>
    <tr>
      <th>Month</th>
      <th>Year</th>
      <th>Gross Salary</th>
      <th>Deductions</th>
      <th>Net Pay</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
    <?php
    if($payrolls->num_rows == 0){
      echo "<tr><td colspan='7'>No payroll records available.</td></tr>";
    } else {
      while($row = $payrolls->fetch_assoc()){
        $popupData = htmlspecialchars(json_encode([
          "absent" => $row['leave_deduction'],
          "deduction" => $row['total_deduction'],
          "net_pay" => $row['net_pay'],
          "gross" => $row['gross_salary']
        ]), ENT_QUOTES, 'UTF-8');

        echo "<tr>
          <td>{$row['month']}</td>
          <td>{$row['year']}</td>
          <td>₹ {$row['gross_salary']}</td>
          <td>₹ {$row['total_deduction']} 
              <button onclick='showPopup(JSON.parse(this.dataset.data))' data-data='{$popupData}'>View</button>
          </td>
          <td><b>₹ {$row['net_pay']}</b></td>
          <td><span class='status {$row['status']}'>{$row['status']}</span></td>
          <td>";
          if ($row['status']=='Verified'){
            echo "<form method='post' action='payslip.php' target='_blank'>
                    <input type='hidden' name='payroll_id' value='{$row['id']}'>
                    <button type='submit'>Download Payslip</button>
                  </form>";
          } else {
            echo "<em>Pending</em>";
          }
        echo "</td></tr>";
      }
    }
    ?>
  </table>
  </div>
</div>

<!-- POPUP -->
<div class="popup" id="popup">
  <div class="popup-content">
    <h3>Deduction Summary</h3>
    <table id="popup-table"></table>
    <button class="close-btn" onclick="closePopup()">Close</button>
  </div>
  </main>
</div>
<footer><p>2025 WorkFusion. All rights reserved.</p></footer>
<script>
function showPopup(data){
  let html = `
    <tr><td><b>Gross Salary:</b></td><td>₹ ${data.gross}</td></tr>
    <tr><td><b>Total Deductions:</b></td><td>₹ ${data.deduction}</td></tr>
    <tr><td><b>Final Pay:</b></td><td>₹ ${data.net_pay}</td></tr>`;
  document.getElementById('popup-table').innerHTML = html;
  document.getElementById('popup').style.display = 'flex';
}
function closePopup(){
  document.getElementById('popup').style.display = 'none';
}
</script>
<script>
// Hamburger Toggle
const hamburger = document.getElementById("hamburger");
const sidebar = document.querySelector(".sidebar");
const overlay = document.getElementById("overlay");

hamburger.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
});

// Close sidebar when clicking overlay
overlay.addEventListener("click", () => {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
});

// Popup functions (unchanged)
function showPopup(data){
  let html = `
    <tr><td><b>Gross Salary:</b></td><td>₹ ${data.gross}</td></tr>
    <tr><td><b>Total Deductions:</b></td><td>₹ ${data.deduction}</td></tr>
    <tr><td><b>Final Pay:</b></td><td>₹ ${data.net_pay}</td></tr>`;
  document.getElementById('popup-table').innerHTML = html;
  document.getElementById('popup').style.display = 'flex';
}
function closePopup(){
  document.getElementById('popup').style.display = 'none';
}
</script>

</body>
</html>
