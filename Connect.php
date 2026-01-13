<?php
$servername = "localhost";
$username = "root"; // or your DB username
$password = "";     // or your DB password
$dbname = "bms_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>