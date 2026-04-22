<?php
include 'db.php';
include 'session.php';

// Prepare data
$first_name     = clean_input($_POST['first_name']     ?? "");
$last_name      = clean_input($_POST['last_name']      ?? "");
$email          = clean_input($_POST['email']          ?? "");
$phone          = clean_input($_POST['phone']          ?? "");
$city           = clean_input($_POST['city']           ?? "");
$vehicle_model  = clean_input($_POST['vehicle_model']  ?? "");
$vehicle_number = clean_input($_POST['vehicle_number'] ?? "");
$connector_type = clean_input($_POST['connector_type'] ?? "");
$password       = $_POST['password']                   ?? "";
$confirm        = $_POST['confirm_password']           ?? "";

// ── Validation ─────────────────────────────────────────────────────────────

if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
    render_message_card("Missing Info", "All required fields must be filled.", "error", ["Go back" => "register.html"]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_message_card("Invalid Email", "Please provide a valid email address.", "error", ["Go back" => "register.html"]);
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    render_message_card("Invalid Phone", "Please enter exactly 10 digits (excluding +91).", "error", ["Go back" => "register.html"]);
}

// Prepend country code for storage
$phone_with_code = "+91" . $phone;

if ($password !== $confirm) {
    render_message_card("Match Error", "Passwords do not match.", "error", ["Go back" => "register.html"]);
}

if (strlen($password) < 8) {
    render_message_card("Weak Password", "Password must be at least 8 characters long.", "error", ["Go back" => "register.html"]);
}

// ── Database Interaction ───────────────────────────────────────────────────

ensure_db_columns($conn);
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (first_name, last_name, email, phone, city, vehicle_model, vehicle_number, connector_type, password)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    render_message_card("Server Error", "Database error: " . $conn->error, "error");
}

$stmt->bind_param("sssssssss", 
    $first_name, $last_name, $email, $phone_with_code, $city, 
    $vehicle_model, $vehicle_number, $connector_type, $hashed
);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    start_session();
    session_regenerate_id(true);

    $_SESSION['user_id']    = $user_id;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name']  = $last_name;
    $_SESSION['email']      = $email;

    render_message_card(
        "Welcome to VoltGrid!",
        "Your account has been created. You are now <strong>signed in</strong> as " . htmlspecialchars($first_name) . ".",
        "success",
        ["Book your first Slot" => "booking.html", "View Dashboard" => "dashboard.php"]
    );
} else {
    if ($conn->errno === 1062) {
        render_message_card("Already Registered", "An account with this email already exists.", "error", ["Sign in instead" => "index.html#signin"]);
    } else {
        render_message_card("Registration Failed", "Error: " . $stmt->error, "error", ["Try again" => "register.html"]);
    }
}

$stmt->close();
$conn->close();
