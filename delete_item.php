<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
}

$id = $_GET['id'];

$sql =
"SELECT * FROM items
WHERE id='$id'";

$result =
mysqli_query($conn,$sql);

$row =
mysqli_fetch_assoc($result);

$image =
$row['image'];

if(file_exists("uploads/".$image)){

    unlink("uploads/".$image);
}

$delete_sql =
"DELETE FROM items
WHERE id='$id'";

mysqli_query($conn,$delete_sql);

header("Location: my_items.php");

?>