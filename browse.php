<?php

include 'includes/db.php';

if(isset($_GET['search'])){

    $search =
    $_GET['search'];

    $sql =
    "SELECT * FROM items
    WHERE title LIKE '%$search%'
    OR description LIKE '%$search%'
    OR category LIKE '%$search%'
    ORDER BY id DESC";

}else{

    $sql =
    "SELECT * FROM items
    ORDER BY id DESC";
}

$result =
mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>Browse Items</title>

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

    padding:20px 50px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    flex-wrap:wrap;
}

header h2{

    font-size:30px;
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

    padding:50px;
}

.title{

    text-align:center;

    margin-bottom:30px;

    font-size:45px;

    color:#111827;
}

.search-form{

    margin-bottom:40px;

    display:flex;

    gap:15px;

    flex-wrap:wrap;
}

.search-box{

    flex:1;

    padding:15px;

    border:none;

    border-radius:8px;

    font-size:18px;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

.search-btn{

    padding:15px 30px;

    background:#2563eb;

    color:white;

    border:none;

    border-radius:8px;

    font-size:18px;

    cursor:pointer;

    transition:0.3s;
}

.search-btn:hover{

    background:#1d4ed8;
}

.grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(320px,1fr));

    gap:30px;
}

.item-link{

    text-decoration:none;

    color:inherit;
}

.card{

    background:white;

    border-radius:12px;

    overflow:hidden;

    box-shadow:0 0 15px rgba(0,0,0,0.1);

    transition:0.3s;
}

.card:hover{

    transform:translateY(-5px);
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

    font-size:22px;

    color:#666;
}

.card-body{

    padding:20px;
}

.card-body h3{

    margin-bottom:15px;

    font-size:28px;

    color:#111827;
}

.card-body p{

    color:#555;

    line-height:1.6;

    margin-bottom:15px;
}

.category{

    display:inline-block;

    background:#2563eb;

    color:white;

    padding:6px 12px;

    border-radius:5px;

    font-size:14px;
}

.status{

    margin-top:20px;

    font-weight:bold;

    color:red;
}

.no-data{

    text-align:center;

    font-size:28px;

    color:#666;

    margin-top:50px;
}

@media(max-width:768px){

    .container{

        padding:20px;
    }

    .title{

        font-size:35px;
    }

    header{

        padding:20px;
    }

    header nav{

        margin-top:15px;
    }

    .search-form{

        flex-direction:column;
    }

    .search-btn{

        width:100%;
    }
}

</style>

</head>

<body>

<header>

<h2>TraceBack</h2>

<nav>

<a href="index.php">Home</a>

<a href="dashboard.php">Dashboard</a>

<a href="logout.php">Logout</a>

</nav>

</header>

<div class="container">

<h1 class="title">

Browse Items

</h1>

<form method="GET" class="search-form">

<input
type="text"
name="search"
class="search-box"
placeholder="Search by title, category or description..."
value="<?php
if(isset($_GET['search'])){
echo $_GET['search'];
}
?>">

<button
type="submit"
class="search-btn">

Search

</button>

</form>

<div class="grid">

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<a
href="item.php?id=<?php echo $row['id']; ?>"
class="item-link">

<div class="card">

<?php

$image_path =
"uploads/".$row['image'];

if(
$row['image'] != "" &&
file_exists($image_path)
){

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

<div class="category">

<?php
echo $row['category'];
?>

</div>

<div class="status">

Status:
<?php
echo ucfirst($row['status']);
?>

</div>

</div>

</div>

</a>

<?php

}

}else{

?>

<div class="no-data">

No Items Found

</div>

<?php

}

?>

</div>

</div>

</body>
</html>