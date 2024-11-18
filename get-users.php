<?php
include 'db-connect.php';

if (isset($_GET['month'])) {
    $month = intval($_GET['month']);
    
    $sql = "SELECT title, description, start_datetime, end_datetime 
            FROM schedule_list 
            WHERE MONTH(start_datetime) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($schedules);
}

$conn->close();
?>
