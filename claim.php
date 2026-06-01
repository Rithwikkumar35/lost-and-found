<?php

session_start();
include 'includes/db.php';
include 'includes/csrf.php';
include 'includes/validation.php';

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

// Generate CSRF token if not exists
$csrf_token = generateCSRFToken();

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Get item details using prepared statement
|--------------------------------------------------------------------------
*/
$item_stmt = $conn->prepare(
    "SELECT items.*, users.fullname AS owner_name 
     FROM items 
     JOIN users ON items.user_id = users.id 
     WHERE items.id = ? 
     LIMIT 1"
);

if (!$item_stmt) {
    error_log("Prepare failed: " . $conn->error);
    die("Database error. Please try again.");
}

$item_stmt->bind_param("i", $item_id);

if (!$item_stmt->execute()) {
    error_log("Execute failed: " . $item_stmt->error);
    die("Database error. Please try again.");
}

$item_result = $item_stmt->get_result();

if ($item_result->num_rows == 0) {
    echo "<div style='text-align:center; padding:50px; font-size:18px; color:#666;'>
            Item not found. <a href='browse.php'>← Back to Browse</a>
          </div>";
    exit();
}

$item = $item_result->fetch_assoc();
$item_stmt->close();

/*
|--------------------------------------------------------------------------
| Prevent owner from claiming their own item
|--------------------------------------------------------------------------
*/
if ((int)$item['user_id'] === $user_id) {
    echo "<div style='text-align:center; padding:50px; font-size:18px; color:#dc2626;'>
            You cannot claim your own item. <a href='browse.php'>← Back to Browse</a>
          </div>";
    exit();
}

/*
|--------------------------------------------------------------------------
| Handle claim submission
|--------------------------------------------------------------------------
*/
if (isset($_POST['submit_claim'])) {
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        
        $message = sanitizeInput($_POST['message'] ?? '');
        $claim_answer1 = sanitizeInput($_POST['claim_answer1'] ?? '');
        $claim_answer2 = sanitizeInput($_POST['claim_answer2'] ?? '');
        $claim_answer3 = sanitizeInput($_POST['claim_answer3'] ?? '');
        $claim_answer4 = sanitizeInput($_POST['claim_answer4'] ?? '');
        
        // Validate input
        if (empty($message)) {
            $error = "Please provide a message explaining why this item is yours.";
        } else if (strlen($message) < 10 || strlen($message) > 1000) {
            $error = "Message must be between 10 and 1000 characters.";
        } else if (empty($claim_answer1) || empty($claim_answer2) || empty($claim_answer3) || empty($claim_answer4)) {
            $error = "Please answer all verification questions.";
        } else {
            
            /*
            |--------------------------------------------------------------------------
            | Check for duplicate claim by same user on same item
            |--------------------------------------------------------------------------
            */
            $check_stmt = $conn->prepare(
                "SELECT id FROM claims 
                 WHERE item_id = ? AND claimant_id = ? 
                 LIMIT 1"
            );
            
            if (!$check_stmt) {
                error_log("Prepare failed: " . $conn->error);
                $error = "Database error. Please try again.";
            } else {
                
                $check_stmt->bind_param("ii", $item_id, $user_id);
                
                if (!$check_stmt->execute()) {
                    error_log("Execute failed: " . $check_stmt->error);
                    $error = "Database error. Please try again.";
                } else {
                    
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        $error = "You have already submitted a claim for this item.";
                    } else {
                        
                        /*
                        |--------------------------------------------------------------------------
                        | Verify answers (case-insensitive comparison)
                        |--------------------------------------------------------------------------
                        */
                        $stored_answer1 = strtolower(trim($item['answer1']));
                        $stored_answer2 = strtolower(trim($item['answer2']));
                        $stored_answer3 = strtolower(trim($item['answer3']));
                        $stored_answer4 = strtolower(trim($item['answer4']));
                        
                        $provided_answer1 = strtolower(trim($claim_answer1));
                        $provided_answer2 = strtolower(trim($claim_answer2));
                        $provided_answer3 = strtolower(trim($claim_answer3));
                        $provided_answer4 = strtolower(trim($claim_answer4));
                        
                        // Check if at least 3 out of 4 answers are correct
                        $correct_count = 0;
                        if ($stored_answer1 === $provided_answer1) $correct_count++;
                        if ($stored_answer2 === $provided_answer2) $correct_count++;
                        if ($stored_answer3 === $provided_answer3) $correct_count++;
                        if ($stored_answer4 === $provided_answer4) $correct_count++;
                        
                        if ($correct_count < 3) {
                            $error = "Incorrect answers. You need at least 3 out of 4 correct answers to proceed. You got " . $correct_count . "/4.";
                        } else {
                            
                            /*
                            |--------------------------------------------------------------------------
                            | Insert claim with verification status
                            |--------------------------------------------------------------------------
                            */
                            $insert_stmt = $conn->prepare(
                                "INSERT INTO claims (
                                    item_id,
                                    claimant_id,
                                    message,
                                    claim_answer1,
                                    claim_answer2,
                                    claim_answer3,
                                    claim_answer4,
                                    answers_verified,
                                    verification_score,
                                    status,
                                    created_at
                                ) VALUES (
                                    ?, ?, ?, ?, ?, ?, ?,
                                    ?, ?, 'pending', NOW()
                                )"
                            );
                            
                            if (!$insert_stmt) {
                                error_log("Prepare failed: " . $conn->error);
                                $error = "Database error. Please try again.";
                            } else {
                                
                                $answers_verified = 1; // Answers were verified
                                $insert_stmt->bind_param(
                                    "iissssii",
                                    $item_id, $user_id, $message,
                                    $claim_answer1, $claim_answer2, $claim_answer3, $claim_answer4,
                                    $answers_verified, $correct_count
                                );
                                
                                if (!$insert_stmt->execute()) {
                                    error_log("Execute failed: " . $insert_stmt->error);
                                    $error = "Failed to submit claim. Please try again.";
                                } else {
                                    
                                    /*
                                    |--------------------------------------------------------------------------
                                    | Notify item owner
                                    |--------------------------------------------------------------------------
                                    */
                                    $claimant_name = $conn->real_escape_string($_SESSION['fullname'] ?? 'Someone');
                                    $item_title = $conn->real_escape_string($item['title']);
                                    $notification_message = $claimant_name . " has submitted a claim for your item: " . $item['title'] . " (" . $correct_count . "/4 answers correct)";
                                    
                                    $notif_stmt = $conn->prepare(
                                        "INSERT INTO notifications (user_id, message, created_at) 
                                         VALUES (?, ?, NOW())"
                                    );
                                    
                                    if ($notif_stmt) {
                                        $notif_stmt->bind_param("is", $item['user_id'], $notification_message);
                                        $notif_stmt->execute();
                                        $notif_stmt->close();
                                    }
                                    
                                    $success = "Claim submitted successfully! The item owner has been notified. You answered " . $correct_count . " out of 4 questions correctly.";
                                    error_log("Claim submitted by user: " . $user_id . " for item: " . $item_id . " with score: " . $correct_count . "/4");
                                }
                                
                                $insert_stmt->close();
                            }
                        }
                    }
                }
                
                $check_stmt->close();
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
    <title>Claim Item - TraceBack</title>
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
            border:1px solid #16a34a;
        }

        .error{
            background:#fecaca;
            color:#991b1b;
            padding:14px 16px;
            border-radius:8px;
            margin-bottom:18px;
            font-weight:bold;
            border:1px solid #dc2626;
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

        .item-image{
            width:100%;
            max-height:300px;
            object-fit:cover;
            border-radius:8px;
            margin:15px 0;
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
            font-family: Arial;
        }

        .q input:focus,
        textarea:focus{
            border-color:#2563eb;
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
            transition:0.3s;
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

        .back:hover{
            text-decoration:underline;
        }

        .score-note{
            background:#fef3c7;
            border:1px solid #fbbf24;
            color:#92400e;
            padding:12px;
            border-radius:6px;
            margin-top:10px;
            font-size:14px;
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

        <?php if (!empty($success)) { ?>
            <div class="success"><?php echo $success; ?></div>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <div class="item-card">
            <h2><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php if (!empty($item['image']) && file_exists("uploads/" . $item['image'])) { ?>
                <img
                    src="uploads/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                    alt="Item Image"
                    class="item-image">
            <?php } ?>

            <span class="badge"><?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?></span>

            <div class="meta">
                <p><b>Description:</b> <?php echo htmlspecialchars($item['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><b>Posted By:</b> <?php echo htmlspecialchars($item['owner_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p><b>Status:</b> <?php echo htmlspecialchars(ucfirst($item['status']), ENT_QUOTES, 'UTF-8'); ?></p>
                <p><b>Type:</b> <?php echo htmlspecialchars(ucfirst($item['item_type']), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div>

        <form method="POST">
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <textarea
                name="message"
                placeholder="Explain why this item belongs to you..."
                required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>

            <div class="question-box">
                <h3>Verification Questions</h3>
                <p style="color:#666; font-size:14px; margin-bottom:15px;">Answer the verification questions to prove ownership. You need at least 3 out of 4 correct answers.</p>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q1'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input
                        type="text"
                        name="claim_answer1"
                        value="<?php echo isset($_POST['claim_answer1']) ? htmlspecialchars($_POST['claim_answer1'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        required>
                </div>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q2'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input
                        type="text"
                        name="claim_answer2"
                        value="<?php echo isset($_POST['claim_answer2']) ? htmlspecialchars($_POST['claim_answer2'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        required>
                </div>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q3'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input
                        type="text"
                        name="claim_answer3"
                        value="<?php echo isset($_POST['claim_answer3']) ? htmlspecialchars($_POST['claim_answer3'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        required>
                </div>

                <div class="q">
                    <label><?php echo htmlspecialchars($item['verify_q4'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <input
                        type="text"
                        name="claim_answer4"
                        value="<?php echo isset($_POST['claim_answer4']) ? htmlspecialchars($_POST['claim_answer4'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        required>
                </div>

                <div class="score-note">
                    <strong>Note:</strong> Answers are case-insensitive. Extra spaces will be trimmed.
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