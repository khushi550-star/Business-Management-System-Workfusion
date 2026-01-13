<?php
session_start();
require_once "Connect.php";

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) die("Employee not found.");

function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Profile | <?php echo h($user['full_name']); ?></title>
<style>
  /* === GLOBAL STYLE === */
  body {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background: #f4f6fb;
    margin: 0;
  }
  /* === PROFILE CONTAINER === */
  .container {
    max-width: 800px;
    background: #fff;
    margin: 40px auto;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    animation: fadeIn 0.5s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  /* === HEADER AREA === */
  .profile-header {
    background: #002147;
    color: white;
    text-align: center;
    padding: 40px 20px;
  }

  .profile-photo {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
    margin-bottom: 10px;
    box-shadow: 0 0 8px rgba(255,255,255,0.5);
  }

  h2 {
    margin: 8px 0 4px;
    font-size: 24px;
  }

  .emp-id {
    font-size: 14px;
    opacity: 0.9;
  }

  /* === DETAILS SECTION === */
  .info-section {
    padding: 25px 40px 30px;
  }

  .section-title {
    color: #002147;
    font-weight: 600;
    margin-bottom: 15px;
    border-left: 5px solid #0047ab;
    padding-left: 10px;
    font-size: 18px;
  }

  .info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 20px;
  }

  .info-box {
    background: #f8faff;
    border-radius: 10px;
    padding: 12px 16px;
    transition: background 0.3s ease;
  }

  .info-box:hover {
    background: #eaf1ff;
  }

  label {
    display: block;
    font-size: 13px;
    color: #0047ab;
    font-weight: 600;
    margin-bottom: 5px;
  }

  .value {
    color: #222;
    font-size: 15px;
  }

  /* === BUTTON === */
  .back-btn {
    display: inline-block;
    margin: 30px 40px;
    background: #0047ab;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
  }

  .back-btn:hover {
    background: #002147;
    transform: scale(1.04);
  }
  @media print {
  body {
    background: white;
    color: #222;
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    font-size: 11pt;
    margin: 0;
    display: flex;
    justify-content: center; /* center horizontally */
    align-items: flex-start; /* align top vertically */
    height: 100%;
  }
  .back-btn, #printProfile {
    display: none; /* hide buttons */
  }

  .container {
    max-width: 800px; /* center container width */
    width: 100%;
    margin: 20px auto;
    padding: 5px 15px;
    box-shadow: none;
    border-radius: 0;
  }

  .profile-header {
    padding: 10px 0 5px;
    text-align: center;
  }

  .profile-photo {
    width: 100px;
    height: 100px;
    display: block;
    margin: 0 auto 8px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
  }

  .profile-header h2 {
    font-size: 14pt;
    margin: 2px 0 2px;
  }

  .emp-id {
    font-size: 10pt;
    margin-bottom: 8px;
  }

  .info-section {
    padding: 3px 0;
  }

  .section-title {
    font-size: 12pt;
    font-weight: 600;
    margin: 6px 0 4px;
  }

  .info-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .info-box {
    flex: 0 0 48%;
    padding: 4px 6px;
    margin-bottom: 4px;
    page-break-inside: avoid;
  }

  label {
    font-weight: 600;
    color: #0047ab;
  }

  .value {
    margin-left: 4px;
  }

  /* Scale slightly to fit on one page */
  body, .container {
    transform: scale(0.95);
    transform-origin: top center;
  }
}



  @media (max-width: 700px) {
    .info-grid {
      grid-template-columns: 1fr;
    }
    .container {
      margin: 20px;
    }
  }
</style>
</head>
<body>

<div class="container">
  <div class="profile-header">
    <img class="profile-photo" src="<?php 
      $photo_path = 'uploads/profile/' . ($user['photo'] ?? '');
      echo (!empty($user['photo']) && file_exists($photo_path)) ? $photo_path : 'default-avatar.png'; 
    ?>" alt="Profile Photo">
    <h2><?php echo h($user['full_name']); ?></h2>
    <div class="emp-id">Employee ID: <?php echo $user['id']; ?></div>
  </div>

  <div class="info-section">
    <div class="section-title">Personal Details</div>
    <div class="info-grid">
      <div class="info-box"><label>Email</label><div class="value"><?php echo h($user['mobile_email']); ?></div></div>
      <div class="info-box"><label>Phone</label><div class="value"><?php echo h($user['phone']); ?></div></div>
      <div class="info-box"><label>Gender</label><div class="value"><?php echo h($user['gender']); ?></div></div>
      <div class="info-box"><label>Date of Birth</label><div class="value"><?php echo h($user['dob']); ?></div></div>
    </div>

    <div class="section-title" style="margin-top:25px;">Work Information</div>
    <div class="info-grid">
      <div class="info-box"><label>Department</label><div class="value"><?php echo h($user['department']); ?></div></div>
      <div class="info-box"><label>Designation</label><div class="value"><?php echo h($user['designation']); ?></div></div>
      <div class="info-box"><label>Work Location</label><div class="value"><?php echo h($user['work_location']); ?></div></div>
      <div class="info-box"><label>Experience</label><div class="value"><?php echo h($user['experience']); ?></div></div>
    </div>

    <div class="section-title" style="margin-top:25px;">Education & Previous Details</div>
    <div class="info-grid">
      <div class="info-box"><label>Qualification</label><div class="value"><?php echo h($user['qualification']); ?></div></div>
      <div class="info-box"><label>Previous Company</label><div class="value"><?php echo h($user['previous_company']); ?></div></div>
    </div>

    <div class="section-title" style="margin-top:25px;">Address</div>
    <div class="info-grid">
      <div class="info-box"><label>Current Address</label><div class="value"><?php echo h($user['current_address']); ?></div></div>
      <div class="info-box"><label>Permanent Address</label><div class="value"><?php echo h($user['permanent_address']); ?></div></div>
    </div>
  </div>
<div style="margin:30px 40px;">
  <a class="back-btn" href="admin_emp.php">← Back to Employee List</a>
  <button class="back-btn" id="printProfile" style="background:#28a745; margin-left:200px;">Print Profile</button>
</div>

<script>
document.getElementById('printProfile').addEventListener('click', function() {
    window.print();
});
</script>

</body>
</html>
