<?php
include 'db-connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        echo json_encode($event);
    } else {
        echo json_encode(["error" => "Event not found"]);
    }

    $stmt->close();
}

$conn->close();
?>
