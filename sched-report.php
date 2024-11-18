<?php
// Debug: Log the script path
error_log("Script Path: " . __FILE__);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

session_start();
require_once('db-connect.php');

// Check if the user is logged in and is a Scheduling-admin
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'scheduling-admin') {
    header("Location: login.php");
    exit;
}

// Function to get bookings per month
function getBookingsPerMonth($conn) {
    $sql = "SELECT 
                MONTH(start_datetime) as month, 
                COUNT(*) as count,
                GROUP_CONCAT(
                    CONCAT_WS('|', id, title, start_datetime, end_datetime, status, user_id, amount_paid, event_type, receipt_number)
                    SEPARATOR ';;'
                ) as bookings
            FROM schedule_list 
            GROUP BY MONTH(start_datetime) 
            ORDER BY MONTH(start_datetime)";

    $result = $conn->query($sql);
    $data = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $month = date("F", mktime(0, 0, 0, $row["month"], 10));
            $data[$month] = [
                'count' => $row['count'],
                'bookings' => []
            ];
            $bookings = explode(';;', $row['bookings']);
            foreach ($bookings as $booking) {
                $booking_parts = explode('|', $booking);
                // Ensure we always have 9 elements, filling with empty strings if necessary
                $booking_parts = array_pad($booking_parts, 9, '');
                
                list($id, $title, $start_datetime, $end_datetime, $status, $user_id, $amount_paid, $event_type, $receipt_number) = $booking_parts;
                
                $data[$month]['bookings'][] = [
                    'id' => $id,
                    'title' => $title,
                    'start' => $start_datetime,
                    'end' => $end_datetime,
                    'status' => $status,
                    'user_id' => $user_id,
                    'amount' => $amount_paid,
                    'type' => $event_type,
                    'receipt' => $receipt_number
                ];
            }
        }
    }

    return $data;
}

$bookingsData = getBookingsPerMonth($conn);

// Handle print request
if (isset($_GET['print'])) {
    $selectedMonth = $_GET['month'] ?? 'all';
    printReport($bookingsData, $selectedMonth);
    exit;
}

function printReport($bookingsData, $selectedMonth) {
    header("Content-Type: text/html");
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Bookings Report - " . htmlspecialchars($selectedMonth) . "</title>
        <style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            @media print {
                .no-print { display: none; }
                body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            }
        </style>
    </head>
    <body>
        <h1>Bookings Report - " . htmlspecialchars($selectedMonth) . "</h1>";

    if ($selectedMonth === 'all') {
        foreach ($bookingsData as $month => $data) {
            echo generateMonthlyReportHTML($month, $data);
        }
    } elseif (isset($bookingsData[$selectedMonth])) {
        echo generateMonthlyReportHTML($selectedMonth, $bookingsData[$selectedMonth]);
    } else {
        echo "<p>No data available for the selected month.</p>";
    }

    echo "<div class='no-print'>
            <button onclick='window.print()'>Print this report</button>
            <button onclick='window.close()'>Close</button>
          </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    </body>
    </html>";
}

function generateMonthlyReportHTML($month, $data) {
    $html = "<h2>" . htmlspecialchars($month) . " - {$data['count']} Bookings</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($data['bookings'] as $booking) {
        $html .= "<tr>
            <td>" . htmlspecialchars($booking['title']) . "</td>
            <td>" . htmlspecialchars($booking['start']) . "</td>
            <td>" . htmlspecialchars($booking['end']) . "</td>
            <td>" . htmlspecialchars($booking['status']) . "</td>
            <td>₱" . htmlspecialchars($booking['amount']) . "</td>
            <td>" . htmlspecialchars($booking['type']) . "</td>
            <td>" . htmlspecialchars($booking['receipt']) . "</td>
        </tr>";
    }

    $html .= "</tbody></table>";
    return $html;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings per Month - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #FF8C00;
            --secondary-color: #FFA500;
            --text-color: #FFFFFF;
            --text-color-muted: #E0E0E0;
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
        }
        .sidebar a {
            padding: 15px 20px;
            color: var(--text-color);
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: var(--primary-color);
            color: var(--background-color);
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
            background-color: var(--card-bg);
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
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--background-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--background-color);
        }
        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
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
        #bookingsTable {
            display: none;
        }
    </style>
</head>
<body>
    <div class="sidebar w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out">
        <div class="flex items-center justify-center mb-2">
            <img src="./images/Logo.png" alt="Ballers Hub Logo" class="w-12 h-12 mr-2">
            <h1 class="text-2xl font-semibold text-orange-500">Ballers Hub</h1>
        </div>
        <nav>
            <a href="sched-admin-dashboard.php" class="sidebar-item flex items-center rounded-lg">
                <i class="fas fa-user-cog text-l" style="margin-left: -14px;"></i>
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
        </nav>
        <button onclick="logout()" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-lg mt-auto w-full">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span>Logout</span>
        </button>
    </div>
    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="background-color: #1E1E1E;">
            <div class="container-fluid" style="background-color: #1E1E1E;">
                <h4 class="navbar-brand" href="#"><?= htmlspecialchars($_SESSION['username']) ?></h4>
            </div>
        </nav>
        <div class="container-fluid">
            <h1 class="mb-4">Bookings per Month</h1>
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title m-0">Monthly Booking Statistics</h5>
                </div>
                <div class="card-body">
                    <div style="height: 400px;">
                        <canvas id="bookingsChart"></canvas>
                    </div>
                </div>
            </div>
            <div id="bookingsTable" class="card shadow mt-4">
                <div class="card-header">
                    <h5 class="card-title m-0">Bookings for <span id="selectedMonth"></span></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="background-color: #333333;">
                            <thead>
                                <tr>
                                    <th style="background-color: #333333; color:#FFFFFF;">Title</th>
                                    <th style="background-color: #333333; color:#FFFFFF;">Start</th>
                                    <th style="background-color: #333333; color:#FFFFFF;">End</th>
                                    <th style="background-color: #333333; color:#FFFFFF;">Status</th>
                                    <th style="background-color: #333333; color:#FFFFFF;">Amount</th>
                                    <th style="background-color: #333333; color:#FFFFFF;">Type</th>
                                    <th style="background-color: #333333; color:#FFFFFF;">Receipt</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody" style="background-color: #333333; color:#FFFFFF;">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <select id="monthSelector" class="form-select mb-2">
                    <option value="all">All Months</option>
                    <?php foreach (array_keys($bookingsData) as $month): ?>
                        <option value="<?= htmlspecialchars($month) ?>"><?= htmlspecialchars($month) ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="printButton" class="btn btn-primary">Print Report</button>
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
    <script>
        const bookingsData = <?php echo json_encode($bookingsData); ?>;
        const ctx = document.getElementById('bookingsChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(bookingsData),
                datasets: [{
                    label: 'Number of Bookings',
                    data: Object.values(bookingsData).map(month => month.count),
                    backgroundColor: 'rgba(255, 165, 0, 0.8)',
                    borderColor: 'rgba(255, 165, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#ffffff'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#ffffff'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#ffffff'
                        }
                    }
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0].index;
                        const selectedMonth = Object.keys(bookingsData)[index];
                        displayBookings(selectedMonth);
                    }
                }
            }
        });

        function displayBookings(month) {
            const bookings = bookingsData[month].bookings;
            const tableBody = document.getElementById('bookingsTableBody');
            const selectedMonthSpan = document.getElementById('selectedMonth');
            
            selectedMonthSpan.textContent = month;
            tableBody.innerHTML = '';

            bookings.forEach(booking => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="background-color: #333333; color:#FFFFFF;">${escapeHtml(booking.title)}</td>
                    <td style="background-color: #333333; color:#FFFFFF;">${new Date(booking.start).toLocaleString()}</td>
                    <td style="background-color: #333333; color:#FFFFFF;">${new Date(booking.end).toLocaleString()}</td>
                    <td style="background-color: #333333; color:#FFFFFF;">${escapeHtml(booking.status)}</td>
                    <td style="background-color: #333333; color:#FFFFFF;">₱${escapeHtml(booking.amount)}</td>
                    <td style="background-color: #333333; color:#FFFFFF;">${escapeHtml(booking.type)}</td>
                    <td style="background-color: #333333; color:#FFFFFF;">${escapeHtml(booking.receipt)}</td>
                `;
                tableBody.appendChild(row);
            });

            document.getElementById('bookingsTable').style.display = 'block';
        }

        function escapeHtml(unsafe) {
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        // Set up SSE for real-time notifications
        const evtSource = new EventSource('sse_notifications.php');
        
        evtSource.addEventListener('newBooking', function(event) {
            const booking = JSON.parse(event.data);
            showNotification(booking);
            // Update the chart and table data here
            updateChartAndTable(booking);
        });

        evtSource.onerror = function(err) {
            console.error("EventSource failed:", err);
        };

        // Close the EventSource when the page is unloaded
        window.addEventListener('beforeunload', function() {
            evtSource.close();
        });

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

        function updateChartAndTable(newBooking) {
            const bookingMonth = new Date(newBooking.start_datetime).toLocaleString('default', { month: 'long' });
            
            if (bookingsData[bookingMonth]) {
                bookingsData[bookingMonth].count++;
                bookingsData[bookingMonth].bookings.push(newBooking);
            } else {
                bookingsData[bookingMonth] = {
                    count: 1,
                    bookings: [newBooking]
                };
            }

            // Update chart
            chart.data.labels = Object.keys(bookingsData);
            chart.data.datasets[0].data = Object.values(bookingsData).map(month => month.count);
            chart.update();

            // If the current month is displayed in the table, update it
            const selectedMonthSpan = document.getElementById('selectedMonth');
            if (selectedMonthSpan.textContent === bookingMonth) {
                displayBookings(bookingMonth);
            }
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

        document.getElementById('printButton').addEventListener('click', function() {
            const selectedMonth = document.getElementById('monthSelector').value;
            const currentPath = window.location.pathname;
            const printWindow = window.open(`${currentPath}?print=1&month=${encodeURIComponent(selectedMonth)}`, '_blank');
            
            // Optional: Close the print window after printing
            if (printWindow) {
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }
        });
    </script>
</body>
</html>
