<?php
// employee_worktimetracking.php
session_start();
include "Connect.php";
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>WorkFusion — Smart Attendance Dashboard</title>

  <!-- FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>


  <style>
    :root {
      --primary: #001f3f;
      --secondary: #004aad;
      --accent: #17a2b8;
      --success: #28a745;
      --warning: #ffc107;
      --danger: #dc3545;
      --purple: #6f42c1;
      --info: #0dcaf0;
      --muted: #6c757d;
      --bg-light: #f4f6fa;
      --card-bg: #ffffff;
    }

   
    * { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0;
     }
    html,body{
      height:100%;
      font-family:  'Segoe UI', Arial, sans-serif;
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


    h2 {
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 6px;
    }

    .layout {
      display: grid;
      grid-template-columns: 380px 1fr;
      gap: 20px;
      margin-top: 20px;
    }

    .card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 18px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      transition: 0.3s;
    }

    .card:hover { box-shadow: 0 8px 22px rgba(0,0,0,0.12); }

    .btn {
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn:hover { background: var(--secondary); }
    .btn:disabled { background: #ccc; cursor: not-allowed; }

    #calendar {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 14px rgba(0,0,0,0.05);
      width:345px;
      height: 100px;
      
    }

    /* Circular date cells */
    .fc-daygrid-day-number {
      display: inline-flex !important;
      justify-content: center;
      align-items: center;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #e9eef6;
      color: var(--primary);
      margin: auto;
      transition: 0.2s;
    }

    .fc-daygrid-day:hover .fc-daygrid-day-number {
      background: var(--secondary);
      color: #fff;
      transform: scale(1.05);
    }

    .fc-toolbar-title {
      font-size: 18px !important;
      color: var(--primary);
    }

    .fc-daygrid-day.fc-day-sat,
    .fc-daygrid-day.fc-day-sun {
      background-color: #e1dff1ff !important;
      opacity: 0.85;
       pointer-events: none; /* 🚫 disable clicks */
  cursor: not-allowed;
    }

    /* Color Legend */
    .legend {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 12px;
      font-size: 13px;
    }
    .legend-item {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .legend-color {
      width: 14px;
      height: 14px;
      border-radius: 50%;
    }

    #mapContainer {
      height: 200px;
      display:flex;
      align-items:center;
      justify-content:center;
      color: #777;
    }


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

    @media(max-width:900px) {
      .layout { grid-template-columns: 1fr; }
    

    }
    #faceModal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(3px);
  z-index: 10000;
  align-items: center;
  justify-content: center;
  animation: fadeBg 0.3s ease-in-out;
}

@keyframes fadeBg {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  width: 420px;
  max-width: 90%;
  text-align: center;
  padding: 25px 20px;
  animation: popupScale 0.3s ease-in-out;
}

@keyframes popupScale {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.modal-card video {
  width: 100%;
  border-radius: 12px;
  border: 3px solid var(--primary);
  margin-top: 10px;
}
 footer { position: relative; background: #002147; color: white; text-align: center; padding: 5px; }
    .sidebar-separator {
  border: 0;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.3); /* white-ish line */
  margin: 16px 0; /* spacing between avatar and menu */
}
/* ======================================
   📱 Responsive Design + Sidebar Toggle
====================================== */

/* Tablets and small laptops */
@media (max-width: 1024px) {
  .layout {
    grid-template-columns: 1fr;
  }

  .sidebar {
    width: 220px;
  }

  .main {
    padding: 16px;
  }

  #calendar {
    width: 100%;
  }
}

/* Mobile and small devices */
@media (max-width: 768px) {
  /* Sidebar hidden by default */
  .sidebar {
    position: fixed;
    top: 64px;
    left: -260px;
    width: 240px;
    height: calc(100% - 64px);
    background: #002147;
    transition: 0.3s ease;
    z-index: 9999;
    box-shadow: 4px 0 10px rgba(0, 0, 0, 0.3);
  }

  /* Active (visible) sidebar */
  .sidebar.active {
    left: 0;
  }

  .main {
    padding: 16px;
  }

  /* Layout becomes vertical */
  .layout {
    grid-template-columns: 1fr;
  }

  /* Profile card simplified */
  .profile-card {
    flex-direction: column;
    align-items: flex-start;
  }

  .photo img {
    width: 70px;
    height: 70px;
  }

  /* Calendar and map responsive */
  #calendar {
    width: 100%;
    height: auto;
  }

  #mapContainer {
    height: 180px;
  }

  /* Navbar adjustments */
  .nav-links {
    display: none;
  }

  .hamburger {
    display: flex;
  }
}

/* Ultra-small phones */
@media (max-width: 480px) {
  .logo {
    width: 140px;
  }

  h2 {
    font-size: 18px;
  }

  .btn {
    padding: 8px 14px;
    font-size: 13px;
  }

  th, td {
    padding: 8px;
    font-size: 12px;
  }

  footer {
    font-size: 12px;
    padding: 6px;
  }
}
@media (max-width: 768px) {
  .profile-card {
    flex-direction: column;       /* Stack photo and info */
    align-items: center;          /* Center everything */
    text-align: center;           /* Center text */
    width: 90%;                   /* Slightly smaller for mobile */
    margin: 0 auto;               /* Center horizontally */
  }

  .profile-card .photo {
    margin-bottom: 12px;          /* Space between photo and text */
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
  <center><h1>WorkFusion — Attendance</h1></center>

  <center><h3><small id="currentDate"></small> | <span id="currentTime"></span></h3></center>
  <h4><hr></h4>

  <div class="layout">
    <div>
      <div class="card">
        <center><h2>📅 Attendance Calendar</h2></center>
        <br>
        <div id="calendar"></div>

        <!-- Color Definition Legend -->
        <div class="legend">
          <div class="legend-item"><span class="legend-color" style="background:#28a745;"></span> Present</div>
          <div class="legend-item"><span class="legend-color" style="background:#ffc107;"></span> Half Day</div>
          <div class="legend-item"><span class="legend-color" style="background:#dc3545;"></span> Absent</div>
          <div class="legend-item"><span class="legend-color" style="background:#6f42c1;"></span> Overtime</div>
          <div class="legend-item"><span class="legend-color" style="background:#0dcaf0;"></span> Leave</div>
        </div>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>📍 Last Check-In Location</h3>
        <div id="mapContainer"><small>No recent check-in</small></div>
      </div>
    </div>

    <div>
      
      <div class="card">
        <center><h2>🕓 Work Time Tracking</h2></center>
        <br>
        <center><h4><p>Check-In Time: <strong id="ci">--:--</strong></h4></p></center>
         <br>
        <center><h4><p>Check-Out Time: <strong id="co">--:--</strong></p></h4></center>
         <br>
        <center><h4><p>Working Hours: <strong id="wd">--</strong></p></h4></center>
         <br>
        <center><h4><p>Status: <strong id="st">--</strong></p></h4></center>
         <br>
        <hr>
         <br>
        <center><button id="checkinBtn" class="btn">Face Check-In</button>
        <button id="checkoutBtn" class="btn" disabled>Check Out</button></center>
      </div>

      <div class="card" style="margin-top:12px">
        <h3>📜 Attendance History (30 Days)</h3>
        <br>
        <div id="historyBox">Loading...</div>
      </div>
    </div>
  </div>

 <!-- ✅ Face Modal -->
<div id="faceModal">
  <div class="modal-card">
    <h3 style="color:var(--primary);font-weight:600;">🔍 Face Check-In Verification</h3>
    <video id="video" autoplay playsinline></video>
    <div style="margin-top:12px;">
      <button id="captureBtn" class="btn">📸 Capture & Check-In</button>
      <button id="closeModal" class="btn" style="background:#555;">Cancel</button>
    </div>
    <p style="font-size:13px;color:#666;margin-top:8px;">Check-in before <b>11:00 AM</b> is required.</p>
  </div>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const checkinBtn = document.getElementById('checkinBtn');
  const checkoutBtn = document.getElementById('checkoutBtn');
  const faceModal = document.getElementById('faceModal');
  const video = document.getElementById('video');
  const captureBtn = document.getElementById('captureBtn');
  const closeModal = document.getElementById('closeModal');
  const ciEl = document.getElementById('ci'), coEl = document.getElementById('co'), wdEl = document.getElementById('wd'), stEl = document.getElementById('st');
  const historyBox = document.getElementById('historyBox');

  function updateClock() {
    const now = new Date();
    document.getElementById('currentDate').textContent = now.toLocaleDateString('en-CA');
    document.getElementById('currentTime').textContent = now.toLocaleTimeString();
    const day = now.getDay();
    if (day === 0 || day === 6) {
      checkinBtn.disabled = true;
      checkinBtn.textContent = "Weekend — No Check-In";
    } else {
      checkinBtn.disabled = false;
      checkinBtn.textContent = "Face Check-In";
    }
  }
  setInterval(updateClock, 1000);
  updateClock();

  const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    height: 450,
    dayCellDidMount: function(info) {
      const day = info.date.getDay();
      if (day === 0 || day === 6) {
        info.el.style.backgroundColor = '#f3f3f3';
        info.el.style.opacity = '0.8';
      }
    }
  });
  calendar.render();

  async function loadData() {
    const res = await fetch('attendance_backend.php?action=fetch');
    const j = await res.json();
    if (j.status !== 'success') return;

    const events = [];
  j.data.forEach(r => {
  const dateObj = new Date(r.date);
  const day = dateObj.getDay(); // 0 = Sunday, 6 = Saturday

  // 🚫 Skip showing leave or attendance entries on weekends
  if (day === 0 || day === 6) return;

  let color = '#adb5bd';
  if (r.status === 'Present') color = '#28a745';
  else if (r.status === 'Half Day') color = '#ffc107';
  else if (r.status === 'Absent') color = '#dc3545';
  else if (r.status === 'Overtime') color = '#6f42c1';
  else if (r.status === 'Leave') color = '#0dcaf0';

  events.push({
    title: r.status,
    start: r.date,
    allDay: true,
    backgroundColor: color
  });
});

    calendar.removeAllEvents();
    calendar.addEventSource(events);

   let html = `
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <th>Date</th>
      <th>Check-In</th>
      <th>Check-Out</th>
      <th>Duration</th>
      <th>Status</th>
      <th>Location</th>
    </tr>
`;
 j.data.forEach(r => {
     html += `<tr>
  <td>${r.date}</td>
  <td>${r.checkin_time || '--'}</td>
  <td>${r.checkout_time || '--'}</td>
  <td>${r.work_duration || '--'}</td>
  <td>
    <span style="color:${
      r.status === 'Present' ? '#28a745' :
      r.status === 'Half Day' ? '#ffc107' :
      r.status === 'Absent' ? '#dc3545' :
      r.status === 'Overtime' ? '#6f42c1' : '#0dcaf0'
    }">${r.status || '--'}</span>
  </td>
  <td>
  ${
    r.latitude && r.longitude
      ? `<a href="https://www.google.com/maps?q=${r.latitude},${r.longitude}" target="_blank" style="color:#007bff;text-decoration:none;">View</a>`
      : '--'
  }
</td>

</tr>`;

    });
    html += '</table>';
    historyBox.innerHTML = html;
  }
  loadData();

  let stream = null;
  checkinBtn.addEventListener('click', async () => {
    if (checkinBtn.disabled) return;
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
      faceModal.style.display = 'flex';
    } catch {
      alert('Camera access denied');
    }
  });

  closeModal.addEventListener('click', () => {
    faceModal.style.display = 'none';
    if (stream) stream.getTracks().forEach(t => t.stop());
  });

  captureBtn.addEventListener('click', async () => {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const imageData = canvas.toDataURL('image/png');

    navigator.geolocation.getCurrentPosition(async (pos) => {
      const fd = new FormData();
      fd.append('action', 'face_checkin');
      fd.append('image', imageData);
      fd.append('latitude', pos.coords.latitude);
      fd.append('longitude', pos.coords.longitude);
      const resp = await fetch('attendance_backend.php', { method: 'POST', body: fd });
      const j = await resp.json();
      alert(j.message);
      if (j.status === 'success') {
        ciEl.textContent = j.checkin_time;
        checkoutBtn.disabled = false;
        faceModal.style.display = 'none';
        if (stream) stream.getTracks().forEach(t => t.stop());

        // ✅ Show map
        const { latitude, longitude } = pos.coords;
        const mapContainer = document.getElementById('mapContainer');
        mapContainer.innerHTML = `
          <iframe width="100%" height="200" style="border:0; border-radius:10px;" loading="lazy"
          allowfullscreen src="https://www.google.com/maps?q=${latitude},${longitude}&hl=en&z=15&output=embed"></iframe>
          <p style="font-size:13px;color:#555;margin-top:4px;">
            Location captured at ${latitude.toFixed(4)}, ${longitude.toFixed(4)}
          </p>`;
      }
    });
  });

 checkoutBtn.addEventListener('click', async () => {
  if (!confirm('Confirm checkout?')) return;

  navigator.geolocation.getCurrentPosition(async (pos) => {
    const { latitude, longitude } = pos.coords;

    const fd = new FormData();
    fd.append('action', 'checkout');
    fd.append('latitude', latitude);
    fd.append('longitude', longitude);

    const resp = await fetch('attendance_backend.php', { method: 'POST', body: fd });
    const j = await resp.json();
    alert(j.message);

    if (j.status === 'success') {
      coEl.textContent = j.checkout_time;
      wdEl.textContent = j.work_duration;
      stEl.textContent = j.status_today;
      checkoutBtn.disabled = true;
      loadData();

      // ✅ Show map for checkout too
      const mapContainer = document.getElementById('mapContainer');
      mapContainer.innerHTML = `
        <iframe width="100%" height="200" style="border:0; border-radius:10px;" loading="lazy"
        allowfullscreen src="https://www.google.com/maps?q=${latitude},${longitude}&hl=en&z=15&output=embed"></iframe>
        <p style="font-size:13px;color:#555;margin-top:4px;">
          Checkout location captured at ${latitude.toFixed(4)}, ${longitude.toFixed(4)}
        </p>`;
    }
  }, () => {
    alert('Location access denied');
  });
});

});
// ✅ Sidebar toggle for mobile
const hamburger = document.getElementById('hamburger');
const sidebar = document.querySelector('.sidebar');

hamburger.addEventListener('click', () => {
  sidebar.classList.toggle('active');
});

</script>

<footer><p>2025 WorkFusion. All rights reserved.</p></footer>
</body>
</html>
