<?php
session_start();
require_once 'db-connect.php';

function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page) ? 'active' : '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ['status' => '', 'message' => '', 'existing_players' => []];

    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['number']) && !empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['number'])) {
        $team = $conn->real_escape_string($_POST['team']);

        // Upload team logo if provided
        $team_logo = null;
        if (isset($_FILES['team_logo']) && $_FILES['team_logo']['error'] === UPLOAD_ERR_OK) {
            $logo_name = $_FILES['team_logo']['name'];
            $logo_tmp = $_FILES['team_logo']['tmp_name'];
            $logo_destination = 'uploads/' . $logo_name;
            if (move_uploaded_file($logo_tmp, $logo_destination)) {
                $team_logo = $logo_destination;
            } else {
                $response['status'] = 'error';
                $response['message'] = "Error uploading team logo.";
                echo json_encode($response);
                exit();
            }
        }

        $first_names = $_POST['first_name'];
        $last_names = $_POST['last_name'];
        $numbers = $_POST['number'];
        $positions = $_POST['position'];
        $heights = $_POST['height'];
        $borns = $_POST['born'];
        $profile_pictures = $_FILES['profile_picture'];

        // Check if any players already exist
        $existing_players = [];
        for ($i = 0; $i < count($first_names); $i++) {
            $first_name = $conn->real_escape_string($first_names[$i]);
            $last_name = $conn->real_escape_string($last_names[$i]);
            $result = $conn->query("SELECT id FROM users WHERE first_name='$first_name' AND last_name='$last_name'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $existing_players["$first_name $last_name"] = $row['id'];
            }
        }

        if (!empty($existing_players)) {
            $response['status'] = 'exists';
            $response['message'] = 'Some players already exist in the database.';
            $response['existing_players'] = $existing_players;
            echo json_encode($response);
            exit();
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Create team
            $stmt_team = $conn->prepare("INSERT INTO teams (team_name, team_logo) VALUES (?, ?)");
            $stmt_team->bind_param("ss", $team, $team_logo);
            $stmt_team->execute();
            $stmt_team->close();

            // Insert new players with default role "player"
            $stmt_users_insert = $conn->prepare("INSERT INTO users (team, first_name, last_name, number, position, height, born, profile_picture, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

            for ($i = 0; $i < count($first_names); $i++) {
                $first_name = isset($first_names[$i]) ? $conn->real_escape_string($first_names[$i]) : null;
                $last_name = isset($last_names[$i]) ? $conn->real_escape_string($last_names[$i]) : null;
                $number = isset($numbers[$i]) ? $conn->real_escape_string($numbers[$i]) : null;
                $position = isset($positions[$i]) ? $conn->real_escape_string($positions[$i]) : null;
                $height = isset($heights[$i]) ? $conn->real_escape_string($heights[$i]) : null;
                $born = isset($borns[$i]) ? $conn->real_escape_string($borns[$i]) : null;
                $role = "player"; // Default role

                $profile_picture = null;
                if (isset($profile_pictures['name'][$i]) && !empty($profile_pictures['name'][$i])) {
                    $file_name = $profile_pictures['name'][$i];
                    $file_tmp = $profile_pictures['tmp_name'][$i];
                    if ($_FILES['profile_picture']['error'][$i] === UPLOAD_ERR_OK) {
                        $file_destination = 'uploads/' . $file_name;
                        move_uploaded_file($file_tmp, $file_destination);
                        $profile_picture = $file_destination;
                    }
                }

                if ($first_name && $last_name && $number && $position && $height && $born) {
                    $stmt_users_insert->bind_param("sssisssss", $team, $first_name, $last_name, $number, $position, $height, $born, $profile_picture, $role);
                    $stmt_users_insert->execute();
                } else {
                    throw new Exception("Player data is incomplete.");
                }
            }

            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = 'Team has been successfully created!';
        } catch (Exception $e) {
            $conn->rollback();
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }

        if ($stmt_users_insert) {
            $stmt_users_insert->close();
        }

        echo json_encode($response);
        exit();
    } elseif (isset($_POST['update_team']) && $_POST['update_team'] == "true") {
        $player_ids = $_POST['player_id'];
        $team = $conn->real_escape_string($_POST['team']);

        $conn->begin_transaction();

        try {
            // Check if the team exists, if not create it
            $stmt_check_team = $conn->prepare("SELECT id FROM teams WHERE team_name = ?");
            $stmt_check_team->bind_param("s", $team);
            $stmt_check_team->execute();
            $result = $stmt_check_team->get_result();

            if ($result->num_rows == 0) {
                // Team doesn't exist, create it
                $stmt_create_team = $conn->prepare("INSERT INTO teams (team_name) VALUES (?)");
                $stmt_create_team->bind_param("s", $team);
                $stmt_create_team->execute();
                $stmt_create_team->close();
            }

            $stmt_check_team->close();

            // Update existing players' team
            $stmt_users_update = $conn->prepare("UPDATE users SET team = ? WHERE id = ?");
            foreach ($player_ids as $player_id) {
                $player_id = $conn->real_escape_string($player_id);
                $stmt_users_update->bind_param("si", $team, $player_id);
                $stmt_users_update->execute();
            }
            $stmt_users_update->close();

            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = 'Team created and players updated successfully!';
        } catch (Exception $e) {
            $conn->rollback();
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }

        echo json_encode($response);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Team - Ballers Hub</title>
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
        #errorModal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }
        #errorModal > div {
            background-color: #2c2c2c;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }
        #errorModal button {
            margin: 10px;
            padding: 10px 20px;
            background-color: #ff6600;
            color: #ffffff;
            border: none;
            cursor: pointer;
        }
        #errorModal button:hover {
            background-color: #ff8533;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
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
        <div class="max-w-4xl mx-auto rounded-lg shadow-lg p-8">
            <h2 class="text-3xl font-bold text-center mb-8 text-orange-500">Create a Team and Add Players</h2>
            <form id="playerForm" method="post" action="CreateTeam.php" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="team" class="block text-sm font-medium text-gray-300">Team Name:</label>
                        <input type="text" id="team" name="team" required class="form-control w-full mt-1">
                    </div>
                    <div>
                        <label for="team_logo" class="block text-sm font-medium text-gray-300">Team Logo:</label>
                        <input type="file" id="team_logo" name="team_logo" accept="image/*" required class="form-control w-full mt-1">
                    </div>
                </div>

                <div id="player-container" class="space-y-6">
                    <div class="player-group p-4 rounded-lg">
                        <div class="player-header flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-orange-500">Player 1:</h3>
                            <button type="button" class="remove-player-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" onclick="removePlayer(this.parentNode.parentNode)" style="display: none;">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" name="first_name[]" placeholder="First Name" required class="form-control">
                            <input type="text" name="last_name[]" placeholder="Last Name" required class="form-control">
                            <input type="number" name="number[]" placeholder="Number" required class="form-control">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Position:</label>
                                <select name="position[]" required class="form-control w-full">
                                    <option value="Point Guard">Point Guard</option>
                                    <option value="Shooting Guard">Shooting Guard</option>
                                    <option value="Small Forward">Small Forward</option>
                                    <option value="Power Forward">Power Forward</option>
                                    <option value="Center">Center</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Height:</label>
                                <select name="height[]" required class="form-control w-full">
                                    <?php for ($feet = 4; $feet <= 7; $feet++): ?>
                                        <?php for ($inches = 0; $inches < 12; $inches++): ?>
                                            <option value="<?= $feet . 'ft ' . $inches . 'in' ?>">
                                                <?= $feet . 'ft ' . $inches . 'in' ?>
                                            </option>
                                        <?php endfor; ?>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Birthdate:</label>
                                <input type="date" name="born[]" required class="form-control w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Profile Picture:</label>
                                <input type="file" name="profile_picture[]" accept="image/*" class="form-control w-full">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <button type="button" class="btn-primary px-4 py-2 rounded" onclick="addColumn()">Add Player</button>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">Create Team</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal">
        <div>
            <span class="close">&times;</span>
            <h2 id="modalMessage"></h2>
            <button id="updateTeamBtn" style="display: none;">Update Team</button>
            <button id="cancelBtn">Cancel</button>
        </div>
    </div>

    <script>
        function addColumn() {
            const container = document.getElementById('player-container');
            const playerCount = container.children.length + 1;

            const playerGroup = document.createElement('div');
            playerGroup.className = 'player-group bg-gray-700 p-4 rounded-lg';

            playerGroup.innerHTML = `
                <div class="player-header flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-orange-500">Player ${playerCount}:</h3>
                    <button type="button" class="remove-player-btn bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" onclick="removePlayer(this.parentNode.parentNode)">Remove</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="first_name[]" placeholder="First Name" required class="form-control">
                    <input type="text" name="last_name[]" placeholder="Last Name" required class="form-control">
                    <input type="number" name="number[]" placeholder="Number" required class="form-control">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Position:</label>
                        <select name="position[]" required class="form-control w-full">
                            <option value="Point Guard">Point Guard</option>
                            <option value="Shooting Guard">Shooting Guard</option>
                            <option value="Small Forward">Small Forward</option>
                            <option value="Power Forward">Power Forward</option>
                            <option value="Center">Center</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Height:</label>
                        <select name="height[]" required class="form-control w-full">
                            <?php for ($feet = 4; $feet <= 7; $feet++): ?>
                                <?php for ($inches = 0; $inches < 12; $inches++): ?>
                                    <option value="<?= $feet . 'ft ' . $inches . 'in' ?>">
                                        <?= $feet . 'ft ' . $inches . 'in' ?>
                                    </option>
                                <?php endfor; ?>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Birthdate:</label>
                        <input type="date" name="born[]" required class="form-control w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Profile Picture:</label>
                        <input type="file" name="profile_picture[]" accept="image/*" class="form-control w-full">
                    </div>
                </div>
            `;

            container.appendChild(playerGroup);
            updateRemoveButtons();
        }

        function removePlayer(playerElement) {
            const container = document.getElementById('player-container');
            container.removeChild(playerElement);
            updatePlayerLabels();
            updateRemoveButtons();
        }

        function updatePlayerLabels() {
            const playerGroups = document.querySelectorAll('#player-container .player-group');
            
            playerGroups.forEach((group, index) => {
                const label = group.querySelector('h3');
                label.textContent = `Player ${index + 1}:`;
            });
        }

        function updateRemoveButtons() {
            const playerGroups = document.querySelectorAll('#player-container .player-group');
            playerGroups.forEach((group, index) => {
                const removeButton = group.querySelector('.remove-player-btn');
                if (playerGroups.length > 1) {
                    removeButton.style.display = 'inline-block';
                } else {
                    removeButton.style.display = 'none';
                }
            });
        }

        function showModal(message) {
            const modal = document.getElementById('errorModal');
            const modalMessage = document.getElementById('modalMessage');
            const updateTeamBtn = document.getElementById('updateTeamBtn');

            modalMessage.textContent = message;
            updateTeamBtn.style.display = 'none';
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        document.getElementById('playerForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('CreateTeam.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showModal(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else if (data.status === 'exists') {
                    document.getElementById('modalMessage').innerHTML = `${data.message}<br><ul>`;
                    for (const [player, id] of Object.entries(data.existing_players)) {
                        document.getElementById('modalMessage').innerHTML += `<li>${player}</li>`;
                    }
                    document.getElementById('modalMessage').innerHTML += '</ul>';
                    document.getElementById('updateTeamBtn').style.display = 'inline-block';
                    document.getElementById('errorModal').style.display = 'block';

                    document.getElementById('updateTeamBtn').onclick = function() {
                        const updateFormData = new FormData();
                        updateFormData.append('update_team', 'true');
                        updateFormData.append('team', formData.get('team'));
                        for (const [name, id] of Object.entries(data.existing_players)) {
                            updateFormData.append('player_id[]', id);
                        }

                        fetch('CreateTeam.php', {
                            method: 'POST',
                            body: updateFormData
                        })
                        .then(response => response.json())
                        .then(data => {
                            showModal(data.message);
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showModal('An error occurred while updating teams.');
                        });
                    };
                } else {
                    showModal(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('An error occurred while submitting the form.');
            });
        };

        document.getElementById('cancelBtn').onclick = closeModal;
        document.querySelector('.close').onclick = closeModal;

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

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('errorModal');
            if (event.target == modal) {
                closeModal();
            }
        } 
    </script>
</body>
</html>
