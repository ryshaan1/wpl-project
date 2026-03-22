<?php
function clean($data){
    return htmlspecialchars(trim($data));
}

$name = clean($_GET['name']);
$email = clean($_GET['email']);
$vehicle = clean($_GET['vehicle']);
$type = clean($_GET['type']);
$slot = clean($_GET['slot']);

if(empty($name) || empty($email) || empty($vehicle)){
    die("All fields are required!");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("Invalid Email Format!");
}
?>

<h2>GET Method Used</h2>
<p><b>Name:</b> <?php echo $name; ?></p>
<p><b>Email:</b> <?php echo $email; ?></p>
<p><b>Vehicle No:</b> <?php echo $vehicle; ?></p>
<p><b>Type:</b> <?php echo $type; ?></p>
<p><b>Allocated Slot:</b> <?php echo $slot; ?></p>
<p style="color:blue;">Data visible in URL (GET Method)</p>