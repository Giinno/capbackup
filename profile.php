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
            background-color: #222222; /* Dark background */
            font-size: 12px;
            margin-bottom: 50px;
            font-family: 'Roboto', Arial, sans-serif;
            color: #ffffff; /* White text color */
        }
        .navbar {
            margin-bottom: 20px;
            background-color: #1a1a1a;
        }
        .navbar-brand {
            font-weight: bold;
            color: #f57c00 !important;
            text-decoration: none;
            white-space: nowrap;
            letter-spacing: 1px;
        }
        .search-bar {
            width: 800px; /* Adjusted to match profile width */
            max-width: 100%;
            margin: 20px auto;
        }
        .profile-container {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            padding: 15px;
            max-width: 800px;
            margin: 15px auto;
            background-color: #333333; /* Dark gray background */
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .profile-info {
            flex-grow: 1;
        }
        .profile-info h1 {
            margin: 0;
            font-size: 20px;
            color: #f57c00;
            font-family: 'Montserrat', sans-serif;
        }
        .profile-info h2 {
            margin: 5px 0;
            font-size: 16px;
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
            font-size: 14px;
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
        .nav-item:hover {
            color: #f57c00;
        }
    </style>
</head>
<body>
<header>
        <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="#" class="navbar-brand">Ballers Hub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
                <ul class="navbar-nav ml-auto mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-expanded="false" style="color:#ffffff; font-size: 15px;">
                            Menu
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="#">Players</a></li>
                            <li><a class="dropdown-item" href="statistics.php">Stats</a></li>
                            <li><a class="dropdown-item" href="#">Leagues</a></li>
                            <li><a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a></li>
                            <li><a class="dropdown-item" href="#">Contact us</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="collaps -navbar-collaps" id="navmenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link" style="color:#ffffff; font-size: 15px;" >Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <h2 class="text-center my-4">Player Profiles</h2>

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
