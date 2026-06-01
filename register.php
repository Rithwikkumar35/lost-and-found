<?php

include 'includes/db.php';
include 'includes/csrf.php';
include 'includes/validation.php';

// Generate CSRF token if not exists
$csrf_token = generateCSRFToken();

$error = "";
$success = "";

if(isset($_POST['register'])){
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        
        // Sanitize and validate inputs
        $fullname = sanitizeInput($_POST['fullname'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation checks
        $hasError = false;
        
        if (empty($fullname)) {
            $error = "Full name is required.";
            $hasError = true;
        } else if (strlen($fullname) < 2 || strlen($fullname) > 100) {
            $error = "Full name must be between 2 and 100 characters.";
            $hasError = true;
        } else if (!validateEmail($email)) {
            $error = "Invalid email format.";
            $hasError = true;
        } else if (empty($password)) {
            $error = "Password is required.";
            $hasError = true;
        } else if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
            $hasError = true;
        } else {
            // Validate password strength
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $error = $passwordValidation['message'];
                $hasError = true;
            }
        }
        
        if (!$hasError) {
            
            // Check if email already exists using prepared statement
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            
            if (!$stmt) {
                error_log("Prepare failed: " . $conn->error);
                $error = "Database error. Please try again.";
            } else {
                
                $stmt->bind_param("s", $email);
                
                if (!$stmt->execute()) {
                    error_log("Execute failed: " . $stmt->error);
                    $error = "Database error. Please try again.";
                } else {
                    
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $error = "Email already exists. Please use a different email or <a href='login.php'>login</a>.";
                    } else {
                        
                        // Hash password securely
                        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                        
                        // Insert new user using prepared statement
                        $insertStmt = $conn->prepare(
                            "INSERT INTO users (fullname, email, password, role, created_at) 
                             VALUES (?, ?, ?, 'user', NOW())"
                        );
                        
                        if (!$insertStmt) {
                            error_log("Prepare failed: " . $conn->error);
                            $error = "Database error. Please try again.";
                        } else {
                            
                            $role = 'user'; // Default role
                            $insertStmt->bind_param("sss", $fullname, $email, $hashedPassword);
                            
                            if (!$insertStmt->execute()) {
                                error_log("Execute failed: " . $insertStmt->error);
                                $error = "Registration failed. Please try again.";
                            } else {
                                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                                // Log successful registration
                                error_log("New user registered: " . $email . " at " . date('Y-m-d H:i:s'));
                                
                                // Clear form
                                $_POST = array();
                            }
                            
                            $insertStmt->close();
                        }
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

<title>Register - TraceBack</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

body{

    font-family:Arial;

    background:#f4f4f4;

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;
    
    padding: 20px;
}

.form-box{

    background:white;

    width:100%;
    
    max-width: 450px;

    padding:40px;

    border-radius:10px;

    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h2{

    text-align:center;

    margin-bottom:20px;
    
    color: #111827;
    
    font-size: 32px;
}

input{

    width:100%;

    padding:12px;

    margin-top:15px;
    
    border:1px solid #ccc;
    
    border-radius: 6px;
    
    font-size: 16px;
    
    outline: none;
}

input:focus{

    border-color: #2563eb;
}

button{

    width:100%;

    padding:12px;

    margin-top:20px;

    background:#2563eb;

    color:white;

    border:none;
    
    border-radius: 6px;
    
    cursor:pointer;
    
    font-size: 16px;
    
    transition: 0.3s;
}

button:hover{

    background: #1d4ed8;
}

.error{

    background:#fecaca;

    color:#991b1b;

    padding:12px;

    margin-bottom:15px;

    border-radius:5px;
    
    text-align:center;
    
    border: 1px solid #dc2626;
}

.success{

    background:#dcfce7;

    color:#166534;

    padding:12px;

    margin-bottom:15px;

    border-radius:5px;
    
    text-align:center;
    
    border: 1px solid #16a34a;
}

.success a{
    color: #166534;
    text-decoration: underline;
    font-weight: bold;
}

.error a{
    color: #991b1b;
    text-decoration: underline;
    font-weight: bold;
}

a{

    text-decoration:none;
}

p{
    margin-top:20px;
    text-align:center;
}

p a{
    color: #2563eb;
    text-decoration: none;
    font-weight: bold;
}

p a:hover{
    text-decoration: underline;
}

.password-hint{
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 12px;
    margin-top: 10px;
    font-size: 13px;
    color: #1e40af;
    line-height: 1.6;
}

</style>

</head>

<body>

<div class="form-box">

<h2>Create Account</h2>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo $error; ?></div>
<?php } ?>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo $success; ?></div>
<?php } ?>

<form method="POST">

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <input
        type="text"
        name="fullname"
        placeholder="Full Name"
        required
        value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">

    <input
        type="email"
        name="email"
        placeholder="Email"
        required
        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

    <input
        type="password"
        name="password"
        placeholder="Password (Min 8 chars, 1 uppercase, 1 lowercase, 1 number)"
        required>

    <input
        type="password"
        name="confirm_password"
        placeholder="Confirm Password"
        required>

    <div class="password-hint">
        <strong>Password Requirements:</strong>
        <ul style="margin: 8px 0 0 20px;">
            <li>Minimum 8 characters</li>
            <li>At least 1 uppercase letter (A-Z)</li>
            <li>At least 1 lowercase letter (a-z)</li>
            <li>At least 1 number (0-9)</li>
        </ul>
    </div>

    <button name="register" type="submit">
        Register
    </button>

</form>

<p>
    Already have an account?
    <a href="login.php">Login</a>
</p>

</div>

</body>
</html>