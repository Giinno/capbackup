<?php
include 'db-connect.php';

// Function to get the latest booking with user information
function getLatestBooking($conn) {
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
        
        return $booking;
    }
    
    return null;
}

// Check for new bookings
$latestBooking = getLatestBooking($conn);

// Return the result as JSON
header('Content-Type: application/json');
echo json_encode($latestBooking);

$conn->close();
?>
