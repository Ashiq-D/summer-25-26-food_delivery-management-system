<?php
require_once __DIR__ . "/../../controllers/restaurant_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | CraveRush Restaurant</title>
  <link rel="stylesheet" href="../../assets/css/restaurant.css?v=2">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "My Profile";
      $pageSubtitle = "View and update your restaurant account.";
      $badgeClass = ($restaurant["availability_status"] == "Open") ? "" : "closed";
      $headerExtra = '<span class="status-badge ' . $badgeClass . '">' . htmlspecialchars($restaurant["availability_status"]) . '</span>';
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <section class="profile-header-card">
      <?php if (!empty($restaurant["profile_image"])) : ?>
        <div class="profile-avatar">
          <img src="../../<?= htmlspecialchars($restaurant["profile_image"]) ?>" alt="<?= htmlspecialchars($restaurant["name"]) ?>">
        </div>
      <?php else : ?>
        <div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($restaurant["name"], 0, 1))) ?></div>
      <?php endif; ?>

      <div>
        <h2><?= htmlspecialchars($restaurant["name"]) ?></h2>
        <p>Restaurant</p>
        <span>Restaurant ID: #<?= (int) $restaurant["restaurant_id"] ?></span>
      </div>
    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Account Information</h2>
          <p>Restaurant ID: #<?= (int) $restaurant["restaurant_id"] ?></p>
        </div>
      </div>

      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_restaurant_profile">

        <div class="field profile-avatar-upload" style="margin-bottom: 18px;">
          <label for="profilePicture">Change Photo</label>
          <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
          <span class="file-chosen">JPG, PNG or WEBP, up to 2MB</span>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($restaurant["name"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($restaurant["email"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($restaurant["username"]) ?>" required>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </section>

  </main>

  <script src="../../assets/js/restaurant.js?v=2"></script>
</body>

</html>
