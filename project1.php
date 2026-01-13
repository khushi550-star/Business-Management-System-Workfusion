<?php
session_start();
include "connect.php";
header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['emp_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$emp_id = $_SESSION['emp_id'];
$action = $_POST['action'] ?? '';

/* ------------------- ADD PROJECT ------------------- */
if ($action === 'add') {
    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO projects (emp_id, project_name, description, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $emp_id, $project_name, $description, $start_date, $end_date);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Project added successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add project']);
    }
    $stmt->close();
    exit;
}

/* ------------------- UPDATE STATUS ------------------- */
if ($action === 'update') {
    $project_id = $_POST['project_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE projects SET status=? WHERE project_id=? AND emp_id=?");
    $stmt->bind_param("sii", $status, $project_id, $emp_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }
    $stmt->close();
    exit;
}

/* ------------------- FETCH PROJECTS ------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE emp_id=? ORDER BY start_date DESC");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $projects]);
    $stmt->close();
    exit;
}

/* ------------------- INVALID ------------------- */
echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
$conn->close();
?>
