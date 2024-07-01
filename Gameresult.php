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
    <title>Game Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
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
            background-color: #333333;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        .result:hover {
            background-color: #444444;
        }
        .team {
            text-align: center;
            flex: 1;
        }
        .team img {
            width: 50px;
            height: 50px;
        }
        .score {
            font-size: 24px;
            color: #F57C00;
        }
        .game-date {
            text-align: center;
            color: #cccccc;
            margin-bottom: 10px;
        }
        .fa-arrow-right {
            font-size: 24px;
            color: #F57C00;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Game Results</h1>
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
</body>
</html>
