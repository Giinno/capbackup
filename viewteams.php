<?php
// Include the database connection
include 'db-connect.php';

// Start the session (if needed for user authentication)
session_start();

// Handle deletion if a POST request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_team'])) {
    $team_name = $_POST['team_name'];

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM teams WHERE team_name = ?");
    $stmt->bind_param("s", $team_name);

    if ($stmt->execute()) {
        echo "<script>alert('Team deleted successfully!'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Error deleting team: " . $conn->error . "'); window.location.href = window.location.href;</script>";
    }

    $stmt->close();
}

// Fetch teams from the database
$sql = "SELECT team_name, team_logo FROM teams";
$result = $conn->query($sql);

// Function to check if a link is active
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page) ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Teams - Ballers Hub</title>
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
        .table-container {
            background-color: #2c2c2c;
            border-radius: 8px;
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #ff6600;
            color: #1a1a1a;
            font-weight: bold;
        }
        tr:hover {
            background-color: #3a3a3a;
        }
        .modal-content {
            background-color: #2c2c2c;
            color: #ffffff;
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
            <h1 class="text-3xl font-bold text-center mb-8 text-orange-500">View and Delete Teams</h1>
            <div class="table-container">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Team Name</th>
                            <th class="px-4 py-2">Team Logo</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-2">
                                <a href="#" class="team-link text-blue-400 hover:text-blue-600" data-team-name="<?php echo htmlspecialchars($row['team_name']); ?>">
                                    <?php echo htmlspecialchars($row['team_name']); ?>
                                </a>
                            </td>
                            <td class="px-4 py-2">
                                <img src="<?php echo htmlspecialchars($row['team_logo']); ?>" alt="Team Logo" class="w-12 h-12 object-cover rounded-full cursor-pointer team-logo" data-team-name="<?php echo htmlspecialchars($row['team_name']); ?>">
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this team?');">
                                    <input type="hidden" name="team_name" value="<?php echo htmlspecialchars($row['team_name']); ?>">
                                    <input type="hidden" name="delete_team" value="1">
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for displaying players -->
    <div id="playersModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Players in Team</h3>
                    <div class="mt-2">
                        <div id="players-list" class="text-sm text-gray-300">
                            <!-- Player data will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal('playersModal')">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for displaying past games -->
    <div id="gamesModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-white" id="games-modal-title">Past Games</h3>
                    <div class="mt-2">
                        <div id="games-list" class="text-sm text-gray-300">
                            <!-- Game data will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal('gamesModal')">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.team-link').click(function(e) {
                e.preventDefault();
                var teamName = $(this).data('team-name');

                $.ajax({
                    url: 'teamplayers.php',
                    type: 'POST',
                    data: {team_name: teamName},
                    success: function(response) {
                        $('#players-list').html(response);
                        $('#playersModal').removeClass('hidden');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr);
                    }
                });
            });

            $('.team-logo').click(function(e) {
                e.preventDefault();
                var teamName = $(this).data('team-name');

                $.ajax({
                    url: 'teamgames.php',
                    type: 'POST',
                    data: {team_name: teamName},
                    success: function(response) {
                        $('#games-list').html(response);
                        $('#gamesModal').removeClass('hidden');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr);
                    }
                });
            });
        });

        function closeModal(modalId) {
            $('#' + modalId).addClass('hidden');
        }

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
