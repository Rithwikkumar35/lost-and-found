<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
}

$user_id =
$_SESSION['user_id'];

$sql =
"SELECT * FROM items
WHERE user_id='$user_id'
ORDER BY id DESC";

$result =
mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>My Items</title>

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

header h2{

    font-size:30px;
}

nav a{

    color:white;

    text-decoration:none;

    margin-left:20px;

    font-size:18px;
}

nav a:hover{

    color:#60a5fa;
}

.container{

    padding:50px;
}

.title{

    text-align:center;

    margin-bottom:40px;

    font-size:45px;

    color:#111827;
}

.grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(320px,1fr));

    gap:30px;
}

.card{

    background:white;

    border-radius:12px;

    overflow:hidden;

    box-shadow:0 0 15px rgba(0,0,0,0.1);
}

.card img{

    width:100%;

    height:250px;

    object-fit:cover;
}

.no-image{

    width:100%;

    height:250px;

    background:#ddd;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#666;

    font-size:22px;
}

.card-body{

    padding:20px;
}

.card-body h3{

    margin-bottom:15px;

    font-size:28px;
}

.card-body p{

    color:#555;

    margin-bottom:15px;

    line-height:1.6;
}

.type{

    display:inline-block;

    padding:6px 12px;

    border-radius:5px;

    color:white;

    font-size:14px;

    margin-right:10px;
}

.lost{

    background:#dc2626;
}

.found{

    background:#16a34a;
}

.status{

    margin-top:20px;

    font-weight:bold;
}

.no-data{

    text-align:center;

    margin-top:60px;

    font-size:28px;

    color:#666;
}

@media(max-width:768px){

    .container{

        padding:20px;
    }

    .title{

        font-size:35px;
    }

    header{

        flex-direction:column;

        align-items:flex-start;
    }

    nav{

        margin-top:15px;
    }
}

</style>

</head>

<body>

<header>

<h2>My Items</h2>

<nav>

<a href="dashboard.php">Dashboard</a>

<a href="browse.php">Browse</a>

<a href="logout.php">Logout</a>

</nav>

</header>

<div class="container">

<h1 class="title">

My Posted Items

</h1>

<div class="grid">

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<div class="card">

<?php

$image_path =
"uploads/".$row['image'];

if(file_exists($image_path)){

?>

<img
src="<?php echo $image_path; ?>">

<?php

}else{

?>

<div class="no-image">

No Image

</div>

<?php

}

?>

<div class="card-body">

<h3>

<?php
echo $row['title'];
?>

</h3>

<p>

<?php
echo $row['description'];
?>

</p>

<?php

if($row['item_type']=="lost"){

?>

<div class="type lost">

Lost

</div>

<?php

}else{

?>

<div class="type found">

Found

</div>

<?php

}

?>
 <a
href="edit_item.php?id=<?php echo $row['id']; ?>"
style="
display:inline-block;
margin-top:15px;
padding:10px 18px;
background:#2563eb;
color:white;
text-decoration:none;
border-radius:5px;">

Edit

</a>

<a
href="delete_item.php?id=<?php echo $row['id']; ?>"

onclick="return confirm('Delete this item?')"

style="
display:inline-block;
margin-top:15px;
margin-left:10px;
padding:10px 18px;
background:red;
color:white;
text-decoration:none;
border-radius:5px;">

Delete

</a>
<div class="status">

Status:
<?php
echo $row['status'];
?>

</div>

</div>

</div>

<?php

}

}else{

?>

<div class="no-data">

No Items Posted Yet

</div>

<?php

}

?>

</div>

</div>

</body>
</html>