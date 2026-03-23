<?php
include "database.php";

$station = $_POST['station'];
$date = $_POST['date'];
$time = $_POST['time_slot'];
$duration = $_POST['duration'];
$vehicle = $_POST['vehicle_number'];
$total = $_POST['total_amount'];

$sql = "INSERT INTO bookings 
VALUES ('', '$station', '$date', '$time', '$duration', '$vehicle', '$total')";

mysqli_query($conn, $sql);

echo "Booking Successful!";
?>