<?php
require_once __DIR__ . "/../../controllers/admin_controller.php";

$tab = $_GET["tab"] ?? "area";
if (!in_array($tab, ["area", "cancellation"]))
{
    $tab = "area";
}

$areaRequests = getAreaChangeRequests("Pending");
$cancellationRequests = getCancellationRequests("Pending");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Requests | CraveRush Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=1">
</head>

<body>

  <?php require __DIR__ . "/partials/sidebar.php"; ?>

  <main class="main-content">

    <?php
      $pageTitle = "Requests";
      $pageSubtitle = "Review pending Area Change and Cancellation requests.";
      require __DIR__ . "/partials/header.php";
    ?>

    <?php require __DIR__ . "/partials/alerts.php"; ?>

    <div class="tabs">
      <a href="?tab=area" class="tab-link <?= $tab == "area" ? "active" : "" ?>">Area Change Requests</a>
      <a href="?tab=cancellation" class="tab-link <?= $tab == "cancellation" ? "active" : "" ?>">Cancellation Requests</a>
    </div>

    <?php if ($tab == "area") : ?>

      <section class="content-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Request ID</th>
                <th>Restaurant</th>
                <th>Current Area</th>
                <th>Requested Area</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($areaRequests as $req) : ?>
                <tr>
                  <td>#<?= (int) $req["request_id"] ?></td>
                  <td><?= htmlspecialchars($req["restaurant_name"]) ?></td>
                  <td><?= htmlspecialchars($req["current_area_name"]) ?></td>
                  <td><?= htmlspecialchars($req["requested_area_name"]) ?></td>
                  <td class="action-cell">
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                      <input type="hidden" name="action" value="approve_area_request">
                      <input type="hidden" name="request_id" value="<?= (int) $req["request_id"] ?>">
                      <button type="submit" class="btn-approve">Approve</button>
                    </form>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                      <input type="hidden" name="action" value="reject_area_request">
                      <input type="hidden" name="request_id" value="<?= (int) $req["request_id"] ?>">
                      <button type="submit" class="btn-danger">Reject</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($areaRequests)) : ?>
                <tr><td colspan="5">No pending area change requests.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

    <?php else : ?>

      <section class="content-card">
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Cancellation ID</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cancellationRequests as $req) : ?>
                <tr>
                  <td>#<?= (int) $req["cancellation_id"] ?></td>
                  <td>#<?= (int) $req["order_id"] ?></td>
                  <td><?= htmlspecialchars($req["customer_name"]) ?></td>
                  <td><?= htmlspecialchars($req["reason"]) ?></td>
                  <td class="action-cell">
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                      <input type="hidden" name="action" value="approve_cancellation">
                      <input type="hidden" name="cancellation_id" value="<?= (int) $req["cancellation_id"] ?>">
                      <button type="submit" class="btn-approve">Approve</button>
                    </form>
                    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
                      <input type="hidden" name="action" value="reject_cancellation">
                      <input type="hidden" name="cancellation_id" value="<?= (int) $req["cancellation_id"] ?>">
                      <button type="submit" class="btn-danger">Reject</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($cancellationRequests)) : ?>
                <tr><td colspan="5">No pending cancellation requests.</td></tr>
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
