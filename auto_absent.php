<?php
include "Connect.php";
date_default_timezone_set('Asia/Kolkata');

header('Content-Type: application/json');

// ---------------------------------------------
// AUTO MARK ABSENT API
// ---------------------------------------------

$today = date('Y-m-d');

// 🔹 Weekend skip (Saturday=6, Sunday=0)
$day = date('w');
if ($day == 0 || $day == 6) {
    echo json_encode(['status' => 'skip', 'message' => 'Weekend! Absent not required.']);
    exit;
}

// 🔹 Fetch employees who did NOT check-in today
$sql = "
    SELECT id FROM users 
    WHERE id NOT IN (
        SELECT emp_id FROM attendance 
        WHERE date = '$today'
    )
";

$absent_users = $conn->query($sql);

$count = 0;

while ($row = $absent_users->fetch_assoc()) {
    $emp_id = $row['id'];

    // 🔹 Skip users who are on approved leave
    $leave = $conn->query("
        SELECT id FROM leaves 
        WHERE emp_id = $emp_id
          AND '$today' BETWEEN start_date AND end_date
          AND status = 'Approved'
    ");

    if ($leave->num_rows > 0) {
        continue; // user is on leave → skip
    }

    // 🔹 Ensure only one "Absent" entry per day
    $check = $conn->query("
        SELECT id FROM attendance 
        WHERE emp_id = $emp_id AND date = '$today'
    ");

    if ($check->num_rows == 0) {
        $conn->query("
            INSERT INTO attendance (emp_id, date, status, created_at)
            VALUES ($emp_id, '$today', 'Absent', NOW())
        ");
        $count++;
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Auto absent done',
    'total_absent_marked' => $count
]);
?>
