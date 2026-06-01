<?php

session_start();

include '../includes/db.php';

if(!isset($_SESSION['admin_id'])){

    header("Location:index.php");

    exit();
}

$sql =
"SELECT * FROM users
ORDER BY id DESC";

$result =
mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Users</title>

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

nav a{

    color:white;

    text-decoration:none;

    margin-left:20px;
}

.container{

    padding:40px;
}

table{

    width:100%;

    background:white;

    border-collapse:collapse;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

table th,
table td{

    padding:15px;

    border:1px solid #ddd;

    text-align:left;
}

table th{

    background:#2563eb;

    color:white;
}

</style>

</head>

<body>

<header>

<h2>Manage Users</h2>

<nav>

<a href="dashboard.php">Dashboard</a>

<a href="items.php">Items</a>

<a href="claims.php">Claims</a>

<a href="logout.php">Logout</a>

</nav>

</header>

<div class="container">

<table>

<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Role</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td>

<?php
echo $row['id'];
?>

</td>

<td>

<?php
echo $row['fullname'];
?>

</td>

<td>

<?php
echo $row['email'];
?>

</td>

<td>

<?php
echo $row['role'];
?>

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>
</html>