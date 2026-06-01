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
        claims.item_id,
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
| Security Check: Only the item owner can approve the claim
|--------------------------------------------------------------------------
*/
if ($row['owner_id'] != $owner_id) {
    header("Location: my_claims.php");
    exit();
}

$item_id     = (int)$row['item_id'];
$claimant_id = (int)$row['claimant_id'];
$item_title  = mysqli_real_escape_string($conn, $row['item_title']);

/*
|--------------------------------------------------------------------------
| 1. Approve the selected claim
|--------------------------------------------------------------------------
*/
mysqli_query(
    $conn,
    "UPDATE claims
     SET status='approved'
     WHERE id='$claim_id'"
);

/*
|--------------------------------------------------------------------------
| 2. Reject all other claims for the same item
|--------------------------------------------------------------------------
*/
mysqli_query(
    $conn,
    "UPDATE claims
     SET status='rejected'
     WHERE item_id='$item_id'
     AND id!='$claim_id'"
);

/*
|--------------------------------------------------------------------------
| 3. Mark the item as recovered
|--------------------------------------------------------------------------
*/
mysqli_query(
    $conn,
    "UPDATE items
     SET status='recovered'
     WHERE id='$item_id'"
);

/*
|--------------------------------------------------------------------------
| 4. Notify the approved claimant
|--------------------------------------------------------------------------
*/
$approved_message =
    "Your claim for \"" .
    $item_title .
    "\" has been approved. Please contact the item owner to arrange collection and bring valid proof of ownership.";

mysqli_query(
    $conn,
    "INSERT INTO notifications (
        user_id,
        message
    )
    VALUES (
        '$claimant_id',
        '$approved_message'
    )"
);

/*
|--------------------------------------------------------------------------
| 5. Notify all rejected claimants
|--------------------------------------------------------------------------
*/
$rejected_message =
    "Your claim for \"" .
    $item_title .
    "\" was rejected because the verification answers did not match.";

$other_claims = mysqli_query(
    $conn,
    "SELECT claimant_id
     FROM claims
     WHERE item_id='$item_id'
     AND status='rejected'"
);

while ($other = mysqli_fetch_assoc($other_claims)) {

    $rejected_user_id = (int)$other['claimant_id'];

    mysqli_query(
        $conn,
        "INSERT INTO notifications (
            user_id,
            message
        )
        VALUES (
            '$rejected_user_id',
            '$rejected_message'
        )"
    );
}

/*
|--------------------------------------------------------------------------
| Redirect Back to Claims Page
|--------------------------------------------------------------------------
*/
header("Location: my_claims.php");
exit();

?>