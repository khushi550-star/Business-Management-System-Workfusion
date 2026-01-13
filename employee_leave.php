<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Leave Portal</title>
  <style>

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


    .profile-card { display: flex; gap: 18px; background: #eef7fb; border-radius: 6px; padding: 16px 18px; align-items: center; margin-bottom: 20px; }

    .container {
      max-width: 800px;
      margin: 40px auto;
      background: #eef6f9;
      padding: 25px;
      border-radius: 16px;
        border: 1px solid #dde3ed;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      color: #002147;
      margin-bottom: 20px;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    input, select, textarea, button {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }

    button {
      background: #002147;
      color: white;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #002147;
    }
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


th, td{
    padding: 12px 15px;
    text-align:center;
}
th{
    background-color: #002147;
    color:white;
    font-weight:600;
    font-size:14px;
    text-transform:uppercase;
}
td{
    font-size:14px;
    color:#555;
}

/* Row styling */
tr:nth-child(even){
    background-color:#f9f9f9;
}
tr:hover{
    background-color:#eaeff5;
}


    .status-pending {
      color: orange;
    }

    .status-approved {
      color: green;
    }

    .status-rejected {
      color: red;
    }
    .sidebar-separator {
  border: 0;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.3); /* white-ish line */
  margin: 16px 0; /* spacing between avatar and menu */
}

    footer { position: relative; background: #002147; color: white; text-align: center; padding: 5px; }
    
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
      <div class="avatar">
        <?php echo isset($_SESSION['full_name']) ? strtoupper(substr($_SESSION['full_name'], 0, 1)) : '?'; ?>
      </div>

      <div class="user-info">
        <div class="name">
          <?php echo isset($_SESSION['full_name']) ? ($_SESSION['full_name']) : 'Guest'; ?>
        </div>
        <div class="emp-id">
          Employee ID: <?php echo isset($_SESSION['emp_id']) ? $_SESSION['emp_id'] : 'N/A'; ?>
        </div>
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
      <div class="content">

        <div class="profile-card">
                    <div class="photo">
    <?php
    // Determine which photo to show: session → DB → default
    $photo_file = !empty($_SESSION['photo']) ? $_SESSION['photo'] : ($user['photo'] ?? '');
    $photo_path = 'uploads/profile/' . $photo_file;

    if (!empty($photo_file) && file_exists($photo_path)) {
        echo '<img src="' . $photo_path . '" style="width:88px;height:88px;border-radius:50%;object-fit:cover;">';
    } else {
        echo '<img src="default-avatar.png" style="width:88px;height:88px;border-radius:50%;object-fit:cover;">';
    }
    ?>
  </div>

          <div class="info">
            <h2><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Guest'; ?> (<?php echo $_SESSION['emp_id']; ?>)</h2>
            <div style="display:flex;gap:32px;margin-top:8px">
              <div>
                <h4>Email</h4>
                <div><?php echo $_SESSION['email']; ?></div>
              </div>
            </div>
          </div>
        </div>

  <div class="container">
    <h1>Send Leave Request</h1>

    <form id="leaveForm" enctype="multipart/form-data">
  <input type="hidden" id="emp_id" name="emp_id" value="<?php echo $_SESSION['emp_id']; ?>">

  <input type="text" value="<?php echo $_SESSION['full_name']; ?>" disabled style="background:#eee;">

 <select id="leave_type" name="leave_type" required>
   <option value="Select Leave Type">Select Leave Type</option>
  <option value="Casual Leave">Casual Leave</option>
  <option value="Sick Leave">Sick Leave</option>
  <option value="Paid Leave">Paid Leave</option>
  <option value="Maternity Leave">Maternity Leave(for Females only)</option>
</select>

  <input type="date" id="start_date" name="start_date" required>
  <input type="date" id="end_date" name="end_date" required>
  <textarea id="reason" name="reason" placeholder="Reason for leave..." rows="3" required></textarea>

<!-- File upload (hidden by default) -->
<div id="medical_upload_section" style="display:none;">
  <label for="medical_file">Upload Medical Certificate (required for Sick Leave)</label>
  <input type="file" id="medical_file" name="medical_file" accept=".pdf,.jpg,.jpeg,.png">
</div>


  <button type="submit">Apply Leave</button>
</form>
  </div>

   <center> <h2 style="margin-top:30px;">Leave History</h2></center>
    <table id="leaveTable">
      <thead>
        <tr>
          <th>Leave Type</th>
          <th>Start</th>
          <th>End</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Medical File</th>
          <th>Action</th>

        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
  
  </main>
</div>

<script>
const leaveForm = document.getElementById("leaveForm");

// ✅ Show/hide medical file upload only if Sick Leave AND duration > 2 days
const leaveTypeInput = document.getElementById("leave_type");
const startDateInput = document.getElementById("start_date");
const endDateInput = document.getElementById("end_date");
const medicalSection = document.getElementById("medical_upload_section");
const medicalFile = document.getElementById("medical_file");

function checkMedicalRequirement() {
  const leaveType = leaveTypeInput.value;
  const start = new Date(startDateInput.value);
  const end = new Date(endDateInput.value);

  // Calculate days difference
  let diffDays = 0;
  if (startDateInput.value && endDateInput.value) {
    diffDays = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1;
  }

  // Show upload only if Sick Leave > 2 days
  if (leaveType === "Sick Leave" && diffDays > 2) {
    medicalSection.style.display = "block";
    medicalFile.required = true;
  } else {
    medicalSection.style.display = "none";
    medicalFile.required = false;
    medicalFile.value = "";
  }
}

// 🔁 Trigger check on leave type or date change
leaveTypeInput.addEventListener("change", checkMedicalRequirement);
startDateInput.addEventListener("change", checkMedicalRequirement);
endDateInput.addEventListener("change", checkMedicalRequirement);

// ✅ Load leave history for display + validation
let leaveHistory = [];

function loadLeaveHistory() {
  const emp_id = document.getElementById("emp_id").value;
  fetch("submit_leave.php?emp_id=" + emp_id)
    .then(res => res.json())
    .then(data => {
      leaveHistory = data;
      const tbody = document.querySelector("#leaveTable tbody");
      tbody.innerHTML = "";

      data.forEach(row => {
        const tr = document.createElement("tr");

        // ✅ Show delete button only for Pending leaves
        let deleteBtn = "-";
        if (row.status === "Pending") {
          deleteBtn = `<button class="delete-btn" data-id="${row.id}" style="background:#c0392b;color:white;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;">Delete</button>`;
        }

        tr.innerHTML = `
          <td>${row.leave_type}</td>
          <td>${row.start_date}</td>
          <td>${row.end_date}</td>
          <td>${row.reason}</td>
          <td class="status-${row.status.toLowerCase()}">${row.status}</td>
          <td>${row.medical_file ? `<a href="${row.medical_file}" target="_blank">View</a>` : '-'}</td>
          <td>${deleteBtn}</td>
        `;
        tbody.appendChild(tr);

        // Notify employee about leave decision
        if (row.status !== 'Pending' && row.notified == 0) {
          alert(`Your leave from ${row.start_date} to ${row.end_date} has been ${row.status}`);
          fetch(`mark_notified.php?id=${row.id}`);
        }
      });

      // ✅ Add delete button event listeners
      document.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", function() {
          const leaveId = this.getAttribute("data-id");
          if (confirm("Are you sure you want to delete this pending leave request?")) {
            fetch("delete_leaves.php?id=" + leaveId)
              .then(res => res.json())
              .then(resp => {
                alert(resp.message);
                loadLeaveHistory();
              })
              .catch(err => {
                console.error(err);
                alert("Error deleting leave.");
              });
          }
        });
      });
    });
}

// ✅ Form submission — show alerts, allow submission
leaveForm.addEventListener("submit", function(e) {
  e.preventDefault();

const leaveType = document.getElementById("leave_type").value;
const startDate = new Date(document.getElementById("start_date").value);
const endDate = new Date(document.getElementById("end_date").value);

// 🚫 Block if leave starts or ends on weekend
if (startDate.getDay() === 0 || startDate.getDay() === 6 ||
    endDate.getDay() === 0 || endDate.getDay() === 6) {
  alert("❌ You cannot start or end your leave on a weekend (Saturday or Sunday).");
  return;
}

// ✅ Count only weekdays between start and end
let workingDays = 0;
const tempDate = new Date(startDate);
while (tempDate <= endDate) {
  const day = tempDate.getDay();
  if (day !== 0 && day !== 6) { // skip Sunday(0) & Saturday(6)
    workingDays++;
  }
  tempDate.setDate(tempDate.getDate() + 1);
}

// ✅ If all selected days are weekends
if (workingDays === 0) {
  alert("❌ All selected dates fall on weekends. Please select working days.");
  return;
}

// Replace diffDays with workingDays for further calculations
const diffDays = workingDays;
const currentYear = new Date().getFullYear();



  // ✅ Count existing leaves
  const casualCount = leaveHistory.filter(l =>
    l.leave_type === "Casual Leave" &&
    new Date(l.start_date).getFullYear() === currentYear
  ).length;

  const sickCount = leaveHistory.filter(l =>
    l.leave_type === "Sick Leave" &&
    new Date(l.start_date).getFullYear() === currentYear
  ).length;

  // ✅ Check for overlapping leave dates
  let overlap = false;
  leaveHistory.forEach(l => {
    const s1 = new Date(l.start_date);
    const e1 = new Date(l.end_date);
    if (
      (startDate <= e1 && endDate >= s1)
    ) {
      overlap = true;
    }
  });

  if (overlap) {
    alert("⚠️ You already have a leave applied for these dates!");
    return; // block submission in this case only
  }

    // ✅ Calculate leave days per year and per month
  const currentMonth = new Date().getMonth(); // 0=Jan, 11=Dec
  const casualDaysYear = leaveHistory
    .filter(l => l.leave_type === "Casual Leave" && new Date(l.start_date).getFullYear() === currentYear)
    .reduce((sum, l) => sum + ((new Date(l.end_date) - new Date(l.start_date)) / (1000 * 60 * 60 * 24) + 1), 0);

  const sickDaysYear = leaveHistory
    .filter(l => l.leave_type === "Sick Leave" && new Date(l.start_date).getFullYear() === currentYear)
    .reduce((sum, l) => sum + ((new Date(l.end_date) - new Date(l.start_date)) / (1000 * 60 * 60 * 24) + 1), 0);

  const monthlyCasualSickDays = leaveHistory
    .filter(l => 
      (l.leave_type === "Casual Leave" || l.leave_type === "Sick Leave") &&
      new Date(l.start_date).getMonth() === currentMonth &&
      new Date(l.start_date).getFullYear() === currentYear
    )
    .reduce((sum, l) => sum + ((new Date(l.end_date) - new Date(l.start_date)) / (1000 * 60 * 60 * 24) + 1), 0);

  // ✅ Yearly alerts
  if (leaveType === "Casual Leave" && casualDaysYear > 10) {
    alert("⚠️ Localhost says: You have taken more than 10 Casual Leave days this year.");
  }
  if (leaveType === "Sick Leave" && sickDaysYear > 10) {
    alert("⚠️ Localhost says: You have taken more than 10 Sick Leave days this year.");
  }

  // ✅ Monthly total alert (Casual + Sick > 3)
  if ((leaveType === "Casual Leave" || leaveType === "Sick Leave") && monthlyCasualSickDays > 3) {
    alert("⚠️ Localhost says: You have taken more than 3 total (Casual + Sick) leave days this month.");
  }

 // ✅ Show alert for >2 days leave — except Maternity Leave
if (leaveType !== "Maternity Leave" && diffDays > 2) {
  alert("⚠️ You are applying for more than 2 days of leave at a time!");
}


  // ✅ Restrict Maternity Leave to 120 days max
if (leaveType === "Maternity Leave" && diffDays > 120) {
  alert("⚠️ Maternity Leave cannot exceed 120 days!");
  return; // Block submission
}

// 🚫 Rule 1: Block past dates
const today = new Date();
today.setHours(0,0,0,0);

if (startDate < today) {
  alert("❌ You cannot apply leave for past dates. Only present or future dates are allowed.");
  return;
}

// 🚫 Rule 2: Block if absent day already recorded
// We'll check attendance API for those dates
const emp_id = document.getElementById("emp_id").value;
const startStr = document.getElementById("start_date").value;
const endStr = document.getElementById("end_date").value;

// Call attendance check API (create a new small PHP file to handle it)
return fetch(`check_absent.php?emp_id=${emp_id}&start=${startStr}&end=${endStr}`)
  .then(res => res.json())
  .then(attData => {
    if (attData.hasAbsent === true) {
      alert(`❌ You cannot apply leave for dates where you were already marked absent (${attData.absentDates.join(', ')})`);
      return; // Stop submission
    }

    // ✅ Continue submission only if no absent conflict
    const formData = new FormData(leaveForm);
    return fetch("submit_leave.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        loadLeaveHistory();
        leaveForm.reset();
        document.getElementById("medical_upload_section").style.display = "none";
      })
      .catch(err => {
        console.error(err);
        alert("Error submitting leave request");
      });
  })
  .catch(err => {
    console.error(err);
    alert("Error checking attendance.");
  });


  // ✅ Continue submission
  const formData = new FormData(this);
  fetch("submit_leave.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      loadLeaveHistory();
      leaveForm.reset();
      document.getElementById("medical_upload_section").style.display = "none";
    })
    .catch(err => {
      console.error(err);
      alert("Error submitting leave request");
    });
});

// Load on page start
window.onload = loadLeaveHistory;
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


<footer><p>2025 WorkFusion. All rights reserved.</p></footer>
</body>
</html>