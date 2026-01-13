<?php
session_start();
include "Connect.php";

if (!isset($_SESSION['emp_id'])) {
    echo json_encode(['status'=>'error','message'=>'Access denied']);
    exit;
}

$emp_id = $_SESSION['emp_id'];
$type = $_GET['type'] ?? '';

if($type === 'task'){
    $stmt = $conn->prepare("SELECT task_name, task_description, status FROM daily_tasks WHERE assigned_to=? ORDER BY id DESC");
    $stmt->bind_param("i", $emp_id);
} elseif($type === 'project'){
    $stmt = $conn->prepare("SELECT project_name, project_description, start_date, end_date, status FROM projects WHERE assigned_to=? ORDER BY id DESC");
    $stmt->bind_param("i", $emp_id);
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid type']);
    exit;
}

$stmt->execute();
$res = $stmt->get_result();
$data = [];
while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode(['status'=>'success','data'=>$data]);
$stmt->close();
$conn->close();
?>
