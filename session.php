<?php
// ── session.php — centralised session & cookie helper ──────────────────────

define('REMEMBER_COOKIE', 'voltgrid_remember');
define('COOKIE_LIFETIME', 60 * 60 * 24 * 30); // 30 days

/**
 * Optimised schema check. Runs only once per request and uses a simpler check.
 */
function ensure_db_columns($conn) {
    static $ensured = false;
    if ($ensured) return;

    $res = $conn->query("SHOW TABLES LIKE 'users'");
    if ($res->num_rows === 0) return; // Table doesn't exist yet, wait for migration

    // Check for remember_token as a proxy for needed updates
    $res = $conn->query("SHOW COLUMNS FROM users LIKE 'remember_token'");
    if ($res->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL");
        $conn->query("ALTER TABLE users ADD COLUMN token_expiry DATETIME DEFAULT NULL");
    }

    $res = $conn->query("SHOW COLUMNS FROM bookings LIKE 'booking_ref'");
    if ($res->num_rows === 0) {
        $conn->query("ALTER TABLE bookings ADD COLUMN user_id INT DEFAULT NULL");
        $conn->query("ALTER TABLE bookings ADD COLUMN booking_ref VARCHAR(10) DEFAULT NULL");
        $conn->query("ALTER TABLE bookings ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    }

    $ensured = true;
}

/**
 * Centralised input cleaning for security/consistency.
 */
function clean_input($data) {
    if (is_null($data)) return "";
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Standardised UI card for success/error messages.
 */
function render_message_card($title, $message, $type = 'info', $links = []) {
    $title_color = ($type === 'error') ? '#EF4444' : ($type === 'success' ? '#10B981' : '#171a20');
    $icon = ($type === 'success') ? '✓' : (($type === 'error') ? '!' : 'i');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>VoltGrid | <?php echo htmlspecialchars($title); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body { font-family:'Outfit',sans-serif; background:#f4f4f4; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
            .card { background:#fff; padding:40px 48px; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.06); text-align:center; max-width:400px; width:90%; animation: slideUp 0.4s ease-out; }
            @keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
            .icon-circle { width:60px; height:60px; background:<?php echo $title_color; ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:26px; color:#fff; margin:0 auto 20px; }
            h2 { font-size:24px; font-weight:600; margin:0 0 12px; color:<?php echo $title_color; ?>; }
            p { color:rgba(0,0,0,.6); font-size:15px; line-height:1.6; margin-bottom:24px; }
            .btns { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
            .btn { display:inline-block; padding:12px 24px; border-radius:6px; text-decoration:none; font-size:14px; font-weight:600; transition: all 0.2s; }
            .primary { background:#171a20; color:#fff; }
            .primary:hover { background:#000; transform: translateY(-1px); }
            .secondary { background:rgba(0,0,0,.05); color:#171a20; }
            .secondary:hover { background:rgba(0,0,0,.1); }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="icon-circle"><?php echo $icon; ?></div>
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <p><?php echo $message; // Already escaped or intentional HTML ?></p>
            <div class="btns">
                <?php foreach ($links as $text => $url): ?>
                    <a href="<?php echo htmlspecialchars($url); ?>" class="btn <?php echo (strpos($text, 'Go back') !== false || strpos($text, 'Dashboard') !== false) ? 'secondary' : 'primary'; ?>">
                        <?php echo htmlspecialchars($text); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

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

function require_login($conn, $redirect = 'login.html') {
    if (!is_logged_in($conn)) {
        header("Location: $redirect");
        exit();
    }
}

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