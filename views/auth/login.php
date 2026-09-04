<?php
require_once __DIR__ . "/../../controllers/login_controller.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Login | CraveRush</title>

  <link rel="stylesheet" href="../../assets/css/login.css?v=10">
</head>

<body class="login-page">

  <form class="card" method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" novalidate>

    <header class="form-header">

      <a href="../../index.php">
        <img src="../../assets/images/logo.png" alt="CraveRush" class="logo">
      </a>

      <h1>Login</h1>

      <p>Enter your email address and password to continue.</p>

    </header>


    <h2 class="section-title">Login Information</h2>


    <div class="field">

      <label for="email">Email Address</label>

      <input
        type="email"
        id="email"
        name="email"
        placeholder="example@email.com"
        value="<?= htmlspecialchars($email) ?>"
      >

      <?php if (!empty($emailErr)): ?>

        <span class="error">
          <?= htmlspecialchars($emailErr) ?>
        </span>

      <?php endif; ?>

    </div>


    <div class="field">

      <label for="password">Password</label>

      <input
        type="password"
        id="password"
        name="password"
        placeholder="Enter your password"
      >

      <?php if (!empty($passwordErr)): ?>

        <span class="error">
          <?= htmlspecialchars($passwordErr) ?>
        </span>

      <?php endif; ?>

    </div>


    <?php if (!empty($loginErr)): ?>

      <span class="error">
        <?= htmlspecialchars($loginErr) ?>
      </span>

    <?php endif; ?>


    <div class="buttons">

      <button type="submit" class="btn-primary">
        Login
      </button>

    </div>


    <p class="login-text">

      Don't have an account?

      <a href="../auth/register.php">
        Create Account
      </a>

    </p>

  </form>

</body>

</html>