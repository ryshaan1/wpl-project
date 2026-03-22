<?php

// Function to sanitize inputs
function clean_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name = clean_input($_POST["name"]);
    $email = clean_input($_POST["email"]);
    $vehicle = clean_input($_POST["vehicle"]);
    $type = clean_input($_POST["type"]);
    $slot = clean_input($_POST["slot"]);

    // Validation
    if(empty($name) || empty($email) || empty($vehicle)){
        echo "<h3 style='color:red;'>All fields are required!</h3>";
        exit();
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<h3 style='color:red;'>Invalid Email Format!</h3>";
        exit();
    }

    // Simulated charger allocation
    $charger = "";

    if($type == "2-Wheeler"){
        $charger = "Standard EV Charger - S1";
    }
    else{
        $charger = "Fast EV Charger - F2";
    }

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Ryshaan's EV Charger Allocation System</title>

<style>
body{
    font-family: Arial;
    background:#f4f7fb;
}

.container{
    width:50%;
    margin:50px auto;
    background:white;
    padding:25px;
    border-radius:8px;
    box-shadow:0 0 10px rgba(0,0,0,0.2);
}

h2{
    color:#1565C0;
}

.result{
    background:#e3f2fd;
    padding:15px;
    margin-top:20px;
}
</style>

</head>

<body>

<div class="container">

<h2>⚡ Ryshaan's EV Charger Allocation System</h2>

<div class="result">

<h3>Charging Slot Allocation Details</h3>

<p><b>Name:</b> <?php echo $name; ?></p>

<p><b>Email:</b> <?php echo $email; ?></p>

<p><b>Vehicle Number:</b> <?php echo $vehicle; ?></p>

<p><b>Vehicle Type:</b> <?php echo $type; ?></p>

<p><b>Requested Slot:</b> <?php echo $slot; ?></p>

<p><b>Allocated Charger:</b> <?php echo $charger; ?></p>

<p style="color:green;"><b>Status:</b> Charger Successfully Reserved!</p>

</div>

</div>

</body>
</html>