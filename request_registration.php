<?php
include "database.php";

$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];
$rating = $_POST['rating'];

$sql = "INSERT INTO feedback 
VALUES ('', '$name', '$email', 'feedback', '$message', '$rating')";

mysqli_query($conn, $sql);

echo "Message Sent!";
?>
