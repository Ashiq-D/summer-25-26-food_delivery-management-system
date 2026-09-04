<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CraveRush | Food Delivery</title>

    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>

<body>

    <header class="navbar">

        <div class="nav-container">

            <a href="index.php" class="nav-logo">
                <img src="assets/images/logo.png" alt="CraveRush">
            </a>

            <div class="nav-buttons">

                <a href="views/auth/login.php" class="login-btn">
                    Login
                </a>

                <a href="views/auth/register.php" class="create-btn">
                    Create Account
                </a>

            </div>

        </div>

    </header>


    <main>

        <section class="hero">

            <div class="hero-container">

                <div class="hero-content">

                    <div class="hero-label">

                        <img src="assets/images/home4.png" alt="Lightning Fast Delivery">

                        <span>Lightning Fast Delivery</span>

                    </div>


                    <h1>
                        Your Favorite Food,
                        <span>Delivered Fast.</span>
                    </h1>


                    <p class="hero-description">
                        Satisfy your cravings with speed. Discover top-rated local
                        restaurants and get hot, fresh meals delivered straight to
                        your door in minutes.
                    </p>


                    <div class="hero-benefits">

                        <div class="benefit">

                            <img src="assets/images/home1.png" alt="Fast Delivery">

                            <span>Fast Delivery</span>

                        </div>


                        <div class="benefit">

                            <img src="assets/images/home2.png" alt="Easy Ordering">

                            <span>Easy Ordering</span>

                        </div>


                        <div class="benefit">

                            <img src="assets/images/home3.png" alt="Trusted Partners">

                            <span>Trusted Partners</span>

                        </div>

                    </div>

                </div>


                <div class="hero-image">

                    <img src="assets/images/home_page.png" alt="CraveRush Food">

                </div>

            </div>

        </section>


        <section class="offers">

            <div class="offers-container">

                <div class="offers-heading">

                    <h2>What CraveRush Offers</h2>

                    <div class="heading-line"></div>

                </div>


                <div class="offers-grid">

                    <div class="offer-item">

                        <div class="offer-image">

                            <img src="assets/images/home1.png" alt="Fast Delivery">

                        </div>

                        <h3>Fast Delivery</h3>

                        <p>
                            Get your food delivered quickly from restaurants near you.
                        </p>

                    </div>


                    <div class="offer-item">

                        <div class="offer-image">

                            <img src="assets/images/home2.png" alt="Easy Ordering">

                        </div>

                        <h3>Easy Ordering</h3>

                        <p>
                            Choose your favorite meals and place your order in just a few steps.
                        </p>

                    </div>


                    <div class="offer-item">

                        <div class="offer-image">

                            <img src="assets/images/home3.png" alt="Reliable Service">

                        </div>

                        <h3>Reliable Service</h3>

                        <p>
                            Connect with restaurants and delivery partners through one simple platform.
                        </p>

                    </div>

                </div>

            </div>

        </section>

    </main>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>