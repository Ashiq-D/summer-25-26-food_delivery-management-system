<?php

/*
 * views/home.php
 *
 * Customer home page for the CraveRush Customer module.
 *
 * This file:
 *   - loads restaurants from the database via RestaurantController
 *   - outputs the HTML structure
 *   - links to assets/css/customer.css (no inline styles)
 *   - links to assets/js/customer.js  (no inline scripts)
 *   - injects restaurant data as window.CUSTOMER_RESTAURANTS for JS
 *
 * Integrates with the existing CraveRush session (user_id).
 */

require_once __DIR__ . "/../../controllers/customer_profile_controller.php";
include_once __DIR__ . "/../../controllers/customer_controller.php";

$controller  = new CustomerController();
$restaurants = $controller->getRestaurants();

$onHomePage = true;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CraveRush - Food Ordering</title>
    <meta name="description" content="Order your favourite food from the best restaurants near you on CraveRush.">

    <!-- Customer stylesheet — no inline CSS -->
    <link rel="stylesheet" href="../../assets/css/customer.css">
</head>

<body>

    <!-- ========================================================
         HEADER
         ======================================================== -->

    <?php require __DIR__ . "/partials/header.php"; ?>

    <!-- ========================================================
         HERO / SEARCH
         ======================================================== -->

    <section class="hero" id="homeSection">

        <h1>
            Delicious food,
            <span>delivered fast.</span>
        </h1>

        <p>
            Discover the best restaurants and order your favourite food.
        </p>

        <div class="search-box">

            <input
                type="text"
                id="searchInput"
                placeholder="Search for restaurants or food..."
                oninput="searchFood()">

            <span class="search-icon">🔍</span>

            <div class="search-results" id="searchResults"></div>

        </div>

    </section>

    <!-- ========================================================
         AREA SELECTOR
         ======================================================== -->

    <div class="pdf-area-bar" id="areaBar">

        <div>
            <label for="customerArea">Your Area</label>
            <div class="pdf-area-note">
                Restaurants are shown according to your selected area.
            </div>
        </div>

        <select id="customerArea" onchange="changeArea()">
            <option value="Dhanmondi">Dhanmondi</option>
            <option value="Gulshan">Gulshan</option>
            <option value="Banani">Banani</option>
            <option value="Uttara">Uttara</option>
            <option value="Mirpur">Mirpur</option>
            <option value="Bashundhara">Bashundhara</option>
        </select>

    </div>

    <!-- ========================================================
         RESTAURANTS
         ======================================================== -->

    <section class="container" id="restaurantSection">

        <h2 class="section-title">Restaurants near you</h2>

        <div class="restaurant-grid" id="restaurantGrid"></div>

    </section>

    <!-- ========================================================
         MENU
         ======================================================== -->

    <section class="container" id="menuSection">

        <button class="back-btn" onclick="goBackToRestaurants()">
            ← Back to restaurants
        </button>

        <div class="menu-header">
            <h2 id="restaurantName"></h2>
            <p id="restaurantDescription"></p>
        </div>

        <div class="menu-grid" id="menuGrid"></div>

    </section>

    <!-- ========================================================
         VIEW YOUR ITEMS (floating bar)
         ======================================================== -->

    <div class="view-items" id="viewItems" onclick="openCart()">
        <span>View your items</span>
        <span id="viewItemsTotal">৳0</span>
    </div>

    <!-- ========================================================
         CART PANEL
         ======================================================== -->

    <div class="cart-panel" id="cartPanel">

        <div class="cart-header">
            <h2>Your items</h2>
            <button class="close-cart" onclick="closeCart()">×</button>
        </div>

        <div class="cart-items" id="cartItems"></div>

        <div class="cart-footer">

            <div class="total">
                <span>Total</span>
                <span id="cartTotal">৳0</span>
            </div>

            <button class="confirm-btn" onclick="confirmOrder()">
                Confirm Order
            </button>

        </div>

    </div>

    <!-- ========================================================
         PAYMENT
         ======================================================== -->

    <section class="payment-section" id="paymentSection">

        <div class="payment-box">

            <h2>Choose Payment Method</h2>

            <p>Select how you want to pay for your order.</p>

            <div class="payment-options">

                <div class="payment-option" onclick="selectPayment(this, 'Cash')">
                    <div class="payment-icon">💵</div>
                    <h3>Cash Payment</h3>
                    <p>Pay when your food arrives.</p>
                </div>

                <div class="payment-option" onclick="selectPayment(this, 'Card')">
                    <div class="payment-icon">💳</div>
                    <h3>Card Payment</h3>
                    <p>Pay using your card.</p>
                </div>

                <div class="payment-option" onclick="selectPayment(this, 'bKash')">
                    <div class="payment-icon">📱</div>
                    <h3>bKash</h3>
                    <p>Pay using bKash.</p>
                </div>

            </div>

            <button class="pay-now" onclick="completePayment()">
                Continue
            </button>

        </div>

    </section>

    <!-- ========================================================
         ORDER TRACKING
         ======================================================== -->

    <section class="tracking-section" id="trackingSection">

        <div class="tracking-box">

            <h2>Track Your Order</h2>

            <p class="tracking-order">
                Place an order to start tracking.
            </p>

            <div class="tracking-status">

                <div class="tracking-step active">
                    <div class="tracking-circle">✓</div>
                    <div>
                        <h3>Order Confirmed</h3>
                        <p>Your order has been confirmed.</p>
                    </div>
                </div>

                <div class="tracking-line"></div>

                <div class="tracking-step active">
                    <div class="tracking-circle">✓</div>
                    <div>
                        <h3>Preparing Food</h3>
                        <p>The restaurant is preparing your food.</p>
                    </div>
                </div>

                <div class="tracking-line"></div>

                <div class="tracking-step active">
                    <div class="tracking-circle">🛵</div>
                    <div>
                        <h3>Out for Delivery</h3>
                        <p>Your deliveryman is on the way.</p>
                    </div>
                </div>

                <div class="tracking-line"></div>

                <div class="tracking-step">
                    <div class="tracking-circle">4</div>
                    <div>
                        <h3>Delivered</h3>
                        <p>Your food will arrive soon.</p>
                    </div>
                </div>

            </div>

            <div class="delivery-info">
                <strong>Estimated delivery time</strong>
                <span>25–35 minutes</span>
            </div>

        </div>

    </section>

    <!-- ========================================================
         SUCCESS SCREEN
         ======================================================== -->

    <section class="success" id="successSection">

        <div class="success-icon">✅</div>

        <h2>Order Confirmed!</h2>

        <p>Your food has been successfully ordered.</p>

        <br>

        <p>Thank you for ordering from CraveRush.</p>

    </section>

    <!-- ========================================================
         REVIEW PANEL
         ======================================================== -->

    <div id="reviewPanel" class="tracking-section">

        <div class="tracking-box">

            <h2>Review Restaurant</h2>

            <p class="tracking-order" id="reviewRestaurant"></p>

            <div class="review-box">

                <label>
                    <strong>Rating</strong>
                </label>

                <select id="reviewRating">
                    <option value="5">★★★★★ - Excellent</option>
                    <option value="4">★★★★ - Very Good</option>
                    <option value="3">★★★ - Good</option>
                    <option value="2">★★ - Poor</option>
                    <option value="1">★ - Very Poor</option>
                </select>

                <textarea
                    id="reviewComment"
                    placeholder="Write your review...">
                </textarea>

                <button class="pay-now" onclick="submitReview()">
                    Submit Review
                </button>

            </div>

        </div>

    </div>

    <!-- ========================================================
         DATA BRIDGE: inject PHP restaurant data for customer.js
         ======================================================== -->

    <script>
        /*
         * window.CUSTOMER_RESTAURANTS is the server-rendered restaurant list.
         * customer.js reads this on load.
         * All values are JSON-encoded by PHP — no raw interpolation.
         */
        window.CUSTOMER_RESTAURANTS = <?php echo json_encode($restaurants, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>

    <!-- Customer JavaScript — no inline logic beyond data bridge above -->
    <script src="../../assets/js/customer.js"></script>

</body>

</html>