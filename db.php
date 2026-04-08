<?php
$conn = new mysqli("localhost", "root", "", "voltgrid");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
