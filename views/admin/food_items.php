<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";
require_once __DIR__ . "/../../models/user_model.php";

$foodItems = getAllFoodItems();
$restaurants = getAllRestaurants();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Food Items | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Food Items";
      $pageSubtitle = "View, add, and remove Food Items across all Restaurants.";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <button type="button" class="btn-secondary toggle-form-btn" onclick="document.getElementById('addFoodItemForm').classList.toggle('hidden-form')">
      + Add New Food Item
    </button>

    <div class="form-card hidden-form" id="addFoodItemForm">
      <h3>Add New Food Item</h3>
      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <input type="hidden" name="action" value="add_food_item">
        <div class="form-grid">
          <div class="form-group">
            <label>Restaurant</label>
            <select name="restaurant_id" required>
              <?php foreach ($restaurants as $r) : ?>
                <option value="<?= (int) $r["restaurant_id"] ?>"><?= htmlspecialchars($r["name"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" required>
          </div>
          <div class="form-group">
            <label>Description</label>
            <input type="text" name="description">
          </div>
          <div class="form-group">
            <label>Price (৳)</label>
            <input type="number" step="0.01" min="0.01" name="price" required>
          </div>
          <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" required>
          </div>
          <div class="form-group">
            <label>Availability Status</label>
            <select name="availability_status" required>
              <option value="Available">Available</option>
              <option value="Unavailable">Unavailable</option>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Create Food Item</button>
        </div>
      </form>
    </div>

    <section class="content-card">
      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Restaurant</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($foodItems as $f) : ?>
              <tr>
                <td>#<?= (int) $f["food_id"] ?></td>
                <td><?= htmlspecialchars($f["name"]) ?></td>
                <td><?= htmlspecialchars($f["restaurant_name"]) ?></td>
                <td><?= htmlspecialchars($f["category"]) ?></td>
                <td>৳<?= number_format($f["price"], 2) ?></td>
                <td><span class="status-badge <?= $f["availability_status"] == "Available" ? "green" : "gray" ?>"><?= htmlspecialchars($f["availability_status"]) ?></span></td>
                <td>
                  <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this record? All associated data will also be deleted. This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete_food_item">
                    <input type="hidden" name="food_id" value="<?= (int) $f["food_id"] ?>">
                    <button type="submit" class="btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($foodItems)) : ?>
              <tr><td colspan="7">No food items yet.</td></tr>
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
