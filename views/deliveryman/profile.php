<?php
require_once __DIR__ . "/../../controllers/deliveryman_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delivery Profile | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/deliveryman.css?v=6">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "My Profile";
      $pageSubtitle = "Manage your personal and vehicle information.";
      $badgeClass = ($deliveryman["availability_status"] == "Available") ? "green" : "orange";
      $headerExtra = '<span class="status-badge ' . $badgeClass . '">' . htmlspecialchars($deliveryman["availability_status"]) . '</span>';
      require __DIR__ . "/partials/header.php";
    ?>

    <section class="profile-header-card">
      <?php if (!empty($deliveryman["profile_image"])) : ?>
        <div class="profile-avatar">
          <img src="../../<?= htmlspecialchars($deliveryman["profile_image"]) ?>" alt="<?= htmlspecialchars($deliveryman["name"]) ?>">
        </div>
      <?php else : ?>
        <div class="profile-avatar">
          <?= htmlspecialchars(strtoupper(substr($deliveryman["name"], 0, 1))) ?>
        </div>
      <?php endif; ?>

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

    <form class="profile-form" id="profileForm" method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">

      <input type="hidden" name="action" value="update_profile">

      <section class="content-card">
        <div class="card-heading">
          <div>
            <h2>Personal Information</h2>
            <p>Your basic account information</p>
          </div>

          <button type="button" class="small-btn" id="editProfileBtn">Edit</button>
        </div>

        <div class="field profile-avatar-upload" style="margin-bottom: 18px;">
          <label for="profilePicture">Change Photo</label>
          <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
          <span class="file-chosen">JPG, PNG or WEBP, up to 2MB</span>
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
                <option value="Bike" <?= ($deliveryman["vehicle_type"] == "Bike") ? "selected" : "" ?>>
                Bike
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

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Delete My Account</h2>
          <p>Permanently delete your Deliveryman account. All associated data will also be deleted. This action cannot be undone. This is not possible while you have an active delivery.</p>
        </div>
      </div>

      <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete your account? This action cannot be undone.');">
        <input type="hidden" name="action" value="delete_account">
        <button type="submit" class="btn-danger">Delete My Account</button>
      </form>
    </section>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/deliveryman.js?v=3"></script>
</body>

</html>
