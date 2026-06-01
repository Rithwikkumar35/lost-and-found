<?php

session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: browse.php");
    exit();
}

$item_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get item details
|--------------------------------------------------------------------------
*/
$item_sql = "
    SELECT items.*, users.fullname AS owner_name
    FROM items
    JOIN users ON items.user_id = users.id
    WHERE items.id = '$item_id'
    LIMIT 1
";

$item_result = mysqli_query($conn, $item_sql);

if (!$item_result || mysqli_num_rows($item_result) == 0) {
    echo "Item not found";
    exit();
}

$item = mysqli_fetch_assoc($item_result);

/*
|--------------------------------------------------------------------------
| Prevent owner from claiming their own item
|--------------------------------------------------------------------------
*/
if ((int)$item['user_id'] === $user_id) {
    echo "You cannot claim your own item.";
    exit();
}

/*
|--------------------------------------------------------------------------
| Handle claim submission
|--------------------------------------------------------------------------
*/
if (isset($_POST['submit_claim'])) {

    $message = mysqli_real_escape_string($conn, trim($_POST['message'] ?? ''));

    $claim_answer1 = mysqli_real_escape_string($conn, trim($_POST['claim_answer1'] ?? ''));
    $claim_answer2 = mysqli_real_escape_string($conn, trim($_POST['claim_answer2'] ?? ''));
    $claim_answer3 = mysqli_real_escape_string($conn, trim($_POST['claim_answer3'] ?? ''));
    $claim_answer4 = mysqli_real_escape_string($conn, trim($_POST['claim_answer4'] ?? ''));

    if ($message === '' || $claim_answer1 === '' || $claim_answer2 === '' || $claim_answer3 === '' || $claim_answer4 === '') {
        $error = "Please fill in all fields.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | Prevent duplicate claim by same user on same item
        |--------------------------------------------------------------------------
        */
        $check_sql = "
            SELECT id
            FROM claims
            WHERE item_id = '$item_id'
              AND claimant_id = '$user_id'
            LIMIT 1
        ";

        $check_result = mysqli_query($conn, $check_sql);

        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $error = "You have already submitted a claim for this item.";
        } else {

            /*
            |--------------------------------------------------------------------------
            | Insert claim
            |--------------------------------------------------------------------------
            */
            $insert_sql = "
                INSERT INTO claims (
                    item_id,
                    claimant_id,
                    message,
                    claim_answer1,
                    claim_answer2,
                    claim_answer3,
                    claim_answer4,
                    status
                ) VALUES (
                    '$item_id',
                    '$user_id',
                    '$message',
                    '$claim_answer1',
                    '$claim_answer2',
                    '$claim_answer3',
                    '$claim_answer4',
                    'pending'
                )
            ";

            if (mysqli_query($conn, $insert_sql)) {

                /*
                |--------------------------------------------------------------------------
                | Notify item owner
                |--------------------------------------------------------------------------
                */
                $claimant_name = mysqli_real_escape_string($conn, $_SESSION['fullname'] ?? 'Someone');
                $item_title = mysqli_real_escape_string($conn, $item['title']);

                $notification_message = $claimant_name . " has submitted a claim for your item: " . $item['title'];

                mysqli_query(
                    $conn,
                    "INSERT INTO notifications (user_id, message)
                     VALUES ('" . (int)$item['user_id'] . "', '" . mysqli_real_escape_string($conn, $notification_message) . "')"
                );

                $success = "Claim submitted successfully. The item owner has been notified.";
            } else {
                $error = "Failed to submit claim.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#f4f4f4;
            padding:20px;
        }

        .container{
            max-width:850px;
            margin:30px auto;
            background:#fff;
            border-radius:14px;
            box-shadow:0 0 15px rgba(0,0,0,0.08);
            overflow:hidden;
        }

        .header{
            background:#111827;
            color:#fff;
            padding:24px 30px;
        }

        .header h1{
            font-size:30px;
            margin-bottom:6px;
        }

        .content{
            padding:30px;
        }

        .success{
            background:#dcfce7;
            color:#166534;
            padding:14px 16px;
            border-radius:8px;
            margin-bottom:18px;
            font-weight:bold;
        }

        .error{
            background:#fecaca;
            color:#991b1b;
            padding:14px 16px;
            border-radius:8px;
            margin-bottom:18px;
            font-weight:bold;
        }

        .item-card{
            background:#f9fafb;
            border:1px solid #e5e7eb;
            border-radius:12px;
            padding:20px;
            margin-bottom:25px;
        }

        .item-card h2{
            font-size:28px;
            color:#111827;
            margin-bottom:10px;
        }

        .meta{
            color:#444;
            line-height:1.7;
            margin-top:10px;
        }

        .badge{
            display:inline-block;
            background:#2563eb;
            color:#fff;
            padding:6px 12px;
            border-radius:999px;
            font-size:13px;
            margin-top:10px;
        }

        .question-box{
            background:#eff6ff;
            border-left:4px solid #2563eb;
            border-radius:8px;
            padding:18px;
            margin-top:18px;
        }

        .question-box h3{
            color:#1e3a8a;
            margin-bottom:14px;
            font-size:22px;
        }

        .q{
            margin-top:14px;
        }

        .q label{
            display:block;
            font-weight:bold;
            color:#1e3a8a;
            margin-bottom:8px;
        }

        .q input,
        textarea{
            width:100%;
            padding:12px 14px;
            border:1px solid #d1d5db;
            border-radius:8px;
            font-size:16px;
            outline:none;
        }

        textarea{
            resize:none;
            min-height:120px;
        }

        .btn{
            width:100%;
            display:block;
            margin-top:22px;
            padding:14px 16px;
            border:none;
            border-radius:8px;
            font-size:17px;
            font-weight:bold;
            cursor:pointer;
            color:#fff;
            background:#16a34a;
        }

        .btn:hover{
            background:#15803d;
        }

        .back{
            display:inline-block;
            margin-top:18px;
            color:#2563eb;
            text-decoration:none;
            font-weight:bold;
        }

        @media(max-width:768px){
            .content{
                padding:20px;
            }

            .header h1{
                font-size:24px;
            }

            .item-card h2{
                font-size:24px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Claim Item</h1>
        <p>Submit your claim and answer the verification questions.</p>
    </div>

    <div class="content">

        <?php if (isset($success)) { ?>
            <div class="success"><?php echo $success; ?></div>
        <?php } ?>

        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <div class="item-card">
            <h2><?php echo htmlspecialchars($item['title']); ?></h2>

            <?php if (!empty($item['image']) && file_exists("uploads/" . $item['image'])) { ?>
                <img
                    src="uploads/<?php echo htmlspecialchars($item['image']); ?>"
                    alt="Item Image"
                    style="width:100%;max-height:350px;object-fit:cover;border-radius:10px;margin-top:15px;">
            <?php } ?>

            <div class="badge"><?php echo htmlspecialchars($item['category']); ?></div>

            <div class="meta">
                <p><b>Description:</b> <?php echo htmlspecialchars($item['description']); ?></p>
                <p><b>Posted By:</b> <?php echo htmlspecialchars($item['owner_name']); ?></p>
                <p><b>Status:</b> <?php echo htmlspecialchars($item['status']); ?></p>
            </div>
        </div>

        <form method="POST">
            <textarea
                name="message"
                placeholder="Explain why this item belongs to you..."
                required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>

            <div class="question-box">
                <h3>Verification Questions</h3>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q1']); ?></label>
                    <input
                        type="text"
                        name="claim_answer1"
                        value="<?php echo isset($_POST['claim_answer1']) ? htmlspecialchars($_POST['claim_answer1']) : ''; ?>"
                        required>
                </div>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q2']); ?></label>
                    <input
                        type="text"
                        name="claim_answer2"
                        value="<?php echo isset($_POST['claim_answer2']) ? htmlspecialchars($_POST['claim_answer2']) : ''; ?>"
                        required>
                </div>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q3']); ?></label>
                    <input
                        type="text"
                        name="claim_answer3"
                        value="<?php echo isset($_POST['claim_answer3']) ? htmlspecialchars($_POST['claim_answer3']) : ''; ?>"
                        required>
                </div>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q4']); ?></label>
                    <input
                        type="text"
                        name="claim_answer4"
                        value="<?php echo isset($_POST['claim_answer4']) ? htmlspecialchars($_POST['claim_answer4']) : ''; ?>"
                        required>
                </div>
            </div>

            <button type="submit" name="submit_claim" class="btn">
                Submit Claim
            </button>
        </form>

        <a class="back" href="browse.php">← Back to Browse</a>
    </div>
</div>

</body>
</html>