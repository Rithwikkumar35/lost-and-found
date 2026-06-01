<?php

session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM notifications
        WHERE user_id = '$user_id'
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

/* Mark all notifications as read */
mysqli_query(
    $conn,
    "UPDATE notifications
     SET is_read = 1
     WHERE user_id = '$user_id'"
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications - TraceBack</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
            font-size:32px;
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

        .box{
            background:white;
            padding:40px;
            border-radius:12px;
            box-shadow:0 0 15px rgba(0,0,0,0.1);
        }

        h1{
            margin-bottom:30px;
            color:#111827;
            font-size:42px;
        }

        .notification{
            padding:20px;
            margin-bottom:15px;
            border-left:5px solid #2563eb;
            background:#eff6ff;
            border-radius:8px;
        }

        .notification p{
            font-size:18px;
            color:#111827;
            margin-bottom:8px;
        }

        .notification small{
            color:#666;
            font-size:14px;
        }

        .empty{
            text-align:center;
            padding:40px;
            color:#666;
            font-size:20px;
        }

        @media(max-width:768px){
            .container{
                padding:20px;
            }

            h1{
                font-size:32px;
            }

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
                margin-top:10px;
                display:inline-block;
            }
        }

    </style>
</head>
<body>

<header>

    <h2>TraceBack Notifications</h2>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>

</header>

<div class="container">

    <div class="box">

        <h1>🔔 My Notifications</h1>

        <?php if (mysqli_num_rows($result) > 0) { ?>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <div class="notification">

                    <p>
                        <?php echo htmlspecialchars($row['message']); ?>
                    </p>

                    <small>
                        <?php echo $row['created_at']; ?>
                    </small>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty">
                No notifications yet.
            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>