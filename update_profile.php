<?php
require_once "Connect.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['emp_id'];
    $photo_name = null;
$emp_id = $_SESSION['emp_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $targetDir = "uploads/profile/";
    $fileName = basename($_FILES["photo"]["name"]);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = "emp_" . $emp_id . "_" . time() . "." . $ext;
    $targetFilePath = $targetDir . $newFileName;

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
        // ✅ Update photo name in database
        $stmt = $conn->prepare("UPDATE users SET photo=? WHERE id=?");
        $stmt->bind_param("si", $newFileName, $emp_id);
        $stmt->execute();
        $stmt->close();

        // ✅ Update session too (so page updates immediately)
        $_SESSION['photo'] = $newFileName;

        echo "<script>alert('Profile photo updated successfully!'); window.location='employee_profile.php';</script>";
    } else {
        echo "<script>alert('Photo upload failed. Please try again.');</script>";
    }
}


    // === Update other fields ===
    $stmt = $conn->prepare("UPDATE users SET full_name=?, gender=?, dob=?, marital_status=?, phone=?, current_address=?, permanent_address=?, department=?, designation=?, work_location=?, bank_name=?, account_no=?, ifsc=?, upi_id=?, emergency_person=?, emergency_relation=?, emergency_contact=?, qualification=?, experience=?, previous_company=? WHERE id=?");

    $stmt->bind_param(
        "ssssssssssssssssssssi",
        $_POST['full_name'], $_POST['gender'], $_POST['dob'], $_POST['marital_status'],
        $_POST['phone'], $_POST['current_address'], $_POST['permanent_address'],
        $_POST['department'], $_POST['designation'], $_POST['work_location'],
        $_POST['bank_name'], $_POST['account_no'], $_POST['ifsc'], $_POST['upi_id'],
        $_POST['emergency_person'], $_POST['emergency_relation'], $_POST['emergency_contact'],
        $_POST['qualification'], $_POST['experience'], $_POST['previous_company'], $emp_id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: employee_profile.php?success=1");
    exit;
}
?>
