<?php
require_once('db-connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $amount_paid = $_POST['amount_paid'];

    $stmt = $conn->prepare("UPDATE `schedule_list` SET `amount_paid` = ? WHERE `id` = ?");
    $stmt->bind_param('di', $amount_paid, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Payment updated successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update payment.']);
    }

    $stmt->close();
    $conn->close();
}
?>
