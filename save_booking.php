<?php
header('Content-Type: application/json');

include 'db.php';
include 'session.php';

ensure_db_columns($conn);

start_session();

// Restore session from remember-me cookie if needed
is_logged_in($conn);

// User ID — null if guest (bookings still allowed without login)
$user_id = $_SESSION['user_id'] ?? null;

function clean($data) {
    return htmlspecialchars(trim($data));
}

$station        = isset($_POST['station'])        ? clean($_POST['station'])        : "";
$date           = isset($_POST['date'])           ? clean($_POST['date'])           : "";
$time_slot      = isset($_POST['time_slot'])      ? clean($_POST['time_slot'])      : "";
$duration       = isset($_POST['duration'])       ? clean($_POST['duration'])       : "";
$vehicle_number = isset($_POST['vehicle_number']) ? clean($_POST['vehicle_number']) : "";
$total_amount   = isset($_POST['total_amount'])   ? clean($_POST['total_amount'])   : "0";

if (empty($station) || empty($date) || empty($time_slot) || empty($duration)) {
    echo json_encode(["success" => false, "error" => "Required fields missing."]);
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO bookings (user_id, station, date, time_slot, duration, vehicle_number, total_amount)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("issssss", $user_id, $station, $date, $time_slot, $duration, $vehicle_number, $total_amount);

if ($stmt->execute()) {
    echo json_encode([
        "success"   => true,
        "id"        => $conn->insert_id,
        "logged_in" => $user_id !== null,
    ]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
