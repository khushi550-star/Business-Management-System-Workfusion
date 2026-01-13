<?php
session_start();
include "Connect.php";

if (!isset($_POST['payroll_id'])) {
    die("Invalid access.");
}
$pid = intval($_POST['payroll_id']);

$sql = "SELECT p.*, u.full_name, u.department, u.designation, u.bank_name, u.account_no, u.ifsc 
        FROM payroll p 
        JOIN users u ON p.emp_id = u.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    die("Payroll record not found.");
}

$monthNum = date('n', strtotime($row['month']));
$year = intval($row['year']);
$prevMonth = $monthNum - 1;
$prevYear = $year;
if ($prevMonth == 0) {
    $prevMonth = 12;
    $prevYear--;
}

$prev = $conn->prepare("SELECT total_deduction FROM payroll 
                        WHERE emp_id=? AND MONTH(STR_TO_DATE(month, '%M'))=? AND year=? LIMIT 1");
$prev->bind_param("iii", $row['emp_id'], $prevMonth, $prevYear);
$prev->execute();
$prevData = $prev->get_result()->fetch_assoc();
$prevDeduction = $prevData ? $prevData['total_deduction'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payslip - <?php echo htmlspecialchars($row['full_name']); ?></title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #f2f4f8;
        margin: 0;
        padding: 30px;
    }
    .payslip {
        background: #fff;
        max-width: 850px;
        margin: auto;
        padding: 35px 40px;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.1);
    }
    /* ---------- Header ---------- */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #002147; /* Dark Blue */
        color: #fff;
        padding: 15px 25px;
        border-radius: 10px;
    }
    .header img {
        height: 60px;
        border-radius: 6px;
        background: #fff;
        padding: 5px;
    }
    .company-details {
        text-align: right;
        line-height: 1.4;
    }
    .company-details h2 {
        margin: 0;
        font-size: 20px;
        color: #fff;
    }
    .company-details p {
        margin: 2px 0;
        font-size: 13px;
        color: #d9e2ec;
    }
    /* ---------- Employee Info ---------- */
    .emp-info {
        margin-top: 25px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        background: #fafbfc;
        padding: 20px;
        border: 1px solid #e5e8eb;
        border-radius: 10px;
    }
    .emp-info p {
        margin: 0;
        color: #333;
        font-size: 14px;
    }
    .emp-info b {
        color: #002147;
    }
    /* ---------- Headings ---------- */
    h3 {
        margin-top: 35px;
        font-size: 18px;
        border-bottom: 2px solid #002147;
        padding-bottom: 6px;
        color: #002147;
        letter-spacing: 0.3px;
    }
    /* ---------- Table ---------- */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px 12px;
        text-align: left;
        font-size: 14px;
    }
    th {
        background-color: #002147;
        color: white;
        font-weight: 600;
    }
    tr:nth-child(even) {
        background: #f9fafb;
    }
    .net {
        background: #f4f6f8;
        font-weight: bold;
    }
    /* ---------- Footer ---------- */
    .footer {
        text-align: center;
        margin-top: 30px;
        color: #777;
        font-size: 13px;
    }
    button {
        margin-top: 10px;
        background: #002147;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.3s;
    }
    button:hover {
        background: #01326b;
    }
</style>
</head>
<body>

<div class="payslip">
    <!-- ======= Header ======= -->
    <div class="header">
        <img src="logo.png" alt="Company Logo">
        <div class="company-details">
            <h2>WorkFusion Pvt. Ltd.</h2>
            <p>Ecc Prayagraj, Uttar Pradesh</p>
            <p>Email: Admin@workfusion.in | Phone: +91 98765 43210</p>
        </div>
    </div>

    <!-- ======= Employee Info ======= -->
    <div class="emp-info">
        <p><b>Employee Name:</b> <?php echo htmlspecialchars($row['full_name']); ?></p>
        <p><b>Department:</b> <?php echo htmlspecialchars($row['department']); ?></p>
        <p><b>Designation:</b> <?php echo htmlspecialchars($row['designation']); ?></p>
        <p><b>Month:</b> <?php echo htmlspecialchars($row['month'] . ' ' . $row['year']); ?></p>
        <p><b>Bank Name:</b> <?php echo htmlspecialchars($row['bank_name']); ?></p>
        <p><b>Account No:</b> <?php echo htmlspecialchars($row['account_no']); ?></p>
        <p><b>IFSC Code:</b> <?php echo htmlspecialchars($row['ifsc']); ?></p>
        <p><b>Status:</b> <?php echo htmlspecialchars($row['status']); ?></p>
    </div>

    <!-- ======= Salary Summary ======= -->
    <h3>Salary Summary</h3>
    <table>
        <tr>
            <th>Earnings</th>
            <th>Amount (₹)</th>
            <th>Deductions</th>
            <th>Amount (₹)</th>
        </tr>
        <tr>
            <td>Basic Salary</td>
            <td><?php echo $row['basic_salary']; ?></td>
            <td>Other Allowance</td>
            <td><?php echo $row['other_allowance']; ?></td>
        </tr>
        <tr>
            <td>Medical Allowance</td>
            <td><?php echo $row['medical_allowance']; ?></td> 
              <td>Leave Deduction</td>
            <td><?php echo $row['leave_deduction']; ?></td> 
          
        </tr>
        <tr>
         
             <td>House Rent Allowance</td>
            <td><?php echo $row['house_rent']; ?></td>  
            <td>Total Deductions</td>
            <td><?php echo $row['total_deduction'] + $prevDeduction; ?></td>
        </tr>
        <tr class="net">
            <td>Gross Salary</td>
            <td><?php echo $row['gross_salary']; ?></td>
            <td>Net Pay (This Month)</td>
            <td><b><?php echo $row['net_pay']; ?></b></td>
        </tr>
    </table>

    <!-- ======= Footer ======= -->
    <div class="footer">
        <p>This is a system-generated payslip. No signature required.</p>
        <button onclick="window.print()">🖨️ Print Payslip</button>
    </div>
</div>

</body>
</html>
