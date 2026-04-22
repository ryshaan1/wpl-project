<?php
include 'db.php';
include 'session.php';

require_login($conn);

// Fetch this user's recent bookings
$bookings = [];
$uid = $_SESSION['user_id'];
$stmt = $conn->prepare(
    "SELECT b.station AS station_code, s.name AS station_name, b.date, b.time_slot, b.duration, b.total_amount, b.booking_ref, b.created_at
     FROM bookings b
     LEFT JOIN stations s ON b.station = s.station_code
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC
     LIMIT 10"
);
if ($stmt) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VoltGrid — My Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Outfit',sans-serif;background:#f4f4f4;min-height:100vh}
header{background:#171a20;color:#fff;padding:16px 32px;display:flex;align-items:center;justify-content:space-between}
header h1{font-size:20px;font-weight:700;letter-spacing:-0.5px}
header nav a{color:rgba(255,255,255,.75);text-decoration:none;font-size:14px;margin-left:20px;font-weight:500}
header nav a:hover{color:#fff}
.main{max-width:900px;margin:40px auto;padding:0 24px}
.greeting{font-size:26px;font-weight:700;color:#171a20;margin-bottom:4px}
.sub{color:rgba(0,0,0,.45);font-size:14px;margin-bottom:32px}
.session-box{background:#fff;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.06);padding:24px 28px;margin-bottom:28px}
.session-box h2{font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:rgba(0,0,0,.4);margin-bottom:16px}
.kv{display:flex;gap:12px;flex-wrap:wrap}
.kv .item{background:#f9f9f9;border:1px solid #e5e5e5;border-radius:6px;padding:10px 16px;min-width:160px}
.kv .item .label{font-size:11px;color:rgba(0,0,0,.4);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px}
.kv .item .val{font-size:14px;font-weight:600;color:#171a20;word-break:break-all}
.bookings h2{font-size:15px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:rgba(0,0,0,.4);margin-bottom:16px}
table{width:100%;background:#fff;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.06);border-collapse:collapse;overflow:hidden}
thead tr{background:#171a20;color:#fff}
th{padding:12px 16px;font-size:13px;font-weight:600;text-align:left}
td{padding:12px 16px;font-size:13px;border-bottom:1px solid #f0f0f0;color:#333}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafafa}
.empty{background:#fff;border-radius:8px;padding:40px;text-align:center;color:rgba(0,0,0,.35);font-size:14px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.btns{display:flex;gap:12px;margin-top:28px;flex-wrap:wrap}
a.btn{display:inline-block;padding:11px 22px;border-radius:5px;text-decoration:none;font-size:14px;font-weight:500}
.btn-dark{background:#171a20;color:#fff}.btn-dark:hover{background:#000}
.btn-light{background:rgba(0,0,0,.07);color:#171a20}.btn-light:hover{background:rgba(0,0,0,.12)}
.btn-red{background:#FEE2E2;color:#DC2626}.btn-red:hover{background:#FECACA}
</style>
</head>
<body>
<header>
    <h1><span style="filter:drop-shadow(0 0 6px #FFD700) drop-shadow(0 0 12px #FFA500);display:inline-block;">⚡</span> VoltGrid</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="booking.html">Book</a>
        <a href="logout.php">Sign Out</a>
    </nav>
</header>

<div class="main">
    <div class="greeting">Hello, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! &#128075;</div>
    <div class="sub">Here's your account overview and recent bookings.</div>

    <!-- Session info panel -->
    <div class="session-box">
        <h2>Session Info</h2>
        <div class="kv">
            <div class="item">
                <div class="label">User ID</div>
                <div class="val"><?php echo htmlspecialchars($_SESSION['user_id']); ?></div>
            </div>
            <div class="item">
                <div class="label">Name</div>
                <div class="val"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></div>
            </div>
            <div class="item">
                <div class="label">Email</div>
                <div class="val"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
            </div>
            <div class="item">
                <div class="label">Session ID</div>
                <div class="val" style="font-size:11px;"><?php echo session_id(); ?></div>
            </div>
            <div class="item">
                <div class="label">Remember-me Cookie</div>
                <div class="val"><?php echo isset($_COOKIE['voltgrid_remember']) ? '&#10003; Active (30 days)' : 'Not set'; ?></div>
            </div>
        </div>
    </div>

    <!-- Recent bookings -->
    <div class="bookings">
        <h2>Recent Bookings</h2>
        <?php if (empty($bookings)): ?>
        <div class="empty">No bookings yet. <a href="booking.html" style="color:#171a20;font-weight:600;">Book your first slot &#8594;</a></div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Station</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Duration</th>
                    <th>Amount</th>
                    <th>Booked On</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($bookings as $b): ?>
                <tr>
                    <td style="font-family:monospace;font-weight:600;"><?php echo htmlspecialchars($b['booking_ref'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($b['station_name'] ?? $b['station_code']); ?></td>
                    <td><?php echo htmlspecialchars($b['date']); ?></td>
                    <td><?php echo htmlspecialchars($b['time_slot']); ?></td>
                    <td><?php echo htmlspecialchars($b['duration']); ?></td>
                    <td>₹<?php echo number_format($b['total_amount'], 2); ?></td>
                    <td><?php echo date('d M Y', strtotime($b['created_at'])); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="btns">
        <a href="booking.html" class="btn btn-dark">+ New Booking</a>
        <a href="index.html" class="btn btn-light">Home</a>
        <a href="logout.php" class="btn btn-red">Sign Out</a>
    </div>
</div>
</body>
</html>