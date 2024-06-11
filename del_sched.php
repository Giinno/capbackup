<?php
require_once('db-connect.php');

if(isset($_POST['title'])){
    $title = $conn->real_escape_string($_POST['title']);

    // Delete the schedule based on the title
    $delete = $conn->query("DELETE FROM `schedule_list` WHERE `title` = '$title'");

    if($delete){
        echo 1;
    } else {
        echo 0;
    }
}
?>
