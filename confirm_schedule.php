<?php
require_once('db-connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    $status = $action === 'confirm' ? 'confirmed' : 'canceled';

    $sql = "UPDATE `schedule_list` SET `status` = ? WHERE `id` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 1]);
    } else {
        echo json_encode(['status' => 0]);
    }

    $stmt->close();
    $conn->close();
}
?>
