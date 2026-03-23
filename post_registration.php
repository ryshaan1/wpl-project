<?php
include "database.php";

$first = $_POST['first_name'];
$last = $_POST['last_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$city = $_POST['city'];
$model = $_POST['vehicle_model'];
$number = $_POST['vehicle_number'];
$type = $_POST['connector_type'];
$password = $_POST['password'];

$sql = "INSERT INTO users 
VALUES ('', '$first', '$last', '$email', '$phone', '$city', '$model', '$number', '$type', '$password')";

mysqli_query($conn, $sql);

echo "Registration Done!";
?>