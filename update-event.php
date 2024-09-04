<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $existing_image_url = $_POST['existing_image_url'];
    
    // Check if a new file was uploaded
    if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] == 0) {
        $image = $_FILES['image_url'];
        $image_name = $image['name'];
        $image_tmp_name = $image['tmp_name'];
        $image_size = $image['size'];
        $image_error = $image['error'];
        $image_type = $image['type'];

        // Extract file extension
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($image_ext, $allowed)) {
            if ($image_error === 0) {
                if ($image_size < 5000000) { // 5MB limit
                    $new_image_name = uniqid('', true) . '.' . $image_ext;
                    $image_destination = 'uploads/' . $new_image_name;

                    // Move the file to the upload directory
                    if (move_uploaded_file($image_tmp_name, $image_destination)) {
                        $image_url = $image_destination;
                    } else {
                        die('Failed to move uploaded file');
                    }
                } else {
                    die('File size exceeds limit');
                }
            } else {
                die('File upload error: ' . $image_error);
            }
        } else {
            die('Invalid file type');
        }
    } else {
        // No new file uploaded, use existing image URL
        $image_url = $existing_image_url;
    }

    $query = "UPDATE events SET title=?, description=?, event_date=?, image_url=? WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $title, $description, $event_date, $image_url, $id);

    if ($stmt->execute()) {
        header("Location: manage-events.php");
    } else {
        die("Error updating event: " . $stmt->error);
    }
}
?>
