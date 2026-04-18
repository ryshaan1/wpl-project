<?php
include 'db.php';

function clean($data){
    return htmlspecialchars(trim($data));
}

$first_name     = isset($_POST['first_name'])     ? clean($_POST['first_name'])     : "";
$last_name      = isset($_POST['last_name'])      ? clean($_POST['last_name'])      : "";
$email          = isset($_POST['email'])          ? clean($_POST['email'])          : "";
$phone          = isset($_POST['phone'])          ? clean($_POST['phone'])          : "";
$city           = isset($_POST['city'])           ? clean($_POST['city'])           : "";
$vehicle_model  = isset($_POST['vehicle_model'])  ? clean($_POST['vehicle_model'])  : "";
$vehicle_number = isset($_POST['vehicle_number']) ? clean($_POST['vehicle_number']) : "";
$connector_type = isset($_POST['connector_type']) ? clean($_POST['connector_type']) : "";
$password       = isset($_POST['password'])       ? $_POST['password']              : "";
$confirm        = isset($_POST['confirm_password'])? $_POST['confirm_password']     : "";

// Validation
if(empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)){
    die("<h3 style='color:red;'>All required fields are missing!</h3>");
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("<h3 style='color:red;'>Invalid email format!</h3>");
}
if($password !== $confirm){
    die("<h3 style='color:red;'>Passwords do not match!</h3>");
}
if(strlen($password) < 8){
    die("<h3 style='color:red;'>Password must be at least 8 characters!</h3>");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO users (first_name, last_name, email, phone, city, vehicle_model, vehicle_number, connector_type, password)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssssssss",
    $first_name, $last_name, $email, $phone, $city,
    $vehicle_model, $vehicle_number, $connector_type, $hashed
);

if($stmt->execute()){
?>
<!DOCTYPE html>
<html>
<head>
<title>VoltGrid — Registration Successful</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body{font-family:'Outfit',sans-serif;background:#f4f4f4;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.card{background:#fff;padding:40px 48px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,.08);text-align:center;max-width:420px;width:100%}
.chk{width:60px;height:60px;background:#171a20;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:26px;color:#fff;margin:0 auto 20px}
h2{font-size:24px;font-weight:600;margin-bottom:8px}
p{color:rgba(0,0,0,.45);font-size:14px;margin-bottom:24px;line-height:1.6}
.btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
a{display:inline-block;padding:12px 24px;border-radius:5px;text-decoration:none;font-size:14px;font-weight:500}
.a1{background:#171a20;color:#fff}.a1:hover{background:#000}
.a2{background:rgba(0,0,0,.07);color:#171a20}.a2:hover{background:rgba(0,0,0,.12)}
</style>
</head>
<body>
<div class="card">
  <div class="chk">✓</div>
  <h2>Account Created!</h2>
  <p>Welcome, <strong><?php echo $first_name . ' ' . $last_name; ?></strong>.<br>Your VoltGrid account is ready to use.</p>
  <div class="btns">
    <a href="login.html" class="a1">Sign In</a>
    <a href="booking.html" class="a2">Book a Slot</a>
  </div>
</div>
</body>
</html>
<?php
} else {
    if($conn->errno === 1062){
        echo "<h3 style='color:red;'>An account with this email already exists. <a href='login.html'>Sign in instead</a>.</h3>";
    } else {
        // Column mismatch fallback — retry with only base columns
        $stmt2 = $conn->prepare(
            "INSERT INTO users (first_name, last_name, email, phone, city, password)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt2->bind_param("ssssss", $first_name, $last_name, $email, $phone, $city, $hashed);
        if($stmt2->execute()){
            echo "<script>window.location='post_registration.php?success=1'</script>";
            // Show success
            echo "<h2 style='font-family:sans-serif;color:#171a20'>✓ Account created! <a href='login.html'>Sign in</a></h2>";
        } else {
            echo "<h3 style='color:red;'>Registration failed: " . htmlspecialchars($stmt2->error) . "</h3>";
        }
        $stmt2->close();
    }
}

$stmt->close();
$conn->close();
?>
