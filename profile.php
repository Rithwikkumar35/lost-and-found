<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");

    exit();
}

$user_id =
$_SESSION['user_id'];

$user_sql =
"SELECT * FROM users
WHERE id='$user_id'";

$user_result =
mysqli_query($conn,$user_sql);

$user =
mysqli_fetch_assoc($user_result);

$item_sql =
"SELECT COUNT(*) AS total_items
FROM items
WHERE user_id='$user_id'";

$item_result =
mysqli_query($conn,$item_sql);

$item_data =
mysqli_fetch_assoc($item_result);

$claim_sql =
"SELECT COUNT(*) AS total_claims
FROM claims
WHERE claimant_id='$user_id'";

$claim_result =
mysqli_query($conn,$claim_sql);

$claim_data =
mysqli_fetch_assoc($claim_result);

$recovered_sql =
"SELECT COUNT(*) AS recovered
FROM items
WHERE user_id='$user_id'
AND status='recovered'";

$recovered_result =
mysqli_query($conn,$recovered_sql);

$recovered_data =
mysqli_fetch_assoc($recovered_result);

?>

<!DOCTYPE html>
<html>

<head>

<title>User Profile</title>

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

    font-family:Arial;

    background:#f4f4f4;
}

header{

    background:#111827;

    color:white;

    padding:20px 40px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    flex-wrap:wrap;
}

header a{

    color:white;

    text-decoration:none;

    margin-left:20px;

    font-size:18px;
}

header a:hover{

    color:#60a5fa;
}

.container{

    max-width:1100px;

    margin:auto;

    padding:40px 20px;
}

.profile-card{

    background:white;

    border-radius:15px;

    padding:40px;

    box-shadow:0 0 15px rgba(0,0,0,0.1);

    text-align:center;

    margin-bottom:40px;
}

.avatar{

    width:120px;

    height:120px;

    background:#2563eb;

    color:white;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

    font-size:45px;

    margin:auto;

    margin-bottom:20px;
}

.profile-card h1{

    color:#111827;

    margin-bottom:10px;
}

.profile-card p{

    color:#555;

    font-size:18px;

    margin-bottom:10px;
}

.stats{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));

    gap:25px;
}

.stat-box{

    background:white;

    padding:35px;

    border-radius:15px;

    text-align:center;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.stat-box h2{

    font-size:45px;

    color:#2563eb;

    margin-bottom:10px;
}

.stat-box p{

    font-size:20px;

    color:#444;
}

.btn-area{

    margin-top:40px;

    text-align:center;
}

.btn{

    display:inline-block;

    margin:10px;

    padding:14px 28px;

    background:#2563eb;

    color:white;

    text-decoration:none;

    border-radius:8px;

    font-size:17px;

    transition:0.3s;
}

.btn:hover{

    background:#1d4ed8;
}

.logout{

    background:red;
}

.logout:hover{

    background:#dc2626;
}

@media(max-width:768px){

    .profile-card{

        padding:25px;
    }

    .avatar{

        width:100px;

        height:100px;

        font-size:35px;
    }

    .profile-card h1{

        font-size:30px;
    }
}

</style>

</head>

<body>

<header>

<h2>TraceBack</h2>

<nav>

<a href="index.php">

Home

</a>

<a href="dashboard.php">

Dashboard

</a>

<a href="browse.php">

Browse

</a>

<a href="my_items.php">

My Items

</a>

<a href="logout.php">

Logout

</a>

</nav>

</header>

<div class="container">

<div class="profile-card">

<div class="avatar">

<?php
echo strtoupper(
substr(
$user['fullname'],
0,
1
)
);
?>

</div>

<h1>

<?php
echo $user['fullname'];
?>

</h1>

<p>

<?php
echo $user['email'];
?>

</p>

<p>

Member of TraceBack LFIS

</p>

</div>

<div class="stats">

<div class="stat-box">

<h2>

<?php
echo $item_data['total_items'];
?>

</h2>

<p>

Posted Items

</p>

</div>

<div class="stat-box">

<h2>

<?php
echo $claim_data['total_claims'];
?>

</h2>

<p>

Total Claims

</p>

</div>

<div class="stat-box">

<h2>

<?php
echo $recovered_data['recovered'];
?>

</h2>

<p>

Recovered Items

</p>

</div>

</div>

<div class="btn-area">

<a
href="edit_profile.php"
class="btn">

Edit Profile

</a>

<a
href="change_password.php"
class="btn">

Change Password

</a>

<a
href="logout.php"
class="btn logout">

Logout

</a>

</div>

</div>

</body>
</html>