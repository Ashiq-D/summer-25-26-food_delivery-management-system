<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/user_model.php";


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
    !isset($_SESSION["customer_id"]) ||
    !isset($_SESSION["user_role"])   ||
    $_SESSION["user_role"] != "customer"
)
{
    header("Location: ../views/auth/login.php");
    exit;
}


$customerId = (int) $_SESSION["customer_id"];


// ── Load customer data for every page ─────────────────────────────────────────

$customer = getCustomerById($customerId);

if (!$customer)
{
    session_unset();
    session_destroy();

    header("Location: ../views/auth/login.php");
    exit;
}


$areas = getAreas();


// ── Profile update (PRG pattern, matches Admin/Restaurant) ───────────────────

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]))
{
    $postAction = $_POST["action"];
    $redirectTo = "profile.php";

    switch ($postAction)
    {
        case "update_customer_profile":
            $name = cleanInput($_POST["name"] ?? "");
            $email = cleanInput($_POST["email"] ?? "");
            $phone = cleanInput($_POST["phone_number"] ?? "");
            $areaId = (int) ($_POST["area_id"] ?? 0);

            if ($name == "" || $email == "" || $phone == "")
            {
                $_SESSION["flash_error"] = "Name, email and phone number are required.";
            }
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $_SESSION["flash_error"] = "Enter a valid email address.";
            }
            elseif ($email !== $customer["email"] && emailExists($email))
            {
                $_SESSION["flash_error"] = "That email is already registered.";
            }
            elseif ($areaId <= 0 || !areaExists($areaId))
            {
                $_SESSION["flash_error"] = "Please select a valid area.";
            }
            else
            {
                updateCustomerProfile($customerId, $name, $email, $phone, $areaId);

                $uploadedImagePath = handleProfileImageUpload("profile_picture", "customer", "customer_" . $customerId);

                if ($uploadedImagePath === false)
                {
                    $_SESSION["flash_error"] = "Profile updated, but the picture upload failed. Use a JPG, PNG or WEBP under 2MB.";
                }
                elseif ($uploadedImagePath !== null)
                {
                    updateCustomerProfileImage($customerId, $uploadedImagePath);
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
