<?php
include "Connect.php";

$emp_id = $_GET['emp_id'];
$start = $_GET['start'];
$end = $_GET['end'];

$response = ["hasAbsent" => false, "absentDates" => []];

// Get absent records from attendance table
$sql = "SELECT date FROM attendance 
        WHERE emp_id = '$emp_id' 
        AND status = 'Absent' 
        AND date BETWEEN '$start' AND '$end'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $response["hasAbsent"] = true;
    $response["absentDates"][] = $row['date'];
  }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
