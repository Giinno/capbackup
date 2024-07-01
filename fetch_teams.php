<?php
require 'db-connect.php';

// Fetch unique teams
$sql = "SELECT DISTINCT team FROM profiles";
$result = $conn->query($sql);

if (!$result) {
    error_log("Query failed: " . $conn->error);
    echo json_encode(["error" => "Query failed: " . $conn->error]);
    exit;
}

$teams = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row['team'];
    }
} else {
    $teams = [];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($teams);
?>
