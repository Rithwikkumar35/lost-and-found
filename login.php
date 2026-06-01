<?php

session_start();
include 'includes/db.php';
include 'includes/csrf.php';
include 'includes/validation.php';

// Generate CSRF token if not exists
$csrf_token = generateCSRFToken();

$error = "";
$success = "";

if (isset($_POST['login'])) {
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        // Sanitize inputs
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        // Validate email format
        if (!validateEmail($email)) {
            $error = "Invalid email format.";
        } else if (empty($password)) {
            $error = "Password is required.";
        } else {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, fullname, email, password, role FROM users WHERE email = ?");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                $error = "Database error. Please try again.";
            } else {
                // Bind parameter
                $stmt->bind_param("s", $email);
                
                // Execute query
                if (!$stmt->execute()) {
                    error_log("Execute failed: " . $stmt->error);
                    $error = "Database error. Please try again.";
                } else {
                    // Get result
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        
                        // Verify password using password_verify (secure)
                        if (password_verify($password, $user['password'])) {
                            
                            // Regenerate session ID for security
                            session_regenerate_id(true);
                            
                            // Store user session
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['fullname'] = $user['fullname'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['role'] = $user['role'];
                            
                            // Log successful login
                            error_log("User logged in: " . $user['email'] . " at " . date('Y-m-d H:i:s'));
                            
                            // Redirect to dashboard
                            header("Location: dashboard.php");
                            exit();
                            
                        } else {
                            $error = "Invalid password.";
                            // Log failed attempt
                            error_log("Failed login attempt for: " . $email . " at " . date('Y-m-d H:i:s'));
                        }
                    } else {
                        $error = "Account not found.";
                        // Log failed attempt
                        error_log("Login attempt for non-existent account: " . $email . " at " . date('Y-m-d H:i:s'));
                    }
                }
                $stmt->close();
            }
        }
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
            color:#991b1b;
            padding:12px;
            margin-bottom:15px;
            border-radius:5px;
            text-align:center;
            border:1px solid #dc2626;
        }

        .success{
            background:#dcfce7;
            color:#166534;
            padding:12px;
            margin-bottom:15px;
            border-radius:5px;
            text-align:center;
            border:1px solid #16a34a;
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

    <?php if (!empty($error)) { ?>
        <div class="error">
            <?php echo sanitizeInput($error); ?>
        </div>
    <?php } ?>

    <?php if (!empty($success)) { ?>
        <div class="success">
            <?php echo sanitizeInput($success); ?>
        </div>
    <?php } ?>

    <form method="POST">

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

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