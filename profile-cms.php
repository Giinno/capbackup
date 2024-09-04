<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile Management</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #f56C00;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }

        .sidebar a {
            font-size: 18px;
            text-decoration: none;
            color: #222222;
            padding: 15px 20px;
            text-align: center;
            width: 80%;
            margin: 10px 0;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            background-color: #ffffff;
            color: black;
            font-weight: bold;
        }

        .sidebar a:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content-container {
            background-color: #333333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin-left: -100px;
        }

        h1 {
            color: #f56C00;
        }

        .logout-button {
            margin-top: auto;
            padding: 10px 20px;
            background-color: #222222;
            color: #f56C00;
            border: none;
            cursor: pointer;
            font-size: 18px;
            text-align: center;
            width: 80%;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .logout-button:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .form-group label {
            font-weight: bold;
        }
        label{
            color: #121212;
        }

        .profile-container {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            padding: 15px;
            max-width: 1000px;
            margin: 20px auto;
            background-color: #333333;
            border-radius: 8px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-info h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
        }

        .profile-info h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #bbbbbb;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .stats div {
            text-align: center;
            flex: 1;
            padding: 0 10px;
        }

        .stats div:not(:last-child) {
            border-right: 1px solid #ddd;
        }

        .additional-info {
            margin-top: 10px;
            color: #bbbbbb;
        }

        .btn-primary,
        .btn-secondary,
        .btn-warning,
        .btn-danger {
            border-radius: 20px;
        }

        .btn-primary,
        .btn-warning,
        .btn-danger {
            color: #ffffff;
        }
        h5 {
            color: #222222;
        }
    </style>
    <script>
        function loadPage(page) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', page, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('content-container').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function logout() {
            window.location.href = 'login.php';
        }

        window.onload = function () {
            loadPage('profile-cms.php');
        };
    </script>
</head>

<body>
    <div class="sidebar">
        <p class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="#">Profile Settings</a>
        <a href="stats-cms.php">Statistics Settings</a>
        <a href="gamresult.php">Game Results</a>
        <a href="CreateTeam.php">Create Team</a>
        <a href="edit-card-content.php">Dashboard Showcase</a>
        <a href="viewteams.php">View Teams</a>
        <a href="Feedback.php">Feedback</a>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="content">
        <div id="content-container" class="content-container">
            <div class="container">
                <h2 class="text-center my-4">Player Profile Management</h2>

                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "ballers_db";

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $name = $_POST['name'] ?? null;
                    $action = $_POST['action'];

                    if ($action == 'Delete') {
                        // Handle delete action
                        $stmt = $conn->prepare("DELETE FROM profiles WHERE name=?");
                        $stmt->bind_param("s", $name);
                        $stmt->execute();
                    } else {
                        // Handle add/update actions
                        $number = $_POST['number'];
                        $position = $_POST['position'];
                        $team = $_POST['team'];
                        $height = $_POST['height'];
                        $born = $_POST['born'];
                        $profile_picture = $_FILES['profile_picture']['name'];
                        $original_name = $_POST['original_name'] ?? null;

                        $target_file = "";
                        if ($profile_picture) {
                            $target_dir = "uploads/";
                            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
                            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                                echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
                                $target_file = "";
                            }
                        }

                        if ($action == 'Add') {
                            $stmt = $conn->prepare("INSERT INTO profiles (number, name, position, team, height, born, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("issssss", $number, $name, $position, $team, $height, $born, $target_file);
                            $stmt->execute();
                        } elseif ($action == 'Update') {
                            if ($target_file) {
                                $stmt = $conn->prepare("UPDATE profiles SET number=?, name=?, position=?, team=?, height=?, born=?, profile_picture=? WHERE name=?");
                                $stmt->bind_param("isssssss", $number, $name, $position, $team, $height, $born, $target_file, $original_name);
                            } else {
                                $stmt = $conn->prepare("UPDATE profiles SET number=?, name=?, position=?, team=?, height=?, born=? WHERE name=?");
                                $stmt->bind_param("issssss", $number, $name, $position, $team, $height, $born, $original_name);
                            }
                            $stmt->execute();
                        }
                    }
                }

                // Fetch profiles
                $sql = "SELECT * FROM profiles";
                $result = $conn->query($sql);
                ?>

                <div class="container mt-5">
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="text-center mb-4">Player Profiles</h2>
                            <div class="table-responsive">
                                <table class="table table-dark table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Number</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Team</th>
                                            <th>Height</th>
                                            <th>Born</th>
                                            <th>Profile Picture</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>
                                                <td>{$row['number']}</td>
                                                <td>{$row['name']}</td>
                                                <td>{$row['position']}</td>
                                                <td>{$row['team']}</td>
                                                <td>{$row['height']}</td>
                                                <td>{$row['born']}</td>
                                                <td><img src='{$row['profile_picture']}' alt='Profile Picture' class='profile-picture'></td>
                                                <td>
                                                    <button class='btn btn-warning btn-sm' onclick='editProfile(" . json_encode($row) . ")'>Edit</button>
                                                    <button class='btn btn-danger btn-sm' onclick='deleteProfile(\"{$row['name']}\")'>Delete</button>
                                                </td>
                                            </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>No profiles found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modals for add/edit/delete profile -->
                <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="profileModalLabel">Add/Edit Profile</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="profileForm" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="number">Number</label>
                                        <input type="number" class="form-control" id="number" name="number" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="position">Position</label>
                                        <input type="text" class="form-control" id="position" name="position" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="team">Team</label>
                                        <input type="text" class="form-control" id="team" name="team" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="height">Height</label>
                                        <input type="text" class="form-control" id="height" name="height" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="born">Born</label>
                                        <input type="date" class="form-control" id="born" name="born" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="profile_picture">Profile Picture</label>
                                        <input type="file" class="form-control-file" id="profile_picture" name="profile_picture">
                                    </div>
                                    <input type="hidden" id="original_name" name="original_name">
                                    <input type="hidden" id="action" name="action">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for delete confirmation -->
                <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel" style="color: #121212;">Delete Profile</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" style="color: #121212;">
                                Are you sure you want to delete this profile?
                            </div>
                            <div class="modal-footer">
                                <form id="deleteForm" method="POST">
                                    <input type="hidden" id="deleteName" name="name">
                                    <input type="hidden" name="action" value="Delete">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary my-4" data-toggle="modal" data-target="#profileModal" onclick="prepareAddProfile()">Add Profile</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function prepareAddProfile() {
            document.getElementById('profileForm').reset();
            document.getElementById('profileModalLabel').innerText = 'Add Profile';
            document.getElementById('action').value = 'Add';
            document.getElementById('original_name').value = '';
        }

        function editProfile(profile) {
            document.getElementById('profileModalLabel').innerText = 'Edit Profile';
            document.getElementById('name').value = profile.name;
            document.getElementById('number').value = profile.number;
            document.getElementById('position').value = profile.position;
            document.getElementById('team').value = profile.team;
            document.getElementById('height').value = profile.height;
            document.getElementById('born').value = profile.born;
            document.getElementById('original_name').value = profile.name;
            document.getElementById('action').value = 'Update';
            $('#profileModal').modal('show');
        }

        function deleteProfile(name) {
            document.getElementById('deleteName').value = name;
            $('#deleteModal').modal('show');
        }
    </script>
</body>

</html>
