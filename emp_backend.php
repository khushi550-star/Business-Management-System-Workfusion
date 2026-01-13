<?php
session_start();
include "Connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(["ok"=>false, "message"=>"Unauthorized"]);
  exit;
}

$user_id = $_SESSION['user_id'];
$meeting_id = $_POST['meeting_id'] ?? 0;

if (!$meeting_id) {
  echo json_encode(["ok"=>false, "message"=>"Invalid meeting ID"]);
  exit;
}

// Check if attendance already marked
$exists = $conn->query("SELECT id FROM attendance WHERE meeting_id=$meeting_id AND user_id=$user_id");
if ($exists->num_rows == 0) {
  $conn->query("INSERT INTO attendance (meeting_id, user_id, marked_at) VALUES ($meeting_id, $user_id, NOW())");
}

// Get meeting link
$link = $conn->query("SELECT meeting_link FROM meetings WHERE id=$meeting_id")->fetch_assoc()['meeting_link'];

echo json_encode(["ok"=>true, "meeting_link"=>$link]);
exit;
?>
