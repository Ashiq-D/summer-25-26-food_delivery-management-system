<?php
    // On the SPA dashboard page, the first three items switch tabs in place
    // via JS. On any other page (currently just profile.php) they are plain
    // links back to the dashboard, and Profile becomes the active item.
    $onDashboard = ($currentPage == "restaurant_dashboard.php");
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <img src="../../assets/images/logo.png" alt="CraveRush">
  </div>

  <nav class="sidebar-menu">

    <?php if ($onDashboard) : ?>

      <div class="menu-item active" id="nav-overview" onclick="showTab('overview')">
        🏠 Overview
      </div>

      <div class="menu-item" id="nav-menu" onclick="showTab('menu')">
        🍕 Menu Management
      </div>

      <div class="menu-item" id="nav-orders" onclick="showTab('orders')">
        📋 Orders
      </div>

    <?php else : ?>

      <a href="restaurant_dashboard.php" class="menu-item">
        🏠 Overview
      </a>

      <a href="restaurant_dashboard.php" class="menu-item">
        🍕 Menu Management
      </a>

      <a href="restaurant_dashboard.php" class="menu-item">
        📋 Orders
      </a>

    <?php endif; ?>

    <a href="profile.php" class="menu-item <?= ($currentPage == "profile.php") ? "active" : "" ?>">
      👤 Profile
    </a>

  </nav>

  <div class="sidebar-bottom">
    <a href="../../controllers/restaurant_controller.php?action=logout" class="logout-btn">Logout</a>
  </div>
</aside>
