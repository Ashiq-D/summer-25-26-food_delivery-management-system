<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/user_model.php";


$email = "";

$emailErr = "";
$passwordErr = "";
$loginErr = "";


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (empty($_POST["email"]))
    {
        $emailErr = "Email is required.";
    }

    else
    {
        $email = cleanInput($_POST["email"]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $emailErr = "Enter a valid email address.";
        }
    }


    if (empty($_POST["password"]))
    {
        $passwordErr = "Password is required.";
    }

    else
    {
        $password = $_POST["password"];
    }


    if (
        empty($emailErr) &&
        empty($passwordErr)
    )
    {
        $user = getCustomerByEmail($email);

        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "customer";
            $_SESSION["customer_id"] = $user["customer_id"];

            header("Location: ../../customer/customer_dashboard.php");
            exit;
        }


        $user = getRestaurantByEmail($email);

        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "restaurant";
            $_SESSION["restaurant_id"] = $user["restaurant_id"];

            header("Location: ../../restaurant/restaurant_dashboard.php");
            exit;
        }


        $user = getDeliverymanByEmail($email);

        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "deliveryman";
            $_SESSION["deliveryman_id"] = $user["deliveryman_id"];

            header("Location: ../deliveryman/dashboard.php");
            exit;
        }


        $user = getAdminByEmail($email);

        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "admin";
            $_SESSION["admin_id"] = $user["admin_id"];

            header("Location: ../../admin/admin_dashboard.php");
            exit;
        }


        $loginErr = "Invalid email or password.";
    }
}

?>