<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db-connect.php';

    // Response array
    $response = ['status' => '', 'message' => '', 'existing_players' => []];

    if (isset($_POST['name']) && isset($_POST['number']) && !empty($_POST['name']) && !empty($_POST['number'])) {
        $team = $conn->real_escape_string($_POST['team']);

        // Handle team logo upload
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

        $names = $_POST['name'];
        $numbers = $_POST['number'];
        $positions = $_POST['position'];
        $heights = $_POST['height'];
        $borns = $_POST['born'];
        $profile_pictures = $_FILES['profile_picture'];

        // Check for existing players by name
        $existing_players = [];
        foreach ($names as $name) {
            $name = $conn->real_escape_string($name);
            $result = $conn->query("SELECT id FROM profiles WHERE name='$name'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $existing_players[$name] = $row['id']; // Store player ID
            }
        }

        if (!empty($existing_players)) {
            // Players exist, prompt user to update their teams
            $response['status'] = 'exists';
            $response['message'] = 'Some players already exist in the database.';
            $response['existing_players'] = $existing_players;
            echo json_encode($response);
            exit();
        }

        $conn->begin_transaction();

        try {
            // Insert into teams table
            $stmt_team = $conn->prepare("INSERT INTO teams (team_name, team_logo) VALUES (?, ?)");
            $stmt_team->bind_param("ss", $team, $team_logo);
            $stmt_team->execute();
            $stmt_team->close();

            // Insert or update profiles table
            $stmt_profiles_insert = $conn->prepare("INSERT INTO profiles (team, name, number, position, height, born, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_profiles_update = $conn->prepare("UPDATE profiles SET team = ?, number = ?, position = ?, height = ?, born = ?, profile_picture = ? WHERE id = ?");

            for ($i = 0; $i < count($names); $i++) {
                $name = isset($names[$i]) ? $conn->real_escape_string($names[$i]) : null;
                $number = isset($numbers[$i]) ? $conn->real_escape_string($numbers[$i]) : null;
                $position = isset($positions[$i]) ? $conn->real_escape_string($positions[$i]) : null;
                $height = isset($heights[$i]) ? $conn->real_escape_string($heights[$i]) : null;
                $born = isset($borns[$i]) ? $conn->real_escape_string($borns[$i]) : null;

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

                if ($name && $number && $position && $height && $born) {
                    $stmt_profiles_insert->bind_param("ssissss", $team, $name, $number, $position, $height, $born, $profile_picture);
                    $stmt_profiles_insert->execute();
                } else {
                    throw new Exception("Player data is incomplete.");
                }
            }

            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = 'Team and players created successfully!';
        } catch (Exception $e) {
            $conn->rollback();
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }

        if ($stmt_profiles_insert) {
            $stmt_profiles_insert->close();
        }

        if ($stmt_profiles_update) {
            $stmt_profiles_update->close();
        }

        echo json_encode($response);
        exit();
    } elseif (isset($_POST['update_team']) && $_POST['update_team'] == "true") {
        // Handle updating the team for an existing player
        $player_ids = $_POST['player_id'];
        $team = $conn->real_escape_string($_POST['team']);

        foreach ($player_ids as $player_id) {
            $player_id = $conn->real_escape_string($player_id);
            $stmt_profiles_update = $conn->prepare("UPDATE profiles SET team = ? WHERE id = ?");
            $stmt_profiles_update->bind_param("si", $team, $player_id);
            $stmt_profiles_update->execute();
        }

        $stmt_profiles_update->close();
        $conn->close();

        $response['status'] = 'success';
        $response['message'] = 'Team updated successfully!';
        echo json_encode($response);
        exit();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid request method.';
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
    <title>Player Registration</title>
    <style>
        /* Basic modal styles */
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
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            text-align: center;
        }

        #errorModal > div > button {
            margin: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        #errorModal > div > button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Form for registering players -->
    <form id="playerForm" method="POST" enctype="multipart/form-data">
        <input type="text" name="team" placeholder="Team Name" required>
        <input type="file" name="team_logo">
        <input type="text" name="name[]" placeholder="Player Name" required>
        <input type="text" name="number[]" placeholder="Player Number" required>
        <input type="text" name="position[]" placeholder="Player Position" required>
        <input type="text" name="height[]" placeholder="Player Height" required>
        <input type="date" name="born[]" placeholder="Date of Birth" required>
        <input type="file" name="profile_picture[]">
        <button type="submit">Submit</button>
    </form>

    <!-- Error Modal -->
    <div id="errorModal">
        <div>
            <h2 id="modalMessage"></h2>
            <button id="updateTeamBtn" style="display: none;">Update Team</button>
            <button id="cancelBtn">Cancel</button>
        </div>
    </div>

    <script>
        document.getElementById('playerForm').onsubmit = function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('', { // Submitting to the same file
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                } else if (data.status === 'exists') {
                    // Show the error modal with the proper message
                    document.getElementById('modalMessage').innerText = `The following players already exist: ${Object.keys(data.existing_players).join(', ')}. Would you like to update their teams?`;
                    document.getElementById('errorModal').style.display = 'block';

                    // Ensure the update button is visible
                    document.getElementById('updateTeamBtn').style.display = 'inline-block';

                    // Handle update team button click
                    document.getElementById('updateTeamBtn').onclick = function() {
                        const updateFormData = new FormData();
                        updateFormData.append('update_team', 'true');
                        updateFormData.append('team', formData.get('team')); // Append other necessary data

                        // Add player IDs to the form data
                        for (const [name, id] of Object.entries(data.existing_players)) {
                            updateFormData.append(`player_id[]`, id);
                        }

                        fetch('', { // Submitting to the same file
                            method: 'POST',
                            body: updateFormData
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            document.getElementById('errorModal').style.display = 'none';
                        })
                        .catch(error => {
                            console.error('Error during update:', error);
                        });
                    };
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error during initial submission:', error);
                alert('An error occurred. Please try again.');
            });
        };

        // Handle cancel button
        document.getElementById('cancelBtn').onclick = function() {
            document.getElementById('errorModal').style.display = 'none';
        };
    </script>
</body>
</html>
