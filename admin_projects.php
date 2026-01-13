<?php
session_start();
include "Connect.php"; // DB connection

// Ensure session variables exist
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_name'] = 'Admin';
    $_SESSION['admin_email'] = 'admin@example.com';
}

// Fetch employees
$emp_res = $conn->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$employees = $emp_res->fetch_all(MYSQLI_ASSOC);

// Fetch tasks
$tasks_stmt = $conn->prepare("
    SELECT dt.task_id, dt.task_name, dt.task_description, dt.task_date, dt.status, dt.submission_file, u.full_name AS employee_name
    FROM daily_tasks dt
    JOIN users u ON dt.assigned_to = u.id
    ORDER BY dt.task_date DESC
");
$tasks_stmt->execute();
$tasks = $tasks_stmt->get_result();

// Fetch projects
$projects_stmt = $conn->prepare("
    SELECT p.project_id, p.project_name, p.project_description, p.start_date, p.end_date, p.status, p.submission_file, u.full_name AS employee_name
    FROM projects p
    JOIN users u ON p.assigned_to = u.id
    ORDER BY p.end_date ASC
");
$projects_stmt->execute();
$projects = $projects_stmt->get_result();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assign new project
    if (isset($_POST['assign_project'])) {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $emp_id = $_POST['employee_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $stmt = $conn->prepare("INSERT INTO projects (project_name, project_description, start_date, end_date, assigned_to, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("ssssi", $title, $desc, $start_date, $end_date, $emp_id);
        $stmt->execute();
        $stmt->close();

       
    // ===== Notify Employee =====
    $msg_emp = "New Project Assigned: $title (Project ID: $project_id)";
    $stmt2 = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'employee')");
    $stmt2->bind_param("is", $emp_id, $msg_emp);
    $stmt2->execute();
    $stmt2->close();

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
} 


    // Assign new task
    if (isset($_POST['assign_task'])) {
        $title = $_POST['title'];
        $desc  = $_POST['description'];
        $emp_id = $_POST['employee_id'];
        $stmt = $conn->prepare("INSERT INTO daily_tasks (task_name, task_description, task_date, assigned_to, status) VALUES (?, ?, CURDATE(), ?, 'Pending')");
        $stmt->bind_param("ssi", $title, $desc, $emp_id);
        $stmt->execute();
        $stmt->close();
            // ===== Notify Employee =====
    $msg_emp = "New Task Assigned: $title (Task ID: $task_id)";
    $stmt2 = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'employee')");
    $stmt2->bind_param("is", $emp_id, $msg_emp);
    $stmt2->execute();
    $stmt2->close();

    // Redirect back
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

    // Verify task
    if (isset($_POST['verify_task'])) {
        $task_id = $_POST['task_id'];
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("UPDATE daily_tasks SET status='Completed', verified_by=?, verified_time=NOW() WHERE task_id=?");
        $stmt->bind_param("ii", $admin_id, $task_id);
        $stmt->execute();
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    // Verify project
    if (isset($_POST['verify_project'])) {
        $project_id = $_POST['project_id'];
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("UPDATE projects SET status='Completed', verified_by=?, verified_time=NOW() WHERE project_id=?");
        $stmt->bind_param("ii", $admin_id, $project_id);
        $stmt->execute();
        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
    
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
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
/* ===== Actions ===== */
.actions {
    display:flex;
    gap:20px;
    margin:25px 0;
}
.actions button {
    flex:1;
    padding:15px;
    font-size:16px;
    font-weight:600;
    border:none;
    border-radius:10px;
    cursor:pointer;
    color:white;
    background: #002147;
    transition:0.3s;
}
.actions button:hover { background:#1e40af; transform:translateY(-2px); }

/* ===== Dashboard Summary (Smaller Version) ===== */
.dashboard {
  display: flex;
  gap: 16px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.card-summary {
  flex: 1;
  min-width: 90px;
  background: #5a9ce9;
  color: white;
  border-radius: 10px;
  padding: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.card-summary h3 {
  margin: 0;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: uppercase;
  opacity: 0.85;
}

.card-summary span {
  display: block;
  font-size: 1.4rem;
  font-weight: 700;
  margin-top: 6px;
}

/* Responsive adjustment for small screens */
@media (max-width: 768px) {
  .card-summary {
    min-width: 80px;
    padding: 10px;
  }
  .card-summary h3 {
    font-size: 0.75rem;
  }
  .card-summary span {
    font-size: 1.2rem;
  }
}


/* ===== Cards ===== */
.card { background:white;   border: 1px solid #dde3ed; border-radius:12px; padding:25px; margin-bottom:25px; box-shadow:0 6px 20px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; }
.card:hover { transform:translateY(-3px); box-shadow:0 10px 25px rgba(0,0,0,0.12); }

  

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

/* ===== Status ===== */
.status { padding:6px 12px; border-radius:15px; font-size:13px; font-weight:600; color:white; display:inline-block; }
.status.pending { background:#facc15; }
.status.completed { background:#16a34a; }
.status.overdue { background:#dc2626; }
a.view-link { color:#2563eb; text-decoration:none; font-weight:500; }
a.view-link:hover { text-decoration:underline; }

/* ===== MODAL ===== */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:25px; border-radius:12px; width:90%; max-width:500px; position:relative; }
.close-btn { position:absolute; top:12px; right:12px; font-size:18px; font-weight:700; cursor:pointer; color:#555; }
input[type="text"], textarea, select, input[type="date"] { width:99%; padding:12px; margin:6px 0 16px; border:1px solid #cbd5e1; border-radius:8px; font-size:15px; }
button.submit-btn { width:100%; padding:12px; border:none; border-radius:8px; background:#2563eb; color:white; font-weight:600; font-size:16px; cursor:pointer; transition:0.3s; }
button.submit-btn:hover { background:#002147; }

/* ===== Responsive ===== */
@media(max-width:768px){
    .dashboard, .actions { flex-direction:column; }
    table, thead, tbody, th, td, tr { display:block; }
    thead { display:none; }
    tr { margin-bottom:15px; background:#fff; border-radius:8px; padding:12px; box-shadow:0 4px 12px rgba(0,0,0,0.05);}
    td { text-align:right; padding:10px; border:none; position:relative; }
    td::before { content:attr(data-label); position:absolute; left:10px; font-weight:bold; text-transform:uppercase; color:#1e293b; }
}
/* ===== SEARCH INPUTS ===== */
#taskSearch, #projectSearch {
    padding: 10px 12px;
    width: 50%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 15px;
    margin-top: 8px;
    margin-bottom: 18px;
    transition: 0.3s;
}

#taskSearch:focus, #projectSearch:focus {
    border-color: #2563eb;
    outline: none;
    box-shadow: 0 0 5px rgba(37, 99, 235, 0.5);
}

/* Label styling */
#taskSearch + label, #projectSearch + label {
    font-weight: 600;
    margin-right: 10px;
    color: #12303f;
}

/* Responsive adjustments */
@media(max-width:768px){
    #taskSearch, #projectSearch {
        width: 100%;
        margin-bottom: 12px;
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

<div class="container">

<!-- ===== ACTION BUTTONS ===== -->
<div class="actions">
    <button id="openTask">Assign New Task</button>
    <button id="openProject">Assign New Project</button>
</div>
<?php
// Fetch dashboard counts
$totalTasksRes = $conn->query("SELECT COUNT(*) AS total FROM daily_tasks");
$totalTasks = $totalTasksRes->fetch_assoc()['total'] ?? 0;

$pendingTasksRes = $conn->query("SELECT COUNT(*) AS pending FROM daily_tasks WHERE status!='Completed'");
$pendingTasks = $pendingTasksRes->fetch_assoc()['pending'] ?? 0;

$completedTasksRes = $conn->query("SELECT COUNT(*) AS completed FROM daily_tasks WHERE status='Completed'");
$completedTasks = $completedTasksRes->fetch_assoc()['completed'] ?? 0;

$totalProjectsRes = $conn->query("SELECT COUNT(*) AS total_projects FROM projects");
$totalProjects = $totalProjectsRes->fetch_assoc()['total_projects'] ?? 0;
?>


<div class="dashboard">
    <div class="card-summary">
        <h3>Total Tasks</h3><span><?php echo $totalTasks; ?></span>
    </div>
    <div class="card-summary">
        <h3>Pending Tasks</h3><span><?php echo $pendingTasks; ?></span>
    </div>
    <div class="card-summary">
        <h3>Completed Tasks</h3><span><?php echo $completedTasks; ?></span>
    </div>
    <div class="card-summary">
        <h3>Total Projects</h3><span><?php echo $totalProjects; ?></span>
    </div>
</div>


<!-- ===== TASK MODAL ===== -->
<div class="modal" id="taskModal">
<div class="modal-content">
<span class="close-btn" id="closeTask">&times;</span>
<h2>Assign New Task</h2>
<br>
<form method="POST">
<input type="hidden" name="assign_task">
<label>Task Title:</label>
<input type="text" name="title" required>
<label>Task Description:</label>
<textarea name="description" rows="3" required></textarea>
<label>Assign To Employee:</label>
<select name="employee_id" required>
    <option value="">Select Employee</option>
    <option value="all">Select All Employees</option> <!-- New Option -->
    <?php foreach($employees as $emp) echo "<option value='{$emp['id']}'>{$emp['full_name']}</option>"; ?>
</select>
<button type="submit" class="submit-btn">Assign Task</button>
</form>
</div>
</div>

<!-- ===== PROJECT MODAL ===== -->
<div class="modal" id="projectModal">
<div class="modal-content">
<span class="close-btn" id="closeProject">&times;</span>
<h2>Assign New Project</h2>
<br>
<form method="POST">
<input type="hidden" name="assign_project">
<label>Project Title:</label>
<input type="text" name="title" required>
<label>Project Description:</label>
<textarea name="description" rows="3" required></textarea>
<label>Start Date:</label>
<input type="date" name="start_date" required>
<label>End Date:</label>
<input type="date" name="end_date" required>
<label>Assign To Employee:</label>
<select name="employee_id" required>
    <option value="">Select Employee</option>
    <option value="all">Select All Employees</option> <!-- New Option -->
    <?php foreach($employees as $emp) echo "<option value='{$emp['id']}'>{$emp['full_name']}</option>"; ?>
</select>

<button type="submit" class="submit-btn">Assign Project</button>
</form>
</div>
</div>

<!-- ===== TASK SEARCH ===== -->
<div style="margin-bottom:15px; display:flex; align-items:center; gap:10px;">
  <label for="taskSearch">Search Tasks:</label>
  <input type="text" id="taskSearch" placeholder="Search by task name, employee, description..." style="padding:8px;width:50%;border-radius:6px;border:1px solid #ccc;">
  <button id="printTasks" style="padding:8px 12px;border:none;border-radius:6px;background: #051c4dff;color:white;cursor:pointer;">Print</button>
</div>




<!-- ===== SUBMITTED TASKS TABLE ===== -->
<div class="card">
<h2>Submitted Tasks</h2>
<br>
<table>
<thead>
<tr>
<th>Task</th>
<th>Description</th>
<th>Employee</th>
<th>Status</th>
<th>Submission</th>
<th>Verify</th>
</tr>
</thead>
<tbody>
<?php if($tasks->num_rows>0): ?>
<?php while($row = $tasks->fetch_assoc()):
$task_deadline = strtotime($row['task_date'] . ' 23:59:59'); // end of the day
$isOverdue = ($row['status'] !== 'Completed' && time() > $task_deadline);

?>
<tr>
<td data-label="Task"><?php echo htmlspecialchars($row['task_name']); ?></td>
<td data-label="Description"><?php echo htmlspecialchars($row['task_description']); ?></td>
<td data-label="Employee"><?php echo htmlspecialchars($row['employee_name']); ?></td>
<td data-label="Status">
<?php if($row['status']==='Completed'): ?>
<span class="status completed">Verified</span>
<?php elseif($isOverdue): ?>
<span class="status overdue">Overdue</span>
<?php else: ?>
<span class="status pending">Pending</span>
<?php endif; ?>
</td>
<td data-label="Submission">
<?php if($row['submission_file']): ?>
<a class="view-link" href="uploads/<?php echo $row['submission_file']; ?>" target="_blank">View</a>
<?php else: ?> - <?php endif; ?>
</td>
<td data-label="Verify">
<?php if($row['status']!=='Completed' && $row['submission_file']): ?>
<form method="POST">
<input type="hidden" name="verify_task" value="1">
<input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
<button type="submit" class="submit-btn">Verify</button>
</form>
<?php elseif($row['status']==='Completed'): ?> Verified <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No tasks found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
<!-- ===== PROJECT SEARCH ===== -->
<div style="margin-bottom:15px; display:flex; align-items:center; gap:10px;">
  <label for="projectSearch">Search Projects:</label>
  <input type="text" id="projectSearch" placeholder="Search by project name, employee, description..." style="padding:8px;width:50%;border-radius:6px;border:1px solid #ccc;">
  <button id="printProjects" style="padding:7px 12px;border:none;border-radius:6px;background: #051c4dff;color:white;cursor:pointer;">Print</button>
</div>

<!-- ===== PROJECTS TABLE ===== -->
<div class="card">
<h2>Assigned Projects</h2>
<br>
<table>
<thead>
<tr>
<th>Project</th>
<th>Description</th>
<th>Employee</th>
<th>Start Date</th>
<th>End Date</th>
<th>Status</th>
<th>Submission</th>
<th>Verify</th>
</tr>
</thead>
<tbody>
<?php if($projects->num_rows>0): ?>
<?php while($proj = $projects->fetch_assoc()):
$project_deadline = strtotime($proj['end_date']);
    $isOverdue = ($proj['status']!=='Completed' && time() > $project_deadline);
    $today = strtotime(date('Y-m-d'));
    $end = strtotime($proj['end_date']);
    $daysLeft = ceil(($project_deadline - time())/86400);
   if($proj['status']=== 'completed') $timelineClass = 'status completed';
     elseif($daysLeft < 0) $timelineClass = 'status overdue';
     elseif($daysLeft <= 3) $timelineClass = 'status pending';
     else $timelineClass = 'status pending';
?>
<tr>
<td data-label="Project"><?php echo htmlspecialchars($proj['project_name']); ?></td>
<td data-label="Description"><?php echo htmlspecialchars($proj['project_description']); ?></td>
<td data-label="Employee"><?php echo htmlspecialchars($proj['employee_name']); ?></td>
<td data-label="Start Date"><?php echo $proj['start_date']; ?></td>
<td data-label="End Date"><?php echo $proj['end_date']; ?></td>
<td data-label="Status">
<?php if($proj['status']==='Completed'): ?>
<span class="status completed">Verified</span>
<?php elseif($isOverdue): ?>
<span class="status overdue">Overdue</span>
<?php else: ?>
<span class="status pending">Pending</span>
<?php endif; ?>
</td>
<td data-label="Submission">
<?php if($proj['submission_file']): ?>
<a class="view-link" href="uploads/<?php echo $proj['submission_file']; ?>" target="_blank">View</a>
<?php else: ?> - <?php endif; ?>
</td>
<td data-label="Verify">
<?php if($proj['status']!=='Completed' && $proj['submission_file']): ?>
<form method="POST">
<input type="hidden" name="verify_project" value="1">
<input type="hidden" name="project_id" value="<?php echo $proj['project_id']; ?>">
<button type="submit" class="submit-btn">Verify</button>
</form>
<?php elseif($proj['status']==='Completed'): ?> Verified <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No projects assigned.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</main>
</div>

<script>
// ===== Modal Logic =====
const taskModal = document.getElementById('taskModal');
const projectModal = document.getElementById('projectModal');

document.getElementById('openTask').onclick = ()=> taskModal.style.display='flex';
document.getElementById('closeTask').onclick = ()=> taskModal.style.display='none';
document.getElementById('openProject').onclick = ()=> projectModal.style.display='flex';
document.getElementById('closeProject').onclick = ()=> projectModal.style.display='none';

// Close modal on outside click
window.onclick = function(e){
    if(e.target==taskModal) taskModal.style.display='none';
    if(e.target==projectModal) projectModal.style.display='none';
}
</script>

  <!-- Footer -->
  <footer>
    
    <p>2025 WorkFusion. All rights reserved.</p>
  </footer>
<?php
// Fetch unseen notifications for admin
$admin_id = $_SESSION['admin_id'];
$notif_res = $conn->prepare("SELECT id, message FROM notifications WHERE user_id=? AND seen=0 ORDER BY created_at DESC");
$notif_res->bind_param("i",$admin_id);
$notif_res->execute();
$notif_res = $notif_res->get_result();
$admin_notifs = [];
while($n = $notif_res->fetch_assoc()){
    $admin_notifs[] = $n;
    $conn->query("UPDATE notifications SET seen=1 WHERE id=".$n['id']);
}
?>

<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;"></div>

<script>
// same toast function
function showToast(msg){
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style = "background:#002147;color:white;padding:12px 18px;margin-top:10px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.2);opacity:0;transition:0.5s;";
    container.appendChild(toast);
    setTimeout(()=>{toast.style.opacity=1;},50);
    setTimeout(()=>{toast.style.opacity=0;setTimeout(()=>container.removeChild(toast),500);},4000);
}

// Display all notifications
<?php foreach($admin_notifs as $n): ?>
showToast("<?= addslashes($n['message']); ?>");
<?php endforeach; ?>
</script>
<script>
// ===== TASK TABLE SEARCH =====
document.getElementById('taskSearch').addEventListener('input', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row=>{
        const task = row.querySelector('td[data-label="Task"]')?.textContent.toLowerCase() || '';
        const desc = row.querySelector('td[data-label="Description"]')?.textContent.toLowerCase() || '';
        const emp  = row.querySelector('td[data-label="Employee"]')?.textContent.toLowerCase() || '';
        if(task.includes(filter) || desc.includes(filter) || emp.includes(filter)){
            row.style.display='';
        } else {
            row.style.display='none';
        }
    });
});

// ===== PROJECT TABLE SEARCH =====
document.getElementById('projectSearch').addEventListener('input', function(){
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row=>{
        const project = row.querySelector('td[data-label="Project"]')?.textContent.toLowerCase() || '';
        const desc = row.querySelector('td[data-label="Description"]')?.textContent.toLowerCase() || '';
        const emp  = row.querySelector('td[data-label="Employee"]')?.textContent.toLowerCase() || '';
        if(project.includes(filter) || desc.includes(filter) || emp.includes(filter)){
            row.style.display='';
        } else {
            row.style.display='none';
        }
    });
});
// ===== PRINT TASKS =====
document.getElementById('printTasks').addEventListener('click', function(){
    const table = document.querySelector('.card:has(h2:contains("Submitted Tasks")) table');
    if(!table) return;
    
    // clone visible rows only
    let clone = table.cloneNode(true);
    clone.querySelectorAll('tbody tr').forEach(tr=>{
        if(tr.style.display === 'none') tr.remove();
    });

    // Open print window
    let w = window.open('', '', 'height=600,width=900');
    w.document.write('<html><head><title>Print Tasks</title>');
    w.document.write('<style>table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;text-align:left;} th{background:#002147;color:white;}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>Tasks Report</h2>');
    w.document.write(clone.outerHTML);
    w.document.write('</body></html>');
    w.document.close();
    w.print();
});
// ===== PRINT TASKS =====
document.getElementById('printTasks').addEventListener('click', function(){
    const card = document.querySelectorAll('.card');
    let table = null;
    card.forEach(c => {
        if(c.querySelector('h2')?.textContent.includes("Submitted Tasks")){
            table = c.querySelector('table');
        }
    });
    if(!table) return;

    // clone visible rows
    let clone = table.cloneNode(true);
    clone.querySelectorAll('tbody tr').forEach(tr=>{
        if(tr.style.display === 'none') tr.remove();
    });

    // Open print window
    let w = window.open('', '', 'height=600,width=900');
    w.document.write('<html><head><title>Tasks Report</title>');
    w.document.write('<style>table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;text-align:left;} th{background:#002147;color:white;}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>Tasks Report</h2>');
    w.document.write(clone.outerHTML);
    w.document.write('</body></html>');
    w.document.close();
    w.print();
});

// ===== PRINT PROJECTS =====
document.getElementById('printProjects').addEventListener('click', function(){
    const card = document.querySelectorAll('.card');
    let table = null;
    card.forEach(c => {
        if(c.querySelector('h2')?.textContent.includes("Assigned Projects")){
            table = c.querySelector('table');
        }
    });
    if(!table) return;

    // clone visible rows
    let clone = table.cloneNode(true);
    clone.querySelectorAll('tbody tr').forEach(tr=>{
        if(tr.style.display === 'none') tr.remove();
    });

    // Open print window
    let w = window.open('', '', 'height=600,width=900');
    w.document.write('<html><head><title>Projects Report</title>');
    w.document.write('<style>table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ccc;padding:8px;text-align:left;} th{background:#002147;color:white;}</style>');
    w.document.write('</head><body>');
    w.document.write('<h2>Projects Report</h2>');
    w.document.write(clone.outerHTML);
    w.document.write('</body></html>');
    w.document.close();
    w.print();
});

</script>


</body>
</html>
