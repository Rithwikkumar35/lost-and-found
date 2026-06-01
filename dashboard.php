<?php

session_start();

if(!isset($_SESSION['user_id'])){

    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Dashboard</title>

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
    transition:0.3s;
}

header{
    background:#111827;
    color:white;
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    transition:0.3s;
}

header h2{
    font-size:34px;
}

nav{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
}

nav a{
    color:white;
    text-decoration:none;
    margin-left:22px;
    font-size:18px;
    transition:0.3s;
}

nav a:hover{
    color:#60a5fa;
}

/* Dark Mode Toggle Button */
#themeToggle{
    margin-left:22px;
    padding:8px 14px;
    background:#374151;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-size:16px;
    transition:0.3s;
}

.container{
    padding:50px;
}

.card{
    background:white;
    padding:45px;
    border-radius:14px;
    box-shadow:0 0 15px rgba(0,0,0,0.1);
    transition:0.3s;
}

.card h1{
    margin-bottom:20px;
    font-size:52px;
    color:#111827;
    transition:0.3s;
}

.card p{
    font-size:22px;
    color:#555;
    margin-bottom:35px;
    transition:0.3s;
}

.button-group{
    display:flex;
    flex-wrap:wrap;
    gap:15px;
}

.btn{
    display:inline-block;
    padding:15px 28px;
    background:#2563eb;
    color:white;
    text-decoration:none;
    border-radius:7px;
    font-size:17px;
    transition:0.3s;
}

.btn:hover{
    background:#1d4ed8;
}

.found-btn{
    background:#16a34a;
}

.found-btn:hover{
    background:#15803d;
}

.items-btn{
    background:#111827;
}

.items-btn:hover{
    background:#000;
}

.claims-btn{
    background:#f59e0b;
}

.claims-btn:hover{
    background:#d97706;
}

.profile-btn{
    background:#7c3aed;
}

.profile-btn:hover{
    background:#6d28d9;
}

.notification-btn{
    background:#0ea5e9;
}

.notification-btn:hover{
    background:#0284c7;
}

.admin-btn{
    background:#dc2626;
}

.admin-btn:hover{
    background:#b91c1c;
}

/* =======================================
   DARK MODE STYLES
======================================= */

body.dark-mode{
    background:#111827;
    color:#f9fafb;
}

body.dark-mode header{
    background:#000000;
}

body.dark-mode .card{
    background:#1f2937;
    box-shadow:0 0 15px rgba(0,0,0,0.4);
}

body.dark-mode .card h1{
    color:#ffffff;
}

body.dark-mode .card p{
    color:#d1d5db;
}

body.dark-mode nav a{
    color:#ffffff;
}

body.dark-mode nav a:hover{
    color:#93c5fd;
}

body.dark-mode #themeToggle{
    background:#fbbf24;
    color:#111827;
}

/* =======================================
   MOBILE RESPONSIVE
======================================= */

@media(max-width:768px){

    header{
        flex-direction:column;
        align-items:flex-start;
    }

    nav{
        margin-top:15px;
    }

    nav a{
        margin-left:0;
        margin-right:15px;
        margin-top:10px;
    }

    #themeToggle{
        margin-left:0;
        margin-top:10px;
    }

    .container{
        padding:20px;
    }

    .card{
        padding:30px 20px;
    }

    .card h1{
        font-size:36px;
    }

    .card p{
        font-size:18px;
    }

    .button-group{
        flex-direction:column;
    }

    .btn{
        width:100%;
        text-align:center;
    }
}

</style>

</head>

<body>

<header>

<h2>TraceBack Dashboard</h2>

<nav>

<a href="index.php">Home</a>

<a href="browse.php">Browse</a>

<a href="my_items.php">My Items</a>

<a href="my_claims.php">My Claims</a>

<a href="profile.php">Profile</a>

<a href="notifications.php">Notifications</a>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){ ?>
    <a href="admin/dashboard.php">Admin Panel</a>
<?php } ?>

<!-- Dark Mode Toggle Button -->
<button
id="themeToggle"
type="button">
🌙 Dark Mode
</button>

<a href="logout.php">Logout</a>

</nav>

</header>

<div class="container">

<div class="card">

<h1>
Welcome,
<?php echo htmlspecialchars($_SESSION['fullname']); ?>
</h1>

<p>
You are successfully logged in to TraceBack LFIS.
</p>

<div class="button-group">

<a
href="post_lost.php"
class="btn">
Post Lost Item
</a>

<a
href="post_found.php"
class="btn found-btn">
Post Found Item
</a>

<a
href="my_items.php"
class="btn items-btn">
My Items
</a>

<a
href="my_claims.php"
class="btn claims-btn">
My Claims
</a>

<a
href="profile.php"
class="btn profile-btn">
My Profile
</a>

<a
href="notifications.php"
class="btn notification-btn">
Notifications
</a>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){ ?>
<a
href="admin/dashboard.php"
class="btn admin-btn">
Admin Panel
</a>
<?php } ?>

</div>

</div>

</div>

<!-- DARK MODE SCRIPT -->
<script>

const toggleButton =
document.getElementById('themeToggle');

// Load saved theme
if(localStorage.getItem('theme') === 'dark'){

    document.body.classList.add('dark-mode');

    toggleButton.innerHTML =
    '☀️ Light Mode';
}

// Toggle theme
toggleButton.addEventListener(
'click',
function(){

    document.body.classList.toggle('dark-mode');

    if(
        document.body.classList.contains('dark-mode')
    ){

        localStorage.setItem(
            'theme',
            'dark'
        );

        toggleButton.innerHTML =
        '☀️ Light Mode';

    }else{

        localStorage.setItem(
            'theme',
            'light'
        );

        toggleButton.innerHTML =
        '🌙 Dark Mode';
    }
});

</script>

</body>
</html>