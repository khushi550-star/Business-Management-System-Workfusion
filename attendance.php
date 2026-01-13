<?php
session_start();
include "Connect.php";

date_default_timezone_set('Asia/Kolkata');

// 🔹 AUTO MARK ABSENT AFTER 11:00 AM
$today = date('Y-m-d');
$currentTime = date('H:i:s');

if ($currentTime >= '11:00:00') {
    // Find all employees who haven't checked in today
    $absentQuery = $conn->query("
        SELECT emp_id FROM users 
        WHERE emp_id NOT IN (
            SELECT emp_id FROM attendance WHERE date = '$today'
        )
    ");

    while ($row = $absentQuery->fetch_assoc()) {
        $emp = $row['emp_id'];

        // Check if already marked
        $check = $conn->query("SELECT id FROM attendance WHERE emp_id='$emp' AND date='$today'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO attendance (emp_id, date, status) VALUES ('$emp', '$today', 'Absent')");
        }
    }
}


// Check login
if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_SESSION['emp_id'];
$current_date = date('Y-m-d');
$current_time = date('H:i:s');


// ----- Get today's attendance record -----
$attendance_query = $conn->prepare("SELECT * FROM attendance WHERE emp_id = ? AND date = ?");
$attendance_query->bind_param("ss", $emp_id, $current_date);
$attendance_query->execute();
$result = $attendance_query->get_result();
$attendance = $result->fetch_assoc();

// ----- Auto Checkout if Overtime (e.g., after 9 hours) -----
if ($attendance && !empty($attendance['checkin_time']) && empty($attendance['checkout_time'])) {
    $checkin_time = strtotime($attendance['checkin_time']);
    $current_time_ts = strtotime($current_time);
    $diff_seconds = $current_time_ts - $checkin_time;

    // Set overtime threshold (e.g., 9 hours = 32400 seconds)
    $overtime_limit = 8 * 3600;

    if ($diff_seconds >= $overtime_limit) {
        $hours = floor($diff_seconds / 3600);
        $minutes = floor(($diff_seconds % 3600) / 60);
        $duration = sprintf("%02d:%02d", $hours, $minutes);

        // Automatically update checkout
        $update = $conn->prepare("UPDATE attendance 
            SET checkout_time = ?, work_duration = ?, work_seconds = ? 
            WHERE emp_id = ? AND date = ?");
        $update->bind_param("ssiss", $current_time, $duration, $diff_seconds, $emp_id, $current_date);
        $update->execute();

        echo "<script>alert('Auto Checkout done due to overtime (Duration: $duration)'); window.location.href='attendance.php';</script>";
        exit();
    }
}

// ----- Check-In -----
if (isset($_POST['checkin'])) {
    // Prevent multiple check-ins
    if ($attendance && !empty($attendance['checkin_time'])) {
        echo "<script>alert('You have already checked in today!');</script>";
        exit();
    }

      // Restrict check-in after 11:00 AM
    $current_hour = date('H'); // 24-hour format
    if ($current_hour >= 11) {
        echo "<script>alert('Check-in time is closed! You can only check in before 11:00 AM.');</script>";
        exit();
    }
    // Insert new record
    $insert = $conn->prepare("INSERT INTO attendance (emp_id, date, checkin_time, status) VALUES (?, ?, ?, 'Present')");
    $insert->bind_param("sss", $emp_id, $current_date, $current_time);
    if ($insert->execute()) {
        echo "<script>alert('Checked in successfully at $current_time'); window.location.href='attendance.php';</script>";
    } else {
        echo "<script>alert('Error during check-in.');</script>";
    }
    exit();
}

// ----- Check-Out -----
if (isset($_POST['checkout'])) {
    if (!$attendance || empty($attendance['checkin_time'])) {
        echo "<script>alert('You must check in before checking out!');</script>";
        exit();
    }

    if (!empty($attendance['checkout_time'])) {
        echo "<script>alert('You have already checked out today!');</script>";
        exit();
    }

    // Calculate work duration
    $checkin_time = strtotime($attendance['checkin_time']);
    $checkout_time = strtotime($current_time);
    $diff_seconds = $checkout_time - $checkin_time;

    if ($diff_seconds < 0) $diff_seconds = 0; // Prevent negative durations

    $hours = floor($diff_seconds / 3600);
    $minutes = floor(($diff_seconds % 3600) / 60);
    $duration = sprintf("%02d:%02d", $hours, $minutes);

    // Update attendance record
    $update = $conn->prepare("UPDATE attendance 
        SET checkout_time = ?, work_duration = ?, work_seconds = ? 
        WHERE emp_id = ? AND date = ?");
    $update->bind_param("ssiss", $current_time, $duration, $diff_seconds, $emp_id, $current_date);

    if ($update->execute()) {
        echo "<script>alert('Checked out successfully at $current_time. Duration: $duration'); window.location.href='attendance.php';</script>";
    } else {
        echo "<script>alert('Error during check-out.');</script>";
    }
    exit();
}
?>
