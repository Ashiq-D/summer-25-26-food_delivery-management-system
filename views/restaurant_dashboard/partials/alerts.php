<?php if (!empty($_SESSION["flash_success"])) : ?>
  <div class="flash-alert success">
    <?= htmlspecialchars($_SESSION["flash_success"]) ?>
  </div>
  <?php unset($_SESSION["flash_success"]); ?>
<?php endif; ?>

<?php if (!empty($_SESSION["flash_error"])) : ?>
  <div class="flash-alert error">
    <?= htmlspecialchars($_SESSION["flash_error"]) ?>
  </div>
  <?php unset($_SESSION["flash_error"]); ?>
<?php endif; ?>
