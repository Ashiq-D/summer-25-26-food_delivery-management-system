<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

// Assumes helpers.php exists in the main project structure
require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/restaurant_model.php";


// ── Logout ────────────────────────────────────────────────────────────────────

if (isset($_GET["action"]) && $_GET["action"] == "logout")
{
    session_unset();
    session_destroy();

    header("Location: ../views/auth/login.php");
    exit;
}


// ── Authentication guard ──────────────────────────────────────────────────────

if (
    !isset($_SESSION["restaurant_id"]) ||
    !isset($_SESSION["user_role"])     ||
    $_SESSION["user_role"] != "restaurant"
)
{
    header("Location: ../views/auth/login.php");
    exit;
}


$restaurantId = (int) $_SESSION["restaurant_id"];


// ── Load restaurant data for dashboard page ───────────────────────────────────

$currentPage = basename($_SERVER["PHP_SELF"]);

if ($currentPage == "restaurant_dashboard.php")
{
    $restaurant = getRestaurantById($restaurantId);

    if (!$restaurant)
    {
        die("Restaurant account not found.");
    }
}
?>
