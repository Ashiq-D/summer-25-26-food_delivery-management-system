    <?php $onHomePage = !empty($onHomePage); ?>

    <header>

        <?php if ($onHomePage) : ?>
            <div class="logo" onclick="goHome()">
                <img src="../../assets/images/logo.png" alt="CraveRush Logo">
            </div>
        <?php else : ?>
            <a class="logo" href="home.php">
                <img src="../../assets/images/logo.png" alt="CraveRush Logo">
            </a>
        <?php endif; ?>

        <nav>

            <?php if ($onHomePage) : ?>
                <a href="#" onclick="goHome()">Home</a>
                <a href="#restaurants" onclick="showRestaurants()">Restaurants</a>
            <?php else : ?>
                <a href="home.php">Home</a>
                <a href="home.php#restaurants">Restaurants</a>
            <?php endif; ?>

            <?php if ($onHomePage) : ?>
                <a href="#" onclick="showTracking()">Track Order</a>
                <a href="#" onclick="openCart()">Cart</a>
                <a href="#" onclick="showReview()">Rating</a>
            <?php else : ?>
                <a href="home.php">Track Order</a>
                <a href="home.php">Cart</a>
                <a href="home.php">Rating</a>
            <?php endif; ?>

            <div class="cart-icon" <?= $onHomePage ? 'onclick="openCart()"' : "" ?>>
                🛒
                <span class="cart-count" id="cartCount">0</span>
            </div>

            <div class="topbar-profile" id="profileToggle">
                <?php if (!empty($customer["profile_image"])) : ?>
                    <div class="header-avatar"><img src="../../<?= htmlspecialchars($customer["profile_image"]) ?>" alt="<?= htmlspecialchars($customer["name"] ?? "Customer") ?>"></div>
                <?php else : ?>
                    <div class="header-avatar"><?= htmlspecialchars(strtoupper(substr($customer["name"] ?? "C", 0, 1))) ?></div>
                <?php endif; ?>
                <span class="profile-caret">▾</span>

                <div class="profile-dropdown" id="profileDropdown">
                    <a href="profile.php">My Profile</a>
                    <a href="../../controllers/customer_profile_controller.php?action=logout" class="logout-link">Logout</a>
                </div>
            </div>

        </nav>

    </header>
