<?php
session_start();
include 'db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $position = $_POST['position'];
    $team = $_POST['team'];
    $phone = $_POST['phone'];
    $profile_picture = $_FILES['profile_picture']['name'];

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, age = ?, position = ?, team = ?, phone = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param('ssssssssi', $firstname, $lastname, $email, $age, $position, $team, $phone, $profile_picture, $user_id);
    $stmt->execute();
    $stmt->close();

    // Upload profile picture
    if ($profile_picture) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
    }
}

$stmt = $conn->prepare("SELECT firstname, lastname, username, email, age, position, team, phone, profile_picture FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($firstname, $lastname, $username, $email, $age, $position, $team, $phone, $profile_picture);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #121212;
        color: #ffffff;
        font-family: 'Roboto', Arial, sans-serif;
    }

    .container {
        margin-top: 50px;
    }

    .card {
        background-color: #333333;
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .card-body {
        color: #ffffff;
    }

    .card-title {
        font-size: 1.5rem;
        color: #f57c00;
    }

    .form-control {
        background-color: #444444;
        color: #ffffff;
        border: none;
        border-radius: 5px;
        padding: 10px;
    }

    .form-control:focus {
        background-color: #555555;
        color: #ffffff;
        border-color: #f57c00;
    }

    .btn-primary {
        background-color: #f57c00;
        border: none;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #ff8c00;
    }

    .profile-picture {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #f57c00;
        margin-bottom: 15px;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .profile-header h1 {
        margin: 0;
    }

    .navbar-nav .nav-link {
        color: #ffffff;
    }

    .navbar-nav .nav-link:hover {
        color: #f57c00;
    }

    .header-banner {
        background: url('images/basketball-banner.jpg') no-repeat center center;
        background-size: cover;
        height: 300px;
        color: #f57c00;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        margin-top: -80px;
        margin-bottom: -100px;
    }

    .navbar {
        margin-bottom: 20px;
        background-color: #1c1e21;
        border-bottom: 3px solid #f57c00;
    }

    .navbar-brand {
        font-weight: bold;
        color: #f57c00 !important;
        text-decoration: none;
        white-space: nowrap;
        letter-spacing: 1px;
    }

    .navbar-nav .nav-link {
        color: #ffffff !important;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
        color: #f57c00 !important;
    }

    .navbar-toggler {
        border: none;
        color: #f57c00;
    }

    .dropdown-item:hover {
        background-color: #f57c00;
    }
    </style>
</head>
<body>

<!-- Navbar -->
<header>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a href="#" class="navbar-brand">Ballers Hub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon">&#9776;</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Menu
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="profile.php">Players</a>
                            <a class="dropdown-item" href="Gameresult.php">Games</a>
                            <a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a>
                            <a class="dropdown-item" href="AboutUs.php">The Team</a>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">Logout</a>
                    </li>
                    <li class="nav-item">
                        <a href="user-profile.php" class="nav-link" style="font-weight: bolder;"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link">Login</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="profile-header">
                <?php if ($profile_picture): ?>
                <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" class="profile-picture" alt="Profile Picture">
                <?php else: ?>
                <img src="images/default-profile.png" class="profile-picture" alt="Default Profile Picture">
                <?php endif; ?>
                <h1 class="card-title"><?php echo htmlspecialchars($username); ?></h1>
            </div>
            <div id="profile-details">
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($firstname); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($lastname); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
                <p><strong>Position:</strong> <?php echo htmlspecialchars($position); ?></p>
                <p><strong>Team:</strong> <?php echo htmlspecialchars($team); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
            </div>
            <div id="profile-edit" style="display: none;">
                <form action="user-profile.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="firstname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastname" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($position); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="team" class="form-label">Team</label>
                        <input type="text" class="form-control" id="team" name="team" value="<?php echo htmlspecialchars($team); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                    </div>
                    <button type="submit" class="btn btn-primary" name="save_changes">Save Changes</button>
                </form>
            </div>
            <button class="btn btn-primary mt-3" id="edit-profile-btn">Edit Profile</button>
            <button class="btn btn-secondary mt-3" id="cancel-edit-btn" style="display: none;">Cancel</button>
        </div>
    </div>
</div>

<script>
document.getElementById('edit-profile-btn').addEventListener('click', function() {
    document.getElementById('profile-details').style.display = 'none';
    document.getElementById('profile-edit').style.display = 'block';
    document.getElementById('edit-profile-btn').style.display = 'none';
    document.getElementById('cancel-edit-btn').style.display = 'block';
});

document.getElementById('cancel-edit-btn').addEventListener('click', function() {
    document.getElementById('profile-details').style.display = 'block';
    document.getElementById('profile-edit').style.display = 'none';
    document.getElementById('edit-profile-btn').style.display = 'block';
    document.getElementById('cancel-edit-btn').style.display = 'none';
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
