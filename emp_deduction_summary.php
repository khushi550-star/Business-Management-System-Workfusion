<?php
session_start();
include "Connect.php";

if (!isset($_SESSION['emp_id'])) {
    header("Location: login.php");
    exit;
}

$emp_id = $_SESSION['emp_id'];

// ✅ Fetch the latest payroll record for the logged-in employee
$payroll = $conn->query("SELECT * FROM payroll WHERE emp_id='$emp_id' ORDER BY id DESC LIMIT 1")->fetch_assoc();

if (!$payroll) {
    echo "<h3 style='color:red;text-align:center;'>No payroll record found.</h3>";
    exit;
}

// ✅ Decode the leave breakdown JSON
$leave_json = !empty($payroll['leave_json']) ? json_decode($payroll['leave_json'], true) : [];

// Extract values safely
$attendance_absent = $leave_json['attendance_absent'] ?? 0;
$attendance_half = $leave_json['attendance_half'] ?? 0;
$casual = $leave_json['casual'] ?? 0;
$sick = $leave_json['sick'] ?? 0;
$paid = $leave_json['paid'] ?? 0;
$maternity = $leave_json['maternity'] ?? 0;

$extraMonthly = $leave_json['extraMonthly'] ?? 0;
$extraCasualYear = $leave_json['extraCasualYear'] ?? 0;
$extraSickYear = $leave_json['extraSickYear'] ?? 0;
$extraPaidYear = $leave_json['extraPaidYear'] ?? 0;

$unpaidDays = $leave_json['unpaidDays'] ?? 0;
$per_day = $leave_json['per_day'] ?? 0;
$total_deduction = $leave_json['total_deduction'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Salary Summary</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f5f8ff;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 950px;
      margin: 30px auto;
      background: #fff;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #003366;
      margin-bottom: 25px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }
    th {
      background: #003366;
      color: #fff;
    }
    tr:nth-child(even) {
      background: #f9f9f9;
    }
    .section-title {
      margin-top: 30px;
      font-weight: bold;
      color: #003366;
      font-size: 18px;
      border-bottom: 2px solid #003366;
      padding-bottom: 6px;
    }
    .summary-box {
      background: #f0f6ff;
      border-radius: 10px;
      padding: 15px;
      margin-top: 20px;
      border: 1px solid #d0e3ff;
    }
    .summary-box p {
      margin: 5px 0;
      font-size: 15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Salary Deduction Summary</h2>

    <div class="summary-box">
      <p><b>Employee ID:</b> <?php echo $emp_id; ?></p>
      <p><b>Month:</b> <?php echo $payroll['month']; ?></p>
      <p><b>Year:</b> <?php echo $payroll['year']; ?></p>
      <p><b>Basic Salary:</b> ₹<?php echo number_format($payroll['basic_salary'],2); ?></p>
      <p><b>Gross Salary:</b> ₹<?php echo number_format($payroll['gross_salary'],2); ?></p>
    </div>

    <h3 class="section-title">Leave Breakdown</h3>
    <table>
      <tr><th>Leave Type</th><th>Days</th></tr>
      <tr><td>Absent Days</td><td><?php echo $attendance_absent; ?></td></tr>
      <tr><td>Half Days</td><td><?php echo $attendance_half; ?></td></tr>
      <tr><td>Casual Leave</td><td><?php echo $casual; ?></td></tr>
      <tr><td>Sick Leave</td><td><?php echo $sick; ?></td></tr>
      <tr><td>Paid Leave</td><td><?php echo $paid; ?></td></tr>
      <tr><td>Maternity Leave</td><td><?php echo $maternity; ?></td></tr>
    </table>

    <h3 class="section-title">Extra Leave Deductions</h3>
    <table>
      <tr><th>Category</th><th>Extra Days</th></tr>
      <tr><td>Extra Monthly Leaves</td><td><?php echo $extraMonthly; ?></td></tr>
      <tr><td>Extra Casual (Yearly)</td><td><?php echo $extraCasualYear; ?></td></tr>
      <tr><td>Extra Sick (Yearly)</td><td><?php echo $extraSickYear; ?></td></tr>
      <tr><td>Extra Paid (Yearly)</td><td><?php echo $extraPaidYear; ?></td></tr>
    </table>

    <h3 class="section-title">Deduction Summary</h3>
    <table>
      <tr><td>Unpaid Days</td><td><?php echo $unpaidDays; ?></td></tr>
      <tr><td>Per Day Salary</td><td>₹<?php echo number_format($per_day,2); ?></td></tr>
      <tr><td><b>Total Deduction</b></td><td><b>₹<?php echo number_format($total_deduction,2); ?></b></td></tr>
    </table>

    <h3 class="section-title">Net Pay</h3>
    <table>
      <tr><td><b>Net Salary Payable</b></td><td><b>₹<?php echo number_format($payroll['net_pay'],2); ?></b></td></tr>
    </table>
  </div>
</body>
</html>
