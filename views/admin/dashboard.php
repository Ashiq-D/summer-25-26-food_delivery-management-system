<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";

$stats = getDashboardStats();
$recentOrders = getRecentOrders(5);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Admin Dashboard";
      $pageSubtitle = "Welcome back, " . $admin["name"] . "!";
      require __DIR__ . "/partials/header.php";
    ?>

    <section class="stats-grid">

      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div>
          <p>Total Customers</p>
          <h2><?= (int) $stats["total_customers"] ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🍽</div>
        <div>
          <p>Total Restaurants</p>
          <h2><?= (int) $stats["total_restaurants"] ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">🛵</div>
        <div>
          <p>Total Deliverymen</p>
          <h2><?= (int) $stats["total_deliverymen"] ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">●</div>
        <div>
          <p>Active Orders</p>
          <h2><?= (int) $stats["active_orders"] ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">৳</div>
        <div>
          <p>Today's Order Value</p>
          <h2>৳<?= number_format($stats["today_order_value"], 2) ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div>
          <p>Today's CraveRush Revenue</p>
          <h2>৳<?= number_format($stats["today_craverush_revenue"], 2) ?></h2>
        </div>
      </div>

      <div class="stat-card <?= ($stats["pending_area_requests"] > 0) ? "alert" : "" ?>">
        <div class="stat-icon">✉</div>
        <div>
          <p>Pending Area Requests</p>
          <h2><?= (int) $stats["pending_area_requests"] ?></h2>
        </div>
      </div>

      <div class="stat-card <?= ($stats["pending_cancellations"] > 0) ? "alert" : "" ?>">
        <div class="stat-icon">✕</div>
        <div>
          <p>Pending Cancellations</p>
          <h2><?= (int) $stats["pending_cancellations"] ?></h2>
        </div>
      </div>

    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Recent Orders</h2>
          <p>The latest orders placed across CraveRush.</p>
        </div>
      </div>

      <?php if (empty($recentOrders)) : ?>

        <p>No orders yet.</p>

      <?php else : ?>

        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Restaurant</th>
              <th>Area</th>
              <th>Date</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($recentOrders as $order) : ?>

              <?php
              $statusClass = "gray";

              if ($order["order_status"] == "Delivered")
              {
                  $statusClass = "green";
              }
              elseif ($order["order_status"] == "Cancelled")
              {
                  $statusClass = "red";
              }
              elseif ($order["order_status"] == "On The Way")
              {
                  $statusClass = "orange";
              }
              elseif ($order["order_status"] == "Ready" || $order["order_status"] == "Prepared" || $order["order_status"] == "Preparing")
              {
                  $statusClass = "blue";
              }
              ?>

              <tr>
                <td>#<?= (int) $order["order_id"] ?></td>
                <td><?= htmlspecialchars($order["restaurant_name"]) ?></td>
                <td><?= htmlspecialchars($order["area_name"]) ?></td>
                <td><?= htmlspecialchars(date("d M, h:i A", strtotime($order["order_date"]))) ?></td>
                <td>৳<?= number_format($order["total_amount"], 2) ?></td>
                <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($order["order_status"]) ?></span></td>
              </tr>

            <?php endforeach; ?>
          </tbody>
        </table>

      <?php endif; ?>
    </section>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/admin.js?v=1"></script>
</body>

</html>
