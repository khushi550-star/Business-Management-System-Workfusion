<?php
session_start();
include "Connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['emp_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    $medical_file_path = NULL;

    // File upload
    if(isset($_FILES['medical_file']) && $_FILES['medical_file']['error'] === 0){
        $file_tmp = $_FILES['medical_file']['tmp_name'];
        $file_name = time() . '_' . $_FILES['medical_file']['name'];
        $upload_dir = 'uploads/medical/';
        if(!is_dir($upload_dir)){
            mkdir($upload_dir, 0777, true);
        }
        if(move_uploaded_file($file_tmp, $upload_dir.$file_name)){
            $medical_file_path = $upload_dir.$file_name;
        }
    }

    // Check if leave already exists for same date
    $check = $conn->prepare("SELECT * FROM leaves WHERE emp_id=? AND start_date=?");
    $check->bind_param("is", $emp_id, $start_date);
    $check->execute();
    $result = $check->get_result();
    if($result->num_rows > 0){
        echo json_encode(['status'=>false, 'message'=>'Leave already applied for this date!']);
        exit;
    }

    // Insert leave into database
    $stmt = $conn->prepare("INSERT INTO leaves (emp_id, leave_type, start_date, end_date, reason, medical_file, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isssss", $emp_id, $leave_type, $start_date, $end_date, $reason, $medical_file_path);

    if($stmt->execute()){
        echo json_encode(['status'=>true, 'message'=>'Leave request submitted successfully']);
    } else {
        echo json_encode(['status'=>false, 'message'=>'Error submitting leave request']);
    }
}

// ===== GET REQUEST: Fetch leave history =====
if(isset($_GET['emp_id'])){
    $emp_id = intval($_GET['emp_id']);
    $res = $conn->query("SELECT *, CONCAT('uploads/medical/', medical_file) AS medical_file_path FROM leaves WHERE emp_id=$emp_id ORDER BY id DESC");
    $leaves = [];
    while($row = $res->fetch_assoc()){
        $leaves[] = [
            'id' => $row['id'],
            'leave_type' => $row['leave_type'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'reason' => $row['reason'],
            'status' => $row['status'],
            'medical_file' => !empty($row['medical_file']) ? $row['medical_file_path'] : '',
            'notified' => $row['notified']
        ];
    }
    echo json_encode($leaves);
    exit;
}
?>
