<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";

$statusFilter = $_GET["status"] ?? "All";
$statuses = ["All", "Pending", "Preparing", "Prepared", "Ready", "On The Way", "Delivered", "Cancelled"];
if (!in_array($statusFilter, $statuses))
{
    $statusFilter = "All";
}

$orders = getAllOrders($statusFilter);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders &amp; Deliveries | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Orders & Deliveries";
      $pageSubtitle = "Monitor every order placed across CraveRush.";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <div class="tabs">
      <?php foreach ($statuses as $s) : ?>
        <a href="?status=<?= urlencode($s) ?>" class="tab-link <?= $statusFilter == $s ? "active" : "" ?>"><?= htmlspecialchars($s) ?></a>
      <?php endforeach; ?>
    </div>

    <section class="content-card">
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Restaurant</th>
              <th>Area</th>
              <th>Deliveryman</th>
              <th>Date</th>
              <th>Total</th>
              <th>Note</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order) : ?>

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
              elseif ($order["order_status"] == "Ready" || $order["order_status"] == "Prepared")
              {
                  $statusClass = "blue";
              }
              ?>

              <tr>
                <td>#<?= (int) $order["order_id"] ?></td>
                <td><?= $order["customer_name"] ? htmlspecialchars($order["customer_name"]) : "<em>Deleted account</em>" ?></td>
                <td><?= htmlspecialchars($order["restaurant_name"]) ?></td>
                <td><?= htmlspecialchars($order["area_name"]) ?></td>
                <td><?= $order["deliveryman_name"] ? htmlspecialchars($order["deliveryman_name"]) : "<em>Unassigned</em>" ?></td>
                <td><?= htmlspecialchars(date("d M, h:i A", strtotime($order["order_date"]))) ?></td>
                <td>৳<?= number_format($order["total_amount"], 2) ?></td>
                <td><?= $order["note"] ? htmlspecialchars($order["note"]) : "—" ?></td>
                <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($order["order_status"]) ?></span></td>
                <td>
                  <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this record? All associated data will also be deleted. This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete_order">
                    <input type="hidden" name="order_id" value="<?= (int) $order["order_id"] ?>">
                    <button type="submit" class="btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)) : ?>
              <tr><td colspan="10">No orders found for this filter.</td></tr>
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
