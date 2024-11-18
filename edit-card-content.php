<?php
session_start();
include 'db-connect.php';

$id = 1; // Assuming there is only one content to manage
$status = '';

// Fetch existing content if any
$title = "";
$body = "";
$image_url = "";

$stmt = $conn->prepare("SELECT title, body, image_url FROM card_content WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $body, $image_url);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $image_url = $_POST['image_url'];
    $response = array();

    // Handle the file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $imageName = basename($image['name']);
        $imagePath = 'uploads/' . $imageName;

        // Create the uploads directory if it doesn't exist
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($image['tmp_name'], $imagePath)) {
            $image_url = $imagePath;
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to upload image.";
            echo json_encode($response);
            exit;
        }
    }

    // Check if the content already exists
    $stmt = $conn->prepare("SELECT id FROM card_content WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update existing content
        $stmt = $conn->prepare("UPDATE card_content SET title = ?, body = ?, image_url = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $body, $image_url, $id);
    } else {
        // Insert new content
        $stmt = $conn->prepare("INSERT INTO card_content (title, body, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $body, $image_url);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Content updated successfully!";
    } else {
        $response['success'] = false;
        $response['message'] = "Error updating content: " . $stmt->error;
    }

    $stmt->close();
    echo json_encode($response);
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Edit Card Content - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <style>
        :root {
            --primary-color: #f56C00;
            --secondary-color: #222;
            --text-color: #ffffff;
            --bg-color: #121212;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            display: flex;
        }

        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav-item {
            margin-bottom: 1rem;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--secondary-color);
            background-color: white;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            border-radius: 5px;
            font-weight: 500;
        }

        .sidebar-nav-link:hover {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }

        .sidebar-nav-link i {
            margin-right: 0.5rem;
        }

        .logout-button {
            margin-top: auto;
            background-color: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            padding: 0.75rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            font-weight: 600;
        }

        .logout-button:hover {
            background-color: var(--text-color);
            color: var(--secondary-color);
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            margin-left: 250px;
        }

        .page-title {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .btn {
            cursor: pointer;
            padding: 0.75rem 1.5rem;
            color: var(--secondary-color);
            background-color: var(--primary-color);
            border: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s, color 0.3s;
        }

        .btn:hover {
            background-color: var(--text-color);
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: var(--secondary-color);
            color: var(--text-color);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(245, 124, 0, 0.25);
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                padding: 1rem;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        .preview-container {
            background: rgba(34, 34, 34, 0.5);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .preview-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .preview-body {
            color: var(--text-color);
            white-space: pre-wrap;
        }

        .edit-form {
            display: none;
        }

        .edit-form.active {
            display: block;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn-edit {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(245, 124, 0, 0.2);
        }
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
                            <i class="fas fa-comment"></i> Admin Registration
                        </a>
                    </li>
                </ul>
            </nav>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Dashboard Showcase Content</h1>
            
            <!-- Preview Section -->
            <div class="preview-container">
                <?php if ($image_url): ?>
                    <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Preview" class="preview-image">
                <?php endif; ?>
                <h2 class="preview-title"><?php echo htmlspecialchars($title); ?></h2>
                <div class="preview-body"><?php echo nl2br(htmlspecialchars($body)); ?></div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn-edit" onclick="toggleEditForm()">
                    <i class="fas fa-edit"></i> Edit Content
                </button>
            </div>

            <!-- Edit Form -->
            <form id="editForm" class="edit-form" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                </div>
                <div class="form-group">
                    <label for="body" class="form-label">Body</label>
                    <textarea class="form-control" id="body" name="body" rows="10" required><?php echo htmlspecialchars($body); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image_url" class="form-label">Current Image URL</label>
                    <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="image" class="form-label">Upload New Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-submit">Update Content</button>
                    <button type="button" class="btn-cancel" onclick="toggleEditForm()">Cancel</button>
                </div>
            </form>
        </main>
    </div>

    <script>
        function toggleEditForm() {
            const editForm = document.getElementById('editForm');
            editForm.classList.toggle('active');
        }

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);

            fetch('edit-card-content.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#f56C00'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#f56C00'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred',
                    icon: 'error',
                    confirmButtonColor: '#f56C00'
                });
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
