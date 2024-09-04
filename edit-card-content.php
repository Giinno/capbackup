<?php
session_start();
include 'db-connect.php';

$id = 1; // Assuming there is only one content to manage, you can change this logic if there are multiple entries.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $image_url = $_POST['image_url'];

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
            echo "Failed to upload image.";
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

    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit;
}

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

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Card Content</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
        }
        .form-label, .form-control {
            color: #ffffff;
        }
        .form-control {
            background-color: #333333;
            border-color: #444444;
        }
        .form-control:focus {
            color: #ffffff;
            background-color: #333333;
            border-color: #F57C00;
            box-shadow: 0 0 0 0.25rem rgba(245, 124, 0, 0.25);
        }
        .btn-primary {
            background-color: #F57C00;
            border-color: #F57C00;
        }
        .btn-primary:hover {
            background-color: #e66900;
            border-color: #e66900;
        }
        .container {
            margin-top: 50px;
        }
        .sidebar {
            height: 100%;
            width: 200px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }

        .sidebar a:hover {
            background-color: #575d63;
        }

        .content {
            margin-left: 210px;
            padding: 20px;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #f56C00;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }
        .sidebar a {
            font-size: 18px;
            text-decoration: none;
            color: #222222;
            padding: 15px 20px;
            text-align: center;
            width: 80%;
            margin: 10px 0;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            background-color: #ffffff;
            color: black;
            font-weight: bold;
        }

        .sidebar a:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .logout-button {
            margin-top: auto;
            padding: 10px 20px;
            background-color: #222222;
            color: #f56C00;
            border: none;
            cursor: pointer;
            font-size: 18px;
            text-align: center;
            width: 80%;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .logout-button:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
        .container{
            margin-left: 270px;
        }
        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <p class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="profile-cms.php">Profile Settings</a>
        <a href="stats-cms.php">Statistics Settings</a>
        <a href="gamresult.php">Game Results</a>
        <a href="CreateTeam.php">Create Team</a>
        <a href="edit-card-content.php">Dashboard Showcase</a>
        <a href="viewteams.php">View Teams</a>
        <a href="Feedback.php">Feedback</a>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
<div class="container">

    <h1>Edit Card Content</h1>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>
        <div class="mb-3">
            <label for="body" class="form-label">Body</label>
            <textarea class="form-control" id="body" name="body" rows="10" required><?php echo htmlspecialchars($body); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image_url" class="form-label">Current Image URL</label>
            <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Upload New Image</label>
            <input type="file" class="form-control" id="image" name="image">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
