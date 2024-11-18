<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON content type header
header('Content-Type: application/json');

function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

function logError($message) {
    $logFile = __DIR__ . '/debug.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, "$timestamp $message\n", FILE_APPEND);
}

try {
    logError("Script started");

    // Required database connection
    require_once 'db-connect.php';
    logError("Database connection file included");

    // Validate team parameter
    if (!isset($_GET['team']) || empty($_GET['team'])) {
        logError("No team specified");
        sendResponse(["error" => "No team specified"], 400);
    }

    $team = $_GET['team'];
    logError("Team: $team");

    // Validate database connection
    if (!isset($conn) || !($conn instanceof mysqli)) {
        logError("Invalid database connection");
        throw new Exception("Database connection failed");
    }

    // Prepare and execute query
    $sql = "SELECT first_name, last_name, number FROM users WHERE team = ?";
    logError("SQL query: $sql");

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        logError("Prepare statement failed: " . $conn->error);
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    $stmt->bind_param('s', $team);
    logError("Parameters bound");

    if (!$stmt->execute()) {
        logError("Execute failed: " . $stmt->error);
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $players = [];

    while ($row = $result->fetch_assoc()) {
        $players[] = [
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'number' => $row['number']
        ];
    }

    logError("Fetched " . count($players) . " players");

    $stmt->close();
    $conn->close();

    // Send the response
    sendResponse(["players" => $players]);
} catch (Exception $e) {
    logError("Error: " . $e->getMessage());
    sendResponse(["error" => "An unexpected error occurred: " . $e->getMessage()], 500);
}
?>
