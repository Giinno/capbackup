<?php
// Include database connection
require_once 'db-connect.php';

// Start the session (if needed for user authentication)
session_start();

// Fetch all teams
$teams_query = "SELECT id, team_name FROM teams ORDER BY team_name";
$teams_result = $conn->query($teams_query);
$teams = $teams_result->fetch_all(MYSQLI_ASSOC);

// Get selected team (default to first team)
$selected_team_id = isset($_GET['team_id']) ? $_GET['team_id'] : $teams[0]['id'];

// Function to fetch team data
function fetchTeamData($conn, $team_id) {
    $query = "SELECT g.game_id, g.team1, g.team2, g.team1_score, g.team2_score, g.game_date,
              CASE 
                WHEN g.team1 = t.team_name THEN g.team1_score
                ELSE g.team2_score
              END as team_score,
              CASE 
                WHEN g.team1 = t.team_name THEN g.team2_score
                ELSE g.team1_score
              END as opponent_score,
              CASE 
                WHEN g.team1 = t.team_name THEN g.team2
                ELSE g.team1
              END as opponent
              FROM games g
              JOIN teams t ON t.id = ?
              WHERE g.team1 = t.team_name OR g.team2 = t.team_name
              ORDER BY g.game_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch data for the selected team
$team_games = fetchTeamData($conn, $selected_team_id);

// Fetch team logo
function fetchTeamLogo($conn, $team_id) {
    $logo_query = "SELECT team_logo FROM teams WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($logo_query);
    $stmt->bind_param("i", $team_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['team_logo'] : null;
}

$team_logo = fetchTeamLogo($conn, $selected_team_id);

// Close the database connection
$conn->close();

// Encode team games data as JSON for JavaScript use
$team_games_json = json_encode($team_games);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Statistics - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #ff6600;
            --bg-dark: #1a1a1a;
            --card-bg: #2a2a2a;
            --sidebar-bg: #222222;
            --text-primary: #e0e0e0;
            --text-secondary: #9ca3af;
            --hover-bg: rgba(255, 102, 0, 0.1);
            }

            body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            }

            /* Sidebar Styles */
            .sidebar {
            background-color: var(--sidebar-bg);
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            }

            .sidebar-item {
            @apply transition-all duration-200;
            }

            .sidebar-item:hover {
            background-color: var(--hover-bg);
            color: var(--primary);
            transform: translateX(5px);
            }

            /* Card Styles */
            .stat-card {
            background-color: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            @apply transition-all duration-300 ease-in-out;
            }

            .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-color: var(--primary);
            }

            .stat-card h2 {
            @apply animate-fade-in;
            }

            /* Table Styles */
            table {
            border-collapse: separate;
            border-spacing: 0 8px;
            width: 100%;
            }

            table th {
            background-color: var(--card-bg);
            color: var(--primary);
            font-weight: 600;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid var(--primary);
            }

            table td {
            background-color: var(--card-bg);
            padding: 1rem;
            color: var(--text-primary);
            transition: all 0.2s ease;
            }

            tr {
            @apply transition-all duration-200;
            }

            tr:hover td {
            background-color: var(--hover-bg);
            }

            /* Animations */
            @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
            }

            .animate-fade-in {
            animation: fade-in 0.5s ease-out;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: auto;
                z-index: 50;
            }

            main {
                margin-bottom: 4rem;
            }

            .stat-card {
                margin-bottom: 1rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            }

            /* Custom Scrollbar */
            ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
            }

            ::-webkit-scrollbar-track {
            background: var(--card-bg);
            }

            ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
            background: #ff8533;
            }
    </style>
</head>
<body class="flex min-h-screen bg-gray-900">
    <!-- Sidebar -->
    <div class="sidebar w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
        <div class="flex items-center justify-center mb-8">
            <img src="./images/Logo.png" alt="Ballers Hub Logo" class="w-12 h-12 mr-2">
            <h1 class="text-2xl font-semibold text-orange-500">Ballers Hub</h1>
        </div>
        <nav>
            <a href="stats-admin-dashboard.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-user-cog text-xl"></i>
                <span>Dashboard</span>
            </a>
            <a href="player_analytics.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-user-astronaut text-xl"></i>
                <span>Player Analytics</span>
            </a>
            <a href="profile-cms.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-user-cog text-xl"></i>
                <span>Profile Settings</span>
            </a>
            <a href="gamresult.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-trophy text-xl"></i>
                <span>Game Results</span>
            </a>
            <a href="CreateTeam.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-users text-xl"></i>
                <span>Create Team</span>
            </a>
            <a href="viewteams.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-eye text-xl"></i>
                <span>View Teams</span>
            </a>
            <a href="update_player_team.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-exchange-alt text-xl"></i>
                <span>Update Player Team</span>
            </a>
            <a href="stat-report.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-chart-bar text-xl"></i>
                <span>Player Stats Report</span>
            </a>
            <a href="team-stat-report.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-chart-line text-xl"></i>
                <span>Team Stats Report</span>
            </a>
        </nav>
        <button onclick="logout()" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg mt-auto w-full">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span>Logout</span>
        </button>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-10 overflow-hidden" style="background-color: #1a1a1a;">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-center mb-8 text-orange-500">Team Statistics Report</h1>
            
            <form id="teamForm" class="mb-6">
                <select name="team" id="teamSelect" class="w-full bg-gray-700 text-white border border-gray-600 rounded-md py-2 px-4 focus:outline-none focus:border-orange-500">
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo htmlspecialchars($team['id']); ?>" 
                        <?php echo ($team['id'] == $selected_team_id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($team['team_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if (!empty($team_games)): ?>
                <h2 class="text-2xl font-semibold mb-4 text-center text-white">
                    <?php echo htmlspecialchars($team_games[0]['team1'] == $teams[0]['team_name'] ? $team_games[0]['team1'] : $team_games[0]['team2']); ?>
                </h2>

                <!-- Team Logo Section -->
                <div class="flex items-center justify-center mb-6">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-orange-500">
                        <img src="<?php echo $team_logo ? htmlspecialchars($team_logo) : 'images/default-team-logo.png'; ?>"
                             alt="Team Logo"
                             class="w-full h-full object-cover">
                    </div>
                </div>

                <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-6">
                    <canvas id="scoreChart"></canvas>
                </div>

                <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                    <canvas id="winLossChart"></canvas>
                </div>
            <?php else: ?>
                <p class="text-center text-white">No data available for this team.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        var teamGames = <?php echo $team_games_json; ?>;
        var scoreChart, winLossChart;

        function updateCharts() {
            var ctx1 = document.getElementById('scoreChart').getContext('2d');
            var ctx2 = document.getElementById('winLossChart').getContext('2d');

            if (scoreChart) {
                scoreChart.destroy();
            }
            if (winLossChart) {
                winLossChart.destroy();
            }

            var labels = teamGames.map(game => new Date(game.game_date).toLocaleDateString());
            var teamScores = teamGames.map(game => parseInt(game.team_score));
            var opponentScores = teamGames.map(game => parseInt(game.opponent_score));

            scoreChart = new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Team Score',
                        data: teamScores,
                        backgroundColor: 'rgba(255, 102, 0, 0.8)',
                        borderColor: 'rgba(255, 102, 0, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(255, 102, 0, 1)',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }, {
                        label: 'Opponent Score',
                        data: opponentScores,
                        backgroundColor: 'rgba(0, 123, 255, 0.8)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#ffffff'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            },
                            ticks: {
                                color: '#ffffff',
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ffffff'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Team vs Opponent Scores',
                            color: '#ffffff',
                            font: {
                                size: 18
                            }
                        }
                    }
                }
            });

            var wins = teamGames.filter(game => parseInt(game.team_score) > parseInt(game.opponent_score)).length;
            var losses = teamGames.length - wins;

            winLossChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: ['Wins', 'Losses'],
                    datasets: [{
                        data: [wins, losses],
                        backgroundColor: ['rgba(255, 102, 0, 0.8)', 'rgba(0, 123, 255, 0.8)'],
                        borderColor: ['rgba(255, 102, 0, 1)', 'rgba(0, 123, 255, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ffffff'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Win/Loss Ratio',
                            color: '#ffffff',
                            font: {
                                size: 18
                            }
                        }
                    }
                }
            });
        }

        // Initial chart creation
        updateCharts();

        // Update team selection
        document.getElementById('teamSelect').addEventListener('change', function() {
            var teamId = this.value;
            window.location.href = 'team-stat-report.php?team_id=' + encodeURIComponent(teamId);
        });

        function logout() {
        fetch('logout.php')
            .then(response => {
                if (response.ok) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Logout failed. Please try again.');
                }
            })
            .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html>
