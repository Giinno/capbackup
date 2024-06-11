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
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            color: black;
            font-weight: bold;
        }
        .sidebar a:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .content {
            margin-left: 250px;
            padding: 20px;
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
            transition: background-color 0.3s, color 0.3s, box-shadow 0.3s;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .logout-button:hover {
            background-color: #f56C00;
            color: #ffffff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        p {
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
        }
    </style>
    <script>
        function loadPage(page) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', page, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('content-container').innerHTML = xhr.responseText;
                }
            }
            xhr.send();
        }

        window.onload = function() {
            loadPage('profile-cms.php');
        }

        function logout() {
            window.location.href = 'login.php';
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <p href="#" class="navbar-brand" style="font-size: 30px;">Ballers Hub</p>
        <a href="#" onclick="loadPage('profile-cms.php')">Profile Settings</a>
        <a href="#" onclick="loadPage('stats-cms.php')">Statistics Settings</a>
        <button class="logout-button" onclick="logout()">Logout</button>
    </div>
    <div class="content">
        <div id="content-container" class="content-container">
            <!-- Content from profile-cms.php and stats-cms.php will be loaded here -->
        </div>
    </div>
</body>
</html>
