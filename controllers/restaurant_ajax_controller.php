<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/restaurant_model.php";

function sendJson($success, $message, $data = null)
{
    header("Content-Type: application/json");

    $response = [
        "success" => $success,
        "message" => $message
    ];

    if ($data !== null)
    {
        $response["data"] = $data;
    }

    echo json_encode($response);
    exit;
}

// ── Authentication guard ──────────────────────────────────────────────────────

if (
    !isset($_SESSION["restaurant_id"]) ||
    !isset($_SESSION["user_role"])     ||
    $_SESSION["user_role"] != "restaurant"
)
{
    sendJson(false, "Please login first.");
}

$restaurantId = (int) $_SESSION["restaurant_id"];

if ($_SERVER["REQUEST_METHOD"] != "POST")
{
    sendJson(false, "Invalid request method.");
}

$action = cleanInput($_POST["action"] ?? "");

// ── Get menu items ────────────────────────────────────────────────────────────

if ($action == "get_items")
{
    $items = getMenuItemsByRestaurant($restaurantId);
    sendJson(true, "Items loaded.", $items);
}

// ── Add menu item ─────────────────────────────────────────────────────────────

else if ($action == "add_item")
{
    $name        = cleanInput($_POST["name"]        ?? "");
    $description = cleanInput($_POST["description"] ?? "");
    $category    = cleanInput($_POST["category"]    ?? "");
    $price       = $_POST["price"] ?? "";

    if ($name == "")
    {
        sendJson(false, "Item name is required.");
    }

    if (strlen($name) < 2)
    {
        sendJson(false, "Item name must be at least 2 characters.");
    }

    if ($category == "")
    {
        sendJson(false, "Category is required.");
    }

    if ($price === "" || !is_numeric($price) || (float) $price <= 0)
    {
        sendJson(false, "Please enter a valid price greater than 0.");
    }

    $price = (float) $price;

    if (addMenuItemForRestaurant($restaurantId, $name, $description, $price, $category))
    {
        sendJson(true, "Menu item added successfully.");
    }

    sendJson(false, "Failed to add menu item. Please try again.");
}

// ── Toggle availability ───────────────────────────────────────────────────────

else if ($action == "toggle_availability")
{
    $foodId    = (int) ($_POST["food_id"] ?? 0);
    $newStatus = cleanInput($_POST["status"] ?? "");

    if ($foodId <= 0)
    {
        sendJson(false, "Invalid item ID.");
    }

    if ($newStatus != "Available" && $newStatus != "Unavailable")
    {
        sendJson(false, "Invalid availability status.");
    }

    if (toggleMenuItemAvailability($foodId, $restaurantId, $newStatus))
    {
        sendJson(true, "Availability updated.");
    }

    sendJson(false, "Failed to update availability, or item not found.");
}

// ── Unknown action ────────────────────────────────────────────────────────────

else
{
    sendJson(false, "Invalid action.");
}
?>
