<?php require_once('db-connect.php'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Boys Scheduling</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <style>
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }

        html,
        body {
            height: 100%;
            width: 100%;
            font-family: Apple Chancery, cursive;
            background-color: #222222;
            color: #f0f0f0;
        }

        .btn-info.text-light:hover,
        .btn-info.text-light:focus {
            background: #000;
        }
        table, tbody, td, tfoot, th, thead, tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }

        .navbar-brand {
            color: #FC5700;
        }

        .navbar-dark .navbar-dark,
        .card-title {
            color: #f0f0f0 !important;
        }

        .navbar-dark .navbar-brand:hover,
        .navbar-dark .text-light:hover,
        .text-light:hover {
            color: #FC5700 !important;
        }

        .bg-primary {
            background-color: #FC5700;
        }

        .btn-primary {
            background-color: #FC5700;
            border-color: #FC5700;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: #cc8400;
            border-color: #cc8400;
        }

        .modal-header,
        .modal-body,
        .modal-footer {
            background-color: #333333;
        }

        .modal-header .modal-title,
        .modal-footer .btn-secondary {
            color: white;
        }

        .btn-primary {
            background-color: #FC5700;
            border-color: #FC5700;
        }
        .control-label {
            color: #f0f0f0;
        }

        #calendar .fc-event {
            background-color: red !important;
            border-color: red !important;
            color: #f0f0f0 !important;
        }

        .card-header {
            background-color: #FC5700;
            color: #FC5700;
        }

        .card-body {
            background-color: #333333;
            color: #f0f0f0;
        }

        .form-control {
            background-color: #555555;
            color: #f0f0f0;
            border-color: #777777;
        }

        .form-control:focus {
            background-color: #666666;
            color: #f0f0f0;
            border-color: #FC5700;
        }

        .btn-default {
            background-color: #444444;
            border-color: #777777;
            color: #f0f0f0;
        }

        .btn-default:hover,
        .btn-default:focus {
            background-color: #555555;
            border-color: #FC5700;
            color: #f0f0f0;
        }

        .text-light {
            color: black !important;
        }

        .container {
            background-color: #1a1a1a;
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
                    <div class="card-header bg-gradient bg-primary text-light">
                        <h5 class="card-title">Schedule Form</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form action="save_schedule.php" method="post" id="schedule-form">
                                <input type="hidden" name="id" value="" disabled>
                                <div class="form-group mb-2">
                                    <label for="title" class="control-label">Reserver's name</label>
                                    <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" required disabled>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="description" class="control-label">Description</label>
                                    <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="description" required disabled></textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="start_datetime" class="control-label">Start</label>
                                    <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_datetime" id="start_datetime" required disabled>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="end_datetime" class="control-label">End</label>
                                    <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_datetime" id="end_datetime" required disabled>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-center">
                            <button class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form" disabled><i class="fa fa-save" href="schedule.php"></i> Save</button>
                            <button class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form" disabled><i class="fa fa-reset"></i> Cancel</button>
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
                            <dt class="text-muted">Description</dt>
                            <dd id="description" class=""></dd>
                            <dt class="text-muted">Start</dt>
                            <dd id="start" class=""></dd>
                            <dt class="text-muted">End</dt>
                            <dd id="end" class=""></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="" disabled>Edit</button>
                        <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="" disabled>Delete</button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Event Details Modal -->

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
</body>
<script>
    var scheds = $.parseJSON('<?= json_encode($sched_res) ?>')
</script>
<script src="./js/script.js"></script>

</html>
