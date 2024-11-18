<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

require_once 'db-connect.php';

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

try {
    if (!isset($_GET['league_name']) || empty($_GET['league_name'])) {
        http_response_code(400);
        throw new Exception('League name is required');
    }

    $league_name = $_GET['league_name'];
    
    // Modified query to ensure we get all necessary team data
    $stmt = $conn->prepare("SELECT DISTINCT team_name FROM teams WHERE league_name = ? ORDER BY team_name");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $league_name);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $teams = [];
    
    while ($row = $result->fetch_assoc()) {
        $teams[] = [
            'team_name' => $row['team_name']
        ];
    }
    
    $stmt->close();
    
    if (empty($teams)) {
        echo json_encode(['teams' => [], 'message' => 'No teams found for this league']);
    } else {
        echo json_encode(['teams' => $teams, 'status' => 'success']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
