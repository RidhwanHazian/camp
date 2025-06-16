<?php
session_start();

// Check if user is logged in and is a customer
if (!isset($_SESSION['customer_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>TasikBiruCamps - Experience Nature at Its Best</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Arial', sans-serif;
    }

    .hero {
      height: 100vh;
      background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('https://images.unsplash.com/photo-1682687220742-aba13b6e50ba?q=80&w=2070&auto=format&fit=crop');
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      margin-bottom: 2rem;
    }

    .hero h1 {
      font-size: 3.5rem;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .activities {
      padding: 2rem 5%;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .activity-card {
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      cursor: pointer;
    }

    .activity-card img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .activity-card:hover img {
      transform: scale(1.05);
    }

    .activity-title {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(transparent, rgba(0,0,0,0.7));
      color: white;
      padding: 1rem;
      text-align: center;
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2.5rem;
      }
    }
  </style>
</head>
<body>

<?php include 'header.php'; ?>
<section class="hero">
  <h1>Experience Nature at Its Best</h1>
</section>

<section class="activities">
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1571687949921-1306bfb24b72" alt="Perkemahan">
    <div class="activity-title">Perkemahan</div>
  </div>
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1572457089126-2d31444e48c9" alt="Kayak">
    <div class="activity-title">Kayak</div>
  </div>
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5" alt="Snoop">
    <div class="activity-title">Snoop</div>
  </div>
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1510525009512-ad7fc13eefab" alt="Fishing">
    <div class="activity-title">Fishing</div>
  </div>
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1478131143081-80f7f84ca84d" alt="Base Camp">
    <div class="activity-title">Base Camp</div>
  </div>
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1523987355523-c7b5b0dd90a7" alt="Pemandangan">
    <div class="activity-title">Pemandangan</div>
  </div>
  <div class="activity-card">
    <img src="https://images.unsplash.com/photo-1537225228614-56cc3556d7ed" alt="Santai">
    <div class="activity-title">Santai</div>
  </div>
</section>
</body>
</html> 