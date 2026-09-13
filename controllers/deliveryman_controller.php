<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/deliveryman_model.php";


if (isset($_GET["action"]) && $_GET["action"] == "logout")
{
    session_unset();
    session_destroy();

    header("Location: ../views/auth/login.php");
    exit;
}


if (!isset($_SESSION["deliveryman_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "deliveryman")
{
    header("Location: ../auth/login.php");
    exit;
}


$deliverymanId = (int) $_SESSION["deliveryman_id"];

$deliveryman = getDeliverymanById($deliverymanId);

if (!$deliveryman)
{
    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit;
}

$successMessage = "";
$errorMessage = "";


if (isset($_SESSION["profile_success"]))
{
    $successMessage = $_SESSION["profile_success"];

    unset($_SESSION["profile_success"]);
}


if (isset($_SESSION["profile_error"]))
{
    $errorMessage = $_SESSION["profile_error"];

    unset($_SESSION["profile_error"]);
}


$currentPage = basename($_SERVER["PHP_SELF"]);


if ($currentPage == "profile.php" && $_SERVER["REQUEST_METHOD"] == "POST")
{
    $action = $_POST["action"] ?? "";

    if ($action == "update_profile")
    {
        $name = cleanInput($_POST["name"] ?? "");
        $email = cleanInput($_POST["email"] ?? "");
        $phone = cleanInput($_POST["phone_number"] ?? "");
        $vehicleType = cleanInput($_POST["vehicle_type"] ?? "");
        $areaId = (int) ($_POST["area_id"] ?? 0);


        if ($name == "")
        {
            $_SESSION["profile_error"] = "Name is required.";

            header("Location: profile.php");
            exit;
        }


        if (!preg_match("/^[a-zA-Z-' ]+$/", $name))
        {
            $_SESSION["profile_error"] = "Enter a valid name.";

            header("Location: profile.php");
            exit;
        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $_SESSION["profile_error"] = "Enter a valid email address.";

            header("Location: profile.php");
            exit;
        }


        if (!preg_match("/^01[3-9][0-9]{8}$/", $phone))
        {
            $_SESSION["profile_error"] = "Enter a valid phone number.";

            header("Location: profile.php");
            exit;
        }


        $vehicles = ["Bicycle", "Bike", "Car"];


        if (!in_array($vehicleType, $vehicles, true))
        {
            $_SESSION["profile_error"] = "Select a valid vehicle type.";

            header("Location: profile.php");
            exit;
        }


        if ($areaId <= 0)
        {
            $_SESSION["profile_error"] = "Please select an area.";

            header("Location: profile.php");
            exit;
        }


        if (!deliverymanAreaExists($areaId))
        {
            $_SESSION["profile_error"] = "Selected area does not exist.";

            header("Location: profile.php");
            exit;
        }


        if (deliverymanEmailExistsForOther($email, $deliverymanId))
        {
            $_SESSION["profile_error"] = "Email already exists.";

            header("Location: profile.php");
            exit;
        }


        $currentAreaId = getDeliverymanCurrentAreaId($deliverymanId);

        if ($currentAreaId === null)
        {
            $_SESSION["profile_error"] = "Deliveryman not found.";

            header("Location: profile.php");
            exit;
        }


        if ($currentAreaId != $areaId)
        {
            if (deliverymanHasActiveOrders($deliverymanId))
            {
                $_SESSION["profile_error"] = "You cannot change area while you have an active delivery.";

                header("Location: profile.php");
                exit;
            }
        }


        if (updateDeliverymanProfile($deliverymanId, $name, $email, $phone, $vehicleType, $areaId))
        {
            $uploadedImagePath = handleProfileImageUpload("profile_picture", "deliveryman", "deliveryman_" . $deliverymanId);

            if ($uploadedImagePath === false)
            {
                $_SESSION["profile_error"] = "Profile updated, but the picture upload failed. Use a JPG, PNG or WEBP under 2MB.";

                header("Location: profile.php");
                exit;
            }
            elseif ($uploadedImagePath !== null)
            {
                updateDeliverymanProfileImage($deliverymanId, $uploadedImagePath);
                $_SESSION["profile_success"] = "Profile and picture updated successfully.";
            }
            else
            {
                $_SESSION["profile_success"] = "Profile updated successfully.";
            }

            header("Location: profile.php");
            exit;
        }


        $_SESSION["profile_error"] = "Failed to update profile.";

        header("Location: profile.php");
        exit;
    }


    if ($action == "delete_account")
    {
        if (deliverymanHasActiveOrders($deliverymanId))
        {
            $_SESSION["profile_error"] = "You cannot delete your account while you have an active delivery.";

            header("Location: profile.php");
            exit;
        }

        if (deleteDeliverymanAccount($deliverymanId))
        {
            session_unset();
            session_destroy();

            header("Location: ../auth/login.php");
            exit;
        }

        $_SESSION["profile_error"] = "Failed to delete account.";

        header("Location: profile.php");
        exit;
    }
}


if ($currentPage == "orders.php" && $_SERVER["REQUEST_METHOD"] == "POST")
{
    $action = $_POST["action"] ?? "";

    if ($action == "claim_order")
    {
        $orderId = (int) ($_POST["order_id"] ?? 0);

        if ($orderId > 0 && claimOrderForDeliveryman($deliverymanId, $orderId))
        {
            $_SESSION["profile_success"] = "Order #CR-" . $orderId . " picked up. Head to Current Delivery to start it.";
        }
        else
        {
            $_SESSION["profile_error"] = "Could not pick up that order. It may have just been claimed by someone else.";
        }

        header("Location: orders.php");
        exit;
    }
}


if ($currentPage == "dashboard.php")
{
    $totalDeliveries = 0;
    $completedDeliveries = 0;
    $activeDeliveries = 0;
    $totalEarnings = 0;

    $stats = getDeliverymanStats($deliverymanId);

    if ($stats)
    {
        $totalDeliveries = $stats["total_deliveries"] ?? 0;
        $completedDeliveries = $stats["completed_deliveries"] ?? 0;
        $activeDeliveries = $stats["active_deliveries"] ?? 0;
        $totalEarnings = $stats["total_earnings"] ?? 0;
    }

    $activeOrder = getActiveOrderSummary($deliverymanId);
}


else if ($currentPage == "orders.php")
{
    $assignedOrder = getAssignedOrder($deliverymanId);

    $availableOrders = [];

    if (!$assignedOrder)
    {
        $availableOrders = getAvailableOrdersForDeliveryman($deliverymanId);
    }
}


else if ($currentPage == "current.php")
{
    $currentDelivery = getCurrentDelivery($deliverymanId);
    $orderItems = false;

    if ($currentDelivery)
    {
        $orderId = (int) $currentDelivery["order_id"];

        $orderItems = getOrderItems($orderId);
    }
}


else if ($currentPage == "history.php")
{
    $deliveryHistory = getDeliveryHistory($deliverymanId);
}


else if ($currentPage == "earnings.php")
{
    $totalEarnings = 0;
    $completedDeliveries = 0;
    $averageDeliveryFee = 0;
    $latestDeliveryFee = 0;

    $earnings = getEarningsSummary($deliverymanId);

    if ($earnings)
    {
        $completedDeliveries = $earnings["completed_deliveries"] ?? 0;
        $totalEarnings = $earnings["total_earnings"] ?? 0;
        $averageDeliveryFee = $earnings["average_delivery_fee"] ?? 0;
    }

    $latestDeliveryFee = getLatestDeliveryFee($deliverymanId);

    $earningHistory = getEarningHistory($deliverymanId);
}


else if ($currentPage == "profile.php")
{
    $areas = getDeliverymanAreas();

    if (!$areas)
    {
        die("Failed to load areas.");
    }
}

?>
