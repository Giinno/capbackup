<!DOCTYPE html>
<html>
<head>
    <title>Basketball Game CMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *{
            padding: 5px;
        }
        body {
            background-color: #333333;
            font-size: 13px;
            margin-bottom: 50px;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 100%;
            margin-left: 280px;
        }
        th, td {
            padding: 5px;
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
        td, h2 {
            color: #ffffff;
        }
        td {
            font-weight: 700;
            font-size: 12px;
        }
        label {
            font-size: 15px;
            padding: 2px;
            word-spacing: 1px;
            letter-spacing: 1px;
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
            margin-left: 330px;
        }
        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }
    </style>
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
    <h2 class="text-center my-4">Basketball Game CMS</h2>

    <!-- Modal -->
    <div class="modal fade" id="playerModal" tabindex="-1" role="dialog" aria-labelledby="playerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="playerModalLabel">Player Form</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="stats-handler.php" method="post" enctype="multipart/form-data" id="playerForm">
                    <div class="modal-body">
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
                            <label for="profilePicture">Profile Picture:</label>
                            <input type="file" name="profile_picture" id="profilePicture" class="form-control-file">
                            <img id="profilePicturePreview" src="#" alt="Profile Picture Preview" style="display:none; max-height: 200px; margin-top: 10px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Player Table -->
    <div class="table-responsive">
        <table class="table table-dark table-striped table-hover">
            <thead>
                <tr>
                    <th>Profile Picture</th>
                    <th>Name</th>
                    <th>Number</th>
                    <th>Points</th>
                    <th>Assists</th>
                    <th>Def. Rebounds</th>
                    <th>Off. Rebounds</th>
                    <th>Steals</th>
                    <th>Blocks</th>
                    <th>Turnovers</th>
                    <th>Fouls</th>
                    <th>2-Point FG Attempted</th>
                    <th>2-Point FG Made</th>
                    <th>3-Point FG Attempted</th>
                    <th>3-Point FG Made</th>
                    <th>Free Throw Attempted</th>
                    <th>Free Throw Made</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'db-connect.php';

                $sql = "SELECT s.*, p.profile_picture FROM statistics s LEFT JOIN profiles p ON s.name = p.name";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><img src='" . $row["profile_picture"] . "' alt='Profile Picture' style='height: 100px; width: 100px;'></td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["number"] . "</td>";
                        echo "<td>" . $row["points"] . "</td>";
                        echo "<td>" . $row["assists"] . "</td>";
                        echo "<td>" . $row["reb_def"] . "</td>";
                        echo "<td>" . $row["reb_off"] . "</td>";
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
                        echo "<td><button class='btn btn-warning btn-sm' onclick='editPlayer(" . json_encode($row) . ")'>Edit</button> ";
                        echo "<button class='btn btn-danger btn-sm' onclick='deletePlayer(" . $row["id"] . ")'>Delete</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='18'>No players found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap and jQuery JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    function editPlayer(player) {
        $('#formAction').val('update');
        $('#playerId').val(player.id);
        $('#playerName').val(player.name);
        $('#playerNumber').val(player.number);
        $('#playerPoints').val(player.points);
        $('#playerAssists').val(player.assists);
        $('#playerRebDef').val(player.reb_def);
        $('#playerRebOff').val(player.reb_off);
        $('#playerSteals').val(player.steals);
        $('#playerBlocks').val(player.blocks);
        $('#playerTurnovers').val(player.turnovers);
        $('#playerFouls').val(player.fouls);
        $('#player2ptAttempted').val(player["2pt_attempted"]);
        $('#player2ptMade').val(player["2pt_made"]);
        $('#player3ptAttempted').val(player["3pt_attempted"]);
        $('#player3ptMade').val(player["3pt_made"]);
        $('#playerFtAttempted').val(player["ft_attempted"]);
        $('#playerFtMade').val(player["ft_made"]);
        $('#profilePicturePreview').attr('src', player.profile_picture).show();
        $('#playerModal').modal('show');
    }

    function deletePlayer(playerId) {
        if (confirm('Are you sure you want to delete this player?')) {
            $.ajax({
                url: 'stats-handler.php',
                type: 'POST',
                data: { action: 'delete', player_id: playerId },
                success: function(response) {
                    location.reload();
                }
            });
        }
    }

    function logout() {
        // Implement logout logic here
    }

    $('#playerForm').on('submit', function(event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'stats-handler.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#playerModal').modal('hide');
                location.reload();
            }
        });
    });

    $('#profilePicture').on('change', function() {
        const [file] = this.files;
        if (file) {
            $('#profilePicturePreview').attr('src', URL.createObjectURL(file)).show();
        }
    });
</script>
</body>
</html>
