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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/manage-events.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Manage Events - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="sidebar-brand">Ballers Hub</div>
            <nav>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="admin-dashboard.php" class="sidebar-nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="manage-events.php" class="sidebar-nav-link">
                            <i class="fas fa-calendar-alt"></i> Manage Events
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="add-event.php" class="sidebar-nav-link">
                            <i class="fas fa-plus-circle"></i> Add Event
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="edit-card-content.php" class="sidebar-nav-link">
                            <i class="fas fa-edit"></i> Dashboard Showcase
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="Feedback.php" class="sidebar-nav-link">
                            <i class="fas fa-comment"></i> Feedback
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="admin_registration.php" class="sidebar-nav-link">
                            <i class="fas fa-comment"></i> Admin Registration
                        </a>
                    </li>
                </ul>
            </nav>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </aside>

        <main class="main-content">
            <h1 class="page-title">Manage Events</h1>
            <button class="btn" onclick="window.location.href='Eventsrecords.php'">Events History</button>
            <table>
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Event Description</th>
                        <th>Event Date</th>
                        <th>Event Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Event Title"><?php echo htmlspecialchars($row['title'] ?? 'N/A'); ?></td>
                                <td data-label="Event Description"><?php echo htmlspecialchars($row['description'] ?? 'N/A'); ?></td>
                                <td data-label="Event Date"><?php echo htmlspecialchars($row['event_date'] ?? 'N/A'); ?></td>
                                <td data-label="Event Image"><img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="Event Image"></td>
                                <td data-label="Actions">
                                    <button class="btn editBtn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn deleteBtn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash-alt"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No upcoming events found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

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
                <button type="submit" class="btn">Update Event</button>
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
                <button type="submit" class="btn">Delete</button>
                <button type="button" class="btn close">Cancel</button>
            </form>
        </div>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function() {
    var editModal = document.getElementById('editModal');
    var deleteModal = document.getElementById('deleteModal');
    var closeSpans = document.getElementsByClassName("close");

    for (var i = 0; i < closeSpans.length; i++) {
        closeSpans[i].onclick = function() {
            editModal.style.display = "none";
            deleteModal.style.display = "none";
        }
    }

    window.onclick = function(event) {
        if (event.target == editModal) {
            editModal.style.display = "none";
        } else if (event.target == deleteModal) {
            deleteModal.style.display = "none";
        }
    }

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

    document.querySelectorAll('.deleteBtn').forEach(button => {
        button.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var formData = new FormData();
                    formData.append('id', id);
                    
                    fetch('delete-event.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Deleted!',
                                'Event has been deleted successfully.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                data.message || 'Failed to delete event.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the event.',
                            'error'
                        );
                    });
                }
            });
        });
    });

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        Swal.fire({
            title: 'Updating Event',
            text: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('update-event.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Event updated successfully',
                    icon: 'success',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to update event',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while updating the event',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
        });
    });
});
</script>
</body>
</html>
<?php
$conn->close();
?>
