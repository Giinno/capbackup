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
            color: #F57C00;
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
    </style>
</head>
<body>

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
