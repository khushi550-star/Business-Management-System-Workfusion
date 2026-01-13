<?php
session_start();
require_once "Connect.php";

if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit;
}

$emp_id = $_SESSION['emp_id'];

// Fetch employee details
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Profile | WorkFusion</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
  --primary: #0047ab;
  --primary-dark: #002b73;
  --bg: #f4f6fb;
  --card-bg: #fff;
  --text-muted: #555;
  --border: #d8dee6;
  --success: #2b8a3e;
  --shadow: rgba(0,0,0,0.08);
}
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

.user-info .emp-id {
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



/* Main Content */
.main { flex:1; padding:30px; overflow-y:auto; }

/* Card */
.card {
  background: var(--card-bg); border-radius:16px;   border: 1px solid #dde3ed; padding:28px; box-shadow:0 6px 20px var(--shadow); margin-bottom:25px;
  transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px var(--shadow); }

h3 {
  color: var(--primary-dark); border-left:5px solid var(--primary); padding-left:12px; margin-top:0 0 18px 0;
  font-weight:600;
}

/* Grid Layout */
.grid-2 { display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:18px; }
label { display:block; font-weight:600; color:var(--text-muted); margin-bottom:6px; }

input, select, textarea {
  width:100%; padding:12px; border-radius:10px; border:1px solid var(--border);
  font-size:14px; outline:none; transition:0.3s;
}
input:focus, select:focus, textarea:focus {
  border-color: var(--primary); box-shadow:0 0 0 3px rgba(0,71,171,0.15);
}
textarea { min-height:70px; resize:vertical; }

/* Buttons */
.btn {
  background: #002147; color:#fff; padding:12px 20px; border:none; border-radius:10px;
  cursor:pointer; font-weight:600; transition:0.3s; margin-top:10px;
}
.btn:hover { background: blue; }
.btn.secondary { background:#fff; color: var(--primary); border:1px solid var(--primary); }
.center { text-align:center; }

/* Profile photo */
.photo-box {
  width:130px; height:130px; border-radius:50%;   border: 1px solid #dde3ed; overflow:hidden; background:#f0f0f0;
  display:flex; justify-content:center; align-items:center; margin:0 auto 15px auto;
  box-shadow: 0 0 0 3px #fff, 0 0 10px rgba(0,0,0,0.15);
}
.photo-box img { width:100%; height:100%; object-fit:cover; }
  footer { background: #002147; color: white; text-align: center; padding: 5px; }
 .sidebar-separator {
  border: 0;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.3); /* white-ish line */
  margin: 16px 0; /* spacing between avatar and menu */
}

/* Responsive */
@media(max-width:768px){
  .app{flex-direction:column;}
  .sidebar{width:100%; flex-direction:row; overflow-x:auto; padding:15px;}
  .sidebar nav a{display:inline-block; margin-right:10px;}
}
/* ===========================
   📱 MOBILE RESPONSIVE DESIGN + SIDEBAR TOGGLE
   =========================== */
@media (max-width: 768px) {
  /* --- Layout stack --- */
  .app {
    flex-direction: column;
  }

  /* --- Sidebar hidden by default --- */
  .sidebar {
    position: fixed;
    top: 0;
    left: -260px; /* Hidden */
    height: 100%;
    width: 240px;
    background: #002147;
    color: white;
    transition: left 0.3s ease;
    z-index: 9999;
    padding-top: 70px; /* space for header */
  }

  /* --- When active, slide in --- */
  .sidebar.active {
    left: 0;
  }

  /* --- Overlay background when sidebar is open --- */
  .overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    z-index: 9998;
  }

  .overlay.active {
    display: block;
  }

  /* --- Navbar adjustments --- */
  .navbar {
    position: relative;
    justify-content: space-between;
  }

  .hamburger {
    display: flex;
    z-index: 10000; /* above sidebar */
  }

  .profile-card {
    flex-direction: column;
    align-items: center;
    text-align: center;
    justify-content: center;
    width: 90%;
    margin: 0 auto 20px auto;
  }

  .profile-card .photo {
    margin-bottom: 12px;
  }

  /* --- Main content padding --- */
  .main {
    padding: 15px;
  }

  /* --- Table scrollable --- */
  table {
    display: block;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
  }

  th, td {
    font-size: 13px;
    padding: 10px;
  }

  footer {
    font-size: 13px;
    padding: 8px;
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
      <div class="avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
      <div class="user-info">
        <div class="name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        <div class="emp-id">Employee ID: <?php echo $_SESSION['emp_id']; ?></div>
      </div>
    </div>
    
 <hr class="sidebar-separator">
  
     <!-- Sidebar Menu -->
 <?php
// Get current filename
$current_page = basename($_SERVER['PHP_SELF']);
?>
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
  <!-- put this right after </aside> and before <main> -->
<div class="overlay" id="overlay" aria-hidden="true"></div>

  <main class="main">
    <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">

      <div class="card center">
        <div class="photo-box" id="photoPreview">
         <?php
$photoPath = "uploads/profile/" . ($_SESSION['photo'] ?? '');
if(!empty($_SESSION['photo']) && file_exists($photoPath)) {
    echo "<img src='$photoPath' alt='Profile'>";
} else {
    echo "<span>No Photo</span>";
}
?>

        </div>
        <input type="file" name="photo" id="photoInput" accept="image/*" style="display:none;">
        <button type="button" id="uploadBtn" class="btn secondary">Upload Photo</button>
      </div>

      <div class="card">
        <h3>👤 Personal Information</h3>
          <br>
        <div class="grid-2">
          <div><label>Full Name</label><input type="text" name="full_name" value="<?php echo h($user['full_name']); ?>"></div>
          <div><label>Gender</label>
            <select name="gender">
              <option value="">Select</option>
              <option value="Male" <?php if($user['gender']==='Male') echo 'selected'; ?>>Male</option>
              <option value="Female" <?php if($user['gender']==='Female') echo 'selected'; ?>>Female</option>
              <option value="Other" <?php if($user['gender']==='Other') echo 'selected'; ?>>Other</option>
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div><label>DOB</label><input type="date" name="dob" value="<?php echo h($user['dob']); ?>"></div>
          <div><label>Marital Status</label><input type="text" name="marital_status" value="<?php echo h($user['marital_status']); ?>"></div>
        </div>
      </div>

      <div class="card">
        <h3>📞 Contact Information</h3>
        <br>
        <div class="grid-2">
          <div><label>Email</label><input type="email" name="email" value="<?php echo h($user['mobile_email']); ?>" readonly></div>
          <div><label>Mobile</label><input type="text" name="phone" value="<?php echo h($user['phone']); ?>"></div>
        </div>
        <div><label>Current Address</label><textarea name="current_address"><?php echo h($user['current_address']); ?></textarea></div>
        <div><label>Permanent Address</label><textarea name="permanent_address"><?php echo h($user['permanent_address']); ?></textarea></div>
      </div>

      <div class="card">
        <h3>💼 Job Details</h3>
                <br>

        <div class="grid-2">
          <div><label>Department</label><input type="text" name="department" value="<?php echo h($user['department']); ?>"></div>
          <div><label>Designation</label><input type="text" name="designation" value="<?php echo h($user['designation']); ?>"></div>
        </div>
        <div><label>Work Location</label><input type="text" name="work_location" value="<?php echo h($user['work_location']); ?>"></div>
      </div>

      <div class="card">
        <h3>🏦 Bank Details</h3>
                <br>

        <div class="grid-2">
          <div><label>Bank Name</label><input type="text" name="bank_name" value="<?php echo h($user['bank_name']); ?>"></div>
          <div><label>Account No.</label><input type="text" name="account_no" value="<?php echo h($user['account_no']); ?>"></div>
        </div>
        <div class="grid-2">
          <div><label>IFSC</label><input type="text" name="ifsc" value="<?php echo h($user['ifsc']); ?>"></div>
          <div><label>UPI ID</label><input type="text" name="upi_id" value="<?php echo h($user['upi_id']); ?>"></div>
        </div>
      </div>

      <div class="card">
        <h3>🚨 Emergency Details</h3>
        <br>
        <div class="grid-2">
          <div><label>Contact Person</label><input type="text" name="emergency_person" value="<?php echo h($user['emergency_person']); ?>"></div>
          <div><label>Relationship</label><input type="text" name="emergency_relation" value="<?php echo h($user['emergency_relation']); ?>"></div>
        </div>
        <div><label>Emergency Contact</label><input type="text" name="emergency_contact" value="<?php echo h($user['emergency_contact']); ?>"></div>
      </div>

      <div class="card">
        <h3>📚 Education & Experience</h3>
               <br>
        <div class="grid-2">
          <div><label>Qualification</label><input type="text" name="qualification" value="<?php echo h($user['qualification']); ?>"></div>
          <div><label>Experience</label><input type="text" name="experience" value="<?php echo h($user['experience']); ?>"></div>
        </div>
        <div><label>Previous Company</label><input type="text" name="previous_company" value="<?php echo h($user['previous_company']); ?>"></div>
      </div>

      <center><button type="submit" class="btn">Save Changes</button></center>
    </form>
  </main>
</div>



<script>
document.getElementById("uploadBtn").onclick = () =>
  document.getElementById("photoInput").click();

document.getElementById("photoInput").onchange = (e)=>{
  const file = e.target.files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = ev => {
      document.getElementById("photoPreview").innerHTML = `<img src="${ev.target.result}">`;
    };
    reader.readAsDataURL(file);
  }
};
const hamburger = document.getElementById("hamburger");
const sidebar = document.querySelector(".sidebar");
const overlay = document.getElementById("overlay");

// Toggle sidebar and overlay on mobile
hamburger.addEventListener("click", () => {
  sidebar.classList.toggle("active");
  overlay.classList.toggle("active");
});

// Close sidebar when overlay clicked
overlay.addEventListener("click", () => {
  sidebar.classList.remove("active");
  overlay.classList.remove("active");
});
</script>
<footer> 2025 WorkFusion. All Rights Reserved.</footer>
</body>
</html>
