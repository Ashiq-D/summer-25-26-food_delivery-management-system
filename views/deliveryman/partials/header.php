    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle">☰</button>

      <div class="topbar-title">
        <span class="topbar-title-main"><?= htmlspecialchars($pageTitle) ?></span>
        <?php if (!empty($pageSubtitle)): ?>
          <span class="topbar-title-sep">/</span>
          <span class="topbar-title-sub"><?= $pageSubtitle ?></span>
        <?php endif; ?>
      </div>

      <div class="topbar-actions">

        <?php if (!empty($headerExtra)): ?>
          <?= $headerExtra ?>
        <?php endif; ?>

        <div class="topbar-profile" id="profileToggle">
          <?php if (!empty($deliveryman["profile_image"])) : ?>
            <div class="header-avatar"><img src="../../<?= htmlspecialchars($deliveryman["profile_image"]) ?>" alt="<?= htmlspecialchars($deliveryman["name"] ?? "Deliveryman") ?>"></div>
          <?php else : ?>
            <div class="header-avatar"><?= htmlspecialchars(strtoupper(substr($deliveryman["name"] ?? "D", 0, 1))) ?></div>
          <?php endif; ?>
          <span class="profile-name"><?= htmlspecialchars($deliveryman["name"] ?? "Deliveryman") ?></span>
          <span class="profile-caret">▾</span>

          <div class="profile-dropdown" id="profileDropdown">
            <a href="profile.php">My Profile</a>
            <a href="../../controllers/deliveryman_controller.php?action=logout" class="logout-link">Logout</a>
          </div>
        </div>

      </div>
    </header>
