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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Boys Scheduling - Admin</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js' rel='stylesheet' />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF8C00;
            --secondary-color: #FFA500;
            --text-color: #FFFFFF;
            --text-color-muted: #CCCCCC;
            --background-color: #1E1E1E;
            --sidebar-bg: #2C2C2C;
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
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
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
        }
        .navbar {
            background-color: var(--sidebar-bg);
        }
        .card {
            background-color: var(--sidebar-bg);
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: var(--primary-color);
            color: var(--background-color);
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        #calendar {
            background-color: var(--background-color);
            padding: 20px;
            border-radius: 15px;
            color: var(--text-color);
        }
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: var(--secondary-color);
        }
        .fc .fc-daygrid-day-number, .fc .fc-col-header-cell-cushion {
            color: var(--text-color);
        }
        .fc .fc-button {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--background-color);
        }
        .fc .fc-button:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .fc .fc-today-button {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--background-color);
        }
        .modal-content {
            background-color: var(--sidebar-bg);
            color: var(--text-color);
        }
        .modal-header, .modal-footer {
            border-color: var(--primary-color);
        }
        .btn-secondary {
            background-color: var(--text-color-muted);
            border-color: var(--text-color-muted);
            color: var(--background-color);
        }
        .btn-secondary:hover {
            background-color: var(--text-color);
            border-color: var(--text-color);
        }
        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }
        .fc-event-title , .fc-event-time , .fc-daygrid-event-dot {
            color: #FF8C00;
        }
        .fc-event-title-container {
            color: #ffffff;
        }
        .fc-list-sticky {
            color: #ffffff;
        }
        .fc-event-main {
            background-color: #1E1E1E;
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
        :root {
            --primary-orange: #ff8c00;
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
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title m-0">Calendar</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
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
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            console.error('Calendar element not found');
            return;
        }

        var confirmedEvents = <?php echo json_encode(array_values(array_filter($sched_res, function($row) {
            return $row['status'] == 'confirmed';
        }))); ?>;

        if (!Array.isArray(confirmedEvents)) {
            console.error('Confirmed events is not an array');
            confirmedEvents = [];
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: confirmedEvents.map(function(row) {
                var now = new Date();
                var eventStart = new Date(row.start_datetime);
                var color = eventStart > now ? '#FF8C00' : '#FFA500';
                return {
                    id: row.id,
                    title: row.title,
                    start: row.start_datetime,
                    end: row.end_datetime,
                    backgroundColor: color,
                    borderColor: color,
                    extendedProps: {
                        description: row.description,
                        amount_paid: row.amount_paid
                    }
                };
            }),
            eventClick: function(info) {
                showEventDetails(info.event);
            }
        });

        calendar.render();
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

    function showEventDetails(event) {
        var modalContent = `
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-day"></i> ${event.title}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><i class="fas fa-info-circle"></i> <strong>Description:</strong> ${event.extendedProps.description}</p>
                <p><i class="fas fa-hourglass-start"></i> <strong>Start:</strong> ${event.start.toLocaleString()}</p>
                <p><i class="fas fa-hourglass-end"></i> <strong>End:</strong> ${event.end.toLocaleString()}</p>
                <p><i class="fas fa-dollar-sign"></i> <strong>Amount Paid:</strong> ₱${event.extendedProps.amount_paid}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        `;

        var modalEl = document.createElement('div');
        modalEl.innerHTML = `
            <div class="modal fade" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        ${modalContent}
                    </div>
                </div>
            </div>
        `;

        var modal = new bootstrap.Modal(modalEl.querySelector('.modal'));
        document.body.appendChild(modalEl);
        modal.show();

        modalEl.querySelector('.modal').addEventListener('hidden.bs.modal', function () {
            document.body.removeChild(modalEl);
        });
    }

    function showNotification(booking) {
        const modal = document.getElementById('bookingNotificationModal');
        const bookingUser = document.getElementById('bookingUser');
        const bookingTitle = document.getElementById('bookingTitle');
        const bookingDateTime = document.getElementById('bookingDateTime');
        const bookingType = document.getElementById('bookingType');
        const bookingAmount = document.getElementById('bookingAmount');

        bookingUser.textContent = `${booking.first_name} ${booking.last_name}` || 'N/A';
        bookingTitle.textContent = booking.title || 'N/A';
        bookingDateTime.textContent = new Date(booking.start_datetime).toLocaleString() || 'N/A';
        bookingType.textContent = booking.event_type || 'N/A';
        bookingAmount.textContent = booking.amount_paid ? `₱${booking.amount_paid}` : 'N/A';

        modal.style.display = 'block';
    }

    function closeNotificationModal() {
        const modal = document.getElementById('bookingNotificationModal');
        modal.style.display = 'none';
    }

    function logout() {
        fetch('logout.php')
            .then(response => {
                if (response.ok) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Logout failed. Please try again.');
                }
            })
            .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html>
