<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkCustomerSession();

$packages_sql = "
  SELECT p.*, pr.adult_price, pr.child_price
  FROM packages p
  LEFT JOIN package_prices pr ON p.package_id = pr.package_id
  ORDER BY p.package_id ASC
";
$packages_result = mysqli_query($conn, $packages_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
      flex-wrap: wrap;
      justify-content: flex-start;
      align-items: flex-start;
      gap: 1rem;
      margin-top: 2rem;
      padding: 0 1rem;
    }

    .card {
      background: rgba(255, 255, 255, 0.7);
      border-radius: 10px;
      overflow: visible;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      transition: all 0.3s ease;
      width: calc(20% - 1rem);
      min-width: 220px;
      position: relative;
      display: flex;
      flex-direction: column;
      margin-bottom: 4.5rem; /* ✅ leave space for dropdown to appear */
    }

    .card img {
      width: 100%;
      height: 130px;
      object-fit: cover;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
    }

    .card-content {
      padding: 0.75rem;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    .card h3 {
      font-size: 1rem;
      color: #333;
      margin-bottom: 0.5rem;
    }

    .card p {
      font-size: 0.85rem;
      color: #555;
      margin-bottom: 0.5rem;
      flex-grow: 1;
    }

    .btn {
      background: rgba(255, 0, 0, 0.9);
      color: #fff;
      border: none;
      padding: 6px 10px;
      border-radius: 5px;
      font-size: 0.8rem;
      cursor: pointer;
      margin: 0.75rem 0 0;
      align-self: flex-start;
    }

    .btn:hover {
      background: rgba(204, 0, 0, 1);
    }

    .package-details {
      position: absolute;
      top: 100%;
      left: 0;
      width: 100%;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      border-bottom-left-radius: 10px;
      border-bottom-right-radius: 10px;
      opacity: 0;
      max-height: 0;
      overflow: hidden;
      pointer-events: none;
      transition: opacity 0.3s ease, max-height 0.3s ease, padding 0.3s ease;
      z-index: 2;
      padding: 0;
    }

    .card.expanded .package-details {
      opacity: 1;
      max-height: 500px;
      padding: 0.75rem;
      pointer-events: auto;
    }

    .package-details h4 {
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      color: #333;
    }

    .package-details ul {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    .package-details li {
      font-size: 0.8rem;
      color: #444;
      margin-bottom: 0.3rem;
      display: flex;
      align-items: center;
    }

    .package-details li::before {
      content: "→";
      margin-right: 6px;
      color: #e74c3c;
    }

    @media (max-width: 768px) {
      .card {
        width: calc(50% - 1rem);
      }
    }

    @media (max-width: 480px) {
      .card {
        width: 100%;
      }
    }

  </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
  <div class="card-grid">
    <?php while ($pkg = mysqli_fetch_assoc($packages_result)): ?>
      <?php
        $img = (!empty($pkg['photo']) && file_exists('Assets/' . $pkg['photo']))
          ? 'Assets/' . $pkg['photo']
          : 'default.jpg';
        $activities = array_filter(array_map('trim', explode(',', $pkg['activity'] ?? '')));
      ?>
      <div class="card">
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($pkg['package_name']) ?>">
        <div class="card-content">
          <h3><?= htmlspecialchars($pkg['package_name']) ?></h3>
          <p><?= htmlspecialchars($pkg['description']) ?></p>
          <button class="btn">View</button>
        </div>
        <div class="package-details">
          <h4>Package Details - <?= htmlspecialchars($pkg['duration']) ?></h4>
          <ul>
            <?php foreach ($activities as $act): ?>
              <li><?= htmlspecialchars($act) ?></li>
            <?php endforeach; ?>
            <li>Adult - RM<?= number_format($pkg['adult_price'], 2) ?></li>
            <li>Kids - RM<?= number_format($pkg['child_price'], 2) ?></li>
          </ul>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
      const btn = card.querySelector('.btn');
      btn.addEventListener('click', function () {
        const isExpanded = card.classList.contains('expanded');

        // Collapse all cards except the clicked one
        cards.forEach(c => {
          if (c !== card) {
            c.classList.remove('expanded');
            c.querySelector('.btn').textContent = 'View';
          }
        });

        // Toggle the current card
        if (isExpanded) {
          card.classList.remove('expanded');
          btn.textContent = 'View';
        } else {
          card.classList.add('expanded');
          btn.textContent = 'Close';
        }
      });
    });
  });
</script>

</body>
</html>
