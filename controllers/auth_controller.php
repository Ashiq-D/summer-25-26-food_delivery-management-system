<?php

require_once __DIR__ . "/../helpers/helpers.php";
require_once __DIR__ . "/../models/user_model.php";


$userType = "customer";

$name = "";
$email = "";
$phone = "";
$areaId = "";

$restaurantUsername = "";
$vehicleType = "";

$userTypeErr = "";
$nameErr = "";
$emailErr = "";
$phoneErr = "";
$areaErr = "";
$passwordErr = "";
$confirmPasswordErr = "";
$restaurantUsernameErr = "";
$vehicleTypeErr = "";

$dbErr = "";


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (empty($_POST["user_type"]))
    {
        $userTypeErr = "Please select an account type.";
    }

    else
    {
        $userType = cleanInput($_POST["user_type"]);

        if (
            $userType != "customer" &&
            $userType != "restaurant" &&
            $userType != "deliveryman"
        )
        {
            $userTypeErr = "Invalid account type.";
        }
    }


    if (empty($_POST["name"]))
    {
        $nameErr = "Name is required.";
    }

    else
    {
        $name = cleanInput($_POST["name"]);

        if (strlen($name) < 2)
        {
            $nameErr = "Name must contain at least 2 characters.";
        }

        else if (!preg_match("/^[a-zA-Z-' ]+$/", $name))
        {
            $nameErr = "Enter a valid name.";
        }
    }


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


    if (empty($_POST["phone"]))
    {
        $phoneErr = "Phone number is required.";
    }

    else
    {
        $phone = cleanInput($_POST["phone"]);

        if (!preg_match("/^01[3-9][0-9]{8}$/", $phone))
        {
            $phoneErr = "Enter a valid phone number.";
        }
    }


    if (empty($_POST["area_id"]))
    {
        $areaErr = "Please select an area.";
    }

    else
    {
        $areaId = cleanInput($_POST["area_id"]);

        if (!filter_var($areaId, FILTER_VALIDATE_INT))
        {
            $areaErr = "Invalid area.";
        }

        else
        {
            $areaId = (int) $areaId;

            if (!areaExists($areaId))
            {
                $areaErr = "Selected area does not exist.";
            }
        }
    }


    if (empty($_POST["password"]))
    {
        $passwordErr = "Password is required.";
    }

    else
    {
        $password = $_POST["password"];

        if (strlen($password) < 8)
        {
            $passwordErr = "Password must contain at least 8 characters.";
        }

        else if (
            !preg_match("/[A-Za-z]/", $password) ||
            !preg_match("/[0-9]/", $password)
        )
        {
            $passwordErr = "Password must contain at least one letter and one number.";
        }
    }


    if (empty($_POST["confirm_password"]))
    {
        $confirmPasswordErr = "Please confirm your password.";
    }

    else
    {
        $confirmPassword = $_POST["confirm_password"];

        if (!empty($password) && $password != $confirmPassword)
        {
            $confirmPasswordErr = "Passwords do not match.";
        }
    }


    if ($userType == "restaurant")
    {
        if (empty($_POST["restaurant_username"]))
        {
            $restaurantUsernameErr = "Username is required.";
        }

        else
        {
            $restaurantUsername = cleanInput($_POST["restaurant_username"]);

            if (strlen($restaurantUsername) < 3)
            {
                $restaurantUsernameErr = "Username must contain at least 3 characters.";
            }

            else if (!preg_match("/^[a-zA-Z0-9_]+$/", $restaurantUsername))
            {
                $restaurantUsernameErr = "Username can contain only letters, numbers and underscore.";
            }
        }
    }


    if ($userType == "deliveryman")
    {
        if (empty($_POST["vehicle_type"]))
        {
            $vehicleTypeErr = "Please select a vehicle type.";
        }

        else
        {
            $vehicleType = cleanInput($_POST["vehicle_type"]);

            if (
                $vehicleType != "Bicycle" &&
                $vehicleType != "Motorcycle" &&
                $vehicleType != "Car"
            )
            {
                $vehicleTypeErr = "Invalid vehicle type.";
            }
        }
    }


    if (
        empty($userTypeErr) &&
        empty($nameErr) &&
        empty($emailErr) &&
        empty($phoneErr) &&
        empty($areaErr) &&
        empty($passwordErr) &&
        empty($confirmPasswordErr) &&
        empty($restaurantUsernameErr) &&
        empty($vehicleTypeErr)
    )
    {
        if (emailExists($email))
        {
            $emailErr = "An account with this email already exists.";
        }
    }


    if (
        empty($userTypeErr) &&
        empty($nameErr) &&
        empty($emailErr) &&
        empty($phoneErr) &&
        empty($areaErr) &&
        empty($passwordErr) &&
        empty($confirmPasswordErr) &&
        empty($restaurantUsernameErr) &&
        empty($vehicleTypeErr)
    )
    {
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        if ($userType == "customer")
        {
            if (insertCustomer($name, $phone, $email, $hashedPassword, $areaId))
            {
                header("Location: login.php");
                exit;
            }

            else
            {
                $dbErr = "Failed to create customer account.";
            }
        }


        else if ($userType == "restaurant")
        {
            if (restaurantUsernameExists($restaurantUsername))
            {
                $restaurantUsernameErr = "Username already exists.";
            }

            else
            {
                if (insertRestaurant($name, $phone, $email, $restaurantUsername, $hashedPassword, $areaId))
                {
                    header("Location: login.php");
                    exit;
                }

                else
                {
                    $dbErr = "Failed to create restaurant account.";
                }
            }
        }


        else if ($userType == "deliveryman")
        {
            if (insertDeliveryman($name, $phone, $email, $hashedPassword, $vehicleType, $areaId))
            {
                header("Location: login.php");
                exit;
            }

            else
            {
                $dbErr = "Failed to create deliveryman account.";
            }
        }
    }
}


$areas = getAreas();


if (!$areas)
{
    die("Failed to load areas.");
}

?>