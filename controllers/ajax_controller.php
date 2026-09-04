<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/deliveryman_model.php";


function sendJson($success, $message)
{
    header("Content-Type: application/json");

    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit;
}


if (!isset($_SESSION["deliveryman_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "deliveryman")
{
    sendJson(false, "Please login first.");
}


$deliverymanId = (int) $_SESSION["deliveryman_id"];


if ($_SERVER["REQUEST_METHOD"] != "POST")
{
    sendJson(false, "Invalid request.");
}


$action = $_POST["action"] ?? "";

if ($action == "" && isset($_POST["status"]))
{
    $action = "online_status";
}


if ($action == "online_status")
{
    $status = cleanInput($_POST["status"] ?? "");


    if ($status != "Online" && $status != "Offline")
    {
        sendJson(false, "Invalid online status.");
    }


    if (updateOnlineStatus($deliverymanId, $status))
    {
        sendJson(true, "Online status updated.");
    }


    sendJson(false, "Failed to update online status.");
}


else if ($action == "start_delivery")
{
    $delivery = getReadyDeliveryForStart($deliverymanId);

    if (!$delivery)
    {
        sendJson(false, "No Ready delivery found.");
    }


    $deliveryId = (int) $delivery["delivery_id"];
    $orderId = (int) $delivery["order_id"];


    if (startDeliveryTransaction($deliverymanId, $deliveryId, $orderId))
    {
        sendJson(true, "Delivery started.");
    }


    sendJson(false, "Failed to start delivery.");
}


else if ($action == "delivered")
{
    $delivery = getOnTheWayDeliveryForCompletion($deliverymanId);

    if (!$delivery)
    {
        sendJson(false, "No active delivery found.");
    }


    $deliveryId = (int) $delivery["delivery_id"];
    $orderId = (int) $delivery["order_id"];
    $paymentMethod = $delivery["payment_method"];


    if (completeDeliveryTransaction($deliverymanId, $deliveryId, $orderId, $paymentMethod))
    {
        sendJson(true, "Delivery completed.");
    }


    sendJson(false, "Failed to complete delivery.");
}


sendJson(false, "Invalid action.");
