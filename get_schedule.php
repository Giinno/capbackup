<?php
require_once('db-connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$scheduleId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($scheduleId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid schedule ID']);
    exit;
}

$stmt = $conn->prepare("SELECT id, title, description, start_datetime, end_datetime, status, amount_paid, event_type, receipt_number FROM schedule_list WHERE id = ?");
$stmt->bind_param("i", $scheduleId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No schedule found with the given ID']);
}

$stmt->close();
$conn->close();
