<?php
include('Connect.php');
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>WorkFusion — Employee Portal</title>
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
    .sidebar a { display: block; padding: 12px; border-radius: 8px; color: white; text-decoration: none; margin-bottom: 6px; }
    .sidebar a:hover, .sidebar a.active { background: #ccd8e6; color: black; }

    .profile-card { display: flex; gap: 18px; background: #eef7fb; border-radius: 6px; padding: 16px 18px; align-items: center; margin-bottom: 20px; }
    .projects {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.projects form {
  display: grid;
  gap: 10px;
  margin-bottom: 20px;
}

.project-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.project-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  background: #f9fafb;
}

.project-card h4 {
  margin-bottom: 5px;
  color: #333;
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
  <!-- Sidebar Menu -->
  
    
    <a class="active" href="employee.php">Dashboard</a>
    <a href="employee_worktimetracking.php"> Attendance & Work Time</a>
    <a href="#">Payslips</a>
    <a href="#">Tasks & Projects</a>
    <a href="#">Support Request</a>
    <a href="#">Meetings</a>
    <a href="#">News & Notices</a>
    <a href="#">Employee Info</a>
    <a href="#">ID Cards</a>
    <a href="#">Logout</a>
  
  </nav>
  </aside>

 <main class="main">
      <div class="content">

        <div class="profile-card">
                   <div class="photo"><img src="m1.jpg" alt="Admin" style="width: 88px;height: 88px; border-radius: 50%;"></div>
          <div class="info">
            <div style="display:flex;gap:32px;margin-top:8px">
              
              </div>
            </div>
          </div>
        </div>

<section class="card projects">
  <h3>My Projects & Tasks</h3>

  <form id="addProjectForm">
    <input type="text" id="project_name" placeholder="Project Name" required>
    <textarea id="description" placeholder="Description"></textarea>
    <input type="date" id="start_date" required>
    <input type="date" id="end_date" required>
    <button type="submit">Add Project</button>
  </form>

  <div id="projectsList" class="project-list"></div>

</section>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("addProjectForm");
  const list = document.getElementById("projectsList");

  // Fetch projects
  function loadProjects() {
    fetch("project1.php")
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          list.innerHTML = "";
          data.data.forEach(p => {
            list.innerHTML += `
              <div class="project-card">
                <h4>${p.project_name}</h4>
                <p>${p.description}</p>
                <p><b>Start:</b> ${p.start_date} | <b>End:</b> ${p.end_date}</p>
                <select onchange="updateStatus(${p.project_id}, this.value)">
                  <option ${p.status === 'Pending' ? 'selected' : ''}>Pending</option>
                  <option ${p.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                  <option ${p.status === 'Completed' ? 'selected' : ''}>Completed</option>
                </select>
              </div>
            `;
          });
        }
      });
  }

  // Add new project
  form.addEventListener("submit", e => {
    e.preventDefault();
    const fd = new FormData(form);
    fd.append("action", "add");

    fetch("project1.php", {
      method: "POST",
      body: fd
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        form.reset();
        loadProjects();
      });
  });

  // Global function for status update
  window.updateStatus = function (id, status) {
    const fd = new FormData();
    fd.append("action", "update");
    fd.append("project_id", id);
    fd.append("status", status);

    fetch("project1.php", {
      method: "POST",
      body: fd
    })
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        loadProjects();
      });
  };

  loadProjects();
});

</script>

</body>
</html>