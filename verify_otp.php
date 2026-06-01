<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['reset_email'])){

    header("Location: forgot_password.php");
    exit();
}

$email =
$_SESSION['reset_email'];

if(isset($_POST['verify'])){

    $entered_otp =
    mysqli_real_escape_string(
        $conn,
        $_POST['otp']
    );

    $sql =
    "SELECT * FROM users
     WHERE email='$email'
     AND otp='$entered_otp'
     AND otp_expiry >= NOW()";

    $result =
    mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0){

        $_SESSION['otp_verified'] =
        true;

        header("Location: reset_password.php");
        exit();

    }else{

        $error =
        "Invalid or expired OTP.";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
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
            background:#16a34a;
            color:white;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:16px;
        }

        button:hover{
            background:#15803d;
        }

        .error{
            background:#fecaca;
            color:#b91c1c;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
            text-align:center;
        }

        .info{
            background:#dbeafe;
            color:#1e3a8a;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
            text-align:center;
        }
    </style>
</head>
<body>

<div class="box">

    <h2>Verify OTP</h2>

    <div class="info">
        Enter the 6-digit OTP sent to your account.
    </div>

    <?php if(isset($error)){ ?>
        <div class="error">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <input
            type="text"
            name="otp"
            placeholder="Enter OTP"
            maxlength="6"
            required>

        <button
            type="submit"
            name="verify">
            Verify OTP
        </button>

    </form>

</div>

</body>
</html>