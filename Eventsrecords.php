<?php
include 'db-connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM events WHERE title LIKE ? ORDER BY event_date ASC";
$stmt = $conn->prepare($query);
$search_param = '%' . $search . '%';
$stmt->bind_param('s', $search_param);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>View Events - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <style>
        :root {
            --primary-color: #f57C00;
            --secondary-color: #222;
            --text-color: #ffffff;
            --bg-color: #121212;
            --card-bg-color: #1E1E1E;
            --hover-color: #2A2A2A;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
        }

        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: none;
            border-radius: 50px;
            background-color: var(--secondary-color);
            color: var(--text-color);
            font-size: 1rem;
            transition: box-shadow 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }

        .back-button {
            position: absolute;
            left: -3rem;
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--primary-color);
            color: var(--text-color);
            border: none;
            border-radius: 50%;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #ff9800;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .event-card {
            background-color: var(--card-bg-color);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .event-details {
            padding: 1.5rem;
        }

        .event-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .event-description {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .event-date {
            font-size: 0.8rem;
            color: #888;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .search-container {
                flex-direction: column;
                align-items: stretch;
            }

            .back-button {
                position: static;
                transform: none;
                margin-bottom: 1rem;
                align-self: flex-start;
            }

            .search-input {
                max-width: none;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Events</h1>
        <div class="search-container">
            <button class="back-button" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
            <input type="text" id="search" class="search-input" name="search" placeholder="Search by event title..." value="<?php echo htmlspecialchars($search); ?>" onkeyup="liveSearch()">
            <i class="fas fa-search search-icon"></i>
        </div>
        <div id="events-grid" class="events-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="event-card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Event Image" class="event-image">
                        <div class="event-details">
                            <h2 class="event-title"><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></h2>
                            <p class="event-description"><?php echo htmlspecialchars($row['description'] ?? 'N/A'); ?></p>
                            <p class="event-date"><?php echo htmlspecialchars($row['event_date'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No events found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function liveSearch() {
            let searchValue = document.getElementById('search').value;
            fetch(`?search=${encodeURIComponent(searchValue)}`)
                .then(response => response.text())
                .then(data => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(data, 'text/html');
                    document.getElementById('events-grid').innerHTML = doc.getElementById('events-grid').innerHTML;
                });
        }
    </script>
</body>
</html>
