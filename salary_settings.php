<?php
session_start();
include "Connect.php";

// Fetch current global salary settings
$current = $conn->query("SELECT * FROM salary_settings ORDER BY id DESC LIMIT 1")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $basic = floatval($_POST['basic_salary']);
    $medical = floatval($_POST['medical_allowance']);
    $house = floatval($_POST['house_rent']);
    $other = floatval($_POST['other_allowance']);

    // Insert new setting record
    $stmt = $conn->prepare("INSERT INTO salary_settings (basic_salary, medical_allowance, house_rent, other_allowance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("dddd", $basic, $medical, $house, $other);
    $stmt->execute();

    echo "<script>alert('✅ Global Salary Structure Updated Successfully!'); window.location='admin_payroll.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Salary Settings</title>
<style>
body {
    font-family: Arial;
    background: #f7f7f7;
    padding: 30px;
}
.container {
    background: #fff;
    max-width: 600px;
    margin: auto;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
label {
    display: block;
    font-weight: bold;
    margin-top: 15px;
}
input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
button, a.btn {
    background: #333;
    color: #fff;
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    margin-top: 20px;
    cursor: pointer;
    text-decoration: none;
}
button:hover, a.btn:hover {
    background: #555;
}
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
</head>
<body>

<div class="container">
    <h2>⚙️ Update Global Salary Structure</h2>

    <form method="POST">
        <label>Basic Salary</label>
        <input type="number" name="basic_salary" step="0.01" value="<?= $current['basic_salary'] ?? '' ?>" required>

        <label>Medical Allowance</label>
        <input type="number" name="medical_allowance" step="0.01" value="<?= $current['medical_allowance'] ?? '' ?>" required>

        <label>House Rent Allowance</label>
        <input type="number" name="house_rent" step="0.01" value="<?= $current['house_rent'] ?? '' ?>" required>

        <label>Other Allowance</label>
        <input type="number" name="other_allowance" step="0.01" value="<?= $current['other_allowance'] ?? '' ?>" required>

        <div class="form-actions">
            <a href="admin_payroll.php" class="btn">← Back to Payroll</a>
            <button type="submit">💾 Save Settings</button>
        </div>
    </form>
</div>

</body>
</html>
