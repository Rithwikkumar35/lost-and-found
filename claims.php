<?php

session_start();

include '../includes/db.php';

if(!isset($_SESSION['admin_id'])){

    header("Location: index.php");

    exit();
}

$sql =
"SELECT claims.*,
items.title,
users.fullname

FROM claims

JOIN items
ON claims.item_id = items.id

JOIN users
ON claims.claimant_id = users.id

ORDER BY claims.id DESC";

$result =
mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Claims</title>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<style>

body{

    margin:0;

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
}

header a{

    color:white;

    text-decoration:none;

    margin-left:20px;
}

.container{

    padding:40px;
}

.title{

    font-size:40px;

    margin-bottom:30px;

    color:#111827;
}

.card{

    background:white;

    padding:25px;

    margin-bottom:25px;

    border-radius:10px;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.card h2{

    margin-bottom:15px;
}

.card p{

    margin:10px 0;

    color:#444;
}

.status{

    font-weight:bold;

    margin-top:15px;
}

.approved{

    color:green;
}

.pending{

    color:orange;
}

.rejected{

    color:red;
}

.btn{

    display:inline-block;

    margin-top:20px;

    padding:10px 20px;

    color:white;

    text-decoration:none;

    border-radius:5px;

    margin-right:10px;
}

.approve{

    background:#16a34a;
}

.reject{

    background:red;
}

.back{

    background:#2563eb;
}

</style>

</head>

<body>

<header>

<h2>TraceBack Admin</h2>

<nav>

<a href="dashboard.php">

Dashboard

</a>

<a href="logout.php">

Logout

</a>

</nav>

</header>

<div class="container">

<h1 class="title">

Manage Claims

</h1>

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<div class="card">

<h2>

<?php
echo $row['title'];
?>

</h2>

<p>

<b>Claimed By:</b>

<?php
echo $row['fullname'];
?>

</p>

<p>

<b>Message:</b>

<?php
echo $row['message'];
?>

</p>

<p class="status">

Status:

<span class="<?php
echo $row['status'];
?>">

<?php
echo ucfirst($row['status']);
?>

</span>

</p>

<?php

if($row['status']=="pending"){

?>

<a
href="approve_claim.php?id=<?php echo $row['id']; ?>"
class="btn approve">

Approve

</a>

<a
href="reject_claim.php?id=<?php echo $row['id']; ?>"
class="btn reject">

Reject

</a>

<?php

}

?>

</div>

<?php

}

}else{

?>

<h2>No Claims Found</h2>

<?php

}

?>

</div>

</body>
</html>