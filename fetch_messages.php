<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Login required");
}

if (!isset($_GET['claim_id']) || !is_numeric($_GET['claim_id'])) {
    exit("Invalid request");
}

$claim_id = (int)$_GET['claim_id'];
$current_user_id = (int)$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Verify that the claim is approved and the user is allowed to view it
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
    exit("Chat not available");
}

$claim = mysqli_fetch_assoc($check_result);

$owner_id = (int)$claim['owner_id'];
$claimant_id = (int)$claim['claimant_id'];

if ($current_user_id !== $owner_id && $current_user_id !== $claimant_id) {
    exit("Access denied");
}

/*
|--------------------------------------------------------------------------
| Get all messages for this claim
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        m.id,
        m.claim_id,
        m.sender_id,
        m.message,
        m.created_at,
        u.fullname AS sender_name
    FROM messages m
    INNER JOIN users u ON m.sender_id = u.id
    WHERE m.claim_id = '$claim_id'
    ORDER BY m.created_at ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    exit("Database error");
}

if (mysqli_num_rows($result) == 0) {
    echo '<div class="empty">No messages yet. Start the conversation now.</div>';
    exit();
}

while ($row = mysqli_fetch_assoc($result)) {

    $isMine = ((int)$row['sender_id'] === $current_user_id);
    $class = $isMine ? 'sent' : 'received';

    echo '<div class="message ' . $class . '">';

    echo '<div class="sender">' . htmlspecialchars($row['sender_name']) . '</div>';
    echo '<div>' . nl2br(htmlspecialchars($row['message'])) . '</div>';
    echo '<span class="time">' . htmlspecialchars($row['created_at']) . '</span>';

    echo '</div>';
}
?>