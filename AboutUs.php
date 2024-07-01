<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Ballers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            scroll-behavior: smooth; /* Add smooth scrolling behavior */
        }
        .navbar {
            background-color: #343a40;
        }
        .navbar-brand{
            color: #F57C00 !important;
        }
        .nav-link{
            color: #ffffff;
        }
        .nav-link:hover{
            color: #F57C00;
        }
        .navbar-brand{
            font-weight: bold;
        }
        .dropdown-item {
            color: #000000 !important;
        }
        .team-member {
            text-align: center;
            padding: 40px;
            border: 2px solid #444444;
            margin: 20px;
            border-radius: 15px;
            background-color: #333333;
            flex: 1 1 30%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        .team-member h3, .team-member h4 {
            color: #F57C00;
            font-size: 1.8em;
        }
        .team-member p {
            font-size: 1.3em;
        }
        .team-member img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 50%;
            max-width: 300px;
            transition: transform 0.3s ease;
        }
        .team-member img:hover {
            transform: scale(1.1);
        }
        .team-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: nowrap;
            gap: 20px;
        }
        h1 {
            font-size: 3em;
        }
        .main-container {
            max-width: 100%;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 1s ease, transform 1s ease;
        }
        .main-container.loaded {
            opacity: 1;
            transform: translateY(0);
        }
        h3{
            margin-top: 15px;
        }
        /* Styles for Contact Section */
        .contact-section {
            background-color: #2C2C2C;
            padding: 50px 0;
            margin-top: 50px;
        }
        .contact-section h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            text-align: center;
        }
        .contact-section p {
            font-size: 1.2em;
            margin-bottom: 40px;
            text-align: center;
        }
        .contact-section .contact-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .contact-section form {
            max-width: 500px;
            margin-right: 50px;
        }
        .contact-section input, .contact-section textarea {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
        }
        .contact-section input[type="submit"] {
            background-color: #F57C00;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .contact-section input[type="submit"]:hover {
            background-color: #e86b00;
        }
        .contact-details {
            max-width: 500px;
            color: #ccc;
        }
        .contact-details p {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .contact-details a {
            color: #F57C00;
            text-decoration: none;
        }
        .contact-details a:hover {
            text-decoration: underline;
        }
        .contact-details i {
            margin-right: 10px;
        }
        .social-media {
            margin-top: 20px;
            text-align: center; /* Center the social media icons */
        }
        .social-media a {
            color: #F57C00;
            margin: 0 10px;
            font-size: 1.5em;
        }
        .social-media a:hover {
            color: #e86b00;
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
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Menu
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php">Players</a></li>
                                <li><a class="dropdown-item" href="/schedule/schedule.php">Reserve a Court</a></li>
                                <li><a class="dropdown-item" href="#">The Team</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a href="Dashboard.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="#contact-section" class="nav-link">Contact Us</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="container main-container mt-5">
        <h1 class="text-center">Meet Our Team</h1>
        <div class="team-container">
            <div class="team-member">
                <img src="images/me.png" alt="Geen Anthony Neo Inclino" class="img-fluid">
                <h3 style="font-family:sans-serif;">Geen Anthony Neo Inclino</h3>
                <h4 style="font-weight: bold;">Head Programmer</h4>
                <p>Geen Inclino leads the technical and programming side, mostly known as the brain of the team.</p>
            </div>
            <div class="team-member">
                <img src="images/apple.jpg" alt="Apple Mae Dinawanao" class="img-fluid">
                <h3 style="font-family:sans-serif;">Apple Mae Dinawanao</h3>
                <h4 style="font-weight: bold;">Project Manager</h4>
                <p>Apple leads product management and documentation for Ballers Hub ecosystem.</p>
            </div>
            <div class="team-member">
                <img src="images/yoyo.jpg" alt="Yohan Yana" class="img-fluid">
                <h3 style="font-family:sans-serif;">Yohan Yana</h3>
                <h4 style="font-weight: bold;">Head Designer</h4>
                <p>Yohan is responsible for designing and maintaining a good UI for the system.</p>
            </div>
        </div>
    </div>

    <div id="contact-section" class="contact-section">
        <div class="container">
            <h2>Contact Us</h2>
            <p>Have any questions? We'd love to hear from you.</p>
            <div class="contact-container">
                <form action="handle_feedback.php" method="post">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                    <input type="submit" value="Submit">
                </form>
                <div class="contact-details">
                    <p><i class="bi bi-envelope-fill"></i>Email: <a href="mailto:BallersHub@gmail.com">BallersHub@gmail.com</a></p>
                    <p><i class="bi bi-telephone-fill"></i>Phone: <a href="tel:+63123456789">+63 123 456 789</a></p>
                    <div class="social-media">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-twitter"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector(".main-container").classList.add("loaded");
        });
    </script>
</body>
</html>
