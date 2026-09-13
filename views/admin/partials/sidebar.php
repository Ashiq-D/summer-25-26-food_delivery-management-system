<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="../../assets/images/logo.png" alt="CraveRush">
  </div>

  <nav class="sidebar-menu">
    <a href="dashboard.php" class="menu-item <?= ($currentPage == "dashboard.php") ? "active" : "" ?>">
      <span class="menu-icon-text">▤</span>
      Dashboard
    </a>

    <a href="users.php" class="menu-item <?= ($currentPage == "users.php") ? "active" : "" ?>">
      <span class="menu-icon-text">👤</span>
      Manage Users
    </a>

    <a href="orders.php" class="menu-item <?= ($currentPage == "orders.php") ? "active" : "" ?>">
      <span class="menu-icon-text">📦</span>
      Orders &amp; Deliveries
    </a>

    <a href="requests.php" class="menu-item <?= ($currentPage == "requests.php") ? "active" : "" ?>">
      <span class="menu-icon-text">✉</span>
      Area Change Requests
    </a>

    <a href="reports.php" class="menu-item <?= ($currentPage == "reports.php") ? "active" : "" ?>">
      <span class="menu-icon-text">📊</span>
      Reports
    </a>

    <a href="areas.php" class="menu-item <?= ($currentPage == "areas.php") ? "active" : "" ?>">
      <span class="menu-icon-text">📍</span>
      Areas
    </a>

    <a href="food_items.php" class="menu-item <?= ($currentPage == "food_items.php") ? "active" : "" ?>">
      <span class="menu-icon-text">🍔</span>
      Food Items
    </a>
  </nav>

  <div class="sidebar-bottom">
    <a href="../../controllers/admin_controller.php?action=logout" class="logout-btn">Logout</a>
  </div>
</aside>
