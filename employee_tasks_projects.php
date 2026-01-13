<?php 
session_start();
include "Connect.php";

$emp_id = $_SESSION['emp_id'];
$full_name = $_SESSION['full_name'] ?? 'Employee';

// ===================== FETCH DAILY TASKS =====================
$tasks_stmt = $conn->prepare("
    SELECT task_id, task_name, task_description, task_date, status, submission_file, submission_time, verified_time
    FROM daily_tasks 
    WHERE assigned_to = ? 
    ORDER BY task_date DESC
");
$tasks_stmt->bind_param("i", $emp_id);
$tasks_stmt->execute();
$tasks = $tasks_stmt->get_result();

// ===================== HANDLE DAILY TASK FILE UPLOAD =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_task_id'])) {
    $task_id = $_POST['upload_task_id'];

    // ===== Check 11 PM same-day limit =====
    $task_query = $conn->prepare("SELECT task_date FROM daily_tasks WHERE task_id=? AND assigned_to=?");
    $task_query->bind_param("ii", $task_id, $emp_id);
    $task_query->execute();
    $task_res = $task_query->get_result()->fetch_assoc();
    $task_query->close();

    if ($task_res) {
        $deadline = strtotime($task_res['task_date'] . ' 23:00:00');
        if (time() > $deadline) {
            echo "<script>alert('❌ Task submission closed! You can only submit before 11 PM on the same day.'); window.location='employee_tasks_projects.php';</script>";
            exit;
        }
    }

    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === 0) {
        $filename = time() . "_" . basename($_FILES['submission_file']['name']);
        $target = "uploads/" . $filename;
        move_uploaded_file($_FILES['submission_file']['tmp_name'], $target);

        $stmt = $conn->prepare("
            UPDATE daily_tasks
            SET submission_file=?, submission_time=NOW(), status='Submitted'
            WHERE task_id=? AND assigned_to=?
        ");
        $stmt->bind_param("sii", $filename, $task_id, $emp_id);
        $stmt->execute();
        $stmt->close();
        header("Location: employee_tasks_projects.php");
        exit;
    }
}

// ===================== HANDLE PROJECT FILE UPLOAD =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_project_id'])) {
    $project_id = $_POST['upload_project_id'];

    // ===== Check project end date =====
    $proj_query = $conn->prepare("SELECT end_date FROM projects WHERE project_id=? AND assigned_to=?");
    $proj_query->bind_param("ii", $project_id, $emp_id);
    $proj_query->execute();
    $proj_res = $proj_query->get_result()->fetch_assoc();
    $proj_query->close();

    if ($proj_res) {
        $deadline = strtotime($proj_res['end_date'] . ' 23:59:59');
        if (time() > $deadline) {
            echo "<script>alert('❌ Project submission closed! The end date has passed.'); window.location='employee_tasks_projects.php';</script>";
            exit;
        }
    }

    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === 0) {
        $filename = time() . "_" . basename($_FILES['project_file']['name']);
        $target = "uploads/" . $filename;
        move_uploaded_file($_FILES['project_file']['tmp_name'], $target);

        $stmt = $conn->prepare("
            UPDATE projects
            SET submission_file=?, submission_time=NOW(), status='Submitted'
            WHERE project_id=? AND assigned_to=?
        ");
        $stmt->bind_param("sii", $filename, $project_id, $emp_id);
        $stmt->execute();
        $stmt->close();
        header("Location: employee_tasks_projects.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Employee Portal - Tasks & Projects</title>
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
.container{padding:20px;max-width:1200px;margin:auto;}
.card{background:white;border-radius:16px;border:1px solid #dde3ed;padding:15px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
/* Table styling */
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


 td{
    padding: 5px 15px;
    text-align:center;
}
th{
    background-color: #002147;
    color:white;
    font-weight:600;
    font-size:14px;
    text-transform:uppercase;
    padding: 12px 15px;
    text-align:center;
}
td{
    font-size: 14px;
    color:#555;
     padding: 10px 10px;
}

/* Row styling */
tr:nth-child(even){
    background-color:#f9f9f9;
}
tr:hover{
    background-color:#eaeff5;
}


.status-Pending{color:orange;font-weight:bold;}
.status-Submitted{color:blue;font-weight:bold;}
.status-Completed{color:green;font-weight:bold;}
button{padding:6px 12px;border:none;border-radius:4px;background:#002147;color:white;cursor:pointer;}
button:hover{background:#0055a5;}
footer{position:relative;background:#002147;color:white;text-align:center;padding:5px;}
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
        <!-- DAILY TASKS -->
        <div class="card">
            <div class="table-container">
          <h2>📝 Daily Tasks</h2>
        



          <table>
            <thead>
              <tr><th>Task</th><th>Description</th><th>Date</th><th>Status</th><th>Upload</th></tr>
            </thead>
            <tbody>
              <?php if($tasks->num_rows>0): while($row=$tasks->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['task_name']); ?></td>
                <td><?= htmlspecialchars($row['task_description']); ?></td>
                <td><?= $row['task_date']; ?></td>
                <td class="status-<?= $row['status']; ?>"><?= $row['status']; ?></td>
                <td>
                  <?php if($row['status']=='Pending'): ?>
  <form method="POST" enctype="multipart/form-data">
    <input type="file" name="submission_file" required>
    <input type="hidden" name="upload_task_id" value="<?= $row['task_id']; ?>">
    <button type="submit">Upload</button>
  </form>
<?php elseif($row['status']=='Submitted'): ?>
  <?php if($row['submission_file']): ?>
    <a href="uploads/<?= $row['submission_file']; ?>" target="_blank">View</a> Submitted
  <?php else: ?>Submitted<?php endif; ?>
<?php elseif($row['status']=='Completed'): ?>
  <?php if($row['submission_file']): ?>
    <a href="uploads/<?= $row['submission_file']; ?>" target="_blank">View</a> Verified
  <?php else: ?>Verified<?php endif; ?>
<?php endif; ?>

                </td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="5">No tasks assigned.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
</div>
        <!-- PROJECTS -->
        <div class="card">
            <div class="table-container">
          <h2>📁 Projects</h2>
          <table>
            <thead>
              <tr><th>Project</th><th>Description</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Upload</th></tr>
            </thead>
            <tbody>
              <?php
              $projects_stmt=$conn->prepare("SELECT project_id, project_name, project_description, start_date, end_date, status, submission_file FROM projects WHERE assigned_to=? ORDER BY start_date DESC");
              $projects_stmt->bind_param("i",$emp_id);
              $projects_stmt->execute();
              $projects=$projects_stmt->get_result();
              if($projects->num_rows>0):
                  while($row=$projects->fetch_assoc()):
              ?>
              <tr>
                <td><?= htmlspecialchars($row['project_name']); ?></td>
                <td><?= htmlspecialchars($row['project_description']); ?></td>
                <td><?= $row['start_date']; ?></td>
                <td><?= $row['end_date']; ?></td>
                <td class="status-<?= $row['status']; ?>"><?= $row['status']; ?></td>
                <td>
                <?php if($row['status']=='Pending'): ?>
            <form method="POST" enctype="multipart/form-data">
              <input type="file" name="project_file" required>
              <input type="hidden" name="upload_project_id" value="<?= $row['project_id']; ?>">
              <button type="submit">Upload</button>
            </form>
          <?php elseif($row['status']=='Submitted'): ?>
            <?php if(!empty($row['submission_file'])): ?>
              <a href="uploads/<?= $row['submission_file']; ?>" target="_blank">View</a> Submitted
            <?php else: ?>Submitted<?php endif; ?>
          <?php elseif($row['status']=='Completed'): ?>
            <?php if(!empty($row['submission_file'])): ?>
              <a href="uploads/<?= $row['submission_file']; ?>" target="_blank">View</a> Verified
            <?php else: ?>Verified<?php endif; ?>
          <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="6">No projects assigned.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
              </div>
      </div>
    </div>
  </main>
</div>

<footer><p>2025 WorkFusion. All rights reserved.</p></footer>

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
</body>
</html>
