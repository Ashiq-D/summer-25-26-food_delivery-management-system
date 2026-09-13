<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";
require_once __DIR__ . "/../../models/user_model.php";

$tab = $_GET["tab"] ?? "customers";
if (!in_array($tab, ["customers", "restaurants", "deliverymen"]))
{
    $tab = "customers";
}

$customers   = getAllCustomers();
$restaurants = getAllRestaurants();
$deliverymen = getAllDeliverymen();
$areas       = getAreas();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Manage Users";
      $pageSubtitle = "View, add, and remove Customers, Restaurants, and Deliverymen.";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <div class="tabs">
      <a href="?tab=customers" class="tab-link <?= $tab == "customers" ? "active" : "" ?>">Customers</a>
      <a href="?tab=restaurants" class="tab-link <?= $tab == "restaurants" ? "active" : "" ?>">Restaurants</a>
      <a href="?tab=deliverymen" class="tab-link <?= $tab == "deliverymen" ? "active" : "" ?>">Deliverymen</a>
    </div>

    <?php if ($tab == "customers") : ?>

      <button type="button" class="btn-secondary toggle-form-btn" onclick="document.getElementById('addCustomerForm').classList.toggle('hidden-form')">
        + Add New Customer
      </button>

      <div class="form-card hidden-form" id="addCustomerForm">
        <h3>Add New Customer</h3>
        <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
          <input type="hidden" name="action" value="add_customer">
          <div class="form-grid">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" required>
            </div>
            <div class="form-group">
              <label>Area</label>
              <select name="area_id" required>
                <?php foreach ($areas as $area) : ?>
                  <option value="<?= (int) $area["area_id"] ?>"><?= htmlspecialchars($area["area_name"]) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Create Customer</button>
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
                <th>Phone</th>
                <th>Email</th>
                <th>Area</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($customers as $c) : ?>
                <tr>
                  <td>#<?= (int) $c["customer_id"] ?></td>
                  <td><?= htmlspecialchars($c["name"]) ?></td>
                  <td><?= htmlspecialchars($c["phone_number"]) ?></td>
                  <td><?= htmlspecialchars($c["email"]) ?></td>
                  <td><?= htmlspecialchars($c["area_name"]) ?></td>
                  <td>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this record? All associated data will also be deleted. This action cannot be undone.');">
                      <input type="hidden" name="action" value="delete_customer">
                      <input type="hidden" name="customer_id" value="<?= (int) $c["customer_id"] ?>">
                      <button type="submit" class="btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($customers)) : ?>
                <tr><td colspan="6">No customers yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

    <?php elseif ($tab == "restaurants") : ?>

      <button type="button" class="btn-secondary toggle-form-btn" onclick="document.getElementById('addRestaurantForm').classList.toggle('hidden-form')">
        + Add New Restaurant
      </button>

      <div class="form-card hidden-form" id="addRestaurantForm">
        <h3>Add New Restaurant</h3>
        <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
          <input type="hidden" name="action" value="add_restaurant">
          <div class="form-grid">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" required>
            </div>
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" required>
            </div>
            <div class="form-group">
              <label>Area</label>
              <select name="area_id" required>
                <?php foreach ($areas as $area) : ?>
                  <option value="<?= (int) $area["area_id"] ?>"><?= htmlspecialchars($area["area_name"]) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Create Restaurant</button>
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
                <th>Username</th>
                <th>Email</th>
                <th>Area</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($restaurants as $r) : ?>
                <tr>
                  <td>#<?= (int) $r["restaurant_id"] ?></td>
                  <td><?= htmlspecialchars($r["name"]) ?></td>
                  <td><?= htmlspecialchars($r["username"]) ?></td>
                  <td><?= htmlspecialchars($r["email"]) ?></td>
                  <td><?= htmlspecialchars($r["area_name"]) ?></td>
                  <td><span class="status-badge <?= $r["availability_status"] == "Open" ? "green" : "gray" ?>"><?= htmlspecialchars($r["availability_status"]) ?></span></td>
                  <td>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this record? All associated data will also be deleted. This action cannot be undone.');">
                      <input type="hidden" name="action" value="delete_restaurant">
                      <input type="hidden" name="restaurant_id" value="<?= (int) $r["restaurant_id"] ?>">
                      <button type="submit" class="btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($restaurants)) : ?>
                <tr><td colspan="7">No restaurants yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

    <?php else : ?>

      <button type="button" class="btn-secondary toggle-form-btn" onclick="document.getElementById('addDeliverymanForm').classList.toggle('hidden-form')">
        + Add New Deliveryman
      </button>

      <div class="form-card hidden-form" id="addDeliverymanForm">
        <h3>Add New Deliveryman</h3>
        <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
          <input type="hidden" name="action" value="add_deliveryman">
          <div class="form-grid">
            <div class="form-group">
              <label>Name</label>
              <input type="text" name="name" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" name="phone" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" required>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" required>
            </div>
            <div class="form-group">
              <label>Vehicle Type</label>
              <select name="vehicle_type" required>
                <option value="Bicycle">Bicycle</option>
                <option value="Bike">Bike</option>
                <option value="Car">Car</option>
              </select>
            </div>
            <div class="form-group">
              <label>Area</label>
              <select name="area_id" required>
                <?php foreach ($areas as $area) : ?>
                  <option value="<?= (int) $area["area_id"] ?>"><?= htmlspecialchars($area["area_name"]) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Create Deliveryman</button>
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
                <th>Phone</th>
                <th>Vehicle</th>
                <th>Area</th>
                <th>Online</th>
                <th>Availability</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($deliverymen as $d) : ?>
                <tr>
                  <td>#<?= (int) $d["deliveryman_id"] ?></td>
                  <td><?= htmlspecialchars($d["name"]) ?></td>
                  <td><?= htmlspecialchars($d["phone_number"]) ?></td>
                  <td><?= htmlspecialchars($d["vehicle_type"]) ?></td>
                  <td><?= htmlspecialchars($d["area_name"]) ?></td>
                  <td><span class="status-badge <?= $d["online_status"] == "Online" ? "green" : "gray" ?>"><?= htmlspecialchars($d["online_status"]) ?></span></td>
                  <td><span class="status-badge <?= $d["availability_status"] == "Available" ? "green" : "orange" ?>"><?= htmlspecialchars($d["availability_status"]) ?></span></td>
                  <td>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this record? All associated data will also be deleted. This action cannot be undone.');">
                      <input type="hidden" name="action" value="delete_deliveryman">
                      <input type="hidden" name="deliveryman_id" value="<?= (int) $d["deliveryman_id"] ?>">
                      <button type="submit" class="btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($deliverymen)) : ?>
                <tr><td colspan="8">No deliverymen yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

    <?php endif; ?>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/admin.js?v=1"></script>
</body>

</html>
