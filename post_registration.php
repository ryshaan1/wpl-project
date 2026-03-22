<?php
include 'db.php';

function clean($data){
    return htmlspecialchars(trim($data));
}

$name = clean($_POST['name']);
$email = clean($_POST['email']);
$vehicle = clean($_POST['vehicle']);
$type = clean($_POST['type']);
$slot = clean($_POST['slot']);

if(empty($name) || empty($email) || empty($vehicle)){
    die("All fields are required!");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("Invalid Email Format!");
}

// Insert into database
$sql = "INSERT INTO bookings (name, email, vehicle_no, vehicle_type, slot)
        VALUES ('$name', '$email', '$vehicle', '$type', '$slot')";

if($conn->query($sql) === TRUE){
    echo "<h2>Booking Successful ✅</h2>";
    echo "<p>Charger slot reserved!</p>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>