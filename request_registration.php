<?php
function clean($data){
    return htmlspecialchars(trim($data));
}

$name = clean($_REQUEST['name']);
$email = clean($_REQUEST['email']);
$vehicle = clean($_REQUEST['vehicle']);
$type = clean($_REQUEST['type']);
$slot = clean($_REQUEST['slot']);

if(empty($name) || empty($email) || empty($vehicle)){
    die("All fields are required!");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    die("Invalid Email Format!");
}
?>

<h2>REQUEST Method Used</h2>
<p><b>Name:</b> <?php echo $name; ?></p>
<p><b>Email:</b> <?php echo $email; ?></p>
<p><b>Vehicle No:</b> <?php echo $vehicle; ?></p>
<p><b>Type:</b> <?php echo $type; ?></p>
<p><b>Allocated Slot:</b> <?php echo $slot; ?></p>
<p style="color:purple;">REQUEST can handle both GET & POST</p>