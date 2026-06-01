<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>TraceBack LFIS</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:Arial;
    background:#f4f4f4;
}

header{
    background:#111827;
    color:white;
    padding:20px 50px;

    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    font-size:30px;
    font-weight:bold;
}

nav a{
    color:white;
    text-decoration:none;
    margin-left:20px;
    font-size:18px;
    transition:0.3s;
}

nav a:hover{
    color:#60a5fa;
}

.hero{

    height:90vh;

    display:flex;
    justify-content:center;
    align-items:center;
    flex-direction:column;

    text-align:center;

    background:linear-gradient(
    rgba(0,0,0,0.5),
    rgba(0,0,0,0.5)),

    url('https://images.unsplash.com/photo-1521791136064-7986c2920216?q=80&w=1200');

    background-size:cover;
    background-position:center;

    color:white;
}

.hero h1{
    font-size:60px;
    margin-bottom:20px;
}

.hero p{
    font-size:22px;
    width:70%;
    line-height:1.6;
}

.btn{

    margin-top:30px;

    display:inline-block;

    background:#2563eb;

    color:white;

    padding:15px 35px;

    text-decoration:none;

    border-radius:5px;

    font-size:18px;

    transition:0.3s;
}

.btn:hover{
    background:#1d4ed8;
}

.features{

    background:white;

    padding:80px 50px;
}

.features h2{

    text-align:center;

    margin-bottom:50px;

    font-size:40px;
}

.cards{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(250px,1fr));

    gap:30px;
}

.card{

    background:#f9f9f9;

    padding:30px;

    border-radius:10px;

    text-align:center;

    box-shadow:0 0 10px rgba(0,0,0,0.1);

    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3{
    margin-bottom:15px;
}

footer{

    background:#111827;

    color:white;

    text-align:center;

    padding:20px;
}

@media(max-width:768px){

    header{
        flex-direction:column;
    }

    nav{
        margin-top:15px;
    }

    .hero h1{
        font-size:40px;
    }

    .hero p{
        width:90%;
        font-size:18px;
    }
}

</style>

</head>

<body>

<header>

<div class="logo">
TraceBack
</div>

<nav>

<a href="index.php">Home</a>

<a href="browse.php">Browse</a>

<a href="login.php">Login</a>

<a href="register.php">Register</a>

</nav>

</header>

<section class="hero">

<h1>Smart Lost & Found System</h1>

<p>

Connecting people with their lost belongings
through smart, secure and efficient technology.

</p>

<a href="register.php" class="btn">
Get Started
</a>

</section>

<section class="features">

<h2>Our Features</h2>

<div class="cards">

<div class="card">

<h3>Lost Item Reporting</h3>

<p>
Users can report lost items with images and details.
</p>

</div>

<div class="card">

<h3>Found Item Listings</h3>

<p>
Found belongings can be uploaded for recovery.
</p>

</div>

<div class="card">

<h3>Claim Verification</h3>

<p>
Admins verify ownership before approval.
</p>

</div>

<div class="card">

<h3>Quick Search</h3>

<p>
Search items using categories and keywords.
</p>

</div>

</div>

</section>

<footer>

<p>

© 2026 TraceBack LFIS | Developed by Rithwik

</p>

</footer>

</body>
</html>