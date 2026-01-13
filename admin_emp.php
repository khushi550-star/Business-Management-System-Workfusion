<?php
session_start();
require_once "Connect.php";

// Fetch all employees
$result = $conn->query("SELECT * FROM users ORDER BY id ASC");

// Collect departments for dropdown filter
$departments = [];
$resDept = $conn->query("SELECT DISTINCT department FROM users WHERE department != '' ORDER BY department ASC");
while($d = $resDept->fetch_assoc()) {
  $departments[] = $d['department'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel | Employee Profiles</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  :root {
    --primary: #0047ab;
    --primary-dark: #002b5e;
    --text-color: #333;
    --bg-color: #f4f6fb;
    --card-bg: #fff;
    --table-border: #e5e7eb;
    --hover-bg: #f9fbff;
    --input-bg: #fff;
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

header .logo {
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
    .profile-card{display:flex;gap:18px;background:#eef7fb;border-radius:6px;padding:16px 18px;margin-bottom:18px;align-items:center}
  .container {
     overflow-x:auto;
    background:white;
    padding:20px;
     border: 1px solid #dde3ed;
    border-radius:10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}


  /* ===== Filter Bar ===== */
  .filter-bar {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
  }

  .filter-bar input, .filter-bar select {
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid var(--table-border);
    background: var(--input-bg);
    color: var(--text-color);
    font-size: 15px;
    outline: none;
    transition: 0.3s ease;
  }

  .filter-bar input:focus, .filter-bar select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 4px var(--primary);
  }

  .filter-bar input {
    flex: 1;
    min-width: 250px;
  }

  .filter-bar select {
    min-width: 180px;
  }


  /* Counter */
  .counter {
    font-weight: 600;
    margin-bottom: 12px;
    font-size: 16px;
    color:  #002147 ;
  }

  

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

/* Employee table images */
#employeeTable img {
  width: 55px;
  height: 55px;
  border-radius: 50%;  /* round table photo */
  object-fit: cover;
  border: 2px solid #e5e7eb;
}

  .action-btn {
    padding: 8px 14px;
    background: var(--primary);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .action-btn:hover {
    background: var(--primary-dark);
  }

  @media (max-width: 768px) {
    .filter-bar { flex-direction: column; }
    .filter-bar input, .filter-bar select { width: 100%; }
    table, thead, tbody, th, td, tr { display: block; }
    thead { display: none; }
    tr {
      background: var(--card-bg);
      margin-bottom: 20px;
      border-radius: 10px;
      padding: 12px;
    }
    td {
      display: flex;
      justify-content: space-between;
      padding: 10px 5px;
      border: none;
    }
    td::before {
      content: attr(data-label);
      font-weight: 600;
      color: var(--primary);
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

  <!-- Filters -->
  <div class="filter-bar">
    <input type="text" id="searchInput" placeholder="🔍 Search by name, email, department...">
    <select id="deptFilter">
      <option value="">All Departments</option>
      <?php foreach($departments as $dept): ?>
        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
 
  <div class="container">
  <!-- Live Employee Counter -->
  <div class="counter" id="employeeCount">
    Total Employees: <?php echo $result->num_rows; ?>
  </div>

  <!-- Employee Table -->
  <table id="employeeTable">
    <thead>
      <tr>
        <th>Photo</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Department</th>
        <th>Designation</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td data-label="Photo">
          <?php 
            $photo_path = 'uploads/profile/' . ($row['photo'] ?? '');
            echo (file_exists($photo_path) && $row['photo']) ? 
                 "<img src='$photo_path'>" : 
                 "<img src='default-avatar.png'>";
          ?>
        </td>
        <td data-label="Full Name"><?php echo htmlspecialchars($row['full_name']); ?></td>
        <td data-label="Email"><?php echo htmlspecialchars($row['mobile_email']); ?></td>
        <td data-label="Phone"><?php echo htmlspecialchars($row['phone']); ?></td>
        <td data-label="Department"><?php echo htmlspecialchars($row['department']); ?></td>
        <td data-label="Designation"><?php echo htmlspecialchars($row['designation']); ?></td>
        <td data-label="Action">
          <a class="action-btn" href="admin_view_employee.php?id=<?php echo $row['id']; ?>">View</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
      </main>
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const deptFilter = document.getElementById('deptFilter');
  const rows = document.querySelectorAll('#employeeTable tbody tr');
  const employeeCount = document.getElementById('employeeCount');

  function updateCounter() {
    const visibleRows = Array.from(rows).filter(r => r.style.display !== 'none');
    employeeCount.textContent = `Total Employees: ${visibleRows.length}`;
  }

  function filterTable() {
    const term = searchInput.value.toLowerCase();
    const dept = deptFilter.value.toLowerCase();
    rows.forEach(r => {
      const values = Array.from(r.cells).map(c => c.textContent.toLowerCase());
      const matchesSearch = values.some(v => v.includes(term));
      const matchesDept = dept === '' || values[4].includes(dept);
      r.style.display = matchesSearch && matchesDept ? '' : 'none';
    });
    updateCounter();
  }

  searchInput.addEventListener('input', filterTable);
  deptFilter.addEventListener('change', filterTable);
</script>

    <!-- Footer -->
  <footer>
    
    <p>2025 WorkFusion. All rights reserved.</p>
  </footer>

</body>
</html>
