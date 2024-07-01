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
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
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
            font-family: 'Apple Chancery', cursive;
            background-color: #222222;
            color: #f0f0f0;
            overflow-x: hidden;
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
            color: #F57C00 !important;
        }

        .bg-primary {
            background-color: #F57C00 !important;
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
            color: #F57C00;
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
            border-color: #F57C00;
        }

        .btn-default {
            background-color: #444444;
            border-color: #777777;
            color: #f0f0f0;
        }

        .btn-default:hover,
        .btn-default:focus {
            background-color: #555555;
            border-color: #F57C00;
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

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }

        .sidebar a {
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            text-decoration: none;
            color: #222222;
            padding: 15px 20px;
            text-align: center;
            width: 80%;
            margin: 10px 0;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            color: black;
            font-weight: bold;
            transform: translateY(0);
        }

        .sidebar a:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content.shifted {
            margin-left: 0;
        }

        .toggle-sidebar-btn {
            position: fixed;
            top: 20px;
            left: 260px;
            background-color: #f56C00;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 8px;
            transition: transform 0.3s;
            z-index: 1000;
        }

        .toggle-sidebar-btn.hidden {
            left: 20px;
            transform: rotate(180deg);
        }
        .logout-button{
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
    </style>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            const toggleBtn = document.querySelector('.toggle-sidebar-btn');
            sidebar.classList.toggle('hidden');
            content.classList.toggle('shifted');
            toggleBtn.classList.toggle('hidden');
        }
    </script>
</head>

<body>
    <div class="sidebar">
        <p href="#" class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="#" onclick="loadPage('Sched-admin.php')">Schedule Settings</a>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="content">
        <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
        <div id="content-container" class="content-container">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient" id="topNavBar">
                <div class="container">
                    <a class="navbar-brand" href="#">Ballers Hub - Admin</a>
                    <div>
                        <a class="text-light" href="login.php">Logout</a>
                    </div>
                </div>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-md-12">
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
            <div class="modal fade" id="schedule-modal" tabindex="-1" aria-labelledby="schedule-modal-label" aria-hidden="true">
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
                            <button type="submit" class="btn btn-primary btn-sm rounded-0" form="schedule-form"><i class="fa fa-save"></i> Save</button>
                            <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Schedule Modal -->
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
                var title = $(this).attr('data-name');
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
                            location.reload();
                        } else {
                            alert('Failed to save schedule.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr);
                        alert('An error occurred: ' + error);
                    }
                });
            });
        });
    </script>
</body>

</html>
