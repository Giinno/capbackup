<?php
require_once('db-connect.php');

// Check if required fields are set and valid
$title = isset($_POST['title']) ? $_POST['title'] : null;
$description = isset($_POST['game_mode']) ? $_POST['game_mode'] : null;  // Use game_mode as description
$start_datetime = isset($_POST['start_datetime']) ? $_POST['start_datetime'] : null;
$end_datetime = isset($_POST['end_datetime']) ? $_POST['end_datetime'] : null;
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

// Check if any required fields are missing
if (!$title || !$description || !$start_datetime || !$end_datetime || !$user_id) {
    echo json_encode(["error" => "All fields are required."]);
    exit;
}

// Insert data into the schedule_list table
$status = 'pending'; // Set the initial status to 'pending'
$sql = "INSERT INTO schedule_list (user_id, title, description, start_datetime, end_datetime, status) 
        VALUES ('$user_id', '$title', '$description', '$start_datetime', '$end_datetime', '$status')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["message" => "Schedule saved successfully. Awaiting admin confirmation."]);
} else {
    echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
}

$conn->close();
?>
