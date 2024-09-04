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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>View Events</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            margin-top: 20px;
            color: #f57C00;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            padding: 0 20px;
        }
        .search-container {
            width: 100%;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            position: relative;
        }
        .search-container input[type="text"] {
            padding: 10px 20px;
            border: 1px solid #555;
            border-radius: 25px;
            background-color: #222;
            color: #fff;
            width: 100%;
            max-width: 500px;
            font-size: 16px;
        }
        .back-button {
            position: absolute;
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            background-color: #f57C00;
            border: none;
            border-radius: 50%;
            color: #fff;
            padding: 10px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .back-button:hover {
            background-color: #ff7d1a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #222;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        table, th, td {
            border: 1px solid #444;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #333;
        }
        tr:nth-child(even) {
            background-color: #222;
        }
        tr:hover {
            background-color: #444;
        }
        img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }
        a {
            color: #f57C00;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .search-container input[type="text"] {
                width: calc(100% - 60px); /* 60px is the back button width */
            }
            table, th, td {
                display: block;
                width: 100%;
            }
            th, td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            th::before, td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: calc(50% - 20px);
                text-align: left;
                font-weight: bold;
            }
            th {
                background-color: transparent;
                border-bottom: 2px solid #444;
            }
        }
    </style>
</head>
<body>
    <h1>View Events</h1>
    <div class="container">
        <div class="search-container">
            <button class="back-button" onclick="history.back()"><i class="fas fa-arrow-left"></i></button>
            <input type="text" id="search" name="search" placeholder="Search by event title..." value="<?php echo htmlspecialchars($search); ?>" onkeyup="liveSearch()">
        </div>
        <table id="events-table">
            <tr>
                <th>Event ID</th>
                <th>Event Title</th>
                <th>Event Description</th>
                <th>Event Date</th>
                <th>Event Image</th>
            </tr>
            <tbody id="events-body">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Event ID"><?php echo htmlspecialchars($row['id']); ?></td>
                            <td data-label="Event Title"><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></td>
                            <td data-label="Event Description"><?php echo htmlspecialchars($row['description'] ?? 'N/A'); ?></td>
                            <td data-label="Event Date"><?php echo htmlspecialchars($row['event_date'] ?? 'N/A'); ?></td>
                            <td data-label="Event Image"><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Event Image"></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No events found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
        function liveSearch() {
            let searchValue = document.getElementById('search').value;
            fetch(`?search=${encodeURIComponent(searchValue)}`)
                .then(response => response.text())
                .then(data => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(data, 'text/html');
                    document.getElementById('events-body').innerHTML = doc.getElementById('events-body').innerHTML;
                });
        }
    </script>
</body>
</html>
