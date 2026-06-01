<?php

session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get all claims submitted for items owned by the logged-in user
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        claims.*,
        items.title AS item_title,
        items.verify_q1,
        items.verify_q2,
        items.verify_q3,
        items.verify_q4,
        users.fullname AS claimant_name,
        users.email AS claimant_email
    FROM claims
    INNER JOIN items
        ON claims.item_id = items.id
    INNER JOIN users
        ON claims.claimant_id = users.id
    WHERE items.user_id = '$user_id'
    ORDER BY claims.id DESC
";

$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>My Claims - TraceBack</title>
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

        .container{
            max-width:1000px;
            margin:40px auto;
            padding:0 20px;
        }

        .title{
            font-size:42px;
            color:#111827;
            margin-bottom:30px;
        }

        .card{
            background:white;
            padding:30px;
            border-radius:12px;
            box-shadow:0 0 15px rgba(0,0,0,0.08);
            margin-bottom:30px;
        }

        .card h3{
            font-size:28px;
            color:#111827;
            margin-bottom:15px;
        }

        .label{
            font-weight:bold;
            color:#111827;
        }

        .info{
            margin-bottom:10px;
            font-size:17px;
            color:#444;
        }

        .answer-box{
            background:#eff6ff;
            border-left:4px solid #2563eb;
            padding:12px 15px;
            margin-top:10px;
            border-radius:6px;
        }

        .answer-box strong{
            display:block;
            color:#1e3a8a;
            margin-bottom:5px;
        }

        .status{
            display:inline-block;
            margin-top:15px;
            padding:6px 14px;
            border-radius:20px;
            font-size:14px;
            font-weight:bold;
            text-transform:uppercase;
        }

        .pending{
            background:#fef3c7;
            color:#92400e;
        }

        .approved{
            background:#dcfce7;
            color:#166534;
        }

        .rejected{
            background:#fecaca;
            color:#991b1b;
        }

        .actions{
            margin-top:20px;
            display:flex;
            gap:15px;
            flex-wrap:wrap;
        }

        .btn{
            display:inline-block;
            padding:12px 20px;
            color:white;
            text-decoration:none;
            border-radius:8px;
            font-size:16px;
            font-weight:bold;
        }

        .approve{
            background:#16a34a;
        }

        .approve:hover{
            background:#15803d;
        }

        .reject{
            background:#dc2626;
        }

        .reject:hover{
            background:#b91c1c;
        }

        .chat{
            background:#2563eb;
        }

        .chat:hover{
            background:#1d4ed8;
        }

        .empty{
            background:white;
            padding:40px;
            border-radius:12px;
            text-align:center;
            color:#666;
            font-size:20px;
            box-shadow:0 0 15px rgba(0,0,0,0.08);
        }

        @media(max-width:768px){

            header{
                padding:20px;
                flex-direction:column;
                align-items:flex-start;
                gap:15px;
            }

            header h2{
                font-size:28px;
            }

            nav a{
                margin-left:0;
                margin-right:15px;
                display:inline-block;
                margin-bottom:10px;
            }

            .title{
                font-size:34px;
            }

            .card{
                padding:20px;
            }

            .card h3{
                font-size:24px;
            }

            .actions{
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
    <h2>TraceBack Claims</h2>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="my_items.php">My Items</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <div class="title">My Item Claims</div>

    <?php if (mysqli_num_rows($result) == 0) { ?>

        <div class="empty">
            No claims have been submitted for your items yet.
        </div>

    <?php } ?>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <div class="card">

            <h3>
                <?php echo htmlspecialchars($row['item_title']); ?>
            </h3>

            <div class="info">
                <span class="label">Claimant:</span>
                <?php echo htmlspecialchars($row['claimant_name']); ?>
            </div>

            <div class="info">
                <span class="label">Email:</span>
                <?php echo htmlspecialchars($row['claimant_email']); ?>
            </div>

            <div class="info">
                <span class="label">Claim Message:</span><br>
                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
            </div>

            <?php
            for ($i = 1; $i <= 4; $i++) {

                $question = $row["verify_q{$i}"];
                $answer   = $row["claim_answer{$i}"];

                if (!empty($question)) {
            ?>
                    <div class="answer-box">
                        <strong>
                            <?php echo htmlspecialchars($question); ?>
                        </strong>
                        <?php echo htmlspecialchars($answer); ?>
                    </div>
            <?php
                }
            }
            ?>

            <!-- STATUS -->
            <div class="status <?php echo htmlspecialchars($row['status']); ?>">
                <?php echo htmlspecialchars($row['status']); ?>
            </div>

            <!-- PENDING ACTIONS -->
            <?php if ($row['status'] === 'pending') { ?>

                <div class="actions">

                    <a
                        href="approve_claim.php?id=<?php echo $row['id']; ?>"
                        class="btn approve"
                        onclick="return confirm('Approve this claim?');">
                        Approve Claim
                    </a>

                    <a
                        href="reject_claim.php?id=<?php echo $row['id']; ?>"
                        class="btn reject"
                        onclick="return confirm('Reject this claim?');">
                        Reject Claim
                    </a>

                </div>

            <?php } ?>

            <!-- APPROVED ACTIONS -->
            <?php if ($row['status'] === 'approved') { ?>

                <div class="actions">

                    <a
                        href="messages.php?claim_id=<?php echo $row['id']; ?>"
                        class="btn chat">
                        Open Chat
                    </a>

                </div>

            <?php } ?>

        </div>

    <?php } ?>

</div>

</body>
</html>