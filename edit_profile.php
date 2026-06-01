<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");

    exit();
}

$user_id =
$_SESSION['user_id'];

$sql =
"SELECT * FROM users
WHERE id='$user_id'";

$result =
mysqli_query($conn,$sql);

$user =
mysqli_fetch_assoc($result);

if(isset($_POST['update'])){

    $fullname =
    mysqli_real_escape_string(
    $conn,
    $_POST['fullname']
    );

    $email =
    mysqli_real_escape_string(
    $conn,
    $_POST['email']
    );

    $mobile =
    mysqli_real_escape_string(
    $conn,
    $_POST['mobile']
    );

    $update_sql =
    "UPDATE users SET

    fullname='$fullname',
    email='$email',
    mobile='$mobile'

    WHERE id='$user_id'";

    mysqli_query(
    $conn,
    $update_sql
    );

    $_SESSION['fullname'] =
    $fullname;

    header(
    "Location: profile.php"
    );

    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Profile</title>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<style>

*{

    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    margin:0;

    font-family:Arial;

    background:#f4f4f4;

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;

    padding:20px;
}

.box{

    background:white;

    width:450px;

    padding:40px;

    border-radius:12px;

    box-shadow:0 0 15px rgba(0,0,0,0.1);
}

.icon{

    text-align:center;

    font-size:55px;

    margin-bottom:10px;
}

h2{

    text-align:center;

    margin-bottom:30px;

    color:#111827;

    font-size:35px;
}

input{

    width:100%;

    padding:14px;

    margin-top:15px;

    border:1px solid #ccc;

    border-radius:6px;

    box-sizing:border-box;

    font-size:16px;

    outline:none;
}

input:focus{

    border-color:#2563eb;
}

button{

    width:100%;

    padding:14px;

    margin-top:20px;

    background:#2563eb;

    color:white;

    border:none;

    border-radius:6px;

    cursor:pointer;

    font-size:16px;

    transition:0.3s;
}

button:hover{

    background:#1d4ed8;
}

.back{

    text-align:center;

    margin-top:20px;
}

.back a{

    text-decoration:none;

    color:#2563eb;

    font-weight:bold;
}

.back a:hover{

    text-decoration:underline;
}

@media(max-width:500px){

    .box{

        width:100%;

        padding:30px 20px;
    }

    h2{

        font-size:28px;
    }
}

</style>

</head>

<body>

<div class="box">

<div class="icon">

👤

</div>

<h2>Edit Profile</h2>

<form method="POST">

<input
type="text"
name="fullname"
placeholder="Full Name"
value="<?php
echo $user['fullname'];
?>"
required>

<input
type="email"
name="email"
placeholder="Email Address"
value="<?php
echo $user['email'];
?>"
required>

<input
type="text"
name="mobile"
placeholder="Mobile Number"
value="<?php
echo $user['mobile'];
?>">

<button
type="submit"
name="update">

Update Profile

</button>

</form>

<div class="back">

<a href="profile.php">

← Back to Profile

</a>

</div>

</div>

</body>
</html>