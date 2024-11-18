<?php
session_start();
require_once('db-connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch only confirmed schedules
$schedules = $conn->query("SELECT * FROM `schedule_list` WHERE status = 'confirmed'");
$sched_res = [];
foreach($schedules->fetch_all(MYSQLI_ASSOC) as $row){
    $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
    $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
    $sched_res[$row['id']] = $row;
}
if(isset($conn)) $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballers Hub Scheduling</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <style>
        :root {
            --primary-orange: #FC5700;
            --text-primary: #ffffff;
            --text-secondary: #e0e0e0;
            --bg-dark: #121212;
            --bg-card: #1e1e1e;
            --bg-input: #2e2e2e;
            --border-color: #4e4e4e;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }
        .navbar {
            background-color: var(--bg-card) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            padding: 1rem 0;
        }
        .navbar-brand {
            color: var(--primary-orange) !important;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .nav-link {
            color: var(--text-primary) !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--primary-orange) !important;
        }
        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .card-header {
            background-color: var(--primary-orange);
            color: var(--text-primary);
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }
        .card-body {
            padding: 1.5rem;
        }
        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--bg-input);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 2px rgba(252, 87, 0, 0.25);
            color: var(--text-primary);
        }
        .btn-primary {
            background-color: var(--primary-orange);
            border: none;
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #e04e00;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        #calendar {
            background-color: var(--bg-card);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .fc {
            background-color: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
        }
        .fc-theme-standard td, 
        .fc-theme-standard th {
            border-color: var(--border-color);
        }
        .fc-theme-standard .fc-scrollgrid {
            border-color: var(--border-color);
        }
        .fc-col-header-cell-cushion, 
        .fc-daygrid-day-number {
            color: var(--text-primary);
            text-decoration: none;
        }
        .fc-event {
            background-color: var(--primary-orange);
            border: none;
            border-radius: 4px;
            padding: 2px 4px;
        }
        .fc-event-title, 
        .fc-event-time {
            color: var(--text-primary);
        }
        .modal-content {
            background-color: var(--bg-card);
            border-radius: 12px;
        }
        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }
        .modal-footer {
            border-top: 1px solid var(--border-color);
        }
        dt {
            color: var(--primary-orange);
            font-weight: 600;
            margin-top: 1rem;
        }
        dd {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .text-info {
            color: var(--primary-orange) !important;
        }
        /* Enhanced flatpickr styles */
        .flatpickr-calendar {
            background-color: var(--bg-card) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }
        .flatpickr-day {
            color: var(--text-primary) !important;
            border-radius: 8px !important;
        }
        .flatpickr-day.selected {
            background-color: var(--primary-orange) !important;
            border-color: var(--primary-orange) !important;
        }
        .flatpickr-day:hover {
            background-color: var(--bg-input) !important;
        }
        .form-check-label {
            color: var(--text-secondary);
        }
        .text-danger {
            color: #ff6b6b !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#" style="color: #FC5700;">Ballers Hub</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div id="calendar"></div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Schedule Form</h5>
                    </div>
                    <div class="card-body">
                        <form action="save_schedule.php" method="post" id="schedule-form">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                            <div class="mb-3">
                                <label for="title" class="form-label">Reserver's Name</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Game Mode</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="game_mode" id="private_game" value="Private Game" checked>
                                    <label class="form-check-label" for="private_game">Private Game</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="game_mode" id="tournament_mode" value="Tournament Mode">
                                    <label class="form-check-label" for="tournament_mode">Tournament Mode</label>
                                </div>
                                <small id="tournament_info" class="text-danger" style="display:none;">Tournaments should be reserved 1 month before game day</small>
                            </div>
                            <div class="mb-3">
                                <label for="start_datetime" class="form-label">Start</label>
                                <input type="text" class="form-control flatpickr" name="start_datetime" id="start_datetime" required style="background-color: #333333;" >
                            </div>
                            <div class="mb-3">
                                <label for="end_datetime" class="form-label">End</label>
                                <input type="text" class="form-control flatpickr" name="end_datetime" id="end_datetime" required style="background-color: #333333;" >
                            </div>
                            <div class="text-center">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Save</button>
                                <button class="btn btn-secondary" type="reset"><i class="fas fa-undo"></i> Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Court Location & Contact</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Location:</strong> Golden Boys, Nunez Vitaliano Agan Ave, Zamboanga</p>
                        <p><strong>Google Maps:</strong> <a href="https://maps.app.goo.gl/XjA7G2DWEKQh8x2W8" target="_blank" class="text-info">View on Google Maps</a></p>
                        <p><strong>Contact Number:</strong> ###########</p>
                        <p><strong>Facebook/Messenger Name:</strong> ###########</p>
                        <p><strong>Gcash Number:</strong> ###########</p>
                     
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Event Details Modal -->
    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <dl>
                        <dt>Title</dt>
                        <dd id="title" class="fw-bold fs-4"></dd>
                        <dt>Game Mode</dt>
                        <dd id="description"></dd>
                        <dt>Start</dt>
                        <dd id="start"></dd>
                        <dt>End</dt>
                        <dd id="end"></dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        var scheds = <?php echo json_encode($sched_res); ?>;
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: Object.values(scheds).map(event => ({
                    id: event.id,
                    title: event.title,
                    start: event.start_datetime,
                    end: event.end_datetime,
                    extendedProps: {
                        description: event.description,
                        sdate: event.sdate,
                        edate: event.edate
                    }
                })),
                eventClick: function(info) {
                    var modal = new bootstrap.Modal(document.getElementById('event-details-modal'));
                    document.getElementById('title').textContent = info.event.title;
                    document.getElementById('description').textContent = info.event.extendedProps.description;
                    document.getElementById('start').textContent = info.event.extendedProps.sdate;
                    document.getElementById('end').textContent = info.event.extendedProps.edate;
                    modal.show();
                }
            });
            calendar.render();
            flatpickr(".flatpickr", {
                enableTime: true,
                dateFormat: "Y-m-d h:i K",
                time_24hr: false,
                minDate: "today",
                theme: "dark"
            });
            document.getElementById('start_datetime').addEventListener('change', function() {
                document.getElementById('end_datetime')._flatpickr.set('minDate', this.value);
            });
            document.querySelectorAll('input[name="game_mode"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('tournament_info').style.display = 
                        document.getElementById('tournament_mode').checked ? 'block' : 'none';
                });
            });
            document.getElementById('schedule-form').addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                // Check if it's tournament mode
                if (document.getElementById('tournament_mode').checked) {
                    var startDate = new Date(document.getElementById('start_datetime').value);
                    var oneMonthFromNow = new Date();
                    oneMonthFromNow.setMonth(oneMonthFromNow.getMonth() + 1);
                    
                    if (startDate < oneMonthFromNow) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Date',
                            text: 'Tournament mode reservations must be at least one month in advance.',
                        });
                        return;
                    }
                }

                fetch('save_schedule.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Schedule saved successfully! Your reservation is pending admin approval.',
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'An error occurred while saving the schedule.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'An error occurred while saving the schedule. Please try again.',
                    });
                });
            });
        });
    </script>
</body>
</html>
