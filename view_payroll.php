<?php
session_start();
include "Connect.php";


// Identify user type
$is_admin = isset($_SESSION['admin_id']);
$payroll_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payroll_id <= 0) {
    echo "<h3>Invalid payroll ID</h3>";
    exit;
}

// ✅ Fetch payroll details
if ($is_admin) {
    // Admin can view all payrolls
    $sql = "SELECT p.*, u.full_name, u.monthly_salary 
            FROM payrolls p
            JOIN users u ON p.emp_id = u.id
            WHERE p.id = $payroll_id";
} else {
    // Employee can view only their own
    $emp_id = $_SESSION['emp_id'];
    $sql = "SELECT p.*, u.full_name, u.monthly_salary 
            FROM payrolls p
            JOIN users u ON p.emp_id = u.id
            WHERE p.id = $payroll_id AND p.emp_id = $emp_id";
}

$res = $conn->query($sql);

if ($res->num_rows === 0) {
    echo "<h3>Unauthorized or Payroll not found.</h3>";
    exit;
}

$payroll = $res->fetch_assoc();

// ✅ Fetch detailed daily breakdown
$details = $conn->query("SELECT * FROM payroll_details WHERE payroll_id = $payroll_id ORDER BY date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payroll Details</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f5f7fa;
        color: #12303f;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 900px;
        margin: 40px auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 25px 35px;
    }
    h1, h2 {
        color: #002147;
        text-align: center;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    th, td {
        padding: 10px 12px;
        border: 1px solid #ccc;
        text-align: center;
    }
    th {
        background: #002147;
        color: white;
    }
    tr:nth-child(even) {
        background: #f2f6fc;
    }
    .summary {
        margin-top: 25px;
        background: #eef6ff;
        padding: 15px;
        border-radius: 8px;
        font-size: 16px;
        line-height: 1.8;
    }
    .btn-back {
        display: inline-block;
        margin-top: 25px;
        padding: 10px 18px;
        background: #002147;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-back:hover {
        background: #004080;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Payroll Details</h1>

    <div class="summary">
        <strong>Employee Name:</strong> <?= htmlspecialchars($payroll['full_name']); ?><br>
        <strong>Employee ID:</strong> <?= $payroll['emp_id']; ?><br>
        <strong>Month:</strong> <?= sprintf('%02d', $payroll['month']); ?>/<?= $payroll['year']; ?><br>
        <strong>Total Working Days:</strong> <?= $payroll['total_working_days']; ?><br>
        <strong>Counted Days:</strong> <?= $payroll['counted_days']; ?><br>
        <strong>Base Salary:</strong> ₹<?= number_format($payroll['monthly_salary'], 2); ?><br>
        <strong>Gross Pay:</strong> ₹<?= number_format($payroll['gross_pay'], 2); ?><br>
    </div>

    <h2>Daily Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Weight</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $details->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['date']); ?></td>
                <td><?= htmlspecialchars($row['status']); ?></td>
                <td><?= htmlspecialchars($row['day_weight']); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <center>
        <a href="<?= $is_admin ? 'admin_payroll.php' : 'employee_payroll.php'; ?>" class="btn-back">← Back</a>
    </center>
</div>
</body>
</html>
