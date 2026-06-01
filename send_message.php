<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Login required");
}

if (!isset($_POST['claim_id']) || !isset($_POST['message'])) {
    http_response_code(400);
    exit("Invalid request");
}

$claim_id  = (int)$_POST['claim_id'];
$sender_id  = (int)$_SESSION['user_id'];
$message    = trim($_POST['message']);

if ($claim_id <= 0) {
    http_response_code(400);
    exit("Invalid claim");
}

if ($message === '') {
    http_response_code(400);
    exit("Message cannot be empty");
}

/*
|--------------------------------------------------------------------------
| Verify this user is allowed to chat on this claim
| Only approved claim owner or approved claimant can send messages
|--------------------------------------------------------------------------
*/
$check_sql = "
    SELECT
        c.id,
        c.claimant_id,
        i.user_id AS owner_id
    FROM claims c
    INNER JOIN items i ON c.item_id = i.id
    WHERE c.id = '$claim_id'
      AND c.status = 'approved'
    LIMIT 1
";

$check_result = mysqli_query($conn, $check_sql);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    http_response_code(403);
    exit("Chat not allowed");
}

$chat = mysqli_fetch_assoc($check_result);

$owner_id    = (int)$chat['owner_id'];
$claimant_id = (int)$chat['claimant_id'];

if ($sender_id !== $owner_id && $sender_id !== $claimant_id) {
    http_response_code(403);
    exit("Access denied");
}

/*
|--------------------------------------------------------------------------
| Insert message
|--------------------------------------------------------------------------
*/
$message = mysqli_real_escape_string($conn, $message);

$sql = "
    INSERT INTO messages (
        claim_id,
        sender_id,
        message
    ) VALUES (
        '$claim_id',
        '$sender_id',
        '$message'
    )
";

if (mysqli_query($conn, $sql)) {
    echo "success";
} else {
    http_response_code(500);
    echo "Error: " . mysqli_error($conn);
}
?>