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
$current_datetime = date('Y-m-d H:i:s');
$stmt = $conn->prepare("SELECT id, title, description, start_datetime, end_datetime, status, amount_paid, event_type, receipt_number FROM `schedule_list` WHERE end_datetime > ? OR status IN ('pending', 'canceled') ORDER BY start_datetime DESC");
if ($stmt) {
    $stmt->bind_param("s", $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
        $sched_res[] = $row;
    }
    $stmt->close();
} else {
    error_log("Error preparing statement: " . $conn->error);
}

// Fetch all schedules for history
$all_sched_res = [];
$stmt = $conn->prepare("SELECT id, title, description, start_datetime, end_datetime, status, amount_paid, event_type, receipt_number FROM `schedule_list` ORDER BY start_datetime DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
        $all_sched_res[] = $row;
    }
    $stmt->close();
} else {
    error_log("Error preparing statement: " . $conn->error);
}

if (isset($conn)) $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - Admin</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/manage-schedule.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
                .modal-content {
                background-color: #1e1e1e;
                color: white;
                }

                .form-control, .form-select {
                    background-color: #2e2e2e;
                    color: white;
                    border-color: #3e3e3e;
                }

                .form-control::placeholder {
                    color: #aaa;
                }

                .table-dark {
                    background-color: #1e1e1e;
                }

                .btn-close-white {
                    filter: invert(1) grayscale(100%) brightness(200%);
                }
                .card-body {
                    background-color: #1e1e1e;
                }
                :root {
                --primary-color: #FF8C00;
                --secondary-color: #FFA500;
                --text-color: #FFFFFF;
                --text-color-muted: #CCCCCC;
                --background-color: #1E1E1E;
                --sidebar-bg: #2C2C2C;
                --card-bg: #333333;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .sidebar {
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            padding-top: 20px;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar .navbar-brand {
            color: var(--primary-color);
            font-size: 24px;
            padding: 20px;
            text-align: center;
            font-weight: 600;
        }
        .sidebar a {
            padding: 15px 20px;
            color: var(--text-color);
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color: var(--primary-color);
            color: var(--background-color);
        }
        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content {
                margin-left: 0;
            }
            .logout-btn {
                position: static;
                margin-top: 20px;
            }
        }
        #bookingNotificationModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        #bookingNotificationModal .modal-content {
            background-color: var(--sidebar-bg);
            margin: 15% auto;
            padding: 20px;
            border: 1px solid var(--primary-color);
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            color: var(--text-color);
        }
        #bookingNotificationModal .close {
            color: var(--text-color-muted);
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        #bookingNotificationModal .close:hover,
        #bookingNotificationModal .close:focus {
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
        }
</style>
<body>
    <div class="sidebar w-64 space-y-6  absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
            <div class="flex items-center justify-center mb-2">
                <img src="./images/Logo.png" alt="Ballers Hub Logo" class="w-12 h-12 mr-2">
                <h1 class="text-2xl font-semibold text-orange-500">Ballers Hub</h1>
            </div>
            <nav>
                <a href="sched-admin-dashboard.php" class="sidebar-item flex items-center rounded-lg">
                    <i class="fas fa-user-cog text-l"></i>
                    <span>Scheduling Dashboard</span>
                </a>
                <a href="sched-admin.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fas fa-tasks text-l"></i>
                    <span>Schedule Calendar</span>
                </a>
                <a href="manage-schedule.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-chart-bar text-l"></i>
                    <span>Manage Schedule</span>
                </a>
                <a href="sched-report.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-sign-out-alt text-l"></i>
                    <span>Scheduling Report</span>
                </a>
                <a href="league-settings.php" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg">
                    <i class="fas fa-basketball-ball text-l"></i>
                    <span>Schedule Leagues</span>
                </a>
                <button onclick="logout()" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg mt-auto w-full">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span>Logout</span>
        </button>
            </nav>
        </div>
    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color: #1E1E1E;">
            <div class="container-fluid" style="background-color: #1E1E1E;">
            <h4 class="navbar-brand" href="#"><?= htmlspecialchars($_SESSION['username']) ?></h4>
            </div>
        </nav>

        <div class="container-fluid">
        <h1 class="mb-4">Manage Schedules</h1>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#scheduleHistoryModal">
            View Schedule History
        </button>
        <div class="card shadow mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">Active, Upcoming, and Canceled Schedules</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="background-color: #1e1e1e; color: white;">Event Type</th>
                                <th style="background-color: #1e1e1e; color: white;">Reserver's Name</th>
                                <th style="background-color: #1e1e1e; color: white;">Description</th>
                                <th style="background-color: #1e1e1e; color: white;">
                                    Start
                                    <button class="btn btn-sm btn-outline-light sort-btn" data-sort="start">
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </th>
                                <th style="background-color: #1e1e1e; color: white;">End</th>
                                <th style="background-color: #1e1e1e; color: white;">Status</th>
                                <th style="background-color: #1e1e1e; color: white;">Amount Paid</th>
                                <th style="background-color: #1e1e1e; color: white;">Receipt Number</th>
                                <th style="background-color: #1e1e1e; color: white;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="schedule-list">
                            <?php if (empty($sched_res)): ?>
                            <tr>
                                <td colspan="9" class="text-center">No schedules available.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($sched_res as $row): ?>
                                <tr id="schedule-<?= htmlspecialchars($row['id']) ?>">
                                    <td style="background-color: #1e1e1e; color: white;">
                                        <select class="form-select event-type" data-id="<?= htmlspecialchars($row['id']) ?>" <?= $row['status'] === 'canceled' ? '' : '' ?>>
                                            <option value="City-wide" <?= $row['event_type'] == 'City-wide' ? 'selected' : '' ?>>City-wide</option>
                                            <option value="Barangay" <?= $row['event_type'] == 'Barangay' ? 'selected' : '' ?>>Barangay</option>
                                            <option value="National" <?= $row['event_type'] == 'National' ? 'selected' : '' ?>>National</option>
                                        </select>
                                    </td>
                                    <td style="background-color: #1e1e1e; color: white;"><?= htmlspecialchars($row['title']) ?></td>
                                    <td style="background-color: #1e1e1e; color: white;"><?= htmlspecialchars($row['description']) ?></td>
                                    <td style="background-color: #1e1e1e; color: white;" data-sort-value="<?= strtotime($row['start_datetime']) ?>"><?= htmlspecialchars($row['sdate']) ?></td>
                                    <td style="background-color: #1e1e1e; color: white;"><?= htmlspecialchars($row['edate']) ?></td>
                                    <td style="background-color: #1e1e1e; color: white;">
                                        <?php
                                        $status_class = [
                                            'confirmed' => 'bg-success',
                                            'pending' => 'bg-secondary',
                                            'canceled' => 'badge-canceled'
                                        ];
                                        $status = htmlspecialchars($row['status']);
                                        $class = $status_class[$status] ?? 'bg-info';
                                        ?>
                                        <span class="badge <?= $class ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td style="background-color: #1e1e1e; color: white;">
                                        <input type="number" class="form-control amount-paid" data-id="<?= htmlspecialchars($row['id']) ?>" value="<?= htmlspecialchars($row['amount_paid']) ?>" step="0.01" min="0" <?= $row['status'] === 'canceled' ? 'disabled' : '' ?> />
                                    </td>
                                    <td class="receipt-number" style="background-color: #1e1e1e; color: white;">
                                        <?= !empty($row['receipt_number']) ? htmlspecialchars($row['receipt_number']) : 'N/A' ?>
                                    </td>
                                    <td style="background-color: #1e1e1e; color: white;">
                                        <button class="btn btn-primary btn-sm edit-schedule" data-id="<?= htmlspecialchars($row['id']) ?>" data-bs-toggle="modal" data-bs-target="#editScheduleModal" <?= $row['status'] === 'canceled' ? 'disabled' : '' ?>><i class="fa fa-edit"></i> Edit</button>
                                        <button class="btn btn-danger btn-sm delete-schedule" data-id="<?= htmlspecialchars($row['id']) ?>" data-action="delete" <?= $row['status'] === 'canceled' ? '' : '' ?>><i class="fa fa-trash"></i> Delete</button>
                                        <?php if ($row['status'] == 'pending'): ?>
                                            <button class="btn btn-success btn-sm confirm-schedule" data-id="<?= htmlspecialchars($row['id']) ?>" data-action="confirm"><i class="fa fa-check"></i> Confirm</button>
                                            <button class="btn btn-secondary btn-sm cancel-schedule" data-id="<?= htmlspecialchars($row['id']) ?>" data-action="cancel"><i class="fa fa-times"></i> Cancel</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Schedule History Modal -->
<div class="modal fade" id="scheduleHistoryModal" tabindex="-1" aria-labelledby="scheduleHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background-color: #1e1e1e; color: white;">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleHistoryModalLabel">Schedule History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control bg-dark text-white" id="scheduleHistorySearch" placeholder="Search schedules...">
                </div>
                <div class="mb-3">
                    <select class="form-select bg-dark text-white" id="statusFilter">
                        <option value="confirmed">Confirmed</option>
                        <option value="all">All Statuses</option>
                        <option value="canceled">Canceled</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-dark">
                        <thead>
                            <tr>
                                <th>Event Type</th>
                                <th>Reserver's Name</th>
                                <th>Description</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                                <th>Amount Paid</th>
                                <th>Receipt Number</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleHistoryList">
                            <?php foreach ($all_sched_res as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['event_type']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['sdate']) ?></td>
                                <td><?= htmlspecialchars($row['edate']) ?></td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'confirmed' => 'bg-success',
                                        'pending' => 'bg-secondary',
                                        'canceled' => 'badge-canceled'
                                    ];
                                    $status = htmlspecialchars($row['status']);
                                    $class = $status_class[$status] ?? 'bg-info';
                                    ?>
                                    <span class="badge <?= $class ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['amount_paid']) ?></td>
                                <td><?= !empty($row['receipt_number']) ? htmlspecialchars($row['receipt_number']) : 'N/A' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
                    <button type="button" class="btn-close btn-secondary" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editScheduleForm">
                        <input type="hidden" id="editScheduleId" name="id">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Reserver's Name</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editStartDatetime" class="form-label">Start Date and Time</label>
                            <input type="datetime-local" class="form-control" id="editStartDatetime" name="start_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEndDatetime" class="form-label">End Date and Time</label>
                            <input type="datetime-local" class="form-control" id="editEndDatetime" name="end_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEventType" class="form-label">Event Type</label>
                            <select class="form-select" id="editEventType" name="event_type" required>
                                <option value="City-wide">City-wide</option>
                                <option value="Barangay">Barangay</option>
                                <option value="National">National</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveScheduleChanges">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <div id="bookingNotificationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeNotificationModal()">&times;</span>
            <h2 class="text-2xl font-bold mb-4 text-orange-500">New Booking Notification</h2>
            <p class="mb-2">Booked by: <span id="bookingUser"></span></p>
            <p class="mb-2">Title: <span id="bookingTitle"></span></p>
            <p class="mb-2">Date & Time: <span id="bookingDateTime"></span></p>
            <p class="mb-2">Event Type: <span id="bookingType"></span></p>
            <p class="mb-2">Amount Paid: <span id="bookingAmount"></span></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
    // Set default filter to "confirmed" and apply initial filter
    $("#statusFilter").val("confirmed");
    filterSchedule("", "confirmed");

    // Schedule History Search and Filter
    $("#scheduleHistorySearch, #statusFilter").on("keyup change", function() {
        var searchValue = $("#scheduleHistorySearch").val().toLowerCase();
        var status = $("#statusFilter").val();
        filterSchedule(searchValue, status);
    });

    function filterSchedule(search, status) {
        $("#scheduleHistoryList tr").each(function() {
            var $row = $(this);
            var rowText = $row.text().toLowerCase();
            var rowStatus = $row.find('td:eq(5) .badge').text().trim().toLowerCase();
            var statusMatch = status === 'all' || rowStatus === status;
            var searchMatch = rowText.indexOf(search) > -1;
            $row.toggle(searchMatch && statusMatch);
        });
    }

    // Update Amount Paid
    $(".amount-paid").on("change", function() {
        var scheduleId = $(this).data('id');
        var amountPaid = $(this).val();
        updateAmountPaid(scheduleId, amountPaid);
    });

    function updateAmountPaid(scheduleId, amountPaid) {
        $.ajax({
            url: 'update_amount_paid.php',
            method: 'POST',
            data: { id: scheduleId, amount_paid: amountPaid },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Amount paid updated successfully'
                    });
                    if (response.receipt_number) {
                        $('#schedule-' + scheduleId + ' .receipt-number').text(response.receipt_number);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update amount paid: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating amount paid:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the amount paid. Please try again.'
                });
            }
        });
    }

    // Edit Schedule
    $(".edit-schedule").on("click", function() {
        var scheduleId = $(this).data('id');
        $.ajax({
            url: 'get_schedule.php',
            method: 'GET',
            data: { id: scheduleId },
            dataType: 'json',
            success: function(response) {
                $('#editScheduleId').val(response.id);
                $('#editTitle').val(response.title);
                $('#editDescription').val(response.description);
                $('#editStartDatetime').val(response.start_datetime.slice(0, 16));
                $('#editEndDatetime').val(response.end_datetime.slice(0, 16));
                $('#editEventType').val(response.event_type);
                $('#editAmountPaid').val(response.amount_paid);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching schedule details:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while fetching schedule details. Please try again.'
                });
            }
        });
    });

    $('#saveScheduleChanges').on('click', function() {
        var formData = $('#editScheduleForm').serialize();
        $.ajax({
            url: 'update_schedule.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Schedule updated successfully'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#editScheduleModal').modal('hide');
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update schedule: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating schedule:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the schedule. Please try again.'
                });
            }
        });
    });

    // Confirm and Cancel Schedule
    $(".confirm-schedule, .cancel-schedule").on("click", function() {
        var scheduleId = $(this).data('id');
        var status = $(this).hasClass('confirm-schedule') ? 'confirmed' : 'canceled';
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to " + status + " this schedule.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, ' + status + ' it!'
        }).then((result) => {
            if (result.isConfirmed) {
                updateScheduleStatus(scheduleId, status);
            }
        });
    });

    function updateScheduleStatus(scheduleId, status) {
        $.ajax({
            url: 'update_schedule_status.php',
            method: 'POST',
            data: { id: scheduleId, status: status },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    var $row = $('#schedule-' + scheduleId);
                    $row.find('.badge').removeClass('bg-secondary bg-success badge-canceled')
                        .addClass(status === 'confirmed' ? 'bg-success' : 'badge-canceled')
                        .text(status.charAt(0).toUpperCase() + status.slice(1));
                    
                    // Remove confirm and cancel buttons
                    $row.find('.confirm-schedule, .cancel-schedule').remove();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Schedule status updated successfully'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update schedule status: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating schedule status:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the schedule status. Please try again.'
                });
            }
        });
    }

    // Update Event Type
    $(".event-type").on("change", function() {
        var scheduleId = $(this).data('id');
        var eventType = $(this).val();
        updateEventType(scheduleId, eventType);
    });

    function updateEventType(scheduleId, eventType) {
        $.ajax({
            url: 'update_event_type.php',
            method: 'POST',
            data: { id: scheduleId, event_type: eventType },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Event type updated successfully'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update event type: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating event type:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the event type. Please try again.'
                });
            }
        });
    }

    // Delete Schedule
    $(".delete-schedule").on("click", function() {
        var scheduleId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteSchedule(scheduleId);
            }
        });
    });

    function deleteSchedule(scheduleId) {
        $.ajax({
            url: 'delete_schedule.php',
            method: 'POST',
            data: { id: scheduleId },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    $('#schedule-' + scheduleId).remove(); // Remove the row from the table
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Schedule has been deleted.'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to delete schedule: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting schedule:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while deleting the schedule. Please try again.'
                });
            }
        });
    }

    // Sorting function
    $('.sort-btn').on('click', function() {
        var column = $(this).data('sort');
        var $tbody = $('#schedule-list');
        var rows = $tbody.find('tr').get();
        
        rows.sort(function(a, b) {
            var A = $(a).children('td').eq(3).data('sort-value');
            var B = $(b).children('td').eq(3).data('sort-value');
            
            if(A < B) {
                return -1;
            }
            if(A > B) {
                return 1;
            }
            return 0;
        });
        
        $.each(rows, function(index, row) {
            $tbody.append(row);
        });
    });
});

// Set up SSE for real-time notifications
const evtSource = new EventSource('sse_notifications.php');

evtSource.addEventListener('newBooking', function(event) {
    const booking = JSON.parse(event.data);
    showNotification(booking);
    // Optionally, you can add the new booking to the calendar here
    calendar.addEvent({
        id: booking.id,
        title: booking.title,
        start: booking.start_datetime,
        end: booking.end_datetime,
        backgroundColor: '#FF8C00',
        borderColor: '#FF8C00',
        extendedProps: {
            description: booking.description,
            amount_paid: booking.amount_paid
        }
    });
});

evtSource.onerror = function(err) {
    console.error("EventSource failed:", err);
};

// Close the EventSource when the page is unloaded
window.addEventListener('beforeunload', function() {
    evtSource.close();
});

function showNotification(booking) {
    Swal.fire({
        title: 'New Booking Notification',
        html: `
            <p><strong>Booked by:</strong> ${booking.first_name} ${booking.last_name}</p>
            <p><strong>Title:</strong> ${booking.title}</p>
            <p><strong>Date & Time:</strong> ${new Date(booking.start_datetime).toLocaleString()}</p>
            <p><strong>Event Type:</strong> ${booking.event_type}</p>
            <p><strong>Amount Paid:</strong> ₱${booking.amount_paid}</p>
        `,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function logout() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out of the system.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log out!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('logout.php')
                .then(response => {
                    if (response.ok) {
                        Swal.fire({
                            title: 'Logged Out!',
                            text: 'You have been successfully logged out.',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Logout failed. Please try again.',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'An unexpected error occurred. Please try again.',
                        icon: 'error'
                    });
                });
        }
    });
}
    </script>
</body>
</html>
