<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profile Management</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            margin: 10px;
        }
        .form-group label {
            font-weight: bold;
        }
        .profile-container {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            padding: 15px;
            max-width: 1000px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
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
        .btn-primary, .btn-secondary, .btn-warning, .btn-danger {
            border-radius: 20px;
        }
        .btn-primary, .btn-warning, .btn-danger {
            color: #ffffff;
        }
    </style>
</head>
<body>
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

    // Fetch all profiles for display
    $sql = "SELECT * FROM profiles";
    $result = $conn->query($sql);
    ?>

    <!-- Add Player Form -->
    <div class="text-right mb-3">
        <button type="button" class="btn btn-primary" onclick="showAddPlayerForm()">Add Player</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='CreateTeam.php'">Add Team</button>
    </div>
    <div id="addPlayerForm" class="mb-3" style="display: none;">
        <form action="profile-cms.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="playerNumber">Number:</label>
                <input type="number" name="number" id="playerNumber" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="playerName">Name:</label>
                <input type="text" name="name" id="playerName" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="playerPosition">Position:</label>
                <div>
                    <input type="radio" name="position" value="Point Guard"> Point Guard <br>
                    <input type="radio" name="position" value="Shooting Guard"> Shooting Guard <br>
                    <input type="radio" name="position" value="Small Forward"> Small Forward <br>
                    <input type="radio" name="position" value="Power Forward"> Power Forward <br>
                    <input type="radio" name="position" value="Center"> Center
                </div>
            </div>
            <div class="form-group">
                <label for="playerTeam">Team:</label>
                <input type="text" name="team" id="playerTeam" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="playerHeight">Height:</label>
                <input type="text" name="height" id="playerHeight" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="playerBorn">Born:</label>
                <input type="date" name="born" id="playerBorn" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="playerProfilePicture">Profile Picture:</label>
                <input type="file" name="profile_picture" id="playerProfilePicture" class="form-control-file">
            </div>
            <div class="text-right">
                <button type="button" class="btn btn-secondary" onclick="hideAddPlayerForm()">Cancel</button>
                <button type="submit" name="action" value="Add" class="btn btn-primary">Add Player</button>
            </div>
        </form>
    </div>

    <!-- Players List -->
    <div class="list-group">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="list-group-item" id="profile-<?php echo htmlspecialchars($row['name']); ?>" style="background-color: #333333; border: none; margin-bottom: 20px; padding: 0;">
                <div class="profile-container">
                    <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($row['name']); ?></h1>
                        <h2><?php echo htmlspecialchars($row['team']); ?></h2>
                        <div class="stats">
                            <div>
                                <strong>Number</strong>
                                <p><?php echo htmlspecialchars($row['number']); ?></p>
                            </div>
                            <div>
                                <strong>Position</strong>
                                <p><?php echo htmlspecialchars($row['position']); ?></p>
                            </div>
                            <div>
                                <strong>Height</strong>
                                <p><?php echo htmlspecialchars($row['height']); ?></p>
                            </div>
                            <div>
                                <strong>Born</strong>
                                <p><?php echo htmlspecialchars($row['born']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <form action="profile-cms.php" method="post" style="display: inline;">
                            <button type="button" class="btn btn-warning" onclick="editPlayer('<?php echo htmlspecialchars($row['name']); ?>')">Edit</button>
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                            <button type="submit" name="action" value="Delete" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="list-group-item edit-form" id="edit-form-<?php echo htmlspecialchars($row['name']); ?>" style="display: none; background-color: #333333; border: none;">
                <form action="profile-cms.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="original_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                    <div class="form-group">
                        <label for="editPlayerNumber-<?php echo htmlspecialchars($row['name']); ?>">Number:</label>
                        <input type="number" name="number" id="editPlayerNumber-<?php echo htmlspecialchars($row['name']); ?>" class="form-control" value="<?php echo htmlspecialchars($row['number']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="editPlayerName-<?php echo htmlspecialchars($row['name']); ?>">Name:</label>
                        <input type="text" name="name" id="editPlayerName-<?php echo htmlspecialchars($row['name']); ?>" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="editPlayerPosition-<?php echo htmlspecialchars($row['name']); ?>">Position:</label>
                        <div>
                            <input type="radio" name="position" value="Point Guard" <?php if($row['position'] == 'Point Guard') echo 'checked'; ?>> Point Guard <br>
                            <input type="radio" name="position" value="Shooting Guard" <?php if($row['position'] == 'Shooting Guard') echo 'checked'; ?>> Shooting Guard <br>
                            <input type="radio" name="position" value="Small Forward" <?php if($row['position'] == 'Small Forward') echo 'checked'; ?>> Small Forward <br>
                            <input type="radio" name="position" value="Power Forward" <?php if($row['position'] == 'Power Forward') echo 'checked'; ?>> Power Forward <br>
                            <input type="radio" name="position" value="Center" <?php if($row['position'] == 'Center') echo 'checked'; ?>> Center
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editPlayerTeam-<?php echo htmlspecialchars($row['name']); ?>">Team:</label>
                        <input type="text" name="team" id="editPlayerTeam-<?php echo htmlspecialchars($row['name']); ?>" class="form-control" value="<?php echo htmlspecialchars($row['team']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="editPlayerHeight-<?php echo htmlspecialchars($row['name']); ?>">Height:</label>
                        <input type="text" name="height" id="editPlayerHeight-<?php echo htmlspecialchars($row['name']); ?>" class="form-control" value="<?php echo htmlspecialchars($row['height']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="editPlayerBorn-<?php echo htmlspecialchars($row['name']); ?>">Born:</label>
                        <input type="date" name="born" id="editPlayerBorn-<?php echo htmlspecialchars($row['name']); ?>" class="form-control" value="<?php echo htmlspecialchars($row['born']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="editPlayerProfilePicture-<?php echo htmlspecialchars($row['name']); ?>">Profile Picture:</label>
                        <input type="file" name="profile_picture" id="editPlayerProfilePicture-<?php echo htmlspecialchars($row['name']); ?>" class="form-control-file">
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit('<?php echo htmlspecialchars($row['name']); ?>')">Cancel</button>
                        <button type="submit" name="action" value="Update" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function showAddPlayerForm() {
    $('#addPlayerForm').show();
}

function hideAddPlayerForm() {
    $('#addPlayerForm').hide();
}

function editPlayer(name) {
    $('#profile-' + CSS.escape(name)).hide();
    $('#edit-form-' + CSS.escape(name)).show();
}

function cancelEdit(name) {
    $('#edit-form-' + CSS.escape(name)).hide();
    $('#profile-' + CSS.escape(name)).show();
}
</script>
</body>
</html>
