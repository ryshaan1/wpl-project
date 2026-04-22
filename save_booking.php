<?php
header('Content-Type: application/json');

include 'db.php';
include 'session.php';

ensure_db_columns($conn);
is_logged_in($conn); // Starts session and restores the user from cookies if necessary

$user_id = $_SESSION['user_id'] ?? null;

// Prepare data using centralized helper
$station        = clean_input($_POST['station']        ?? "");
$date           = clean_input($_POST['date']           ?? "");
$time_slot      = clean_input($_POST['time_slot']      ?? "");
$duration       = clean_input($_POST['duration']       ?? "");
$vehicle_number = clean_input($_POST['vehicle_number'] ?? "");
$booking_ref    = clean_input($_POST['booking_ref']    ?? "");
$total_amount   = clean_input($_POST['total_amount']   ?? "0");

// ── Validation ─────────────────────────────────────────────────────────────

if (empty($station) || empty($date) || empty($time_slot) || empty($duration)) {
    echo json_encode(["success" => false, "error" => "Required fields missing."]);
    exit();
}

// ── Database Interaction ───────────────────────────────────────────────────

$stmt = $conn->prepare(
    "INSERT INTO bookings (user_id, station, date, time_slot, duration, vehicle_number, booking_ref, total_amount)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("isssssss", $user_id, $station, $date, $time_slot, $duration, $vehicle_number, $booking_ref, $total_amount);

if ($stmt->execute()) {
    echo json_encode([
        "success"   => true,
        "id"        => $conn->insert_id,
        "ref"       => $booking_ref,
        "logged_in" => $user_id !== null,
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
