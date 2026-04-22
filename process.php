<?php
include 'db.php';
include 'session.php';

ensure_db_columns($conn);
start_session();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit();
}

$action      = clean_input($_POST["action"] ?? "");
$email       = clean_input($_POST["email"] ?? "");
$password    = $_POST["password"] ?? "";
$remember_me = isset($_POST["remember_me"]) && $_POST["remember_me"] === "1";

if ($action !== "login") {
    header("Location: index.html");
    exit();
}

// ── Validation ─────────────────────────────────────────────────────────────

if (empty($email) || empty($password)) {
    render_message_card("Missing Info", "Email and password are required.", "error", ["Go back" => "index.html#signin"]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    render_message_card("Invalid Email", "Please provide a valid email address.", "error", ["Go back" => "index.html#signin"]);
}

// ── Authentication ─────────────────────────────────────────────────────────

$stmt = $conn->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    render_message_card(
        "Account Not Found", 
        "No account found with that email.", 
        "error", 
        ["Sign In" => "index.html#signin", "Create Account" => "register.html"]
    );
}

$stmt->bind_result($user_id, $first_name, $last_name, $hashed);
$stmt->fetch();
$stmt->close();

if (!password_verify($password, $hashed)) {
    $conn->close();
    render_message_card("Incorrect Password", "Please check your password and try again.", "error", ["Go back" => "index.html#signin"]);
}

// ── Session Management ─────────────────────────────────────────────────────

session_regenerate_id(true);
$_SESSION['user_id']    = $user_id;
$_SESSION['first_name'] = $first_name;
$_SESSION['last_name']  = $last_name;
$_SESSION['email']      = $email;

if ($remember_me) {
    set_remember_cookie($conn, $user_id);
}

$conn->close();

$msg = "You are signed in as <strong>" . htmlspecialchars($email) . "</strong>.";
if ($remember_me) {
    $msg .= "<br><span style='font-size:12px;color:#6B7280;'>Staying signed in for 30 days.</span>";
}

render_message_card(
    "Welcome back, $first_name!",
    $msg,
    "success",
    ["Book a Slot" => "booking.html", "My Dashboard" => "dashboard.php"]
);
?>
