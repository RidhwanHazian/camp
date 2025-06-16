<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - TasikBiruCamps</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            position: relative;
            color: #fff;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-image: url('backgroundcamp.jpg');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            padding: 0;
            margin: 0;
        }

        .nav-links li a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links li a:hover {
            color: #28a745;
        }

        .about-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .about-text {
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .about-text h1 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: #fff;
            font-weight: 600;
        }

        .about-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .about-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: #28a745;
            color: #fff;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
            background-color: #218838;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #28a745;
        }

        .feature-card p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        @media (max-width: 768px) {
            .about-container {
                grid-template-columns: 1fr;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">TasikBiruCamps</div>
        <ul class="nav-links">
            <li><a href="homepage.php">Home</a></li>
            <li><a href="campsites.php">Campsites</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <div class="about-container">
        <div class="about-text">
            <h1>About TasikBiruCamps</h1>
            <p>Welcome to TasikBiruCamps, your gateway to unforgettable camping experiences in the heart of nature. We're passionate about connecting outdoor enthusiasts with the most beautiful camping spots while ensuring comfort, safety, and environmental preservation.</p>
            
            <p>Our mission is to make camping accessible to everyone while promoting sustainable outdoor practices and creating lasting memories in nature's embrace.</p>
            
            <div class="features">
                <div class="feature-card">
                    <h3>Premium Locations</h3>
                    <p>Carefully selected camping spots with breathtaking views</p>
                </div>
                <div class="feature-card">
                    <h3>Easy Booking</h3>
                    <p>Simple and secure reservation system</p>
                </div>
                <div class="feature-card">
                    <h3>24/7 Support</h3>
                    <p>Always here to help with your camping needs</p>
                </div>
            </div>

            <br>
            <a href="campsites.php" class="btn">Lihat Pakej Kami</a>
        </div>
        
        <div class="about-image">
            <img src="https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Camping Scene">
        </div>
    </div>
</body>
</html>