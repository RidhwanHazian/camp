<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Camping Reservation</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

  <style>
    body {
      margin: 0;
      font-family: 'Montserrat', sans-serif;
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
      background-image: url('backgroundcamp.jpg'); /* Your custom image */
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
      background-color: rgba(0, 0, 0, 0.5); /* Dark overlay */
      z-index: -1;
    }

    .header, .campsites-section, .footer {
      text-align: center;
      padding: 2rem;
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

    .hero {
      padding: 5rem 2rem;
    }

    .hero h1 {
      font-size: 3rem;
      margin-bottom: 1rem;
    }

    .hero p {
      font-size: 1.25rem;
      margin-bottom: 2rem;
    }

    .btn {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      background-color: #28a745;
      color: #fff;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }

    .footer {
      background-color: rgba(0, 0, 0, 0.6);
      padding: 1rem;
      margin-top: 2rem;
    }
  </style>
</head>
<body>
  <header class="header">
    <nav class="navbar">
      <div class="logo">TasikBiruCamps</div>
      <ul class="nav-links">
        <li><a href="#">Home</a></li>
        <li><a href="campsites.php">Campsites</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
      </ul>
    </nav>
    <div class="hero">
      <h1 class="font_0 wixui-rich-text__text" style="text-align:center; font-size:100px; font-family:'Times New Roman', serif;">
        Find Your Perfect Campsite
      </h1>

      <p>Book nature’s best spots with ease and comfort.</p>
      <a href="about.php" class="btn">About Us</a>
    </div>
  </header>

  <br><br>
  <br><br>
  <br><br>
  <br><br>
  <br><br>
  <br><br>
  <br><br>
  <footer class="footer">
    <p>&copy; 2025 TasikBiruCamps. All rights reserved.</p>
  </footer>
</body>
</html>
