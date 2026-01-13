<?php
// admin_notices.php
session_start();
include "Connect.php"; // must set $conn (mysqli)

// Optional admin check
// if(!isset($_SESSION['is_admin'])) { header('Location: login.php'); exit; }

$uploadDir = __DIR__ . '/uploads/notices/';
$webUploadDir = 'uploads/notices/';

// ensure upload dir exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// helper: sanitize filename
function safe_name($name){ return preg_replace('/[^a-z0-9\-_\.]/i','_', $name); }

$flash = '';

// ===== Handle POST (Add or Update) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'add'; // 'add' or 'edit'
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $audience_type = $_POST['audience_type'] ?? 'all';
$recipients = '';

if ($audience_type === 'single') {
    $recipients = $_POST['single_emp_id'] ?? '';
} elseif ($audience_type === 'multiple') {
    $recipients = isset($_POST['multiple_emp_ids']) ? implode(',', $_POST['multiple_emp_ids']) : '';
}

   $posted_by_fullname = $_SESSION['admin_name'] ?? 'Admin';
$posted_by = 'Admin';
$posted_by_email = $_SESSION['admin_email'] ?? null;
$posted_by_role = 'admin';
$posted_by_id = $_SESSION['admin_id'] ?? 0;


    $attachment_path = null;
    $attachment_type = null;

    if ($title === '' || $description === '') {
        $flash = "Title and description are required.";
    } else {
        // file upload
        if (!empty($_FILES['attachment']['name'])) {
            $f = $_FILES['attachment'];
            $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $flash = "Invalid file type. Allowed: " . implode(', ', $allowed);
            } elseif ($f['size'] > 5 * 1024 * 1024) {
                $flash = "File too large (max 5MB).";
            } else {
                $newname = time() . '_' . safe_name($f['name']);
                $dest = $uploadDir . $newname;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $flash = "File upload failed.";
                } else {
                    $attachment_path = $webUploadDir . $newname;
                    $attachment_type = mime_content_type($dest);
                }
            }
        }

        if ($flash === '') {
            if ($mode === 'add') {
               $stmt = $conn->prepare("INSERT INTO notices (title, description, attachment, attachment_type, posted_by, posted_by_fullname, posted_by_email, posted_by_role, posted_by_id, is_published, audience_type, recipients) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssisss", $title, $description, $attachment_path, $attachment_type, $posted_by, $posted_by_fullname, $posted_by_email, $posted_by_role, $posted_by_id, $is_published, $audience_type, $recipients);


                if ($stmt->execute()) { $flash = "Notice added successfully."; } else { $flash = "DB error: " . $conn->error; if ($attachment_path) @unlink(__DIR__ . '/' . $attachment_path); }
                $stmt->close();
            } elseif ($mode === 'edit') {
                $id = intval($_POST['id'] ?? 0);
                
                // fetch existing attachment
                $oldAttach = null;
                $res = $conn->prepare("SELECT attachment FROM notices WHERE id=?");
                $res->bind_param("i",$id); $res->execute();
                $old = $res->get_result()->fetch_assoc(); $res->close();
                if ($old) $oldAttach = $old['attachment'];

                if ($attachment_path === null) {
                    // keep old
                    $attachment_path = $oldAttach;
                    $res2 = $conn->prepare("SELECT attachment_type FROM notices WHERE id=?");
                    $res2->bind_param("i",$id); $res2->execute();
                    $row2 = $res2->get_result()->fetch_assoc();
                    $attachment_type = $row2['attachment_type'] ?? null;
                    $res2->close();
                } else {
                    // delete old file
                    if ($oldAttach && file_exists(__DIR__ . '/' . $oldAttach)) @unlink(__DIR__ . '/' . $oldAttach);
                }

                $stmt = $conn->prepare("UPDATE notices SET title=?, description=?, attachment=?, attachment_type=?, posted_by=?, posted_by_fullname=?, posted_by_email=?, is_published=? WHERE id=?");
                $stmt->bind_param("sssssssis", $title, $description, $attachment_path, $attachment_type, $posted_by, $posted_by_fullname, $posted_by_email, $is_published, $id);
                if ($stmt->execute()) { $flash = "Notice updated successfully."; } else { $flash = "DB error: " . $conn->error; }
                $stmt->close();
            }
        }
    }

    $_SESSION['flash'] = $flash;
    header("Location: admin_notices.php");
    exit;
    
}

// ===== Handle GET actions: delete / toggle / edit mode =====
$action = $_GET['action'] ?? '';
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->prepare("SELECT attachment FROM notices WHERE id=?");
    $res->bind_param("i", $id); $res->execute();
    $r = $res->get_result()->fetch_assoc(); $res->close();
    if ($r && $r['attachment'] && file_exists(__DIR__ . '/' . $r['attachment'])) @unlink(__DIR__ . '/' . $r['attachment']);
    $del = $conn->prepare("DELETE FROM notices WHERE id=?"); $del->bind_param("i",$id); $del->execute(); $del->close();
    $_SESSION['flash'] = "Notice deleted."; header("Location: admin_notices.php"); exit;
}
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE notices SET is_published = 1 - is_published WHERE id = ?"); $stmt->bind_param("i",$id); $stmt->execute(); $stmt->close();
    $_SESSION['flash'] = "Publish status toggled."; header("Location: admin_notices.php"); exit;
}

// If edit mode is requested, fetch the notice to prefill form
$edit_notice = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->prepare("SELECT * FROM notices WHERE id=?"); $res->bind_param("i",$id); $res->execute(); $edit_notice = $res->get_result()->fetch_assoc(); $res->close();
}

// fetch all notices for list
$list = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin — Manage Notices</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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
    /* Profile card */
    .profile-card{display:flex;gap:18px;background:#eef7fb;border-radius:6px;padding:16px 18px;margin-bottom:18px;align-items:center}

        .sidebar-separator {
  border: 0;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.3); /* white-ish line */
  margin: 16px 0; /* spacing between avatar and menu */
}

h1 {
  color: #002147;
  font-size: 30px;
  font-weight: 800;
  margin-bottom: 24px;
  
  padding-left: 14px;
  letter-spacing: 0.5px;
}

/* ===== Grid Layout ===== */
.grid {
  display: flex;
  gap: 48px;
  flex-wrap: wrap;
  justify-content: center; /* center columns horizontally */
}

/* ===== Columns ===== */
.col {
  background: #eef6f9;

  padding: 16px;
  border-radius: 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
  border: 1px solid #dde3ed;
}

.col.form { 
  width: 720px; 
  margin: 0 auto;  /* horizontally center if alone */
}

.col.list { 
  flex: 1; 
  min-width: 300px; 
}

/* ===== Form ===== */
label {
  display: block;
  margin-top: 12px;
  font-weight: 600;
  color: #002147;
}

input[type=text],
textarea,
input[type=file] {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cfd8eb;
  border-radius: 8px;
  font-size: 15px;
  transition: all 0.2s;
}

input[type=text]:focus,
textarea:focus,
input[type=file]:focus {
  border-color: #0066cc;
  box-shadow: 0 0 6px rgba(0,102,204,0.2);
  outline: none;
}

/* ===== Buttons ===== */
.btn {
  display: inline-block;
  padding: 5px 14px;
  border-radius: 8px;
  text-decoration: none;
  color: #fff;
  background: #0a2b4dff;
  margin-right: 6px;
  font-weight: 600;
  transition: all 0.2s;
}

.btn:hover {
  background: #004a99;
  transform: translateY(-2px);
}

.btn.warn { background: #ff9500; }
.btn.warn:hover { background: #cc7500; }
.btn.danger { background: #e53935; }
.btn.danger:hover { background: #b71c1c; }

/* ===== Center entire page content vertically if needed ===== */


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
tbody tr {
  background: #f9fbff;
  border-radius: 30px;
  transition: all 0.2s;
}
td .btn{  display: inline-block;
  padding: 1px 14px;
  border-radius: 8px;
  text-decoration: none;
  color: #fff;
  background: #0a2b4dff;
  margin-right: 6px;
  font-weight: 600;
  transition: all 0.2s;

}


tbody tr:hover {
  background: #e6f0ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,102,204,0.1);
}

.attachment-link {
  font-size: 13px;
  color: #07223dff;
  text-decoration: underline;
}

.title-small {
  font-size: 16px;
  font-weight: 700;
  color: #002147;
  margin-bottom: 8px;
}

.small {
  font-size: 13px;
  color: #555;
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
                <h3>Email</h3>
                <div><?php echo $_SESSION['admin_email']; ?></div>
              </div>
            </div>
          </div>
        </div>
<div class="wrap">
  <h1><center>Announcements & Notices</center></h1>

  <div style="text-align:right;margin-bottom:20px;">
    <button id="showFormBtn" class="btn">+ Publish / Announce Notice</button>
  </div>

  <div class="grid">
    <!-- Form column -->
    <div class="col form" id="noticeForm" style="display:none;">

      <?php if ($edit_notice): ?><h3>Edit Notice</h3><?php else: ?><h3>Add New Notice</h3><?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="<?= $edit_notice ? 'edit' : 'add' ?>">
        <?php if($edit_notice): ?><input type="hidden" name="id" value="<?= intval($edit_notice['id']) ?>"><?php endif; ?>

        <label>Title</label>
                <br>

        <input type="text" name="title" value="<?= htmlspecialchars($edit_notice['title'] ?? '') ?>" required>

        <label>Description</label>
        <br>
        <textarea name="description" rows="6" required><?= htmlspecialchars($edit_notice['description'] ?? '') ?></textarea>
        <!-- Audience Selection -->
<label>Send Notice To:</label>
<br>
<select name="audience_type" id="audience_type" onchange="toggleRecipients()" required>
  <option value="all" <?= isset($edit_notice) && $edit_notice['audience_type']=='all' ? 'selected' : '' ?>>All Employees</option>
  <option value="single" <?= isset($edit_notice) && $edit_notice['audience_type']=='single' ? 'selected' : '' ?>>Particular Employee</option>
  <option value="multiple" <?= isset($edit_notice) && $edit_notice['audience_type']=='multiple' ? 'selected' : '' ?>>Selected Employees</option>
</select>

<!-- Single Employee -->
<div id="singleSelect" style="display:none;margin-top:10px;">
  <label>Select Employee:</label>
  <select name="single_emp_id">
    <option value="">-- Choose --</option>
    <?php
    $emps = $conn->query("SELECT id, full_name FROM users ORDER BY full_name");
    while($e = $emps->fetch_assoc()){
        $sel = isset($edit_notice) && $edit_notice['recipients']==$e['id'] ? 'selected' : '';
        echo "<option value='{$e['id']}' $sel>{$e['full_name']}</option>";
    }
    ?>
  </select>
</div>

<!-- Multiple Employees -->
<div id="multipleSelect" style="display:none;margin-top:10px;">
  <label>Select Employees:</label><br>
  <?php
  $emps->data_seek(0);
  $selected_ids = isset($edit_notice['recipients']) ? explode(',', $edit_notice['recipients']) : [];
  while($e = $emps->fetch_assoc()){
      $checked = in_array($e['id'], $selected_ids) ? 'checked' : '';
      echo "<input type='checkbox' name='multiple_emp_ids[]' value='{$e['id']}' $checked> {$e['full_name']}<br>";
  }
  ?>
</div>

<script>
function toggleRecipients(){
  const type = document.getElementById('audience_type').value;
  document.getElementById('singleSelect').style.display = (type === 'single') ? 'block' : 'none';
  document.getElementById('multipleSelect').style.display = (type === 'multiple') ? 'block' : 'none';
}
window.onload = toggleRecipients;
</script>
<script>
document.getElementById('showFormBtn').addEventListener('click', function() {
  const formSection = document.getElementById('noticeForm');
  if (formSection.style.display === 'none') {
    formSection.style.display = 'block';
    this.textContent = '− Hide Form';
  } else {
    formSection.style.display = 'none';
    this.textContent = '+ Publish / Announce Notice';
  }
});
</script>


        <label>Attachment (pdf/jpg/png/doc)</label>
        <br>
        <?php if(!empty($edit_notice['attachment'])): ?>
          <div class="small">Existing: <a class="attachment-link" href="<?= htmlspecialchars($edit_notice['attachment']) ?>" target="_blank"><?= htmlspecialchars(basename($edit_notice['attachment'])) ?></a></div>
        <?php endif; ?>
        <input type="file" name="attachment">

        <label style="margin-top:10px;"><input type="checkbox" name="is_published" <?= ($edit_notice ? $edit_notice['is_published'] : 1) ? 'checked' : '' ?>> Published</label>

        <div style="margin-top:12px">
          <button type="submit" class="btn"><?= $edit_notice ? 'Update Notice' : 'Add Notice' ?></button>
          <?php if($edit_notice): ?><a class="btn warn" href="admin_notices.php">Cancel Edit</a><?php endif; ?>
        </div>
      </form>
    </div>

    <!-- List column -->
    <div class="col list">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div class="title-small">All Notices</div>
        <div class="small">Total: <?= $list->num_rows ?></div>
      </div>

      <table>
        <thead>
          <tr>
            <th style="width:36%">Title & Description</th>
            <th>Posted To</th>
            <th>Published</th>
            <th>Created</th>
            <th style="width:170px">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while($row = $list->fetch_assoc()): ?>
          <tr>
            <td>
              <div style="font-weight:700"><?= htmlspecialchars($row['title']) ?></div>
              <div class="small"><?= htmlspecialchars(mb_strimwidth(strip_tags($row['description']),0,160,'...')) ?></div>
              <?php if(!empty($row['attachment'])): ?>
                <div class="small"><a class="attachment-link" href="<?= htmlspecialchars($row['attachment']) ?>" target="_blank"><?= htmlspecialchars(basename($row['attachment'])) ?></a></div>
              <?php endif; ?>
            </td>
          <td>
  <?php
    $posted_to_names = '';

    // If notice sent to all employees
    if ($row['audience_type'] === 'all') {
        $posted_to_names = 'All Employees';
    }
    // If notice sent to a single employee
    elseif ($row['audience_type'] === 'single' && !empty($row['recipients'])) {
        $emp_id = intval($row['recipients']);
        $res_emp = $conn->query("SELECT full_name FROM users WHERE id = $emp_id");
        if ($res_emp && $emp = $res_emp->fetch_assoc()) {
            $posted_to_names = htmlspecialchars($emp['full_name']);
        }
    }
    // If notice sent to multiple employees
    elseif ($row['audience_type'] === 'multiple' && !empty($row['recipients'])) {
        $ids = explode(',', $row['recipients']);
        $in = implode(',', array_map('intval', $ids));
        $res_emp = $conn->query("SELECT full_name FROM users WHERE id IN ($in)");
        $names = [];
        while ($emp = $res_emp->fetch_assoc()) {
            $names[] = $emp['full_name'];
        }
        $posted_to_names = htmlspecialchars(implode(', ', $names));
    } else {
        $posted_to_names = '—';
    }

    echo $posted_to_names;
  ?>
</td>

            <td><?= $row['is_published'] ? 'Yes' : 'No' ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
<button class="btn"
  onclick="openEditModal(
      '<?= $row['id'] ?>',
      `<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>`,
      `<?= htmlspecialchars($row['description'], ENT_QUOTES) ?>`,
      '<?= $row['is_published'] ?>'
  )">
  Edit
</button>


  <a class="btn" 
     href="admin_notices.php?action=toggle&id=<?= $row['id'] ?>">
     <?= $row['is_published'] ? 'Unpublish' : 'Publish' ?>
  </a>

  <a class="btn danger" 
     href="admin_notices.php?action=delete&id=<?= $row['id'] ?>" 
     onclick="return confirm('Delete this notice?')">
     Delete
  </a>
</td>

          </tr>
        <?php endwhile; ?>
        <?php if ($list->num_rows == 0): ?>
          <tr><td colspan="5" class="small">No notices found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
        </main>
        </div>
<footer>
    
    <p>2025 WorkFusion. All rights reserved.</p>
  </footer>
  <!-- Notification Popup -->
<div id="notifyPopup" class="notify-popup"></div>

<script>
  // Show notification if flash message exists
  document.addEventListener("DOMContentLoaded", function() {
    const flash = <?= json_encode($flash ?? ""); ?>;
    if (flash && flash.trim() !== "") {
      showNotification(flash);
    }
  });

  function showNotification(msg) {
    const popup = document.getElementById("notifyPopup");
    popup.textContent = msg;
    popup.classList.add("show");

    // Auto hide after 3 seconds
    setTimeout(() => popup.classList.remove("show"), 3000);
  }
</script>

<style>
  /* Simple Notification Popup */
  .notify-popup {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #002147;
    color: #fff;
    padding: 12px 18px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.4s ease;
    z-index: 9999;
  }
  .notify-popup.show {
    opacity: 1;
    transform: translateY(0);
  }
</style>
<!-- ==================== EDIT NOTICE POPUP MODAL ===================== -->
<div id="editModal" style="
  display:none; 
  position:fixed; 
  top:0; left:0; 
  width:100%; height:100%; 
  background:rgba(0,0,0,0.55);
  z-index:9999; 
  justify-content:center; 
  align-items:center;">
  
  <div style="width:500px;background:white;padding:20px;border-radius:12px;">

    <h2>Edit Notice</h2>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="mode" value="edit">
      <input type="hidden" name="id" id="edit_id">

      <label>Title</label>
      <input type="text" name="title" id="edit_title" required>

      <label>Description</label>
      <textarea name="description" id="edit_desc" rows="5" required></textarea>

      <label>Publish</label>
      <input type="checkbox" name="is_published" id="edit_pub">

      <br><br>
      <label>Replace Attachment (optional)</label>
      <input type="file" name="attachment">

      <br><br>
      <button class="btn" type="submit">Update</button>
      <button type="button" class="btn danger" onclick="closeEditModal()">Cancel</button>
    </form>

  </div>
</div>
<!-- ==================== END MODAL ===================== -->
<script>
function openEditModal(id, title, desc, pub){
    document.getElementById("edit_id").value = id;
    document.getElementById("edit_title").value = title;
    document.getElementById("edit_desc").value = desc;
    document.getElementById("edit_pub").checked = (pub == 1);

    document.getElementById("editModal").style.display = "flex";
}

function closeEditModal(){
    document.getElementById("editModal").style.display = "none";
}
</script>

</body>
 
</html>
