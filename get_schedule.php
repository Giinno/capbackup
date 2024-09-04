<?php
session_start();
require_once('db-connect.php');

// Check if the user is logged in and is a Scheduling-admin
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'scheduling-admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit;
}

$schedule_id = $_GET['id'] ?? 0;

if (!$schedule_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$query = $conn->prepare("SELECT id, title, description, start_datetime, end_datetime FROM `schedule_list` WHERE id = ?");
$query->bind_param("i", $schedule_id);

if ($query->execute()) {
    $result = $query->get_result();
    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
        // Format the datetime values for the form inputs
        $schedule['start_datetime'] = date("Y-m-d\TH:i:s", strtotime($schedule['start_datetime']));
        $schedule['end_datetime'] = date("Y-m-d\TH:i:s", strtotime($schedule['end_datetime']));
        echo json_encode($schedule);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Schedule not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve schedule: ' . $conn->error]);
}

$conn->close();
