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

if(isset($_POST['update'])){

    $title =
    $_POST['title'];

    $description =
    $_POST['description'];

    $category =
    $_POST['category'];

    $old_image =
    $_POST['old_image'];

    $image = $old_image;

    if($_FILES['image']['name'] != ""){

        $image =
        time().'_'.
        str_replace(
        ' ',
        '_',
        $_FILES['image']['name']
        );

        $temp_name =
        $_FILES['image']['tmp_name'];

        move_uploaded_file(
            $temp_name,
            "uploads/".$image
        );
    }

    $update_sql =
    "UPDATE items SET

    title='$title',

    description='$description',

    category='$category',

    image='$image'

    WHERE id='$id'";

    mysqli_query($conn,$update_sql);

    header("Location: my_items.php");
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Item</title>

<style>

body{

    font-family:Arial;

    background:#f4f4f4;

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;
}

.form-box{

    background:white;

    width:500px;

    padding:40px;

    border-radius:10px;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h2{

    text-align:center;

    margin-bottom:25px;
}

input,
textarea,
select{

    width:100%;

    padding:12px;

    margin-top:15px;

    border:1px solid #ccc;

    border-radius:5px;

    font-size:16px;
}

textarea{

    height:120px;
}

button{

    width:100%;

    padding:14px;

    margin-top:20px;

    background:#2563eb;

    color:white;

    border:none;

    border-radius:5px;

    font-size:17px;

    cursor:pointer;
}

button:hover{

    background:#1d4ed8;
}

img{

    width:100%;

    height:250px;

    object-fit:cover;

    border-radius:10px;

    margin-top:20px;
}

</style>

</head>

<body>

<div class="form-box">

<h2>Edit Item</h2>

<form
method="POST"
enctype="multipart/form-data">

<input
type="hidden"
name="old_image"
value="<?php echo $row['image']; ?>">

<input
type="text"
name="title"
value="<?php echo $row['title']; ?>"
required>

<textarea
name="description"
required><?php echo $row['description']; ?></textarea>

<select name="category">

<option
<?php
if($row['category']=="Mobile"){
echo "selected";
}
?>>

Mobile

</option>

<option
<?php
if($row['category']=="Wallet"){
echo "selected";
}
?>>

Wallet

</option>

<option
<?php
if($row['category']=="ID Card"){
echo "selected";
}
?>>

ID Card

</option>

<option
<?php
if($row['category']=="Bag"){
echo "selected";
}
?>>

Bag

</option>

<option
<?php
if($row['category']=="Watch"){
echo "selected";
}
?>>

Watch

</option>

<option
<?php
if($row['category']=="Other"){
echo "selected";
}
?>>

Other

</option>

</select>

<img
src="uploads/<?php echo $row['image']; ?>">

<input
type="file"
name="image">

<button name="update">

Update Item

</button>

</form>

</div>

</body>
</html>