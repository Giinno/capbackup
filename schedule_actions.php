<?php
session_start();
require_once('db-connect.php');

// Check if the user is logged in and is a Scheduling-admin
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'scheduling-admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized action.']);
    exit;
}

$action = $_POST['action'] ?? '';
$schedule_id = $_POST['id'] ?? 0;

// Log received data for debugging
error_log("Action: $action, Schedule ID: $schedule_id");

if (!$schedule_id || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

switch ($action) {
    case 'delete':
        $query = $conn->prepare("DELETE FROM `schedule_list` WHERE id = ?");
        $query->bind_param("i", $schedule_id);
        if ($query->execute()) {
            $response = ['status' => 'success', 'message' => 'Schedule deleted successfully.'];
        } else {
            $response['message'] = 'Failed to delete schedule: ' . $conn->error;
        }
        break;

    case 'confirm':
        $query = $conn->prepare("UPDATE `schedule_list` SET status = 'confirmed' WHERE id = ?");
        $query->bind_param("i", $schedule_id);
        if ($query->execute()) {
            $response = ['status' => 'success', 'message' => 'Schedule confirmed successfully.'];
        } else {
            $response['message'] = 'Failed to confirm schedule: ' . $conn->error;
        }
        break;

    case 'cancel':
        $query = $conn->prepare("UPDATE `schedule_list` SET status = 'cancelled' WHERE id = ?");
        $query->bind_param("i", $schedule_id);
        if ($query->execute()) {
            $response = ['status' => 'success', 'message' => 'Schedule cancelled successfully.'];
        } else {
            $response['message'] = 'Failed to cancel schedule: ' . $conn->error;
        }
        break;

    default:
        $response['message'] = 'Invalid action.';
        break;
}

echo json_encode($response);
$conn->close();
