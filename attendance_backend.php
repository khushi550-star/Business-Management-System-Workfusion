<?php
session_start();
include "Connect.php";
date_default_timezone_set('Asia/Kolkata');
header('Content-Type: application/json');


// 🔹 AUTO MARK ABSENT AFTER 11:00 AM
$today = date('Y-m-d');
$currentTime = date('H:i:s');

if ($currentTime >= '11:00:00') {
    // Fetch all employees who haven’t checked in today
    $absent = $conn->query("
        SELECT id FROM users 
        WHERE id NOT IN (
            SELECT emp_id FROM attendance WHERE date='$today'
        )
    ");
    
    while ($row = $absent->fetch_assoc()) {
        $emp_id_abs = $row['id'];
          // Check if employee is on leave today
        $leave = $conn->query("
            SELECT id FROM leaves 
            WHERE emp_id=$emp_id_abs 
              AND '$today' BETWEEN start_date AND end_date 
              AND status='Approved'
        ");
        if ($leave->num_rows > 0) continue; // skip leave users
        // Insert “Absent” record only once per day
        $check = $conn->query("SELECT id FROM attendance WHERE emp_id=$emp_id_abs AND date='$today'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO attendance (emp_id, date, status, created_at) VALUES ($emp_id_abs, '$today', 'Absent', NOW())");
        }
    }
}
// 🔹 END AUTO ABSENT LOGIC


$emp_id = $_SESSION['emp_id'] ?? null;
if (!$emp_id) {
  echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
  exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ========================== FACE CHECK-IN ==========================
if ($action === 'face_checkin') {
  $image = $_POST['image'] ?? '';
  $latitude = $_POST['latitude'] ?? '';
  $longitude = $_POST['longitude'] ?? '';
  $date = date('Y-m-d');
  $time = date('H:i:s');

  
// Skip weekends (Saturday = 6, Sunday = 0)
$dayOfWeek = date('w');
if ($dayOfWeek == 0 || $dayOfWeek == 6) {
  echo json_encode(['status' => 'error', 'message' => 'Weekend! Attendance not required.']);
  exit;
}


  // ⏰ Restrict check-in after 11:00 AM
  $current_hour = date('H'); // 24-hour format
  if ($current_hour >= 11) {
    echo json_encode(['status' => 'error', 'message' => 'Check-in time closed! You can only check in before 11:00 AM.']);
    exit;
  }
  


  // Check existing
  $chk = $conn->prepare("SELECT id FROM attendance WHERE emp_id=? AND date=?");
  $chk->bind_param('ss', $emp_id, $date);
  $chk->execute();
  $res = $chk->get_result();
  if ($res->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Already checked in today']);
    exit;
  }

  // Save photo
  $photoPath = "uploads/" . $emp_id . "_" . time() . ".png";
  $img = str_replace('data:image/png;base64,', '', $image);
  $img = str_replace(' ', '+', $img);
  file_put_contents($photoPath, base64_decode($img));

  // Save record
  $stmt = $conn->prepare("INSERT INTO attendance (emp_id, date, checkin_time, status, latitude, longitude, photo, created_at)
                          VALUES (?, ?, ?, 'Pending', ?, ?, ?, NOW())");
  $stmt->bind_param('ssssss', $emp_id, $date, $time, $latitude, $longitude, $photoPath);
  $stmt->execute();

  echo json_encode(['status' => 'success', 'message' => 'Check-in successful', 'checkin_time' => $time]);
  exit;
}

// ========================== CHECKOUT ==========================
if ($action === 'checkout') {
  $date = date('Y-m-d');
  $time = date('H:i:s');

  
// Skip weekends (Saturday = 6, Sunday = 0)
$dayOfWeek = date('w');
if ($dayOfWeek == 0 || $dayOfWeek == 6) {
  echo json_encode(['status' => 'error', 'message' => 'Weekend! Checkout not required.']);
  exit;
}


  $q = $conn->prepare("SELECT * FROM attendance WHERE emp_id=? AND date=?");
  $q->bind_param('ss', $emp_id, $date);
  $q->execute();
  $res = $q->get_result();
  if ($res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No check-in found']);
    exit;
  }

  $r = $res->fetch_assoc();
  $checkin = strtotime($r['date'] . ' ' . $r['checkin_time']);
  $checkout = strtotime($r['date'] . ' ' . $time);
  $diff = $checkout - $checkin;
  $hours = floor($diff / 3600);
  $minutes = floor(($diff % 3600) / 60);
  $work_duration = sprintf('%02d:%02d', $hours, $minutes);

  // Status
  if ($diff >= 8 * 3600) $status = 'Overtime';
  elseif ($diff >= 6 * 3600) $status = 'Present';
  elseif ($diff >= 3600) $status = 'Half Day';
  else $status = 'Absent';

  $up = $conn->prepare("UPDATE attendance SET checkout_time=?, work_duration=?, work_seconds=?, status=? WHERE id=?");
  $up->bind_param('ssisi', $time, $work_duration, $diff, $status, $r['id']);
  $up->execute();

  echo json_encode(['status' => 'success', 'message' => 'Checkout successful', 'checkout_time' => $time, 'work_duration' => $work_duration, 'status_today' => $status]);
  exit;
}

// ========================== FETCH DATA ==========================
if ($action === 'fetch') {
  $data = [];
  $q = $conn->prepare("SELECT * FROM attendance WHERE emp_id=? ORDER BY date DESC LIMIT 60");
  $q->bind_param('s', $emp_id);
  $q->execute();
  $res = $q->get_result();
  while ($row = $res->fetch_assoc()) $data[] = $row;

  echo json_encode(['status' => 'success', 'data' => $data]);
  exit;
}

// ========================== AUTO CHECKOUT ==========================
$sql = "SELECT id, emp_id, date, checkin_time FROM attendance WHERE checkout_time IS NULL OR checkout_time=''";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
  $checkin_dt = strtotime($r['date'] . ' ' . $r['checkin_time']);
  if (time() - $checkin_dt >= 8 * 3600) {
    $auto_time = date('H:i:s', $checkin_dt + 8 * 3600);
    $work_duration = '08:00';
    $status = 'Overtime';
    $diff = 8 * 3600;

    $u = $conn->prepare("UPDATE attendance SET checkout_time=?, work_duration=?, work_seconds=?, status=? WHERE id=?");
    $u->bind_param('ssisi', $auto_time, $work_duration, $diff, $status, $r['id']);
    $u->execute();
  }
}
?>
