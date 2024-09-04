<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $description, $event_date, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
