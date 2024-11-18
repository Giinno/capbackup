<?php
include 'db-connect.php';

$sql = "SELECT s.*, u.profile_picture 
        FROM statistics s 
        LEFT JOIN users u ON s.first_name = u.first_name AND s.last_name = u.last_name";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basketball Game CMS</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
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
            color: #121212;
        }
        .content {
            background-color: #1a1a1a;
        }
        .btn-primary {
            background-color: #ff6600;
            color: #121212;
        }
        .btn-primary:hover {
            background-color: #ff8533;
        }
        .table th {
            background-color: #ff6600;
            color: #121212;
        }
        .table td {
            background-color: #2a2a2a;
        }
        .modal-content {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }
    </style>
</head>
<body class="flex h-screen bg-gray-900">
    <div class="sidebar w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
        <div class="flex items-center justify-center mb-8">
            <img src="./images/Logo.png" alt="Ballers Hub Logo" class="w-12 h-12 mr-2">
            <h1 class="text-2xl font-semibold text-orange-500">Ballers Hub</h1>
        </div>
        <nav>
            <a href="profile-cms.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-user-cog text-xl"></i>
                <span>Profile Settings</span>
            </a>
            <a href="stats-cms.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                <i class="fas fa-chart-bar text-xl"></i>
                <span>Statistics Settings</span>
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
                <i class="fas fa-file-alt text-xl"></i>
                <span>Stats Report</span>
            </a>
        </nav>
        <button onclick="logout()" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg mt-auto w-full">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span>Logout</span>
        </button>
    </div>

    <div class="content flex-1 p-10 overflow-y-auto">
        <h2 class="text-3xl font-bold text-center mb-8 text-orange-500">Basketball Game CMS</h2>

        <div class="mb-4 relative">
            <input type="text" id="searchInput" class="w-full p-2 pl-10 bg-gray-800 text-white rounded-lg" placeholder="Search players...">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Picture</th>
                        <th class="px-4 py-2">First Name</th>
                        <th class="px-4 py-2">Last Name</th>
                        <th class="px-4 py-2">No.</th>
                        <th class="px-4 py-2">Pts</th>
                        <th class="px-4 py-2">Ast</th>
                        <th class="px-4 py-2">Reb D</th>
                        <th class="px-4 py-2">Reb O</th>
                        <th class="px-4 py-2">Stl</th>
                        <th class="px-4 py-2">Blk</th>
                        <th class="px-4 py-2">TO</th>
                        <th class="px-4 py-2">Fls</th>
                        <th class="px-4 py-2">2PA</th>
                        <th class="px-4 py-2">2PM</th>
                        <th class="px-4 py-2">3PA</th>
                        <th class="px-4 py-2">3PM</th>
                        <th class="px-4 py-2">FTA</th>
                        <th class="px-4 py-2">FTM</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="playerTableBody">
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='border px-4 py-2'><img src='" . $row["profile_picture"] . "' alt='Profile' class='w-12 h-12 rounded-full object-cover'></td>";
                            echo "<td class='border px-4 py-2'>" . $row["first_name"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["last_name"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["number"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["points"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["assists"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["reb_def"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["reb_off"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["steals"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["blocks"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["turnovers"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["fouls"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["2pt_attempted"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["2pt_made"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["3pt_attempted"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["3pt_made"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["ft_attempted"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["ft_made"] . "</td>";
                            echo "<td class='border px-4 py-2'>
                                    <button class='bg-yellow-500 text-black px-2 py-1 rounded-lg mr-2' onclick='editPlayer(" . json_encode($row) . ")'><i class='fas fa-edit'></i></button>
                                    <button class='bg-red-500 text-white px-2 py-1 rounded-lg' onclick='deletePlayer(" . $row["id"] . ")'><i class='fas fa-trash-alt'></i></button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='19' class='text-center border px-4 py-2'>No players found</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="playerModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="stats-handler.php" method="post" enctype="multipart/form-data" id="playerForm">
                    <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-100 mb-4">Player Form</h3>
                        <input type="hidden" name="action" id="formAction">
                        <input type="hidden" name="player_id" id="playerId">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="playerFirstName" class="block text-gray-300 mb-2">First Name:</label>
                                <input type="text" name="first_name" id="playerFirstName" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                            </div>
                            <div>
                                <label for="playerLastName" class="block text-gray-300 mb-2">Last Name:</label>
                                <input type="text" name="last_name" id="playerLastName" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                            </div>
                            <div>
                                <label for="playerNumber" class="block text-gray-300 mb-2">Number:</label>
                                <input type="number" name="number" id="playerNumber" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg" required>
                            </div>
                            <!-- Add all other form fields here, following the same pattern -->
                        </div>
                        <div class="mt-4">
                            <label for="profilePicture" class="block text-gray-300 mb-2">Profile Picture:</label>
                            <input type="file" name="profile_picture" id="profilePicture" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                            <img id="profilePicturePreview" src="#" alt="Profile Picture Preview" class="mt-2 max-h-40 hidden">
                        </div>
                    </div>
                    <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save changes
                        </button>
                        <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function editPlayer(player) {
            $('#formAction').val('update');
            $('#playerId').val(player.id);
            $('#playerFirstName').val(player.first_name);
            $('#playerLastName').val(player.last_name);
            $('#playerNumber').val(player.number);
            // Set values for all other fields
            $('#profilePicturePreview').attr('src', player.profile_picture).show();
            $('#playerModal').removeClass('hidden');
        }

        function deletePlayer(playerId) {
            if (confirm('Are you sure you want to delete this player?')) {
                $.ajax({
                    url: 'stats-handler.php',
                    type: 'POST',
                    data: { action: 'delete', player_id: playerId },
                    success: function(response) {
                        location.reload();
                    }
                });
            }
        }

        function closeModal() {
            $('#playerModal').addClass('hidden');
        }

        function logout() {
            // Implement logout logic here
            window.location.href = 'login.php';
        }

        $('#playerForm').on('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                url: 'stats-handler.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    closeModal();
                    location.reload();
                }
            });
        });

        $('#profilePicture').on('change', function() {
            const [file] = this.files;
            if (file) {
                $('#profilePicturePreview').attr('src', URL.createObjectURL(file)).removeClass('hidden');
            }
        });

        $(document).ready(function() {
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#playerTableBody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>
