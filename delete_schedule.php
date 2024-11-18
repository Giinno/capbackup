<?php
require_once('db-connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$scheduleId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($scheduleId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid schedule ID']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM schedule_list WHERE id = ?");
$stmt->bind_param("i", $scheduleId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Schedule deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No schedule found with the given ID']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete schedule: ' . $conn->error]);
}

$stmt->close();
$conn->close();
