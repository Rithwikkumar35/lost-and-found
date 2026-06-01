<?php

session_start();

include 'includes/db.php';

if(
    !isset($_SESSION['reset_email']) ||
    !isset($_SESSION['otp_verified'])
){

    header("Location: forgot_password.php");
    exit();
}

$email =
$_SESSION['reset_email'];

if(isset($_POST['reset'])){

    $new_password =
    $_POST['password'];

    $hashed_password =
    password_hash(
        $new_password,
        PASSWORD_DEFAULT
    );

    mysqli_query(
        $conn,
        "UPDATE users
         SET password='$hashed_password',
             otp=NULL,
             otp_expiry=NULL
         WHERE email='$email'"
    );

    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_verified']);

    $success =
    "Password reset successfully.";

    header("Refresh:3; url=login.php");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
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
            background:#dc2626;
            color:white;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:16px;
        }

        button:hover{
            background:#b91c1c;
        }

        .success{
            background:#dcfce7;
            color:#166534;
            padding:12px;
            border-radius:6px;
            margin-bottom:15px;
            text-align:center;
        }
    </style>
</head>
<body>

<div class="box">

    <h2>Reset Password</h2>

    <?php if(isset($success)){ ?>
        <div class="success">
            <?php echo $success; ?>
            <br><br>
            Redirecting to login...
        </div>
    <?php } ?>

    <form method="POST">

        <input
            type="password"
            name="password"
            placeholder="Enter New Password"
            required>

        <button
            type="submit"
            name="reset">
            Reset Password
        </button>

    </form>

</div>

</body>
</html>