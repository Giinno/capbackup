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
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            text-align: center;
        }

        .table-container {
            background-color: var(--secondary-color);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        th {
            background-color: #2e2e2e;
            font-weight: 600;
        }

        tr:hover {
            background-color: #2a2a2a;
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
            <h1 class="page-title">Feedback Messages</h1>
            <div class="table-container">
                <table>
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
                            echo "<tr><td colspan='5' style='text-align: center;'>No messages found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>
