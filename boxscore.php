<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basketball Game Box Score</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            font-size: 14px;
            padding: 20px;
        }
        h2, h4 {
            color: #F57C00;
            text-align: center;
            margin-bottom: 20px;
        }
        .team-card {
            perspective: 1000px;
            margin-bottom: 20px;
        }
        .team-card-inner {
            position: relative;
            width: 100%;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }
        .team-card:hover .team-card-inner {
            transform: rotateY(180deg);
        }
        .team-card-front, .team-card-back {
            position: absolute;
            width: 100%;
            backface-visibility: hidden;
            border: none;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background-color: #333333;
        }
        .team-card-back {
            transform: rotateY(180deg);
        }
        .team-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }
        .final-score {
            font-size: 24px;
            font-weight: bold;
            color: #F57C00;
        }
        .vs {
            font-size: 30px;
            color: #ffffff;
        }
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #444444;
            color: #ffffff;
        }
        th {
            background-color: #333333;
        }
        img {
            border-radius: 50%;
        }
        .alert {
            background-color: #444444;
            border: none;
            color: #ffffff;
        }
        .back {
            color: #ffffff;
            font-size: 15px;
            float: right;
        }
        .back:hover {
            color: #F57C00;
        }
        .mvp {
            background-color: #F57C00;
            color: #222222;
        }
        .mvp-label {
            background-color: #F57C00;
            color: #222222;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 10px;
            font-weight: bold;
        }
        .team-section{
            margin-top: 200px;
            margin-bottom: -200px;
        }
    </style>
</head>
<body>
    <header>
        <div><a href="Gameresult.php" class="back">Back</a></div>
    </header>
    <div class="container">
        <h2>Basketball Game Box Score</h2>

        <?php
        require 'db-connect.php';

        $gameId = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

        if ($gameId > 0) {
            $gameQuery = "SELECT * FROM games WHERE game_id = $gameId";
            $gameResult = $conn->query($gameQuery);

            if ($gameResult->num_rows > 0) {
                $game = $gameResult->fetch_assoc();
                $team1 = $game['team1'];
                $team2 = $game['team2'];
                $team1Score = $game['team1_score'];
                $team2Score = $game['team2_score'];

                $teamLogos = [];
                $teamNamesStr = "'" . $team1 . "','" . $team2 . "'";
                $teamResult = $conn->query("SELECT team_name, team_logo FROM teams WHERE team_name IN ($teamNamesStr)");
                if ($teamResult->num_rows > 0) {
                    while ($row = $teamResult->fetch_assoc()) {
                        $teamLogos[$row['team_name']] = $row['team_logo'];
                    }
                }
                echo "<div class='row justify-content-center align-items-center'>";
                echo "<div class='col-md-4 team-card'>
                        <div class='team-card-inner'>
                            <div class='team-card-front'>
                                <img src='" . htmlspecialchars($teamLogos[$team1]) . "' alt='$team1 Logo' class='team-logo'>
                                <p class='final-score'>$team1: $team1Score</p>
                            </div>
                            <div class='team-card-back'>
                                <h4>$team1 Stats</h4>
                                <p>Average Total Points: 0</p>
                                <p>Assists per Game: 0</p>
                                <p>Rebounds per Game: 0</p>
                                <p>Blocks per Game: 0</p>
                                <p>Steals per Game: 0</p>
                                <p>3 Points per Game: 0</p>
                            </div>
                        </div>
                    </div>";
                echo "<div class='col-md-2 text-center'><p class='vs'>VS</p></div>";
                echo "<div class='col-md-4 team-card'>
                        <div class='team-card-inner'>
                            <div class='team-card-front'>
                                <img src='" . htmlspecialchars($teamLogos[$team2]) . "' alt='$team2 Logo' class='team-logo'>
                                <p class='final-score'>$team2: $team2Score</p>
                            </div>
                            <div class='team-card-back'>
                                <h4>$team2 Stats</h4>
                                <p>Average Total Points: 0</p>
                                <p>Assists per Game: 0</p>
                                <p>Rebounds per Game: 0</p>
                                <p>Blocks per Game: 0</p>
                                <p>Steals per Game: 0</p>
                                <p>3 Points per Game: 0</p>
                            </div>
                        </div>
                    </div>";
                echo "</div>";

                function displayTeamStats($conn, $gameId, $team, &$mvpData) {
                    echo "<div class='team-section'>";
                    echo "<h4>Team: $team</h4>";
                    echo '<div class="table-container">';
                    echo '<table class="table table-striped table-dark">';
                    echo "<thead>";
                    echo "<tr>
                            <th>Profile Picture</th>
                            <th>Name</th>
                            <th>Number</th>
                            <th>Pts</th>
                            <th>Ast</th>
                            <th>Def Reb</th>
                            <th>Off Reb</th>
                            <th>Reb</th>
                            <th>Stl</th>
                            <th>Blk</th>
                            <th>TO</th>
                            <th>F</th>
                            <th>2PA</th>
                            <th>2PM</th>
                            <th>3PA</th>
                            <th>3PM</th>
                            <th>FTA</th>
                            <th>FTM</th>
                          </tr>";
                    echo "</thead><tbody>";

                    $statsQuery = "
                        SELECT s.*, p.profile_picture, p.number 
                        FROM statistics s
                        JOIN profiles p ON s.name = p.name
                        WHERE s.game_id = $gameId AND p.team = '$team'
                    ";
                    $statsResult = $conn->query($statsQuery);

                    if ($statsResult->num_rows > 0) {
                        while ($player = $statsResult->fetch_assoc()) {
                            $totalRebounds = $player["reb_def"] + $player["reb_off"];
                            $profilePicture = $player["profile_picture"];
                            $totalContribution = $player["points"] + $totalRebounds + $player["assists"];
                            
                            if ($totalContribution > $mvpData['contribution']) {
                                $mvpData = [
                                    'contribution' => $totalContribution,
                                    'player' => $player["name"]
                                ];
                            }

                            $isMvp = ($player["name"] == $mvpData['player']);
                            $mvpClass = $isMvp ? 'mvp' : '';

                            echo "<tr class='$mvpClass'>";
                            echo "<td>";
                            if ($isMvp) {
                                echo "<span class='mvp-label'>MVP</span>";
                            }
                            echo "<img src='" . htmlspecialchars($profilePicture) . "' alt='Profile Picture' width='50' height='50'></td>";
                            echo "<td>" . htmlspecialchars($player["name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["number"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["points"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["assists"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["reb_def"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["reb_off"]) . "</td>";
                            echo "<td>" . htmlspecialchars($totalRebounds) . "</td>";
                            echo "<td>" . htmlspecialchars($player["steals"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["blocks"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["turnovers"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["fouls"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["2pt_attempted"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["2pt_made"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["3pt_attempted"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["3pt_made"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["ft_attempted"]) . "</td>";
                            echo "<td>" . htmlspecialchars($player["ft_made"]) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='18' class='text-center'>No data available</td></tr>";
                    }

                    echo "</tbody></table></div>";
                    echo "</div>";
                }

                $mvpData = ['contribution' => 0, 'player' => ''];

                displayTeamStats($conn, $gameId, $team1, $mvpData);
                displayTeamStats($conn, $gameId, $team2, $mvpData);

            } else {
                echo "<div class='alert alert-warning text-center'>Game not found</div>";
            }
        } else {
            echo "<div class='alert alert-warning text-center'>Invalid game ID</div>";
        }

        $conn->close();
        ?>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
