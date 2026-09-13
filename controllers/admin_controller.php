<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/admin_model.php";


if (isset($_GET["action"]) && $_GET["action"] == "logout")
{
    session_unset();
    session_destroy();

    header("Location: ../views/auth/login.php");
    exit;
}


if (!isset($_SESSION["admin_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "admin")
{
    header("Location: ../auth/login.php");
    exit;
}


$adminId = (int) $_SESSION["admin_id"];

$admin = getAdminById($adminId);

if (!$admin)
{
    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit;
}


$currentPage = basename($_SERVER["PHP_SELF"]);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]))
{
    require_once __DIR__ . "/../models/user_model.php";

    $postAction = $_POST["action"];
    $redirectTo = "dashboard.php";

    switch ($postAction)
    {
        case "update_admin_profile":
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
            elseif ($email !== $admin["email"] && emailExists($email))
            {
                $_SESSION["flash_error"] = "That email is already registered.";
            }
            elseif (adminUsernameExistsForOther($username, $adminId))
            {
                $_SESSION["flash_error"] = "That username is already taken.";
            }
            else
            {
                updateAdminProfile($adminId, $name, $email, $username);

                $uploadedImagePath = handleProfileImageUpload("profile_picture", "admin", "admin_" . $adminId);

                if ($uploadedImagePath === false)
                {
                    $_SESSION["flash_error"] = "Profile updated, but the picture upload failed. Use a JPG, PNG or WEBP under 2MB.";
                }
                elseif ($uploadedImagePath !== null)
                {
                    updateAdminProfileImage($adminId, $uploadedImagePath);
                    $_SESSION["flash_success"] = "Profile and picture updated successfully.";
                }
                else
                {
                    $_SESSION["flash_success"] = "Profile updated successfully.";
                }
            }
            $redirectTo = "profile.php";
            break;

        case "delete_own_admin":
            $targetAdminId = (int) $_POST["admin_id"];
            $result = deleteOwnAdminAccount($adminId, $targetAdminId);

            if ($result === "deleted")
            {
                session_unset();
                session_destroy();

                header("Location: ../auth/login.php");
                exit;
            }
            elseif ($result === "last_admin")
            {
                $_SESSION["flash_error"] = "Cannot delete: CRAVERUSH must always retain at least one Admin account.";
            }
            else
            {
                $_SESSION["flash_error"] = "You can only delete your own Admin account.";
            }
            $redirectTo = "profile.php";
            break;

        case "add_food_item":
            $restaurantId = (int) $_POST["restaurant_id"];
            $name = cleanInput($_POST["name"]);
            $description = cleanInput($_POST["description"] ?? "");
            $price = (float) $_POST["price"];
            $category = cleanInput($_POST["category"]);
            $availabilityStatus = cleanInput($_POST["availability_status"]);

            if ($price <= 0)
            {
                $_SESSION["flash_error"] = "Price must be a positive amount.";
            }
            elseif (!in_array($availabilityStatus, ["Available", "Unavailable"], true))
            {
                $_SESSION["flash_error"] = "Invalid availability status.";
            }
            else
            {
                insertFoodItem($restaurantId, $name, $description, $price, $category, $availabilityStatus);
                $_SESSION["flash_success"] = "Food item created successfully.";
            }
            $redirectTo = "food_items.php";
            break;

        case "delete_food_item":
            deleteFoodItem((int) $_POST["food_id"]);
            $_SESSION["flash_success"] = "Food item deleted.";
            $redirectTo = "food_items.php";
            break;

        case "add_customer":
            $name = cleanInput($_POST["name"]);
            $phone = cleanInput($_POST["phone"]);
            $email = cleanInput($_POST["email"]);
            $areaId = (int) $_POST["area_id"];

            if (emailExists($email))
            {
                $_SESSION["flash_error"] = "That email is already registered.";
            }
            else
            {
                $hashed = password_hash($_POST["password"], PASSWORD_DEFAULT);
                insertCustomer($name, $phone, $email, $hashed, $areaId);
                $_SESSION["flash_success"] = "Customer created successfully.";
            }
            $redirectTo = "users.php?tab=customers";
            break;

        case "add_restaurant":
            $name = cleanInput($_POST["name"]);
            $phone = cleanInput($_POST["phone"]);
            $email = cleanInput($_POST["email"]);
            $username = cleanInput($_POST["username"]);
            $areaId = (int) $_POST["area_id"];

            if (emailExists($email))
            {
                $_SESSION["flash_error"] = "That email is already registered.";
            }
            elseif (restaurantUsernameExists($username))
            {
                $_SESSION["flash_error"] = "That username is already taken.";
            }
            else
            {
                $hashed = password_hash($_POST["password"], PASSWORD_DEFAULT);
                insertRestaurant($name, $phone, $email, $username, $hashed, $areaId);
                $_SESSION["flash_success"] = "Restaurant created successfully.";
            }
            $redirectTo = "users.php?tab=restaurants";
            break;

        case "add_deliveryman":
            $name = cleanInput($_POST["name"]);
            $phone = cleanInput($_POST["phone"]);
            $email = cleanInput($_POST["email"]);
            $vehicleType = cleanInput($_POST["vehicle_type"]);
            $areaId = (int) $_POST["area_id"];

            if (emailExists($email))
            {
                $_SESSION["flash_error"] = "That email is already registered.";
            }
            else
            {
                $hashed = password_hash($_POST["password"], PASSWORD_DEFAULT);
                insertDeliveryman($name, $phone, $email, $hashed, $vehicleType, $areaId);
                $_SESSION["flash_success"] = "Deliveryman created successfully.";
            }
            $redirectTo = "users.php?tab=deliverymen";
            break;

        case "delete_customer":
            deleteCustomer((int) $_POST["customer_id"]);
            $_SESSION["flash_success"] = "Customer deleted.";
            $redirectTo = "users.php?tab=customers";
            break;

        case "delete_restaurant":
            deleteRestaurant((int) $_POST["restaurant_id"]);
            $_SESSION["flash_success"] = "Restaurant deleted.";
            $redirectTo = "users.php?tab=restaurants";
            break;

        case "delete_deliveryman":
            if (deleteDeliveryman((int) $_POST["deliveryman_id"]))
            {
                $_SESSION["flash_success"] = "Deliveryman deleted.";
            }
            else
            {
                $_SESSION["flash_error"] = "Cannot delete: this deliveryman has an active order.";
            }
            $redirectTo = "users.php?tab=deliverymen";
            break;

        case "delete_order":
            deleteOrder((int) $_POST["order_id"]);
            $_SESSION["flash_success"] = "Order deleted.";
            $redirectTo = "orders.php";
            break;

        case "approve_area_request":
            approveAreaChangeRequest((int) $_POST["request_id"]);
            $_SESSION["flash_success"] = "Area change request approved.";
            $redirectTo = "requests.php?tab=area";
            break;

        case "reject_area_request":
            rejectAreaChangeRequest((int) $_POST["request_id"]);
            $_SESSION["flash_success"] = "Area change request rejected.";
            $redirectTo = "requests.php?tab=area";
            break;

        case "approve_cancellation":
            approveCancellationRequest((int) $_POST["cancellation_id"]);
            $_SESSION["flash_success"] = "Cancellation approved.";
            $redirectTo = "requests.php?tab=cancellation";
            break;

        case "reject_cancellation":
            rejectCancellationRequest((int) $_POST["cancellation_id"]);
            $_SESSION["flash_success"] = "Cancellation request rejected.";
            $redirectTo = "requests.php?tab=cancellation";
            break;

        case "add_area":
            addArea(cleanInput($_POST["area_name"]));
            $_SESSION["flash_success"] = "Area added.";
            $redirectTo = "areas.php";
            break;

        case "delete_area":
            if (deleteArea((int) $_POST["area_id"]))
            {
                $_SESSION["flash_success"] = "Area deleted.";
            }
            else
            {
                $_SESSION["flash_error"] = "Cannot delete: this area still has users assigned to it.";
            }
            $redirectTo = "areas.php";
            break;

        case "add_adjacency":
            addAreaAdjacency((int) $_POST["area_id_1"], (int) $_POST["area_id_2"]);
            $_SESSION["flash_success"] = "Areas linked as adjacent.";
            $redirectTo = "areas.php";
            break;

        case "remove_adjacency":
            removeAreaAdjacency((int) $_POST["area_id_1"], (int) $_POST["area_id_2"]);
            $_SESSION["flash_success"] = "Adjacency removed.";
            $redirectTo = "areas.php";
            break;

        case "update_delivery_fee":
            $fee = (float) $_POST["delivery_fee"];
            if ($fee > 0)
            {
                updateDeliveryFee($fee);
                $_SESSION["flash_success"] = "Delivery fee updated.";
            }
            else
            {
                $_SESSION["flash_error"] = "Delivery fee must be a positive amount.";
            }
            $redirectTo = "reports.php";
            break;
    }

    header("Location: $redirectTo");
    exit;
}
