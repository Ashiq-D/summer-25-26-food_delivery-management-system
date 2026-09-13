<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/deliveryman_model.php";

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
    !isset($_SESSION["deliveryman_id"]) ||
    !isset($_SESSION["user_role"])     ||
    $_SESSION["user_role"] != "deliveryman"
)
{
    sendJson(false, "Please login first.");
}

$deliverymanId = (int) $_SESSION["deliveryman_id"];

if ($_SERVER["REQUEST_METHOD"] != "POST")
{
    sendJson(false, "Invalid request method.");
}

$action = cleanInput($_POST["action"] ?? "");

// ── Get Available Orders ──────────────────────────────────────────────────────

if ($action == "get_available_orders")
{
    // Fetch the area from the database to guarantee it's not tampered with
    $areaId = getDeliverymanCurrentAreaId($deliverymanId);

    if ($areaId === null) {
        sendJson(false, "Could not determine your delivery area.");
    }

    $orders = getAvailableOrdersForArea($areaId);
    sendJson(true, "Available orders loaded.", $orders);
}

// ── Accept Order ──────────────────────────────────────────────────────────────

else if ($action == "accept_order")
{
    $orderId = (int) ($_POST["order_id"] ?? 0);

    if ($orderId <= 0)
    {
        sendJson(false, "Invalid order ID.");
    }

    $areaId = getDeliverymanCurrentAreaId($deliverymanId);

    if ($areaId === null) {
        sendJson(false, "Could not determine your delivery area.");
    }

    if (acceptOrder($deliverymanId, $orderId, $areaId))
    {
        sendJson(true, "Order accepted successfully.");
    }

    sendJson(false, "Failed to accept order. It may have already been assigned.");
}

// ── Unknown action ────────────────────────────────────────────────────────────

else
{
    sendJson(false, "Invalid action.");
}
?>
