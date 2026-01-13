<?php
include "Connect.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM leaves WHERE id = $id AND status = 'Pending'";
    if ($conn->query($sql)) {
        echo json_encode(["message" => "Leave request deleted successfully."]);
    } else {
        echo json_encode(["message" => "Error deleting leave."]);
    }
} else {
    echo json_encode(["message" => "Invalid request."]);
}
?>
