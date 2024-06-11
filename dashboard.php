<?php
session_start();

// Check if the user is logged in, if not then redirect them to the login page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Redirect based on user role
if ($_SESSION['role'] === 'Statistics-admin') {
    header("Location: Stats-admin.php");
    exit;
}

if ($_SESSION['role'] === 'Scheduling-admin') {
    header("Location: Sched-admin.php");
    exit;
}

// Include your database connection file if you need to query user-specific information
include 'db-connect.php';

// Example of querying user-specific information if needed
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $email);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ballers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        body {
            background-color: #222222;
            font-family: 'Montserrat', sans-serif;
            color: #ffffff;
            margin-bottom: 50px;
            opacity: 0; /* Start hidden for the fade-in effect */
            animation: fadeIn 1s forwards; /* Fade-in animation */
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .navbar {
            background-color: #1a1a1a;
            margin-top: -25px;
        }

        .navbar-brand {
            font-weight: bold;
            color: #f57c00 !important;
        }

        .nav-link {
            color: white;
        }

        .nav-link:hover {
            color: #f57c00;
        }
        .nav-link:active {
            color: #f57c00;
        }
        
        .dropdown-item {
            color: black !important;
            font-style: italic;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background-color: #f57c00;
        }

        .card {
            margin-top: 20px;
            border: none;
            background-color: #333333;
            color: #ffffff;
        }

        .card-body {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-weight: bold;
            font-size: 1.25rem;
            color: #f57c00;
        }

        .card-text {
            font-size: 0.95rem;
        }

        .btn-primary {
            background-color: #f57c00;
            border: none;
        }

        .carousel-inner img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .container {
            margin-top: 30px;
        }

        .img-fluid {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header-banner {
            background: url('images/basketball-banner.jpg') no-repeat center center;
            background-size: cover;
            height: 300px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin-top: -80px;
            margin-bottom: -100px;
        }

        /* Ensure sufficient contrast */
        .header-banner {
            color: #f57c00;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="#" class="navbar-brand">Ballers Hub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Menu
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php">Players</a></li>
                            <li><a class="dropdown-item" href="statistics.php">Stats</a></li>
                            <li><a class="dropdown-item" href="#">Leagues</a></li>
                            <li><a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a></li>
                            <li><a class="dropdown-item" href="#">Contact us</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="header-banner">
    Welcome to Ballers Hub
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div id="carouselExample" class="carousel slide">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="images/gboys.jpg" class="d-block w-100" alt="Slide 1">
                    </div>
                    <div class="carousel-item">
                        <img src="images/gboys1.jpg" class="d-block w-100" alt="Slide 2">
                    </div>
                    <div class="carousel-item">
                        <img src="images/gboys2.jpg" class="d-block w-100" alt="Slide 3">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Golden Boys Basketball Gym</h3>
                    <p class="card-text">Golden Boys Basketball Gym is a premier, private reservable basketball court designed for enthusiasts and professionals alike. Whether you're looking to shoot some hoops with your friends, engage in a competitive match, or host a full-scale tournament, our state-of-the-art facility provides the perfect environment. With top-notch amenities and a commitment to excellence, Golden Boys Basketball Gym ensures an unmatched experience for all basketball lovers.</p>
                    <a href="schedule.php" class="btn btn-primary">Reserve now</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card new-card">
                <div class="card-body">
                    <h1 class="card-title">Jandrix Despalo of Sto Niño All-Stars</h1>
                    <p class="card-text">Jandrix Despalo exploded on the court with a performance for the ages! He put on an offensive masterclass, dropping a staggering <strong>70 points.</strong> Despalo wasn't just a scoring machine; he dominated the boards with an impressive <strong>21 rebounds.</strong> With <strong>0 assists</strong>, his focus on scoring and securing rebounds powered his team's offense. To cap off this incredible night, imagine Despalo shooting an exceptional percentage, like a scorching 75% from the field, making his scoring outburst even more impressive. This performance solidified Despalo's place as a true offensive force to be reckoned with.</p>
                    <a href="profile.php" class="btn btn-primary">View more</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <img src="images/jandrix.png" class="img-fluid rounded-start" alt="Jandrix Despalo">
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
