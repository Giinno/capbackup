<?php
session_start();
include 'db-connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $team = $_POST['team'];
    
    // Update profile picture handling
    if ($_FILES['profile_picture']['name']) {
        $profile_picture = $_FILES['profile_picture']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_picture);

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $upload_success = true;
        } else {
            $upload_error = "Error: There was an issue uploading the file.";
        }
    } else {
        $profile_picture = $_POST['existing_profile_picture'] ?? '';
    }

    $bio = $_POST['bio'];
    $number = $_POST['number'];
    $height = $_POST['height'];
    $born = $_POST['born'];

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, position = ?, team = ?, profile_picture = ?, bio = ?, number = ?, height = ?, born = ? WHERE id = ?");
    $stmt->bind_param('ssssssssssi', $first_name, $last_name, $email, $position, $team, $profile_picture, $bio, $number, $height, $born, $user_id);
    
    if ($stmt->execute()) {
        $update_success = true;
    } else {
        $update_error = "Error updating profile: " . $conn->error;
    }
    $stmt->close();
}

// Fetch logged-in user data
$stmt = $conn->prepare("SELECT first_name, last_name, email, position, team, profile_picture, bio, number, height, born FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email, $position, $team, $profile_picture, $bio, $number, $height, $born);
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
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    body {
        background: linear-gradient(135deg, #1a1c20 0%, #0c0e10 100%);
        color: #ffffff;
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
    }

    .container {
        margin-top: 2rem;
        padding: 0 1rem;
        max-width: 1200px;
    }

    .card {
        background: linear-gradient(145deg, #2a2d35 0%, #1a1c20 100%);
        border: none;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-body {
        padding: 2.5rem;
    }

    .profile-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 2.5rem;
        padding: 1rem;
        background: linear-gradient(180deg, rgba(245,124,0,0.1) 0%, rgba(0,0,0,0) 100%);
        border-radius: 15px;
    }

    .profile-picture {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #f57c00;
        box-shadow: 0 0 20px rgba(245,124,0,0.3);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
    }

    .profile-picture:hover {
        transform: scale(1.05);
    }

    .card-title {
        font-size: 2.5rem;
        color: #f57c00;
        margin-bottom: 0.5rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }

    #profile-details {
        background: rgba(51, 51, 51, 0.8);
        padding: 2rem;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        color: #ffffff;
    }

    #profile-details p {
        margin-bottom: 1rem;
        padding: 0.8rem;
        border-bottom: 1px solid rgba(245,124,0,0.2);
        transition: background-color 0.3s ease;
        color: #ffffff;
        background: rgba(51, 51, 51, 0.5);
    }

    #profile-details strong {
        color: #f57c00;
        margin-right: 1rem;
        font-weight: 600;
        display: inline-block;
        min-width: 100px;
    }
    .form-control {
        background-color: rgba(68, 68, 68, 0.4);
        color: #ffffff;
        border: 1px solid rgba(245,124,0,0.2);
        border-radius: 10px;
        padding: 12px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        color: #ffffff;
        background-color: rgba(68, 68, 68, 0.6);
        border-color: #f57c00;
        box-shadow: 0 0 0 2px rgba(245,124,0,0.2);
    }

    .form-label {
        color: #f57c00;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(45deg, #f57c00 0%, #ff9800 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(245,124,0,0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(45deg, #ff9800 0%, #f57c00 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245,124,0,0.4);
    }

    .btn-secondary {
        background: linear-gradient(45deg, #444 0%, #333 100%);
        border: none;
        color: #fff;
    }

    .btn-secondary:hover {
        background: linear-gradient(45deg, #333 0%, #444 100%);
        color: #f57c00;
    }

    @media (max-width: 768px) {
        .container {
            padding: 0 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
        }

        .card-title {
            font-size: 2rem;
        }
    }
    .profile-info {
        color: #ffffff;
        font-size: 1.1rem;
        margin-left: 10px;
    }

    .navbar-brand, .nav-link { 
        color: #f57c00 !important; 
    }

    .dropdown-menu {
        background: #2a2d35;
    }

    .dropdown-item {
        color: #ffffff;
    }

    .dropdown-item:hover {
        background: #f57c00;
        color: #ffffff;
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
                        <a href="user-profile.php" class="nav-link" style="font-weight: bolder;"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="nav-link">Login</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Back</a>
                    </li>
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
                <img src="<?php echo htmlspecialchars($profile_picture); ?>" class="profile-picture" alt="Profile Picture">
                <?php else: ?>
                <img src="images/default-profile.png" class="profile-picture" alt="Default Profile Picture">
                <?php endif; ?>
                <h1 class="card-title"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></h1>
            </div>
            <div id="profile-details">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Position:</strong> <?php echo htmlspecialchars($position); ?></p>
                <p><strong>Team:</strong> <?php echo htmlspecialchars($team); ?></p>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($bio); ?></p>
                <p><strong>Number:</strong> <?php echo htmlspecialchars($number); ?></p>
                <p><strong>Height:</strong> <?php echo htmlspecialchars($height); ?></p>
                <p><strong>Born:</strong> <?php echo htmlspecialchars($born); ?></p>
            </div>
            <div id="profile-edit" style="display: none;">
                <form action="user-profile.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Position</label>
                        <select class="form-select" id="position" name="position" required>
                            <option value="Point Guard" <?php echo ($position == 'Point Guard') ? 'selected' : ''; ?>>Point Guard</option>
                            <option value="Shooting Guard" <?php echo ($position == 'Shooting Guard') ? 'selected' : ''; ?>>Shooting Guard</option>
                            <option value="Small Forward" <?php echo ($position == 'Small Forward') ? 'selected' : ''; ?>>Small Forward</option>
                            <option value="Power Forward" <?php echo ($position == 'Power Forward') ? 'selected' : ''; ?>>Power Forward</option>
                            <option value="Center" <?php echo ($position == 'Center') ? 'selected' : ''; ?>>Center</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="team" class="form-label">Team</label>
                        <input type="text" class="form-control" id="team" name="team" value="<?php echo htmlspecialchars($team); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" required><?php echo htmlspecialchars($bio); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="number" class="form-label">Number</label>
                        <input type="text" class="form-control" id="number" name="number" value="<?php echo htmlspecialchars($number); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="height" class="form-label">Height</label>
                        <select class="form-select" id="height" name="height" required>
                            <?php
                            for ($feet = 4; $feet <= 8; $feet++) {
                                for ($inches = 0; $inches < 12; $inches++) {
                                    $height_option = $feet . "'" . $inches . '"';
                                    echo "<option value=\"$height_option\"" . ($height == $height_option ? ' selected' : '') . ">$height_option</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="born" class="form-label">Born</label>
                        <input type="date" class="form-control" id="born" name="born" value="<?php echo htmlspecialchars($born); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                        <?php if ($profile_picture): ?>
                            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Current Profile Picture" class="mt-2" style="max-width: 100px; max-height: 100px;">
                            <input type="hidden" name="existing_profile_picture" value="<?php echo htmlspecialchars($profile_picture); ?>">
                        <?php endif; ?>
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

document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($update_success) && $update_success): ?>
        Swal.fire({
            title: 'Success!',
            text: 'Your profile has been updated successfully.',
            icon: 'success',
            confirmButtonColor: '#f57c00'
        });
    <?php endif; ?>

    <?php if (isset($update_error)): ?>
        Swal.fire({
            title: 'Error!',
            text: <?php echo json_encode($update_error); ?>,
            icon: 'error',
            confirmButtonColor: '#f57c00'
        });
    <?php endif; ?>

    <?php if (isset($upload_success) && $upload_success): ?>
        Swal.fire({
            title: 'Success!',
            text: 'Your profile picture has been uploaded successfully.',
            icon: 'success',
            confirmButtonColor: '#f57c00'
        });
    <?php endif; ?>

    <?php if (isset($upload_error)): ?>
        Swal.fire({
            title: 'Error!',
            text: <?php echo json_encode($upload_error); ?>,
            icon: 'error',
            confirmButtonColor: '#f57c00'
        });
    <?php endif; ?>
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
