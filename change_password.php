<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");

    exit();
}

$user_id =
$_SESSION['user_id'];

if(isset($_POST['change'])){

    $old_password =
    $_POST['old_password'];

    $new_password =
    $_POST['new_password'];

    $confirm_password =
    $_POST['confirm_password'];

    $sql =
    "SELECT * FROM users
    WHERE id='$user_id'";

    $result =
    mysqli_query($conn,$sql);

    $user =
    mysqli_fetch_assoc($result);

    if(
    password_verify(
    $old_password,
    $user['password']
    )
    ){

        if(
        strlen($new_password) < 6
        ){

            $error =
            "New Password Must Be At Least 6 Characters";

        }else{

            if(
            $new_password ==
            $confirm_password
            ){

                $hashed_password =
                password_hash(
                $new_password,
                PASSWORD_DEFAULT
                );

                mysqli_query(
                $conn,
                "UPDATE users
                SET password='$hashed_password'
                WHERE id='$user_id'"
                );

                $success =
                "Password Changed Successfully";

            }else{

                $error =
                "New Password And Confirm Password Do Not Match";
            }
        }

    }else{

        $error =
        "Old Password Incorrect";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Change Password</title>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<style>

*{

    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    margin:0;

    font-family:Arial;

    background:#f4f4f4;

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;

    padding:20px;
}

.box{

    background:white;

    width:450px;

    padding:40px;

    border-radius:12px;

    box-shadow:0 0 15px rgba(0,0,0,0.1);
}

.icon{

    text-align:center;

    font-size:55px;

    margin-bottom:10px;
}

h2{

    text-align:center;

    margin-bottom:30px;

    color:#111827;

    font-size:34px;
}

input{

    width:100%;

    padding:14px;

    margin-top:15px;

    border:1px solid #ccc;

    border-radius:6px;

    box-sizing:border-box;

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

    font-size:16px;

    transition:0.3s;
}

button:hover{

    background:#1d4ed8;
}

.success{

    background:#bbf7d0;

    color:green;

    padding:12px;

    border-radius:5px;

    margin-bottom:15px;

    text-align:center;
}

.error{

    background:#fecaca;

    color:red;

    padding:12px;

    border-radius:5px;

    margin-bottom:15px;

    text-align:center;
}

.back{

    text-align:center;

    margin-top:20px;
}

.back a{

    text-decoration:none;

    color:#2563eb;

    font-weight:bold;
}

.back a:hover{

    text-decoration:underline;
}

@media(max-width:500px){

    .box{

        width:100%;

        padding:30px 20px;
    }

    h2{

        font-size:28px;
    }
}

</style>

</head>

<body>

<div class="box">

<div class="icon">

🔒

</div>

<h2>Change Password</h2>

<?php

if(isset($success)){

?>

<div class="success">

<?php
echo $success;
?>

</div>

<?php

}

?>

<?php

if(isset($error)){

?>

<div class="error">

<?php
echo $error;
?>

</div>

<?php

}

?>

<form method="POST">

<input
type="password"
name="old_password"
placeholder="Old Password"
required>

<input
type="password"
name="new_password"
placeholder="New Password"
required>

<input
type="password"
name="confirm_password"
placeholder="Confirm New Password"
required>

<button
type="submit"
name="change">

Change Password

</button>

</form>

<div class="back">

<a href="profile.php">

← Back to Profile

</a>

</div>

</div>

</body>
</html>