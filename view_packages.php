<?php
session_start();
include 'session_check.php';
checkCustomerSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Packages - TasikBiruCamps</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Arial', sans-serif;
    }

    body {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('summerCamp.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      min-height: 100vh;
    }

    .container {
      max-width: 100%;
      margin: 2rem auto;
      padding: 0 1rem;
    }

    .card-grid {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      padding: 0 1rem;
      flex-wrap: nowrap;
      justify-content: center;
      align-items: flex-start;
    }

    .card {
      background: rgba(255, 255, 255, 0.7);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
      flex: 0 0 calc(20% - 0.8rem);
      width: calc(20% - 0.8rem);
      backdrop-filter: blur(5px);
      display: flex;
      flex-direction: column;
      position: relative;
      height: auto;
    }

    .card:hover {
      transform: translateY(-5px);
      background: rgba(255, 255, 255, 0.85);
    }

    .card.expanded {
      transform: none;
    }

    .card.expanded .package-details {
      max-height: 300px;
      opacity: 1;
      padding: 0.75rem;
      margin: 0 0.75rem 0.75rem;
      pointer-events: auto;
    }

    .card-content {
      padding: 0.75rem;
      display: flex;
      flex-direction: column;
      min-height: 140px;
    }

    .card img {
      width: 100%;
      height: 130px;
      object-fit: cover;
    }

    .card h3 {
      margin: 0;
      color: #333;
      font-size: 0.9rem;
      font-weight: bold;
      min-height: 2.7rem;
      display: flex;
      align-items: flex-start;
    }

    .card p {
      margin: 0.5rem 0;
      color: #666;
      font-size: 0.8rem;
      line-height: 1.3;
      flex: 1;
      min-height: 3.2rem;
    }

    .package-details {
      max-height: 0;
      opacity: 0;
      padding: 0;
      margin: 0;
      background: rgba(0, 0, 0, 0.05);
      border-radius: 8px;
      overflow: hidden;
      transition: all 0.3s ease;
      pointer-events: none;
    }

    .package-details h4 {
      color: #333;
      margin: 0 0 0.5rem;
      font-size: 0.9rem;
    }

    .package-details ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .package-details li {
      padding: 0.2rem 0;
      color: #555;
      display: flex;
      align-items: center;
      font-size: 0.8rem;
    }

    .package-details li:before {
      content: "→";
      margin-right: 0.5rem;
      color: #ff0000;
    }

    .btn {
      display: inline-block;
      text-decoration: none;
      background: rgba(255, 0, 0, 0.9);
      color: white;
      padding: 0.4rem 1rem;
      text-align: center;
      margin: 0.5rem 0;
      border-radius: 5px;
      transition: all 0.3s ease;
      font-size: 0.8rem;
      cursor: pointer;
      border: none;
      width: fit-content;
      align-self: flex-start;
    }

    .btn:hover {
      background: rgba(204, 0, 0, 1);
    }

    @media (max-width: 768px) {
      .card-grid {
        flex-wrap: wrap;
        gap: 1rem;
      }
      
      .card {
        flex: 0 0 calc(50% - 0.5rem);
        width: calc(50% - 0.5rem);
      }
    }

    @media (max-width: 480px) {
      .card {
        flex: 0 0 100%;
        width: 100%;
      }
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const cards = document.querySelectorAll('.card');
      let currentlyExpanded = null;
      
      cards.forEach(card => {
        const viewBtn = card.querySelector('.btn');
        const details = card.querySelector('.package-details');
        
        viewBtn.addEventListener('click', function(e) {
          e.preventDefault();
          
          // If clicking the same card that's already expanded, just close it
          if (card === currentlyExpanded) {
            card.classList.remove('expanded');
            viewBtn.textContent = 'View';
            currentlyExpanded = null;
            return;
          }
          
          // If there's a different card expanded, close it first
          if (currentlyExpanded) {
            currentlyExpanded.classList.remove('expanded');
            currentlyExpanded.querySelector('.btn').textContent = 'View';
          }
          
          // Expand the clicked card
          card.classList.add('expanded');
          viewBtn.textContent = 'Close';
          currentlyExpanded = card;
          
          // Smooth scroll if needed
          const rect = card.getBoundingClientRect();
          const isVisible = (rect.top >= 0) && (rect.bottom <= window.innerHeight);
          
          if (!isVisible) {
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        });
      });
    });
  </script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
  <div class="card-grid">
    <!-- Package A -->
    <div class="card">
      <img src="package A.png" alt="Package A">
      <div class="card-content">
        <div>
          <h3>Package A - Adventure Rush</h3>
          <p>3 days of high-energy fun with war games, jungle trekking, and team challenges.</p>
          <button class="btn">View</button>
        </div>
        <div class="package-details">
          <h4>Package Details - 3 Days 2 Nights</h4>
          <ul>
            <li>Khemah (Camping)</li>
            <li>Water Confident</li>
            <li>Water Rescue</li>
            <li>Kayak</li>
            <li>LDK</li>
            <li>Night Walk</li>
            <li>Potensi Diri</li>
            <li>Jungle Tracking</li>
            <li>War Game</li>
            <li>Makan & Minum</li>
            <li>Adult-RM180 Kids-RM160</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Package B -->
    <div class="card">
      <img src="package B.png" alt="Package B">
      <div class="card-content">
        <div>
          <h3>Package B - Survival Quest</h3>
          <p>Test your limits with survival skills and rugged nature exploration over 3 days.</p>
          <button class="btn">View</button>
        </div>
        <div class="package-details">
          <h4>Package Details - 3 Days 2 Nights</h4>
          <ul>
            <li>Khemah (Camping)</li>
            <li>Water Confident</li>
            <li>Water Rescue</li>
            <li>Kayak</li>
            <li>LDK</li>
            <li>Night Walk</li>
            <li>Potensi Diri</li>
            <li>Jungle Tracking</li>
            <li>Survival</li>
            <li>Makan & Minum</li>
            <li>Adult-RM180 Kids-RM160</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Package C -->
    <div class="card">
      <img src="package C.png" alt="Package C">
      <div class="card-content">
        <div>
          <h3>Package C - Nature Explorer</h3>
          <p>Balanced 3-day outdoor adventure for nature lovers and confidence builders.</p>
          <button class="btn">View</button>
        </div>
        <div class="package-details">
          <h4>Package Details - 3 Days 2 Nights</h4>
          <ul>
            <li>Khemah (Camping)</li>
            <li>Water Confident</li>
            <li>Water Rescue</li>
            <li>Kayak</li>
            <li>LDK</li>
            <li>Night Walk</li>
            <li>Potensi Diri</li>
            <li>Jungle Tracking</li>
            <li>Makan & Minum</li>
            <li>Adult-RM180 Kids-RM160</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Package D -->
    <div class="card">
      <img src="package D.png" alt="Package D">
      <div class="card-content">
        <div>
          <h3>Package D - Rapid Adventure</h3>
          <p>An action-filled 2-day escape with water and jungle thrills.</p>
          <button class="btn">View</button>
        </div>
        <div class="package-details">
          <h4>Package Details - 2 Days 1 Night</h4>
          <ul>
            <li>Khemah (Camping)</li>
            <li>Water Confident</li>
            <li>Water Rescue</li>
            <li>Kayak</li>
            <li>LDK</li>
            <li>Night Walk</li>
            <li>Jungle Tracking</li>
            <li>Makan & Minum</li>
            <li>Adult-RM130 Kids-RM110</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Package E -->
    <div class="card">
      <img src="package E.png" alt="Package E">
      <div class="card-content">
        <div>
          <h3>Package E - Day Escape</h3>
          <p>One fun-filled day of water activities and outdoor bonding.</p>
          <button class="btn">View</button>
        </div>
        <div class="package-details">
          <h4>Package Details - 1 Day</h4>
          <ul>
            <li>Water Confident</li>
            <li>Water Rescue</li>
            <li>Kayak</li>
            <li>Makan & Minum</li>
            <li>Adult-RM120 Kids-RM100</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>