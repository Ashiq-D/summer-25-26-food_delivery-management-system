<?php
require_once "create_acc_process.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account | CraveRush</title>
  <link rel="stylesheet" href="create_acc.css?v=5">
</head>

<body>

  <form class="card" method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" novalidate>

    <header class="form-header">
      <a href="home.php"><img src="assets/images/logo.png" alt="CraveRush" class="logo"></a>

      <h1>Create Account</h1>

      <p>Select your account type and complete the information below.</p>
    </header>

    <div class="account-type-section">
      <label class="section-label">Account Type</label>

      <div class="account-type-buttons">

        <button type="button" class="role-btn <?= ($userType == "customer") ? "active" : "" ?>" data-role="customer">
          Customer
        </button>

        <button type="button" class="role-btn <?= ($userType == "restaurant") ? "active" : "" ?>" data-role="restaurant">
          Restaurant
        </button>

        <button type="button" class="role-btn <?= ($userType == "deliveryman") ? "active" : "" ?>" data-role="deliveryman">
          Delivery Man
        </button>

      </div>

      <input type="hidden" id="userType" name="user_type" value="<?= htmlspecialchars($userType) ?>">

      <?php if (!empty($userTypeErr)): ?>
        <span class="error"><?= htmlspecialchars($userTypeErr) ?></span>
      <?php endif; ?>
    </div>

    <h2 class="section-title">Basic Information</h2>

    <div class="field">
      <label for="name">Name</label>

      <input type="text" id="name" name="name" placeholder="Enter name" value="<?= htmlspecialchars($name) ?>">

      <?php if (!empty($nameErr)): ?>
        <span class="error"><?= htmlspecialchars($nameErr) ?></span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="email">Email Address</label>

      <input type="email" id="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($email) ?>">

      <?php if (!empty($emailErr)): ?>
        <span class="error"><?= htmlspecialchars($emailErr) ?></span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="phone">Phone Number</label>

      <input type="text" id="phone" name="phone" placeholder="01XXXXXXXXX" value="<?= htmlspecialchars($phone) ?>">

      <?php if (!empty($phoneErr)): ?>
        <span class="error"><?= htmlspecialchars($phoneErr) ?></span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="areaId">Area</label>

      <select id="areaId" name="area_id">

        <option value="" disabled <?= ($areaId == "") ? "selected" : "" ?>>
          Select area...
        </option>

        <?php while ($area = mysqli_fetch_assoc($areas)): ?>

        <option value="<?= (int) $area["area_id"] ?>" <?= ($areaId == $area["area_id"]) ? "selected" : "" ?>>
          <?= htmlspecialchars($area["area_name"]) ?>
        </option>

        <?php endwhile; ?>

      </select>

      <?php if (!empty($areaErr)): ?>
        <span class="error"><?= htmlspecialchars($areaErr) ?></span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="password">Password</label>

      <input type="password" id="password" name="password" placeholder="At least 8 characters">

      <?php if (!empty($passwordErr)): ?>
        <span class="error"><?= htmlspecialchars($passwordErr) ?></span>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="confirmPassword">Confirm Password</label>

      <input type="password" id="confirmPassword" name="confirm_password" placeholder="Enter password again">

      <?php if (!empty($confirmPasswordErr)): ?>
        <span class="error"><?= htmlspecialchars($confirmPasswordErr) ?></span>
      <?php endif; ?>
    </div>

    <div id="restaurantFields" class="role-fields">

      <h2 class="section-title">Restaurant Information</h2>

      <div class="field">
        <label for="restaurantUsername">Username</label>

        <input type="text" id="restaurantUsername" name="restaurant_username" placeholder="Enter restaurant username" value="<?= htmlspecialchars($restaurantUsername) ?>">

        <?php if (!empty($restaurantUsernameErr)): ?>
          <span class="error"><?= htmlspecialchars($restaurantUsernameErr) ?></span>
        <?php endif; ?>
      </div>

    </div>

    <div id="deliverymanFields" class="role-fields">

      <h2 class="section-title">Delivery Man Information</h2>

      <div class="field">
        <label for="vehicleType">Vehicle Type</label>

        <select id="vehicleType" name="vehicle_type">

          <option value="" disabled <?= ($vehicleType == "") ? "selected" : "" ?>>
            Select vehicle type...
          </option>

          <option value="Bicycle" <?= ($vehicleType == "Bicycle") ? "selected" : "" ?>>
            Bicycle
          </option>

          <option value="Motorcycle" <?= ($vehicleType == "Motorcycle") ? "selected" : "" ?>>
            Motorcycle
          </option>

          <option value="Scooter" <?= ($vehicleType == "Scooter") ? "selected" : "" ?>>
            Scooter
          </option>

        </select>

        <?php if (!empty($vehicleTypeErr)): ?>
          <span class="error"><?= htmlspecialchars($vehicleTypeErr) ?></span>
        <?php endif; ?>
      </div>

    </div>

    <?php if (!empty($dbErr)): ?>
      <span class="error"><?= htmlspecialchars($dbErr) ?></span>
    <?php endif; ?>

    <div class="buttons">
      <button type="submit" class="btn-primary">Create Account</button>

      <button type="reset" class="btn-secondary">Reset</button>
    </div>

    <p class="login-text">
      Already have an account?
      <a href="login.php">Login</a>
    </p>

  </form>

  <script src="create_acc.js?v=1"></script>

</body>

</html>