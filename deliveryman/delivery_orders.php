<?php
require_once "delivery_process.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assigned Deliveries | CraveRush</title>
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

      <a href="delivery_orders.php" class="menu-item active">
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

      <a href="delivery_earnings.php" class="menu-item">
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
        <h1>Assigned Deliveries</h1>
        <p>View the order currently assigned to you.</p>
      </div>

      <span class="count-badge">
        <?= $assignedOrder ? "1 Active Order" : "0 Active Orders" ?>
      </span>
    </header>

    <?php if ($assignedOrder): ?>

    <section class="order-card">
      <div class="order-card-top">
        <div>
          <span class="order-number">
            #CR-<?= (int) $assignedOrder["order_id"] ?>
          </span>

          <span class="status-badge gray">
            <?= htmlspecialchars($assignedOrder["delivery_status"]) ?>
          </span>
        </div>

        <strong class="order-price">
          ৳<?= number_format($assignedOrder["total_amount"], 0) ?>
        </strong>
      </div>

      <div class="order-addresses">
        <div class="address-box">
          <small>Pickup Restaurant</small>

          <h3>
            <?= htmlspecialchars($assignedOrder["restaurant_name"]) ?>
          </h3>

          <p>
            Area: <?= htmlspecialchars($assignedOrder["area_name"]) ?>
          </p>
        </div>

        <div class="address-arrow">→</div>

        <div class="address-box">
          <small>Customer</small>

          <h3>
            <?= htmlspecialchars($assignedOrder["customer_name"]) ?>
          </h3>

          <p>
            Delivery Area: <?= htmlspecialchars($assignedOrder["area_name"]) ?>
          </p>
        </div>
      </div>

      <div class="order-meta">
        <span>
          <strong>Payment Method:</strong>
          <?= htmlspecialchars($assignedOrder["payment_method"]) ?>
        </span>

        <span>
          <strong>Payment Status:</strong>
          <?= htmlspecialchars($assignedOrder["payment_status"]) ?>
        </span>

        <span>
          <strong>Items:</strong>
          <?= (int) $assignedOrder["item_count"] ?>
        </span>

        <span>
          <strong>Delivery Fee:</strong>
          ৳<?= number_format($assignedOrder["delivery_fee"], 0) ?>
        </span>
      </div>

      <div class="order-actions">
        <a href="delivery_current.php" class="btn-primary">
          View Details
        </a>
      </div>
    </section>

    <?php else: ?>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>No Assigned Delivery</h2>
          <p>You currently have no order assigned to you.</p>
        </div>
      </div>
    </section>

    <?php endif; ?>

  </main>

  <script src="delivery.js?v=1"></script>
</body>

</html>