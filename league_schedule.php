<?php
session_start();
include 'db-connect.php';

function fetchData($conn, $query, $params = []) {
    try {
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function calculateMVPScore($stats) {
    $fgPercentage = $stats['2pt_attempted'] > 0 ? ($stats['2pt_made'] / $stats['2pt_attempted']) : 0;
    $tpPercentage = $stats['3pt_attempted'] > 0 ? ($stats['3pt_made'] / $stats['3pt_attempted']) : 0;
    $ftPercentage = $stats['ft_attempted'] > 0 ? ($stats['ft_made'] / $stats['ft_attempted']) : 0;

    return ($stats['points'] * 1.0) +
           (($stats['reb_off'] + $stats['reb_def']) * 1.2) +
           ($stats['assists'] * 1.5) +
           ($stats['steals'] * 2.0) +
           ($stats['blocks'] * 2.0) -
           ($stats['turnovers'] * 1.0) +
           ($fgPercentage * 100 * 0.5) +
           ($tpPercentage * 100 * 0.5) +
           ($ftPercentage * 100 * 0.3);
}

// Sanitize and validate the league parameter
$league_name = isset($_GET['league']) ? trim($_GET['league']) : '';
if (empty($league_name)) {
    header("Location: dashboard.php");
    exit();
}

// Fetch league games schedule with team names and game information
$scheduleQuery = "SELECT lgs.*, t1.team_name AS team1_name, t2.team_name AS team2_name,
                  g.team1_score, g.team2_score
                  FROM league_games_schedule lgs
                  LEFT JOIN teams t1 ON lgs.team1 = t1.id
                  LEFT JOIN teams t2 ON lgs.team2 = t2.id
                  LEFT JOIN games g ON (lgs.team1 = g.team1 AND lgs.team2 = g.team2 
                    AND lgs.game_date = g.game_date AND lgs.league_name = g.league_name)
                  WHERE lgs.league_name = ?
                  ORDER BY lgs.game_date ASC, lgs.game_time ASC";

$schedules = fetchData($conn, $scheduleQuery, [$league_name]);

// Process schedules and group by title
$groupedSchedules = [];
foreach ($schedules as $schedule) {
    $time = DateTime::createFromFormat('H:i:s', $schedule['game_time']);
    if ($time) {
        $schedule['game_time'] = $time->format('g:i A');
    }
    
    $schedule['game_id'] = isset($schedule['game_id']) ? intval($schedule['game_id']) : null;
    
    if (!isset($groupedSchedules[$schedule['title']])) {
        $groupedSchedules[$schedule['title']] = $schedule;
    }
}

// Fetch player statistics for the league
$statsQuery = "SELECT s.*, CONCAT(s.first_name, ' ', s.last_name) AS player_name,
               SUM(s.points) AS total_points,
               SUM(s.reb_off + s.reb_def) AS total_rebounds,
               SUM(s.assists) AS total_assists,
               COUNT(DISTINCT s.game_id) AS games_played
               FROM statistics s
               WHERE s.league_name = ?
               GROUP BY s.first_name, s.last_name";

$playerStats = fetchData($conn, $statsQuery, [$league_name]);

// Calculate MVP scores
$mvpScores = [];
foreach ($playerStats as &$stats) {
    $mvpScores[$stats['player_name']] = calculateMVPScore($stats);
    
    // Calculate averages
    $stats['avg_points'] = $stats['games_played'] > 0 ? $stats['total_points'] / $stats['games_played'] : 0;
    $stats['avg_rebounds'] = $stats['games_played'] > 0 ? $stats['total_rebounds'] / $stats['games_played'] : 0;
    $stats['avg_assists'] = $stats['games_played'] > 0 ? $stats['total_assists'] / $stats['games_played'] : 0;
}

// Sort MVP scores in descending order
arsort($mvpScores);

// Get the top MVP player
$topMVP = array_slice($mvpScores, 0, 1, true);
$topMVPName = key($topMVP);
$topMVPScore = reset($topMVP);
$topMVPStats = array_filter($playerStats, function($stat) use ($topMVPName) {
    return $stat['player_name'] === $topMVPName;
});
$topMVPStats = reset($topMVPStats);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($league_name); ?> Schedule and MVP Rankings - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <style>
        .schedule-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            background: #ffffff;
            cursor: pointer;
        }
        
        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .card-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .team-vs {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0;
            padding: 1rem;
            background: linear-gradient(145deg, #2c3e50, #34495e);
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .team-name {
            font-weight: 700;
            color: #ffffff;
            font-size: 1.2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .vs-badge {
            background-color: #e74c3c;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .game-info {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.1rem;
            color: #2c3e50;
        }
        
        .info-item i {
            color: #3498db;
            font-size: 1.2rem;
        }
        
        .score-display {
            font-size: 2rem;
            font-weight: 800;
            color: #2c3e50;
            text-align: center;
            margin: 1.5rem 0;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .league-title {
            color: #f57c00;
            font-size: 3rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 4px solid #3498db;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* MVP Card Styles */
        .mvp-card {
            display: none;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            padding: 2rem;
            margin: 30px auto;
            max-width: 800px;
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.5s ease;
        }
        
        .mvp-card.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .mvp-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding: 1.5rem;
            background: linear-gradient(145deg, #2c3e50, #34495e);
            border-radius: 15px;
            color: white;
        }
        
        .mvp-profile {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .mvp-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-item {
            text-align: center;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2c3e50;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .stat-average {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(145deg, #3498db, #2980b9);
            border: none;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        
        @media (max-width: 768px) {
            .team-vs {
                flex-direction: column;
                gap: 15px;
                padding: 1.5rem;
            }
            
            .mvp-header {
                flex-direction: column;
                text-align: center;
                padding: 2rem;
            }
            
            .mvp-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .league-title {
                font-size: 2.2rem;
            }
            
            .card-title {
                font-size: 1.3rem;
            }
            
            .team-name {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">Ballers Hub</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">Home</a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">Logout</a>
                        </li>
                        <li class="nav-item">
                            <a href="user-profile.php" class="nav-link" style="font-weight: bolder;"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">Login</a>
                        </li>
                        <li class="nav-item">
                            <a href="Registration.php" class="nav-link">Sign up</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container mt-4">
        <h1 class="league-title"><?php echo htmlspecialchars($league_name); ?> Schedule</h1>
        
        <?php if (empty($groupedSchedules)): ?>
            <div class="alert alert-info" role="alert">
                No schedules found for this league.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($groupedSchedules as $schedule): ?>
                    <div class="col-md-6">
                        <div class="card schedule-card" onclick="window.location.href='boxscore.php?game_id=<?php echo $schedule['game_id']; ?>'">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($schedule['title']); ?></h5>
                                
                                <div class="team-vs">
                                    <span class="team-name"><?php echo htmlspecialchars($schedule['team1_name']); ?></span>
                                    <span class="vs-badge">VS</span>
                                    <span class="team-name"><?php echo htmlspecialchars($schedule['team2_name']); ?></span>
                                </div>

                                <?php if (isset($schedule['team1_score']) && isset($schedule['team2_score'])): ?>
                                    <div class="score-display">
                                        <?php echo htmlspecialchars($schedule['team1_score']); ?> - <?php echo htmlspecialchars($schedule['team2_score']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="game-info">
                                    <div class="info-item">
                                        <i class="far fa-calendar"></i>
                                        <?php echo htmlspecialchars($schedule['game_date']); ?>
                                    </div>
                                    <div class="info-item">
                                        <i class="far fa-clock"></i>
                                        <?php echo htmlspecialchars($schedule['game_time']); ?>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($schedule['venue']); ?>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="badge <?php echo $schedule['status'] == 'Completed' ? 'bg-success' : ($schedule['status'] == 'Upcoming' ? 'bg-primary' : 'bg-warning'); ?>">
                                            <?php echo htmlspecialchars($schedule['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mt-5">
        <button class="btn btn-primary" onclick="toggleMVP()">Show League MVP</button>
        
        <div id="mvpCard" class="mvp-card">
            <div class="mvp-header">
                <img src="<?php echo htmlspecialchars($topMVPStats['profile_picture']); ?>" alt="<?php echo htmlspecialchars($topMVPName); ?>" class="mvp-profile">
                <div>
                    <h2><?php echo htmlspecialchars($topMVPName); ?></h2>
                    <p>Jersey Number: <?php echo htmlspecialchars($topMVPStats['number']); ?></p>
                    <p>Games Played: <?php echo htmlspecialchars($topMVPStats['games_played']); ?></p>
                </div>
            </div>
            <div class="mvp-stats-grid">
                <div class="stat-item">
                    <div class="stat-label">Points</div>
                    <div class="stat-value"><?php echo number_format($topMVPStats['total_points']); ?></div>
                    <div class="stat-average">Avg: <?php echo number_format($topMVPStats['avg_points'], 1); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Rebounds</div>
                    <div class="stat-value"><?php echo number_format($topMVPStats['total_rebounds'],); ?></div>
                    <div class="stat-average">Avg: <?php echo number_format($topMVPStats['avg_rebounds'], 1); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Assists</div>
                    <div class="stat-value"><?php echo number_format($topMVPStats['total_assists'],); ?></div>
                    <div class="stat-average">Avg: <?php echo number_format($topMVPStats['avg_assists'], 1); ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function toggleMVP() {
        const mvpCard = document.getElementById('mvpCard');
        const mvpButton = document.querySelector('.btn-primary');
        
        if (mvpCard.style.display === 'none' || mvpCard.style.display === '') {
            mvpCard.style.display = 'block';
            setTimeout(() => {
                mvpCard.classList.add('show');
            }, 50);
            mvpButton.textContent = 'Hide League MVP';
        } else {
            mvpCard.classList.remove('show');
            setTimeout(() => {
                mvpCard.style.display = 'none';
            }, 500);
            mvpButton.textContent = 'Show League MVP';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const scheduleCards = document.querySelectorAll('.schedule-card');
        scheduleCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 150);
        });
    });
    </script>
</body>
</html>
