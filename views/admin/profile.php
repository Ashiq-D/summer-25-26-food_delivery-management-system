<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";

$adminCount = countAdmins();
$isLastAdmin = ($adminCount <= 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "My Profile";
      $pageSubtitle = "View and update your own Admin account.";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <section class="profile-header-card">
      <?php if (!empty($admin["profile_image"])) : ?>
        <div class="profile-avatar">
          <img src="../../<?= htmlspecialchars($admin["profile_image"]) ?>" alt="<?= htmlspecialchars($admin["name"]) ?>">
        </div>
      <?php else : ?>
        <div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($admin["name"], 0, 1))) ?></div>
      <?php endif; ?>

      <div>
        <h2><?= htmlspecialchars($admin["name"]) ?></h2>
        <p>Admin</p>
        <span>Admin ID: #<?= (int) $admin["admin_id"] ?></span>
      </div>
    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Account Information</h2>
          <p>Admin ID: #<?= (int) $admin["admin_id"] ?></p>
        </div>
      </div>

      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_admin_profile">

        <div class="field profile-avatar-upload" style="margin-bottom: 18px;">
          <label for="profilePicture">Change Photo</label>
          <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg,image/png,image/webp">
          <span class="file-chosen">JPG, PNG or WEBP, up to 2MB</span>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($admin["name"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin["email"]) ?>" required>
          </div>
          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin["username"]) ?>" required>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Save Changes</button>
        </div>
      </form>
    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Delete My Account</h2>
          <p>
            <?php if ($isLastAdmin) : ?>
              You are the only remaining Admin. CRAVERUSH must always retain at least one Admin account, so this action is disabled.
            <?php else : ?>
              Permanently delete your own Admin account. You cannot delete any other Admin's account. All associated data will also be deleted. This action cannot be undone.
            <?php endif; ?>
          </p>
        </div>
      </div>

      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete your own Admin account? This action cannot be undone.');">
        <input type="hidden" name="action" value="delete_own_admin">
        <input type="hidden" name="admin_id" value="<?= (int) $admin["admin_id"] ?>">
        <button type="submit" class="btn-danger" <?= $isLastAdmin ? "disabled" : "" ?>>Delete My Account</button>
      </form>
    </section>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/admin.js?v=1"></script>
</body>

</html>
