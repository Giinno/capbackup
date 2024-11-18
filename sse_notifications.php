<?php
// File: sse_notifications.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

include 'db-connect.php';

function sendSSE($data) {
    echo "event: newBooking\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

while (true) {
    $sql = "SELECT sl.id, sl.title, sl.description, sl.start_datetime, sl.end_datetime, 
                   sl.status, sl.user_id, sl.amount_paid, sl.event_type, sl.receipt_number,
                   u.first_name, u.last_name
            FROM schedule_list sl
            JOIN users u ON sl.user_id = u.id
            WHERE sl.notification_sent_at IS NULL 
            ORDER BY sl.start_datetime DESC 
            LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Mark the booking as notified
        $updateSql = "UPDATE schedule_list SET notification_sent_at = NOW() WHERE id = " . $booking['id'];
        $conn->query($updateSql);
        
        sendSSE($booking);
    }
    
    // Wait for 5 seconds before checking again
    sleep(5);
}

$conn->close();
?>
