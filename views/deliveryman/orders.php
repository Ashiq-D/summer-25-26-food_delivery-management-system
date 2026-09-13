<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assigned Deliveries | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/deliveryman.css?v=6">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Assigned Deliveries";
      $pageSubtitle = "View the order currently assigned to you.";
      $headerExtra = '<span class="count-badge">' . ($assignedOrder ? "1 Active Order" : "0 Active Orders") . '</span>';
      require __DIR__ . "/partials/header.php";
    ?>

    <?php if (!empty($successMessage)): ?>

    <div class="success-message">
      <?= htmlspecialchars($successMessage) ?>
    </div>

    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>

    <div class="error-message">
      <?= htmlspecialchars($errorMessage) ?>
    </div>

    <?php endif; ?>

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
        <a href="current.php" class="btn-primary">
          View Details
        </a>
      </div>
    </section>

    <?php else: ?>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Available Orders Near You</h2>
          <p>Pick up an order that's ready for delivery in your area.</p>
        </div>
      </div>

      <?php if (empty($availableOrders)): ?>

      <p>No orders are ready for pickup in your area right now. Check back soon.</p>

      <?php else: ?>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order</th>
              <th>Restaurant</th>
              <th>Area</th>
              <th>Items</th>
              <th>Payment</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($availableOrders as $available): ?>

            <tr>
              <td>#CR-<?= (int) $available["order_id"] ?></td>
              <td><?= htmlspecialchars($available["restaurant_name"]) ?></td>
              <td><?= htmlspecialchars($available["area_name"]) ?></td>
              <td><?= (int) $available["item_count"] ?></td>
              <td><?= htmlspecialchars($available["payment_method"]) ?></td>
              <td>৳<?= number_format($available["total_amount"], 0) ?></td>
              <td>
                <form method="POST" action="orders.php">
                  <input type="hidden" name="action" value="claim_order">
                  <input type="hidden" name="order_id" value="<?= (int) $available["order_id"] ?>">
                  <button type="submit" class="btn-primary">Pick Up</button>
                </form>
              </td>
            </tr>

            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php endif; ?>
    </section>

    <?php endif; ?>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/deliveryman.js?v=3"></script>
</body>

</html>
