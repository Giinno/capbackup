
<!DOCTYPE html>
<html>
<head>
    <title>Basketball Game Stats Sheet</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: whitesmoke;
            margin: 20px;
            font-size: 15px;
        }
        .container {
            max-width: 1200px;
            margin-left: 150px;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .form-control {
            font-size: 14px;
        }
        .form-control-lg {
            font-size: 14px;
            height: calc(1.5em + 1rem + 2px);
            padding: 0.5rem 1rem;
            width: 80px;
        }
        .form-control-name {
            font-size: 14px;
            height: calc(1.5em + 1.5rem + 2px);
            padding: 0.75rem 1rem;
            width: 150px;
        }
        .btn {
            font-size: 16px;
        }
    </style>
    <script>
    function calculateTotalPoints(row) {
        const twoPtMade = parseInt(row.querySelector('[name*="[2pt_made]"]').value) || 0;
        const threePtMade = parseInt(row.querySelector('[name*="[3pt_made]"]').value) || 0;
        const ftMade = parseInt(row.querySelector('[name*="[ft_made]"]').value) || 0;
        const totalPoints = (twoPtMade * 2) + (threePtMade * 3) + ftMade;
        row.querySelector('[name*="[points]"]').value = totalPoints;
    }

    function validateMadeAttempted(input) {
        const row = input.closest('tr');
        const attemptedName = input.name.replace('_made', '_attempted');
        const attempted = row.querySelector(`[name="${attemptedName}"]`);

        if (parseInt(input.value) > parseInt(attempted.value)) {
            alert('Made shots cannot be greater than attempted shots.');
            input.value = attempted.value;
        }
        calculateTotalPoints(row);
    }

    function addPlayerRow() {
        const table = document.getElementById('playersTable');
        const rowCount = table.rows.length;
        const row = table.insertRow(rowCount);

        const playerCells = `
            <td><input type="text" name="players[${rowCount}][name]" class="form-control form-control-name" required></td>
            <td><input type="number" name="players[${rowCount}][number]" class="form-control form-control-lg" required></td>
            <td>
                <input type="number" name="players[${rowCount}][2pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                <input type="number" name="players[${rowCount}][2pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this)">
            </td>
            <td>
                <input type="number" name="players[${rowCount}][3pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                <input type="number" name="players[${rowCount}][3pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this)">
            </td>
            <td>
                <input type="number" name="players[${rowCount}][ft_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                <input type="number" name="players[${rowCount}][ft_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this)">
            </td>
            <td>
                <input type="number" name="players[${rowCount}][reb_off]" class="form-control form-control-lg" placeholder="Off." required>
                <input type="number" name="players[${rowCount}][reb_def]" class="form-control form-control-lg" placeholder="Def." required>
            </td>
            <td><input type="number" name="players[${rowCount}][assists]" class="form-control form-control-lg" required></td>
            <td><input type="number" name="players[${rowCount}][steals]" class="form-control form-control-lg" required></td>
            <td><input type="number" name="players[${rowCount}][blocks]" class="form-control form-control-lg" required></td>
            <td><input type="number" name="players[${rowCount}][turnovers]" class="form-control form-control-lg" required></td>
            <td><input type="number" name="players[${rowCount}][fouls]" class="form-control form-control-lg" required></td>
            <td><input type="number" name="players[${rowCount}][points]" class="form-control form-control-lg" required readonly></td>
            <td><button type="button" class="btn btn-danger" onclick="removePlayerRow(this)">Remove</button></td>
        `;

        row.innerHTML = playerCells;
    }

    function removePlayerRow(button) {
        const row = button.closest('tr');
        row.parentNode.removeChild(row);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[name*="[2pt_made]"], [name*="[3pt_made]"], [name*="[ft_made]"]').forEach(input => {
            input.addEventListener('input', () => validateMadeAttempted(input));
        });

        // Prevent form submission on Enter key press
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    });
    </script>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Basketball Stats Sheet</h2>

    <!-- Form to input statistics -->
    <form method="post" action="">
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Player</th>
                    <th>#</th>
                    <th>2-Point FG</th>
                    <th>3-Point FG</th>
                    <th>Free Throw</th>
                    <th>Rebounds</th>
                    <th>Assists</th>
                    <th>Steals</th>
                    <th>Blocks</th>
                    <th>Turnovers</th>
                    <th>Fouls</th>
                    <th>Total Points</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="playersTable">
                <!-- Row template for adding player statistics -->
                <tr>
                    <td><input type="text" name="players[0][name]" class="form-control form-control-name" required></td>
                    <td><input type="number" name="players[0][number]" class="form-control form-control-lg" required></td>
                    <td>
                        <input type="number" name="players[0][2pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                        <input type="number" name="players[0][2pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this)">
                    </td>
                    <td>
                        <input type="number" name="players[0][3pt_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                        <input type="number" name="players[0][3pt_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this)">
                    </td>
                    <td>
                        <input type="number" name="players[0][ft_attempted]" class="form-control form-control-lg" placeholder="Att." required>
                        <input type="number" name="players[0][ft_made]" class="form-control form-control-lg" placeholder="Made" required oninput="validateMadeAttempted(this)">
                    </td>
                    <td>
                        <input type="number" name="players[0][reb_off]" class="form-control form-control-lg" placeholder="Off." required>
                        <input type="number" name="players[0][reb_def]" class="form-control form-control-lg" placeholder="Def." required>
                    </td>
                    <td><input type="number" name="players[0][assists]" class="form-control form-control-lg" required></td>
                    <td><input type="number" name="players[0][steals]" class="form-control form-control-lg" required></td>
                    <td><input type="number" name="players[0][blocks]" class="form-control form-control-lg" required></td>
                    <td><input type="number" name="players[0][turnovers]" class="form-control form-control-lg" required></td>
                    <td><input type="number" name="players[0][fouls]" class="form-control form-control-lg" required></td>
                    <td><input type="number" name="players[0][points]" class="form-control form-control-lg" required readonly></td>
                    <td><button type="button" class="btn btn-danger" onclick="removePlayerRow(this)">Remove</button></td>
                </tr>
            </tbody>
        </table>
        <div class="text-center">
            <button type="button" class="btn btn-primary" onclick="addPlayerRow()">Add Player</button>
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        foreach ($_POST['players'] as $player) {
            $name = $conn->real_escape_string($player['name']);
            $number = $conn->real_escape_string($player['number']);
            $two_pt_attempted = $conn->real_escape_string($player['2pt_attempted']);
            $two_pt_made = $conn->real_escape_string($player['2pt_made']);
            $three_pt_attempted = $conn->real_escape_string($player['3pt_attempted']);
            $three_pt_made = $conn->real_escape_string($player['3pt_made']);
            $ft_attempted = $conn->real_escape_string($player['ft_attempted']);
            $ft_made = $conn->real_escape_string($player['ft_made']);
            $reb_off = $conn->real_escape_string($player['reb_off']);
            $reb_def = $conn->real_escape_string($player['reb_def']);
            $assists = $conn->real_escape_string($player['assists']);
            $steals = $conn->real_escape_string($player['steals']);
            $blocks = $conn->real_escape_string($player['blocks']);
            $turnovers = $conn->real_escape_string($player['turnovers']);
            $fouls = $conn->real_escape_string($player['fouls']);
            $points = $conn->real_escape_string($player['points']);

            $sql = "INSERT INTO statistics (name, number, 2pt_attempted, 2pt_made, 3pt_attempted, 3pt_made, ft_attempted, ft_made, reb_off, reb_def, assists, steals, blocks, turnovers, fouls, points) 
                    VALUES ('$name', '$number', '$two_pt_attempted', '$two_pt_made', '$three_pt_attempted', '$three_pt_made', '$ft_attempted', '$ft_made', '$reb_off', '$reb_def', '$assists', '$steals', '$blocks', '$turnovers', '$fouls', '$points')";

            if ($conn->query($sql) === TRUE) {
                echo "<div class='alert alert-success'>New record created successfully for $name</div>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }

        $conn->close();
    }
    ?>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
