<?php

session_start();

include 'includes/db.php';
include 'includes/ai_questions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$questions = array();
$temp_image = "";

/*
|--------------------------------------------------------------------------
| STEP 1: Generate AI Questions
|--------------------------------------------------------------------------
*/
if (isset($_POST['generate_questions'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Upload image temporarily
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {

        $temp_image =
            time() . '_' .
            str_replace(' ', '_', $_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            "uploads/" . $temp_image
        );
    }

    // Generate AI-based dynamic questions
    $questions = generateVerificationQuestions(
        $title,
        $description,
        $category
    );
}

/*
|--------------------------------------------------------------------------
| STEP 2: Final Submit
|--------------------------------------------------------------------------
*/
if (isset($_POST['submit'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $item_type = "found";
    $user_id = $_SESSION['user_id'];

    // Use temporary uploaded image
    $image = mysqli_real_escape_string($conn, $_POST['temp_image']);

    // Generated questions
    $verify_q1 = mysqli_real_escape_string($conn, $_POST['verify_q1']);
    $verify_q2 = mysqli_real_escape_string($conn, $_POST['verify_q2']);
    $verify_q3 = mysqli_real_escape_string($conn, $_POST['verify_q3']);
    $verify_q4 = mysqli_real_escape_string($conn, $_POST['verify_q4']);

    // Finder's secret answers
    $answer1 = mysqli_real_escape_string($conn, $_POST['answer1']);
    $answer2 = mysqli_real_escape_string($conn, $_POST['answer2']);
    $answer3 = mysqli_real_escape_string($conn, $_POST['answer3']);
    $answer4 = mysqli_real_escape_string($conn, $_POST['answer4']);

    // Insert into database
    $sql = "INSERT INTO items(
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
        answer4
    ) VALUES (
        '$title',
        '$description',
        '$category',
        '$item_type',
        '$image',
        '$user_id',
        '$verify_q1',
        '$verify_q2',
        '$verify_q3',
        '$verify_q4',
        '$answer1',
        '$answer2',
        '$answer3',
        '$answer4'
    )";

    mysqli_query($conn, $sql);

    echo "<script>
            alert('Found Item Posted Successfully with AI Verification Questions!');
            window.location='dashboard.php';
          </script>";
    exit();
}

if (isset($_POST['temp_image'])) {
    $temp_image = $_POST['temp_image'];
}

$selected_category = isset($_POST['category']) ? $_POST['category'] : "";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Post Found Item</title>
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

        input,
        textarea,
        select{
            width:100%;
            padding:12px;
            margin-top:15px;
            border:1px solid #ccc;
            border-radius:5px;
            font-size:16px;
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
        }

        .generate-btn{
            background:#2563eb;
        }

        .generate-btn:hover{
            background:#1d4ed8;
        }

        .submit-btn{
            background:#16a34a;
        }

        .submit-btn:hover{
            background:#15803d;
        }

        .question-box{
            margin-top:25px;
            padding:20px;
            background:#f9fafb;
            border:1px solid #e5e7eb;
            border-radius:8px;
        }

        .question-box h3{
            margin-bottom:15px;
            color:#111827;
        }

        label{
            display:block;
            margin-top:15px;
            font-weight:bold;
            color:#374151;
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
    </style>
</head>
<body>

<div class="form-box">

    <h2>Post Found Item</h2>

    <form method="POST" enctype="multipart/form-data">

        <input
            type="text"
            name="title"
            placeholder="Item Title"
            required
            value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">

        <textarea
            name="description"
            placeholder="Description"
            required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>

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

            <input type="file" name="image" required>

        <?php } else { ?>

            <input
                type="hidden"
                name="temp_image"
                value="<?php echo htmlspecialchars($temp_image); ?>">

            <div class="preview">
                <p><strong>Uploaded Image Preview:</strong></p>
                <img src="uploads/<?php echo htmlspecialchars($temp_image); ?>">
            </div>

        <?php } ?>

        <?php if (empty($questions)) { ?>

            <button
                type="submit"
                name="generate_questions"
                class="generate-btn">
                Generate AI Verification Questions
            </button>

        <?php } else { ?>

            <input
                type="hidden"
                name="temp_image"
                value="<?php echo htmlspecialchars($temp_image); ?>">

            <div class="question-box">

                <h3>AI Generated Verification Questions</h3>

                <?php for ($i = 0; $i < 4; $i++) { ?>

                    <label>
                        <?php echo htmlspecialchars($questions[$i]); ?>
                    </label>

                    <input
                        type="text"
                        name="answer<?php echo $i + 1; ?>"
                        required>

                    <input
                        type="hidden"
                        name="verify_q<?php echo $i + 1; ?>"
                        value="<?php echo htmlspecialchars($questions[$i]); ?>">

                <?php } ?>

            </div>

            <button
                type="submit"
                name="submit"
                class="submit-btn">
                Post Found Item
            </button>

        <?php } ?>

    </form>

</div>

</body>
</html>