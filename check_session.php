<?php
// ── check_session.php ────────────────────────────────────────────────────────
// Called by static HTML pages via fetch() to determine login state.
// Returns JSON so the page can update its nav dynamically.

header('Content-Type: application/json');
header('Cache-Control: no-store');

include 'db.php';
include 'session.php';

$logged_in = is_logged_in($conn);
$conn->close();

if ($logged_in) {
    echo json_encode([
        'logged_in'  => true,
        'first_name' => $_SESSION['first_name'],
        'last_name'  => $_SESSION['last_name'],
        'email'      => $_SESSION['email'],
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
