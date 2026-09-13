// ── Tab switching ─────────────────────────────────────────────────────

function showTab(tab)
{
    document.querySelectorAll(".tab-section").forEach(function(el)
    {
        el.classList.remove("active");
    });

    document.querySelectorAll(".menu-item").forEach(function(el)
    {
        el.classList.remove("active");
    });

    document.getElementById("tab-" + tab).classList.add("active");
    document.getElementById("nav-" + tab).classList.add("active");

    if (tab === "menu")
    {
        loadMenuItems();
    }
    else if (tab === "orders")
    {
        loadOrders();
    }
}

// ── Toast ─────────────────────────────────────────────────────────────

function showToast(message, isError)
{
    var toast = document.getElementById("toast");

    toast.textContent = message;
    toast.className = "toast show" + (isError ? " error" : "");

    setTimeout(function()
    {
        toast.className = "toast";
    }, 3500);
}

// ── Sidebar + profile dropdown toggle (matches Admin/Deliveryman) ────────

var menuToggle = document.getElementById("menuToggle");
var sidebar = document.getElementById("sidebar");

if (menuToggle && sidebar)
{
    menuToggle.addEventListener("click", function()
    {
        sidebar.classList.toggle("show");
    });
}


var profileToggle = document.getElementById("profileToggle");
var profileDropdown = document.getElementById("profileDropdown");

if (profileToggle && profileDropdown)
{
    profileToggle.addEventListener("click", function(event)
    {
        event.stopPropagation();
        profileDropdown.classList.toggle("show");
    });

    document.addEventListener("click", function(event)
    {
        if (!profileToggle.contains(event.target))
        {
            profileDropdown.classList.remove("show");
        }
    });
}

// ── Load menu items via AJAX ──────────────────────────────────────────

function loadMenuItems()
{
    var formData = new FormData();
    formData.append("action", "get_items");

    fetch("../../controllers/restaurant_ajax_controller.php",
    {
        method: "POST",
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data)
    {
        if (data.success)
        {
            renderMenuGrid(data.data);
        }
        else
        {
            document.getElementById("menuGrid").innerHTML =
                "<p class=\"empty-state\">" + data.message + "</p>";
        }
    })
    .catch(function()
    {
        document.getElementById("menuGrid").innerHTML =
            "<p class=\"empty-state\">Failed to load menu items.</p>";
    });
}

// ── Render menu items ─────────────────────────────────────────────────

function renderMenuGrid(items)
{
    var grid = document.getElementById("menuGrid");

    if (!items || items.length === 0)
    {
        grid.innerHTML = "<p class=\"empty-state\">No menu items yet. Add your first item!</p>";
        return;
    }

    var html = "";

    items.forEach(function(item)
    {
        var isAvailable = item.availability_status === "Available";

        var categoryEmoji = {
            "Rice":      "🍚",
            "Curry":     "🍛",
            "Grill":     "🔥",
            "Fast Food": "🍔",
            "Pizza":     "🍕",
            "Pasta":     "🍝",
            "Salad":     "🥗",
            "Beverage":  "🥤",
            "Dessert":   "🍰",
            "Other":     "🍽️"
        };

        var emoji = categoryEmoji[item.category] || "🍽️";

        html += "<div class=\"food-card\" data-name=\"" + item.name.toLowerCase() + "\">";

        if (item.image_path)
        {
            html += "  <img class=\"food-thumb\" src=\"../../" + item.image_path + "\" alt=\"" + escapeHtml(item.name) + "\">";
        }
        else
        {
            html += "  <div class=\"food-emoji\">" + emoji + "</div>";
        }
        html += "  <div class=\"food-name\">" + escapeHtml(item.name) + "</div>";
        html += "  <div class=\"food-category\">" + escapeHtml(item.category) + "</div>";
        html += "  <div class=\"food-price\">৳" + parseFloat(item.price).toFixed(2) + "</div>";

        if (item.description)
        {
            html += "  <div class=\"food-description\">" + escapeHtml(item.description) + "</div>";
        }

        var newStatus  = isAvailable ? "Unavailable" : "Available";
        var toggleClass = isAvailable ? "availability-toggle available" : "availability-toggle";
        var labelClass  = isAvailable ? "toggle-label available" : "toggle-label";
        var labelText   = isAvailable ? "Available" : "Unavailable";

        html += "  <button class=\"" + toggleClass + "\"";
        html += "    onclick=\"toggleAvailability(" + item.food_id + ", '" + newStatus + "')\">";
        html += "    <span></span>";
        html += "  </button>";
        html += "  <div class=\"" + labelClass + "\">" + labelText + "</div>";
        html += "</div>";
    });

    grid.innerHTML = html;
}

// ── HTML escaping ─────────────────────────────────────────────────────

function escapeHtml(str)
{
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
}

// ── Filter menu ───────────────────────────────────────────────────────

function filterMenu()
{
    var query = document.getElementById("menuSearch").value.toLowerCase();

    document.querySelectorAll(".food-card").forEach(function(card)
    {
        var name = card.getAttribute("data-name") || "";
        card.style.display = name.includes(query) ? "" : "none";
    });
}

// ── Toggle availability ───────────────────────────────────────────────

function toggleAvailability(foodId, newStatus)
{
    var formData = new FormData();
    formData.append("action",   "toggle_availability");
    formData.append("food_id",  foodId);
    formData.append("status",   newStatus);

    fetch("../../controllers/restaurant_ajax_controller.php",
    {
        method: "POST",
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data)
    {
        if (data.success)
        {
            showToast(data.message, false);
            loadMenuItems();
        }
        else
        {
            showToast(data.message, true);
        }
    })
    .catch(function()
    {
        showToast("Request failed. Please try again.", true);
    });
}

// ── Modal controls ────────────────────────────────────────────────────

function openModal()
{
    document.getElementById("addModal").classList.add("show");

    document.getElementById("itemName").value        = "";
    document.getElementById("itemCategory").value    = "";
    document.getElementById("itemPrice").value       = "";
    document.getElementById("itemDescription").value = "";
    document.getElementById("itemImage").value        = "";
}

function closeModal()
{
    document.getElementById("addModal").classList.remove("show");
}

// ── Submit add item ───────────────────────────────────────────────────

function submitAddItem()
{
    var name        = document.getElementById("itemName").value.trim();
    var category    = document.getElementById("itemCategory").value;
    var price       = document.getElementById("itemPrice").value.trim();
    var description = document.getElementById("itemDescription").value.trim();

    if (name === "")
    {
        showToast("Item name is required.", true);
        return;
    }

    if (name.length < 2)
    {
        showToast("Item name must be at least 2 characters.", true);
        return;
    }

    if (category === "")
    {
        showToast("Please select a category.", true);
        return;
    }

    if (price === "" || isNaN(price) || parseFloat(price) <= 0)
    {
        showToast("Please enter a valid price greater than 0.", true);
        return;
    }

    var formData = new FormData();
    formData.append("action",       "add_item");
    formData.append("name",         name);
    formData.append("category",     category);
    formData.append("price",        price);
    formData.append("description",  description);

    var imageFile = document.getElementById("itemImage").files[0];
    if (imageFile)
    {
        formData.append("food_image", imageFile);
    }

    fetch("../../controllers/restaurant_ajax_controller.php",
    {
        method: "POST",
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data)
    {
        if (data.success)
        {
            closeModal();
            showToast(data.message, false);
            loadMenuItems();
        }
        else
        {
            showToast(data.message, true);
        }
    })
    .catch(function()
    {
        showToast("Request failed. Please try again.", true);
    });
}

// ── Close modal on overlay click ──────────────────────────────────────

document.getElementById("addModal").addEventListener("click", function(e)
{
    if (e.target === this)
    {
        closeModal();
    }
});

// ── Orders Management ──────────────────────────────────────────────────

function loadOrders()
{
    var formData = new FormData();
    formData.append("action", "get_orders");

    fetch("../../controllers/restaurant_ajax_controller.php",
    {
        method: "POST",
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data)
    {
        if (data.success)
        {
            renderOrdersGrid(data.data);
        }
        else
        {
            document.getElementById("ordersGrid").innerHTML =
                "<p class=\"empty-state\">" + data.message + "</p>";
        }
    })
    .catch(function()
    {
        document.getElementById("ordersGrid").innerHTML =
            "<p class=\"empty-state\">Failed to load orders.</p>";
    });
}

function renderOrdersGrid(orders)
{
    var grid = document.getElementById("ordersGrid");

    if (!orders || orders.length === 0)
    {
        grid.innerHTML = "<p class=\"empty-state\">No incoming orders yet.</p>";
        return;
    }

    var html = "";

    orders.forEach(function(order)
    {
        var badgeClass = "";
        if (order.Order_Status === "Pending") badgeClass = "badge-pending";
        else if (order.Order_Status === "Preparing") badgeClass = "badge-preparing";
        else if (order.Order_Status === "Prepared") badgeClass = "badge-prepared";
        else if (order.Order_Status === "Delivered") badgeClass = "badge-delivered";

        html += "<div class=\"order-card\">";
        html += "  <div class=\"order-header\">";
        html += "    <h3>Order #CR" + order.Order_ID + "</h3>";
        html += "    <span class=\"status-badge " + badgeClass + "\">" + escapeHtml(order.Order_Status) + "</span>";
        html += "  </div>";
        html += "  <div class=\"order-details\">";
        html += "    <p><strong>Customer:</strong> " + escapeHtml(order.Customer_Name) + "</p>";
        html += "    <p><strong>Time:</strong> " + escapeHtml(order.Order_Date) + "</p>";
        html += "    <p><strong>Total:</strong> ৳" + parseFloat(order.Total_Amount).toFixed(2) + " (" + escapeHtml(order.Payment_Method) + ")</p>";
        html += "  </div>";
        
        html += "  <div class=\"order-items\">";
        html += "    <h4>Items:</h4><ul>";
        order.items.forEach(function(item) {
            var customText = item.Customization ? " (" + escapeHtml(item.Customization) + ")" : "";
            html += "<li>" + parseInt(item.Quantity) + "x " + escapeHtml(item.Food_Name_At_Purchase) + customText + "</li>";
        });
        html += "    </ul>";
        html += "  </div>";

        html += "  <div class=\"order-actions\">";
        
        if (order.Order_Status === "Pending") {
            html += "    <button class=\"btn-primary\" onclick=\"updateOrderStatus(" + order.Order_ID + ", 'Preparing')\">Confirm Order</button>";
        } else if (order.Order_Status === "Preparing") {
            html += "    <button class=\"btn-primary\" onclick=\"updateOrderStatus(" + order.Order_ID + ", 'Prepared')\">Mark as Prepared</button>";
        }
        
        html += "  </div>";
        html += "</div>";
    });

    grid.innerHTML = html;
}

function updateOrderStatus(orderId, newStatus)
{
    var formData = new FormData();
    formData.append("action", "update_order_status");
    formData.append("order_id", orderId);
    formData.append("status", newStatus);

    fetch("../../controllers/restaurant_ajax_controller.php",
    {
        method: "POST",
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data)
    {
        if (data.success)
        {
            showToast(data.message, false);
            loadOrders();
        }
        else
        {
            showToast(data.message, true);
        }
    })
    .catch(function()
    {
        showToast("Request failed. Please try again.", true);
    });
}
