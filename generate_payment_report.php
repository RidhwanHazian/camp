<?php
session_start();
include 'db_connection.php';
include 'session_check.php';
checkAdminSession();

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$sql = "
  SELECT 
    b.full_name,
    b.phone_no,
    pk.package_name,
    p.payment_date,
    p.amount
  FROM payments p
  JOIN bookings b ON p.booking_id = b.booking_id
  JOIN packages pk ON b.package_id = pk.package_id
  WHERE DATE(p.payment_date) BETWEEN '$startDate' AND '$endDate'
  ORDER BY p.payment_date ASC
";

$result = mysqli_query($conn, $sql);
$total_payment = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Payment Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f4f4; }
        .container { max-width: 800px; margin: 50px auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 0 12px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f8f9fa; text-transform: uppercase; font-weight: 600; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: left; padding-top: 20px; font-size: 16px; }
        .back-link { display: block; text-align: center; margin-top: 2rem; color: #2980b9; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        @media print {
            .back-link { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
<div class="container">
    <h2>Customer Payment Report</h2>
    <p style="text-align:center; margin-top: -10px;">
        From <?= htmlspecialchars($startDate) ?> to <?= htmlspecialchars($endDate) ?>
    </p>

    <?php
    ob_start(); // Start output buffering for table rows

    if ($result && mysqli_num_rows($result) > 0) {
        $count = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $total_payment += $row['amount'];
            echo "<tr>
                    <td>{$count}</td>
                    <td>" . htmlspecialchars($row['full_name']) . "</td>
                    <td>" . htmlspecialchars($row['package_name']) . "</td>
                    <td>" . date('Y-m-d', strtotime($row['payment_date'])) . "</td>
                    <td>" . number_format($row['amount'], 2) . "</td>
                    <td>" . htmlspecialchars($row['phone_no']) . "</td>
                  </tr>";
            $count++;
        }
    } else {
        echo "<tr><td colspan='6'>No payments found for this month.</td></tr>";
    }

    $tableRows = ob_get_clean(); // Store output in variable and stop buffering
    ?>

    <!-- ✅ Display total above table -->
    <p class="total">Total Payment: RM <?= number_format($total_payment, 2) ?></p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Package</th>
                <th>Payment Date</th>
                <th>Amount (RM)</th>
                <th>Phone</th>
            </tr>
        </thead>
        <tbody>
            <?= $tableRows ?>
        </tbody>
    </table>

    <a href="customer_payment.php" class="back-link">← Back to Customer Payment</a>
</div>

</body>
</html>
