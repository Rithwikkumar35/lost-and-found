<?php

session_start();

include 'includes/db.php';
include 'includes/csrf.php';
include 'includes/validation.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
$csrf_token = generateCSRFToken();

$questions = array();
$temp_image = "";
$error = "";
$success = "";

// Create uploads directory if it doesn't exist
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
}

/*
|--------------------------------------------------------------------------
| STEP 1: Generate AI Questions
|--------------------------------------------------------------------------
*/
if (isset($_POST['generate_questions'])) {
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        
        // Validate required fields
        if (empty($title) || empty($description) || empty($category)) {
            $error = "All fields are required.";
        } else if (strlen($title) < 3 || strlen($title) > 200) {
            $error = "Title must be between 3 and 200 characters.";
        } else if (strlen($description) < 10 || strlen($description) > 2000) {
            $error = "Description must be between 10 and 2000 characters.";
        } else {
            
            // Validate file upload
            if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
                
                $fileValidation = validateFileUpload($_FILES['image']);
                
                if (!$fileValidation['valid']) {
                    $error = $fileValidation['message'];
                } else {
                    
                    // Sanitize filename and move file
                    $temp_image = sanitizeFilename($_FILES['image']['name']);
                    $upload_path = "uploads/" . $temp_image;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        error_log("File upload failed for: " . $temp_image);
                        $error = "Failed to upload image. Please try again.";
                    } else {
                        // Generate AI Questions
                        include 'includes/ai_questions.php';
                        $questions = generateVerificationQuestions($title, $description, $category);
                        $success = "Image uploaded and questions generated successfully!";
                    }
                }
            } else {
                $error = "Please upload an image.";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| STEP 2: Final Submit
|--------------------------------------------------------------------------
*/
if (isset($_POST['submit'])) {
    
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please try again.";
    } else {
        
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $temp_image = sanitizeInput($_POST['temp_image'] ?? '');
        
        // Validate fields
        if (empty($title) || empty($description) || empty($category) || empty($temp_image)) {
            $error = "All fields are required.";
        } else {
            
            $item_type = "lost";
            $user_id = (int)$_SESSION['user_id'];
            
            // Verify image file exists
            if (!file_exists("uploads/" . $temp_image)) {
                $error = "Image file not found. Please upload again.";
            } else {
                
                // Get verification answers
                $verify_q1 = sanitizeInput($_POST['verify_q1'] ?? '');
                $verify_q2 = sanitizeInput($_POST['verify_q2'] ?? '');
                $verify_q3 = sanitizeInput($_POST['verify_q3'] ?? '');
                $verify_q4 = sanitizeInput($_POST['verify_q4'] ?? '');
                
                $answer1 = sanitizeInput($_POST['answer1'] ?? '');
                $answer2 = sanitizeInput($_POST['answer2'] ?? '');
                $answer3 = sanitizeInput($_POST['answer3'] ?? '');
                $answer4 = sanitizeInput($_POST['answer4'] ?? '');
                
                // Validate answers
                if (empty($answer1) || empty($answer2) || empty($answer3) || empty($answer4)) {
                    $error = "Please answer all verification questions.";
                } else {
                    
                    // Use prepared statement to insert item
                    $stmt = $conn->prepare(
                        "INSERT INTO items(
                            title,
                            description,
                            category,
                            item_type,
                            image,
                            user_id,
                            verify_q1,
                            verify_q2,
                            verify_q3,
                            verify_q4,
                            answer1,
                            answer2,
                            answer3,
                            answer4,
                            status,
                            created_at
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?,
                            ?, ?, ?, ?, ?, ?,
                            ?, ?, 'active', NOW()
                        )"
                    );
                    
                    if (!$stmt) {
                        error_log("Prepare failed: " . $conn->error);
                        $error = "Database error. Please try again.";
                    } else {
                        
                        $status = "active";
                        $stmt->bind_param(
                            "sssssissssssss",
                            $title, $description, $category, $item_type, $temp_image, $user_id,
                            $verify_q1, $verify_q2, $verify_q3, $verify_q4,
                            $answer1, $answer2, $answer3, $answer4
                        );
                        
                        if (!$stmt->execute()) {
                            error_log("Execute failed: " . $stmt->error);
                            $error = "Failed to post item. Please try again.";
                        } else {
                            $success = "Lost Item Posted Successfully!";
                            error_log("Lost item posted by user: " . $user_id);
                            
                            // Redirect after 2 seconds
                            echo "<script>setTimeout(function(){ window.location='dashboard.php'; }, 2000);</script>";
                            $questions = array();
                            $temp_image = "";
                            $_POST = array();
                        }
                        
                        $stmt->close();
                    }
                }
            }
        }
    }
}

if (isset($_POST['temp_image'])) {
    $temp_image = sanitizeInput($_POST['temp_image']);
}

$selected_category = isset($_POST['category']) ? sanitizeInput($_POST['category']) : "";

?>
<!DOCTYPE html>
<html>
<head>
<title>Post Lost Item - TraceBack</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:Arial;
    background:#f4f4f4;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
}
.form-box{
    background:white;
    width:100%;
    max-width:600px;
    padding:40px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}
h2{
    text-align:center;
    margin-bottom:20px;
    color:#111827;
}
.error{
    background:#fecaca;
    color:#991b1b;
    padding:12px;
    margin-bottom:15px;
    border-radius:5px;
    border:1px solid #dc2626;
}
.success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    margin-bottom:15px;
    border-radius:5px;
    border:1px solid #16a34a;
}
input, textarea, select{
    width:100%;
    padding:12px;
    margin-top:15px;
    border:1px solid #ccc;
    border-radius:5px;
    font-size:16px;
    font-family: Arial;
}
input:focus, textarea:focus, select:focus{
    border-color:#2563eb;
    outline:none;
}
textarea{
    height:120px;
    resize:none;
}
button{
    width:100%;
    padding:12px;
    margin-top:20px;
    color:white;
    border:none;
    border-radius:5px;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
}
.generate-btn{background:#2563eb;}
.generate-btn:hover{background:#1d4ed8;}
.submit-btn{background:#16a34a;}
.submit-btn:hover{background:#15803d;}
.question-box{
    margin-top:25px;
    padding:20px;
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:8px;
}
label{
    display:block;
    margin-top:15px;
    font-weight:bold;
}
.preview{
    margin-top:15px;
    text-align:center;
}
.preview img{
    max-width:200px;
    border-radius:8px;
    border:1px solid #ddd;
}
.file-input-label{
    display:block;
    margin-top:15px;
    padding:12px;
    background:#eff6ff;
    border:2px dashed #2563eb;
    border-radius:5px;
    text-align:center;
    cursor:pointer;
    color:#2563eb;
    font-weight:bold;
}
.file-input-label:hover{
    background:#bfdbfe;
}
input[type="file"]{
    display:none;
}
</style>
</head>
<body>

<div class="form-box">

<h2>Post Lost Item</h2>

<?php if (!empty($error)) { ?>
    <div class="error"><?php echo $error; ?></div>
<?php } ?>

<?php if (!empty($success)) { ?>
    <div class="success"><?php echo $success; ?></div>
<?php } ?>

<form method="POST" enctype="multipart/form-data">

    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <input
        type="text"
        name="title"
        placeholder="Item Title"
        required
        maxlength="200"
        value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8') : ''; ?>">

    <textarea
        name="description"
        placeholder="Description (10-2000 characters)"
        required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>

    <select name="category" required>
        <option value="">Select Category</option>
        <option value="Mobile" <?php if($selected_category=="Mobile") echo "selected"; ?>>Mobile</option>
        <option value="Wallet" <?php if($selected_category=="Wallet") echo "selected"; ?>>Wallet</option>
        <option value="ID Card" <?php if($selected_category=="ID Card") echo "selected"; ?>>ID Card</option>
        <option value="Bag" <?php if($selected_category=="Bag") echo "selected"; ?>>Bag</option>
        <option value="Watch" <?php if($selected_category=="Watch") echo "selected"; ?>>Watch</option>
        <option value="Laptop" <?php if($selected_category=="Laptop") echo "selected"; ?>>Laptop</option>
        <option value="Keys" <?php if($selected_category=="Keys") echo "selected"; ?>>Keys</option>
        <option value="Other" <?php if($selected_category=="Other") echo "selected"; ?>>Other</option>
    </select>

    <?php if (empty($temp_image)) { ?>

        <label for="image-input" class="file-input-label">
            📷 Click to upload image (JPG, PNG, GIF - Max 5MB)
        </label>
        <input type="file" id="image-input" name="image" accept="image/*" required>

    <?php } else { ?>

        <input type="hidden" name="temp_image" value="<?php echo htmlspecialchars($temp_image, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="preview">
            <p><strong>Uploaded Image Preview:</strong></p>
            <img src="uploads/<?php echo htmlspecialchars($temp_image, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="Uploaded image">
        </div>

    <?php } ?>

    <?php if (empty($questions)) { ?>

        <button type="submit"
                name="generate_questions"
                class="generate-btn">
            Generate AI Verification Questions
        </button>

    <?php } else { ?>

        <input type="hidden" name="temp_image"
               value="<?php echo htmlspecialchars($temp_image, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="question-box">
            <h3>AI Generated Verification Questions</h3>
            <p style="color:#666; font-size:14px; margin-top:10px;">Answer these questions correctly so the owner can verify their ownership.</p>

            <?php for ($i = 0; $i < 4; $i++) { ?>

                <label><?php echo htmlspecialchars($questions[$i], ENT_QUOTES, 'UTF-8'); ?></label>

                <input
                    type="text"
                    name="answer<?php echo $i + 1; ?>"
                    placeholder="Your answer"
                    required>

                <input
                    type="hidden"
                    name="verify_q<?php echo $i + 1; ?>"
                    value="<?php echo htmlspecialchars($questions[$i], ENT_QUOTES, 'UTF-8'); ?>">

            <?php } ?>

        </div>

        <button type="submit"
                name="submit"
                class="submit-btn">
            Post Lost Item
        </button>

    <?php } ?>

</form>

</div>

<script>
document.getElementById('image-input')?.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        // Client-side validation
        var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        var maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!validTypes.includes(file.type)) {
            alert('Invalid file type. Please upload JPG, PNG, or GIF.');
            e.target.value = '';
            return;
        }
        
        if (file.size > maxSize) {
            alert('File is too large. Maximum size is 5MB.');
            e.target.value = '';
            return;
        }
    }
});
</script>

</body>
</html>