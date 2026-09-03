<?php
require_once "delivery_process.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delivery Dashboard | CraveRush</title>
  <link rel="stylesheet" href="delivery.css?v=5">
</head>

<body>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <img src="../assets/images/logo.png" alt="CraveRush">
    </div>

    <nav class="sidebar-menu">
      <a href="delivery_dashboard.php" class="menu-item active">
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
        <h1>Delivery Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($deliveryman["name"]) ?>.</p>
        <p>Area: <?= htmlspecialchars($deliveryman["area_name"]) ?></p>
      </div>

      <div class="availability">
        <div>
          <small>Online Status</small>
          <span id="onlineStatusText">
            <?= htmlspecialchars($deliveryman["online_status"]) ?>
          </span>
        </div>

        <button type="button" class="availability-toggle <?= ($deliveryman["online_status"] == "Online") ? "active" : "" ?>" id="onlineToggle">
          <span></span>
        </button>

        <div>
          <small>Availability</small>
          <strong id="availabilityStatus">
            <?= htmlspecialchars($deliveryman["availability_status"]) ?>
          </strong>
        </div>
      </div>
    </header>

    <section class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon">▤</div>

        <div>
          <p>Total Assigned</p>
          <h2><?= (int) $totalDeliveries ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">✓</div>

        <div>
          <p>Total Completed</p>
          <h2><?= (int) $completedDeliveries ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">●</div>

        <div>
          <p>Active Delivery</p>
          <h2><?= (int) $activeDeliveries ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">৳</div>

        <div>
          <p>Total Earnings</p>
          <h2>৳<?= number_format($totalEarnings, 2) ?></h2>
        </div>
      </div>

    </section>

    <section class="content-grid">

      <div class="content-card">
        <div class="card-heading">
          <div>
            <h2>Current Delivery</h2>

            <?php if ($activeOrder): ?>
              <p>Order #CR-<?= (int) $activeOrder["order_id"] ?></p>
            <?php else: ?>
              <p>No Active Order</p>
            <?php endif; ?>
          </div>

          <?php if ($activeOrder): ?>
            <span class="status-badge orange">
              <?= htmlspecialchars($activeOrder["delivery_status"]) ?>
            </span>
          <?php endif; ?>
        </div>

        <?php if ($activeOrder): ?>

        <div class="delivery-route">

          <div class="route-item">
            <span class="route-point restaurant-point"></span>

            <div>
              <small>Pickup</small>
              <h3><?= htmlspecialchars($activeOrder["restaurant_name"]) ?></h3>
              <p>Area: <?= htmlspecialchars($activeOrder["area_name"]) ?></p>
            </div>
          </div>

          <div class="route-line"></div>

          <div class="route-item">
            <span class="route-point customer-point"></span>

            <div>
              <small>Delivery</small>
              <h3><?= htmlspecialchars($activeOrder["customer_name"]) ?></h3>
              <p>Delivery Area: <?= htmlspecialchars($activeOrder["area_name"]) ?></p>
            </div>
          </div>

        </div>

        <div class="order-info-grid">
          <div>
            <span>Order Amount</span>
            <strong>৳<?= number_format($activeOrder["total_amount"], 2) ?></strong>
          </div>

          <div>
            <span>Payment Method</span>
            <strong><?= htmlspecialchars($activeOrder["payment_method"]) ?></strong>
          </div>

          <div>
            <span>Payment Status</span>
            <strong><?= htmlspecialchars($activeOrder["payment_status"]) ?></strong>
          </div>
        </div>

        <a href="delivery_current.php" class="primary-link">View Current Delivery</a>

        <?php else: ?>

        <div class="empty-delivery">
          <p>You do not have an active delivery right now.</p>
        </div>

        <?php endif; ?>
      </div>

      <div class="content-card">
        <div class="card-heading">
          <div>
            <h2>Delivery Summary</h2>
            <p>Overall delivery summary</p>
          </div>
        </div>

        <div class="summary-list">
          <div class="summary-row">
            <span>Total Deliveries</span>
            <strong><?= (int) $totalDeliveries ?></strong>
          </div>

          <div class="summary-row">
            <span>Completed</span>
            <strong><?= (int) $completedDeliveries ?></strong>
          </div>

          <div class="summary-row">
            <span>Active Delivery</span>
            <strong><?= (int) $activeDeliveries ?></strong>
          </div>

          <div class="summary-row">
            <span>Total Earnings</span>
            <strong class="orange-text">৳<?= number_format($totalEarnings, 2) ?></strong>
          </div>
        </div>

        <a href="delivery_earnings.php" class="secondary-link">View Earnings</a>
      </div>

    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Assigned Delivery</h2>
          <p>Your currently assigned order</p>
        </div>

        <a href="delivery_orders.php" class="view-all">View</a>
      </div>

      <?php if ($activeOrder): ?>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Restaurant</th>
              <th>Delivery Area</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>
            <tr>
              <td>#CR-<?= (int) $activeOrder["order_id"] ?></td>
              <td><?= htmlspecialchars($activeOrder["restaurant_name"]) ?></td>
              <td><?= htmlspecialchars($activeOrder["area_name"]) ?></td>
              <td>৳<?= number_format($activeOrder["total_amount"], 2) ?></td>
              <td>
                <span class="status-badge gray">
                  <?= htmlspecialchars($activeOrder["delivery_status"]) ?>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <?php else: ?>

      <p>No order is currently assigned to you.</p>

      <?php endif; ?>

    </section>

  </main>

  <script src="delivery.js?v=1"></script>
</body>

</html>