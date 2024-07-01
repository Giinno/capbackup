<?php
include 'db-connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO feedback (name, email, message) VALUES ('$name', '$email', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Feedback submitted successfully!');
                window.location.href = 'AboutUs.php';
              </script>";
    } else {
        echo "<script>
                alert('Error: " . $sql . "<br>" . $conn->error . "');
                window.location.href = 'AboutUs.php';
              </script>";
    }

    $conn->close();
}
?>
