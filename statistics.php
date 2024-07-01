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
    require 'db-connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Insert game results into the 'games' table
            $stmtGames = $conn->prepare("INSERT INTO games (team1, team2, team1_score, team2_score) VALUES (?, ?, ?, ?)");
            $stmtGames->bind_param("ssii", $_POST['teams'][1]['team'], $_POST['teams'][2]['team'], $_POST['teams'][1]['total_score'], $_POST['teams'][2]['total_score']);
            $stmtGames->execute();
            if ($stmtGames->error) {
                throw new Exception($stmtGames->error);
            }
            $game_id = $stmtGames->insert_id; // Get the inserted game_id
            $stmtGames->close();

            // Insert player statistics
            $stmtStats = $conn->prepare("
                INSERT INTO statistics (
                    game_id, name, number, points, assists, reb_off, reb_def, rebounds, steals, turnovers, blocks, fouls, 
                    2pt_attempted, 2pt_made, 3pt_attempted, 3pt_made, ft_attempted, ft_made, profile_picture
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmtStats === false) {
                throw new Exception($conn->error);
            }

            foreach ($_POST['teams'] as $team) {
                foreach ($team['players'] as $player) {
                    $profilePicture = $player['profile_picture'] ?? 'default_profile_picture.jpg';
                    $totalRebounds = $player['reb_off'] + $player['reb_def'];

                    $stmtStats->bind_param("isiiiiiiiiiiiiiiisi", 
                        $game_id,
                        $player['name'],
                        $player['number'],
                        $player['points'],
                        $player['assists'],
                        $player['reb_off'],
                        $player['reb_def'],
                        $totalRebounds,
                        $player['steals'],
                        $player['turnovers'],
                        $player['blocks'],
                        $player['fouls'],
                        $player['2pt_attempted'],
                        $player['2pt_made'],
                        $player['3pt_attempted'],
                        $player['3pt_made'],
                        $player['ft_attempted'],
                        $player['ft_made'],
                        $profilePicture
                    );

                    if (!$stmtStats->execute()) {
                        throw new Exception($stmtStats->error);
                    }
                }
            }
            $stmtStats->close();

            // Commit transaction
            $conn->commit();

            // Redirect to the same page to refresh
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo "<div class='alert alert-danger text-center'>Failed to save data: " . $e->getMessage() . "</div>";
        }
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
                <th>Off Reb</th>
                <th>Def Reb</th>
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
            $profilePicture = $row["profile_picture"];

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
