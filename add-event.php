<?php
// Assuming connection to the database is established
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $league_name = $_POST['league_name']; // New field
    $created_at = date('Y-m-d H:i:s');

    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    // Upload the image
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $query = "INSERT INTO events (title, description, event_date, league_name, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $title, $description, $event_date, $league_name, $target_file, $created_at);

        if ($stmt->execute()) {
            $status = 'success';
        } else {
            $status = 'error';
        }

        $stmt->close();
    } else {
        $status = 'upload_error';
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./css/add-event.css">
    <style>
        /* Your existing styles here */
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-brand">Ballers Hub</div>
            <nav>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="admin-dashboard.php" class="sidebar-nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="manage-events.php" class="sidebar-nav-link">
                            <i class="fas fa-calendar-alt"></i> Manage Events
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="add-event.php" class="sidebar-nav-link">
                            <i class="fas fa-plus-circle"></i> Add Event
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="edit-card-content.php" class="sidebar-nav-link">
                            <i class="fas fa-edit"></i> Dashboard Showcase
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="Feedback.php" class="sidebar-nav-link">
                            <i class="fas fa-comment"></i> Feedback
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="admin_registration.php" class="sidebar-nav-link">
                            <i class="fas fa-user-plus"></i> Admin Registration
                        </a>
                    </li>
                </ul>
            </nav>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Add Event</h1>

            <?php if (isset($status)): ?>
                <div class="status-message <?php echo $status == 'success' ? 'status-success' : 'status-error'; ?>">
                    <?php
                    switch ($status) {
                        case 'success':
                            echo 'Game event was successfully added';
                            break;
                        case 'error':
                            echo 'Failed to add event';
                            break;
                        case 'upload_error':
                            echo 'Failed to upload image';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form action="add-event.php" method="post" enctype="multipart/form-data" class="event-form">
                <div class="form-group">
                    <label for="title" class="form-label">Event Title:</label>
                    <input type="text" id="title" name="title" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="description" class="form-label">Event Description:</label>
                    <textarea id="description" name="description" required class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="event_date" class="form-label">Event Date:</label>
                    <input type="text" id="event_date" name="event_date" required class="form-input flatpickr-input">
                </div>
                
                <div class="form-group">
                    <label for="league_name" class="form-label">League Name:</label>
                    <input type="text" id="league_name" name="league_name" required class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="image" class="form-label">Event Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required class="form-input">
                </div>
                
                <button type="submit" class="form-submit">Add Event</button>
            </form>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
         document.addEventListener('DOMContentLoaded', function() {
            flatpickr("#event_date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                theme: "dark"
            });
            document.getElementById('addEventForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Adding Event',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                const formData = new FormData(this);
                fetch('add-event.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'manage-events.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.'
                    });
                });
            });
        });
        
    </script>
</body>
</html>
