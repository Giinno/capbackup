<?php
include 'db-connect.php';

// Function to get user count by role
function getUserCountByRole($conn, $role) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Function to get users by role
function getUsersByRole($conn, $role) {
    $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    return $users;
}

// Handle AJAX request
if (isset($_GET['action']) && $_GET['action'] == 'getUsers') {
    header('Content-Type: application/json');
    $role = $_GET['role'];
    $users = getUsersByRole($conn, $role);
    echo json_encode($users);
    exit;
}

// Get counts for each role
$playerCount = getUserCountByRole($conn, 'player');
$committeeCount = getUserCountByRole($conn, 'Statistics-admin');
$scheduleKeeperCount = getUserCountByRole($conn, 'Scheduling-admin');
$coachCount = getUserCountByRole($conn, 'coach');

// Calculate total count
$totalCount = $playerCount + $committeeCount + $scheduleKeeperCount + $coachCount;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff6600;
            --bg-color: #1a1a1a;
            --text-color: #e0e0e0;
            --sidebar-bg: #222222;
            --card-bg: #2a2a2a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .sidebar {
            background-color: var(--sidebar-bg);
            transition: all 0.3s;
        }

        .sidebar-item:hover {
            background-color: var(--primary-color);
            color: var(--bg-color);
        }

        .stat-card {
            background-color: var(--card-bg);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--bg-color);
        }

        .btn-primary:hover {
            background-color: #ff8533;
        }

        table th {
            background-color: var(--primary-color);
            color: var(--bg-color);
        }

        table td {
            background-color: var(--card-bg);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen">
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

    <main class="flex-grow p-6 md:p-8">
        <h1 class="text-3xl font-bold mb-6 text-orange-500">Admin Dashboard</h1>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
            <div class="stat-card p-4 rounded-lg cursor-pointer" onclick="showUsers('player')">
                <h2 class="text-3xl font-bold text-orange-500"><?php echo $playerCount; ?></h2>
                <p class="text-lg">Players</p>
            </div>
            <div class="stat-card p-4 rounded-lg cursor-pointer" onclick="showUsers('Statistics-admin')">
                <h2 class="text-3xl font-bold text-orange-500"><?php echo $committeeCount; ?></h2>
                <p class="text-lg">Committees</p>
            </div>
            <div class="stat-card p-4 rounded-lg cursor-pointer" onclick="showUsers('Scheduling-admin')">
                <h2 class="text-3xl font-bold text-orange-500"><?php echo $scheduleKeeperCount; ?></h2>
                <p class="text-lg">Schedule Keepers</p>
            </div>
            <div class="stat-card p-4 rounded-lg cursor-pointer" onclick="showUsers('coach')">
                <h2 class="text-3xl font-bold text-orange-500"><?php echo $coachCount; ?></h2>
                <p class="text-lg">Coaches</p>
            </div>
            <div class="stat-card p-4 rounded-lg">
                <h2 class="text-3xl font-bold text-orange-500"><?php echo $totalCount; ?></h2>
                <p class="text-lg">Total Users</p>
            </div>
        </div>

        <div id="userList" class="p-4 rounded-lg"></div>
    </main>

    <script>
        function showUsers(role) {
            fetch(`admin-dashboard.php?action=getUsers&role=${encodeURIComponent(role)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    let tableHtml = `
                        <h2 class="text-2xl font-bold mb-4 text-orange-500">${role.charAt(0).toUpperCase() + role.slice(1)} List</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2">First Name</th>
                                        <th class="px-4 py-2">Last Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.forEach(user => {
                        tableHtml += `
                            <tr>
                                <td class="px-4 py-2">${user.first_name}</td>
                                <td class="px-4 py-2">${user.last_name}</td>
                            </tr>
                        `;
                    });

                    tableHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    document.getElementById('userList').innerHTML = tableHtml;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('userList').innerHTML = `<p class="text-red-500">Error loading user data: ${error.message}</p>`;
                });
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
