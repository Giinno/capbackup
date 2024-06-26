<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
            margin-bottom: 50px;
        }
        .navbar {
            background-color: #1c1e21;
            border-bottom: 3px solid #f57c00;
        }
        .navbar-brand, .nav-link {
            color: #f57c00 !important;
            font-weight: bold;
        }
        .profile-container {
            border: none;
            padding: 20px;
            max-width: 800px;
            margin: 20px auto;
            background-color: #1f1f1f;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            transition: transform 0.3s ease;
        }
        .profile-container:hover {
            transform: translateY(-5px);
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
            border: 3px solid #f57c00;
        }
        .profile-info {
            flex: 1;
        }
        .profile-info h1 {
            margin: 0;
            font-size: 28px;
            color: #f57c00;
        }
        .profile-info h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #ffffff;
        }
        .additional-info {
            margin-top: 10px;
            color: #b3b3b3;
        }
        .average-stats {
            display: flex;
            justify-content: space-around;
            background-color: #282828;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            width: 100%;
        }
        .average-stats div {
            text-align: center;
        }
        .average-stats h2 {
            font-size: 20px;
            color: #f57c00;
        }
        .average-stats p {
            font-size: 16px;
            color: #ffffff;
        }
        .stats-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            color: #ffffff;
        }
        .stats-table th, .stats-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #444;
        }
        .stats-table th {
            background-color: #282828;
            color: #f57c00;
        }
        .stats-table tbody tr:nth-child(odd) {
            background-color: #222;
        }
        .stats-table tbody tr:nth-child(even) {
            background-color: #1a1a1a;
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="#" class="navbar-brand">Ballers Hub</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">&#9776;</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarToggler">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">Back</a>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="profile-container">
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "ballers_db";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if (isset($_GET['id'])) {
            $player_id = intval($_GET['id']);
            $sql_profile = "SELECT * FROM profiles WHERE id = $player_id";
            $result_profile = $conn->query($sql_profile);

            if ($result_profile->num_rows > 0) {
                $row_profile = $result_profile->fetch_assoc();
                $name = $row_profile['name'];
                echo "<img src='" . $row_profile['profile_picture'] . "' class='profile-picture' alt='Profile Picture'>";
                echo "<div class='profile-info'>";
                echo "<h1>" . $row_profile['name'] . "</h1>";
                echo "<h2>Number: " . $row_profile['number'] . "</h2>";
                echo "<h2>Position: " . $row_profile['position'] . "</h2>";
                echo "<h2>Team: " . $row_profile['team'] . "</h2>";
                echo "<h2>Height: " . $row_profile['height'] . "</h2>";
                echo "<h2>Born: " . $row_profile['born'] . "</h2>";
                echo "<div class='additional-info'>Additional info can be displayed here.</div>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning'>No profile found for this player.</div>";
            }

            $sql_stats = "SELECT * FROM statistics WHERE name = '$name'";
            $result_stats = $conn->query($sql_stats);

            $total_points = 0;
            $total_assists = 0;
            $total_rebounds = 0;
            $game_count = 0;

            if ($result_stats->num_rows > 0) {
                while($row_stats = $result_stats->fetch_assoc()) {
                    $totalRebounds = $row_stats["reb_def"] + $row_stats["reb_off"];
                    $total_points += $row_stats["points"];
                    $total_assists += $row_stats["assists"];
                    $total_rebounds += $totalRebounds;
                    $game_count++;
                }

                $average_points = $total_points / $game_count;
                $average_assists = $total_assists / $game_count;
                $average_rebounds = $total_rebounds / $game_count;

                echo "<div class='average-stats'>";
                echo "<div><h2>PPG</h2><p>" . number_format($average_points, 2) . "</p></div>";
                echo "<div><h2>APG</h2><p>" . number_format($average_assists, 2) . "</p></div>";
                echo "<div><h2>RPG</h2><p>" . number_format($average_rebounds, 2) . "</p></div>";
                echo "</div>";
            } else {
                echo "<div class='average-stats'>";
                echo "<h2>No Statistics Found for This Player</h2>";
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>No player selected.</div>";
        }

        $conn->close();
        ?>
    </div>
    
    <div class="container">
        <h3 class="text-center my-4">Past Games</h3>
        <?php
        if ($result_stats->num_rows > 0) {
            $result_stats->data_seek(0);

            echo '<table class="stats-table">';
            echo "<thead>";
            echo "<tr>
                    <th>Points</th>
                    <th>Assists</th>
                    <th>Def Reb</th>
                    <th>Off Reb</th>
                    <th>Rebounds</th>
                    <th>Steals</th>
                    <th>Blocks</th>
                    <th>Turnovers</th>
                    <th>Fouls</th>
                    <th>2PA</th>
                    <th>2PM</th>
                    <th>3PA</th>
                    <th>3PM</th>
                    <th>FTA</th>
                    <th>FTM</th>
                </tr>";
            echo "</thead><tbody>";

            while($row_stats = $result_stats->fetch_assoc()) {
                $totalRebounds = $row_stats["reb_def"] + $row_stats["reb_off"];
                echo "<tr>";
                echo "<td>" . $row_stats["points"] . "</td>";
                echo "<td>" . $row_stats["assists"] . "</td>";
                echo "<td>" . $row_stats["reb_def"] . "</td>";
                echo "<td>" . $row_stats["reb_off"] . "</td>";
                echo "<td>" . $totalRebounds . "</td>";
                echo "<td>" . $row_stats["steals"] . "</td>";
                echo "<td>" . $row_stats["blocks"] . "</td>";
                echo "<td>" . $row_stats["turnovers"] . "</td>";
                echo "<td>" . $row_stats["fouls"] . "</td>";
                echo "<td>" . $row_stats["2pt_attempted"] . "</td>";
                echo "<td>" . $row_stats["2pt_made"] . "</td>";
                echo "<td>" . $row_stats["3pt_attempted"] . "</td>";
                echo "<td>" . $row_stats["3pt_made"] . "</td>";
                echo "<td>" . $row_stats["ft_attempted"] . "</td>";
                echo "<td>" . $row_stats["ft_made"] . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        }
        ?>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
