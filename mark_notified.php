<?php
require "Connect.php";

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE leaves SET notified=1 WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $stmt->close();
}
?>
