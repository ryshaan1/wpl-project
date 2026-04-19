<?php
header('Content-Type: application/json');

include 'db.php';

function clean($d){ return htmlspecialchars(trim($d)); }

// ── Common fields ─────────────────────────────────────────────────────────────
$name    = isset($_POST['full_name']) ? clean($_POST['full_name']) : "";
$email   = isset($_POST['email'])     ? clean($_POST['email'])     : "";
$message = isset($_POST['message'])   ? clean($_POST['message'])   : "";
$type    = isset($_POST['type'])      ? clean($_POST['type'])      : "Feedback";

if (empty($message)) {
    echo json_encode(["success" => false, "error" => "Message is required"]);
    exit();
}

// ── Route to the correct table ────────────────────────────────────────────────
switch (strtolower($type)) {

    // ── Report Issue ──────────────────────────────────────────────────────────
    case 'issue':
    case 'report issue':
        $booking_ref     = isset($_POST['booking_reference']) ? clean($_POST['booking_reference']) : null;
        $attachment_path = null;

        // Handle optional file upload
        if (!empty($_FILES['attachment']['tmp_name'])) {
            $upload_dir = __DIR__ . '/uploads/issues/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $ext      = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('issue_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $filename)) {
                $attachment_path = 'uploads/issues/' . $filename;
            }
        }

        $stmt = $conn->prepare(
            "INSERT INTO report_issues (name, email, booking_reference, message, attachment_path)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $email, $booking_ref, $message, $attachment_path);
        break;

    // ── Contact Us ────────────────────────────────────────────────────────────
    case 'contact':
    case 'contact us':
        $phone = isset($_POST['phone']) ? clean($_POST['phone']) : null;

        $stmt = $conn->prepare(
            "INSERT INTO contact_us (name, email, phone, message)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $phone, $message);
        break;

    // ── Feedback (default) ────────────────────────────────────────────────────
    default:
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
        if ($rating < 1 || $rating > 5) { $rating = null; }

        $stmt = $conn->prepare(
            "INSERT INTO feedback (name, email, message, rating)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sssi", $name, $email, $message, $rating);
        break;
}

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
