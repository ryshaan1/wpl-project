<?php
header('Content-Type: application/json');

include 'db.php';

function clean($d){ return htmlspecialchars(trim($d)); }

$name    = isset($_POST['full_name']) ? clean($_POST['full_name']) : "";
$email   = isset($_POST['email'])     ? clean($_POST['email'])     : "";
$message = isset($_POST['message'])   ? clean($_POST['message'])   : "";
$rating  = isset($_POST['rating'])    ? (int)$_POST['rating']      : 0;
$type    = isset($_POST['type'])      ? clean($_POST['type'])      : "Feedback";

if(empty($message)){
    echo json_encode(["success" => false, "error" => "Message is required"]);
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO feedback (name, email, type, message, rating)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssssi", $name, $email, $type, $message, $rating);

if($stmt->execute()){
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
