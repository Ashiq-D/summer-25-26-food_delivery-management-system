<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Current Delivery | CraveRush</title>
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

      <a href="current.php" class="menu-item active">
        <img src="../../assets/images/currentDelivery.png" alt="Current Delivery" class="menu-icon">
        Current Delivery
      </a>

      <a href="history.php" class="menu-item">
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
        <h1>Current Delivery</h1>

        <?php if ($currentDelivery): ?>
          <p>Order #CR-<?= (int) $currentDelivery["order_id"] ?></p>
        <?php else: ?>
          <p>No Active Order</p>
        <?php endif; ?>
      </div>

      <?php if ($currentDelivery): ?>
        <span class="status-badge orange" id="currentStatus">
          <?= htmlspecialchars($currentDelivery["order_status"]) ?>
        </span>
      <?php endif; ?>
    </header>

    <?php if ($currentDelivery): ?>

    <section class="content-card">

      <div class="delivery-progress">

        <div class="progress-step active" data-step="1">
          <span>1</span>
          <p>Ready / Assigned</p>
        </div>

        <div class="progress-line"></div>

        <div class="progress-step <?= ($currentDelivery["order_status"] == "On The Way") ? "active" : "" ?>" data-step="2">
          <span>2</span>
          <p>On The Way</p>
        </div>

        <div class="progress-line"></div>

        <div class="progress-step" data-step="3" id="deliveredStep">
          <span>3</span>
          <p>Delivered</p>
        </div>

      </div>

    </section>

    <section class="content-grid">

      <div class="content-card">
        <div class="card-heading">
          <div>
            <h2>Pickup Information</h2>
            <p>Restaurant details</p>
          </div>
        </div>

        <div class="details-list">
          <div>
            <span>Restaurant</span>
            <strong><?= htmlspecialchars($currentDelivery["restaurant_name"]) ?></strong>
          </div>

          <div>
            <span>Area</span>
            <strong><?= htmlspecialchars($currentDelivery["area_name"]) ?></strong>
          </div>

          <div>
            <span>Phone</span>
            <strong><?= htmlspecialchars($currentDelivery["restaurant_phone"]) ?></strong>
          </div>
        </div>
      </div>

      <div class="content-card">
        <div class="card-heading">
          <div>
            <h2>Customer Information</h2>
            <p>Delivery details</p>
          </div>
        </div>

        <div class="details-list">
          <div>
            <span>Customer</span>
            <strong><?= htmlspecialchars($currentDelivery["customer_name"]) ?></strong>
          </div>

          <div>
            <span>Delivery Area</span>
            <strong><?= htmlspecialchars($currentDelivery["area_name"]) ?></strong>
          </div>

          <div>
            <span>Phone</span>
            <strong><?= htmlspecialchars($currentDelivery["customer_phone"]) ?></strong>
          </div>
        </div>
      </div>

    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Order Information</h2>
          <p>Order #CR-<?= (int) $currentDelivery["order_id"] ?></p>
        </div>

        <strong class="order-price">
          ৳<?= number_format($currentDelivery["total_amount"], 0) ?>
        </strong>
      </div>

      <div class="order-items">

        <?php if ($orderItems && mysqli_num_rows($orderItems) > 0): ?>

          <?php while ($item = mysqli_fetch_assoc($orderItems)): ?>

          <div>
            <span>
              <?= (int) $item["quantity"] ?> × <?= htmlspecialchars($item["food_name_at_purchase"]) ?>
            </span>

            <strong>
              ৳<?= number_format($item["price_at_purchase"] * $item["quantity"], 0) ?>
            </strong>
          </div>

          <?php if (!empty($item["customization"])): ?>

          <div>
            <span>Customization</span>
            <strong><?= htmlspecialchars($item["customization"]) ?></strong>
          </div>

          <?php endif; ?>

          <?php endwhile; ?>

        <?php else: ?>

          <div>
            <span>No Order Items found.</span>
          </div>

        <?php endif; ?>

      </div>

      <div class="details-list">

        <div>
          <span>Food Subtotal</span>
          <strong>
            ৳<?= number_format($currentDelivery["food_subtotal"], 0) ?>
          </strong>
        </div>

        <div>
          <span>Delivery Fee</span>
          <strong>
            ৳<?= number_format($currentDelivery["delivery_fee"], 0) ?>
          </strong>
        </div>

        <div>
          <span>Payment Method</span>
          <strong><?= htmlspecialchars($currentDelivery["payment_method"]) ?></strong>
        </div>

        <div>
          <span>Payment Status</span>
          <strong id="paymentStatus">
            <?= htmlspecialchars($currentDelivery["payment_status"]) ?>
          </strong>
        </div>

      </div>

      <div class="total-row">
        <span>Total Amount</span>
        <strong>
          ৳<?= number_format($currentDelivery["total_amount"], 0) ?>
        </strong>
      </div>
    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Update Delivery Status</h2>
          <p>Update the order as you complete each step.</p>
        </div>
      </div>

      <div class="status-actions">

        <?php if ($currentDelivery["order_status"] == "Ready"): ?>

        <button type="button" class="status-btn" id="startDeliveryBtn" data-action="start_delivery">
          Confirm Pickup & Start Delivery
        </button>

        <?php endif; ?>

        <?php if ($currentDelivery["order_status"] == "On The Way"): ?>

        <button type="button" class="status-btn" id="deliveredBtn" data-action="delivered">

          <?php if ($currentDelivery["payment_method"] == "Cash on Delivery"): ?>
            Confirm Cash Collected & Delivered
          <?php else: ?>
            Confirm Delivered
          <?php endif; ?>

        </button>

        <?php endif; ?>

      </div>
    </section>

    <?php else: ?>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>No Active Delivery</h2>
          <p>You currently have no order assigned for delivery.</p>
        </div>
      </div>

      <a href="orders.php" class="primary-link">
        View Assigned Deliveries
      </a>
    </section>

    <?php endif; ?>

  </main>

  <script src="../../assets/js/deliveryman.js?v=1"></script>
</body>

</html>
