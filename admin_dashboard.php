
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - TasikBiruCamps</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f5f7fa;
      display: flex;
      min-height: 100vh;
      color: #333;
    }

    .sidebar {
      width: 250px;
      background: #2c3e50;
      color: #ecf0f1;
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding: 2rem 1rem;
      box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    }

    .sidebar h2 {
      font-weight: 600;
      font-size: 1.8rem;
      text-align: center;
      margin-bottom: 2rem;
    }

    .sidebar a {
      padding: 12px 20px;
      margin: 0.5rem 0;
      color: #bdc3c7;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 500;
      display: flex;
      align-items: center;
      transition: background 0.3s, color 0.3s;
    }

    .sidebar a i {
      margin-right: 12px;
      font-size: 1rem;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #2980b9;
      color: white;
    }

    .content {
      margin-left: 250px;
      padding: 2.5rem 3rem;
      width: calc(100% - 250px);
    }

    h1 {
      font-size: 2.2rem;
      font-weight: 600;
      margin-bottom: 2rem;
      color: #34495e;
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .card {
      background: white;
      padding: 1.5rem 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover .card-intro:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }

    .card h3 {
      font-size: 1.2rem;
      color: #555;
      margin-bottom: 0.5rem;
    }

    .card .value {
      font-size: 2rem;
      font-weight: 600;
      color: #2c3e50;
    }

    .chart-container {
      width: 100%;
      max-width: 400px;
      margin: 2rem;
      margin-top: 0rem;
    }

    .card.chart-container {
     padding: 1rem 1.5rem;
    }

    .profit-card 
    {
      background: #27ae60;
      color: white;
      padding: 20px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      max-width: 400px;
      flex: 1 1 300px;
    }

    .profit-card i 
    {
      font-size: 2rem;
      margin-right: 15px;
    }

    .profit-info span 
    {
      font-size: 1rem;
      display: block;
    }

    .profit-info strong 
    {
      font-size: 1.8rem;
    }

    .flex-row 
    {
      display: flex;
      flex-wrap: wrap; /* Ensures it wraps on small screens */
      margin-top: 2rem;
      align-items: flex-start;
    }

    .badge {
      background-color: #ccc;
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      display: inline-block;
      margin-top:0rem;
    }

    #trending-card {
      border-left: 8px solid #ccc;
      padding-left: 1rem;
      max-width: 500px; 
      margin-top: 0rem;
      flex: 1 1 300px;
    }

    p {
      font-size: 1.1rem;
      color: #555;
    }

    .card-intro {
      margin-bottom: 2rem;
      background: white;
      padding: 1.5rem 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    canvas#packageChart {
      width: 100% !important;
      height: auto !important;
      max-height: 280px;
    }
  </style>

</head>
<body>

  <div class="sidebar">
    <h2>Admin</h2>
    <a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_bookings.php"><i class="fas fa-calendar-check"></i> Manage Bookings</a>
    <a href="manage_campsites.php"><i class="fas fa-campground"></i> Manage Campsites</a>
    <a href="manage_staff.php"><i class="fas fa-users"></i> Manage Staff</a>
    <a href="manage_feedback.php"><i class="fas fa-comments"></i> Feedback Customer</a>
    <a href="customer_payment.php"><i class="fas fa-credit-card"></i> Customer Payment</a>
    <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
  
  <?php
    include 'db_connection.php';

    // Query total bookings
    $bookingQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings");
    $bookingCount = mysqli_fetch_assoc($bookingQuery)['total'];

    // Query total campsites
    $campsiteQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM campsites");
    $campsiteCount = mysqli_fetch_assoc($campsiteQuery)['total'];

    // Query total staff
    $staffQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM staff");
    $staffCount = mysqli_fetch_assoc($staffQuery)['total'];

    // Query total profit
    $profitQuery = mysqli_query($conn, "SELECT SUM(amount) as total FROM payments");
    $totalProfit = mysqli_fetch_assoc($profitQuery)['total'] ?? 0;

    // Dynamic package distribution
    $packageLabels = [];
    $packageCounts = [];

    $pkgQuery = mysqli_query($conn, "
      SELECT p.package_name, COUNT(b.booking_id) AS total 
      FROM packages p 
      LEFT JOIN bookings b ON b.package_id = p.package_id 
      GROUP BY p.package_name
    ");

    $maxCount = 0;
    $trendingPackages = [];

    while ($row = mysqli_fetch_assoc($pkgQuery)) {
        $packageLabels[] = $row['package_name'];
        $packageCounts[] = (int)$row['total'];
        if ($row['total'] > $maxCount) {
            $maxCount = $row['total'];
            $trendingPackages = [$row['package_name']];
        } elseif ($row['total'] == $maxCount && $maxCount > 0) {
            $trendingPackages[] = $row['package_name'];
        }
    }
  ?>


  <div class="content">
    <h1>Welcome, Admin 👋</h1>

    <div class="card-intro">
      <p>This is your dashboard. Use the sidebar to manage bookings, campsites, staff, feedback, and payments.</p>
    </div>

    <div class="dashboard-cards">
      <div class="card">
        <h3>Total Bookings</h3>
        <div class="value"><?= $bookingCount ?></div>
      </div>
      <div class="card">
        <h3>Total Campsites</h3>
        <div class="value"><?= $campsiteCount ?></div>
      </div>
      <div class="card">
        <h3>Staff Members</h3>
        <div class="value"><?= $staffCount ?></div>
      </div>
    </div>

    <div class="flex-row">
      <div class="profit-card">
      <i class="fas fa-money-bill-wave"></i>
      <div class="profit-info">
        <span>Total Profit</span>
        <strong>RM <?= number_format($totalProfit, 2) ?></strong>
      </div>
    </div>

      <div class="card chart-container">
        <h3>Package Selection Distribution</h3>
        <canvas id="packageChart"></canvas>
      </div>

      <div id="trending-card" class="card">
        <div id="trending-badge" class="badge">🔥 Trending</div>
        <h3>Most Trending Package</h3>
          <div id="trending-value" class="value">Loading...</div>
      </div>
    </div>
  </div>

    <!-- Chart.js Script -->
  <script>
    // Make PHP data available to JS
    window.packageCountsData = <?= json_encode($packageCounts) ?>;
    window.packageLabels = <?= json_encode($packageLabels) ?>;
    window.trendingPackages = <?= json_encode($trendingPackages) ?>;
  </script>
  <script src="admin_dashboard.js"></script>

</body>
</html>