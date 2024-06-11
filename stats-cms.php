<!DOCTYPE html>
<html>
<head>
    <title>Basketball Game CMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
            font-size: 13px;
            margin-bottom: 50px;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #343a40;
            color: white;
        }
        img {
            border-radius: 50%;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-warning {
            color: #fff;
        }
        label, td, h2 {
            color: #f57C00;
        }

        td {
            font-weight: 700;
            font-size: 15px;
        }
        label {
            font-size: 15px;
            padding: 2px;
            word-spacing: 1px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Basketball Game CMS</h2>

    <form action="" method="post" enctype="multipart/form-data" class="border p-4 my-4" id="playerForm">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="player_id" id="playerId">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="playerName">Name:</label>
                <input type="text" name="name" id="playerName" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="playerNumber">Number:</label>
                <input type="number" name="number" id="playerNumber" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="playerPoints">Points:</label>
                <input type="number" name="points" id="playerPoints" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="playerAssists">Assists:</label>
                <input type="number" name="assists" id="playerAssists" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="playerRebDef">Def. Rebounds:</label>
                <input type="number" name="reb_def" id="playerRebDef" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="playerRebOff">Off. Rebounds:</label>
                <input type="number" name="reb_off" id="playerRebOff" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="playerSteals">Steals:</label>
                <input type="number" name="steals" id="playerSteals" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="playerBlocks">Blocks:</label>
                <input type="number" name="blocks" id="playerBlocks" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="playerTurnovers">Turnovers:</label>
                <input type="number" name="turnovers" id="playerTurnovers" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="playerFouls">Fouls:</label>
                <input type="number" name="fouls" id="playerFouls" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="player2ptAttempted">2-Point FG Attempted:</label>
                <input type="number" name="2pt_attempted" id="player2ptAttempted" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="player2ptMade">2-Point FG Made:</label>
                <input type="number" name="2pt_made" id="player2ptMade" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="player3ptAttempted">3-Point FG Attempted:</label>
                <input type="number" name="3pt_attempted" id="player3ptAttempted" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="player3ptMade">3-Point FG Made:</label>
                <input type="number" name="3pt_made" id="player3ptMade" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="playerFtAttempted">Free Throw Attempted:</label>
                <input type="number" name="ft_attempted" id="playerFtAttempted" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label for="playerFtMade">Free Throw Made:</label>
                <input type="number" name="ft_made" id="playerFtMade" class="form-control" required>
            </div>
        </div>
        <div class="form-group">
            <label for="playerProfilePicture">Profile Picture:</label>
            <input type="file" name="profile_picture" id="playerProfilePicture" class="form-control-file">
        </div>
        <div class="form-group">
            <button type="button" onclick="setFormAction('Add')" class="btn btn-primary">Add</button>
            <button type="button" onclick="setFormAction('Update')" class="btn btn-warning">Update</button>
        </div>
    </form>

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

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        $id = $_POST['player_id'];
        $number = $_POST['number'];
        $name = $_POST['name'];
        $two_pt_attempted = $_POST['2pt_attempted'];
        $two_pt_made = $_POST['2pt_made'];
        $three_pt_attempted = $_POST['3pt_attempted'];
        $three_pt_made = $_POST['3pt_made'];
        $ft_attempted = $_POST['ft_attempted'];
        $ft_made = $_POST['ft_made'];
        $reb_off = $_POST['reb_off'];
        $reb_def = $_POST['reb_def'];
        $assists = $_POST['assists'];
        $steals = $_POST['steals'];
        $blocks = $_POST['blocks'];
        $turnovers = $_POST['turnovers'];
        $fouls = $_POST['fouls'];
        $points = $_POST['points'];
        $profile_picture = $_FILES['profile_picture']['name'];

        if ($profile_picture) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
            if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
                $target_file = "";
            }
        } else {
            $target_file = "";
        }

        if ($action == 'Add') {
            $stmt = $conn->prepare("INSERT INTO statistics (number, name, 2pt_attempted, 2pt_made, 3pt_attempted, 3pt_made, ft_attempted, ft_made, reb_off, reb_def, assists, steals, blocks, turnovers, fouls, points, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiiiiiiiiiiiiiss", $number, $name, $two_pt_attempted, $two_pt_made, $three_pt_attempted, $three_pt_made, $ft_attempted, $ft_made, $reb_off, $reb_def, $assists, $steals, $blocks, $turnovers, $fouls, $points, $target_file);
            $stmt->execute();
            $stmt->close();
            echo "<div class='alert alert-success'>Player added successfully</div>";
        } elseif ($action == 'Update') {
            $stmt = $conn->prepare("UPDATE statistics SET number=?, name=?, 2pt_attempted=?, 2pt_made=?, 3pt_attempted=?, 3pt_made=?, ft_attempted=?, ft_made=?, reb_off=?, reb_def=?, assists=?, steals=?, blocks=?, turnovers=?, fouls=?, points=?, profile_picture=? WHERE id=?");
            $stmt->bind_param("isiiiiiiiiiiiiissi", $number, $name, $two_pt_attempted, $two_pt_made, $three_pt_attempted, $three_pt_made, $ft_attempted, $ft_made, $reb_off, $reb_def, $assists, $steals, $blocks, $turnovers, $fouls, $points, $target_file, $id);
            $stmt->execute();
            $stmt->close();
            echo "<div class='alert alert-warning'>Player updated successfully</div>";
        } elseif ($action == 'Delete') {
            $stmt = $conn->prepare("DELETE FROM statistics WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            echo "<div class='alert alert-danger'>Player deleted successfully</div>";
        }
    }

    // Fetch players from the database
    $result = $conn->query("SELECT * FROM statistics");

    echo '<table class="table table-striped">';
    echo '<thead><tr><th>Name</th><th>Number</th><th>2P FG</th><th>3P FG</th><th>FT</th><th>Reb Off</th><th>Reb Def</th><th>Assists</th><th>Steals</th><th>Blocks</th><th>Turnovers</th><th>Fouls</th><th>Points</th><th>Profile Picture</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['name'] . '</td>';
        echo '<td>' . $row['number'] . '</td>';
        echo '<td>' . $row['2pt_made'] . '/' . $row['2pt_attempted'] . '</td>';
        echo '<td>' . $row['3pt_made'] . '/' . $row['3pt_attempted'] . '</td>';
        echo '<td>' . $row['ft_made'] . '/' . $row['ft_attempted'] . '</td>';
        echo '<td>' . $row['reb_off'] . '</td>';
        echo '<td>' . $row['reb_def'] . '</td>';
        echo '<td>' . $row['assists'] . '</td>';
        echo '<td>' . $row['steals'] . '</td>';
        echo '<td>' . $row['blocks'] . '</td>';
        echo '<td>' . $row['turnovers'] . '</td>';
        echo '<td>' . $row['fouls'] . '</td>';
        echo '<td>' . $row['points'] . '</td>';
        echo '<td>';
        if ($row['profile_picture']) {
            echo '<img src="uploads/' . $row['profile_picture'] . '" alt="Profile Picture" width="50" height="50">';
        } else {
            echo 'N/A';
        }
        echo '</td>';
        echo '<td>
                <button class="btn btn-warning btn-sm" onclick="editPlayer(' . $row['id'] . ', \'' . $row['name'] . '\', ' . $row['number'] . ', ' . $row['2pt_attempted'] . ', ' . $row['2pt_made'] . ', ' . $row['3pt_attempted'] . ', ' . $row['3pt_made'] . ', ' . $row['ft_attempted'] . ', ' . $row['ft_made'] . ', ' . $row['reb_off'] . ', ' . $row['reb_def'] . ', ' . $row['assists'] . ', ' . $row['steals'] . ', ' . $row['blocks'] . ', ' . $row['turnovers'] . ', ' . $row['fouls'] . ', ' . $row['points'] . ')">Edit</button>
                <button class="btn btn-danger btn-sm" onclick="deletePlayer(' . $row['id'] . ')">Delete</button>
              </td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    $conn->close();
    ?>
</div>

<script>
    function setFormAction(action) {
        document.getElementById('formAction').value = action;
        document.getElementById('playerForm').submit();
    }

    function editPlayer(id, name, number, two_pt_attempted, two_pt_made, three_pt_attempted, three_pt_made, ft_attempted, ft_made, reb_off, reb_def, assists, steals, blocks, turnovers, fouls, points) {
        document.getElementById('formAction').value = 'Update';
        document.getElementById('playerId').value = id;
        document.getElementById('playerName').value = name;
        document.getElementById('playerNumber').value = number;
        document.getElementById('player2ptAttempted').value = two_pt_attempted;
        document.getElementById('player2ptMade').value = two_pt_made;
        document.getElementById('player3ptAttempted').value = three_pt_attempted;
        document.getElementById('player3ptMade').value = three_pt_made;
        document.getElementById('playerFtAttempted').value = ft_attempted;
        document.getElementById('playerFtMade').value = ft_made;
        document.getElementById('playerRebOff').value = reb_off;
        document.getElementById('playerRebDef').value = reb_def;
        document.getElementById('playerAssists').value = assists;
        document.getElementById('playerSteals').value = steals;
        document.getElementById('playerBlocks').value = blocks;
        document.getElementById('playerTurnovers').value = turnovers;
        document.getElementById('playerFouls').value = fouls;
        document.getElementById('playerPoints').value = points;
    }

    function deletePlayer(id) {
        if (confirm("Are you sure you want to delete this player?")) {
            document.getElementById('formAction').value = 'Delete';
            document.getElementById('playerId').value = id;
            document.getElementById('playerForm').submit();
        }
    }
</script>

<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
