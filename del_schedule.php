<?php
require_once('db-connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM `schedule_list` WHERE `id` = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo 1;
    } else {
        echo 0;
    }

    $stmt->close();
    $conn->close();
}
?>
