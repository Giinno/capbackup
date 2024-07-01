<?php
include 'db-connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $age = (int)$_POST['age'];
    $email = trim($_POST['email']);
    $position = trim($_POST['position']);
    $team = trim($_POST['team']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($position) || empty($team) || empty($phone) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Please fill all the fields.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "<script>alert('Username already exists! Please choose a different username.');</script>";
        } else {
            // Proceed with registration
            $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, age, email, position, team, phone, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisssss", $firstname, $lastname, $username, $age, $email, $position, $team, $phone, $passwordHash);

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
    <link rel="stylesheet" href="css/registration.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.79.0">
    <title>Registration Form</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sign-in/">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #333333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }
        .form-control {
            background-color: #444444;
            border: none;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #ffffff;
        }
        .form-control::placeholder {
            color: #bbbbbb;
        }
        .form-control:focus {
            background-color: #555555;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
            border: none;
            color: #ffffff;
        }
        .btn-primary {
            background-color: #0062cc;
            border-color: #005cbf;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s, box-shadow 0.3s;
            width: 50%;
            margin: 0 auto;
            display: block;
        }
        .btn-primary:hover {
            background-color: #004085;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        .btn-primary:focus {
            background-color: #004085;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        .forgot-password {
            color: #cccccc;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 10px;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .head-title {
            font-family: 'Comfortaa', cursive;
            font-size: 2.5rem;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }
        .logo {
            border-radius: 50%;
            border: 2px solid #ffffff;
        }
        select {
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="registration.php" onsubmit="return validatePassword()">
            <div class="text-center">
                <img src="images/Logo.png" class="logo mb-4" alt="Company Logo" width="150" height="150">
            </div>
            <h1 class="head-title text-center mb-4">Registration Form</h1>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" id="firstname" name="firstname" class="form-control" placeholder="First Name" required autofocus>
                    </div>
                    <div class="form-group">
                        <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Surname" required>
                    </div>
                    <div class="form-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="number" id="age" name="age" class="form-control" min="1" placeholder="Age" required>
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" name="email" class="form-control" placeholder="E-mail" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <select id="position" name="position" class="form-control" required>
                            <option value="none" class="redme">Select Position</option>
                            <option value="Guard">Point Guard</option>
                            <option value="Guard">Shooting Guard</option>
                            <option value="Forward">Small Forward</option>
                            <option value="Forward">Power Forward</option>
                            <option value="Center">Center</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" id="team" name="team" class="form-control" placeholder="Team Name" required>
                    </div>
                    <div class="form-group">
                        <input type="text" id="phone" name="phone" class="form-control" maxlength="11" placeholder="Phone Number" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary mt-4" id="submit" name="submit" type="submit">Create User</button>
            <a href="login.php" class="forgot-password">Already have an account?</a>
            <p class="mt-5 mb-3 text-muted text-center">&copy; Ballers Hub</p>
        </form>
    </div>

    <script>
    function validatePassword() {
        var password = document.getElementById('password').value;
        var confirm_password = document.getElementById('confirm_password').value;
        if (password !== confirm_password) {
            alert("Passwords do not match!");
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
