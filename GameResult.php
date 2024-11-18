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
    <title>Games - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #f57c00;
            --bg-dark: #121212;
            --bg-card: #1e1e1e;
            --bg-hover: #2a2a2a;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
        }
        body {
            background-color: var(--bg-dark);
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
            margin-bottom: 50px;
            line-height: 1.6;
        }
        .navbar {
            background: linear-gradient(to right, #1a1a1a, #2d2d2d);
            border-bottom: 3px solid var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 600;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }
        .navbar-brand:hover {
            transform: scale(1.05);
        }
        .nav-link {
            color: var(--text-primary) !important;
            transition: color 0.3s ease;
            font-weight: 500;
        }
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        .dropdown-menu {
            background-color: var(--bg-card);
            border: 1px solid var(--bg-hover);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .dropdown-item {
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        .dropdown-item:hover {
            background-color: var(--bg-hover);
            color: var(--primary-color);
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 2rem 15px;
        }
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 3rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 1rem;
        }
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--primary-color);
        }
        .result {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(145deg, var(--bg-card), var(--bg-hover));
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .result:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.2);
        }
        .team {
            text-align: center;
            flex: 1;
            padding: 1rem;
            transition: transform 0.3s ease;
        }
        .team:hover {
            transform: scale(1.05);
        }
        .team img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid var(--primary-color);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .team img:hover {
            border-color: var(--text-primary);
            transform: scale(1.1);
        }
        .score {
            font-size: 2rem;
            color: var(--primary-color);
            margin-top: 0.5rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .game-date {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .fa-arrow-right {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin: 0 2rem;
            transition: transform 0.3s ease;
        }
        .result:hover .fa-arrow-right {
            transform: translateX(10px);
        }
        @media (max-width: 768px) {
            .result {
                flex-direction: column;
                padding: 1rem;
            }
            
            .team {
                margin: 1rem 0;
            }
            
            .fa-arrow-right {
                transform: rotate(90deg);
                margin: 1rem 0;
            }
            
            .result:hover .fa-arrow-right {
                transform: rotate(90deg) translateX(10px);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                <div class="game-date">
                    <i class="far fa-calendar-alt me-2"></i>
                    <?php echo htmlspecialchars(date('F j, Y', strtotime($game['game_date']))); ?>
                </div>
                <a href="boxscore.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>" class="result">
                    <div class="team">
                        <img src="<?php echo htmlspecialchars($teams[$game['team1']]); ?>" alt="<?php echo htmlspecialchars($game['team1']); ?> Logo">
                        <p class="h5 mb-2"><?php echo htmlspecialchars($game['team1']); ?></p>
                        <p class="score"><?php echo htmlspecialchars($game['team1_score']); ?></p>
                    </div>
                    <i class="fas fa-arrow-right"></i>
                    <div class="team">
                        <img src="<?php echo htmlspecialchars($teams[$game['team2']]); ?>" alt="<?php echo htmlspecialchars($game['team2']); ?> Logo">
                        <p class="h5 mb-2"><?php echo htmlspecialchars($game['team2']); ?></p>
                        <p class="score"><?php echo htmlspecialchars($game['team2_score']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No game results available at the moment.
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
