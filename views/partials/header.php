    <header class="topbar">
      <button type="button" class="menu-toggle" id="menuToggle">☰</button>

      <div>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <?= $pageSubtitle ?>
      </div>

      <?php if (!empty($headerExtra)): ?>
        <?= $headerExtra ?>
      <?php endif; ?>
    </header>
