<?php
include 'db-connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'] ?? null;
    $last_name = $_POST['last_name'] ?? null;
    $action = $_POST['action'];

    if ($action == 'Delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE first_name=? AND last_name=?");
        $stmt->bind_param("ss", $first_name, $last_name);
        if ($stmt->execute()) {
            echo "Deleted Successfully";
        } else {
            echo "Failed to delete. Please try again.";
        }
    } else {
        $number = $_POST['number'];
        $position = $_POST['position'];
        $team = $_POST['team'];
        $height = $_POST['height'];
        $born = $_POST['born'];
        $profile_picture = $_FILES['profile_picture']['name'];
        $original_first_name = $_POST['original_first_name'] ?? null;
        $original_last_name = $_POST['original_last_name'] ?? null;

        $target_file = "";
        if ($profile_picture) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                echo "Sorry, there was an error uploading your file.";
                exit;
            }
        }

        if ($action == 'Add') {
            $stmt = $conn->prepare("INSERT INTO users (number, first_name, last_name, position, team, height, born, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $number, $first_name, $last_name, $position, $team, $height, $born, $target_file);
            if ($stmt->execute()) {
                echo "Profile added successfully";
            } else {
                echo "Failed to add profile. Please try again.";
            }
        } elseif ($action == 'Update') {
            if ($target_file) {
                $stmt = $conn->prepare("UPDATE users SET number=?, first_name=?, last_name=?, position=?, team=?, height=?, born=?, profile_picture=? WHERE first_name=? AND last_name=?");
                $stmt->bind_param("isssssssss", $number, $first_name, $last_name, $position, $team, $height, $born, $target_file, $original_first_name, $original_last_name);
            } else {
                $stmt = $conn->prepare("UPDATE users SET number=?, first_name=?, last_name=?, position=?, team=?, height=?, born=? WHERE first_name=? AND last_name=?");
                $stmt->bind_param("issssssss", $number, $first_name, $last_name, $position, $team, $height, $born, $original_first_name, $original_last_name);
            }
            if ($stmt->execute()) {
                echo "Success! Your Changes have been edited and saved";
            } else {
                echo "Failed to update profile. Please try again.";
            }
        }
    }
    exit;
}

// Fetch profiles with role "player"
$sql = "SELECT * FROM users WHERE role = 'player'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile Management</title>
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

    <div class="content flex-1 p-10 overflow-y-auto">
        <h2 class="text-3xl font-bold text-center mb-8 text-orange-500">Player Profile Management</h2>

        <div class="mb-4 relative">
            <input type="text" id="searchInput" class="w-full p-2 pl-10 bg-gray-800 text-white rounded-lg" placeholder="Search profiles...">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>

        <button class="btn-primary mb-4 px-4 py-2 rounded-lg flex items-center" onclick="prepareAddProfile()">
            <i class="fas fa-plus mr-2"></i> Add Profile
        </button>

        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 rounded-tl-lg">Number</th>
                        <th class="px-4 py-2">First Name</th>
                        <th class="px-4 py-2">Last Name</th>
                        <th class="px-4 py-2">Position</th>
                        <th class="px-4 py-2">Team</th>
                        <th class="px-4 py-2">Height</th>
                        <th class="px-4 py-2">Born</th>
                        <th class="px-4 py-2">Profile Picture</th>
                        <th class="px-4 py-2 rounded-tr-lg">Actions</th>
                    </tr>
                </thead>
                <tbody id="profileTableBody">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td class='border px-4 py-2'>{$row['number']}</td>
                                <td class='border px-4 py-2'>{$row['first_name']}</td>
                                <td class='border px-4 py-2'>{$row['last_name']}</td>
                                <td class='border px-4 py-2'>{$row['position']}</td>
                                <td class='border px-4 py-2'>{$row['team']}</td>
                                <td class='border px-4 py-2'>{$row['height']}</td>
                                <td class='border px-4 py-2'>{$row['born']}</td>
                                <td class='border px-4 py-2'><img src='{$row['profile_picture']}' alt='Profile Picture' class='w-16 h-16 object-cover rounded-full'></td>
                                <td class='border px-4 py-2'>
                                    <button class='bg-yellow-500 text-black px-2 py-1 rounded-lg mr-2' onclick='editProfile(" . json_encode($row) . ")'><i class='fas fa-edit'></i></button>
                                    <button class='bg-red-500 text-white px-2 py-1 rounded-lg' onclick='deleteProfile(\"{$row['first_name']}\", \"{$row['last_name']}\")'><i class='fas fa-trash-alt'></i></button>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center border px-4 py-2'>No profiles found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    <div id="profileModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeProfileModal()">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex">
                    <div class="w-2/3 p-6">
                        <h3 class="text-lg font-medium text-gray-100 mb-4" id="profileModalLabel">Add/Edit Profile</h3>
                        <form id="profileForm" method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-gray-300 mb-2">First Name</label>
                                    <input type="text" id="first_name" name="first_name" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-gray-300 mb-2">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="number" class="block text-gray-300 mb-2">Number</label>
                                    <input type="number" id="number" name="number" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="position" class="block text-gray-300 mb-2">Position</label>
                                    <input type="text" id="position" name="position" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="team" class="block text-gray-300 mb-2">Team</label>
                                    <input type="text" id="team" name="team" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="height" class="block text-gray-300 mb-2">Height</label>
                                    <input type="text" id="height" name="height" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="born" class="block text-gray-300 mb-2">Born</label>
                                    <input type="date" id="born" name="born" required class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                                <div>
                                    <label for="profile_picture" class="block text-gray-300 mb-2">Profile Picture</label>
                                    <input type="file" id="profile_picture" name="profile_picture" class="w-full px-3 py-2 bg-gray-700 text-white rounded-lg">
                                </div>
                            </div>
                            <input type="hidden" id="original_first_name" name="original_first_name">
                            <input type="hidden" id="original_last_name" name="original_last_name">
                            <input type="hidden" id="action" name="action">
                            <div class="mt-6">
                                <button type="submit" class="inline-flex justify-center w-full rounded-lg border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:text-sm">
                                    Submit
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="w-1/3 bg-gray-700 p-6 flex items-center justify-center">
                        <img id="currentProfilePicture" src="" alt="Current Profile Picture" class="max-w-full max-h-64 object-cover rounded-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-100">Delete Profile</h3>
                    <p class="mt-2 text-gray-300">Are you sure you want to delete this profile?</p>
                </div>
                <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteForm" method="POST">
                        <input type="hidden" id="deleteFirstName" name="first_name">
                        <input type="hidden" id="deleteLastName" name="last_name">
                        <input type="hidden" name="action" value="Delete">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                    </form>
                    <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-gray-700 text-base font-medium text-gray-300 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="fixed z-20 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-100" id="messageModalTitle"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-300" id="messageModalContent"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeMessageModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openModal(action, profile = null) {
            const modal = document.getElementById('profileModal');
            const form = document.getElementById('profileForm');
            document.getElementById('action').value = action;
            if (action === 'Update') {
                // Pre-fill the form with the profile data
                document.getElementById('first_name').value = profile.first_name;
                document.getElementById('last_name').value = profile.last_name;
                document.getElementById('number').value = profile.number;
                document.getElementById('position').value = profile.position;
                document.getElementById('team').value = profile.team;
                document.getElementById('height').value = profile.height;
                document.getElementById('born').value = profile.born;
                document.getElementById('original_first_name').value = profile.first_name;
                document.getElementById('original_last_name').value = profile.last_name;
                document.getElementById('currentProfilePicture').src = profile.profile_picture;
            } else {
                // Clear the form for adding a new profile
                form.reset();
                document.getElementById('currentProfilePicture').src = '';
            }
            modal.classList.remove('hidden');
        }

        function closeProfileModal() {
            document.getElementById('profileModal').classList.add('hidden');
        }

        function deleteProfile(firstName, lastName) {
            document.getElementById('deleteFirstName').value = firstName;
            document.getElementById('deleteLastName').value = lastName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function prepareAddProfile() {
            openModal('Add');
        }

        function editProfile(profile) {
            openModal('Update', profile);
        }

        // Add this function for the search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#profileTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Add logout function
        function logout() {
            // Implement logout logic here
            alert('Logout functionality to be implemented');
        }

        // Function to display messages
        function displayMessage(message, type) {
            const messageModal = document.getElementById('messageModal');
            const messageTitle = document.getElementById('messageModalTitle');
            const messageContent = document.getElementById('messageModalContent');
            
            messageTitle.textContent = type === 'success' ? 'Success' : 'Error';
            messageContent.textContent = message;
            messageModal.classList.remove('hidden');
        }

        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
            location.reload();
        }

        // Modify form submission to use AJAX
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                closeProfileModal();
                displayMessage(data, 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('An error occurred. Please try again.', 'danger');
            });
        });

        // Modify delete form submission to use AJAX
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                closeDeleteModal();
                displayMessage(data, 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('An error occurred. Please try again.', 'danger');
            });
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
