<?php
session_start();
require_once('db-connect.php');

// Check if the user is logged in and is a Scheduling-admin
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'scheduling-admin') {
    header("Location: login.php");
    exit;
}

// Fetch schedules from the database
$sched_res = [];
$schedules = $conn->query("SELECT id, title, description, start_datetime, end_datetime, status, amount_paid FROM `schedule_list`");
if ($schedules) {
    while ($row = $schedules->fetch_assoc()) {
        // Format dates for display and JSON data
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
        $row['start_datetime'] = date("Y-m-d\TH:i:s", strtotime($row['start_datetime'])); // ISO 8601 format
        $row['end_datetime'] = date("Y-m-d\TH:i:s", strtotime($row['end_datetime'])); // ISO 8601 format
        $sched_res[] = $row;
    }
} else {
    echo "Error fetching schedules: " . $conn->error;
}
if (isset($conn)) $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Boys Scheduling - Admin</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <link rel="stylesheet" href="./css/sched-admin.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var confirmedEvents = <?php echo json_encode(array_values(array_filter($sched_res, function($row) {
                return $row['status'] == 'confirmed';
            }))); ?>;

            // Ensure confirmedEvents is an array
            if (!Array.isArray(confirmedEvents)) {
                confirmedEvents = [];
            }

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: confirmedEvents.map(function(row) {
                    return {
                        id: row.id,
                        title: row.title,
                        start: row.start_datetime,
                        end: row.end_datetime,
                        description: row.description
                    };
                }),
                eventClick: function(info) {
                    alert('Event: ' + info.event.title + '\n' +
                          'Description: ' + info.event.extendedProps.description);
                }
            });
            calendar.render();

            // Handle action buttons
            $(document).on('click', '.edit-schedule, .delete-schedule, .confirm-schedule, .cancel-schedule', function() {
                var scheduleId = $(this).data('id');
                var action = $(this).data('action');
                
                if (action === 'delete') {
                    if (confirm('Are you sure you want to delete this schedule?')) {
                        $.ajax({
                            url: 'schedule_actions.php',
                            method: 'POST',
                            data: { action: 'delete', id: scheduleId },
                            success: function(response) {
                                $('#schedule-' + scheduleId).remove();
                                alert(response.message);
                            }
                        });
                    }
                } else if (action === 'confirm' || action === 'cancel') {
                    $.ajax({
                        url: 'schedule_actions.php',
                        method: 'POST',
                        data: { action: action, id: scheduleId },
                        success: function(response) {
                            alert(response.message);
                            location.reload();
                        }
                    });
                } else if ($(this).hasClass('edit-schedule')) {
                    // Open the edit modal and populate fields
                    $.ajax({
                        url: 'get_schedule.php',
                        method: 'GET',
                        data: { id: scheduleId },
                        success: function(response) {
                            $('#schedule-id').val(response.id);
                            $('#title').val(response.title);
                            $('#description').val(response.description);
                            $('#start_datetime').val(response.start_datetime);
                            $('#end_datetime').val(response.end_datetime);
                            $('#schedule-modal').modal('show');
                        }
                    });
                }
            });

            // Handle amount paid updates
            $(document).on('change', '.amount-paid', function() {
                var scheduleId = $(this).data('id');
                var amountPaid = $(this).val();

                $.ajax({
                    url: 'update_payment.php',
                    method: 'POST',
                    data: {
                        id: scheduleId,
                        amount_paid: amountPaid
                    },
                    success: function(response) {
                        alert(response.message);
                    }
                });
            });
        });
    </script>
</head>

<body>
    <div class="sidebar">
        <p href="#" class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="#" onclick="loadPage('sched-admin.php')">Schedule Settings</a>
    </div>
    <div class="content">
        <div id="content-container" class="content-container">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient" id="topNavBar">
                <div class="container">
                    <a class="navbar-brand" href="#">Ballers Hub - Admin</a>
                    <div>
                        <a class="text-light" href="logout.php">Logout</a>
                    </div>
                </div>
            </nav>
            <div class="container-fluid">
                <div class="row">
                    <!-- Main Content -->
                    <div class="col-md-12">
                        <div class="container py-5" id="page-container">
                            <h1>Welcome to the Scheduling Admin Dashboard, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Calendar Integration -->
                                    <div class="card rounded-0 shadow mb-4">
                                        <div class="card-header bg-gradient bg-primary text-light">
                                            <h5 class="card-title">Calendar</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="calendar"></div>
                                        </div>
                                    </div>
                                    <!-- /Calendar Integration -->
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
                                                            <th>Status</th>
                                                            <th>Amount Paid</th> <!-- New column -->
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="schedule-list">
                                                        <?php 
                                                            $i = 1;
                                                            foreach ($sched_res as $row):
                                                        ?>
                                                        <tr id="schedule-<?= $row['id'] ?>">
                                                            <td><?= $i++ ?></td>
                                                            <td><?= htmlspecialchars($row['title']) ?></td>
                                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                                            <td><?= htmlspecialchars($row['sdate']) ?></td>
                                                            <td><?= htmlspecialchars($row['edate']) ?></td>
                                                            <td>
                                                                <?php if ($row['status'] == 'confirmed'): ?>
                                                                    <span class="badge badge-success">Confirmed</span>
                                                                <?php elseif ($row['status'] == 'pending'): ?>
                                                                    <span class="badge badge-secondary">Pending</span>
                                                                <?php else: ?>
                                                                    <span class="badge badge-danger">Canceled</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <!-- Input form for amount paid -->
                                                                <input type="number" class="form-control amount-paid" data-id="<?= $row['id'] ?>" value="<?= htmlspecialchars($row['amount_paid']) ?>" step="0.01" min="0" />
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-primary btn-sm edit-schedule" data-id="<?= $row['id'] ?>">
                                                                    <i class="fa fa-edit"></i> Edit
                                                                </button>
                                                                <button class="btn btn-danger btn-sm delete-schedule" data-id="<?= $row['id'] ?>" data-action="delete">
                                                                    <i class="fa fa-trash"></i> Delete
                                                                </button>
                                                                <?php if ($row['status'] == 'pending'): ?>
                                                                    <button class="btn btn-success btn-sm confirm-schedule" data-id="<?= $row['id'] ?>" data-action="confirm">
                                                                        <i class="fa fa-check"></i> Confirm
                                                                    </button>
                                                                    <button class="btn btn-secondary btn-sm cancel-schedule" data-id="<?= $row['id'] ?>" data-action="cancel">
                                                                        <i class="fa fa-times"></i> Cancel
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <?php if (count($sched_res) == 0): ?>
                                                        <tr>
                                                            <td colspan="8" class="text-center">No schedules available.</td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Manage Schedules -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Main Content -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>
