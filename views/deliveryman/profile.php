<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delivery Profile | CraveRush</title>
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

      <a href="history.php" class="menu-item">
        <img src="../../assets/images/history.png" alt="Delivery History" class="menu-icon">
        Delivery History
      </a>

      <a href="earnings.php" class="menu-item">
        <img src="../../assets/images/earnings.png" alt="Earnings" class="menu-icon">
        Earnings
      </a>

      <a href="profile.php" class="menu-item active">
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
        <h1>My Profile</h1>
        <p>Manage your personal and vehicle information.</p>
      </div>

      <span class="status-badge <?= ($deliveryman["availability_status"] == "Available") ? "green" : "orange" ?>">
        <?= htmlspecialchars($deliveryman["availability_status"]) ?>
      </span>
    </header>

    <section class="profile-header-card">
      <div class="profile-avatar">
        <?= htmlspecialchars(strtoupper(substr($deliveryman["name"], 0, 1))) ?>
      </div>

      <div>
        <h2><?= htmlspecialchars($deliveryman["name"]) ?></h2>
        <p>Deliveryman</p>
        <span>Deliveryman ID: DM-<?= (int) $deliveryman["deliveryman_id"] ?></span>
      </div>
    </section>

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

    <form class="profile-form" id="profileForm" method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">

      <input type="hidden" name="action" value="update_profile">

      <section class="content-card">
        <div class="card-heading">
          <div>
            <h2>Personal Information</h2>
            <p>Your basic account information</p>
          </div>

          <button type="button" class="small-btn" id="editProfileBtn">Edit</button>
        </div>

        <div class="form-grid">

          <div class="field">
            <label for="profileId">Deliveryman ID</label>
            <input type="text" id="profileId" value="DM-<?= (int) $deliveryman["deliveryman_id"] ?>" readonly>
          </div>

          <div class="field">
            <label for="profileName">Full Name</label>
            <input type="text" id="profileName" name="name" value="<?= htmlspecialchars($deliveryman["name"]) ?>" readonly>
            <span class="error" id="nameError"></span>
          </div>

          <div class="field">
            <label for="profileEmail">Email Address</label>
            <input type="email" id="profileEmail" name="email" value="<?= htmlspecialchars($deliveryman["email"]) ?>" readonly>
            <span class="error" id="emailError"></span>
          </div>

          <div class="field">
            <label for="profilePhone">Phone Number</label>
            <input type="text" id="profilePhone" name="phone_number" value="<?= htmlspecialchars($deliveryman["phone_number"]) ?>" readonly>
            <span class="error" id="phoneError"></span>
          </div>

        </div>
      </section>

      <section class="content-card">
        <div class="card-heading">
          <div>
            <h2>Vehicle Information</h2>
            <p>Your registered delivery vehicle</p>
          </div>
        </div>

        <div class="form-grid">

          <div class="field">
            <label for="vehicleType">Vehicle Type</label>

            <select id="vehicleType" name="vehicle_type" disabled>
                <option value="Bicycle" <?= ($deliveryman["vehicle_type"] == "Bicycle") ? "selected" : "" ?>>
                Bicycle
                </option>
                <option value="Motorcycle" <?= ($deliveryman["vehicle_type"] == "Motorcycle") ? "selected" : "" ?>>
                Motorcycle
                </option>
                <option value="Car" <?= ($deliveryman["vehicle_type"] == "Car") ? "selected" : "" ?>>
                Car
                </option>
            </select>
            <span class="error" id="vehicleError"></span>
           </div>

          <div class="field">
            <label for="deliveryArea">Area</label>

            <select id="deliveryArea" name="area_id" disabled>

              <?php while ($area = mysqli_fetch_assoc($areas)): ?>

              <option value="<?= (int) $area["area_id"] ?>" <?= ($deliveryman["area_id"] == $area["area_id"]) ? "selected" : "" ?>>
                <?= htmlspecialchars($area["area_name"]) ?>
              </option>

              <?php endwhile; ?>

            </select>

            <span class="error" id="areaError"></span>
          </div>

        </div>
      </section>

      <section class="content-card">
        <div class="card-heading">
          <div>
            <h2>Account Status</h2>
            <p>Your current delivery account status</p>
          </div>
        </div>

        <div class="form-grid">

          <div class="field">
            <label for="profileOnlineStatus">Online Status</label>
            <input type="text" id="profileOnlineStatus" value="<?= htmlspecialchars($deliveryman["online_status"]) ?>" readonly>
          </div>

          <div class="field">
            <label for="profileAvailabilityStatus">Availability Status</label>
            <input type="text" id="profileAvailabilityStatus" value="<?= htmlspecialchars($deliveryman["availability_status"]) ?>" readonly>
          </div>

        </div>

        <button type="submit" class="btn-primary save-profile-btn" id="saveProfileBtn">
          Save Changes
        </button>
      </section>

    </form>

  </main>

  <script src="../../assets/js/deliveryman.js?v=2"></script>
</body>

</html>
