<?php
include 'db-connect.php';

$sql = "SELECT id, name, email, message, created_at FROM feedback";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Messages - Ballers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .navbar {
            background-color: #222222;
        }
        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }
        .table-container {
            margin-top: 50px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 15px;
        }
        .table-dark {
            background-color: #1e1e1e;
        }
        .table-dark th {
            background-color: #2e2e2e;
        }
        .table-dark tr:hover {
            background-color: #2a2a2a;
        }
        .table-dark td, .table-dark th {
            border: 1px solid #3a3a3a;
        }
        .table-dark thead th {
            border-bottom: 2px solid #4a4a4a;
        }
        h1 {
            font-size: 3em;
            margin-bottom: 20px;
            text-align: center;
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
            margin-left: 265px;
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

    <div class="container table-container">
        <h1>Feedback Messages</h1>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['message']}</td>
                                <td>{$row['created_at']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No messages found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
