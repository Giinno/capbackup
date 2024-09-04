<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'update') {
        $player_id = $_POST['player_id'];
        $name = $_POST['name'];
        $number = $_POST['number'];
        $points = $_POST['points'];
        $assists = $_POST['assists'];
        $reb_def = $_POST['reb_def'];
        $reb_off = $_POST['reb_off'];
        $steals = $_POST['steals'];
        $blocks = $_POST['blocks'];
        $turnovers = $_POST['turnovers'];
        $fouls = $_POST['fouls'];
        $pt2_attempted = $_POST['2pt_attempted'];
        $pt2_made = $_POST['2pt_made'];
        $pt3_attempted = $_POST['3pt_attempted'];
        $pt3_made = $_POST['3pt_made'];
        $ft_attempted = $_POST['ft_attempted'];
        $ft_made = $_POST['ft_made'];

        // Handle profile picture upload
        $profile_picture = "";
        if (!empty($_FILES['profile_picture']['name'])) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $target_file;
        }

        if ($profile_picture != "") {
            $sql = "UPDATE statistics SET name='$name', number='$number', points='$points', assists='$assists', reb_def='$reb_def', reb_off='$reb_off', steals='$steals', blocks='$blocks', turnovers='$turnovers', fouls='$fouls', 2pt_attempted='$pt2_attempted', 2pt_made='$pt2_made', 3pt_attempted='$pt3_attempted', 3pt_made='$pt3_made', ft_attempted='$ft_attempted', ft_made='$ft_made', profile_picture='$profile_picture' WHERE id='$player_id'";
        } else {
            $sql = "UPDATE statistics SET name='$name', number='$number', points='$points', assists='$assists', reb_def='$reb_def', reb_off='$reb_off', steals='$steals', blocks='$blocks', turnovers='$turnovers', fouls='$fouls', 2pt_attempted='$pt2_attempted', 2pt_made='$pt2_made', 3pt_attempted='$pt3_attempted', 3pt_made='$pt3_made', ft_attempted='$ft_attempted', ft_made='$ft_made' WHERE id='$player_id'";
        }

        if ($conn->query($sql) === TRUE) {
            echo "Player updated successfully";
        } else {
            echo "Error updating player: " . $conn->error;
        }
    } elseif ($action == 'delete') {
        $player_id = $_POST['player_id'];

        $sql = "DELETE FROM statistics WHERE id='$player_id'";

        if ($conn->query($sql) === TRUE) {
            echo "Player deleted successfully";
        } else {
            echo "Error deleting player: " . $conn->error;
        }
    }

    $conn->close();
}
?>
