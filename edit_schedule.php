<?php
session_start();
require_once('db-connect.php');

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'scheduling-admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (!$id) {
    header("Location: manage-schedule.php");
    exit;
}

$sql = "SELECT * FROM schedule_list WHERE id = '$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $schedule = $result->fetch_assoc();
} else {
    header("Location: manage-schedule.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $start_datetime = $conn->real_escape_string($_POST['start_datetime']);
    $end_datetime = $conn->real_escape_string($_POST['end_datetime']);
    $event_type = $conn->real_escape_string($_POST['event_type']);
    $amount_paid = $conn->real_escape_string($_POST['amount_paid']);

    $update_sql = "UPDATE schedule_list SET 
                   title = '$title', 
                   description = '$description', 
                   start_datetime = '$start_datetime', 
                   end_datetime = '$end_datetime', 
                   event_type = '$event_type', 
                   amount_paid = '$amount_paid' 
                   WHERE id = '$id'";

    if ($conn->query($update_sql)) {
        header("Location: manage-schedule.php");
        exit;
    } else {
        $error = "Error updating schedule: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Schedule</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link rel="stylesheet" href="./css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Schedule</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="title">Reserver's Name</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($schedule['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" required><?php echo htmlspecialchars($schedule['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="start_datetime">Start Date and Time</label>
                <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['start_datetime'])); ?>" required>
            </div>
            <div class="form-group">
                <label for="end_datetime">End Date and Time</label>
                <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($schedule['end_datetime'])); ?>" required>
            </div>
            <div class="form-group">
                <label for="event_type">Event Type</label>
                <select class="form-control" id="event_type" name="event_type" required>
                    <option value="City-wide" <?php echo $schedule['event_type'] == 'City-wide' ? 'selected' : ''; ?>>City-wide</option>
                    <option value="Barangay" <?php echo $schedule['event_type'] == 'Barangay' ? 'selected' : ''; ?>>Barangay</option>
                    <option value="National" <?php echo $schedule['event_type'] == 'National' ? 'selected' : ''; ?>>National</option>
                </select>
            </div>
            <div class="form-group">
                <label for="amount_paid">Amount Paid</label>
                <input type="number" class="form-control" id="amount_paid" name="amount_paid" value="<?php echo htmlspecialchars($schedule['amount_paid']); ?>" step="0.01" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Schedule</button>
            <a href="manage-schedule.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
</body>
</html>
