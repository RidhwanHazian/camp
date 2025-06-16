<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Campsites - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
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
    }

    .logo {
      font-size: 1.5rem;
      font-weight: bold;
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

    .campsites-section {
      padding: 3rem 2rem;
      text-align: center;
    }

    .campsites-section h1 {
      font-size: 2.5rem;
      margin-bottom: 2rem;
    }

    .campsite-list {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 2rem;
    }

    .campsite-card {
      background-color: rgba(0, 0, 0, 0.6);
      padding: 1rem;
      border-radius: 10px;
      width: 280px;
      color: #fff;
      transition: transform 0.3s ease;
    }

    .campsite-card:hover {
      transform: scale(1.05);
    }

    .campsite-card img {
      width: 100%;
      border-radius: 10px;
    }

    .campsite-card h3 {
      margin-top: 1rem;
      font-size: 1.5rem;
    }

    .campsite-card p {
      font-size: 1rem;
      margin: 0.5rem 0 1rem;
    }

    .btn {
      display: inline-block;
      padding: 0.5rem 1rem;
      background-color: #28a745;
      color: #fff;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }

    .footer {
      background-color: rgba(0, 0, 0, 0.6);
      text-align: center;
      padding: 1rem;
      margin-top: 3rem;
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">TasikBiruCamps</div>
    <ul class="nav-links">
      <li><a href="homepage.php">Home</a></li>
      <li><a href="about.php">About Us</a></li>
      <li><a href="campsites.php">Campsites</a></li>
      <li><a href="login.php">Login</a></li>
    </ul>
  </nav>

  <section class="campsites-section">
    <h1>Our Camping Packages</h1>
    <div class="campsite-list">
      
      <!-- Package A -->
      <div class="campsite-card">
        <img src="package A.png" alt="Package A">
        <h3>Package A - Adventure Rush</h3>
        <p>3 days of high-energy fun with war games, jungle trekking, and team challenges.</p>
        <a href="package_detail.php?package=A" class="btn">View</a>
      </div>

      <!-- Package B -->
      <div class="campsite-card">
        <img src="package B.png" alt="Package B">
        <h3>Package B - <br>Survival Quest</h3>
        <p>Test your limits with survival skills and rugged nature exploration over 3 days.</p>
        <a href="package_detail.php?package=B" class="btn">View</a>
      </div>

      <!-- Package C -->
      <div class="campsite-card">
        <img src="package C.png" alt="Package C">
        <h3>Package C - <br>Nature Explorer</h3>
        <p>3 days of balanced outdoor adventure, perfect for connecting with nature and building confidence.</p>
        <a href="package_detail.php?package=C" class="btn">View</a>
      </div>

      <!-- Package D -->
      <div class="campsite-card">
        <img src="package D.png" alt="Package D">
        <h3>Package D - <br>Rapid Adventure</h3>
        <p>An action-filled 2-day escape with water and jungle thrills.</p>
        <a href="package_detail.php?package=D" class="btn">View</a>
      </div>

      <!-- Package E -->
      <div class="campsite-card">
        <img src="package E.png" alt="Package E">
        <h3>Package E - <br>Day Escape</h3>
        <p>One fun-filled day of water activities and outdoor bonding.</p>
        <a href="package_detail.php?package=E" class="btn">View</a>
      </div>

    </div>
  </section>

  <footer class="footer">
    <p>&copy; 2025 TasikBiruCamps. All rights reserved.</p>
  </footer>
</body>
</html>
