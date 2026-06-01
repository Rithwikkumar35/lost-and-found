<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];

/* Get claim ID from URL */
$claim_id = 0;

if (isset($_GET['claim_id']) && is_numeric($_GET['claim_id'])) {
    $claim_id = (int)$_GET['claim_id'];
} elseif (isset($_SERVER['PATH_INFO'])) {
    $pathInfo = trim($_SERVER['PATH_INFO'], '/');
    if (is_numeric($pathInfo)) {
        $claim_id = (int)$pathInfo;
    }
}

if ($claim_id <= 0) {
    die("Invalid claim ID.");
}

/* Get approved claim */
$sql = "
    SELECT
        c.id AS claim_id,
        c.item_id,
        c.claimant_id,
        c.status,
        i.title AS item_title,
        i.user_id AS owner_id
    FROM claims c
    INNER JOIN items i ON c.item_id = i.id
    WHERE c.id = '$claim_id'
      AND c.status = 'approved'
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Chat is only available for approved claims.");
}

$claim = mysqli_fetch_assoc($result);

$owner_id = (int)$claim['owner_id'];
$claimant_id = (int)$claim['claimant_id'];

/* Check access */
if ($current_user_id !== $owner_id && $current_user_id !== $claimant_id) {
    die("Access denied.");
}

/* Determine other user */
$other_user_id = ($current_user_id === $owner_id) ? $claimant_id : $owner_id;

/* Get other user details */
$user_sql = "
    SELECT fullname, email
    FROM users
    WHERE id = '$other_user_id'
    LIMIT 1
";

$user_result = mysqli_query($conn, $user_sql);

if (!$user_result) {
    die("Database error: " . mysqli_error($conn));
}

$other_user = mysqli_fetch_assoc($user_result);

$other_name = $other_user['fullname'] ?? 'User';
$other_email = $other_user['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - TraceBack</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}

.header {
    background: #0f172a;
    color: white;
    padding: 20px 40px;
}

.header h1 {
    font-size: 32px;
}

.container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.chat-header {
    background: #2563eb;
    color: white;
    padding: 20px;
}

.chat-header h2 {
    margin-bottom: 8px;
}

.chat-box {
    height: 450px;
    overflow-y: auto;
    padding: 20px;
    background: #f8fafc;
}

.message {
    margin-bottom: 15px;
    padding: 12px 16px;
    border-radius: 10px;
    max-width: 70%;
    line-height: 1.5;
    word-wrap: break-word;
}

.sent {
    background: #2563eb;
    color: white;
    margin-left: auto;
}

.received {
    background: #e5e7eb;
    color: #111827;
}

.sender {
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 5px;
    opacity: 0.85;
}

.time {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    opacity: 0.7;
}

.empty {
    text-align: center;
    color: #6b7280;
    padding: 80px 20px;
    font-size: 18px;
}

.chat-form {
    display: flex;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #ddd;
    background: white;
}

.chat-form input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
}

.chat-form button {
    padding: 12px 25px;
    background: #16a34a;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}

.chat-form button:hover {
    background: #15803d;
}

.back {
    display: inline-block;
    margin: 20px;
    color: #2563eb;
    text-decoration: none;
    font-weight: bold;
}

.back:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .container {
        margin: 10px;
    }

    .chat-box {
        height: 380px;
    }

    .message {
        max-width: 90%;
    }

    .chat-form {
        flex-direction: column;
    }

    .chat-form button {
        width: 100%;
    }
}
</style>
</head>
<body>

<div class="header">
    <h1>TraceBack Chat</h1>
</div>

<div class="container">

    <div class="chat-header">
        <h2><?php echo htmlspecialchars($claim['item_title']); ?></h2>
        <p>
            Chat with
            <strong><?php echo htmlspecialchars($other_name); ?></strong>
            <?php if ($other_email !== '') { ?>
                (<?php echo htmlspecialchars($other_email); ?>)
            <?php } ?>
        </p>
    </div>

    <div id="chat-box" class="chat-box">
        <div class="empty">Loading messages...</div>
    </div>

    <form id="chat-form" class="chat-form">
        <input
            type="text"
            id="message"
            placeholder="Type your message..."
            required
            autocomplete="off">
        <button type="submit">Send</button>
    </form>

    <a href="my_claims.php" class="back">← Back to My Claims</a>
</div>

<script>
const chatBox = document.getElementById('chat-box');
const chatForm = document.getElementById('chat-form');
const messageInput = document.getElementById('message');
const claimId = <?php echo (int)$claim_id; ?>;

function loadMessages() {
    fetch('fetch_messages.php?claim_id=' + encodeURIComponent(claimId))
        .then(response => response.text())
        .then(data => {
            chatBox.innerHTML = data;
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(() => {
            chatBox.innerHTML =
                '<div class="empty">Failed to load messages.</div>';
        });
}

chatForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const message = messageInput.value.trim();
    if (message === '') return;

    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body:
            'claim_id=' + encodeURIComponent(claimId) +
            '&message=' + encodeURIComponent(message)
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === 'success') {
            messageInput.value = '';
            loadMessages();
        } else {
            alert(data);
        }
    })
    .catch(() => {
        alert('Failed to send message.');
    });
});

/* Initial load */
loadMessages();

/* Refresh every 5 seconds only when tab is visible */
let refreshInterval = setInterval(function () {
    if (document.visibilityState === 'visible') {
        loadMessages();
    }
}, 5000);
</script>

</body>
</html>