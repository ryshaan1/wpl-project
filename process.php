<?php
include 'db.php';
include 'session.php';

ensure_db_columns($conn);

start_session();

function clean_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $action = isset($_POST["action"]) ? $_POST["action"] : "";

    // ── SIGN-IN ──────────────────────────────────────────────────
    if($action === "login"){
        $email       = isset($_POST["email"])       ? clean_input($_POST["email"]) : "";
        $password    = isset($_POST["password"])    ? $_POST["password"]           : "";
        $remember_me = isset($_POST["remember_me"]) && $_POST["remember_me"] === "1";

        if(empty($email) || empty($password)){
            ?><!DOCTYPE html><html><head><title>VoltGrid</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
            <style>body{font-family:'Outfit',sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.card{background:#fff;padding:36px 44px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;max-width:380px;width:100%}h3{color:#EF4444;margin-bottom:16px}a{color:#171a20;font-weight:600}</style>
            </head><body><div class="card"><h3>Email and password are required.</h3><a href="index.html#signin">&#8592; Go back</a></div></body></html><?php
            exit();
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            ?><!DOCTYPE html><html><head><title>VoltGrid</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
            <style>body{font-family:'Outfit',sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.card{background:#fff;padding:36px 44px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;max-width:380px;width:100%}h3{color:#EF4444;margin-bottom:16px}a{color:#171a20;font-weight:600}</style>
            </head><body><div class="card"><h3>Invalid email format.</h3><a href="index.html#signin">&#8592; Go back</a></div></body></html><?php
            exit();
        }

        $stmt = $conn->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows === 0){
            $stmt->close(); $conn->close();
            ?><!DOCTYPE html><html><head><title>VoltGrid</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
            <style>body{font-family:'Outfit',sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.card{background:#fff;padding:36px 44px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;max-width:380px;width:100%}h3{color:#EF4444;margin-bottom:16px}a{color:#171a20;font-weight:600;display:inline-block;margin:6px 8px}
            </style></head><body><div class="card"><h3>No account found with that email.</h3><a href="index.html#signin">&#8592; Sign In</a><a href="register.html">Create Account</a></div></body></html><?php
            exit();
        }

        $stmt->bind_result($user_id, $first_name, $last_name, $hashed);
        $stmt->fetch();
        $stmt->close();

        if(!password_verify($password, $hashed)){
            $conn->close();
            ?><!DOCTYPE html><html><head><title>VoltGrid</title>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
            <style>body{font-family:'Outfit',sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.card{background:#fff;padding:36px 44px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;max-width:380px;width:100%}h3{color:#EF4444;margin-bottom:16px}a{color:#171a20;font-weight:600}</style>
            </head><body><div class="card"><h3>Incorrect password. Please try again.</h3><a href="index.html#signin">&#8592; Go back</a></div></body></html><?php
            exit();
        }

        // ── Create the session ────────────────────────────────────
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user_id;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name']  = $last_name;
        $_SESSION['email']      = $email;

        // ── Remember-me cookie (30 days) ─────────────────────────
        if ($remember_me) {
            set_remember_cookie($conn, $user_id);
        }

        $conn->close();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
        <title>VoltGrid — Signed In</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
        body{font-family:'Outfit',sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .card{background:#fff;padding:40px 48px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;max-width:400px;width:100%}
        .chk{width:60px;height:60px;background:#171a20;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;color:#fff;margin:0 auto 20px}
        h2{font-size:24px;font-weight:600;margin-bottom:8px}
        p{color:rgba(0,0,0,.45);font-size:14px;margin-bottom:24px}
        .btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
        a{display:inline-block;padding:12px 24px;border-radius:5px;text-decoration:none;font-size:14px;font-weight:500}
        .a1{background:#171a20;color:#fff}.a1:hover{background:#000}
        .a2{background:rgba(0,0,0,.07);color:#171a20}.a2:hover{background:rgba(0,0,0,.12)}
        </style>
        </head>
        <body>
        <div class="card">
            <div class="chk">&#10003;</div>
            <h2>Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p>You are signed in as <strong><?php echo htmlspecialchars($email); ?></strong>.</p>
            <?php if($remember_me): ?>
            <p style="font-size:12px;color:#6B7280;margin-top:-12px;">You will stay signed in for 30 days on this device.</p>
            <?php endif; ?>
            <div class="btns">
                <a href="booking.html" class="a1">Book a Slot</a>
                <a href="dashboard.php" class="a2">My Dashboard</a>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit();
    }

    header("Location: index.html");
    exit();

} else {
    header("Location: index.html");
    exit();
}
?>
