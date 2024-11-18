<?php
// Include database connection
require_once 'db-connect.php';

// Start the session (if needed for user authentication)
session_start();

// Function to fetch all players for suggestions
function fetchAllPlayers($conn) {
    $query = "SELECT DISTINCT CONCAT(first_name, ' ', last_name) as name FROM statistics ORDER BY last_name, first_name";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch player data
function fetchPlayerData($conn, $first_name, $last_name) {
    $query = "SELECT s.game_id, s.first_name, s.last_name, 
              SUM(s.points) as points, SUM(s.assists) as assists, SUM(s.rebounds) as rebounds, 
              SUM(s.steals) as steals, SUM(s.blocks) as blocks, SUM(s.turnovers) as turnovers, 
              SUM(s.2pt_attempted) as 2pt_attempted, SUM(s.2pt_made) as 2pt_made, 
              SUM(s.3pt_attempted) as 3pt_attempted, SUM(s.3pt_made) as 3pt_made, 
              SUM(s.ft_attempted) as ft_attempted, SUM(s.ft_made) as ft_made, 
              SUM(s.reb_off) as reb_off, SUM(s.reb_def) as reb_def, SUM(s.fouls) as fouls,
              g.game_date, g.team1, g.team2, g.team1_score, g.team2_score
              FROM statistics s
              JOIN games g ON s.game_id = g.game_id
              WHERE s.first_name = ? AND s.last_name = ?
              GROUP BY s.game_id, s.first_name, s.last_name 
              ORDER BY g.game_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $first_name, $last_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC) ?: []; // Return empty array if no results
}

function generatePrintableReport($player1Games, $player2Games, $selected_stat) {
    $html = "<h1>Player Statistics Report</h1>";
    
    // Player 1 Report
    if (!empty($player1Games)) {
        $html .= generatePlayerReport($player1Games[0]['first_name'] . ' ' . $player1Games[0]['last_name'], $player1Games, $selected_stat);
    }
    
    // Player 2 Report (if exists)
    if (!empty($player2Games)) {
        $html .= generatePlayerReport($player2Games[0]['first_name'] . ' ' . $player2Games[0]['last_name'], $player2Games, $selected_stat);
    }
    
    return $html;
}

function generatePlayerReport($playerName, $games, $selected_stat) {
    $html = "<h2>$playerName</h2>";
    $html .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>
                <tr>
                    <th>Date</th>
                    <th>Opponent</th>
                    <th>Score</th>
                    <th>" . ucfirst(str_replace('_', ' ', $selected_stat)) . "</th>
                </tr>";
    
    foreach ($games as $game) {
        $opponent = $game['team1'] === $playerName ? $game['team2'] : $game['team1'];
        $score = $game['team1'] === $playerName ? 
            "{$game['team1_score']} - {$game['team2_score']}" : 
            "{$game['team2_score']} - {$game['team1_score']}";
        
        $html .= "<tr>
                    <td>" . date('Y-m-d', strtotime($game['game_date'])) . "</td>
                    <td>$opponent</td>
                    <td>$score</td>
                    <td>{$game[$selected_stat]}</td>
                  </tr>";
    }
    
    $html .= "</table>";
    return $html;
}

// Get all players for suggestions
$all_players = fetchAllPlayers($conn);

// Get selected player names (default to empty strings)
$selected_first_name1 = isset($_GET['first_name1']) ? $_GET['first_name1'] : '';
$selected_last_name1 = isset($_GET['last_name1']) ? $_GET['last_name1'] : '';
$selected_first_name2 = isset($_GET['first_name2']) ? $_GET['first_name2'] : '';
$selected_last_name2 = isset($_GET['last_name2']) ? $_GET['last_name2'] : '';

// Get selected stat (default to points)
$selected_stat = isset($_GET['stat']) ? $_GET['stat'] : 'points';

// Initialize variables
$player1_games = [];
$player2_games = [];

// Fetch data for player 1
if ($selected_first_name1 && $selected_last_name1) {
    $player1_games = fetchPlayerData($conn, $selected_first_name1, $selected_last_name1);
}

// Fetch data for player 2
if ($selected_first_name2 && $selected_last_name2) {
    $player2_games = fetchPlayerData($conn, $selected_first_name2, $selected_last_name2);
}

// Available stats
$available_stats = ['points', 'assists', 'rebounds', 'steals', 'blocks', 'turnovers', '2pt_attempted', '2pt_made', '3pt_attempted', '3pt_made', 'ft_attempted', 'ft_made', 'reb_off', 'reb_def', 'fouls'];

// Handle print request
if (isset($_GET['print'])) {
    $printableReport = generatePrintableReport($player1_games, $player2_games, $selected_stat);
    
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Player Statistics Report</title>
        <style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            h1, h2 { color: #333; }
            @media print {
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        $printableReport
        <div class='no-print'>
            <button onclick='window.print()'>Print this report</button>
            <button onclick='window.close()'>Close</button>
        </div>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>";
    exit;
}

// Encode player games data as JSON for JavaScript use
$player1_games_json = json_encode($player1_games);
$player2_games_json = json_encode($player2_games);
$all_players_json = json_encode($all_players);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Statistics - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1a1a1a;
            color: #ffffff;
        }
        .sidebar {
            background-color: #1e1e1e;
            transition: all 0.3s;
        }
        .sidebar-item {
            transition: all 0.3s;
        }
        .sidebar-item:hover, .sidebar-item.active {
            background-color: #ff6600;
            color: #1a1a1a;
        }
        .content {
            background-color: #1a1a1a;
        }
        .btn-primary {
            background-color: #ff6600;
            color: #1a1a1a;
        }
        .btn-primary:hover {
            background-color: #ff8533;
        }
        .search-container {
            position: relative;
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: #2d2d2d;
            border: 1px solid #3d3d3d;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
        }
        .search-result-item {
            padding: 10px;
            cursor: pointer;
        }
        .search-result-item:hover {
            background-color: #3d3d3d;
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
            <h1 class="text-3xl font-bold text-center mb-8 text-orange-500">Player Statistics Reports</h1>
            
            <form id="playerForm" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-2">
                        <div class="search-container">
                            <input type="text" id="player1Input" placeholder="Player 1 Name" class="w-full bg-gray-700 text-white border border-gray-600 rounded-md py-2 px-4 focus:outline-none focus:border-orange-500" value="<?php echo htmlspecialchars($selected_first_name1 . ' ' . $selected_last_name1); ?>">
                            <div id="player1Results" class="search-results hidden"></div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <div class="search-container">
                            <input type="text" id="player2Input" placeholder="Player 2 Name (Optional)" class="w-full bg-gray-700 text-white border border-gray-600 rounded-md py-2 px-4 focus:outline-none focus:border-orange-500" value="<?php echo htmlspecialchars($selected_first_name2 . ' ' . $selected_last_name2); ?>">
                            <div id="player2Results" class="search-results hidden"></div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex space-x-4">
            <button type="button" onclick="comparePlayersAndUpdateChart()" class="flex-1 bg-orange-500 text-white py-2 px-4 rounded-md hover:bg-orange-600 transition duration-300">
                Compare Players
            </button>
            <button onclick="printReport()" class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition duration-300">
                Print Report
            </button>
            </div>
            </form>

            <select id="statSelect" onchange="updateChart()" class="w-full bg-gray-700 text-white border border-gray-600 rounded-md py-2 px-4 mb-6 focus:outline-none focus:border-orange-500">
                <?php foreach ($available_stats as $stat): ?>
                    <option value="<?php echo $stat; ?>" <?php echo ($stat == $selected_stat) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(str_replace('_', ' ', $stat)); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                <canvas id="statsChart"></canvas>
            </div>
        </div>
    </div>

                    
    <script>
        var player1Games = <?php echo $player1_games_json; ?>;
        var player2Games = <?php echo $player2_games_json; ?>;
        var allPlayers = <?php echo $all_players_json; ?>;
        var chart;

        function searchPlayers(inputId, resultsId) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            const searchTerm = input.value.toLowerCase();

            results.innerHTML = '';
            results.classList.remove('hidden');

            const filteredPlayers = allPlayers.filter(player => 
                player.name.toLowerCase().includes(searchTerm)
            );

            filteredPlayers.forEach(player => {
                const div = document.createElement('div');
                div.textContent = player.name;
                div.className = 'search-result-item';
                div.onclick = function() {
                    input.value = player.name;
                    results.classList.add('hidden');
                };
                results.appendChild(div);
            });

            if (filteredPlayers.length === 0) {
                results.classList.add('hidden');
            }
        }

        document.getElementById('player1Input').addEventListener('input', () => searchPlayers('player1Input', 'player1Results'));
        document.getElementById('player2Input').addEventListener('input', () => searchPlayers('player2Input', 'player2Results'));

        document.addEventListener('click', function(e) {
            const player1Results = document.getElementById('player1Results');
            const player2Results = document.getElementById('player2Results');
            if (!e.target.closest('#player1Input') && !e.target.closest('#player1Results')) {
                player1Results.classList.add('hidden');
            }
            if (!e.target.closest('#player2Input') && !e.target.closest('#player2Results')) {
                player2Results.classList.add('hidden');
            }
        });

        function comparePlayersAndUpdateChart() {
            const player1Name = document.getElementById('player1Input').value.trim();
            const player2Name = document.getElementById('player2Input').value.trim();
            const selectedStat = document.getElementById('statSelect').value;

            let url = 'stat-report.php?stat=' + encodeURIComponent(selectedStat);

            if (player1Name) {
                const [firstName1, lastName1] = player1Name.split(' ');
                url += '&first_name1=' + encodeURIComponent(firstName1) + '&last_name1=' + encodeURIComponent(lastName1);
            }

            if (player2Name) {
                const [firstName2, lastName2] = player2Name.split(' ');
                url += '&first_name2=' + encodeURIComponent(firstName2) + '&last_name2=' + encodeURIComponent(lastName2);
            }

            window.location.href = url;
        }

        function updateChart() {
            var selectedStat = document.getElementById('statSelect').value;
            var ctx = document.getElementById('statsChart').getContext('2d');

            if (chart) {
                chart.destroy();
            }

            var labels = player1Games.map(game => new Date(game.game_date).toLocaleDateString());
            var data1 = player1Games.map(game => parseFloat(game[selectedStat]));
            var datasets = [{
                label: '<?php echo $selected_first_name1 . ' ' . $selected_last_name1; ?>',
                data: data1,
                backgroundColor: 'rgba(255, 102, 0, 0.8)',
                borderColor: 'rgba(255, 102, 0, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(255, 102, 0, 1)',
                pointRadius: 4,
                pointHoverRadius: 6
            }];

            if (player2Games.length > 0) {
                var data2 = player2Games.map(game => parseFloat(game[selectedStat]));
                datasets.push({
                    label: '<?php echo $selected_first_name2 . ' ' . $selected_last_name2; ?>',
                    data: data2,
                    backgroundColor: 'rgba(0, 123, 255, 0.8)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                });
            }

            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
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
                            text: selectedStat.charAt(0).toUpperCase() + selectedStat.slice(1).replace('_', ' ') + ' Over Games',
                            color: '#ffffff',
                            font: {
                                size: 18
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var playerGames = context.datasetIndex === 0 ? player1Games : player2Games;
                                    var game = playerGames[context.dataIndex];
                                    var opponent = game.team1 === context.dataset.label ? game.team2 : game.team1;
                                    var score = game.team1 === context.dataset.label ? 
                                        `${game.team1_score} - ${game.team2_score}` : 
                                        `${game.team2_score} - ${game.team1_score}`;
                                    return [
                                        `${context.dataset.label}`,
                                        `${selectedStat.charAt(0).toUpperCase() + selectedStat.slice(1).replace('_', ' ')}: ${context.parsed.y}`,
                                        `Opponent: ${opponent}`,
                                        `Score: ${score}`,
                                        `Date: ${new Date(game.game_date).toLocaleDateString()}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });
        }

        function printReport() {
            const selectedStat = document.getElementById('statSelect').value;
            const player1Name = document.getElementById('player1Input').value.trim();
            const player2Name = document.getElementById('player2Input').value.trim();

            let url = 'stat-report.php?print=1&stat=' + encodeURIComponent(selectedStat);

            if (player1Name) {
                const [firstName1, lastName1] = player1Name.split(' ');
                url += '&first_name1=' + encodeURIComponent(firstName1) + '&last_name1=' + encodeURIComponent(lastName1);
            }

            if (player2Name) {
                const [firstName2, lastName2] = player2Name.split(' ');
                url += '&first_name2=' + encodeURIComponent(firstName2) + '&last_name2=' + encodeURIComponent(lastName2);
            }

            const printWindow = window.open(url, '_blank');
            
            if (printWindow) {
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }
        }

        // Initial chart creation
        updateChart();

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
