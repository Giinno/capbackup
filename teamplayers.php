<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = $_POST['team_name'];

    // Fetch players from the profiles table based on the team name
    $stmt = $conn->prepare("SELECT name, number, position, height, born, profile_picture FROM profiles WHERE team = ?");
    $stmt->bind_param("s", $team_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $result->fetch_assoc()) {
            echo "<li class='list-group-item'>";
            echo "<img src='" . $row['profile_picture'] . "' alt='Profile Picture' style='width: 30px; height: 30px; margin-right: 10px;'>" . $row['name'] . " - " . $row['position'];
            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No players found for this team.</p>";
    }

    $stmt->close();
}
$conn->close();
?>
