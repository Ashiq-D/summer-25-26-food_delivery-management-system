/*
 * customer.js
 *
 * All customer-facing JavaScript for the CraveRush Customer module.
 * Moved from inline <script> in views/home.php.
 *
 * AJAX paths (relative to views/home.php):
 *   ../../ajax/order.php
 *   ../../ajax/restaurants.php
 *   ../../ajax/search.php
 *   ../../ajax/tracking.php
 *   ../../ajax/review.php
 *
 * Data bridge: home.php injects `window.CUSTOMER_RESTAURANTS` via PHP.
 */

/* ============================================================
   SECURITY: HTML ESCAPE HELPER
   ============================================================ */

/**
 * Escape a string for safe insertion into HTML.
 * Prevents XSS when inserting database or user-supplied content.
 *
 * @param {string} str
 * @returns {string}
 */
function escapeHtml(str) {
    if (str === null || str === undefined) {
        return "";
    }
    return String(str)
        .replace(/&/g,  "&amp;")
        .replace(/</g,  "&lt;")
        .replace(/>/g,  "&gt;")
        .replace(/"/g,  "&quot;")
        .replace(/'/g,  "&#039;");
}

/* ============================================================
   APPLICATION STATE
   ============================================================ */

/*
 * restaurants: injected by home.php via window.CUSTOMER_RESTAURANTS.
 * This is the server-rendered list loaded on page start.
 */
let restaurants        = window.CUSTOMER_RESTAURANTS || [];

let cart               = [];
let selectedRestaurant = null;
let selectedPayment    = "";
let customerArea       = "";
let currentOrder       = null;
let reviewSubmitted    = false;

/**
 * Order status sequence used for tracking display.
 */
const statuses = [
    "Pending",
    "Preparing",
    "Ready",
    "On The Way",
    "Delivered"
];

/* ============================================================
   DISPLAY RESTAURANTS
   ============================================================ */

/**
 * Render the restaurant grid.
 *
 * If a list is provided (e.g. from search/area filter), use that.
 * Otherwise, use the current global restaurants array.
 *
 * @param {Array} [list]
 */
function displayRestaurants(list) {

    const displayList = Array.isArray(list) ? list : restaurants;

    const grid = document.getElementById("restaurantGrid");

    if (!grid) {
        return;
    }

    grid.innerHTML = "";

    if (displayList.length === 0) {

        grid.innerHTML = `
            <p class="cart-empty">
                No restaurants found in this area.
            </p>
        `;

        return;
    }

    displayList.forEach(restaurant => {

        const card = document.createElement("div");

        card.className = "restaurant-card";

        card.onclick = function () {
            openRestaurant(parseInt(restaurant.id));
        };

        card.innerHTML = `

            <img
                src="${escapeHtml(restaurant.image)}"
                class="restaurant-img"
                alt="${escapeHtml(restaurant.name)}"
                onerror="this.style.display='none'"
            >

            <div class="restaurant-info">

                <h3>
                    ${escapeHtml(restaurant.name)}
                </h3>

                <p>
                    ${escapeHtml(restaurant.area || "")}
                </p>

            </div>

        `;

        grid.appendChild(card);

    });

}

/* ============================================================
   OPEN RESTAURANT / DISPLAY MENU
   ============================================================ */

/**
 * Open a restaurant's menu by its ID.
 *
 * @param {number} id Restaurant_ID
 */
function openRestaurant(id) {

    selectedRestaurant = restaurants.find(r => parseInt(r.id) === id) || null;

    if (!selectedRestaurant) {
        return;
    }

    hideAllSections();

    document.getElementById("menuSection").style.display = "block";

    document.getElementById("restaurantName").textContent =
        selectedRestaurant.name;

    document.getElementById("restaurantDescription").textContent =
        selectedRestaurant.area || "";

    displayMenu();

    window.scrollTo({ top: 0, behavior: "smooth" });

}

/**
 * Render the food menu for the currently selected restaurant.
 */
function displayMenu() {

    const menuGrid = document.getElementById("menuGrid");

    if (!menuGrid) {
        return;
    }

    menuGrid.innerHTML = "";

    if (
        !selectedRestaurant ||
        !Array.isArray(selectedRestaurant.menu) ||
        selectedRestaurant.menu.length === 0
    ) {

        menuGrid.innerHTML = `
            <p class="cart-empty">
                No menu items available.
            </p>
        `;

        return;
    }

    selectedRestaurant.menu.forEach(food => {

        const card = document.createElement("div");

        card.className = "food-card";

        card.innerHTML = `

            <img
                src="${escapeHtml(food.image)}"
                class="food-img"
                alt="${escapeHtml(food.name)}"
                onerror="this.style.display='none'"
            >

            <div class="food-info">

                <h3>
                    ${escapeHtml(food.name)}
                </h3>

                <p class="food-description">
                    ${escapeHtml(food.description || "")}
                </p>

                <div class="price">
                    ৳${escapeHtml(String(food.price))}
                </div>

                <div class="customization-box">

                    <label>
                        Customization instructions (optional)
                    </label>

                    <textarea
                        id="customize-${parseInt(food.id)}"
                        placeholder="e.g. Less spicy, no onions...">
                    </textarea>

                </div>

                <button
                    class="add-btn"
                    onclick="addToCart(${parseInt(food.id)})">

                    + Add

                </button>

            </div>

        `;

        menuGrid.appendChild(card);

    });

}

/* ============================================================
   CART OPERATIONS
   ============================================================ */

/**
 * Add a food item to the cart (or increment quantity if already present).
 *
 * @param {number} foodId
 */
function addToCart(foodId) {

    if (!selectedRestaurant || !Array.isArray(selectedRestaurant.menu)) {
        return;
    }

    const food = selectedRestaurant.menu.find(
        item => parseInt(item.id) === foodId
    );

    if (!food) {
        return;
    }

    const noteEl = document.getElementById("customize-" + foodId);
    const customization = noteEl ? noteEl.value.trim() : "";

    const existing = cart.find(item => parseInt(item.id) === foodId);

    if (existing) {

        existing.quantity++;

        if (customization !== "") {
            existing.customization = customization;
        }

    } else {

        cart.push({
            id:            parseInt(food.id),
            name:          food.name,
            price:         food.price,   /* display only — server recalculates */
            quantity:      1,
            customization: customization
        });

    }

    if (noteEl) {
        noteEl.value = "";
    }

    updateCart();

}

/**
 * Update cart count badge, floating bar total, and re-render cart items.
 */
function updateCart() {

    let totalQuantity = 0;
    let totalPrice    = 0;

    cart.forEach(item => {
        totalQuantity += item.quantity;
        totalPrice    += Number(item.price) * item.quantity;
    });

    const count = document.getElementById("cartCount");

    if (count) {
        count.textContent  = totalQuantity;
        count.style.display = totalQuantity > 0 ? "flex" : "none";
    }

    const viewItems = document.getElementById("viewItems");

    if (viewItems) {

        if (totalQuantity > 0) {

            viewItems.style.display = "flex";

            const viewTotal = document.getElementById("viewItemsTotal");

            if (viewTotal) {
                viewTotal.textContent = "৳" + totalPrice;
            }

        } else {

            viewItems.style.display = "none";

        }

    }

    displayCart();

}

/**
 * Render all items currently in the cart panel.
 */
function displayCart() {

    const cartItems = document.getElementById("cartItems");

    if (!cartItems) {
        return;
    }

    cartItems.innerHTML = "";

    if (cart.length === 0) {

        cartItems.innerHTML = `
            <p class="cart-empty">
                Your cart is empty.
            </p>
        `;

        const cartTotal = document.getElementById("cartTotal");

        if (cartTotal) {
            cartTotal.textContent = "৳0";
        }

        return;

    }

    let total = 0;

    cart.forEach(item => {

        total += Number(item.price) * item.quantity;

        const div = document.createElement("div");

        div.className = "cart-item";

        div.innerHTML = `

            <div class="cart-item-info">

                <h4>
                    ${escapeHtml(item.name)}
                </h4>

                <p>
                    ৳${Number(item.price) * item.quantity}
                </p>

                ${
                    item.customization
                        ? `<div class="cart-item-customization">
                               Customization: ${escapeHtml(item.customization)}
                           </div>`
                        : ""
                }

            </div>

            <div class="quantity">

                <button onclick="changeQuantity(${parseInt(item.id)}, -1)">
                    −
                </button>

                <span>
                    ${item.quantity}
                </span>

                <button onclick="changeQuantity(${parseInt(item.id)}, 1)">
                    +
                </button>

            </div>

        `;

        cartItems.appendChild(div);

    });

    const cartTotal = document.getElementById("cartTotal");

    if (cartTotal) {
        cartTotal.textContent = "৳" + total;
    }

}

/**
 * Increment or decrement an item's quantity.
 * Removes the item if quantity reaches 0.
 *
 * @param {number} id      Food ID
 * @param {number} amount  +1 or -1
 */
function changeQuantity(id, amount) {

    const item = cart.find(i => parseInt(i.id) === id);

    if (!item) {
        return;
    }

    item.quantity += amount;

    if (item.quantity <= 0) {
        cart = cart.filter(i => parseInt(i.id) !== id);
    }

    updateCart();

}

/* ============================================================
   OPEN / CLOSE CART
   ============================================================ */

function openCart() {

    if (cart.length === 0) {
        alert("Please select some food first.");
        return;
    }

    const cartPanel = document.getElementById("cartPanel");

    if (cartPanel) {
        cartPanel.classList.add("open");
    }

}

function closeCart() {

    const cartPanel = document.getElementById("cartPanel");

    if (cartPanel) {
        cartPanel.classList.remove("open");
    }

}

/* ============================================================
   CONFIRM ORDER (cart → payment page)
   ============================================================ */

/**
 * Move from the cart to the payment selection screen.
 * Builds the currentOrder object (display only — server recalculates totals).
 */
function confirmOrder() {

    if (cart.length === 0) {
        alert("Your cart is empty.");
        return;
    }

    const foodSubtotal = cart.reduce(
        (sum, item) => sum + Number(item.price) * item.quantity,
        0
    );

    currentOrder = {
        id:             null,
        restaurantId:   selectedRestaurant ? parseInt(selectedRestaurant.id) : null,
        restaurantName: selectedRestaurant ? selectedRestaurant.name : "Restaurant",
        area:           customerArea,
        items:          JSON.parse(JSON.stringify(cart)),
        foodSubtotal:   foodSubtotal,
        deliveryFee:    50,
        total:          foodSubtotal + 50,
        paymentMethod:  "",
        paymentStatus:  "Pending",
        status:         "Pending",
        cancellationRequest: null,
        review:         null,
        orderDate:      new Date().toLocaleString()
    };

    closeCart();

    hideAllSections();

    document.getElementById("paymentSection").style.display = "block";

    renderCartSummary();

    window.scrollTo({ top: 0, behavior: "smooth" });

}

/* ============================================================
   CART SUMMARY (inside cart footer during payment)
   ============================================================ */

/**
 * Inject a cost summary breakdown into the cart footer.
 * Note: these amounts are display-only — the server recalculates.
 */
function renderCartSummary() {

    const footer = document.querySelector(".cart-footer");

    if (!footer || !currentOrder) {
        return;
    }

    let box = document.getElementById("cartSummary");

    if (!box) {

        box = document.createElement("div");
        box.id        = "cartSummary";
        box.className = "pdf-order-summary";

        footer.insertBefore(
            box,
            footer.querySelector(".total")
        );

    }

    box.innerHTML = `

        <div class="pdf-summary-row">
            <span>Food Subtotal</span>
            <span>৳${currentOrder.foodSubtotal}</span>
        </div>

        <div class="pdf-summary-row">
            <span>Delivery Fee</span>
            <span>৳${currentOrder.deliveryFee}</span>
        </div>

        <div class="pdf-summary-row total-row">
            <span>Total Amount</span>
            <span>৳${currentOrder.total}</span>
        </div>

    `;

}

/* ============================================================
   PAYMENT SELECTION
   ============================================================ */

/**
 * Mark a payment option as selected.
 *
 * @param {HTMLElement} element  The clicked .payment-option element
 * @param {string}      payment  "Cash", "Card", or "bKash"
 */
function selectPayment(element, payment) {

    document
        .querySelectorAll(".payment-option")
        .forEach(option => option.classList.remove("selected"));

    element.classList.add("selected");

    selectedPayment = payment;

    if (currentOrder) {

        if (payment === "Cash") {
            currentOrder.paymentMethod = "Cash on Delivery";
            currentOrder.paymentStatus = "Pending";
            showOnlinePayment(false);
        } else {
            currentOrder.paymentMethod = "Online Payment";
            showOnlinePayment(true);
        }

    }

}

/**
 * Show or hide the online payment information box.
 *
 * @param {boolean} show
 */
function showOnlinePayment(show) {

    const paymentBox = document.querySelector(".payment-box");

    if (!paymentBox) {
        return;
    }

    let box = document.getElementById("onlinePaymentBox");

    if (!box) {

        box = document.createElement("div");
        box.id        = "onlinePaymentBox";
        box.className = "online-payment-box";

        box.innerHTML = `
            <h3>Secure Online Payment</h3>
            <p class="online-payment-info">
                Your payment will be processed securely.
                No payment details are stored.
            </p>
        `;

        paymentBox.insertBefore(
            box,
            paymentBox.querySelector(".pay-now")
        );

    }

    box.style.display = show ? "block" : "none";

}

/* ============================================================
   COMPLETE PAYMENT → SUBMIT ORDER TO SERVER
   ============================================================ */

/**
 * Submit the order to the server via AJAX.
 *
 * The server validates food IDs, availability, restaurant ownership,
 * and recalculates the real total — frontend prices are display-only.
 */
function completePayment() {

    if (!currentOrder) {
        alert("Please confirm your order first.");
        return;
    }

    if (selectedPayment === "") {
        alert("Please select a payment method.");
        return;
    }

    if (!selectedRestaurant) {
        alert("Please select a restaurant.");
        return;
    }

    if (!customerArea || customerArea === "") {
        alert("Please select your area.");
        return;
    }

    if (selectedPayment !== "Cash") {
        currentOrder.paymentMethod = "Online Payment";
        currentOrder.paymentStatus = "Successful";
    } else {
        currentOrder.paymentMethod = "Cash on Delivery";
        currentOrder.paymentStatus = "Pending";
    }

    currentOrder.status = "Pending";

    /*
     * Send items to the server.
     * The server will retrieve real prices — frontend prices are display only.
     * totalAmount is intentionally NOT sent for server validation.
     */
    fetch("../../controllers/customer_ajax_controller.php?action=create_order", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({
            restaurantId:    currentOrder.restaurantId,
            paymentMethod:   currentOrder.paymentMethod,
            deliveryAddress: currentOrder.area,
            items:           currentOrder.items.map(item => ({
                id:            item.id,
                quantity:      item.quantity,
                customization: item.customization || ""
            }))
        })

    })

    .then(response => {

        if (!response.ok) {
            throw new Error("Network response was not ok.");
        }

        return response.json();

    })

    .then(data => {

        if (data.success) {

            currentOrder.id = "CR" + data.orderId;

            hideAllSections();

            document.getElementById("successSection").style.display = "block";

            cart = [];
            updateCart();

            selectedPayment = "";

            window.scrollTo({ top: 0, behavior: "smooth" });

            alert(
                "Order confirmed using " +
                currentOrder.paymentMethod +
                "!"
            );

        } else {

            alert(data.message || "Order could not be placed.");

        }

    })

    .catch(error => {

        console.error("Order error:", error);

        alert("Something went wrong while placing the order. Please try again.");

    });

}

/* ============================================================
   SEARCH
   ============================================================ */

/**
 * Called on search input change.
 * Fetches matching restaurants from the server.
 */
function searchFood() {

    const input   = document.getElementById("searchInput");
    const results = document.getElementById("searchResults");

    if (!input || !results) {
        return;
    }

    const query = input.value.trim();

    if (query === "") {
        results.style.display = "none";
        results.innerHTML     = "";
        return;
    }

    fetch("../../controllers/customer_ajax_controller.php?action=search&search=" + encodeURIComponent(query))

    .then(response => {

        if (!response.ok) {
            throw new Error("Search request failed.");
        }

        return response.json();

    })

    .then(data => {

        results.innerHTML = "";

        if (!Array.isArray(data) || data.length === 0) {

            results.innerHTML = `
                <div class="search-result">
                    <strong>No results found</strong>
                    <small>Try another restaurant or food name.</small>
                </div>
            `;

            results.style.display = "block";

            return;

        }

        data.forEach(restaurant => {

            const result = document.createElement("div");

            result.className = "search-result";

            result.innerHTML = `
                <strong>${escapeHtml(restaurant.name)}</strong>
                <small>${escapeHtml(restaurant.area || "")}</small>
            `;

            result.onclick = function () {

                results.style.display = "none";
                input.value           = "";

                /*
                 * The search result may be a restaurant not in the current
                 * filtered list. Load the restaurant's data and open it.
                 */
                const existing = restaurants.find(
                    r => parseInt(r.id) === parseInt(restaurant.id)
                );

                if (existing) {

                    openRestaurant(parseInt(restaurant.id));

                } else {

                    /*
                     * Fetch full restaurant data (with menu) from the
                     * restaurant list endpoint and add to local array.
                     */
                    fetch("../../controllers/customer_ajax_controller.php?action=get_restaurants")

                    .then(r => r.json())

                    .then(allRestaurants => {

                        if (Array.isArray(allRestaurants)) {

                            restaurants = allRestaurants;

                            displayRestaurants();

                            openRestaurant(parseInt(restaurant.id));

                        }

                    })

                    .catch(() => {});

                }

            };

            results.appendChild(result);

        });

        results.style.display = "block";

    })

    .catch(error => {

        console.error("Search error:", error);

        results.innerHTML = `
            <div class="search-result">
                <strong>Search error</strong>
                <small>Please try again.</small>
            </div>
        `;

        results.style.display = "block";

    });

}

/* ============================================================
   NAVIGATION HELPERS
   ============================================================ */

/**
 * Hide all major page sections.
 */
function hideAllSections() {

    const sections = [
        "homeSection",
        "restaurantSection",
        "menuSection",
        "paymentSection",
        "trackingSection",
        "successSection",
        "reviewPanel"
    ];

    sections.forEach(id => {

        const el = document.getElementById(id);

        if (el) {
            el.style.display = "none";
        }

    });

}

function goBackToRestaurants() {

    hideAllSections();

    document.getElementById("restaurantSection").style.display = "block";

    displayRestaurants();

    window.scrollTo({ top: 0, behavior: "smooth" });

}

function showRestaurants() {

    hideAllSections();

    document.getElementById("restaurantSection").style.display = "block";

    closeCart();

    displayRestaurants();

    window.scrollTo({ top: 0, behavior: "smooth" });

}

function goHome() {

    hideAllSections();

    document.getElementById("homeSection").style.display      = "block";
    document.getElementById("restaurantSection").style.display = "block";

    const searchInput   = document.getElementById("searchInput");
    const searchResults = document.getElementById("searchResults");

    if (searchInput)   { searchInput.value         = ""; }
    if (searchResults) { searchResults.style.display = "none"; }

    closeCart();

    displayRestaurants();

    window.scrollTo({ top: 0, behavior: "smooth" });

}

/* ============================================================
   ORDER TRACKING
   ============================================================ */

/**
 * Show the tracking section and load order status from the server.
 *
 * NOTE: advanceOrderStatus(), adminApproveCancellation(),
 * adminRejectCancellation() are DEMO-ONLY frontend functions.
 * They do NOT persist anything to the database.
 * Real order status changes come from the Admin / Restaurant module.
 */
function showTracking() {

    hideAllSections();

    document.getElementById("trackingSection").style.display = "block";

    closeCart();

    if (!currentOrder || !currentOrder.id) {

        window.scrollTo({ top: 0, behavior: "smooth" });

        return;

    }

    /* Extract numeric order ID from "CR12345" */
    const databaseOrderId = parseInt(
        String(currentOrder.id).replace("CR", "")
    );

    if (isNaN(databaseOrderId) || databaseOrderId <= 0) {

        window.scrollTo({ top: 0, behavior: "smooth" });

        return;

    }

    fetch("../../controllers/customer_ajax_controller.php?action=track_order&orderId=" + databaseOrderId)

    .then(response => response.json())

    .then(data => {

        if (!data.success) {
            alert(data.message || "Unable to load tracking.");
            return;
        }

        const order = data.order;

        /* Update local state from database */
        currentOrder.status        = order.Order_Status;
        currentOrder.paymentMethod = order.Payment_Method;
        currentOrder.paymentStatus = order.Payment_Status;
        currentOrder.total         = Number(order.Total_Amount);
        currentOrder.deliveryFee   = Number(order.Delivery_Fee);

        renderTrackingBox(order);

        window.scrollTo({ top: 0, behavior: "smooth" });

    })

    .catch(error => {

        console.error("Tracking error:", error);

        alert("Unable to load order tracking.");

    });

}

/**
 * Render the tracking progress UI inside #trackingSection.
 *
 * @param {Object} order  Order data from tracking.php
 */
function renderTrackingBox(order) {

    const tracking = document.querySelector("#trackingSection .tracking-box");

    if (!tracking) {
        return;
    }

    const currentStatus = order.Order_Status;
    
    // Map backend statuses to the 4 frontend steps
    const frontendSteps = [
        { key: "Order Confirmed", desc: "Your order has been confirmed." },
        { key: "Preparing Food", desc: "The restaurant is preparing your food." },
        { key: "Out for Delivery", desc: "Your deliveryman is on the way." },
        { key: "Delivered", desc: "Your order has been delivered successfully." }
    ];

    let currentIndex = 0;
    if (currentStatus === "Pending") currentIndex = 0;
    else if (currentStatus === "Preparing" || currentStatus === "Prepared" || currentStatus === "Ready") currentIndex = 1;
    else if (currentStatus === "On The Way") currentIndex = 2;
    else if (currentStatus === "Delivered") currentIndex = 3;

    const stepsHtml = frontendSteps.map((step, index) => `

        <div class="tracking-step ${index <= currentIndex ? "active" : ""}">

            <div class="tracking-circle">
                ${
                    index < currentIndex || (currentStatus === "Delivered" && index === currentIndex)
                        ? "✓"
                        : index === currentIndex
                            ? "●"
                            : index + 1
                }
            </div>

            <div>
                <h3>${escapeHtml(step.key)}</h3>
                <p>${escapeHtml(step.desc)}</p>
            </div>

        </div>

        ${
            index < frontendSteps.length - 1
                ? `<div class="tracking-line ${index < currentIndex || (currentStatus === "Delivered" && index === currentIndex) ? "" : "inactive"}"></div>`
                : ""
        }

    `).join("");

    const cancellationHtml = currentStatus === "Pending"
        ? `
            <div class="cancel-box">
                <strong>Need to cancel?</strong>
                <textarea
                    id="cancelReason"
                    placeholder="Enter cancellation reason...">
                </textarea>
                <button
                    class="pdf-action-btn danger"
                    onclick="requestCancellation()">
                    Submit Cancellation Request
                </button>
            </div>
        `
        : "";

    const reviewHtml = currentStatus === "Delivered"
        ? `
            <div class="review-box">
                <strong>Your order has been delivered.</strong>
                <p class="review-prompt-text">
                    You can now review the restaurant.
                </p>
                <button
                    class="pdf-action-btn review-btn-top"
                    onclick="showReview()">
                    Review Restaurant
                </button>
            </div>
        `
        : "";

    tracking.innerHTML = `

        <h2>Track Your Order</h2>

        <p class="tracking-order">
            Order #${escapeHtml(String(currentOrder.id))}
        </p>

        <span class="tracking-current">
            Current Status: ${escapeHtml(currentStatus)}
        </span>

        <div class="tracking-status">
            ${stepsHtml}
        </div>

        <div class="delivery-info">
            <strong>Delivery Fee</strong>
            <span>৳${currentOrder.deliveryFee}</span>
        </div>

        <div class="delivery-info">
            <strong>Total Amount</strong>
            <span>৳${currentOrder.total}</span>
        </div>

        ${cancellationHtml}

        ${reviewHtml}

    `;

}

/**
 * Human-readable description for each order status step.
 *
 * @param {string} status
 * @returns {string}
 */
function statusDescription(status) {

    const descriptions = {
        "Pending":    "Your order has been received and is waiting for the restaurant.",
        "Preparing":  "The restaurant is preparing your food.",
        "Ready":      "Your food is ready and waiting for delivery pickup.",
        "On The Way": "The deliveryman is on the way to you.",
        "Delivered":  "Your order has been delivered successfully."
    };

    return descriptions[status] || "";

}

/* ============================================================
   AREA SELECTION
   ============================================================ */

/**
 * Called when the customer changes their area in the dropdown.
 * Fetches matching restaurants from the server.
 */
function changeArea() {

    const areaSelect = document.getElementById("customerArea");

    if (!areaSelect) {
        return;
    }

    const selectedArea = areaSelect.value;

    if (
        currentOrder &&
        currentOrder.status !== "Delivered" &&
        currentOrder.status !== "Cancelled"
    ) {

        alert("You cannot change your area while you have an active order.");

        areaSelect.value = customerArea;

        return;

    }

    customerArea = selectedArea;

    /* Fetch restaurants filtered by the new area from the server */
    fetch("../../controllers/customer_ajax_controller.php?action=get_restaurants&area=" + encodeURIComponent(selectedArea))

    .then(response => response.json())

    .then(data => {

        if (Array.isArray(data)) {

            restaurants = data;

            displayRestaurants();

        }

    })

    .catch(error => {

        console.error("Area filter error:", error);

    });

}

/* ============================================================
   CANCELLATION (frontend + server-side request)
   ============================================================ */

/**
 * Submit a cancellation request for the current pending order.
 *
 * NOTE: The cancellationRequest object is stored locally for display.
 * Actual cancellation approval/rejection is managed by the Admin module.
 * adminApproveCancellation() and adminRejectCancellation() are DEMO-ONLY.
 */
function requestCancellation() {

    if (!currentOrder) {
        alert("There is no active order.");
        return;
    }

    if (currentOrder.status !== "Pending") {
        alert("Cancellation is only allowed while the order is Pending.");
        return;
    }

    const field  = document.getElementById("cancelReason");
    const reason = field ? field.value.trim() : prompt("Reason for cancellation:");

    if (!reason) {
        alert("Please provide a cancellation reason.");
        return;
    }

    /*
     * DEMO: Cancellation request is stored locally.
     * The real approval/rejection requires the Admin module.
     */
    currentOrder.cancellationRequest = {
        id:      "CAN" + Date.now(),
        orderId: currentOrder.id,
        reason:  reason,
        status:  "Pending"
    };

    alert(
        "Cancellation request submitted. The order remains Pending until an admin reviews it."
    );

    showTracking();

}

/* ============================================================
   DEMO-ONLY: ADMIN ACTIONS
   These functions simulate admin behaviour in the browser for
   demonstration purposes. They do NOT persist to the database.
   Real status changes come from the Admin / Restaurant module.
   ============================================================ */

function adminApproveCancellation() {

    if (!currentOrder || !currentOrder.cancellationRequest) {
        alert("No pending cancellation request.");
        return;
    }

    currentOrder.cancellationRequest.status = "Approved";
    currentOrder.status       = "Cancelled";
    currentOrder.paymentStatus = "Pending";

    alert("[DEMO] Cancellation approved.");

    showTracking();

}

function adminRejectCancellation() {

    if (!currentOrder || !currentOrder.cancellationRequest) {
        alert("No pending cancellation request.");
        return;
    }

    currentOrder.cancellationRequest.status = "Rejected";

    alert("[DEMO] Cancellation rejected.");

    showTracking();

}

function advanceOrderStatus() {

    if (!currentOrder) {
        alert("Place an order first.");
        return;
    }

    const index = statuses.indexOf(currentOrder.status);

    if (index < 0 || index >= statuses.length - 1) {
        alert("The order cannot be advanced further.");
        return;
    }

    currentOrder.status = statuses[index + 1];

    if (currentOrder.status === "Delivered") {

        if (currentOrder.paymentMethod === "Cash on Delivery") {
            currentOrder.paymentStatus = "Successful";
        }

        cart = [];
        updateCart();

        alert("[DEMO] Order Delivered! Cart cleared.");

    }

    showTracking();

}

/* ============================================================
   REVIEW
   ============================================================ */

/**
 * Show the review panel.
 * Only allowed after the order is Delivered.
 */
function showReview() {

    if (
        !currentOrder ||
        currentOrder.status !== "Delivered"
    ) {

        alert(
            "You can review the restaurant only after the order is delivered."
        );

        return;

    }

    hideAllSections();

    document.getElementById("reviewPanel").style.display = "block";

    const reviewRestaurant = document.getElementById("reviewRestaurant");

    if (reviewRestaurant) {
        reviewRestaurant.textContent =
            currentOrder.restaurantName +
            " • Order #" +
            currentOrder.id;
    }

    window.scrollTo({ top: 0, behavior: "smooth" });

}

/**
 * Submit a review via AJAX.
 * Server enforces: delivered order required + no duplicate.
 */
function submitReview() {

    if (
        !currentOrder ||
        currentOrder.status !== "Delivered"
    ) {

        alert("Review is available only after delivery.");

        return;

    }

    if (currentOrder.review) {

        alert("This order has already been reviewed.");

        return;

    }

    const ratingEl  = document.getElementById("reviewRating");
    const commentEl = document.getElementById("reviewComment");

    if (!ratingEl || !commentEl) {
        return;
    }

    const rating  = Number(ratingEl.value);
    const comment = commentEl.value.trim();

    if (comment === "") {
        alert("Please write your review.");
        return;
    }

    fetch("../../controllers/customer_ajax_controller.php?action=submit_review", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({
            restaurantId: currentOrder.restaurantId,
            rating:       rating,
            comment:      comment
        })

    })

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            currentOrder.review = {
                rating:  rating,
                comment: comment
            };

            alert(data.message);

            commentEl.value = "";

            showRestaurants();

        } else {

            alert(data.message || "Review could not be submitted.");

        }

    })

    .catch(error => {

        console.error("Review error:", error);

        alert("Unable to submit review. Please try again.");

    });

}

/* ============================================================
   PROFILE DROPDOWN TOGGLE
   ============================================================ */

(function initProfileDropdown() {

    const profileToggle = document.getElementById("profileToggle");
    const profileDropdown = document.getElementById("profileDropdown");

    if (profileToggle && profileDropdown) {

        profileToggle.addEventListener("click", function (event) {
            event.stopPropagation();
            profileDropdown.classList.toggle("show");
        });

        document.addEventListener("click", function (event) {
            if (!profileToggle.contains(event.target)) {
                profileDropdown.classList.remove("show");
            }
        });
    }

})();

/* ============================================================
   INITIAL PAGE LOAD
   ============================================================ */

/* Set initial area from the dropdown and filter restaurants */
(function initArea() {

    const areaSelect = document.getElementById("customerArea");

    if (areaSelect) {
        customerArea = areaSelect.value;
        
        // Filter the initial PHP-injected restaurants by the default selected area
        if (window.CUSTOMER_RESTAURANTS && Array.isArray(window.CUSTOMER_RESTAURANTS)) {
            restaurants = window.CUSTOMER_RESTAURANTS.filter(r => r.area === customerArea);
        }
    }

})();

displayRestaurants();
updateCart();
