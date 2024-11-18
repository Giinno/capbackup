<?php
// Include the database connection file
require_once 'db-connect.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Initialize $leagues and $teams as empty arrays
$leagues = array();
$teams = array();
// Fetch leagues from the database
$leagueQuery = "SELECT DISTINCT league_name FROM teams ORDER BY league_name";
$leagueResult = $conn->query($leagueQuery);
if ($leagueResult->num_rows > 0) {
    while($row = $leagueResult->fetch_assoc()) {
        $leagues[] = $row['league_name'];
    }
}
// Fetch teams from the database
$teamQuery = "SELECT team_name, league_name FROM teams ORDER BY league_name, team_name";
$teamResult = $conn->query($teamQuery);
if ($teamResult->num_rows > 0) {
    while($row = $teamResult->fetch_assoc()) {
        $teams[] = $row;
    }
}
$successMessage = '';
$errors = [];
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gameData = [];
    $selectedLeague = $_POST['league'] ?? '';
    if (empty($selectedLeague)) {
        $errors[] = "League is not selected.";
    } else {
        $gameData['league_name'] = $selectedLeague;
    }
    // Validate and process the submitted data
    for ($i = 1; $i <= 2; $i++) {
        if (!isset($_POST['teams'][$i]['team_name']) || empty($_POST['teams'][$i]['team_name'])) {
            $errors[] = "Team $i is not selected.";
        } else {
            $gameData["team{$i}_name"] = $_POST['teams'][$i]['team_name'];
        }
        if (!isset($_POST['teams'][$i]['total_score']) || !is_numeric($_POST['teams'][$i]['total_score'])) {
            $errors[] = "Invalid total score for Team $i.";
        } else {
            $gameData["team{$i}_score"] = intval($_POST['teams'][$i]['total_score']);
        }
        if (isset($_POST['teams'][$i]['players'])) {
            $totalPoints = 0;
            foreach ($_POST['teams'][$i]['players'] as $playerIndex => $playerData) {
                // Validate player data
                if (empty($playerData['first_name']) || empty($playerData['last_name']) || !is_numeric($playerData['number'])) {
                    $errors[] = "Invalid player data for Team $i, player " . ($playerIndex + 1);
                    continue;
                }
                // Validate shot attempts and makes
                foreach (['2pt', '3pt', 'ft'] as $shotType) {
                    $attempted = intval($playerData["{$shotType}_attempted"]);
                    $made = intval($playerData["{$shotType}_made"]);
                    if ($made > $attempted) {
                        $errors[] = "Made shots cannot be greater than attempted shots for {$shotType} (Team $i, {$playerData['first_name']} {$playerData['last_name']})";
                    }
                }
                // Calculate and validate player points
                $playerPoints = (intval($playerData['2pt_made']) * 2) + 
                                (intval($playerData['3pt_made']) * 3) + 
                                intval($playerData['ft_made']);
                if ($playerPoints != intval($playerData['points'])) {
                    $errors[] = "Calculated points do not match entered points for {$playerData['first_name']} {$playerData['last_name']} (Team $i)";
                }
                $totalPoints += $playerPoints;
            }
            // Validate team total score
            if ($totalPoints != $gameData["team{$i}_score"]) {
                $errors[] = "Total score does not match the sum of player points for Team $i";
            }
        }
    }
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
    
        try {
            // Insert game data into the games table
            $stmt = $conn->prepare("INSERT INTO games (Team1, Team2, team1_score, team2_score, game_date, league_name) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt->bind_param("ssiis", $gameData['team1_name'], $gameData['team2_name'], $gameData['team1_score'], $gameData['team2_score'], $gameData['league_name']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error inserting game data: " . $stmt->error);
            }
            
            $gameId = $stmt->insert_id;
            $stmt->close();
    
            // Update league_games_schedule table
            $updateScheduleStmt = $conn->prepare("UPDATE league_games_schedule 
                                                  SET team1_score = ?, team2_score = ?, status = 'Completed'
                                                  WHERE league_name = ? AND team1 = ? AND team2 = ? AND game_date = CURDATE()");
            $updateScheduleStmt->bind_param("iisss", $gameData['team1_score'], $gameData['team2_score'], $gameData['league_name'], $gameData['team1_name'], $gameData['team2_name']);
            
            if (!$updateScheduleStmt->execute()) {
                throw new Exception("Error updating league_games_schedule: " . $updateScheduleStmt->error);
            }
            
            $updateScheduleStmt->close();
    
            // Insert player stats
            $playerStmt = $conn->prepare("INSERT INTO statistics (first_name, last_name, game_id, points, assists, rebounds, steals, turnovers, number, `2pt_attempted`, `2pt_made`, `3pt_attempted`, `3pt_made`, `ft_attempted`, `ft_made`, reb_off, reb_def, blocks, fouls) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$playerStmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            foreach ($_POST['teams'] as $teamData) {
                foreach ($teamData['players'] as $playerData) {
                    $rebounds = intval($playerData['reb_off']) + intval($playerData['reb_def']);
                    
                    $playerStmt->bind_param(
                        "ssiiiiiiiiiiiiiiiii", 
                        $playerData['first_name'],
                        $playerData['last_name'],
                        $gameId,
                        $playerData['points'],
                        $playerData['assists'],
                        $rebounds,
                        $playerData['steals'],
                        $playerData['turnovers'],
                        $playerData['number'],
                        $playerData['2pt_attempted'],
                        $playerData['2pt_made'],
                        $playerData['3pt_attempted'],
                        $playerData['3pt_made'],
                        $playerData['ft_attempted'],
                        $playerData['ft_made'],
                        $playerData['reb_off'],
                        $playerData['reb_def'],
                        $playerData['blocks'],
                        $playerData['fouls']
                    );
    
                    if (!$playerStmt->execute()) {
                        throw new Exception("Error inserting player data: " . $playerStmt->error);
                    }
                }
            }
    
            $playerStmt->close();
    
            // If we've made it this far, commit the transaction
            $conn->commit();
            $successMessage = "Game stats submitted successfully!";
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basketball Game Stats Sheet</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
        .sidebar-item:hover {
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
        .btn-success {
            background-color: #4CAF50;
        }
        .btn-danger {
            background-color: #f44336;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            background-color: #2c2c2c;
        }
        th {
            background-color: #ff6600;
            color: #1a1a1a;
        }
        .form-control {
            background-color: #2c2c2c;
            color: #ffffff;
            border: 1px solid #444444;
            border-radius: 4px;
            padding: 8px;
        }
        .form-control:focus {
            border-color: #ff6600;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 102, 0, 0.2);
        }
        .modal {
            transition: opacity 0.25s ease;
        }
        body.modal-active {
            overflow-x: hidden;
            overflow-y: visible !important;
        }
        p {
            color: black;
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
        <div class="max-w-6xl mx-auto" style="background-color: #1a1a1a;">
            <h2 class="text-3xl font-bold text-center mb-8 text-orange-500">Basketball Game Stats Sheet</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="space-y-8" id="gameStatsForm">
                <div class="mb-4">
                    <label for="leagueSelect" class="block mb-2 text-sm font-medium">Select League:</label>
                    <select id="leagueSelect" name="league" class="form-control w-full" required>
                        <option value="">Select a league</option>
                        <?php foreach ($leagues as $league_name): ?>
                            <option value="<?php echo htmlspecialchars($league_name); ?>"><?php echo htmlspecialchars($league_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php for ($i = 1; $i <= 2; $i++): ?>
                    <div class="rounded-lg p-6 mb-8 shadow-lg">
                        <h3 class="text-xl font-semibold mb-4 text-orange-500">Team <?php echo $i; ?></h3>
                        <div class="mb-4">
                            <label for="teamSelect<?php echo $i; ?>" class="block mb-2 text-sm font-medium">Select Team <?php echo $i; ?>:</label>
                            <select id="teamSelect<?php echo $i; ?>" name="teams[<?php echo $i; ?>][team_name]" class="form-control w-full" required>
                                <option value="">Select a team</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary mb-4 px-4 py-2 rounded" onclick="addPlayerRow(<?php echo $i; ?>)">Add Player</button>
                        <div class="overflow-x-auto">
                            <table id="playersTable<?php echo $i; ?>" class="w-full mb-4">
                                <thead>
                                    <tr>
                                        <th class="rounded-tl-lg">First Name</th>
                                        <th>Last Name</th>
                                        <th>Number</th>
                                        <th>2Pt</th>
                                        <th>3Pt</th>
                                        <th>FT</th>
                                        <th>Reb</th>
                                        <th>Ast</th>
                                        <th>Stl</th>
                                        <th>Blk</th>
                                        <th>TO</th>
                                        <th>Fouls</th>
                                        <th>Pts</th>
                                        <th class="rounded-tr-lg"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Player rows will be dynamically added here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-4">
                            <label for="totalScore<?php echo $i; ?>" class="block mb-2 text-sm font-medium">Total Score:</label>
                            <input type="number" id="totalScore<?php echo $i; ?>" name="teams[<?php echo $i; ?>][total_score]" class="form-control w-full" required>
                        </div>
                    </div>
                <?php endfor; ?>
                <button type="submit" class="btn btn-success w-full py-3 px-4 rounded-lg text-lg font-semibold transition duration-300 ease-in-out transform hover:scale-105">Submit Game Stats</button>
            </form>
        </div>
    </div>
    <!-- Success Modal -->
    <div id="successModal" class="modal opacity-0 pointer-events-none fixed w-full h-full top-0 left-0 flex items-center justify-center">
        <div class="modal-overlay absolute w-full h-full bg-gray-900 opacity-50"></div>
        
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold">Success</p>
                    <div class="modal-close cursor-pointer z-50">
                        <svg class="fill-current text-black" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>
                <p id="modalMessage"></p>
                <div class="mt-4">
                    <button class="modal-close px-4 bg-indigo-500 p-3 rounded-lg text-white hover:bg-indigo-400">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        const leagueSelect = document.getElementById('leagueSelect');
        const teamSelect1 = document.getElementById('teamSelect1');
        const teamSelect2 = document.getElementById('teamSelect2');
        leagueSelect.addEventListener('change', function() {
            fetchTeams(this.value);
        });
        teamSelect1.addEventListener('change', function() {
            fetchPlayers(this.value, 1);
        });
        teamSelect2.addEventListener('change', function() {
            fetchPlayers(this.value, 2);
        });
        <?php if (!empty($successMessage)): ?>
        showModal('<?php echo $successMessage; ?>');
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
        alert('<?php echo implode("\n", $errors); ?>');
        <?php endif; ?>
    });
        function fetchLeagues() {
            fetch('fetch_leagues.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const leagueSelect = document.getElementById('leagueSelect');
                    leagueSelect.innerHTML = '<option value="">Select a league</option>';
                    
                    if (Array.isArray(data)) {
                        data.forEach(league => {
                            const option = document.createElement('option');
                            option.value = league;
                            option.textContent = league;
                            leagueSelect.appendChild(option);
                        });
                    } else if (data.error) {
                        throw new Error(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error fetching leagues:', error);
                    alert('Error fetching leagues: ' + error.message);
                });
        }
        // Call fetchLeagues when the page loads
        function fetchTeams(league_name) {
    if (!league_name) {
        console.error('League name is required');
        return;
    }

    fetch(`fetch_teams.php?league_name=${encodeURIComponent(league_name)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            const teamSelect1 = document.getElementById('teamSelect1');
            const teamSelect2 = document.getElementById('teamSelect2');
            
            // Clear existing options
            teamSelect1.innerHTML = '<option value="">Select a team</option>';
            teamSelect2.innerHTML = '<option value="">Select a team</option>';

            if (data.teams && Array.isArray(data.teams)) {
                data.teams.forEach(team => {
                    const option1 = document.createElement('option');
                    option1.value = team.team_name;
                    option1.textContent = team.team_name;
                    teamSelect1.appendChild(option1);

                    const option2 = document.createElement('option');
                    option2.value = team.team_name;
                    option2.textContent = team.team_name;
                    teamSelect2.appendChild(option2);
                });
            } else {
                console.warn('No teams found for this league');
            }
        })
        .catch(error => {
            console.error('Error fetching teams:', error);
            alert('Error fetching teams: ' + error.message);
        });
    }

        function fetchPlayers(team, teamIndex) {
            fetch(`fetch_players.php?team=${encodeURIComponent(team)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const playersTableBody = document.querySelector(`#playersTable${teamIndex} tbody`);
                    playersTableBody.innerHTML = '';
                    data.players.forEach((player, index) => {
                        addPlayerRow(teamIndex, player);
                    });
                })
                .catch(error => {
                    console.error('Error fetching players:', error);
                    alert('Error fetching players: ' + error.message);
                });
        }
        function addPlayerRow(teamIndex, player = null) {
            const table = document.getElementById(`playersTable${teamIndex}`).querySelector('tbody');
            const rowCount = table.rows.length;
            const row = table.insertRow(rowCount);
            row.innerHTML = `
                <td><input type="text" name="teams[${teamIndex}][players][${rowCount}][first_name]" class="form-control form-control-name" value="${player ? player.first_name : ''}" required ${player ? 'readonly' : ''}></td>
                <td><input type="text" name="teams[${teamIndex}][players][${rowCount}][last_name]" class="form-control form-control-name" value="${player ? player.last_name : ''}" required ${player ? 'readonly' : ''}></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][number]" class="form-control form-control-lg" value="${player ? player.number : ''}" required ${player ? 'readonly' : ''}></td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][2pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][2pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                </td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][3pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][3pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                </td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][ft_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][ft_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                </td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][reb_off]" class="form-control form-control-lg" placeholder="Off." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][reb_def]" class="form-control form-control-lg" placeholder="Def." required>
                </td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][assists]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][steals]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][blocks]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][turnovers]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][fouls]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][points]" class="form-control form-control-lg" required readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removePlayerRow(this)">Remove</button></td>
            `;
        }
        function validateMadeAttempted(input, teamIndex) {
            const row = input.closest('tr');
            const attemptedName = input.name.replace('_made', '_attempted');
            const attempted = row.querySelector(`[name="${attemptedName}"]`);
            if (parseInt(input.value) > parseInt(attempted.value)) {
                alert('Made shots cannot be greater than attempted shots.');
                input.value = attempted.value;
            }
            calculateTotalPoints(row);
            updateTotalScore(teamIndex);
        }
        function calculateTotalPoints(row) {
            const twoPtMade = parseInt(row.querySelector('[name*="[2pt_made]"]').value) || 0;
            const threePtMade = parseInt(row.querySelector('[name*="[3pt_made]"]').value) || 0;
            const ftMade = parseInt(row.querySelector('[name*="[ft_made]"]').value) || 0;
            const totalPoints = (twoPtMade * 2) + (threePtMade * 3) + ftMade;
            row.querySelector('[name*="[points]"]').value = totalPoints;
        }
        function updateTotalScore(teamIndex) {
            const totalScoreInput = document.getElementById(`totalScore${teamIndex}`);
            let totalPoints = 0;
            document.querySelectorAll(`#playersTable${teamIndex} [name*="[points]"]`).forEach(input => {
                totalPoints += parseInt(input.value) || 0;
            });
            totalScoreInput.value = totalPoints;
        }
        function removePlayerRow(button) {
            const row = button.closest('tr');
            const teamIndex = button.closest('table').id.replace('playersTable', '');
            row.remove();
            updateTotalScore(teamIndex);
        }
        function showModal(message) {
            const modal = document.getElementById('successModal');
            const modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = message;
            modal.classList.remove('opacity-0', 'pointer-events-none');
            document.body.classList.add('modal-active');
        }
        function closeModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('opacity-0', 'pointer-events-none');
            document.body.classList.remove('modal-active');
        }
        document.querySelectorAll('.modal-close').forEach(closeButton => {
            closeButton.addEventListener('click', closeModal);
        });
        document.querySelector('.modal-overlay').addEventListener('click', closeModal);
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.body.classList.contains('modal-active')) {
                closeModal();
            }
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
