<?php
// ── session.php — centralised session & cookie helper ──────────────────────

define('REMEMBER_COOKIE', 'voltgrid_remember');
define('COOKIE_LIFETIME', 60 * 60 * 24 * 30); // 30 days

/**
 * Ensure the DB has the columns sessions/cookies need.
 * Runs ALTER TABLE only if the column is actually missing — safe to call on
 * every request (the INFORMATION_SCHEMA check costs ~1ms).
 */
function ensure_db_columns($conn) {
    $db = $conn->query("SELECT DATABASE()")->fetch_row()[0];

    $cols = [];
    $r = $conn->query(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = '$db'
         AND TABLE_NAME IN ('users','bookings')"
    );
    while ($row = $r->fetch_row()) $cols[] = $row[0];

    if (!in_array('remember_token', $cols))
        $conn->query("ALTER TABLE users ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL");
    if (!in_array('token_expiry', $cols))
        $conn->query("ALTER TABLE users ADD COLUMN token_expiry DATETIME DEFAULT NULL");
    if (!in_array('user_id', $cols))
        $conn->query("ALTER TABLE bookings ADD COLUMN user_id INT DEFAULT NULL");
    if (!in_array('created_at', $cols))
        $conn->query("ALTER TABLE bookings ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
}

/**
 * Start the session (safe to call multiple times).
 */
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('voltgrid_sess');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/**
 * Return true if a user is currently logged in via session OR a valid
 * "remember me" cookie.  If the cookie is valid, it silently restores
 * the session so the rest of the request sees $_SESSION populated.
 */
function is_logged_in($conn = null) {
    start_session();

    if (!empty($_SESSION['user_id'])) {
        return true;
    }

    if (!empty($_COOKIE[REMEMBER_COOKIE]) && $conn !== null) {
        $token = $_COOKIE[REMEMBER_COOKIE];
        $token_hash = hash('sha256', $token);

        $stmt = $conn->prepare(
            "SELECT id, first_name, last_name, email
             FROM users
             WHERE remember_token = ? AND token_expiry > NOW()"
        );
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($uid, $fn, $ln, $em);
            $stmt->fetch();
            $stmt->close();

            $_SESSION['user_id']    = $uid;
            $_SESSION['first_name'] = $fn;
            $_SESSION['last_name']  = $ln;
            $_SESSION['email']      = $em;

            set_remember_cookie($conn, $uid);
            return true;
        }
        $stmt->close();
        clear_remember_cookie();
    }

    return false;
}

/**
 * Require the user to be logged in; redirect to sign-in if not.
 */
function require_login($conn, $redirect = 'index.html#signin') {
    if (!is_logged_in($conn)) {
        header("Location: $redirect");
        exit();
    }
}

/**
 * Generate a new remember-me token, store its hash in the DB,
 * and drop the cookie in the browser.
 */
function set_remember_cookie($conn, $user_id) {
    $token      = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expiry     = date('Y-m-d H:i:s', time() + COOKIE_LIFETIME);

    $stmt = $conn->prepare(
        "UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?"
    );
    $stmt->bind_param("ssi", $token_hash, $expiry, $user_id);
    $stmt->execute();
    $stmt->close();

    setcookie(REMEMBER_COOKIE, $token, [
        'expires'  => time() + COOKIE_LIFETIME,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Delete the remember-me cookie from the browser and clear the DB token.
 */
function clear_remember_cookie($conn = null, $user_id = null) {
    if ($conn !== null && $user_id !== null) {
        $stmt = $conn->prepare(
            "UPDATE users SET remember_token = NULL, token_expiry = NULL WHERE id = ?"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    setcookie(REMEMBER_COOKIE, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
?>
