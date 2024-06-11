<?php
require_once('db-connect.php');

$response = ['status' => 0, 'message' => ''];

if(isset($_POST['title']) && isset($_POST['description']) && isset($_POST['start_datetime']) && isset($_POST['end_datetime'])){
    $id = isset($_POST['id']) ? $conn->real_escape_string($_POST['id']) : '';
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $start_datetime = $conn->real_escape_string($_POST['start_datetime']);
    $end_datetime = $conn->real_escape_string($_POST['end_datetime']);

    if(empty($id)){
        // Insert new schedule
        $insert = $conn->query("INSERT INTO `schedule_list` (`title`, `description`, `start_datetime`, `end_datetime`) VALUES ('$title', '$description', '$start_datetime', '$end_datetime')");
        if($insert){
            $response['status'] = 1;
            $response['message'] = 'Schedule added successfully.';
        } else {
            $response['message'] = 'Failed to add schedule.';
        }
    } else {
        // Update existing schedule
        $update = $conn->query("UPDATE `schedule_list` SET `title` = '$title', `description` = '$description', `start_datetime` = '$start_datetime', `end_datetime` = '$end_datetime' WHERE `id` = '$id'");
        if($update){
            $response['status'] = 1;
            $response['message'] = 'Schedule updated successfully.';
        } else {
            $response['message'] = 'Failed to update schedule.';
        }
    }
}

echo json_encode($response);
?>
