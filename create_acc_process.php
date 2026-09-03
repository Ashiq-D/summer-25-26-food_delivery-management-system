<?php

require_once "database/db.php";


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


function cleanInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);

    return $data;
}


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

            $areaCheck = mysqli_prepare(
                $conn,
                "SELECT Area_ID
                FROM Area
                WHERE Area_ID = ?"
            );

            mysqli_stmt_bind_param(
                $areaCheck,
                "i",
                $areaId
            );

            mysqli_stmt_execute($areaCheck);

            $areaResult = mysqli_stmt_get_result($areaCheck);


            if (mysqli_num_rows($areaResult) == 0)
            {
                $areaErr = "Selected area does not exist.";
            }


            mysqli_stmt_close($areaCheck);
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
                $vehicleType != "Scooter"
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
        $emailCheck = mysqli_prepare(
            $conn,
            "SELECT Email
            FROM Customer
            WHERE Email = ?

            UNION ALL

            SELECT Email
            FROM Restaurant
            WHERE Email = ?

            UNION ALL

            SELECT Email
            FROM Deliveryman
            WHERE Email = ?

            UNION ALL

            SELECT Email
            FROM Admin
            WHERE Email = ?

            LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $emailCheck,
            "ssss",
            $email,
            $email,
            $email,
            $email
        );

        mysqli_stmt_execute($emailCheck);

        $emailResult = mysqli_stmt_get_result($emailCheck);


        if (mysqli_num_rows($emailResult) > 0)
        {
            $emailErr = "An account with this email already exists.";
        }


        mysqli_stmt_close($emailCheck);
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
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO Customer
                (Name, Phone_Number, Email, Password, Area_ID)
                VALUES (?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssi",
                $name,
                $phone,
                $email,
                $hashedPassword,
                $areaId
            );


            if (mysqli_stmt_execute($stmt))
            {
                mysqli_stmt_close($stmt);

                header("Location: login.php");
                exit;
            }

            else
            {
                $dbErr = "Failed to create customer account.";
            }


            mysqli_stmt_close($stmt);
        }


        else if ($userType == "restaurant")
        {
            $usernameCheck = mysqli_prepare(
                $conn,
                "SELECT Restaurant_ID
                FROM Restaurant
                WHERE Username = ?"
            );

            mysqli_stmt_bind_param(
                $usernameCheck,
                "s",
                $restaurantUsername
            );

            mysqli_stmt_execute($usernameCheck);

            $usernameResult = mysqli_stmt_get_result($usernameCheck);


            if (mysqli_num_rows($usernameResult) > 0)
            {
                $restaurantUsernameErr = "Username already exists.";
            }

            else
            {
                $availabilityStatus = "Available";

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO Restaurant
                    (Name, Phone_Number, Email, Username, Password, Area_ID, Availability_Status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssis",
                    $name,
                    $phone,
                    $email,
                    $restaurantUsername,
                    $hashedPassword,
                    $areaId,
                    $availabilityStatus
                );


                if (mysqli_stmt_execute($stmt))
                {
                    mysqli_stmt_close($stmt);
                    mysqli_stmt_close($usernameCheck);

                    header("Location: login.php");
                    exit;
                }

                else
                {
                    $dbErr = "Failed to create restaurant account.";
                }


                mysqli_stmt_close($stmt);
            }


            mysqli_stmt_close($usernameCheck);
        }


        else if ($userType == "deliveryman")
        {
            $onlineStatus = "Offline";
            $availabilityStatus = "Available";

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO Deliveryman
                (Name, Phone_Number, Email, Password, Vehicle_Type, Area_ID, Online_Status, Availability_Status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssiss",
                $name,
                $phone,
                $email,
                $hashedPassword,
                $vehicleType,
                $areaId,
                $onlineStatus,
                $availabilityStatus
            );


            if (mysqli_stmt_execute($stmt))
            {
                mysqli_stmt_close($stmt);

                header("Location: login.php");
                exit;
            }

            else
            {
                $dbErr = "Failed to create deliveryman account.";
            }


            mysqli_stmt_close($stmt);
        }
    }
}


$areas = mysqli_query(
    $conn,
    "SELECT Area_ID AS area_id,
    Area_Name AS area_name
    FROM Area
    ORDER BY Area_Name ASC"
);


if (!$areas)
{
    die("Failed to load areas.");
}

?>