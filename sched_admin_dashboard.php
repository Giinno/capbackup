<?php
session_start();
include 'db-connect.php';

// Check if the user is logged in and is a Scheduling-admin
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'scheduling-admin') {
    header("Location: login.php");
    exit;
}

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
    <title>Golden Boys Scheduling - Admin</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
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
            background-color: var(--background-color);
            color: var(--text-color);
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
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .stat-card {
            background-color: var(--card-bg);
            transition: all 0.3s;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background-color: var(--primary-color);
            color: var(--background-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
        }
        table th {
            background-color: var(--primary-color);
            color: var(--background-color);
        }
        table td {
            background-color: var(--card-bg);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: var(--card-bg);
            margin: 15% auto;
            padding: 20px;
            border: 1px solid var(--primary-color);
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            color: var(--text-color);
        }
        .close {
            color: var(--text-color-muted);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
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
            .logout-btn {
                position: static;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
        <div class="flex items-center justify-center mb-2">
            <img src="./images/Logo.png" alt="Ballers Hub Logo" class="w-12 h-12 mr-2">
            <h1 class="text-2xl font-semibold text-orange-500">Ballers Hub</h1>
        </div>
        <nav>
                <a href="sched-admin-dashboard.php" class="sidebar-item flex items-center rounded-lg">
                    <i class="fas fa-user-cog text-l" style="margin-left: -14px;"></i>
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
        <nav class="navbar navbar-expand-lg navbar-dark mb-4">
            <div class="container-fluid">
                <h4 style="margin-bottom: 50px;" class="navbar-brand" href="#"><?= htmlspecialchars($_SESSION['username']) ?></h4>
            </div>
        </nav>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
            <?php
            $roles = [
                'player' => ['icon' => 'fa-user', 'count' => $playerCount],
                'Statistics-admin' => ['icon' => 'fa-chart-pie', 'count' => $committeeCount],
                'Scheduling-admin' => ['icon' => 'fa-calendar-check', 'count' => $scheduleKeeperCount],
                'coach' => ['icon' => 'fa-whistle', 'count' => $coachCount],
                'total' => ['icon' => 'fa-users', 'count' => $totalCount]
            ];

            foreach ($roles as $role => $data) {
                $displayRole = ucfirst($role === 'Statistics-admin' ? 'Committee' : ($role === 'Scheduling-admin' ? 'Schedule Keeper' : $role));
                echo "<div class='stat-card p-6 rounded-lg cursor-pointer transform hover:scale-105 transition-all duration-300' onclick=\"showUsers('$role')\">";
                echo "<i class='fas {$data['icon']} text-4xl text-orange-500 mb-4'></i>";
                echo "<h2 class='text-3xl font-bold text-orange-500'>{$data['count']}</h2>";
                echo "<p class='text-lg text-gray-300'>$displayRole</p>";
                echo "</div>";
            }
            ?>
        </div>

        <div id="userList" class="p-6 rounded-lg shadow-lg"></div>
    </div>

    <div id="bookingNotificationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="text-2xl font-bold mb-4 text-orange-500">New Booking Notification</h2>
            <p class="mb-2">Booked by: <span id="bookingUser"></span></p>
            <p class="mb-2">Title: <span id="bookingTitle"></span></p>
            <p class="mb-2">Date & Time: <span id="bookingDateTime"></span></p>
            <p class="mb-2">Event Type: <span id="bookingType"></span></p>
            <p class="mb-2">Amount Paid: <span id="bookingAmount"></span></p>
        </div>
    </div>

    <script>
        function showUsers(role) {
            if (role === 'total') return;

            fetch(`sched-admin-dashboard.php?action=getUsers&role=${encodeURIComponent(role)}`)
                .then(response => response.json())
                .then(data => {
                    let tableHtml = `
                        <h2 class="text-2xl font-bold mb-4 text-orange-500">${role.charAt(0).toUpperCase() + role.slice(1)} List</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">First Name</th>
                                        <th class="px-4 py-2 text-left">Last Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.forEach(user => {
                        tableHtml += `
                            <tr class="hover:bg-gray-700 transition-colors duration-200">
                                <td class="px-4 py-2 border-t border-gray-700">${user.first_name}</td>
                                <td class="px-4 py-2 border-t border-gray-700">${user.last_name}</td>
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

        function showNotification(booking) {
            const modal = document.getElementById('bookingNotificationModal');
            const bookingUser = document.getElementById('bookingUser');
            const bookingTitle = document.getElementById('bookingTitle');
            const bookingDateTime = document.getElementById('bookingDateTime');
            const bookingType = document.getElementById('bookingType');
            const bookingAmount = document.getElementById('bookingAmount');

            bookingUser.textContent = `${booking.first_name} ${booking.last_name}` || 'N/A';
            bookingTitle.textContent = booking.title || 'N/A';
            bookingDateTime.textContent = new Date(booking.start_datetime).toLocaleString() || 'N/A';
            bookingType.textContent = booking.event_type || 'N/A';
            bookingAmount.textContent = booking.amount_paid ? `$${booking.amount_paid}` : 'N/A';

            modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('bookingNotificationModal');
            modal.style.display = 'none';
        }

        // Set up SSE
        const evtSource = new EventSource('sse_notifications.php');
        
        evtSource.addEventListener('newBooking', function(event) {
            const booking = JSON.parse(event.data);
            showNotification(booking);
        });

        evtSource.onerror = function(err) {
            console.error("EventSource failed:", err);
        };

        // Close the EventSource when the page is unloaded
        window.addEventListener('beforeunload', function() {
            evtSource.close();
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
