<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delivery History | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/deliveryman.css?v=1">
</head>

<body>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <img src="../../assets/images/logo.png" alt="CraveRush">
    </div>

    <nav class="sidebar-menu">
      <a href="dashboard.php" class="menu-item">
        <img src="../../assets/images/home.png" alt="Dashboard" class="menu-icon">
        Dashboard
      </a>

      <a href="orders.php" class="menu-item">
        <img src="../../assets/images/assigned.png" alt="Assigned Deliveries" class="menu-icon">
        Assigned Deliveries
      </a>

      <a href="current.php" class="menu-item">
        <img src="../../assets/images/currentDelivery.png" alt="Current Delivery" class="menu-icon">
        Current Delivery
      </a>

      <a href="history.php" class="menu-item active">
        <img src="../../assets/images/history.png" alt="Delivery History" class="menu-icon">
        Delivery History
      </a>

      <a href="earnings.php" class="menu-item">
        <img src="../../assets/images/earnings.png" alt="Earnings" class="menu-icon">
        Earnings
      </a>

      <a href="profile.php" class="menu-item">
        <img src="../../assets/images/profile.png" alt="Profile" class="menu-icon">
        Profile
      </a>
    </nav>

    <div class="sidebar-bottom">
      <a href="../../controllers/deliveryman_controller.php?action=logout" class="logout-btn">Logout</a>
    </div>
  </aside>

  <main class="main-content">

    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle">☰</button>

      <div>
        <h1>Delivery History</h1>
        <p>View your completed deliveries.</p>
      </div>
    </header>

    <section class="content-card">

      <div class="card-heading">
        <div>
          <h2>Completed Deliveries</h2>
          <p>Your previous successfully delivered orders.</p>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Restaurant</th>
              <th>Customer</th>
              <th>Delivery Area</th>
              <th>Order Date</th>
              <th>Earning</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>

            <?php if ($deliveryHistory && mysqli_num_rows($deliveryHistory) > 0): ?>

              <?php while ($delivery = mysqli_fetch_assoc($deliveryHistory)): ?>

              <tr>
                <td>#CR-<?= (int) $delivery["order_id"] ?></td>

                <td>
                  <?= htmlspecialchars($delivery["restaurant_name"]) ?>
                </td>

                <td>
                  <?= htmlspecialchars($delivery["customer_name"]) ?>
                </td>

                <td>
                  <?= htmlspecialchars($delivery["area_name"]) ?>
                </td>

                <td>
                  <?= date("d M Y", strtotime($delivery["order_date"])) ?>
                </td>

                <td>
                  ৳<?= number_format($delivery["delivery_fee"], 0) ?>
                </td>

                <td>
                  <span class="status-badge green">
                    <?= htmlspecialchars($delivery["delivery_status"]) ?>
                  </span>
                </td>
              </tr>

              <?php endwhile; ?>

            <?php else: ?>

              <tr>
                <td colspan="7">
                  No delivery history found.
                </td>
              </tr>

            <?php endif; ?>

          </tbody>
        </table>
      </div>

    </section>

  </main>

  <script src="../../assets/js/deliveryman.js?v=1"></script>
</body>

</html>
