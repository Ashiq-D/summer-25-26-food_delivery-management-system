<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delivery History | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/deliveryman.css?v=6">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Delivery History";
      $pageSubtitle = "View your completed deliveries.";
      require __DIR__ . "/partials/header.php";
    ?>

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

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/deliveryman.js?v=3"></script>
</body>

</html>
