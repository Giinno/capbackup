<?php
session_start();
require_once('db-connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Boys Scheduling</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <link rel="stylesheet" href="./css/schedule.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <style>
        /* Add any custom styles here */
        body {
            font-family: 'Arial', sans-serif;
            color: #f1f1f1;
        }

        #topNavBar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #page-container {
            margin-top: 30px;
        }

        #calendar {
            background-color: #1f1f1f;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .card {
            background-color: #2d2d2d;
            border: none;
        }

        .card-header {
            background-color: #FC5700;
            border-bottom: 2px solid #f1f1f1;
        }

        .card-title {
            font-weight: bold;
            color: #fff;
        }

        .card-body {
            color: #ddd;
        }

        .form-control {
            background-color: #1a1a1a;
            color: #f1f1f1;
            border: 1px solid #444;
        }

        .form-control:focus {
            background-color: #2b2b2b;
            color: #fff;
            border-color: #FC5700;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #FC5700;
            border: none;
        }

        .btn-primary:hover {
            background-color: #ff6f26;
        }

        .btn-default {
            background-color: #444;
            color: #f1f1f1;
        }

        .btn-default:hover {
            background-color: #666;
            color: #fff;
        }

        #event-details-modal .modal-content {
            background-color: #2d2d2d;
            color: #f1f1f1;
        }

        #event-details-modal .modal-header {
            border-bottom: 1px solid #444;
        }

        #event-details-modal .modal-footer {
            border-top: 1px solid #444;
        }

        #event-details-modal .btn-primary {
            background-color: #FC5700;
            border: none;
        }

        #event-details-modal .btn-primary:hover {
            background-color: #ff6f26;
        }

        #event-details-modal .btn-danger {
            background-color: #d9534f;
        }

        #event-details-modal .btn-danger:hover {
            background-color: #c9302c;
        }

        #event-details-modal .btn-secondary {
            background-color: #444;
            color: #f1f1f1;
        }

        #event-details-modal .btn-secondary:hover {
            background-color: #666;
            color: #fff;
        }
    </style>
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient" id="topNavBar">
        <div class="container">
            <a class="navbar-brand" style="color: #FC5700;" href="#">
                Ballers Hub
            </a>
            <div>
                <a class="text-light" href="dashboard.php" style="color: #FC5700;">Home</a>
            </div>
        </div>
    </nav>
    <div class="container py-5" id="page-container">
        <div class="row">
            <div class="col-md-9">
                <div id="calendar"></div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-0 shadow">
                    <div class="card-header bg-gradient bg-primary text-light" style="background-color:#FC5700;">
                        <h5 class="card-title">Schedule Form</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form action="save_schedule.php" method="post" id="schedule-form">
                                <input type="hidden" name="id" value="">
                                <!-- Hidden field for user_id -->
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

                                <div class="form-group mb-2">
                                    <label for="title" class="control-label">Reserver's Name</label>
                                    <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" required>
                                </div>

                                <!-- Game Mode (Radio Buttons) -->
                                <div class="form-group mb-2">
                                    <label for="description" class="control-label">Game Mode</label>
                                    <div>
                                        <input type="radio" name="game_mode" id="private_game" value="Private Game" checked>
                                        <label for="private_game">Private Game</label>
                                    </div>
                                    <div>
                                        <input type="radio" name="game_mode" id="tournament_mode" value="Tournament Mode">
                                        <label for="tournament_mode">Tournament Mode</label>
                                    </div>
                                    <!-- Hidden message that will appear when Tournament Mode is selected -->
                                    <small id="tournament_info" class="text-danger">Tournaments should be reserved 1 month before game day</small>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="start_datetime" class="control-label">Start</label>
                                    <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_datetime" id="start_datetime" required>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="end_datetime" class="control-label">End</label>
                                    <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_datetime" id="end_datetime" required>
                                </div>

                                <div class="text-center">
                                    <button class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form"><i class="fa fa-save"></i> Save</button>
                                    <button class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form"><i class="fa fa-reset"></i> Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card rounded-0 shadow mt-3">
                    <div class="card-header bg-gradient bg-primary text-light">
                        <h5 class="card-title">Court Location & Contact</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <p><strong>Location:</strong> Golden Boys, Nunez Vitaliano Agan Ave, Zamboanga</p>
                            <p><strong>Google Maps:</strong> <a href="https://maps.app.goo.gl/XjA7G2DWEKQh8x2W8" target="_blank">Golden Boys, Nunez Vitaliano Agan Ave, Zamboanga</a></p>
                            <p><strong>Contact Number:</strong> ###########</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title">Schedule Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">
                    <div class="container-fluid">
                        <dl>
                            <dt class="text-muted">Title</dt>
                            <dd id="title" class="fw-bold fs-4"></dd>
                            <dt class="text-muted">Game Mode</dt>
                            <dd id="game_mode" class=""></dd>
                            <dt class="text-muted">Start</dt>
                            <dd id="start" class=""></dd>
                            <dt class="text-muted">End</dt>
                            <dd id="end" class=""></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="">Delete</button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php 
        $schedules = $conn->query("SELECT * FROM `schedule_list`");
        $sched_res = [];
        foreach($schedules->fetch_all(MYSQLI_ASSOC) as $row){
            $row['sdate'] = date("F d, Y h:i A",strtotime($row['start_datetime']));
            $row['edate'] = date("F d, Y h:i A",strtotime($row['end_datetime']));
            $sched_res[$row['id']] = $row;
        }
        if(isset($conn)) $conn->close();
    ?>
    
    <script>
        var scheds = $.parseJSON('<?= json_encode($sched_res) ?>');

        document.addEventListener('DOMContentLoaded', function() {
            const tournamentMode = document.getElementById('tournament_mode');
            const privateGame = document.getElementById('private_game');
            const tournamentInfo = document.getElementById('tournament_info');

            // Show/Hide text when "Tournament Mode" is selected
            tournamentMode.addEventListener('change', function() {
                if (tournamentMode.checked) {
                    tournamentInfo.style.display = 'block';
                }
            });

            privateGame.addEventListener('change', function() {
                if (privateGame.checked) {
                    tournamentInfo.style.display = 'none';
                }
            });

            // Ensure end date is not before start date
            const startDateTimeInput = document.getElementById('start_datetime');
            const endDateTimeInput = document.getElementById('end_datetime');

            startDateTimeInput.addEventListener('change', function() {
                const startDateTime = new Date(startDateTimeInput.value);
                endDateTimeInput.min = startDateTimeInput.value;

                if (endDateTimeInput.value && new Date(endDateTimeInput.value) < startDateTime) {
                    endDateTimeInput.value = '';
                }
            });

            endDateTimeInput.addEventListener('change', function() {
                const startDateTime = new Date(startDateTimeInput.value);
                const endDateTime = new Date(endDateTimeInput.value);

                if (endDateTime < startDateTime) {
                    alert('End date and time cannot be earlier than start date and time.');
                    endDateTimeInput.value = '';
                }
            });
        });
    </script>
    
    <script src="./js/script.js"></script>
</body>
</html>
