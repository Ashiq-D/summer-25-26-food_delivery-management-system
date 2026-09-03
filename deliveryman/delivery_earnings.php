<?php
require_once "delivery_process.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Earnings | CraveRush</title>
  <link rel="stylesheet" href="delivery.css?v=1">
</head>

<body>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <img src="../assets/images/logo.png" alt="CraveRush">
    </div>

    <nav class="sidebar-menu">
      <a href="delivery_dashboard.php" class="menu-item">
        <img src="../assets/images/home.png" alt="Dashboard" class="menu-icon">
        Dashboard
      </a>

      <a href="delivery_orders.php" class="menu-item">
        <img src="../assets/images/assigned.png" alt="Assigned Deliveries" class="menu-icon">
        Assigned Deliveries
      </a>

      <a href="delivery_current.php" class="menu-item">
        <img src="../assets/images/currentDelivery.png" alt="Current Delivery" class="menu-icon">
        Current Delivery
      </a>

      <a href="delivery_history.php" class="menu-item">
        <img src="../assets/images/history.png" alt="Delivery History" class="menu-icon">
        Delivery History
      </a>

      <a href="delivery_earnings.php" class="menu-item active">
        <img src="../assets/images/earnings.png" alt="Earnings" class="menu-icon">
        Earnings
      </a>

      <a href="delivery_profile.php" class="menu-item">
        <img src="../assets/images/profile.png" alt="Profile" class="menu-icon">
        Profile
      </a>
    </nav>

    <div class="sidebar-bottom">
      <a href="../login.php" class="logout-btn">Logout</a>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle">☰</button>

      <div>
        <h1>Earnings</h1>
        <p>View your earnings from completed deliveries.</p>
      </div>
    </header>

    <section class="stats-grid earnings-stats">

      <div class="stat-card">
        <div class="stat-icon">৳</div>

        <div>
          <p>Total Earnings</p>
          <h2>৳<?= number_format($totalEarnings, 0) ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">✓</div>

        <div>
          <p>Completed Deliveries</p>
          <h2><?= (int) $completedDeliveries ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">৳</div>

        <div>
          <p>Average Delivery Fee</p>
          <h2>৳<?= number_format($averageDeliveryFee, 0) ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">৳</div>

        <div>
          <p>Latest Delivery Fee</p>
          <h2>৳<?= number_format($latestDeliveryFee, 0) ?></h2>
        </div>
      </div>

    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Earning History</h2>
          <p>Earnings from successfully delivered orders</p>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Order Date</th>
              <th>Restaurant</th>
              <th>Delivery Fee</th>
              <th>Earning</th>
            </tr>
          </thead>

          <tbody>

            <?php if ($earningHistory && mysqli_num_rows($earningHistory) > 0): ?>

              <?php while ($earning = mysqli_fetch_assoc($earningHistory)): ?>

              <tr>
                <td>#CR-<?= (int) $earning["order_id"] ?></td>
                <td><?= date("d M Y", strtotime($earning["order_date"])) ?></td>
                <td><?= htmlspecialchars($earning["restaurant_name"]) ?></td>
                <td>৳<?= number_format($earning["delivery_fee"], 0) ?></td>
                <td class="earning-value">৳<?= number_format($earning["delivery_fee"], 0) ?></td>
              </tr>

              <?php endwhile; ?>

            <?php else: ?>

              <tr>
                <td colspan="5">No earning history found.</td>
              </tr>

            <?php endif; ?>

          </tbody>
        </table>
      </div>
    </section>

  </main>

  <script src="delivery.js?v=1"></script>
</body>

</html>