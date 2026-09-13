    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle">☰</button>

      <div class="topbar-title">
        <span class="topbar-title-main"><?= htmlspecialchars($pageTitle) ?></span>
        <?php if (!empty($pageSubtitle)): ?>
          <span class="topbar-title-sep">/</span>
          <span class="topbar-title-sub"><?= htmlspecialchars($pageSubtitle) ?></span>
        <?php endif; ?>
      </div>

      <div class="topbar-actions">

        <?php if (!empty($headerExtra)): ?>
          <?= $headerExtra ?>
        <?php endif; ?>

        <div class="topbar-profile" id="profileToggle">
          <?php if (!empty($restaurant["profile_image"])) : ?>
            <div class="header-avatar"><img src="../../<?= htmlspecialchars($restaurant["profile_image"]) ?>" alt="<?= htmlspecialchars($restaurant["name"] ?? "Restaurant") ?>"></div>
          <?php else : ?>
            <div class="header-avatar"><?= htmlspecialchars(strtoupper(substr($restaurant["name"] ?? "R", 0, 1))) ?></div>
          <?php endif; ?>
          <span class="profile-name"><?= htmlspecialchars($restaurant["name"] ?? "Restaurant") ?></span>
          <span class="profile-caret">▾</span>

          <div class="profile-dropdown" id="profileDropdown">
            <a href="profile.php">My Profile</a>
            <a href="../../controllers/restaurant_controller.php?action=logout" class="logout-link">Logout</a>
          </div>
        </div>

      </div>
    </header>
