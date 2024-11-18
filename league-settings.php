<?php
// Include database connection
include 'db-connect.php';

// Fetch all teams from the database
$teams_query = "SELECT team_name, team_logo FROM teams ORDER BY team_name";
$teams_result = $conn->query($teams_query);
$teams = $teams_result->fetch_all(MYSQLI_ASSOC);

// Fetch all leagues from the database
$leagues_query = "SELECT DISTINCT league_name FROM events ORDER BY league_name";
$leagues_result = $conn->query($leagues_query);
$leagues = $leagues_result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ... (keep the existing form handling code)
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Games - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF8C00;
            --secondary-color: #FFA500;
            --text-color: #FFFFFF;
            --text-color-muted: #CCCCCC;
            --background-color: #1E1E1E;
            --sidebar-bg: #2C2C2C;
            --card-bg: #333333;
        }
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
        }
        .sidebar {
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar .navbar-brand {
            color: var(--primary-color);
            font-size: 24px;
            padding: 20px;
            text-align: center;
            font-weight: 600;
        }
        .sidebar a {
            padding: 15px 20px;
            color: var(--text-color);
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color: var(--primary-color);
            color: var(--background-color);
        }
        .logout-btn {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
        }
        .card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: var(--primary-color);
            text-align: center;
        }
        form {
            display: grid;
            gap: 15px;
        }
        label {
            font-weight: 500;
            color: var(--text-color);
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .btn {
            background-color: var(--primary-color);
            color: var(--background-color);
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }
        .btn:hover {
            background-color: var(--secondary-color);
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .success {
            background-color: #155724;
            color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #721c24;
            color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="flex items-center justify-center mb-2">
                <img src="./images/Logo.png" alt="Ballers Hub Logo" class="w-12 h-12 mr-2">
                <h1 class="text-2xl font-semibold" style="color: #FFFFFF;" >Ballers Hub</h1>
            </div>
            <nav>
                <a href="sched-admin-dashboard.php" class="sidebar-item flex items-center rounded-lg">
                    <i class="fas fa-user-cog text-l"></i>
                    <span>Scheduling Dashboard</span>
                </a>
                <a href="sched-admin.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fas fa-tasks text-l"></i>
                    <span>Schedule Calendar</span>
                </a>
                <a href="manage-schedule.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-chart-bar text-l"></i>
                    <span>Manage Schedule</span>
                </a>
                <a href="sched-report.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-sign-out-alt text-l"></i>
                    <span>Scheduling Report</span>
                </a>
                <a href="league-settings.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-basketball-ball text-l"></i>
                    <span>Schedule Leagues</span>
                </a>
                <button onclick="logout()" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg mt-auto w-full">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span>Logout</span>
        </button>
            </nav>
        </div>
        <div class="content">
            <div class="card">
                <h1>Schedule League Games</h1>
                
                <?php if (isset($success_message)): ?>
                    <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div>
                        <label for="league_name">League Name:</label>
                        <select name="league_name" id="league_name" required>
                            <option value="">Select a league</option>
                            <?php foreach ($leagues as $league): ?>
                                <option value="<?php echo htmlspecialchars($league['league_name']); ?>">
                                    <?php echo htmlspecialchars($league['league_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="title">Title:</label>
                        <input type="text" name="title" id="title" required>
                    </div>
                    
                    <div>
                        <label for="team1">Team 1:</label>
                        <select name="team1" id="team1" required>
                            <option value="">Select Team 1</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo htmlspecialchars($team['team_name']); ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="team2">Team 2:</label>
                        <select name="team2" id="team2" required>
                            <option value="">Select Team 2</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo htmlspecialchars($team['team_name']); ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="game_date">Game Date:</label>
                        <input type="date" name="game_date" id="game_date" required>
                    </div>
                    
                    <div>
                        <label for="game_time">Game Time:</label>
                        <input type="time" name="game_time" id="game_time" required>
                    </div>
                    
                    <div>
                        <label for="venue">Venue:</label>
                        <input type="text" name="venue" id="venue" required>
                    </div>
                    
                    <button type="submit" class="btn">Schedule Game</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Prevent selecting the same team for both team1 and team2
        document.getElementById('team2').addEventListener('change', function() {
            var team1 = document.getElementById('team1');
            var team2 = document.getElementById('team2');
            if (team1.value === team2.value) {
                alert("Team 1 and Team 2 cannot be the same!");
                this.value = "";
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
