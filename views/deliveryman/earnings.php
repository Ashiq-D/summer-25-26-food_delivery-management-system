<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Earnings | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/deliveryman.css?v=6">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Earnings";
      $pageSubtitle = "View your earnings from completed deliveries.";
      require __DIR__ . "/partials/header.php";
    ?>

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

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/deliveryman.js?v=3"></script>
</body>

</html>
