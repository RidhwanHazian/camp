<?php
include 'db_connection.php';

// Fetch packages
$sql = "SELECT * FROM packages ORDER BY package_id ASC";
$result = mysqli_query($conn, $sql);
?>

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
      height: 180px;
      object-fit: cover;
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
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php
          $image = (!empty($row['photo']) && file_exists('Assets/' . $row['photo']))
            ? 'Assets/' . $row['photo']
            : 'default.jpg';
        ?>
        <div class="campsite-card">
          <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($row['package_name']) ?>">
          <h3><?= htmlspecialchars($row['package_name']) ?></h3>
          <p><?= htmlspecialchars($row['description']) ?></p>
          <a href="package_detail.php?package=<?= urlencode($row['package_name']) ?>" class="btn">View</a>
        </div>
      <?php endwhile; ?>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; 2025 TasikBiruCamps. All rights reserved.</p>
  </footer>
</body>
</html>
