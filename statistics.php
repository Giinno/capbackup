<!DOCTYPE html>
<html>
<head>
    <title>Basketball Player Statistics</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            font-weight: bold;
            align-items: center;
        }
        body {
            background-color: whitesmoke;
            align-items: center;
            margin-left: -50px;
            font-size: 13px;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        img {
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Basketball Player Statistics</h2>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "ballers_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch player statistics
    $sql = "SELECT * FROM statistics";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo '<table class="table table-striped">';
        echo "<thead class='thead-dark'>";
        echo "<tr>
                <th>Profile Picture</th>
                <th>Name</th>
                <th>Number</th>
                <th>Points</th>
                <th>Assists</th>
                <th>Def Reb</th>
                <th>Off Reb</th>
                <th>Rebounds</th>
                <th>Steals</th>
                <th>Blocks</th>
                <th>Turnovers</th>
                <th>Fouls</th>
                <th>2PA</th>
                <th>2PM</th>
                <th>3PA</th>
                <th>3PM</th>
                <th>FTA</th>
                <th>FTM</th>
              </tr>";
        echo "</thead><tbody>";
        while($row = $result->fetch_assoc()) {
            $totalRebounds = $row["reb_def"] + $row["reb_off"];

            // Fetch profile picture based on player's name
            $playerName = $row["name"];
            $profilePictureSql = "SELECT profile_picture FROM statistics WHERE name = '$playerName'";
            $profilePictureResult = $conn->query($profilePictureSql);
            if ($profilePictureResult->num_rows > 0) {
                $profilePictureRow = $profilePictureResult->fetch_assoc();
                $profilePicture = $profilePictureRow["profile_picture"];
            } else {
                $profilePicture = 'default_profile_picture.jpg'; // Use a default picture if none is provided
            }

            echo "<tr>";
            echo "<td><img src='" . $profilePicture . "' alt='Profile Picture' width='50' height='50'></td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["number"] . "</td>";
            echo "<td>" . $row["points"] . "</td>";
            echo "<td>" . $row["assists"] . "</td>";
            echo "<td>" . $row["reb_def"] . "</td>";
            echo "<td>" . $row["reb_off"] . "</td>";
            echo "<td>" . $totalRebounds . "</td>";
            echo "<td>" . $row["steals"] . "</td>";
            echo "<td>" . $row["blocks"] . "</td>";
            echo "<td>" . $row["turnovers"] . "</td>";
            echo "<td>" . $row["fouls"] . "</td>";
            echo "<td>" . $row["2pt_attempted"] . "</td>";
            echo "<td>" . $row["2pt_made"] . "</td>";
            echo "<td>" . $row["3pt_attempted"] . "</td>";
            echo "<td>" . $row["3pt_made"] . "</td>";
            echo "<td>" . $row["ft_attempted"] . "</td>";
            echo "<td>" . $row["ft_made"] . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-warning text-center'>0 results</div>";
    }

    $conn->close();
    ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
