<?php
session_start();
include 'db-connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check for empty inputs
    if (empty($username) || empty($password)) {
        $error = "Please fill in both fields.";
    } else {
        // Query to check the user credentials
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $stored_password, $role);
            $stmt->fetch();

            // Compare the provided password directly with the stored password
            if ($password == $stored_password) {
                // Password is correct, start a new session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = trim($role); // Trim any leading/trailing spaces

                // Debugging logs
                error_log("User ID: " . $_SESSION['user_id']);
                error_log("User role: " . $_SESSION['role']);

                // Redirect based on role
                switch (strtolower($_SESSION['role'])) {
                    case 'statistics-admin':
                        error_log("Redirecting to Stats-admin-.php");
                        header("Location: stats-admin-dashboard.php");
                        exit(); // Ensure the script stops execution after header redirect
                    case 'scheduling-admin':
                        error_log("Redirecting to Sched-admin-.php");
                        header("Location: Sched-admin-dashboard.php");
                        exit(); // Ensure the script stops execution after header redirect
                    case 'super-admin':
                        error_log("Redirecting to admin-dashboard.php");
                        header("Location: admin-dashboard.php");
                        exit(); // Ensure the script stops execution after header redirect
                    default:
                        error_log("Redirecting to dashboard.php");
                        header("Location: dashboard.php");
                        exit(); // Ensure the script stops execution after header redirect
                }
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that username.";
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
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <title>Login</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
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
        .form-signin {
            background-color: #333333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
        }
        .form-control {
            background-color: #444444;
            border: none;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: #ffffff;
            margin-bottom: 10px;
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
            width: 100%;
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
            text-align: center;
        }
        .logo {
            border-radius: 50%;
            border: 2px solid #ffffff;
            display: block;
            margin: 0 auto 20px auto;
        }
    </style>
</head>
<body>
    <form class="form-signin" method="POST" action="login.php">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <img src="images/Logo.png" class="logo" alt="Company Logo" width="150" height="150">
        <h1 class="head-title">Login Form</h1>
        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" id="submit" name="submit" type="submit">Login</button>
        <!-- <a href="forgot_password.php" class="forgot-password">Forgot Password?</a> -->
        <a href="Registration.php" class="forgot-password">Don't have an account?</a>
        <p class="mt-3 mb-3 text-muted text-center">&copy; Ballers Hub</p>
    </form>
</body>
</html>
