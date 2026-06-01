<?php

include 'includes/db.php';

if(isset($_POST['register'])){

    $fullname = $_POST['fullname'];

    $email = $_POST['email'];

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $check =
    mysqli_query(
    $conn,
    "SELECT * FROM users WHERE email='$email'"
    );

    if(mysqli_num_rows($check)>0){

        echo "Email Already Exists";

    }else{

        $sql = "INSERT INTO users(

            fullname,
            email,
            password

        )

        VALUES(

            '$fullname',
            '$email',
            '$password'

        )";

        mysqli_query($conn,$sql);

        echo "Registration Successful";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Register</title>

<style>

body{

    font-family:Arial;

    background:#f4f4f4;

    display:flex;

    justify-content:center;

    align-items:center;

    height:100vh;
}

.form-box{

    background:white;

    width:400px;

    padding:40px;

    border-radius:10px;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h2{

    text-align:center;

    margin-bottom:20px;
}

input{

    width:100%;

    padding:12px;

    margin-top:15px;
}

button{

    width:100%;

    padding:12px;

    margin-top:20px;

    background:#2563eb;

    color:white;

    border:none;

    cursor:pointer;
}

a{

    text-decoration:none;
}

</style>

</head>

<body>

<div class="form-box">

<h2>Create Account</h2>

<form method="POST">

<input
type="text"
name="fullname"
placeholder="Full Name"
required>

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

<button name="register">
Register
</button>

</form>

<p style="margin-top:20px;text-align:center;">

Already have account?

<a href="login.php">
Login
</a>

</p>

</div>

</body>
</html>