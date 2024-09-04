<?php
session_start();
include 'db-connect.php';

// Fetch card content
$card_content_stmt = $conn->prepare("SELECT title, body, image_url FROM card_content WHERE id = 1");
$card_content_stmt->execute();
$card_content_stmt->bind_result($card_title, $card_body, $card_image_url);
$card_content_stmt->fetch();
$card_content_stmt->close();

// Fetch future events
$current_date = date('Y-m-d');
$events_stmt = $conn->prepare("SELECT title, description, event_date, image_url FROM events WHERE event_date >= ? ORDER BY event_date ASC");
$events_stmt->bind_param('s', $current_date);
$events_stmt->execute();
$events_stmt->bind_result($event_title, $event_description, $event_date, $event_image);
$events = [];
while ($events_stmt->fetch()) {
    $events[] = [
        'title' => $event_title,
        'description' => $event_description,
        'event_date' => $event_date,
        'image' => $event_image
    ];
}
$events_stmt->close();

// Fetch reservation notifications for the logged-in user
$notifications = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $notification_stmt = $conn->prepare("SELECT id, title, status FROM schedule_list WHERE user_id = ? ORDER BY start_datetime DESC");
    $notification_stmt->bind_param('i', $user_id); // Bind the user_id parameter
    $notification_stmt->execute();
    $notification_stmt->bind_result($reservation_id, $reservation_title, $reservation_status);
    while ($notification_stmt->fetch()) {
        $notifications[] = [
            'id' => $reservation_id,
            'title' => $reservation_title,
            'status' => $reservation_status
        ];
    }
    $notification_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>
<body>
<header>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a href="#" class="navbar-brand">Ballers Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">&#9776;</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <!-- Replacing text with a FontAwesome bell icon -->
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell"></i> <!-- Notification icon -->
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <div class="list-group">
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($notification['title']); ?></strong><br>
                                            <small class="text-muted">Status: <?php echo htmlspecialchars($notification['status']); ?></small>
                                        </div>
                                        <!-- Add an icon to represent the status -->
                                        <?php if ($notification['status'] == 'pending'): ?>
                                            <span class="badge bg-warning rounded-pill">Pending <i class="fas fa-clock"></i></span>
                                        <?php elseif ($notification['status'] == 'confirmed'): ?>
                                            <span class="badge bg-success rounded-pill">Confirmed <i class="fas fa-check-circle"></i></span>
                                        <?php elseif ($notification['status'] == 'canceled'): ?>
                                            <span class="badge bg-danger rounded-pill">Canceled <i class="fas fa-times-circle"></i></span>
                                        <?php endif; ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <a class="list-group-item list-group-item-action">No new notifications</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">Logout</a>
                </li>
                <li class="nav-item">
                    <a href="user-profile.php" class="nav-link" style="font-weight: bolder;"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a href="login.php" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                    <a href="Registration.php" class="nav-link">Sign up</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
</header>

<div class="header-banner">
    Welcome to Ballers Hub
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <div id="carouselExample" class="carousel slide">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="images/gboys.jpg" class="d-block w-100" alt="Slide 1">
                    </div>
                    <div class="carousel-item">
                        <img src="images/gboys1.jpg" class="d-block w-100" alt="Slide 2">
                    </div>
                    <div class="carousel-item">
                        <img src="images/gboys2.jpg" class="d-block w-100" alt="Slide 3">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Golden Boys Basketball Gym</h3>
                    <p class="card-text">Golden Boys Basketball Gym is a premier, private reservable basketball court designed for enthusiasts and professionals alike. Whether you're looking to shoot some hoops with your friends, engage in a competitive match, or host a full-scale tournament, our state-of-the-art facility provides the perfect environment. With top-notch amenities and a commitment to excellence, Golden Boys Basketball Gym ensures an unmatched experience for all basketball lovers.</p>
                    <a href="schedule.php" class="btn btn-primary">Reserve now</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card new-card">
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($card_title); ?></h1>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($card_body)); ?></p>
                    <a href="profile.php" class="btn btn-primary">View more</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <img src="<?php echo htmlspecialchars($card_image_url); ?>" class="img-fluid rounded-start" alt="Card image">
        </div>
    </div>

    <!-- Display events -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center" style="color: #f57c00;">Upcoming Events</h2>
        </div>
        <?php foreach ($events as $event): ?>
        <div class="col-md-4">
            <div class="card event-card">
                <?php if ($event['image']): ?>
                <img src="<?php echo htmlspecialchars($event['image']); ?>" class="card-img-top" alt="Event Image">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($event['description']); ?></p>
                    <p class="event-date"><?php echo date('F j, Y', strtotime($event['event_date'])); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'include/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
