<?php
require 'db-connect.php';

$team = $_GET['team'];

if (empty($team)) {
    error_log("No team specified");
    echo json_encode(["error" => "No team specified"]);
    exit;
}

$sql = "SELECT name, number FROM profiles WHERE team = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
    exit;
}

$stmt->bind_param('s', $team);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();
$players = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $players[] = $row;
    }
} else {
    error_log("No players found for team: " . $team);
    $players = [];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($players);
?>
