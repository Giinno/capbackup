<!DOCTYPE html>
<html>
<head>
    <title>Basketball Game Stats Sheet</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #333333;
            color: #ffffff;
            margin: 100px;
            font-size: 15px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            color: #ffffff;
        }
        th, td {
            padding: 5px;
            text-align: center;
            border: 1px solid #333333;
            color: #ffffff;
        }
        th {
            background-color: #333333;
            font-size: px;
        }
        .form-control {
            font-size: 14px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #555555;
        }
        .form-control-lg {
            font-size: 14px;
            height: calc(1.5em + 1rem + 2px);
            padding: 0.5rem 1rem;
            width: 80px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #555555;
        }
        .form-control-name {
            font-size: 14px;
            height: calc(1.5em + 1.5rem + 2px);
            padding: 0.75rem 1rem;
            width: 150px;
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #555555;
        }
        .btn {
            font-size: 16px;
        }
        .btn-primary {
            background-color: #F57C00;
            border: none;
        }
        .btn-success {
            background-color: #4CAF50;
            border: none;
        }
        .btn-danger {
            background-color: #f44336;
            border: none;
        }
        .text-center h2 {
            color: #F57C00;
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
            margin-left: 300px;
        }
        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }
    </style>
</head>
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetchTeams();

            document.getElementById('teamSelect1').addEventListener('change', function() {
                fetchPlayers(this.value, 1);
            });

            document.getElementById('teamSelect2').addEventListener('change', function() {
                fetchPlayers(this.value, 2);
            });
        });

        function fetchTeams() {
            fetch('fetch_teams.php')
                .then(response => response.json())
                .then(data => {
                    const teamSelect1 = document.getElementById('teamSelect1');
                    const teamSelect2 = document.getElementById('teamSelect2');
                    teamSelect1.innerHTML = '<option value="">Select a team</option>';
                    teamSelect2.innerHTML = '<option value="">Select a team</option>';
                    data.forEach(team => {
                        const option1 = document.createElement('option');
                        option1.value = team;
                        option1.textContent = team;
                        teamSelect1.appendChild(option1);

                        const option2 = document.createElement('option');
                        option2.value = team;
                        option2.textContent = team;
                        teamSelect2.appendChild(option2);
                    });
                })
                .catch(error => {
                    console.error('Error fetching teams:', error);
                    alert('Error fetching teams: ' + error.message);
                });
        }

        function fetchPlayers(team, teamIndex) {
            fetch(`fetch_players.php?team=${team}`)
                .then(response => response.json())
                .then(data => {
                    const playersTableBody = document.querySelector(`#playersTable${teamIndex} tbody`);
                    playersTableBody.innerHTML = '';
                    data.forEach((player, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><input type="text" name="teams[${teamIndex}][players][${index}][name]" class="form-control form-control-name" value="${player.name}" required readonly></td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][number]" class="form-control form-control-lg" value="${player.number}" required readonly></td>
                            <td>
                                <input type="number" name="teams[${teamIndex}][players][${index}][2pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                                <input type="number" name="teams[${teamIndex}][players][${index}][2pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                            </td>
                            <td>
                                <input type="number" name="teams[${teamIndex}][players][${index}][3pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                                <input type="number" name="teams[${teamIndex}][players][${index}][3pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                            </td>
                            <td>
                                <input type="number" name="teams[${teamIndex}][players][${index}][ft_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                                <input type="number" name="teams[${teamIndex}][players][${index}][ft_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                            </td>
                            <td>
                                <input type="number" name="teams[${teamIndex}][players][${index}][reb_off]" class="form-control form-control-lg" placeholder="Off." required>
                                <input type="number" name="teams[${teamIndex}][players][${index}][reb_def]" class="form-control form-control-lg" placeholder="Def." required>
                            </td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][assists]" class="form-control form-control-lg" required></td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][steals]" class="form-control form-control-lg" required></td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][blocks]" class="form-control form-control-lg" required></td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][turnovers]" class="form-control form-control-lg" required></td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][fouls]" class="form-control form-control-lg" required></td>
                            <td><input type="number" name="teams[${teamIndex}][players][${index}][points]" class="form-control form-control-lg" required readonly></td>
                            <td><button type="button" class="btn btn-danger" onclick="removePlayerRow(this)">Remove</button></td>
                        `;
                        playersTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error fetching players:', error);
                    alert('Error fetching players: ' + error.message);
                });
        }

        function validateMadeAttempted(input, teamIndex) {
            const row = input.closest('tr');
            const attemptedName = input.name.replace('_made', '_attempted');
            const attempted = row.querySelector(`[name="${attemptedName}"]`);

            if (parseInt(input.value) > parseInt(attempted.value)) {
                alert('Made shots cannot be greater than attempted shots.');
                input.value = attempted.value;
            }
            calculateTotalPoints(row);
            validateTotalScore(teamIndex);
        }

        function calculateTotalPoints(row) {
            const twoPtMade = parseInt(row.querySelector('[name*="[2pt_made]"]').value) || 0;
            const threePtMade = parseInt(row.querySelector('[name*="[3pt_made]"]').value) || 0;
            const ftMade = parseInt(row.querySelector('[name*="[ft_made]"]').value) || 0;
            const totalPoints = (twoPtMade * 2) + (threePtMade * 3) + ftMade;
            row.querySelector('[name*="[points]"]').value = totalPoints;
        }

        function validateTotalScore(teamIndex) {
            const totalScoreInput = document.getElementById(`totalScore${teamIndex}`);
            const totalScore = parseInt(totalScoreInput.value) || 0;
            let totalPoints = 0;

            document.querySelectorAll(`#playersTable${teamIndex} [name*="[points]"]`).forEach(input => {
                totalPoints += parseInt(input.value) || 0;
            });

            if (totalPoints !== totalScore) {
                totalScoreInput.setCustomValidity('Total score does not match the sum of player points.');
            } else {
                totalScoreInput.setCustomValidity('');
            }
        }

        function addPlayerRow(teamIndex) {
            const table = document.getElementById(`playersTable${teamIndex}`).querySelector('tbody');
            const rowCount = table.rows.length;
            const row = table.insertRow(rowCount);
            row.innerHTML = `
                <td><input type="text" name="teams[${teamIndex}][players][${rowCount}][name]" class="form-control form-control-name" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][number]" class="form-control form-control-lg" required></td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][2pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][2pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                </td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][3pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][3pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                </td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][ft_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][ft_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this, ${teamIndex})">
                </td>
                <td>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][reb_off]" class="form-control form-control-lg" placeholder="Off." required>
                    <input type="number" name="teams[${teamIndex}][players][${rowCount}][reb_def]" class="form-control form-control-lg" placeholder="Def." required>
                </td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][assists]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][steals]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][blocks]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][turnovers]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][fouls]" class="form-control form-control-lg" required></td>
                <td><input type="number" name="teams[${teamIndex}][players][${rowCount}][points]" class="form-control form-control-lg" required readonly></td>
                <td><button type="button" class="btn btn-danger" onclick="removePlayerRow(this)">Remove</button></td>
            `;
        }

        function removePlayerRow(button) {
            const row = button.closest('tr');
            row.remove();
            const teamIndex = button.closest('table').id.replace('playersTable', '');
            validateTotalScore(teamIndex);
        }
    </script>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Basketball Game Stats Sheet</h2>
        <form action="statistics.php" method="POST">
            <div class="form-group">
                <label for="teamSelect1">Select Team 1:</label>
                <select class="form-control" id="teamSelect1" name="teams[1][team]" required>
                    <option value="">Select a team</option>
                </select>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="addPlayerRow(1)">Add Player</button>
            </div>
            <table class="table" id="playersTable1">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Number</th>
                        <th>2Pt</th>
                        <th>3Pt</th>
                        <th>FT</th>
                        <th>Reb</th>
                        <th>Assists</th>
                        <th>Steals</th>
                        <th>Blocks</th>
                        <th>TO</th>
                        <th>Fouls</th>
                        <th>Points</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Player rows will be dynamically added here -->
                </tbody>
            </table>
            <div class="form-group">
                <label for="totalScore1">Total Score:</label>
                <input type="number" class="form-control" id="totalScore1" name="teams[1][total_score]" required oninput="validateTotalScore(1)">
                <div class="invalid-feedback">Total score does not match the sum of player points.</div>
            </div>
            <hr>
            <div class="form-group">
                <label for="teamSelect2">Select Team 2:</label>
                <select class="form-control" id="teamSelect2" name="teams[2][team]" required>
                    <option value="">Select a team</option>
                </select>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="addPlayerRow(2)">Add Player</button>
            </div>
            <table class="table" id="playersTable2">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Number</th>
                        <th>2Pt</th>
                        <th>3Pt</th>
                        <th>FT</th>
                        <th>Reb</th>
                        <th>Assists</th>
                        <th>Steals</th>
                        <th>Blocks</th>
                        <th>TO</th>
                        <th>Fouls</th>
                        <th>Points</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Player rows will be dynamically added here -->
                </tbody>
            </table>
            <div class="form-group">
                <label for="totalScore2">Total Score:</label>
                <input type="number" class="form-control" id="totalScore2" name="teams[2][total_score]" required oninput="validateTotalScore(2)">
                <div class="invalid-feedback">Total score does not match the sum of player points.</div>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
