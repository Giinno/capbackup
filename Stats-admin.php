<?php
session_start();

// Check if the user is logged in and has the role of 'Statistics-admin'
if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'statistics-admin') {
    // Redirect to login page if the user is not logged in or does not have the right role
    header('Location: login.php');
    exit;
}

// Debugging line to check session values
error_log("User ID: " . $_SESSION['user_id']);
error_log("User role: " . $_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stats Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background-color: #222222;
            color: #ffffff;
            overflow-x: hidden;
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
            console.log('Loading page:', page);
            const xhr = new XMLHttpRequest();
            xhr.open('GET', page, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    console.log('ReadyState:', xhr.readyState, 'Status:', xhr.status);
                    if (xhr.status === 200) {
                        document.getElementById('content-container').innerHTML = xhr.responseText;
                    } else {
                        console.error('Failed to load page:', xhr.status, xhr.statusText);
                        document.getElementById('content-container').innerHTML = '<p>Failed to load content. Please try again later.</p>';
                    }
                }
            };
            xhr.onerror = function() {
                console.error('Request error:', xhr.status, xhr.statusText);
                document.getElementById('content-container').innerHTML = '<p>Request error. Please check your network connection and try again.</p>';
            };
            xhr.send();
        }

        window.onload = function() {
            loadPage('profile-cms.php');
        };

        function logout() {
            window.location.href = 'dashboard.php';
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            const toggleBtn = document.querySelector('.toggle-sidebar-btn');
            sidebar.classList.toggle('hidden');
            content.classList.toggle('shifted');
            toggleBtn.classList.toggle('hidden');
        }

        function logout() {
    window.location.href = 'dashboard.php';
}

    </script>
</head>
<body>
    <div class="sidebar">
        <p class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="#" onclick="loadPage('profile-cms.php'); return false;">Profile Settings</a>
        <a href="#" onclick="loadPage('stats-cms.php'); return false;">Statistics Settings</a>
        <a href="#" onclick="loadPage('gamresult.php'); return false;">Game Results</a>
        <a href="#" onclick="loadPage('edit-card-content.php'); return false;">Dashboard Showcase</a>
        <a href="#" onclick="loadPage('viewteams.php'); return false;">View Teams</a>
        <a href="#" onclick="loadPage('feedback.php'); return false;">Feedback</a>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="content">
        <button class="toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>
        <div id="content-container" class="content-container">
            <!-- Content from profile-cms.php and other pages will be loaded here -->
        </div>
    </div>
</body>
</html>
