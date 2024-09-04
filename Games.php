<?php
// Include the database connection file
require 'db-connect.php';

$message = "";
$message_type = "";

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team1 = $_POST['team1'];
    $total_score1 = $_POST['total_score1'];
    $team2 = $_POST['team2'];
    $total_score2 = $_POST['total_score2'];

    // Prepare and bind for first team
    $stmt = $conn->prepare("INSERT INTO games (team, total_score) VALUES (?, ?)");
    $stmt->bind_param("si", $team1, $total_score1);
    $stmt2 = $conn->prepare("INSERT INTO games (team, total_score) VALUES (?, ?)");
    $stmt2->bind_param("si", $team2, $total_score2);

    // Execute the statements
    if ($stmt->execute() && $stmt2->execute()) {
        $message = "New records created successfully";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error . " / " . $stmt2->error;
        $message_type = "error";
    }

    $stmt->close();
    $stmt2->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Game Scores</title>
    <style>
        body {
            background-color: #222222;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #333333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 700px;
            text-align: center;
        }
        h1 {
            color: #F57C00;
        }
        form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .form-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        input[type="text"], input[type="number"] {
            width: 150px;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #F57C00;
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #e06c00;
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
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #333333;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 300px;
            text-align: center;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submit Game Scores</h1>
        <form method="POST">
            <div class="form-row">
                <input type="text" name="team1" placeholder="Team 1" required>
                <input type="number" name="total_score1" placeholder="Total Score 1" required>
            </div>
            <div class="form-row">
                <input type="text" name="team2" placeholder="Team 2" required>
                <input type="number" name="total_score2" placeholder="Total Score 2" required>
            </div>
            <input type="submit" value="Submit">
        </form>
    </div>

    <?php if (!empty($message)): ?>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p><?php echo $message; ?></p>
        </div>
    </div>
    <script>
        document.getElementById("myModal").style.display = "block";

        function closeModal() {
            document.getElementById("myModal").style.display = "none";
            window.location.href = window.location.href; // Refresh the page
        }

        // Automatically close the modal after 3 seconds
        setTimeout(closeModal, 3000);
    </script>
    <?php endif; ?>
</body>
</html>
