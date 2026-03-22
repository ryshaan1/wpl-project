<?php
$conn = new mysqli("localhost", "root", "", "ev_charger");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>