<?php

session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_claims.php");
    exit();
}

$claim_id = (int)$_GET['id'];
$owner_id = (int)$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get claim details and verify that the logged-in user owns the item
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        claims.id AS claim_id,
        claims.claimant_id,
        items.user_id AS owner_id,
        items.title AS item_title
    FROM claims
    INNER JOIN items
        ON claims.item_id = items.id
    WHERE claims.id = '$claim_id'
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: my_claims.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

/*
|--------------------------------------------------------------------------
| Security Check: Only the item owner can reject the claim
|--------------------------------------------------------------------------
*/
if ($row['owner_id'] != $owner_id) {
    header("Location: my_claims.php");
    exit();
}

$claimant_id = (int)$row['claimant_id'];
$item_title  = mysqli_real_escape_string($conn, $row['item_title']);

/*
|--------------------------------------------------------------------------
| Update Claim Status
|--------------------------------------------------------------------------
*/
mysqli_query(
    $conn,
    "UPDATE claims
     SET status='rejected'
     WHERE id='$claim_id'"
);

/*
|--------------------------------------------------------------------------
| Send Notification to Claimant
|--------------------------------------------------------------------------
*/
$notification_message =
    "Your claim for \"" .
    $item_title .
    "\" was rejected because the verification answers did not match.";

mysqli_query(
    $conn,
    "INSERT INTO notifications (
        user_id,
        message
    )
    VALUES (
        '$claimant_id',
        '$notification_message'
    )"
);

/*
|--------------------------------------------------------------------------
| Redirect Back
|--------------------------------------------------------------------------
*/
header("Location: my_claims.php");
exit();

?>