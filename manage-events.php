<?php
include 'db-connect.php';

$query = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$result = $conn->query($query);

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
    <title>Manage Events</title>
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
        table {
            width: 90%;
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #333;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
            position: relative;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
        }
        .btn {
            cursor: pointer;
            padding: 10px 20px;
            color: #fff;
            background-color: #f57C00;
            border: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #ff7d1a;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="file"],
        .form-group input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 5px;
            background-color: #222;
            color: #fff;
        }
    </style>
</head>
<body>
    <h1>Manage Events</h1>  
    <button class="btn" onclick="window.location.href='Eventsrecords.php'">Events History</button>
    <table>
        <tr>
            <th>Event Title</th>
            <th>Event Description</th>
            <th>Event Date</th>
            <th>Event Image</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td data-label="Event Title"><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></td>
                    <td data-label="Event Description"><?php echo htmlspecialchars($row['description'] ?? 'N/A'); ?></td>
                    <td data-label="Event Date"><?php echo htmlspecialchars($row['event_date'] ?? 'N/A'); ?></td>
                    <td data-label="Event Image"><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Event Image"></td>
                    <td data-label="Actions">
                        <button class="btn editBtn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-edit"></i> Edit</button> |
                        <button class="btn deleteBtn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash-alt"></i> Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No upcoming events found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <form id="editForm" method="post" action="update-event.php" enctype="multipart/form-data">
                <input type="hidden" id="editId" name="id">
                <div class="form-group">
                    <label for="editTitle">Event Title:</label>
                    <input type="text" id="editTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label for="editDescription">Event Description:</label>
                    <input type="text" id="editDescription" name="description" required>
                </div>
                <div class="form-group">
                    <label for="editDate">Event Date:</label>
                    <input type="date" id="editDate" name="event_date" required>
                </div>
                <div class="form-group">
                    <label for="editImage">Event Image:</label>
                    <input type="file" id="editImage" name="image_url" accept="image/*">
                    <input type="hidden" id="existingImageUrl" name="existing_image_url">
                </div>
                <input type="submit" value="Update Event" class="btn">
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <p>Are you sure you want to delete this event?</p>
            <form id="deleteForm" method="post" action="delete-event.php">
                <input type="hidden" id="deleteId" name="id">
                <input type="submit" value="Delete" class="btn">
                <button type="button" class="btn close">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get modals
            var editModal = document.getElementById('editModal');
            var deleteModal = document.getElementById('deleteModal');

            // Get the <span> elements that close the modals
            var closeSpans = document.getElementsByClassName("close");

            // Close the modal when the user clicks on <span> (x)
            for (var i = 0; i < closeSpans.length; i++) {
                closeSpans[i].onclick = function() {
                    editModal.style.display = "none";
                    deleteModal.style.display = "none";
                }
            }

            // Close the modal when the user clicks anywhere outside of the modal
            window.onclick = function(event) {
                if (event.target == editModal) {
                    editModal.style.display = "none";
                } else if (event.target == deleteModal) {
                    deleteModal.style.display = "none";
                }
            }

            // Edit button click handler
            document.querySelectorAll('.editBtn').forEach(button => {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var row = this.closest('tr');
                    var title = row.cells[0].innerText;
                    var description = row.cells[1].innerText;
                    var date = row.cells[2].innerText;
                    var imageUrl = row.querySelector('img').getAttribute('src');

                    document.getElementById('editId').value = id;
                    document.getElementById('editTitle').value = title;
                    document.getElementById('editDescription').value = description;
                    document.getElementById('editDate').value = date;
                    document.getElementById('existingImageUrl').value = imageUrl;

                    editModal.style.display = "flex";
                });
            });

            // Delete button click handler
            document.querySelectorAll('.deleteBtn').forEach(button => {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    document.getElementById('deleteId').value = id;
                    deleteModal.style.display = "flex";
                });
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
