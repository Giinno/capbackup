<?php
include 'db-connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
                echo "Error uploading team logo.";
                exit();
            }
        }

        $names = $_POST['name'];
        $numbers = $_POST['number'];
        $positions = $_POST['position'];
        $heights = $_POST['height'];
        $borns = $_POST['born'];
        $profile_pictures = $_FILES['profile_picture'];

        $conn->begin_transaction();

        try {
            // Insert into teams table
            $stmt_team = $conn->prepare("INSERT INTO teams (team_name, team_logo) VALUES (?, ?)");
            $stmt_team->bind_param("ss", $team, $team_logo);
            $stmt_team->execute();
            $stmt_team->close();

            // Insert into profiles table
            $stmt_profiles = $conn->prepare("INSERT INTO profiles (team, name, number, position, height, born, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");

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
                    $stmt_profiles->bind_param("ssissss", $team, $name, $number, $position, $height, $born, $profile_picture);
                    $stmt_profiles->execute();
                } else {
                    throw new Exception("Player data is incomplete.");
                }
            }

            $conn->commit();
            echo "Team and players created successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }

        if ($stmt_profiles) {
            $stmt_profiles->close();
        }
    } else {
        echo "Error: Player data arrays are not set or empty.";
    }

    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
