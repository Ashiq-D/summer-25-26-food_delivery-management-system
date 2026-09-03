<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once "database/db.php";


$email = "";

$emailErr = "";
$passwordErr = "";
$loginErr = "";


function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);

    return $data;
}


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
        $stmt = mysqli_prepare(
            $conn,
            "SELECT Customer_ID AS customer_id,
            Password AS password
            FROM Customer
            WHERE Email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);


        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "customer";
            $_SESSION["customer_id"] = $user["customer_id"];

            mysqli_stmt_close($stmt);

            header("Location: customer/customer_dashboard.php");
            exit;
        }


        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Restaurant_ID AS restaurant_id,
            Password AS password
            FROM Restaurant
            WHERE Email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);


        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "restaurant";
            $_SESSION["restaurant_id"] = $user["restaurant_id"];

            mysqli_stmt_close($stmt);

            header("Location: restaurant/restaurant_dashboard.php");
            exit;
        }


        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Deliveryman_ID AS deliveryman_id,
            Password AS password
            FROM Deliveryman
            WHERE Email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);


        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "deliveryman";
            $_SESSION["deliveryman_id"] = $user["deliveryman_id"];

            mysqli_stmt_close($stmt);

            header("Location: deliveryman/delivery_dashboard.php");
            exit;
        }


        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Admin_ID AS admin_id,
            Password AS password
            FROM Admin
            WHERE Email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);


        if (
            $user &&
            password_verify($password, $user["password"])
        )
        {
            $_SESSION["user_role"] = "admin";
            $_SESSION["admin_id"] = $user["admin_id"];

            mysqli_stmt_close($stmt);

            header("Location: admin/admin_dashboard.php");
            exit;
        }


        mysqli_stmt_close($stmt);


        $loginErr = "Invalid email or password.";
    }
}

?>