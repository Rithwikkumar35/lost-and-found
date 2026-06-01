<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){

    header("Location: browse.php");
    exit();
}

$item_id = mysqli_real_escape_string($conn, $_GET['id']);

$sql = "
SELECT items.*, users.fullname, users.email, users.mobile
FROM items
JOIN users ON items.user_id = users.id
WHERE items.id = '$item_id'
";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) == 0){
    echo "Item not found";
    exit();
}

$item = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
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
            max-width:1000px;
            margin:40px auto;
            background:white;
            border-radius:12px;
            overflow:hidden;
            box-shadow:0 0 15px rgba(0,0,0,0.1);
        }

        .image img{
            width:100%;
            height:450px;
            object-fit:cover;
            display:block;
        }

        .details{
            padding:40px;
        }

        .details h1{
            font-size:42px;
            margin-bottom:15px;
            color:#111827;
        }

        .meta{
            margin:15px 0;
            color:#444;
            font-size:18px;
            line-height:1.7;
        }

        .category{
            display:inline-block;
            background:#2563eb;
            color:white;
            padding:8px 14px;
            border-radius:6px;
            margin-top:10px;
            font-size:14px;
        }

        .status{
            margin-top:20px;
            font-size:18px;
            font-weight:bold;
            color:red;
        }

        .owner-box{
            margin-top:25px;
            padding:20px;
            background:#f9f9f9;
            border-radius:10px;
            border:1px solid #e5e7eb;
        }

        .owner-box h3{
            margin-bottom:10px;
            color:#111827;
        }

        .owner-box p{
            margin:6px 0;
            color:#444;
            font-size:16px;
        }

        .buttons{
            margin-top:30px;
            display:flex;
            flex-wrap:wrap;
            gap:15px;
        }

        .btn{
            display:inline-block;
            padding:14px 24px;
            border-radius:8px;
            color:white;
            text-decoration:none;
            font-size:16px;
            transition:0.3s;
        }

        .claim-btn{
            background:#16a34a;
        }

        .claim-btn:hover{
            background:#15803d;
        }

        .call-btn{
            background:#111827;
        }

        .call-btn:hover{
            background:#000;
        }

        .wa-btn{
            background:#25D366;
        }

        .wa-btn:hover{
            background:#1faa52;
        }

        .email-btn{
            background:#2563eb;
        }

        .email-btn:hover{
            background:#1d4ed8;
        }

        .no-mobile{
            margin-top:10px;
            color:#dc2626;
            font-weight:bold;
        }

        @media(max-width:768px){
            header{
                flex-direction:column;
                align-items:flex-start;
            }

            nav{
                margin-top:15px;
            }

            nav a{
                margin-left:0;
                margin-right:15px;
            }

            .details{
                padding:25px;
            }

            .details h1{
                font-size:32px;
            }

            .image img{
                height:280px;
            }

            .buttons{
                flex-direction:column;
            }

            .btn{
                width:100%;
                text-align:center;
            }
        }
    </style>
</head>
<body>

<header>
    <h2>TraceBack</h2>
    <nav>
        <a href="browse.php">Browse</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <div class="image">
        <?php if(!empty($item['image']) && file_exists("uploads/".$item['image'])) { ?>
            <img src="uploads/<?php echo $item['image']; ?>" alt="Item Image">
        <?php } else { ?>
            <div style="height:450px;display:flex;justify-content:center;align-items:center;background:#ddd;color:#666;font-size:24px;">
                No Image Available
            </div>
        <?php } ?>
    </div>

    <div class="details">
        <h1><?php echo $item['title']; ?></h1>

        <div class="category"><?php echo $item['category']; ?></div>

        <div class="status">Status: <?php echo ucfirst($item['status']); ?></div>

        <div class="meta">
            <p><b>Description:</b> <?php echo $item['description']; ?></p>
            <p><b>Posted By:</b> <?php echo $item['fullname']; ?></p>
            <p><b>Email:</b> <?php echo $item['email']; ?></p>
        </div>

        <div class="owner-box">
            <h3>Contact Finder / Owner</h3>
            <p><b>Mobile:</b> <?php echo !empty($item['mobile']) ? $item['mobile'] : 'Not provided'; ?></p>

            <?php if(!empty($item['mobile'])) { 
                $phone = preg_replace('/[^0-9+]/', '', $item['mobile']);
                $whatsappNumber = preg_replace('/[^0-9]/', '', $item['mobile']);
            ?>
                <div class="buttons">
                    <a class="btn call-btn" href="tel:<?php echo $phone; ?>">Call Owner</a>
                    <a class="btn wa-btn" href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank">WhatsApp Owner</a>
                    <a class="btn email-btn" href="mailto:<?php echo $item['email']; ?>">Email Owner</a>
                </div>
            <?php } else { ?>
                <div class="no-mobile">
                    Mobile number is not available for this user.
                </div>
            <?php } ?>
        </div>

        <div class="buttons">
            <a class="btn claim-btn" href="claim.php?id=<?php echo $item['id']; ?>">Claim This Item</a>
        </div>
    </div>

</div>

</body>
</html>