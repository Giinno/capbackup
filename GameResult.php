<?php
// Include the database connection file
require 'db-connect.php';

// Fetch all game results
$gamesResult = $conn->query("SELECT * FROM games ORDER BY game_date DESC");

$games = [];
if ($gamesResult->num_rows > 0) {
    while ($row = $gamesResult->fetch_assoc()) {
        $games[] = $row;
    }
}

// Fetch the team logos
$teams = [];
if (count($games) > 0) {
    $teamNames = array_unique(array_merge(array_column($games, 'team1'), array_column($games, 'team2')));
    $teamNamesStr = "'" . implode("','", $teamNames) . "'";

    $teamResult = $conn->query("SELECT team_name, team_logo FROM teams WHERE team_name IN ($teamNamesStr)");
    if ($teamResult->num_rows > 0) {
        while ($row = $teamResult->fetch_assoc()) {
            $teams[$row['team_name']] = $row['team_logo'];
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
            margin-bottom: 50px;
        }
        .navbar {
            background-color: #1c1e21;
            border-bottom: 3px solid #f57c00;
            margin-bottom: 25px;
        }
        .navbar-brand {
            color: #f57c00 !important;
            font-weight: bold;
        }
        .nav-link {
            color: #ffffff;
        }
        .nav-link:hover {
            color: #F57C00;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        h1 {
            color: #F57C00;
            text-align: center;
            margin-bottom: 40px;
        }
        .result {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #1f1f1f;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .result:hover {
            background-color: #444444;
            transform: translateY(-5px);
        }
        .team {
            text-align: center;
            flex: 1;
        }
        .team img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #f57c00;
            margin-bottom: 10px;
        }
        .score {
            font-size: 24px;
            color: #F57C00;
            margin-top: 10px;
        }
        .game-date {
            text-align: center;
            color: #b3b3b3;
            margin-bottom: 20px;
        }
        .fa-arrow-right {
            font-size: 24px;
            color: #F57C00;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container" style="margin-top: 25px;">
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
                            <li><a class="dropdown-item" href="Gameresult.php">Games</a></li>
                            <li><a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a></li>
                            <li><a class="dropdown-item" href="AboutUs.php">The Team</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Back</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Featured Games</h1>
        <?php if (count($games) > 0): ?>
            <?php foreach ($games as $game): ?>
                <div class="game-date"><?php echo htmlspecialchars($game['game_date']); ?></div>
                <a href="boxscore.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>" class="result">
                    <div class="team">
                        <img src="<?php echo htmlspecialchars($teams[$game['team1']]); ?>" alt="<?php echo htmlspecialchars($game['team1']); ?> Logo">
                        <p><?php echo htmlspecialchars($game['team1']); ?></p>
                        <p class="score"><?php echo htmlspecialchars($game['team1_score']); ?></p>
                    </div>
                    <div class="team">
                        <img src="<?php echo htmlspecialchars($teams[$game['team2']]); ?>" alt="<?php echo htmlspecialchars($game['team2']); ?> Logo">
                        <p><?php echo htmlspecialchars($game['team2']); ?></p>
                        <p class="score"><?php echo htmlspecialchars($game['team2_score']); ?></p>
                    </div>
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No game results available.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
