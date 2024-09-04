<?php
// Include the database connection
include 'db-connect.php';

// Handle deletion if a POST request is made
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = $_POST['team_name'];

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM teams WHERE team_name = ?");
    $stmt->bind_param("s", $team_name);

    if ($stmt->execute()) {
        echo "<script>alert('Team deleted successfully!'); window.location.href = window.location.href;</script>";
    } else {
        echo "<script>alert('Error deleting team: " . $conn->error . "'); window.location.href = window.location.href;</script>";
    }

    $stmt->close();
}

// Fetch teams from the database
$sql = "SELECT team_name, team_logo FROM teams";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Teams - Ballers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/viewteams.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<style>
    h5{
        color: black;
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

    <div class="container table-container">
        <h1>View and Delete Teams</h1>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Team Logo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <a href="#" class="team-link" data-team="<?php echo $row['team_name']; ?>">
                            <?php echo $row['team_name']; ?>
                        </a>
                    </td>
                    <td><img src="<?php echo $row['team_logo']; ?>" alt="Team Logo" style="width: 50px; height: auto;"></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="team_name" value="<?php echo $row['team_name']; ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to delete this team?');">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal for displaying players -->
    <div class="modal fade" id="playersModal" tabindex="-1" aria-labelledby="playersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="playersModalLabel">Players in Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="players-list">
                    <!-- Player data will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Close the database connection
    $conn->close();
    ?>

    <script>
        $(document).ready(function() {
            $('.team-link').click(function(e) {
                e.preventDefault();
                var teamName = $(this).data('team');

                $.ajax({
                    url: 'teamplayers.php', // Endpoint to fetch players data
                    type: 'POST',
                    data: {team_name: teamName},
                    success: function(response) {
                        $('#players-list').html(response);
                        $('#playersModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr);
                    }
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
