<?php

session_start();

include '../includes/db.php';

if(!isset($_SESSION['admin_id'])){

    header("Location:index.php");

    exit();
}

$sql =
"SELECT * FROM items
ORDER BY id DESC";

$result =
mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>Manage Items</title>

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

header h2{

    margin:0;
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

img{

    width:80px;

    height:80px;

    object-fit:cover;

    border-radius:5px;
}

</style>

</head>

<body>

<header>

<h2>Manage Items</h2>

<nav>

<a href="dashboard.php">Dashboard</a>

<a href="claims.php">Claims</a>

<a href="users.php">Users</a>

<a href="logout.php">Logout</a>

</nav>

</header>

<div class="container">

<table>

<tr>

<th>ID</th>

<th>Image</th>

<th>Title</th>

<th>Category</th>

<th>Status</th>

<th>Type</th>

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

<img
src="../uploads/<?php echo $row['image']; ?>">

</td>

<td>

<?php
echo $row['title'];
?>

</td>

<td>

<?php
echo $row['category'];
?>

</td>

<td>

<?php
echo $row['status'];
?>

</td>

<td>

<?php
echo $row['type'];
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