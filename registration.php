<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $age = (int)$_POST['age'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = trim($_POST['role']);

    // Set position and team to null if role is coach
    $position = ($role === 'coach') ? null : trim($_POST['position']);
    $team = ($role === 'coach') ? null : trim($_POST['team']);

    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($role)) {
        echo "<script>alert('Please fill all the required fields.');</script>";
    } elseif ($role === 'player' && (empty($position) || empty($team))) {
        echo "<script>alert('Position and Team are required for players.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Username already exists! Please choose a different username.');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, age, email, position, team, phone, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssississs", $firstname, $lastname, $username, $age, $email, $position, $team, $phone, $password, $role);

            if ($stmt->execute()) {
                echo "<script>
                    alert('Registration successful!');
                    window.location.href = 'login.php';
                </script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&family=Montserrat&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Form</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #333333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
        }
        .form-control {
            background-color: #444444;
            border: none;
            border-radius: 8px;
            color: #ffffff;
            padding: 12px;
            margin-bottom: 15px;
        }
        .form-control::placeholder {
            color: #bbbbbb;
        }
        .form-control:focus {
            background-color: #555555;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
            border: none;
            color: #ffffff;
        }
        .btn-primary {
            background-color: #0062cc;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #004085;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            transform: translateY(-2px);
        }
        .forgot-password {
            color: #cccccc;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 15px;
            transition: color 0.3s ease;
        }
        .forgot-password:hover {
            color: #ffffff;
            text-decoration: underline;
        }
        .head-title {
            font-family: 'Comfortaa', cursive;
            font-size: 2.5rem;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            text-align: center;
        }
        .logo {
            border-radius: 50%;
            border: 3px solid #ffffff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
        }
        .modal-content {
            background-color: #333333;
            margin: 15% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
            border-radius: 15px;
            position: relative;
        }
        .modal-btn {
            margin: 10px;
            padding: 12px 24px;
            background-color: #0062cc;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .modal-btn:hover {
            background-color: #004085;
            transform: translateY(-2px);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 15px;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .head-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="registration.php" onsubmit="return validateForm()">
            <div class="text-center">
                <img src="images/Logo.png" class="logo mb-4" alt="Company Logo" width="150" height="150">
            </div>
            <h1 class="head-title">Registration Form</h1>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First Name" required autofocus>
                    <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Surname" required>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                    <input type="number" id="age" name="age" class="form-control" min="1" placeholder="Age" required>
                    <input type="email" id="email" name="email" class="form-control" placeholder="E-mail" required>
                </div>
                <div class="col-md-6">
                    <div id="playerFields" style="display: none;">
                        <select id="position" name="position" class="form-control">
                            <option value="none">Select Position</option>
                            <option value="Guard">Point Guard</option>
                            <option value="Guard">Shooting Guard</option>
                            <option value="Forward">Small Forward</option>
                            <option value="Forward">Power Forward</option>
                            <option value="Center">Center</option>
                        </select>
                        <input type="text" id="team" name="team" class="form-control" placeholder="Team Name">
                    </div>
                    <input type="text" id="phone" name="phone" class="form-control" maxlength="11" placeholder="Phone Number" required>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    <input type="hidden" id="role" name="role" value="">
                </div>
            </div>
            <button class="btn btn-primary" id="submit" name="submit" type="submit">Create User</button>
            <a href="login.php" class="forgot-password">Already have an account?</a>
            <p class="mt-4 mb-3 text-muted text-center">&copy; Ballers Hub</p>
        </form>
    </div>

    <div id="roleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Are you a player or a coach?</h2>
            <button class="modal-btn" onclick="setRole('player')">Player</button>
            <button class="modal-btn" onclick="setRole('coach')">Coach</button>
        </div>
    </div>

    <script>
    function validateForm() {
        var password = document.getElementById('password').value;
        var confirm_password = document.getElementById('confirm_password').value;
        var role = document.getElementById('role').value;

        if (password !== confirm_password) {
            alert("Passwords do not match!");
            return false;
        }

        if (role === '') {
            alert("Please select a role (Player or Coach).");
            return false;
        }

        if (role === 'player') {
            var position = document.getElementById('position').value;
            var team = document.getElementById('team').value;
            if (position === 'none' || team === '') {
                alert("Please select a position and enter a team name.");
                return false;
            }
        }

        return true;
    }

    window.onload = function() {
        document.getElementById('roleModal').style.display = 'block';
    }

    function setRole(role) {
        document.getElementById('role').value = role;
        document.getElementById('roleModal').style.display = 'none';
        if (role === 'player') {
            document.getElementById('playerFields').style.display = 'block';
        } else {
            document.getElementById('playerFields').style.display = 'none';
        }
    }

    function closeModal() {
        document.getElementById('roleModal').style.display = 'none';
        document.getElementById('role').value = '';
    }
    </script>
</body>
</html>
