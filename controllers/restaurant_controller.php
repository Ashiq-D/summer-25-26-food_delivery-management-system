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


// ── Load restaurant data for every page (matches Admin's pattern) ────────────

$restaurant = getRestaurantById($restaurantId);

if (!$restaurant)
{
    session_unset();
    session_destroy();

    header("Location: ../views/auth/login.php");
    exit;
}


$currentPage = basename($_SERVER["PHP_SELF"]);


// ── Profile update (PRG pattern, matches Admin/Deliveryman) ──────────────────

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]))
{
    require_once __DIR__ . "/../models/user_model.php";

    $postAction = $_POST["action"];
    $redirectTo = "profile.php";

    switch ($postAction)
    {
        case "update_restaurant_profile":
            $name = cleanInput($_POST["name"] ?? "");
            $email = cleanInput($_POST["email"] ?? "");
            $username = cleanInput($_POST["username"] ?? "");

            if ($name == "" || $email == "" || $username == "")
            {
                $_SESSION["flash_error"] = "Name, email and username are required.";
            }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $_SESSION["flash_error"] = "Enter a valid email address.";
            }
            elseif ($email !== $restaurant["email"] && emailExists($email))
            {
                $_SESSION["flash_error"] = "That email is already registered.";
            }
            elseif (restaurantUsernameExistsForOther($username, $restaurantId))
            {
                $_SESSION["flash_error"] = "That username is already taken.";
            }
            else
            {
                updateRestaurantProfile($restaurantId, $name, $email, $username);

                $uploadedImagePath = handleProfileImageUpload("profile_picture", "restaurant", "restaurant_" . $restaurantId);

                if ($uploadedImagePath === false)
                {
                    $_SESSION["flash_error"] = "Profile updated, but the picture upload failed. Use a JPG, PNG or WEBP under 2MB.";
                }
                elseif ($uploadedImagePath !== null)
                {
                    updateRestaurantProfileImage($restaurantId, $uploadedImagePath);
                    $_SESSION["flash_success"] = "Profile and picture updated successfully.";
                }
                else
                {
                    $_SESSION["flash_success"] = "Profile updated successfully.";
                }
            }
            $redirectTo = "profile.php";
            break;
    }

    header("Location: $redirectTo");
    exit;
}
?>
