<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="../../assets/images/logo.png" alt="CraveRush">
  </div>

  <nav class="sidebar-menu">
    <a href="dashboard.php" class="menu-item <?= ($currentPage == "dashboard.php") ? "active" : "" ?>">
      <img src="../../assets/images/home.png" alt="Dashboard" class="menu-icon">
      Dashboard
    </a>

    <a href="orders.php" class="menu-item <?= ($currentPage == "orders.php") ? "active" : "" ?>">
      <img src="../../assets/images/assigned.png" alt="Assigned Deliveries" class="menu-icon">
      Assigned Deliveries
    </a>

    <a href="current.php" class="menu-item <?= ($currentPage == "current.php") ? "active" : "" ?>">
      <img src="../../assets/images/currentDelivery.png" alt="Current Delivery" class="menu-icon">
      Current Delivery
    </a>

    <a href="history.php" class="menu-item <?= ($currentPage == "history.php") ? "active" : "" ?>">
      <img src="../../assets/images/history.png" alt="Delivery History" class="menu-icon">
      Delivery History
    </a>

    <a href="earnings.php" class="menu-item <?= ($currentPage == "earnings.php") ? "active" : "" ?>">
      <img src="../../assets/images/earnings.png" alt="Earnings" class="menu-icon">
      Earnings
    </a>

    <a href="profile.php" class="menu-item <?= ($currentPage == "profile.php") ? "active" : "" ?>">
      <img src="../../assets/images/profile.png" alt="Profile" class="menu-icon">
      Profile
    </a>
  </nav>

  <div class="sidebar-bottom">
    <a href="../../controllers/deliveryman_controller.php?action=logout" class="logout-btn">Logout</a>
  </div>
</aside>
