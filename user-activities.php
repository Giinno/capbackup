<?php
session_start();
include 'db-connect.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to fetch data from database
function fetchData($conn, $query, $params = []) {
    try {
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    } catch (mysqli_sql_exception $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// Fetch all user activities
$activities = fetchData($conn, "SELECT id, title, status, start_datetime, amount_paid, is_read, receipt_number FROM schedule_list WHERE user_id = ? ORDER BY start_datetime DESC", [$_SESSION['user_id']]);

// Function to mark notification as read
function markNotificationAsRead($conn, $notification_id) {
    $stmt = $conn->prepare("UPDATE schedule_list SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    $stmt->execute();
    $stmt->close();
}

// Handle marking notifications as read
if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
    markNotificationAsRead($conn, $_POST['notification_id']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$conn->close();

// Function to format date
function formatDate($date) {
    return date('F j, Y, g:i a', strtotime($date));
}

// Function to get status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning rounded-pill">Pending <i class="fas fa-clock"></i></span>',
        'confirmed' => '<span class="badge bg-success rounded-pill">Confirmed <i class="fas fa-check-circle"></i></span>',
        'canceled' => '<span class="badge bg-danger rounded-pill">Canceled <i class="fas fa-times-circle"></i></span>'
    ];
    return $badges[$status] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activities - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
  --primary-color: #8B5CF6;
  --secondary-color: #D946EF;
  --dark-bg: #1A1F2C;
  --card-bg: #222837;
  --text-primary: #FFFFFF;
  --text-secondary: #A0AEC0;
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
}

body {
  background-color: var(--dark-bg);
  color: var(--text-primary);
  font-family: 'Montserrat', sans-serif;
}

.navbar {
  background-color: var(--card-bg);
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
  color: var(--primary-color);
  font-weight: 700;
  font-size: 1.5rem;
}

.nav-link {
  color: var(--text-secondary);
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: var(--primary-color);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

h1 {
  color: var(--text-primary);
  font-weight: 700;
  margin-bottom: 2rem;
  font-size: 2rem;
}

.table {
  background-color: var(--card-bg);
  border-radius: 1rem;
  overflow: hidden;
  margin-top: 2rem;
  border: none;
}

.table thead th {
  background-color: rgba(139, 92, 246, 0.1);
  color: var(--text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.875rem;
  padding: 1rem;
  border: none;
}

.table tbody td {
  color: var(--text-primary);
  background-color: #121212;
  padding: 1rem;
  border-color: rgba(255, 255, 255, 0.1);
  vertical-align: middle;
}

.table-striped tbody tr:nth-of-type(odd) {
  background-color: rgba(255, 255, 255, 0.02);
}

.table-hover tbody tr:hover {
  background-color: rgba(139, 92, 246, 0.05);
}

.badge {
  padding: 0.5rem 1rem;
  font-weight: 500;
  font-size: 0.875rem;
}

.badge.bg-warning {
  background-color: var(--warning) !important;
  color: #000;
}

.badge.bg-success {
  background-color: var(--success) !important;
}

.badge.bg-danger {
  background-color: var(--danger) !important;
}

.btn-primary {
  background-color: var(--primary-color);
  border: none;
  padding: 0.5rem 1rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: var(--secondary-color);
  transform: translateY(-1px);
}

.mark-read-btn {
  color: var(--text-secondary);
  transition: all 0.3s ease;
}

.mark-read-btn:hover {
  color: var(--primary-color);
}

.receipt-number {
  font-family: 'Roboto Mono', monospace;
  color: var(--success);
  background: rgba(16, 185, 129, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.table-responsive {
  border-radius: 1rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

@media (max-width: 768px) {
  .table thead {
    display: none;
  }
  
  .table tbody td {
    display: block;
    padding: 0.5rem 1rem;
    text-align: right;
    border: none;
  }
  
  .table tbody td::before {
    content: attr(data-label);
    float: left;
    font-weight: 600;
    color: var(--text-secondary);
  }
  
  .table tbody tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }
}
    </style>
</head>
<body>
<header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">Ballers Hub</a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Back to Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <div class="container mt-5">
        <h1><i class="fas fa-history me-2"></i>Your Activities</h1>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Amount Paid</th>
                        <th>Receipt Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td data-label="Title"><?php echo htmlspecialchars($activity['title']); ?></td>
                            <td data-label="Date & Time"><?php echo formatDate($activity['start_datetime']); ?></td>
                            <td data-label="Status"><?php echo getStatusBadge($activity['status']); ?></td>
                            <td data-label="Amount Paid">₱<?php echo number_format($activity['amount_paid'], 2); ?></td>
                            <td data-label="Receipt Number">
                                <?php if (!empty($activity['receipt_number'])): ?>
                                    <span class="receipt-number"><?php echo htmlspecialchars($activity['receipt_number']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Action">
                                <?php if (!$activity['is_read']): ?>
                                    <button type="button" class="mark-read-btn" onclick="markAsRead(<?php echo $activity['id']; ?>)" title="Mark as read">
                                        <i class="fas fa-check"></i> Mark as read
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fas fa-check"></i> Read</span>
                                <?php endif; ?>
                                <?php if ($activity['amount_paid'] > 0 && !empty($activity['receipt_number'])): ?>
                                    <button onclick="downloadReceipt(<?php echo $activity['id']; ?>)" class="btn btn-sm btn-primary ms-2" title="Download Receipt">
                                        <i class="fas fa-download"></i> Receipt
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'include/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function markAsRead(id) {
        Swal.fire({
            title: 'Mark as read?',
            text: "This notification will be marked as read.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark it!'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="notification_id" value="${id}">
                    <input type="hidden" name="mark_read" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function downloadReceipt(id) {
        Swal.fire({
            title: 'Download Receipt?',
            text: "You're about to download the receipt.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, download it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `download_receipt.php?id=${id}`;
            }
        });
    }
    </script>
</body>
</html>
