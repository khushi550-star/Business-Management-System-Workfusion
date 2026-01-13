<?php
session_start();
include "Connect.php";
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['admin_id'])) {
  $_SESSION['admin_id'] = 1; // demo admin
}
$admin_id = $_SESSION['admin_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Meeting Portal</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    /* Profile card */
    .profile-card{display:flex;gap:18px;background:#eef7fb;border-radius:6px;padding:16px 18px;margin-bottom:18px;align-items:center}
/* CONTAINER */
.container{max-width:1200px;margin:auto}
.card{background:white;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,.08);padding:20px;margin-bottom:20px}
h3{color:#002147;text-align:center;margin-bottom:20px}
h5{color:#003d80;margin-bottom:10px}

/* FORM STYLING */
label{display:block;font-weight:600;color:#333;margin-top:10px}
input,select{width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;margin-top:4px}
button{cursor:pointer;border:none;padding:8px 14px;border-radius:8px;font-weight:600}
.btn-primary{background:#002147;color:white}
.btn-primary:hover{background:#003d80}
.btn-success{background: #002147;color:white}
.btn-success:hover{background:#007b55}
.btn-info{background:#0096c7;color:white}
.btn-info:hover{background:#0077a1}
.btn-secondary{background:#6c757d;color:white}
.btn-secondary:hover{background:#565e64}
.btn-danger{background:#dc3545;color:white}
.btn-danger:hover{background:#a71d2a}

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
  padding: 14px;
  text-align: center;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid rgba(255,255,255,0.3);
}

td {
  padding: 6px 6px;
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
/* MODALS (custom) */
.modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.4);display:none;justify-content:center;align-items:center;z-index:1000}
.modal.active{display:flex}
.modal-content{background:white;border-radius:12px;max-width:800px;width:90%;padding:20px;animation:fadeIn .3s ease}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.modal-header h5{margin:0;color:#002147}
.close-btn{background:#dc3545;color:white;border:none; color: #fff;
  font-size: 26px;
  cursor: pointer;
  transition: color 0.3s ease;
borde-radius:50%}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}


/* FOOTER */
footer{background:#002147;color:white;text-align:center;padding:8px;font-size:14px}
.hidden{display:none}
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
     <center> <h2>📋 Conduct Meetings</h2></center>
     <br>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>📅 Meetings</h2>
    <button type="button" id="toggleFormBtn" class="btn-primary">➕ Create New Meeting</button>
  </div>

  <form id="createMeetingForm" enctype="multipart/form-data" class="hidden" style="margin-top:15px;">
    <label>Meeting Title</label>
    <input type="text" name="title" required placeholder="Enter meeting title">

    <div style="display:flex;gap:10px;margin-top:10px">
      <div style="flex:1">
        <label>Date</label>
        <input type="date" name="date" required>
      </div>
      <div style="flex:1">
        <label>Start Time</label>
        <input type="time" name="start_time" required>
      </div>
      <div style="flex:1">
        <label>End Time</label>
        <input type="time" name="end_time">
      </div>
    </div>

    <label class="mt-3">Meeting Link</label>
    <input type="text" name="meeting_link" placeholder="https://meet.example.com/...">

    <label class="mt-3">Invite Type</label>
    <select id="inviteType" name="invite_type" required>
      <option value="">-- Select Invite Type --</option>
      <option value="all">All Employees</option>
      <option value="selected">Selected Employees</option>
      <option value="particular">Particular Employee</option>
    </select>

    <div id="selectedEmployees" class="hidden">
      <label>Select Employees</label>
      <select name="invitees[]" multiple style="height:100px">
        <?php
        $users = $conn->query("SELECT id, full_name, mobile_email FROM users");
        while ($u = $users->fetch_assoc()) {
          echo "<option value='{$u['id']}'>{$u['full_name']} ({$u['mobile_email']})</option>";
        }
        ?>
      </select>
    </div>

    <div style="text-align:right;margin-top:15px">
      <button class="btn-success">Create Meeting</button>
    </div>
  </form>
</div>


      <div class="card">
        <h5>📅 All Meetings</h5>
        <table id="meetingTable">
          <thead>
            <tr>
              <th>ID</th><th>Title</th><th>Date</th><th>Meeting Link</th><th>Attendance</th><th>Upload Files</th><th>View Files</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  
</div>
<!-- Attendance Modal -->
<div class="modal" id="attendanceModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5>👥 Meeting Attendance</h5>
      <button class="close-btn" onclick="closeModal('attendanceModal')">&times;</button>
    </div>
    <div class="modal-body" id="attendanceBody"></div>
  </div>
</div>

<!-- Files Modal -->
<div class="modal" id="filesModal">
  <div class="modal-content">
    <div class="modal-header">
      <h5>📂 Uploaded Files</h5>
      <button class="close-btn" onclick="closeModal('filesModal')">&times;</button>
    </div>
    <div class="modal-body" id="filesBody"></div>
  </div>
            </main>
</div>

<footer> 2025 WorkFusion. All rights reserved.</footer>

<script>
$("#inviteType").on("change", function(){
  $(this).val()==="selected"||$(this).val()==="particular"
  ? $("#selectedEmployees").removeClass("hidden")
  : $("#selectedEmployees").addClass("hidden");
});

function loadMeetings(){
  $.get("meeting_backend.php?action=fetch_meetings", function(data){
    let html="";
    data.forEach(m=>{
      html+=`<tr>
      <td>${m.id}</td>
      <td>${m.title}</td>
      <td>${m.date}</td>
      <td><a href="${m.meeting_link}" target="_blank">Open</a></td>
      <td>${m.attended}/${m.total} 
          <button class='btn-info viewAttendance' data-id='${m.id}'>View</button></td>
      <td>
        <form class="uploadForm" data-id="${m.id}" enctype="multipart/form-data">
          <input type="file" name="file" required>
          <button type="submit" class="btn-success">Upload</button>
        </form>
      </td>
      <td><button class="btn-secondary viewFiles" data-id="${m.id}">View</button></td>
      </tr>`;
    });
    $("#meetingTable tbody").html(html);
  },'json');
}
loadMeetings();

$("#createMeetingForm").on("submit", function(e){
  e.preventDefault();
  const fd=new FormData(this);
  fd.append('action','create_meeting');
  $.ajax({
    url:'meeting_backend.php',method:'POST',data:fd,processData:false,contentType:false,
    success:function(res){alert(res.message);if(res.ok){loadMeetings();$("#createMeetingForm")[0].reset();}}
  });
});

$(document).on("submit",".uploadForm",function(e){
  e.preventDefault();
  const fd=new FormData(this);
  fd.append('action','upload_file');
  fd.append('meeting_id',$(this).data("id"));
  $.ajax({
    url:'meeting_backend.php',method:'POST',data:fd,processData:false,contentType:false,
    success:function(res){alert(res.message);loadMeetings();}
  });
});

$(document).on("click",".viewAttendance",function(){
  const mid=$(this).data("id");
  $.get("meeting_backend.php?action=view_attendance&meeting_id="+mid,function(data){
    let html="<table><tr><th>Name</th><th>Email</th><th>Status</th><th>Marked At</th></tr>";
    if(data.length===0) html+="<tr><td colspan=4>No records</td></tr>";
    data.forEach(r=>{html+=`<tr><td>${r.full_name}</td><td>${r.mobile_email}</td><td>${r.attendance_status}</td><td>${r.marked_at||'-'}</td></tr>`});
    html+="</table>";
    $("#attendanceBody").html(html);
    openModal('attendanceModal');
  },'json');
});

$(document).on("click",".viewFiles",function(){
  const mid=$(this).data("id");
  $.get("meeting_backend.php?action=view_files&meeting_id="+mid,function(data){
    let html="<table><tr><th>File Name</th><th>Action</th></tr>";
    if(data.length===0) html+="<tr><td colspan=2>No files</td></tr>";
    data.forEach(f=>{
      html+=`<tr><td>${f.file_name}</td>
      <td><a href="uploads/meeting_files/${f.file_path}" target="_blank" class="btn-primary">View</a>
      <button class="btn-danger deleteFile" data-id="${f.id}" data-mid="${mid}">Delete</button></td></tr>`;
    });
    html+="</table>";
    $("#filesBody").html(html);
    openModal('filesModal');
  },'json');
});

$(document).on("click",".deleteFile",function(){
  if(!confirm("Delete this file?"))return;
  const fid=$(this).data("id"),mid=$(this).data("mid");
  $.post("meeting_backend.php",{action:"delete_file",file_id:fid},function(res){
    alert(res.message);
    if(res.ok){
      $.get("meeting_backend.php?action=view_files&meeting_id="+mid,function(data){
        let html="<table><tr><th>File Name</th><th>Action</th></tr>";
        if(data.length===0) html+="<tr><td colspan=2>No files</td></tr>";
        data.forEach(f=>{
          html+=`<tr><td>${f.file_name}</td>
          <td><a href="uploads/meeting_files/${f.file_path}" target="_blank" class="btn-primary">View</a>
          <button class="btn-danger deleteFile" data-id="${f.id}" data-mid="${mid}">Delete</button></td></tr>`;
        });
        html+="</table>";
        $("#filesBody").html(html);
      },'json');
    }
  },'json');
});

function openModal(id){document.getElementById(id).classList.add('active')}
function closeModal(id){document.getElementById(id).classList.remove('active')}
// Toggle Create Meeting Form
$("#toggleFormBtn").on("click", function() {
  $("#createMeetingForm").toggleClass("hidden");
  if($("#createMeetingForm").hasClass("hidden")){
    $(this).text("➕ Create New Meeting");
  } else {
    $(this).text("❌ Close Form");
  }
});

</script>
</body>
</html>
