<?php
session_start();
include 'db-connect.php';

// Fetch user-specific information if logged in
$user_info = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Pagination and Search
$players_per_page = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Prepare the WHERE clause for the search
$where_clause = "WHERE role = 'player'";
if (!empty($search)) {
    $where_clause .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR team LIKE '%$search%' OR bio LIKE '%$search%')";
}

// Fetch total number of players matching the search
$total_players = $conn->query("SELECT COUNT(*) FROM users $where_clause")->fetch_row()[0];
$total_pages = ceil($total_players / $players_per_page);

// Adjust page if it exceeds the total pages
$page = min($page, max(1, $total_pages));

$offset = ($page - 1) * $players_per_page;

// Fetch players for current page
$stmt = $conn->prepare("SELECT id, first_name, last_name, team, number, position, height, born, profile_picture, bio FROM users $where_clause LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $players_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$players = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Profiles - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background-color: #1a1a1a;
            padding: 1rem 0;
            border-bottom: 1px solid #333;
        }
        .navbar-brand {
            color: #f57c00 !important;
            font-weight: 600;
            font-size: 1.5rem;
        }
        .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #f57c00 !important;
        }
        .dropdown-menu {
            background-color: #1a1a1a;
            border: 1px solid #333;
        }
        .dropdown-item {
            color: #e0e0e0;
        }
        .dropdown-item:hover {
            background-color: #333;
            color: #f57c00;
        }
        .header-banner {
            background: linear-gradient(45deg, #1a1a1a, #333333);
            color: #f57c00;
            padding: 2rem;
            text-align: center;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 3px solid #f57c00;
        }
        .search-bar {
            margin-bottom: 2rem;
        }
        .search-bar input {
            background-color: #1e1e1e;
            border: 1px solid #333;
            color: #e0e0e0;
            padding: 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .search-bar input:focus {
            background-color: #2a2a2a;
            border-color: #f57c00;
            box-shadow: 0 0 0 2px rgba(245, 124, 0, 0.2);
        }
        .profile-container {
            background: linear-gradient(145deg, #1e1e1e, #2a2a2a);
            border-radius: 15px;
            padding: 1.5rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #333;
        }
        .profile-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(245, 124, 0, 0.1);
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: block;
            border: 3px solid #f57c00;
            box-shadow: 0 0 15px rgba(245, 124, 0, 0.3);
        }
        .name-link {
            color: #f57c00;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            display: block;
            margin-bottom: 0.5rem;
        }
        .name-link:hover {
            color: #ff9800;
        }
        h3 {
            color: #e0e0e0;
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            background-color: #1a1a1a;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .stats div {
            text-align: center;
            padding: 0.5rem;
        }
        .stats strong {
            display: block;
            color: #f57c00;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        .stats p {
            margin: 0;
            font-size: 1rem;
        }
        .additional-info {
            margin-top: auto;
            padding-top: 1rem;
            font-style: italic;
            color: #999;
            font-size: 0.9rem;
            text-align: center;
            border-top: 1px solid #333;
        }
        .pagination {
            margin-top: 2rem;
            justify-content: center;
        }
        .page-link {
            color: #f57c00;
            background-color: #1a1a1a;
            border: 1px solid #333;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .page-link:hover, .page-link:focus {
            background-color: #333;
            color: #f57c00;
            border-color: #f57c00;
        }
        .page-item.active .page-link {
            background-color: #f57c00;
            border-color: #f57c00;
            color: #1a1a1a;
        }
        .page-item.disabled .page-link {
            color: #666;
            background-color: #1a1a1a;
            border-color: #333;
        }
        @media (max-width: 768px) {
            .header-banner {
                font-size: 1.5rem;
                padding: 1.5rem;
            }
            .profile-container {
                margin-bottom: 1rem;
            }
        }
        #searchInput::placeholder {
            color: #666;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a href="dashboard.php" class="navbar-brand">Ballers Hub</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Menu
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Players</a>
                                <a class="dropdown-item" href="Gameresult.php">Games</a>
                                <a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a>
                                <a class="dropdown-item" href="AboutUs.php">Contact us</a>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">Home</a>
                        </li>
                        <?php if ($user_info): ?>
                            <li class="nav-item">
                                <a href="user-profile.php" class="nav-link"><?php echo htmlspecialchars($user_info['first_name']); ?></a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="login.php" class="nav-link">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="header-banner">
        PLAYERS PROFILE
    </div>

    <div class="container">
        <form action="" method="GET" class="search-bar">
            <input type="text" id="searchInput" style="color: #ffffff;" name="search" class="form-control" placeholder="Search for players" aria-label="Search for players" value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <div id="profileList" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (empty($players)): ?>
                <div class="col-12 text-center">
                    <p>No players found matching your search criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($players as $player): ?>
                    <div class="col profile-item">
                        <div class="profile-container">
                            <img src="<?php echo !empty($player['profile_picture']) ? htmlspecialchars($player['profile_picture']) : 'images/default-profile.png'; ?>" class="profile-picture" alt="Profile picture of <?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?>">
                            <h2><a href="player-profile.php?id=<?php echo htmlspecialchars($player['id']); ?>" class="name-link"><?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></a></h2>
                            <h3><?php echo htmlspecialchars($player['team']); ?></h3>
                            <div class="stats">
                                <div><strong>Number</strong><p><?php echo htmlspecialchars($player['number']); ?></p></div>
                                <div><strong>Position</strong><p><?php echo htmlspecialchars($player['position']); ?></p></div>
                                <div><strong>Height</strong><p><?php echo htmlspecialchars($player['height']); ?></p></div>
                                <div><strong>Born</strong><p><?php echo htmlspecialchars($player['born']); ?></p></div>
                            </div>
                            <div class="additional-info">
                                <?php echo htmlspecialchars($player['bio'] ?? 'No additional information available.'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Player profiles pagination">
                <ul class="pagination">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.querySelector('.search-bar');
        const searchInput = document.getElementById('searchInput');

        searchInput.addEventListener('input', function() {
            // Add a small delay to prevent too many requests while typing
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                searchForm.submit();
            }, 300);
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
