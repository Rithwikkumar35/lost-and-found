<?php

session_start();
include 'includes/db.php';

if (isset($_POST['login'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            // Store user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            // If user is admin, store admin session variables
            // but DO NOT redirect to admin dashboard automatically
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['fullname'];
            }

            // Redirect ALL users (including admins) to normal dashboard
            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Invalid password.";
        }

    } else {
        $error = "Account not found.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>User Login - TraceBack</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#f4f4f4;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        .login-box{
            background:white;
            width:500px;
            padding:50px;
            border-radius:12px;
            box-shadow:0 0 20px rgba(0,0,0,0.1);
        }

        h2{
            text-align:center;
            margin-bottom:30px;
            font-size:48px;
            color:#111827;
        }

        input{
            width:100%;
            padding:14px;
            margin-top:15px;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:16px;
            outline:none;
        }

        input:focus{
            border-color:#2563eb;
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
            font-size:17px;
            transition:0.3s;
        }

        button:hover{
            background:#1d4ed8;
        }

        .error{
            background:#fecaca;
            color:red;
            padding:12px;
            margin-bottom:15px;
            border-radius:5px;
            text-align:center;
        }

        .links{
            text-align:center;
            margin-top:20px;
            line-height:2;
        }

        .links a{
            text-decoration:none;
            color:#2563eb;
            font-weight:bold;
        }

        .links a:hover{
            text-decoration:underline;
        }

    </style>
</head>
<body>

<div class="login-box">

    <h2>User Login</h2>

    <?php if (isset($error)) { ?>
        <div class="error">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Email"
            required>

        <input
            type="password"
            name="password"
            placeholder="Password"
            required>

        <button
            type="submit"
            name="login">
            Login
        </button>

    </form>

    <div class="links">
      <p>
    <a href="forgot_password.php">Forgot Password?</a>
</p>

        <p>
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

        <p>
            Are you an Admin?
            <a href="admin/index.php">Admin Login</a>
        </p>

    </div>

</div>

</body>
</html>