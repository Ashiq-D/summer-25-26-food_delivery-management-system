<?php
require_once "../../controllers/restaurant_controller.php";
$restaurantId = $_SESSION['restaurant_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Dashboard | CraveRush</title>
    <!-- Use correct relative path based on the file being in views/ -->
    <link rel="stylesheet" href="../../assets/css/restaurant.css">
</head>

<body>

    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <img src="../../assets/images/logo.png" alt="CraveRush">
        </div>

        <nav class="sidebar-menu">

            <div class="menu-item active" id="nav-overview" onclick="showTab('overview')">
                🏠 Overview
            </div>

            <div class="menu-item" id="nav-menu" onclick="showTab('menu')">
                🍕 Menu Management
            </div>

            <div class="menu-item" id="nav-orders" onclick="showTab('orders')">
                📋 Orders
            </div>

        </nav>

        <div class="sidebar-bottom">
            <a href="../../controllers/restaurant_controller.php?action=logout" class="logout-btn">Logout</a>
        </div>

    </aside>


    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Restaurant Dashboard</h1>
                <p>Welcome back, <strong><?= htmlspecialchars($restaurant["name"]) ?></strong></p>
            </div>

            <span class="status-badge <?= ($restaurant["availability_status"] == "Open") ? "" : "closed" ?>">
                <?= htmlspecialchars($restaurant["availability_status"]) ?>
            </span>
        </header>


        <!-- ── Toast Notification ─────────────────────────────────────── -->
        <div id="toast" class="toast"></div>


        <!-- ── Overview Tab ──────────────────────────────────────────── -->
        <div id="tab-overview" class="tab-section active">

            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h2>Overview</h2>
                        <p>Manage your restaurant menu and availability.</p>
                    </div>

                    <button class="btn-primary" onclick="showTab('menu')">
                        Manage Menu
                    </button>
                </div>

                <div class="restaurant-info">
                    <p>Restaurant: <strong><?= htmlspecialchars($restaurant["name"]) ?></strong></p>
                    <p>Username: <strong><?= htmlspecialchars($restaurant["username"]) ?></strong></p>
                    <p>Email: <strong><?= htmlspecialchars($restaurant["email"]) ?></strong></p>
                    <p>Status: <strong><?= htmlspecialchars($restaurant["availability_status"]) ?></strong></p>
                </div>

            </div>

        </div>


        <!-- ── Menu Management Tab ───────────────────────────────────── -->
        <div id="tab-menu" class="tab-section">

            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h2>Menu Management</h2>
                        <p>Add items and toggle their availability.</p>
                    </div>

                    <button class="btn-primary" onclick="openModal()">+ Add Item</button>
                </div>

                <div class="menu-controls">
                    <input
                        type="text"
                        id="menuSearch"
                        class="search-input"
                        placeholder="Search menu items..."
                        oninput="filterMenu()"
                    >
                </div>

                <div class="menu-grid" id="menuGrid">
                    <p class="empty-state">Loading menu items...</p>
                </div>

            </div>

        </div>

        <!-- ── Orders Tab ───────────────────────────────────────────────── -->
        <div id="tab-orders" class="tab-section">

            <div class="content-card">

                <div class="card-heading">
                    <div>
                        <h2>Orders</h2>
                        <p>Manage incoming orders and update statuses.</p>
                    </div>

                    <button class="btn-primary" onclick="loadOrders()">↻ Refresh</button>
                </div>

                <div class="orders-grid" id="ordersGrid">
                    <p class="empty-state">Loading orders...</p>
                </div>

            </div>

        </div>

    </main>


    <!-- ── Add Item Modal ─────────────────────────────────────────────── -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">

            <h2>Add New Menu Item</h2>

            <div class="field">
                <label for="itemName">Item Name <span class="required">*</span></label>
                <input type="text" id="itemName" placeholder="e.g. Chicken Biryani">
            </div>

            <div class="field">
                <label for="itemCategory">Category <span class="required">*</span></label>
                <select id="itemCategory">
                    <option value="">Select a category</option>
                    <option value="Rice">Rice</option>
                    <option value="Curry">Curry</option>
                    <option value="Grill">Grill</option>
                    <option value="Fast Food">Fast Food</option>
                    <option value="Pizza">Pizza</option>
                    <option value="Pasta">Pasta</option>
                    <option value="Salad">Salad</option>
                    <option value="Beverage">Beverage</option>
                    <option value="Dessert">Dessert</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="field">
                <label for="itemPrice">Price (৳) <span class="required">*</span></label>
                <input type="number" id="itemPrice" placeholder="e.g. 250" min="1" step="0.01">
            </div>

            <div class="field">
                <label for="itemDescription">Description</label>
                <textarea id="itemDescription" placeholder="Short description (optional)"></textarea>
            </div>

            <div class="modal-buttons">
                <button class="btn-primary" onclick="submitAddItem()">Save Item</button>
                <button class="btn-secondary" onclick="closeModal()">Cancel</button>
            </div>

        </div>
    </div>

    <!-- External JS for Restaurant Dashboard -->
    <script src="../../assets/js/restaurant.js"></script>

</body>

</html>
