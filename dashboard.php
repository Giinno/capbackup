<?php
session_start();
include 'db-connect.php';

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

// Fetch card content
$card_content = fetchData($conn, "SELECT title, body, image_url FROM card_content WHERE id = 1")[0] ?? [
    'title' => 'Welcome to Golden Boys Basketball Gym',
    'body' => 'Experience the best basketball facilities in town.',
    'image_url' => 'images/default_card.jpg'
];

// Fetch future events
$current_date = date('Y-m-d');
$events = fetchData($conn, "SELECT id, title, description, event_date, image_url, league_name FROM events WHERE event_date >= ? ORDER BY event_date ASC LIMIT 6", [$current_date]);

// Fetch reservation notifications for the logged-in user
$notifications = [];
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
    $notifications = fetchData($conn, "SELECT id, title, status, start_datetime, amount_paid, is_read FROM schedule_list WHERE user_id = ? AND start_datetime > ? ORDER BY start_datetime DESC LIMIT 5", [$_SESSION['user_id'], $seven_days_ago]);
    $unread_count = count(array_filter($notifications, function($n) { return $n['is_read'] == 0; }));
}

// Fetch carousel images
$carousel_images = fetchData($conn, "SELECT image_url, alt_text FROM carousel_images ORDER BY display_order ASC LIMIT 3");

// If no carousel images found, use default images
if (empty($carousel_images)) {
    $carousel_images = [
        ['image_url' => 'images/gboys.jpg', 'alt_text' => 'Golden Boys Basketball Gym 1'],
        ['image_url' => 'images/gboys1.jpg', 'alt_text' => 'Golden Boys Basketball Gym 2'],
        ['image_url' => 'images/gboys2.jpg', 'alt_text' => 'Golden Boys Basketball Gym 3']
    ];
}

// Function to format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <style>
        .notification-item {
            position: relative;
        }
        .mark-read-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
        }
        .mark-read-btn:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
<header>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a href="#" class="navbar-brand">Ballers Hub</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg" aria-labelledby="notificationDropdown" style="min-width: 300px;">
                        <h6 class="dropdown-header">Notifications</h6>
                        <div class="list-group">
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center notification-item <?php echo $notification['is_read'] ? 'bg-light' : ''; ?>">
                                        <div>
                                            <strong><?php echo htmlspecialchars($notification['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo formatDate($notification['start_datetime']); ?></small><br>
                                            <small class="text-muted">Amount Paid: ₱<?php echo number_format($notification['amount_paid'], 2); ?></small>
                                        </div>
                                        <?php echo getStatusBadge($notification['status']); ?>
                                        <?php if (!$notification['is_read']): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="mark_read" class="mark-read-btn" title="Mark as read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <a class="list-group-item list-group-item-action">No new notifications</a>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <a href="user-activities.php" class="btn btn-primary btn-sm w-100">View All Activities</a>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Menu
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="profile.php">Players</a>
                        <a class="dropdown-item" href="Gameresult.php">Games</a>
                        <a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a>
                        <a class="dropdown-item" href="AboutUs.php">Contact us</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                    </div>
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
            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach ($carousel_images as $index => $image): ?>
                        <button type="button" data-bs-target="#carouselExample" data-bs-slide-to="<?php echo $index; ?>" <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach ($carousel_images as $index => $image): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($image['alt_text']); ?>">
                        </div>
                    <?php endforeach; ?>
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
                    <h1 class="card-title"><?php echo htmlspecialchars($card_content['title']); ?></h1>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($card_content['body'])); ?></p>
                    <a href="profile.php" class="btn btn-primary">View more</a>
                    <a href="GameResult.php" class="btn btn-primary">Past games</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <img src="<?php echo htmlspecialchars($card_content['image_url']); ?>" class="img-fluid rounded-start" alt="Card image">
        </div>
    </div>

    <!-- Display events -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center" style="color: #f57c00;">Upcoming Events</h2>
        </div>
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
            <div class="col-md-4 mb-4">
                <div class="card event-card h-100" onclick="redirectToLeagueSchedule('<?php echo htmlspecialchars($event['league_name']); ?>')" style="cursor: pointer;">
                    <?php if (!empty($event['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($event['image_url']); ?>" class="card-img-top" alt="Event Image">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo htmlspecialchars($event['description']); ?></p>
                        <p class="event-date mt-auto"><?php echo formatDate($event['event_date']); ?></p>
                        <p class="league-name">League: <?php echo htmlspecialchars($event['league_name']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center">No upcoming events at the moment. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>

<?php include 'include/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
    function redirectToLeagueSchedule(leagueName) {
        if (leagueName) {
            const encodedLeagueName = encodeURIComponent(leagueName);
            window.location.href = `league-schedule.php?league=${encodedLeagueName}`;
        } else {
            // Fallback to general schedule if no league name is provided
            window.location.href = 'schedule.php';
        }
    }
    </script>
</body>
</html>
