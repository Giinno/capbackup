<?php
session_start();
require_once('db-connect.php');

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize response array
$response = ['status' => 'error', 'message' => 'An error occurred'];

// Get posted data
$user_id = $_POST['user_id'] ?? null;
$title = $_POST['title'] ?? null;
$description = $_POST['game_mode'] ?? null;
$start_datetime = $_POST['start_datetime'] ?? null;
$end_datetime = $_POST['end_datetime'] ?? null;

// Log received data
error_log("Received data: " . print_r($_POST, true));

// Validate required fields
if (!$user_id || !$title || !$description || !$start_datetime || !$end_datetime) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit();
}

// Convert to timestamps for validation
$start_timestamp = strtotime($start_datetime);
$end_timestamp = strtotime($end_datetime);

// Ensure end time is at least 1 hour after start time
if ($end_timestamp <= $start_timestamp || ($end_timestamp - $start_timestamp) < 3600) {
    $response['message'] = 'End time must be at least 1 hour after start time.';
    echo json_encode($response);
    exit();
}

// Check for overlapping reservations with stricter validation
$sql = "SELECT * FROM `schedule_list` WHERE status IN ('confirmed', 'pending') 
        AND ((? < end_datetime AND ? > start_datetime) 
        OR (? < end_datetime AND ? > start_datetime) 
        OR (? >= start_datetime AND ? <= end_datetime))";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = 'Failed to prepare statement: ' . $conn->error;
    echo json_encode($response);
    exit();
}

$stmt->bind_param('ssssss', $start_datetime, $start_datetime, $end_datetime, $end_datetime, $start_datetime, $end_datetime);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response['message'] = 'The selected time slot overlaps with an existing reservation. Please choose a different time.';
    echo json_encode($response);
    exit();
}

// Insert the reservation into the database with 'pending' status
$sql = "INSERT INTO `schedule_list` (user_id, title, description, start_datetime, end_datetime, status) VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = 'Failed to prepare insert statement: ' . $conn->error;
    echo json_encode($response);
    exit();
}

$stmt->bind_param('issss', $user_id, $title, $description, $start_datetime, $end_datetime);

if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Schedule saved successfully! Your reservation is pending admin approval.';
} else {
    $response['message'] = 'Failed to save schedule: ' . $stmt->error;
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>
