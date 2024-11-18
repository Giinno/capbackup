<?php
session_start();
require_once 'db-connect.php';

function calculateTeamScore($conn, $gameId, $team) {
    $query = "
        SELECT SUM(s.points) as total_points
        FROM statistics s
        JOIN users u ON s.first_name = u.first_name AND s.last_name = u.last_name
        WHERE s.game_id = ? AND u.team = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $gameId, $team);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total_points'];
}

function getParam($key, $default = null) {
    return isset($_GET[$key]) ? htmlspecialchars($_GET[$key]) : $default;
}

$gameId = intval(getParam('game_id', 0));

function getTeamLogos($conn, $team1, $team2) {
    $teamNamesStr = "'" . $conn->real_escape_string($team1) . "','" . $conn->real_escape_string($team2) . "'";
    $teamResult = $conn->query("SELECT team_name, team_logo FROM teams WHERE team_name IN ($teamNamesStr)");
    $teamLogos = [];
    if ($teamResult && $teamResult->num_rows > 0) {
        while ($row = $teamResult->fetch_assoc()) {
            $teamLogos[$row['team_name']] = $row['team_logo'];
        }
    }
    return $teamLogos;
}

function displayTeamStats($conn, $gameId, $team, $mvpData, $reportedScore) {
    $calculatedScore = calculateTeamScore($conn, $gameId, $team);
    $scoreDiscrepancy = $reportedScore != $calculatedScore;
    $statsQuery = "
        SELECT s.*, u.profile_picture, u.number 
        FROM statistics s
        JOIN users u ON s.first_name = u.first_name AND s.last_name = u.last_name
        WHERE s.game_id = ? AND u.team = ?
        ORDER BY 
            CASE 
                WHEN s.first_name = ? AND s.last_name = ? THEN 0 
                ELSE 1 
            END,
            s.points DESC
    ";
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param("isss", $gameId, $team, $mvpData['first_name'], $mvpData['last_name']);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<div class='team-stats-container mb-5'>";
    echo "<h3 class='team-name mb-4'>" . htmlspecialchars($team) . "</h3>";
    echo "<div class='row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4'>";

    while ($player = $result->fetch_assoc()) {
        $totalRebounds = $player["reb_def"] + $player["reb_off"];
        $isMvp = ($player["first_name"] . " " . $player["last_name"] == $mvpData['first_name'] . " " . $mvpData['last_name'] && $team == $mvpData['team']);
        $mvpClass = $isMvp ? 'mvp' : '';

        echo "<div class='col'>";
        echo "<div class='card h-60 $mvpClass'>";
        echo "<div class='card-header position-relative'>";
        echo "<img src='" . htmlspecialchars($player["profile_picture"]) . "' alt='Profile' class='profile-pic'>";
        if ($isMvp) {
            echo "<span class='mvp-label'>MVP</span>";
        }
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<h5 class='card-title'>" . htmlspecialchars($player["first_name"] . " " . $player["last_name"]) . " <small>#" . htmlspecialchars($player["number"]) . "</small></h5>";
        echo "<ul class='list-unstyled'>";
        echo "<li><strong>PTS:</strong> " . htmlspecialchars($player["points"]) . "</li>";
        echo "<li><strong>AST:</strong> " . htmlspecialchars($player["assists"]) . "</li>";
        echo "<li><strong>REB:</strong> " . htmlspecialchars($totalRebounds) . "</li>";
        echo "<li><strong>STL:</strong> " . htmlspecialchars($player["steals"]) . "</li>";
        echo "<li><strong>BLK:</strong> " . htmlspecialchars($player["blocks"]) . "</li>";
        echo "<li><strong>TO:</strong> " . htmlspecialchars($player["turnovers"]) . "</li>";
        echo "<li><strong>FLS:</strong> " . htmlspecialchars($player["fouls"]) . "</li>";
        echo "</ul>";
        echo "</div>";
        echo "<div class='card-footer'>";
        echo "<small>2PT: " . htmlspecialchars($player["2pt_made"]) . "/" . htmlspecialchars($player["2pt_attempted"]) . " | ";
        echo "3PT: " . htmlspecialchars($player["3pt_made"]) . "/" . htmlspecialchars($player["3pt_attempted"]) . " | ";
        echo "FT: " . htmlspecialchars($player["ft_made"]) . "/" . htmlspecialchars($player["ft_attempted"]) . "</small>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
        
    // Add score validation message
    if ($scoreDiscrepancy) {
        echo "<div class='alert alert-warning mt-3'>
            <strong>Score Discrepancy:</strong> Reported score ($reportedScore) does not match calculated score ($calculatedScore).
        </div>";
    }
    echo "</div></div>";
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basketball Game Box Score</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #121212; 
            color: #ffffff; 
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar { 
            background: linear-gradient(to right, #1a1a1a, #2a2a2a);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .navbar-brand, .nav-link { 
            color: #f57c00 !important; 
            transition: color 0.3s ease;
        }
        
        .navbar-brand:hover, .nav-link:hover {
            color: #ff9800 !important;
        }
        
        .team-card { 
            perspective: 1000px; 
            height: 350px; 
            margin-bottom: 40px;
        }
        
        .team-card-inner { 
            position: relative; 
            width: 100%; 
            height: 100%; 
            text-align: center; 
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1); 
            transform-style: preserve-3d; 
        }
        
        .team-card:hover .team-card-inner { 
            transform: rotateY(180deg); 
        }
        
        .team-card-front, .team-card-back { 
            position: absolute; 
            width: 100%; 
            height: 100%; 
            backface-visibility: hidden; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            background: linear-gradient(145deg, #1e1e1e, #2a2a2a);
            border-radius: 15px; 
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            padding: 2rem;
        }
        
        .team-card-back { 
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            transform: rotateY(180deg); 
        }
        
        .team-logo { 
            max-width: 180px; 
            max-height: 180px; 
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .team-card:hover .team-logo {
            transform: scale(1.05);
        }
        
        .final-score { 
            font-size: 2.5em; 
            font-weight: bold; 
            color: #f57c00;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .vs { 
            font-size: 2.5em; 
            font-weight: bold; 
            color: #f57c00;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .card { 
            border: none; 
            border-radius: 15px; 
            overflow: visible;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2); 
            margin-top: 80px; 
            padding-top: 60px; 
            background: linear-gradient(145deg, #1e1e1e, #2a2a2a);
            color: #ffffff; 
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header { 
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            height: 100px; 
            position: relative;
            padding-top: 60px; 
            border-bottom: 2px solid #f57c00;
        }
        
        .profile-pic { 
            width: 120px; 
            height: 120px; 
            object-fit: cover; 
            border-radius: 50%; 
            position: absolute; 
            top: -60px; 
            left: 50%; 
            transform: translateX(-50%); 
            border: 4px solid #f57c00; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            z-index: 1;
            transition: transform 0.3s ease;
        }
        
        .card:hover .profile-pic {
            transform: translateX(-50%) scale(1.05);
        }
        
        .mvp { 
            background: linear-gradient(145deg, #2a2a2a, rgba(245, 124, 0, 0.2));
            border: 2px solid #f57c00;
        }
        
        .mvp-label { 
            position: absolute; 
            top: 10px; 
            right: 10px; 
            background: linear-gradient(to right, #f57c00, #ff9800);
            color: #ffffff; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 0.9em; 
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .team-name { 
            color: #f57c00;
            font-size: 2em;
            font-weight: 600;
            margin: 30px 0 0 0;
            padding-bottom: 0px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .card-title {
            color: #f57c00;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .list-unstyled li {
            margin-bottom: 0.5rem;
            font-size: 0.95em;
        }
        
        .card-footer {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-top: none;
            padding: 1rem;
        }
        
        @media (max-width: 768px) {
            .team-card {
                height: 300px;
            }
            
            .team-logo {
                max-width: 140px;
                max-height: 140px;
            }
            
            .final-score {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
        <header>
            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <a href="#" class="navbar-brand">Ballers Hub</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarToggler">
                        <ul class="navbar-nav ml-auto">
                            <li class="nav-item">
                                <a href="dashboard.php" class="nav-link">Home</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" id="backButton">Back</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
    <div class="container mt-4">
        <h2 class="text-center mb-4 text-white">Basketball Game Box Score</h2>

        
        <?php
        if ($gameId > 0) {
            $gameQuery = "SELECT * FROM games WHERE game_id = ?";
            $stmt = $conn->prepare($gameQuery);
            $stmt->bind_param("i", $gameId);
            $stmt->execute();
            $gameResult = $stmt->get_result();

            if ($gameResult->num_rows > 0) {
                $game = $gameResult->fetch_assoc();
                $team1 = $game['team1'];
                $team2 = $game['team2'];
                $team1Score = $game['team1_score'];
                $team2Score = $game['team2_score'];
                $winningTeam = $team1Score > $team2Score ? $team1 : $team2;
            
                $teamLogos = getTeamLogos($conn, $team1, $team2);

                echo "<div class='row justify-content-center align-items-center mb-4'>";
                foreach ([$team1, $team2] as $index => $team) {
                    $score = $index === 0 ? $team1Score : $team2Score;
                    echo "<div class='col-md-4 team-card mb-3'>
                            <div class='team-card-inner'>
                                <div class='team-card-front'>
                                    <img src='" . htmlspecialchars($teamLogos[$team]) . "' alt='$team Logo' class='team-logo'>
                                    <h4 class='text-white'>$team</h4>
                                    <p class='final-score'>$score</p>
                                </div>
                                <div class='team-card-back'>
                                    <h4 class='text-white'>$team Stats</h4>
                                    <p>Avg Points: 0</p>
                                    <p>Assists/Game: 0</p>
                                    <p>Rebounds/Game: 0</p>
                                    <p>Blocks/Game: 0</p>
                                    <p>Steals/Game: 0</p>
                                    <p>3 Points/Game: 0</p>
                                </div>
                            </div>
                          </div>";
                    if ($index === 0) {
                        echo "<div class='col-md-2 text-center'><p class='vs'>VS</p></div>";
                    }
                }
                echo "</div>";

                // Calculate MVP for the entire game
                $mvpQuery = "
                    SELECT s.first_name, s.last_name, u.team,
                        (s.points + s.reb_def + s.reb_off + s.assists) as total_contribution
                    FROM statistics s
                    JOIN users u ON s.first_name = u.first_name AND s.last_name = u.last_name
                    WHERE s.game_id = ?
                    ORDER BY total_contribution DESC
                    LIMIT 1
                ";
                $mvpStmt = $conn->prepare($mvpQuery);
                $mvpStmt->bind_param("i", $gameId);
                $mvpStmt->execute();
                $mvpResult = $mvpStmt->get_result();
                $mvpData = $mvpResult->fetch_assoc();
                $mvpStmt->close();

                displayTeamStats($conn, $gameId, $team1, $mvpData, $team1Score);
                displayTeamStats($conn, $gameId, $team2, $mvpData, $team2Score);


                echo "<div class='text-center mt-5'>";
                echo "<h3 class='text-white'>MVP of the Game</h3>";
                echo "<p class='text-white'>" . htmlspecialchars($mvpData['first_name'] . " " . $mvpData['last_name']) . " from " . htmlspecialchars($mvpData['team']) . "</p>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-danger'>Game not found!</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Invalid game ID.</div>";
        }

        $conn->close();
        ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    document.getElementById('backButton').addEventListener('click', function(e) {
        e.preventDefault();
        if (document.referrer) {
            window.location.href = document.referrer;
        } else {
            window.history.back();
        }
    });
</script>
</body>
</html>
