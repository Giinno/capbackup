<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #222222;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #f56C00;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        .navbar-brand {
            font-weight: bold;
            color: #222222 !important;
            margin-bottom: 40px;
        }
        .sidebar a {
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            text-decoration: none;
            color: #222222;
            padding: 15px 20px;
            text-align: center;
            width: 80%;
            margin: 10px 0;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            color: black;
            font-weight: bold;
            transform: translateY(0);
        }
        .sidebar a:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .content.shifted {
            margin-left: 0;
        }
        .content-container {
            background-color: #333333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        h1 {
            color: #f56C00;
        }
        .logout-button {
            margin-top: auto;
            padding: 10px 20px;
            background-color: #222222;
            color: #f56C00;
            border: none;
            cursor: pointer;
            font-size: 18px;
            text-align: center;
            width: 80%;
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s, transform 0.3s;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }
        .logout-button:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }
        .toggle-sidebar-btn {
            position: fixed;
            top: 20px;
            left: 260px;
            background-color: #f56C00;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 18px;
            border-radius: 8px;
            transition: transform 0.3s;
            z-index: 1000;
        }
        .toggle-sidebar-btn.hidden {
            left: 20px;
            transform: rotate(180deg);
        }
        p {
            font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
    </style>
    <script>
        function loadPage(page) {
            document.getElementById('content-container').innerHTML = '<iframe src="' + page + '" frameborder="0" style="width:100%; height:100vh;"></iframe>';
        }

        window.onload = function() {
            loadPage('manage-events.php');
        };

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('hidden');
            document.querySelector('.content').classList.toggle('shifted');
            document.querySelector('.toggle-sidebar-btn').classList.toggle('hidden');
        }

        function logout() {
    window.location.href = 'dashboard.php';
}

    </script>
</head>
<body>
    <div class="sidebar">
        <p class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="#" onclick="loadPage('manage-events.php'); return false;">Manage Events</a>
        <a href="#" onclick="loadPage('add-event.php'); return false;">Add Event</a>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="content">
        <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
        <div id="content-container" class="content-container">
            <!-- Content from manage-events.php and other pages will be loaded here -->
        </div>
    </div>
</body>
</html>
