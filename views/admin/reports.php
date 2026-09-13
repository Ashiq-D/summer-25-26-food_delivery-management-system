<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";

$report = getRevenueReport();
$deliveryFee = getDeliveryFee();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Reports";
      $pageSubtitle = "Revenue and profit generated from Delivered orders (10% of food subtotal).";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Delivery Fee</h2>
          <p>The fixed delivery fee paid directly to the Deliveryman on every order.</p>
        </div>
      </div>

      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <input type="hidden" name="action" value="update_delivery_fee">
        <div class="form-grid">
          <div class="form-group">
            <label>Delivery Fee (৳)</label>
            <input type="number" step="0.01" min="0.01" name="delivery_fee" value="<?= htmlspecialchars((string) $deliveryFee) ?>" required>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Update Delivery Fee</button>
        </div>
      </form>
    </section>

    <section class="reports-summary">
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div>
          <p>Delivered Orders</p>
          <h2><?= (int) $report["delivered_orders"] ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">৳</div>
        <div>
          <p>Total Food Subtotal</p>
          <h2>৳<?= number_format($report["total_food_subtotal"], 2) ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div>
          <p>CraveRush Revenue (10%)</p>
          <h2>৳<?= number_format($report["total_revenue"], 2) ?></h2>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div>
          <p>Profit</p>
          <h2>৳<?= number_format($report["total_profit"], 2) ?></h2>
        </div>
      </div>
    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Monthly Breakdown</h2>
          <p>Revenue by month, based on Delivered orders.</p>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Month</th>
              <th>Delivered Orders</th>
              <th>Food Subtotal</th>
              <th>Revenue (10%)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($report["monthly"] as $row) : ?>
              <tr>
                <td><?= htmlspecialchars(date("F Y", strtotime($row["month"] . "-01"))) ?></td>
                <td><?= (int) $row["delivered_orders"] ?></td>
                <td>৳<?= number_format($row["food_subtotal"], 2) ?></td>
                <td>৳<?= number_format($row["revenue"], 2) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($report["monthly"])) : ?>
              <tr><td colspan="4">No delivered orders yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/admin.js?v=1"></script>
</body>

</html>
