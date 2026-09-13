<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";
require_once __DIR__ . "/../../models/user_model.php";

$areas = getAreas();

$areasArray = [];
mysqli_data_seek($areas, 0);
while ($row = mysqli_fetch_assoc($areas))
{
    $areasArray[] = $row;
}

$adjacencies = getAreaAdjacencies();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Areas | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Areas";
      $pageSubtitle = "Manage delivery Areas and which Areas are adjacent to each other.";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <button type="button" class="btn-secondary toggle-form-btn" onclick="document.getElementById('addAreaForm').classList.toggle('hidden-form')">
      + Add New Area
    </button>

    <div class="form-card hidden-form" id="addAreaForm">
      <h3>Add New Area</h3>
      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <input type="hidden" name="action" value="add_area">
        <div class="form-grid">
          <div class="form-group">
            <label>Area Name</label>
            <input type="text" name="area_name" required>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Add Area</button>
        </div>
      </form>
    </div>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>All Areas</h2>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Area Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($areasArray as $area) : ?>
              <tr>
                <td>#<?= (int) $area["area_id"] ?></td>
                <td><?= htmlspecialchars($area["area_name"]) ?></td>
                <td>
                  <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this record? All associated data will also be deleted. This action cannot be undone. (Only possible if no one is currently assigned to this Area.)');">
                    <input type="hidden" name="action" value="delete_area">
                    <input type="hidden" name="area_id" value="<?= (int) $area["area_id"] ?>">
                    <button type="submit" class="btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="content-card">
      <div class="card-heading">
        <div>
          <h2>Area Adjacency</h2>
          <p>Used for a Deliveryman's service range: Bicycle = own Area only, Bike = own + adjacent, Car = own + adjacent + two-away.</p>
        </div>
      </div>

      <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <input type="hidden" name="action" value="add_adjacency">
        <div class="form-grid">
          <div class="form-group">
            <label>Area 1</label>
            <select name="area_id_1" required>
              <?php foreach ($areasArray as $area) : ?>
                <option value="<?= (int) $area["area_id"] ?>"><?= htmlspecialchars($area["area_name"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Area 2</label>
            <select name="area_id_2" required>
              <?php foreach ($areasArray as $area) : ?>
                <option value="<?= (int) $area["area_id"] ?>"><?= htmlspecialchars($area["area_name"]) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Link as Adjacent</button>
        </div>
      </form>

      <div class="adjacency-list">
        <?php foreach ($adjacencies as $adj) : ?>
          <div class="adjacency-chip">
            <?= htmlspecialchars($adj["area_name_1"]) ?> ↔ <?= htmlspecialchars($adj["area_name_2"]) ?>
            <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently remove this Area adjacency? This action cannot be undone.');">
              <input type="hidden" name="action" value="remove_adjacency">
              <input type="hidden" name="area_id_1" value="<?= (int) $adj["area_id_1"] ?>">
              <input type="hidden" name="area_id_2" value="<?= (int) $adj["area_id_2"] ?>">
              <button type="submit">✕</button>
            </form>
          </div>
        <?php endforeach; ?>
        <?php if (empty($adjacencies)) : ?>
          <p>No area adjacencies defined yet.</p>
        <?php endif; ?>
      </div>
    </section>

  </main>

  <?php $footerAssetPath = "../../"; require_once __DIR__ . "/../partials/footer.php"; ?>

  <script src="../../assets/js/admin.js?v=1"></script>
</body>

</html>
