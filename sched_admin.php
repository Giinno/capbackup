<?php require_once('db-connect.php'); ?>
<?php
session_start();

// Check if the user is logged in and is a Scheduling-admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Scheduling-admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Boys Scheduling - Admin</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <style>
        /* Add your styles here */
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }

        html,
        body {
            height: 100%;
            width: 100%;
            font-family: 'Apple Chancery', cursive;
            background-color: #222222;
            color: #f0f0f0;
        }

        .btn-info.text-light:hover,
        .btn-info.text-light:focus {
            background: #000;
        }

        table,
        tbody,
        td,
        tfoot,
        th,
        thead,
        tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }

        .navbar-dark .navbar-brand,
        .navbar-dark .text-light,
        .text-light,
        .card-title {
            color: #f0f0f0 !important;
        }

        .navbar-dark .navbar-brand:hover,
        .navbar-dark .text-light:hover {
            color: #F57C00 !important; /* Changed to orange */
        }

        .bg-primary {
            background-color: #F57C00 !important; /* Changed to orange */
        }

        .btn-primary {
            background-color: #F57C00;
            border-color: #F57C00;
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
            background-color: #F57C00;
            border-color: #F57C00;
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
            background-color: #444444;
            color: #F57C00; /* Changed to orange */
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
            border-color: #F57C00; /* Changed to orange */
        }

        .btn-default {
            background-color: #444444;
            border-color: #777777;
            color: #f0f0f0;
        }

        .btn-default:hover,
        .btn-default:focus {
            background-color: #555555;
            border-color: #F57C00; /* Changed to orange */
            color: #f0f0f0;
        }

        .text-light {
            color: black !important;
            font-weight: 600;
            font-size: 18px;
        }

        th,
        td {
            color: white;
        }
    </style>
</head>

<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient" id="topNavBar">
        <div class="container">
            <a class="navbar-brand" href="#">
                Ballers Hub - Admin
            </a>
            <div>
                <a class="text-light" href="login.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3" style="width: 245px;">
                <div class="bg-primary p-3">
                    <a href="#" class="text-light">Schedule Settings</a>
                </div>
            </div>
            <!-- /Sidebar -->
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container py-5" id="page-container">
                    <h1>Welcome to the Scheduling Admin Dashboard, <?= $_SESSION['username'] ?>!</h1>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card rounded-0 shadow">
                                <div class="card-header bg-gradient bg-primary text-light">
                                    <h5 class="card-title">Manage Schedules</h5>
                                </div>
                                <div class="card-body">
                                    <div class="container-fluid">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Reserver's Name</th>
                                                    <th>Description</th>
                                                    <th>Start</th>
                                                    <th>End</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="schedule-list">
                                                <?php 
                                                    $schedules = $conn->query("SELECT * FROM `schedule_list`");
                                                    $i = 1;
                                                    while($row = $schedules->fetch_assoc()):
                                                ?>
                                                <tr id="schedule-<?= $row['title'] ?>">
                                                    <td><?= $i++ ?></td>
                                                    <td><?= $row['title'] ?></td>
                                                    <td><?= $row['description'] ?></td>
                                                    <td><?= date("F d, Y h:i A", strtotime($row['start_datetime'])) ?></td>
                                                    <td><?= date("F d, Y h:i A", strtotime($row['end_datetime'])) ?></td>
                                                    <td>
                                                        <button class="btn btn-primary btn-sm edit-schedule"
                                                            data-id="<?= $row['id'] ?>"><i class="fa fa-edit"></i></button>
                                                        <button class="btn btn-danger btn-sm delete-schedule"
                                                            data-name="<?= $row['title'] ?>"><i class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#schedule-modal"><i class="fa fa-plus"></i> Add Schedule</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Main Content -->
        </div>
    </div>
    <!-- Schedule Modal -->
    <div class="modal fade" id="schedule-modal" tabindex="-1" aria-labelledby="schedule-modal-label"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schedule-modal-label">Schedule Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="save_schedule.php" method="post" id="schedule-form">
                        <input type="hidden" name="id" id="schedule-id">
                        <div class="form-group mb-2">
                            <label for="title" class="control-label">Reserver's name</label>
                            <input type="text" class="form-control form-control-sm rounded-0" name="title"
                                id="title" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="description" class="control-label">Description</label>
                            <textarea rows="3" class="form-control form-control-sm rounded-0" name="description"
                                id="description" required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label for="start_datetime" class="control-label">Start</label>
                            <input type="datetime-local" class="form-control form-control-sm rounded-0"
                                name="start_datetime" id="start_datetime" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="end_datetime" class="control-label">End</label>
                            <input type="datetime-local" class="form-control form-control-sm rounded-0"
                                name="end_datetime" id="end_datetime" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm rounded-0"
                        form="schedule-form"><i class="fa fa-save"></i> Save</button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-0"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Schedule Modal -->

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

        $(document).ready(function(){
            $('.edit-schedule').click(function(){
                var id = $(this).attr('data-id');
                var schedule = scheds[id];
                $('#schedule-id').val(schedule.id);
                $('#title').val(schedule.title);
                $('#description').val(schedule.description);
                $('#start_datetime').val(schedule.start_datetime);
                $('#end_datetime').val(schedule.end_datetime);
                $('#schedule-modal').modal('show');
            });

            $('.delete-schedule').click(function(){
                var title = $(this).attr('data-name'); // Get the title attribute
                if(confirm('Are you sure you want to delete this schedule?')){
                    $.ajax({
                        url: 'del_schedule.php',
                        type: 'POST',
                        data: {title: title},
                        success: function(response){
                            if(response == 1){
                                $('#schedule-' + title).remove();
                                alert('Schedule deleted successfully.');
                            } else {
                                alert('Failed to delete schedule.');
                            }
                        }
                    });
                }
            });

            $('#schedule-form').submit(function(e){
                e.preventDefault();
                $.ajax({
                    url: 'save_schedule.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response){
                        var data = JSON.parse(response);
                        if(data.status == 1){
                            alert('Schedule saved successfully.');
                            location.reload(); // Reload the page to reflect the changes
                        } else {
                            alert('Failed to save schedule.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr); // For debugging
                        alert('An error occurred: ' + error);
                    }
                });
            });
        });
    </script>
</body>

</html>
