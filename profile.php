<?php
session_start();

// Check if the user is logged in, if not then redirect them to the login page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profiles</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        body {
            background-color: #121212; /* Dark background */
            font-size: 14px;
            margin-bottom: 50px;
            font-family: 'Roboto', Arial, sans-serif;
            color: #ffffff; /* White text color */
        }
        .navbar {
            margin-bottom: 20px;
            background-color: #1c1e21;
            border-bottom: 3px solid #f57c00;
        }
        .navbar-brand {
            font-weight: bold;
            color: #f57c00 !important;
            text-decoration: none;
            white-space: nowrap;
            letter-spacing: 1px;
        }
        .navbar-nav .nav-link {
            color: #ffffff !important;
            font-size: 15px;
            transition: color 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            color: #f57c00 !important;
        }
        .navbar-toggler {
            border: none;
            color: #f57c00;
        }
        .search-bar {
            width: 800px; /* Adjusted to match profile width */
            max-width: 100%;
            margin: 20px auto;
        }
        .profile-container {
            display: flex;
            align-items: center;
            border: 1px solid #444;
            padding: 15px;
            max-width: 800px;
            margin: 15px auto;
            background-color: #1c1e21; /* Slightly darker background */
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .profile-container:hover {
            transform: translateY(-5px);
        }
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid #f57c00;
        }
        .profile-info {
            flex-grow: 1;
        }
        .profile-info h1 {
            margin: 0;
            font-size: 22px;
            color: #f57c00;
            font-family: 'Montserrat', sans-serif;
        }
        .profile-info h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #bbbbbb; /* Lighter gray for secondary text */
            font-family: 'Montserrat', sans-serif;
        }
        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            border-top: 1px solid #555; /* Darker border */
            padding-top: 10px;
        }
        .stats div {
            text-align: center;
            flex: 1;
        }
        .stats div:not(:last-child) {
            border-right: 1px solid #555; /* Darker border */
        }
        .stats strong {
            font-size: 16px;
        }
        .stats p {
            font-size: 14px;
            margin: 0;
        }
        .additional-info {
            margin-top: 10px;
            color: #bbbbbb; /* Lighter gray for additional info */
        }
        a.name-link {
            color: #007bff;
            text-decoration: none;
        }
        a.name-link:hover {
            text-decoration: underline;
        }
        .dropdown-item:hover {
            background-color: #f57c00;
        }
        .header-banner {
            background: url('images/basketball-banner.jpg') no-repeat center center;
            background-size: cover;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin-top: -80px;
            margin-bottom: -80px;
            color: #f57c00;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            letter-spacing: 1.5px;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="#" class="navbar-brand">Ballers Hub</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">&#9776;</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Menu
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="#">Players</a>
                            <a class="dropdown-item" href="Gameresult.php">Games</a>
                            <a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a>
                            <a class="dropdown-item" href="#">Contact us</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="header-banner" style="font-family:Arial, Helvetica, sans-serif;">
    PLAYERS PROFILE
</div>

<div class="search-bar">
    <input type="text" id="searchInput" class="form-control" placeholder="Search for names..">
</div>

<div id="profileList">
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ballers_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch and display data
    $sql = "SELECT * FROM profiles";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='profile-container'>";
            echo "<img src='" . $row['profile_picture'] . "' class='profile-picture' alt='Profile Picture'>";
            echo "<div class='profile-info'>";
            echo "<h1><a href='player-profile.php?id=" . $row['id'] . "' class='name-link'>" . $row['name'] . "</a></h1>";
            echo "<h2>" . $row['team'] . "</h2>";
            echo "<div class='stats'>";
            echo "<div><strong>Number</strong><p>" . $row['number'] . "</p></div>";
            echo "<div><strong>Position</strong><p>" . $row['position'] . "</p></div>";
            echo "<div><strong>Height</strong><p>" . $row['height'] . "</p></div>";
            echo "<div><strong>Born</strong><p>" . $row['born'] . "</p></div>";
            echo "</div>";
            echo "<div class='additional-info'>Additional info can be displayed here.</div>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>No profiles found.</div>";
    }

    $conn->close();
    ?>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        let filter = this.value.toUpperCase();
        let profiles = document.getElementById('profileList').getElementsByClassName('profile-container');

        Array.from(profiles).forEach(function (profile) {
            let name = profile.getElementsByTagName('h1')[0].textContent;
            if (name.toUpperCase().indexOf(filter) > -1) {
                profile.style.display = "";
            } else {
                profile.style.display = "none";
            }
        });
    });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
