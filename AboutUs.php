<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Ballers Hub</title>
    <link rel="icon" href="./images/Bhub2.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F57C00;
            --bg-dark: #121212;
            --bg-card: #1e1e1e;
            --bg-hover: #2a2a2a;
            --text-primary: #ffffff;
            --text-secondary: #e0e0e0;
        }
        body {
            background-color: var(--bg-dark);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }
        /* Navbar styles */
        .navbar {
            background-color: #343a40;
        }
        .navbar-brand {
            color: #F57C00 !important;
            font-weight: bold;
        }
        .nav-link {
            color: #ffffff;
        }
        .nav-link:hover {
            color: #F57C00;
        }
        .dropdown-item {
            color: #000000 !important;
        }
        .team-member {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(145deg, var(--bg-card), var(--bg-hover));
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 1rem;
            flex: 1 1 300px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 40px rgba(245, 124, 0, 0.1);
        }
        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(245, 124, 0, 0.1),
                transparent
            );
            transition: 0.5s;
        }
        .team-member:hover::before {
            left: 100%;
        }
        .team-member img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .team-member:hover img {
            transform: scale(1.1) rotate(5deg);
        }
        .team-member h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin: 1rem 0;
            font-weight: 600;
        }
        .team-member h4 {
            color: var(--text-secondary);
            font-size: 1.2rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .team-member p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }
        .team-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            padding: 2rem 0;
        }
        .contact-section {
        background-color: #1a1a1a;
        padding: 80px 0;
        margin-top: 50px;
    }

    .section-title {
        font-size: 2.5rem;
        color: #F57C00;
        text-align: center;
        margin-bottom: 20px;
    }

    .section-description {
        color: #e0e0e0;
        text-align: center;
        margin-bottom: 50px;
        font-size: 1.1rem;
    }

    .contact-container {
        display: flex;
        justify-content: space-between;
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .contact-form-wrapper {
        flex: 1;
        max-width: 600px;
    }

    .contact-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #333;
        border-radius: 8px;
        background-color: #2a2a2a;
        color: #ffffff;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: #F57C00;
        box-shadow: 0 0 0 2px rgba(245, 124, 0, 0.2);
    }

    .form-input::placeholder {
        color: #888;
    }

    textarea.form-input {
        min-height: 150px;
        resize: vertical;
    }

    .submit-btn {
        background-color: #F57C00;
        color: #ffffff;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: fit-content;
    }

    .submit-btn:hover {
        background-color: #e65100;
        transform: translateY(-2px);
    }

    .contact-info {
        flex: 1;
        max-width: 400px;
        padding: 30px;
        background-color: #2a2a2a;
        border-radius: 12px;
        height: fit-content;
    }

    .info-item {
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #e0e0e0;
    }

    .info-item i {
        color: #F57C00;
        font-size: 1.2rem;
    }

    .info-item a {
        color: #F57C00;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .info-item a:hover {
        color: #e65100;
    }

    .social-media {
        margin-top: 30px;
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    .social-link {
        color: #F57C00;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        color: #e65100;
        transform: translateY(-3px);
    }

    @media (max-width: 768px) {
        .contact-container {
            flex-direction: column;
        }

        .contact-info {
            max-width: 100%;
        }
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
        <h2 class="section-title">Contact Us</h2>
        <p class="section-description">Have any questions? We'd love to hear from you.</p>
        
        <div class="contact-container">
            <div class="contact-form-wrapper">
                <form action="handle_feedback.php" method="post" class="contact-form">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required class="form-input">
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required class="form-input">
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Your Message" rows="5" required class="form-input"></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>

            <div class="contact-info">
                <div class="info-item">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Email:</span>
                    <a href="mailto:BallersHub@gmail.com">BallersHub@gmail.com</a>
                </div>
                <div class="info-item">
                    <i class="bi bi-telephone-fill"></i>
                    <span>Phone:</span>
                    <a href="tel:+63123456789">+63 123 456 789</a>
                </div>
                <div class="social-media">
                    <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
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
