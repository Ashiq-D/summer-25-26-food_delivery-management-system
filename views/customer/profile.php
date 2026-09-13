<?php
require_once __DIR__ . "/../../controllers/customer_profile_controller.php";

$onHomePage = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | CraveRush</title>
  <link rel="stylesheet" href="../../assets/css/customer.css">
</head>

<body>

  <?php require __DIR__ . "/partials/header.php"; ?>

  <section class="container" style="padding-top: 40px; padding-bottom: 60px;">

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <div class="profile-header-card">
      <?php if (!empty($customer["profile_image"])) : ?>
        <div class="profile-avatar">
          <img src="../../<?= htmlspecialchars($customer["profile_image"]) ?>" alt="<?= htmlspecialchars($customer["name"]) ?>">
        </div>
      <?php else : ?>
        <div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($customer["name"], 0, 1))) ?></div>
      <?php endif; ?>

      <div>
        <h2><?= htmlspecialchars($customer["name"]) ?></h2>
        <p>Customer</p>
        <span>Customer ID: #<?= (int) $customer["customer_id"] ?></span>
      </div>
    </div>

    <div class="content-card">
      <div class="card-heading">
        <div>
          <h2>Account Information</h2>
          <p>Customer ID: #<?= (int) $customer["customer_id"] ?></p>
        </div>
      </div>

      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_customer_profile">

        <div class="field profile-avatar-upload" style="margin-bottom: 18px;">
          <label for="profilePicture">Change Photo</label>
          <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
          <span class="file-chosen">JPG, PNG or WEBP, up to 2MB</span>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($customer["name"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($customer["email"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone_number" value="<?= htmlspecialchars($customer["phone_number"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Area</label>
            <select name="area_id" required>
              <?php while ($area = mysqli_fetch_assoc($areas)) : ?>
                <option value="<?= (int) $area["area_id"] ?>" <?= ($customer["area_id"] == $area["area_id"]) ? "selected" : "" ?>>
                  <?= htmlspecialchars($area["area_name"]) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </div>

  </section>

  <script src="../../assets/js/customer-profile.js"></script>
</body>

</html>
