<?php
include 'db-connect.php';

function getOpponentName($conn, $game_id, $team_name) {
    $sql = "SELECT team1, team2 FROM games WHERE game_id = $game_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return ($row['team1'] == $team_name) ? $row['team2'] : $row['team1'];
    }
    return "Unknown Opponent";
}

// Fetch player data and calculate averages
if (isset($_GET['id'])) {
    $player_id = intval($_GET['id']);
    $sql_profile = "SELECT * FROM users WHERE id = $player_id";
    $result_profile = $conn->query($sql_profile);

    if ($result_profile->num_rows > 0) {
        $row_profile = $result_profile->fetch_assoc();
        $first_name = $row_profile['first_name'];
        $last_name = $row_profile['last_name'];
        $team = $row_profile['team'];

        $sql_stats = "SELECT * FROM statistics WHERE first_name = ? AND last_name = ?";
        $stmt = $conn->prepare($sql_stats);
        $stmt->bind_param("ss", $first_name, $last_name);
        $stmt->execute();
        $result_stats = $stmt->get_result();

        $total_points = $total_assists = $total_rebounds = $game_count = 0;

        if ($result_stats->num_rows > 0) {
            while ($row_stats = $result_stats->fetch_assoc()) {
                $total_points += $row_stats["points"];
                $total_assists += $row_stats["assists"];
                $total_rebounds += ($row_stats["reb_def"] + $row_stats["reb_off"]);
                $game_count++;
            }

            $average_points = $game_count > 0 ? $total_points / $game_count : 'N/A';
            $average_assists = $game_count > 0 ? $total_assists / $game_count : 'N/A';
            $average_rebounds = $game_count > 0 ? $total_rebounds / $game_count : 'N/A';
        } else {
            $average_points = $average_assists = $average_rebounds = 'N/A';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #f57c00;
            --secondary-color: #7E69AB;
            --background-dark: #121212;
            --card-dark: #242937;
            --text-primary: #FFFFFF;
            --text-secondary: #A0AEC0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--background-dark) 0%, #2D3748 100%);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .navbar {
            background-color: rgba(26, 31, 44, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.5rem;
        }

        .nav-link {
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .profile-container {
            background: var(--card-dark);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1000px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-picture {
            width: 200px;
            height: 190px;
            border-radius: 50%;
            border: 4px solid var(--primary-color);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .stats-card {
            background: linear-gradient(145deg, var(--card-dark), #2A303F);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(139, 92, 246, 0.2);
        }

        .stat-icon {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .game-card {
            background: var(--card-dark);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
        }

        h1, h2, h3, h4, h5 {
            font-weight: 600;
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        .past-games {
            background: var(--card-dark);
            border-radius: 20px;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 768px) {
            .profile-container {
                margin: 1rem;
                padding: 1rem;
            }

            .profile-picture {
                width: 150px;
                height: 150px;
            }

            .stats-card {
                margin-bottom: 1rem;
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
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="profile.php" class="nav-link">Back</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="profile-container">
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="<?php echo $row_profile['profile_picture'] ?: 'images/default-profile.png'; ?>" class="profile-picture" alt="Profile picture of <?php echo $first_name . ' ' . $last_name; ?>">
                    <h1><?php echo $first_name . ' ' . $last_name; ?></h1>
                    <h4><?php echo $row_profile['position']; ?> | #<?php echo $row_profile['number']; ?></h4>
                    <h5><?php echo $row_profile['team']; ?></h5>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-basketball-ball stat-icon"></i>
                                <h3><?php echo is_numeric($average_points) ? number_format($average_points, 1) : $average_points; ?></h3>
                                <p>PPG</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-hands-helping stat-icon"></i>
                                <h3><?php echo is_numeric($average_assists) ? number_format($average_assists, 1) : $average_assists; ?></h3>
                                <p>APG</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stats-card text-center">
                                <i class="fas fa-chart-line stat-icon"></i>
                                <h3><?php echo is_numeric($average_rebounds) ? number_format($average_rebounds, 1) : $average_rebounds; ?></h3>
                                <p>RPG</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4>Bio</h4>
                        <p><?php echo htmlspecialchars($row_profile['bio']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="past-games">
            <h2 class="text-center mb-4">Past Games</h2>
            <div class="row">
                <?php
                if (isset($result_stats) && $result_stats->num_rows > 0) {
                    $result_stats->data_seek(0); // Reset result set pointer
                    while ($row_stats = $result_stats->fetch_assoc()) {
                        $opponent = getOpponentName($conn, $row_stats["game_id"], $team);
                        $game_id = $row_stats["game_id"];
                        $sql_game_date = "SELECT game_date FROM games WHERE game_id = $game_id";
                        $result_game_date = $conn->query($sql_game_date);
                        $game_date = ($result_game_date->num_rows > 0) ? $result_game_date->fetch_assoc()['game_date'] : 'Unknown Date';
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="game-card">
                                <h5>vs <?php echo $opponent; ?></h5>
                                <p class="text-muted"><?php echo $game_date; ?></p>
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <h4><?php echo $row_stats["points"]; ?></h4>
                                        <p>PTS</p>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h4><?php echo $row_stats["assists"]; ?></h4>
                                        <p>AST</p>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h4><?php echo $row_stats["reb_def"] + $row_stats["reb_off"]; ?></h4>
                                        <p>REB</p>
                                    </div>
                                </div>
                                <a href="/schedule/boxscore.php?game_id=<?php echo $game_id; ?>" class="btn btn-primary btn-sm mt-3 w-100">View Full Stats</a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<div class='col'><div class='alert alert-warning'>No past games found for this player.</div></div>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
