<?php
session_start();

// If not logged in, redirect to login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <title>WorkFusion — ADMIN Portal </title>
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

       
/* ===== Search Bar Styled like Filter Bar ===== */
.search {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
}

.search input {
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid var(--table-border, #ddd);
  background: var(--input-bg, #fff);
  color:  #12303f;
  font-size: 15px;
  outline: none;
  transition: 0.3s ease;
  flex: 1;
  min-width: 270px;
}

.search input:focus {
  border-color: var(--primary, #2563eb);
  box-shadow: 0 0 4px var(--primary, #2563eb);
}

/* ===== Card Grid ===== */
.card-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin: 20px 0;
}

/* ===== Individual Card Styling ===== */
.card {
  flex: 1 1 200px; /* Adjust min-width */
  background: #eef6f9; /* Same as summary */
  color: #002147;
  text-align: center;
  padding: 22px;
  border-radius: 12px;
  text-decoration: none;
  transition: 0.3s;
  box-shadow:0 6px 15px rgba(3,10,116,0.6);
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(0,0,0,0.18);
}

/* ===== Icon Styling ===== */
.card .icon {
  font-size: 2rem;
  margin-bottom: 12px;
  display: block;
}

/* ===== Text Styling ===== */
.card small {
  display: block;
  color:#0b3b48;
  font-size: 0.85rem;
  font-weight: 600;
  opacity: 0.9;
}

/* ===== Responsive ===== */
@media(max-width: 768px){
  .card-grid {
    flex-direction: column;
  }
  .card {
    flex: 1 1 100%;
  }
}


    /* Small widgets under cards */
    .widgets{display:flex;gap:14px;margin-top:16px}
    .widget{flex:1;background:#fff;border-radius:10px;padding:14px;box-shadow:0 4px 12px rgba(10,30,50,0.04)}

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
   /* ======================================
   📱 Responsive Design Enhancements
====================================== */

/* Tablet (max-width: 1024px) */
@media (max-width: 1024px) {
  .sidebar {
    width: 200px;
  }

  .main {
    padding: 16px;
  }

  .profile-card .info h2 {
    font-size: 20px;
  }

  .card-grid {
    gap: 15px;
  }

  .card {
    flex: 1 1 45%;
  }
}

/* Mobile (max-width: 768px) */
@media (max-width: 768px) {
  /* Navbar adjustments */
  .navbar {
    flex-direction: row;
    justify-content: space-between;
  }

 
  /* Sidebar becomes collapsible */
  .sidebar {
    position: fixed;
    top: 60px;
    left: -100%;
    width: 70%;
    height: calc(100% - 60px);
    background: #002147;
    transition: 0.3s;
    z-index: 999;
  }

  .sidebar.active {
    left: 0;
  }

  .main {
    padding: 16px;
  }

  /* Hide sidebar by default on mobile */
  .menu a {
    font-size: 15px;
    padding: 8px;
  }

  /* Profile Card */
  .profile-card {
    flex-direction: column;
    text-align: center;
  }

  .profile-card .photo {
    width: 70px;
    height: 70px;
  }

  /* Cards stacked vertically */
  .card-grid {
    flex-direction: column;
    align-items: stretch;
  }

  .card {
    flex: 1 1 100%;
  }

  /* Search bar fits full width */
  .search {
    flex-direction: column;
  }

  .search input {
    min-width: 100%;
  }

  /* Footer smaller */
  footer {
    font-size: 13px;
    padding: 6px;
  }
}

/* Small phones (max-width: 480px) */
@media (max-width: 480px) {
  .navbar {
    padding: 10px;
  }

 

  .card small {
    font-size: 0.8rem;
  }

  .profile-card .info h2 {
    font-size: 18px;
  }

  .profile-card .info small {
    font-size: 12px;
  }

  .hamburger span {
    width: 20px;
    height: 2px;
  }

  .card {
    padding: 16px;
  }
}
/* ======================================
   📱 Force 3 Cards per Row on Mobile
====================================== */
@media (max-width: 768px) {
  .card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* ✅ 3 cards per row */
    gap: 12px;
  }

  .card {
    flex: none;
    width: 100%;
    font-size: 14px;
    padding: 16px;
  }

  .card .icon {
    font-size: 1.5rem;
    margin-bottom: 6px;
  }

  .card small {
    font-size: 0.8rem;
  }
}

/* For very small screens (below 480px) – still keep 3 per row but smaller */
@media (max-width: 480px) {
  .card-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
  }

  .card {
    padding: 10px;
  }

  .card .icon {
    font-size: 1.2rem;
  }

  .card small {
    font-size: 0.7rem;
  }
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
    const hamburger = document.getElementById("hamburger");
    const navLinks = document.getElementById("navLinks");

    hamburger.addEventListener("click", () => {
      navLinks.classList.toggle("active");
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


        <div class="search" style="margin-bottom:12px">
          <input placeholder="🔍What are you looking for?" />
        </div>

<section class="card-grid">

  <a href="admin_att_tracking.php" class="card">
    <div class="icon"><i class="fa-solid fa-clock"></i></div>
    <small>Attendance Tracking</small>
  </a>

  <a href="admin_payroll.php" class="card">
    <div class="icon"><i class="fa-solid fa-money-bill-wave"></i></div>
    <small>Payroll Management</small>
  </a>

  <a href="admin_projects.php" class="card">
    <div class="icon"><i class="fa-solid fa-folder-tree"></i></div>
    <small>Tasks or Projects Assign</small>
  </a>

  <a href="admin_portal.php" class="card">
    <div class="icon"><i class="fa-solid fa-users"></i></div>
    <small>Meetings Conducting</small>
  </a>

  <a href="admin_notices.php" class="card">
    <div class="icon"><i class="fa-solid fa-newspaper"></i></div>
    <small>Announcement & Notices</small>
  </a>

  <a href="admin_emp.php" class="card">
    <div class="icon"><i class="fa-solid fa-id-card"></i></div>
    <small>Employee Info</small>
  </a>

  <a href="admin_leave.php" class="card">
    <div class="icon"><i class="fa-solid fa-file-pen"></i></div>
    <small>Leave Requests</small>
  </a>

</section>

    </main>
  </div>

  <script>
    // small JS: allow toggling sidebar on small screens
    (function(){
      var btn = document.querySelector('.menu-toggle');
      var sidebar = document.getElementById('sidebar');
      if(btn){
        btn.addEventListener('click', function(){
          sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
      }
    })();
    
  </script>
  <script>
  const searchInput = document.querySelector('.search input');
  const cards = document.querySelectorAll('.card-grid .card');

  searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();

    cards.forEach(card => {
      const text = card.querySelector('small').textContent.toLowerCase();
      if(text.includes(query)){
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  });
</script>

     <!-- Footer -->
  <footer>
    
    <p>2025 WorkFusion. All rights reserved.</p>
  </footer>

</body>
</html>