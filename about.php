<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - TasikBiruCamps</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: url('backgroundcamp.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', 'Montserrat', Arial, sans-serif;
            overflow-x: hidden;
        }
        .hero-navbar-bg {
            background: rgba(20, 20, 20, 0.85);
            width: 100vw;
            position: relative;
            z-index: 2;
        }
        .main-bg {
            background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            width: 100vw;
            position: relative;
            z-index: 1;
        }
        .hero {
            background: none !important;
            color: #2e8b57;
            padding: 80px 0 260px 0;
            text-align: center;
            position: relative;
            box-shadow: none !important;
        }
        .hero h1 {
            color:rgb(255, 255, 255) !important;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .hero p {
            color: #222 !important;
            font-size: 1.3rem;
            margin-bottom: 32px;
            text-shadow: 0 2px 8px rgba(255,255,255,0.15);
        }
        .hero .cta-btn {
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 16px 38px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(40,167,69,0.13);
            transition: background 0.2s, transform 0.2s;
        }
        .hero .cta-btn:hover {
            background: #218838;
            transform: translateY(-2px) scale(1.04);
        }
        .about-section {
            background: transparent !important;
            box-shadow: none;
            color: #fff;
            text-align: center;
            padding: 80px 20px 40px 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        .about-section h2, .about-section p {
            color: #fff;
            text-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .about-section h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 24px;
        }
        .about-section p {
            font-size: 1.3rem;
            margin-bottom: 32px;
        }
        .about-img {
            flex: 1 1 320px;
            min-width: 280px;
            max-width: 400px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
        }
        .about-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }
        .about-content {
            flex: 2 1 400px;
        }
        .about-content h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: #222;
        }
        .about-content p {
            font-size: 1.1rem;
            color: #444;
            margin-bottom: 18px;
            line-height: 1.7;
        }
        .features-row {
            display: flex;
            gap: 32px;
            margin: 48px auto 0 auto;
            justify-content: center;
            flex-wrap: wrap;
        }
        .feature-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(40,167,69,0.10), 0 1.5px 8px rgba(80,80,160,0.08);
            padding: 36px 32px 28px 32px;
            min-width: 260px;
            max-width: 340px;
            flex: 1 1 260px;
            text-align: center;
            transition: transform 0.22s cubic-bezier(.4,2,.6,1), box-shadow 0.22s, background 0.22s;
            margin-bottom: 18px;
            border: 2.5px solid rgba(40,167,69,0.13);
            position: relative;
            overflow: hidden;
        }
        .feature-card:before {
            display: none;
        }
        .feature-card:hover {
            transform: translateY(-10px) scale(1.045);
            box-shadow: 0 16px 48px rgba(40,167,69,0.18), 0 2px 12px rgba(80,80,160,0.10);
            background: #f8fafc;
            border-color: #28a745;
        }
        .feature-card i {
            font-size: 2.7rem;
            color: #6f74c6;
            margin-bottom: 18px;
            z-index: 1;
            position: relative;
            transition: color 0.2s;
        }
        .feature-card:hover i {
            color: #28a745;
        }
        .feature-card h3 {
            font-size: 1.25rem;
            color: #28a745;
            margin-bottom: 14px;
            font-weight: 700;
            z-index: 1;
            position: relative;
        }
        .feature-card p {
            color: #333;
            font-size: 1.08rem;
            z-index: 1;
            position: relative;
        }
        @media (max-width: 900px) {
            .features-row { flex-direction: column; align-items: center; gap: 24px; }
            .feature-card { max-width: 95vw; }
        }
        .team-section {
            max-width: 1100px;
            margin: 60px auto 0 auto;
            text-align: center;
        }
        .team-section h2 {
            color: #6f74c6;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .team-row {
            display: flex;
            gap: 32px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .team-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(80,80,160,0.10);
            padding: 28px 22px;
            min-width: 200px;
            max-width: 240px;
            flex: 1 1 200px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .team-card:hover {
            transform: translateY(-8px) scale(1.04);
            box-shadow: 0 8px 32px rgba(80,80,160,0.18);
        }
        .team-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #e0e7ff;
            margin: 0 auto 14px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #6f74c6;
            overflow: hidden;
        }
        .team-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .team-card h4 {
            margin: 0 0 6px 0;
            font-size: 1.1rem;
            color: #222;
            font-weight: 700;
        }
        .team-card p {
            color: #666;
            font-size: 0.98rem;
        }
        @media (max-width: 900px) {
            .about-section, .features-row, .team-row { flex-direction: column; align-items: center; }
            .about-img, .about-content { max-width: 100%; }
        }
        .hero-navbar {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 3vw;
            background: rgba(20, 20, 20, 0.75);
            box-sizing: border-box;
        }
        .hero-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            letter-spacing: 1px;
        }
        .hero-nav-links {
            list-style: none;
            display: flex;
            gap: 2.5rem;
            margin: 0;
            padding: 0;
        }
        .hero-nav-links li a {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .hero-nav-links li a:hover {
            color: #28a745;
        }
        @media (max-width: 700px) {
            .hero-navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 1rem 1vw;
            }
            .hero-nav-links {
                gap: 1.2rem;
                font-size: 1rem;
            }
        }
        .hero-section {
            width: 100vw;
            min-height: 60vh;
            background: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            position: relative;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(20, 20, 20, 0.55);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            color: #fff;
            padding: 80px 20px 60px 20px;
        }
        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 32px;
            color: #e0e7ff;
        }
        .discover-btn {
            background: #ff9800;
            color: #fff;
            border: none;
            border-radius: 30px;
            padding: 16px 38px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(255,152,0,0.13);
            transition: background 0.2s, transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .discover-btn:hover {
            background: #e65100;
            transform: translateY(-2px) scale(1.04);
        }
        @media (max-width: 700px) {
            .hero-content h1 { font-size: 2rem; }
            .hero-content p { font-size: 1rem; }
        }
        /* --- Our Story Animated Section --- */
        .our-story-section {
            width: 100%;
            max-width: 100%;
            margin: 60px 0 0 0;
            border-radius: 0 0 32px 32px;
            box-shadow: 0 8px 32px rgba(80,80,160,0.10);
            background: #fff;
            display: flex;
            gap: 48px;
            align-items: stretch;
            flex-wrap: wrap;
            padding-left: max(8vw, 16px);
            padding-right: max(8vw, 16px);
            padding-top: 48px;
            padding-bottom: 32px;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        .our-story-img-card {
            display: flex;
            align-items: stretch;
            justify-content: center;
            flex: 1 1 350px;
            min-width: 280px;
            animation: fadeInLeft 1s;
        }
        .our-story-img-card img {
            width: auto;
            height: 100%;
            max-width: 100%;
            max-height: none;
            display: block;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(80,80,160,0.13);
            object-fit: cover;
            margin: 0;
        }
        .our-story-content {
            flex: 2 1 400px;
            min-width: 300px;
            animation: fadeInRight 1s;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .our-story-content h2 {
            color: #2e8b57;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 18px;
        }
        .our-story-content p {
            color: #333;
            font-size: 1.15rem;
            margin-bottom: 18px;
            line-height: 1.7;
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @media (max-width: 900px) {
            .our-story-section {
                flex-direction: column;
                gap: 24px;
                padding: 32px 2vw;
                border-radius: 0 0 18px 18px;
            }
            .our-story-img-card, .our-story-content { width: 100%; min-width: 0; }
            .our-story-img-card { justify-content: center; align-items: center; }
            .our-story-img-card img {
                width: 100%;
                height: auto;
                max-width: 100%;
                max-height: 320px;
            }
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: rgba(0, 0, 0, 0.6);
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff !important;
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
    <div class="hero">
        <h1 class="font_0 wixui-rich-text__text" style="text-align:center; font-size:100px; font-family:'Times New Roman', serif;">About TasikBiruCamps</h1>
        <p>We are passionate about creating unforgettable outdoor experiences.</p>
        <p>Our mission is to connect people with nature, foster community, and inspire adventure in the heart of Malaysia's beautiful landscapes.</p>
        <a href="campsites.php" class="cta-btn">Explore Our Campsites</a>
    </div>
    <div class="our-story-section" id="about-section">
        <div class="our-story-img-card">
            <img src="backgroundcamp.jpg" alt="TasikBiruCamps Nature" />
        </div>
        <div class="our-story-content">
            <h2>Our Story</h2>
            <p>TasikBiruCamps was founded in 2018 by a group of passionate nature lovers and outdoor experts who wanted to make camping accessible, safe, and fun for everyone. Frustrated by the lack of easy booking and quality facilities, we set out to create a platform that connects people with the best camping experiences in Malaysia.</p>
            <p>Today, TasikBiruCamps partners with beautiful campsites, offers a variety of adventure packages, and brings together a vibrant community of campers, families, and explorers. Our mission is to inspire more people to experience the magic of nature, build friendships, and create memories that last a lifetime.</p>
        </div>
    </div>
    <div class="features-row">
                <div class="feature-card">
            <i class="fas fa-campground"></i>
            <h3>Beautiful Campsites</h3>
            <p>Enjoy scenic views, clean facilities, and a variety of camping options for all ages and groups.</p>
                </div>
                <div class="feature-card">
            <i class="fas fa-hiking"></i>
            <h3>Adventure Activities</h3>
            <p>From hiking and kayaking to team-building games, we offer activities for every adventurer.</p>
                </div>
                <div class="feature-card">
            <i class="fas fa-users"></i>
            <h3>Community & Events</h3>
            <p>Meet fellow campers, join our events, and make memories that last a lifetime.</p>
        </div>
    </div>
    <div class="team-section">
        <h2>Meet Our Team</h2>
        <div class="team-row">
            <div class="team-card">
                <div class="team-avatar"><i class="fas fa-user-tie"></i></div>
                <h4>Amirul</h4>
                <p>Founder & Camp Director</p>
            </div>
            <div class="team-card">
                <div class="team-avatar"><i class="fas fa-user-astronaut"></i></div>
                <h4>Siti</h4>
                <p>Adventure Coordinator</p>
                </div>
            <div class="team-card">
                <div class="team-avatar"><i class="fas fa-user-nurse"></i></div>
                <h4>Farhan</h4>
                <p>Safety & Wellness Lead</p>
            </div>
            <div class="team-card">
                <div class="team-avatar"><i class="fas fa-user-friends"></i></div>
                <h4>Nurul</h4>
                <p>Community Manager</p>
        </div>
        </div>
    </div>
</body>
</html>