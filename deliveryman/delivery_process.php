<?php

if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}

require_once "../database/db.php";


function sendJson($success, $message)
{
    header("Content-Type: application/json");

    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit;
}


function cleanInput($data)
{
    return stripslashes(trim($data));
}


$currentPage = basename($_SERVER["PHP_SELF"]);


if (!isset($_SESSION["deliveryman_id"]) || !isset($_SESSION["user_role"]) || $_SESSION["user_role"] != "deliveryman")
{
    if ($currentPage == "delivery_process.php")
    {
        sendJson(false, "Please login first.");
    }

    header("Location: ../login.php");
    exit;
}


$deliverymanId = (int) $_SESSION["deliveryman_id"];

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


if ($currentPage == "delivery_process.php" && $_SERVER["REQUEST_METHOD"] == "POST")
{
    $action = $_POST["action"] ?? "";

    if ($action == "logout")
    {
        session_unset();
        session_destroy();

        header("Location: ../home.php");
        exit;
    }

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


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Deliveryman
            SET Online_Status = ?
            WHERE Deliveryman_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $status, $deliverymanId);


        if (mysqli_stmt_execute($stmt))
        {
            mysqli_stmt_close($stmt);

            sendJson(true, "Online status updated.");
        }


        mysqli_stmt_close($stmt);

        sendJson(false, "Failed to update online status.");
    }


    else if ($action == "start_delivery")
    {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT d.Delivery_ID AS delivery_id,
            o.Order_ID AS order_id
            FROM Delivery d
            JOIN `Order` o ON d.Order_ID = o.Order_ID
            WHERE d.Deliveryman_ID = ?
            AND o.Order_Status = 'Ready'
            LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $delivery = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$delivery)
        {
            sendJson(false, "No Ready delivery found.");
        }


        $deliveryId = (int) $delivery["delivery_id"];
        $orderId = (int) $delivery["order_id"];


        mysqli_begin_transaction($conn);


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE `Order`
            SET Order_Status = 'On The Way'
            WHERE Order_ID = ?
            AND Order_Status = 'Ready'"
        );

        mysqli_stmt_bind_param($stmt, "i", $orderId);

        $orderUpdated = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Delivery
            SET Delivery_Status = 'On The Way'
            WHERE Delivery_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliveryId);

        $deliveryUpdated = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Deliveryman
            SET Availability_Status = 'Busy'
            WHERE Deliveryman_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliverymanId);

        $deliverymanUpdated = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        if ($orderUpdated && $deliveryUpdated && $deliverymanUpdated)
        {
            mysqli_commit($conn);

            sendJson(true, "Delivery started.");
        }


        mysqli_rollback($conn);

        sendJson(false, "Failed to start delivery.");
    }


    else if ($action == "delivered")
    {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT d.Delivery_ID AS delivery_id,
            o.Order_ID AS order_id,
            o.Payment_Method AS payment_method
            FROM Delivery d
            JOIN `Order` o ON d.Order_ID = o.Order_ID
            WHERE d.Deliveryman_ID = ?
            AND o.Order_Status = 'On The Way'
            LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $delivery = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$delivery)
        {
            sendJson(false, "No active delivery found.");
        }


        $deliveryId = (int) $delivery["delivery_id"];
        $orderId = (int) $delivery["order_id"];
        $paymentMethod = $delivery["payment_method"];


        mysqli_begin_transaction($conn);


        if ($paymentMethod == "Cash on Delivery")
        {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE `Order`
                SET Order_Status = 'Delivered',
                Payment_Status = 'Successful'
                WHERE Order_ID = ?
                AND Order_Status = 'On The Way'"
            );
        }

        else
        {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE `Order`
                SET Order_Status = 'Delivered'
                WHERE Order_ID = ?
                AND Order_Status = 'On The Way'"
            );
        }


        mysqli_stmt_bind_param($stmt, "i", $orderId);

        $orderUpdated = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Delivery
            SET Delivery_Status = 'Delivered'
            WHERE Delivery_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliveryId);

        $deliveryUpdated = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Deliveryman
            SET Availability_Status = 'Available'
            WHERE Deliveryman_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliverymanId);

        $deliverymanUpdated = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        if ($orderUpdated && $deliveryUpdated && $deliverymanUpdated)
        {
            mysqli_commit($conn);

            sendJson(true, "Delivery completed.");
        }


        mysqli_rollback($conn);

        sendJson(false, "Failed to complete delivery.");
    }


    else if ($action == "update_profile")
    {
        $name = cleanInput($_POST["name"] ?? "");
        $email = cleanInput($_POST["email"] ?? "");
        $phone = cleanInput($_POST["phone_number"] ?? "");
        $vehicleType = cleanInput($_POST["vehicle_type"] ?? "");
        $areaId = (int) ($_POST["area_id"] ?? 0);


        if ($name == "")
        {
            $_SESSION["profile_error"] = "Name is required.";

            header("Location: delivery_profile.php");
            exit;
        }


        if (!preg_match("/^[a-zA-Z-' ]+$/", $name))
        {
            $_SESSION["profile_error"] = "Enter a valid name.";

            header("Location: delivery_profile.php");
            exit;
        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $_SESSION["profile_error"] = "Enter a valid email address.";

            header("Location: delivery_profile.php");
            exit;
        }


        if (!preg_match("/^01[3-9][0-9]{8}$/", $phone))
        {
            $_SESSION["profile_error"] = "Enter a valid phone number.";

            header("Location: delivery_profile.php");
            exit;
        }


        $vehicles = ["Bicycle", "Motorcycle", "Scooter"];


        if (!in_array($vehicleType, $vehicles, true))
        {
            $_SESSION["profile_error"] = "Select a valid vehicle type.";

            header("Location: delivery_profile.php");
            exit;
        }


        if ($areaId <= 0)
        {
            $_SESSION["profile_error"] = "Please select an area.";

            header("Location: delivery_profile.php");
            exit;
        }


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Area_ID AS area_id
            FROM Area
            WHERE Area_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $areaId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $areaExists = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$areaExists)
        {
            $_SESSION["profile_error"] = "Selected area does not exist.";

            header("Location: delivery_profile.php");
            exit;
        }


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Deliveryman_ID AS deliveryman_id
            FROM Deliveryman
            WHERE Email = ?
            AND Deliveryman_ID != ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $email, $deliverymanId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $emailExists = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if ($emailExists)
        {
            $_SESSION["profile_error"] = "Email already exists.";

            header("Location: delivery_profile.php");
            exit;
        }


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Area_ID AS area_id
            FROM Deliveryman
            WHERE Deliveryman_ID = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $currentDeliveryman = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);


        if (!$currentDeliveryman)
        {
            $_SESSION["profile_error"] = "Deliveryman not found.";

            header("Location: delivery_profile.php");
            exit;
        }


        if ($currentDeliveryman["area_id"] != $areaId)
        {
            $stmt = mysqli_prepare(
                $conn,
                "SELECT COUNT(*) AS active_orders
                FROM Delivery d
                JOIN `Order` o ON d.Order_ID = o.Order_ID
                WHERE d.Deliveryman_ID = ?
                AND o.Order_Status IN ('Ready', 'On The Way')"
            );

            mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $active = mysqli_fetch_assoc($result);

            mysqli_stmt_close($stmt);


            if ($active["active_orders"] > 0)
            {
                $_SESSION["profile_error"] = "You cannot change area while you have an active delivery.";

                header("Location: delivery_profile.php");
                exit;
            }
        }


        $stmt = mysqli_prepare(
            $conn,
            "UPDATE Deliveryman
            SET Name = ?, Email = ?, Phone_Number = ?,
            Vehicle_Type = ?, Area_ID = ?
            WHERE Deliveryman_ID = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ssssii",
            $name,
            $email,
            $phone,
            $vehicleType,
            $areaId,
            $deliverymanId
        );


        if (mysqli_stmt_execute($stmt))
        {
            mysqli_stmt_close($stmt);

            $_SESSION["profile_success"] = "Profile updated successfully.";

            header("Location: delivery_profile.php");
            exit;
        }


        mysqli_stmt_close($stmt);

        $_SESSION["profile_error"] = "Failed to update profile.";

        header("Location: delivery_profile.php");
        exit;
    }


    sendJson(false, "Invalid action.");

}


if ($currentPage == "delivery_dashboard.php")
{
    $totalDeliveries = 0;
    $completedDeliveries = 0;
    $activeDeliveries = 0;
    $totalEarnings = 0;

    $activeOrder = null;


    $stmt = mysqli_prepare(
        $conn,
        "SELECT d.Deliveryman_ID AS deliveryman_id,
        d.Name AS name,
        d.Phone_Number AS phone_number,
        d.Email AS email,
        d.Vehicle_Type AS vehicle_type,
        d.Online_Status AS online_status,
        d.Availability_Status AS availability_status,
        d.Area_ID AS area_id,
        a.Area_Name AS area_name
        FROM Deliveryman d
        JOIN Area a ON d.Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $deliveryman = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$deliveryman)
    {
        die("Deliveryman not found.");
    }


    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(d.Delivery_ID) AS total_deliveries,
        SUM(CASE WHEN o.Order_Status = 'Delivered' THEN 1 ELSE 0 END) AS completed_deliveries,
        SUM(CASE WHEN o.Order_Status IN ('Ready', 'On The Way') THEN 1 ELSE 0 END) AS active_deliveries,
        SUM(CASE WHEN o.Order_Status = 'Delivered' THEN o.Delivery_Fee ELSE 0 END) AS total_earnings
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        WHERE d.Deliveryman_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $stats = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($stats)
    {
        $totalDeliveries = $stats["total_deliveries"] ?? 0;
        $completedDeliveries = $stats["completed_deliveries"] ?? 0;
        $activeDeliveries = $stats["active_deliveries"] ?? 0;
        $totalEarnings = $stats["total_earnings"] ?? 0;
    }


    $stmt = mysqli_prepare(
        $conn,
        "SELECT d.Delivery_ID AS delivery_id,
        d.Delivery_Status AS delivery_status,
        o.Order_ID AS order_id,
        o.Food_Subtotal AS food_subtotal,
        o.Delivery_Fee AS delivery_fee,
        o.Total_Amount AS total_amount,
        o.Payment_Method AS payment_method,
        o.Payment_Status AS payment_status,
        o.Order_Status AS order_status,
        r.Name AS restaurant_name,
        c.Name AS customer_name,
        a.Area_Name AS area_name
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        JOIN Restaurant r ON o.Restaurant_ID = r.Restaurant_ID
        JOIN Customer c ON o.Customer_ID = c.Customer_ID
        JOIN Area a ON o.Delivery_Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status IN ('Ready', 'On The Way')
        ORDER BY d.Delivery_ID ASC
        LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $activeOrder = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}


else if ($currentPage == "delivery_orders.php")
{
    $assignedOrder = null;


    $stmt = mysqli_prepare(
        $conn,
        "SELECT d.Delivery_ID AS delivery_id,
        d.Delivery_Status AS delivery_status,
        o.Order_ID AS order_id,
        o.Total_Amount AS total_amount,
        o.Food_Subtotal AS food_subtotal,
        o.Delivery_Fee AS delivery_fee,
        o.Payment_Method AS payment_method,
        o.Payment_Status AS payment_status,
        o.Order_Status AS order_status,
        r.Name AS restaurant_name,
        c.Name AS customer_name,
        a.Area_Name AS area_name,
        (
            SELECT COALESCE(SUM(oi.Quantity), 0)
            FROM Order_Item oi
            WHERE oi.Order_ID = o.Order_ID
        ) AS item_count
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        JOIN Restaurant r ON o.Restaurant_ID = r.Restaurant_ID
        JOIN Customer c ON o.Customer_ID = c.Customer_ID
        JOIN Area a ON o.Delivery_Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status IN ('Ready', 'On The Way')
        ORDER BY d.Delivery_ID ASC
        LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $assignedOrder = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
}


else if ($currentPage == "delivery_current.php")
{
    $currentDelivery = null;
    $orderItems = false;


    $stmt = mysqli_prepare(
        $conn,
        "SELECT d.Delivery_ID AS delivery_id,
        d.Delivery_Status AS delivery_status,
        o.Order_ID AS order_id,
        o.Customer_ID AS customer_id,
        o.Food_Subtotal AS food_subtotal,
        o.Delivery_Fee AS delivery_fee,
        o.Total_Amount AS total_amount,
        o.Payment_Method AS payment_method,
        o.Payment_Status AS payment_status,
        o.Order_Status AS order_status,
        r.Name AS restaurant_name,
        r.Phone_Number AS restaurant_phone,
        c.Name AS customer_name,
        c.Phone_Number AS customer_phone,
        a.Area_Name AS area_name
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        JOIN Restaurant r ON o.Restaurant_ID = r.Restaurant_ID
        JOIN Customer c ON o.Customer_ID = c.Customer_ID
        JOIN Area a ON o.Delivery_Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status IN ('Ready', 'On The Way')
        ORDER BY d.Delivery_ID ASC
        LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $currentDelivery = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($currentDelivery)
    {
        $orderId = (int) $currentDelivery["order_id"];


        $stmt = mysqli_prepare(
            $conn,
            "SELECT Food_Name_At_Purchase AS food_name_at_purchase,
            Price_At_Purchase AS price_at_purchase,
            Quantity AS quantity,
            Customization AS customization
            FROM Order_Item
            WHERE Order_ID = ?
            ORDER BY Order_Item_ID ASC"
        );

        mysqli_stmt_bind_param($stmt, "i", $orderId);
        mysqli_stmt_execute($stmt);

        $orderItems = mysqli_stmt_get_result($stmt);

        mysqli_stmt_close($stmt);
    }
}


else if ($currentPage == "delivery_history.php")
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT o.Order_ID AS order_id,
        o.Order_Date AS order_date,
        o.Delivery_Fee AS delivery_fee,
        r.Name AS restaurant_name,
        c.Name AS customer_name,
        a.Area_Name AS area_name,
        d.Delivery_Status AS delivery_status
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        JOIN Restaurant r ON o.Restaurant_ID = r.Restaurant_ID
        JOIN Customer c ON o.Customer_ID = c.Customer_ID
        JOIN Area a ON o.Delivery_Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status = 'Delivered'
        ORDER BY o.Order_ID DESC"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $deliveryHistory = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
}


else if ($currentPage == "delivery_earnings.php")
{
    $totalEarnings = 0;
    $completedDeliveries = 0;
    $averageDeliveryFee = 0;
    $latestDeliveryFee = 0;


    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS completed_deliveries,
        COALESCE(SUM(o.Delivery_Fee), 0) AS total_earnings,
        COALESCE(AVG(o.Delivery_Fee), 0) AS average_delivery_fee
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status = 'Delivered'"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $earnings = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($earnings)
    {
        $completedDeliveries = $earnings["completed_deliveries"] ?? 0;
        $totalEarnings = $earnings["total_earnings"] ?? 0;
        $averageDeliveryFee = $earnings["average_delivery_fee"] ?? 0;
    }


    $stmt = mysqli_prepare(
        $conn,
        "SELECT o.Delivery_Fee AS delivery_fee
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status = 'Delivered'
        ORDER BY o.Order_ID DESC
        LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $latest = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($latest)
    {
        $latestDeliveryFee = $latest["delivery_fee"];
    }


    $stmt = mysqli_prepare(
        $conn,
        "SELECT o.Order_ID AS order_id,
        o.Order_Date AS order_date,
        o.Delivery_Fee AS delivery_fee,
        r.Name AS restaurant_name
        FROM Delivery d
        JOIN `Order` o ON d.Order_ID = o.Order_ID
        JOIN Restaurant r ON o.Restaurant_ID = r.Restaurant_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status = 'Delivered'
        ORDER BY o.Order_ID DESC"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $earningHistory = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
}


else if ($currentPage == "delivery_profile.php")
{
    $stmt = mysqli_prepare(
        $conn,
        "SELECT d.Deliveryman_ID AS deliveryman_id,
        d.Name AS name,
        d.Phone_Number AS phone_number,
        d.Email AS email,
        d.Vehicle_Type AS vehicle_type,
        d.Area_ID AS area_id,
        d.Online_Status AS online_status,
        d.Availability_Status AS availability_status,
        a.Area_Name AS area_name
        FROM Deliveryman d
        JOIN Area a ON d.Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $deliveryman = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$deliveryman)
    {
        die("Deliveryman not found.");
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
}


else if ($currentPage == "delivery_process.php")
{
    header("Location: delivery_dashboard.php");
    exit;
}

?>