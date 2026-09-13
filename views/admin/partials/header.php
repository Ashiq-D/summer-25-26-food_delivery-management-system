    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle">☰</button>

      <div class="topbar-title">
        <span class="topbar-title-main"><?= htmlspecialchars($pageTitle) ?></span>
        <span class="topbar-title-sep">/</span>
        <span class="topbar-title-sub"><?= htmlspecialchars($pageSubtitle) ?></span>
      </div>

      <div class="topbar-actions">

        <div class="topbar-search">
          <input type="text" id="adminSearch" placeholder="Search" autocomplete="off">
          <span class="search-icon">🔍</span>
        </div>

        <div class="topbar-profile" id="profileToggle">
          <?php if (!empty($admin["profile_image"])) : ?>
            <div class="header-avatar"><img src="../../<?= htmlspecialchars($admin["profile_image"]) ?>" alt="<?= htmlspecialchars($admin["name"] ?? "Admin") ?>"></div>
          <?php else : ?>
            <div class="header-avatar"><?= htmlspecialchars(strtoupper(substr($admin["name"] ?? "A", 0, 1))) ?></div>
          <?php endif; ?>
          <span class="profile-name"><?= htmlspecialchars($admin["name"] ?? "Admin") ?></span>
          <span class="profile-caret">▾</span>

          <div class="profile-dropdown" id="profileDropdown">
            <a href="profile.php">My Profile</a>
            <a href="../../controllers/admin_controller.php?action=logout" class="logout-link">Logout</a>
          </div>
        </div>

      </div>
    </header>
