<?php

session_start();

include 'includes/db.php';
include 'send_otp.php';

if (isset($_POST['send_otp'])) {

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $sql = "SELECT * FROM users
            WHERE email='$email'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        // Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // OTP expiry time (10 minutes)
        $expiry = date(
            'Y-m-d H:i:s',
            strtotime('+10 minutes')
        );

        // Save OTP and expiry in database
        mysqli_query(
            $conn,
            "UPDATE users
             SET otp='$otp',
                 otp_expiry='$expiry'
             WHERE email='$email'"
        );

        // Save email in session
        $_SESSION['reset_email'] = $email;

        // Send OTP to registered email
        if (sendOTP($email, $otp)) {

            $success =
            "OTP has been sent to your registered email address.<br>
             Please check your inbox and spam folder.";

            // Redirect to OTP verification page after 3 seconds
            header("Refresh:3; url=verify_otp.php");

        } else {

            // Show exact PHPMailer error for debugging
            global $mail;

            $error =
            "Mailer Error: " . $mail->ErrorInfo;
        }

    } else {

        $error =
        "Email not found.";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <style>
        body{
            margin:0;
            font-family:Arial;
            background:#f4f4f4;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .box{
            background:white;
            width:450px;
            padding:40px;
            border-radius:12px;
            box-shadow:0 0 15px rgba(0,0,0,0.1);
        }

        h2{
            text-align:center;
            margin-bottom:30px;
            color:#111827;
        }

        input{
            width:100%;
            padding:14px;
            margin-top:15px;
            border:1px solid #ccc;
            border-radius:6px;
            box-sizing:border-box;
        }

        button{
            width:100%;
            padding:14px;
            margin-top:20px;
            background:#2563eb;
            color:white;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:16px;
        }

        button:hover{
            background:#1d4ed8;
        }

        .success{
            background:#dcfce7;
            color:#166534;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
            text-align:center;
        }

        .error{
            background:#fecaca;
            color:#b91c1c;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
            text-align:center;
            word-wrap:break-word;
        }

        .back{
            text-align:center;
            margin-top:20px;
        }

        .back a{
            text-decoration:none;
            color:#2563eb;
        }
    </style>
</head>
<body>

<div class="box">

    <h2>Forgot Password</h2>

    <?php if (isset($success)) { ?>
        <div class="success">
            <?php echo $success; ?>
            <br><br>
            Redirecting to OTP verification...
        </div>
    <?php } ?>

    <?php if (isset($error)) { ?>
        <div class="error">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Enter your registered email"
            required>

        <button
            type="submit"
            name="send_otp">
            Send OTP
        </button>

    </form>

    <div class="back">
        <a href="login.php">
            ← Back to Login
        </a>
    </div>

</div>

</body>
</html>