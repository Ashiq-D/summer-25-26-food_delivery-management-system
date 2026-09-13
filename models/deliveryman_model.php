<?php

require_once __DIR__ . "/../config/config.php";


function getDeliverymanById($deliverymanId)
{
    global $conn;

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
        d.Profile_Image_Path AS profile_image,
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

    return $deliveryman;
}


function getDeliverymanStats($deliverymanId)
{
    global $conn;

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

    return $stats;
}


function getActiveOrderSummary($deliverymanId)
{
    global $conn;

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
        LEFT JOIN Customer c ON o.Customer_ID = c.Customer_ID
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

    return $activeOrder;
}


function getAssignedOrder($deliverymanId)
{
    global $conn;

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
        LEFT JOIN Customer c ON o.Customer_ID = c.Customer_ID
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

    return $assignedOrder;
}


function getCurrentDelivery($deliverymanId)
{
    global $conn;

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
        LEFT JOIN Customer c ON o.Customer_ID = c.Customer_ID
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

    return $currentDelivery;
}


function getOrderItems($orderId)
{
    global $conn;

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

    return $orderItems;
}


function getDeliveryHistory($deliverymanId)
{
    global $conn;

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
        LEFT JOIN Customer c ON o.Customer_ID = c.Customer_ID
        JOIN Area a ON o.Delivery_Area_ID = a.Area_ID
        WHERE d.Deliveryman_ID = ?
        AND o.Order_Status = 'Delivered'
        ORDER BY o.Order_ID DESC"
    );

    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    mysqli_stmt_execute($stmt);

    $deliveryHistory = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);

    return $deliveryHistory;
}


function getEarningsSummary($deliverymanId)
{
    global $conn;

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

    return $earnings;
}


function getLatestDeliveryFee($deliverymanId)
{
    global $conn;

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
        return $latest["delivery_fee"];
    }

    return 0;
}


function getEarningHistory($deliverymanId)
{
    global $conn;

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

    return $earningHistory;
}


function getDeliverymanAreas()
{
    global $conn;

    $areas = mysqli_query(
        $conn,
        "SELECT Area_ID AS area_id,
        Area_Name AS area_name
        FROM Area
        ORDER BY Area_Name ASC"
    );

    return $areas;
}


function deliverymanAreaExists($areaId)
{
    global $conn;

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

    return (bool) $areaExists;
}


function deliverymanEmailExistsForOther($email, $deliverymanId)
{
    global $conn;

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

    return (bool) $emailExists;
}


function getDeliverymanCurrentAreaId($deliverymanId)
{
    global $conn;

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

    if ($currentDeliveryman)
    {
        return (int) $currentDeliveryman["area_id"];
    }

    return null;
}


function deliverymanHasActiveOrders($deliverymanId)
{
    global $conn;

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

    return ($active["active_orders"] > 0);
}


function deleteDeliverymanAccount($deliverymanId)
{
    global $conn;

    $deliverymanId = (int) $deliverymanId;

    $stmt = mysqli_prepare($conn, "DELETE FROM Deliveryman WHERE Deliveryman_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function updateDeliverymanProfile($deliverymanId, $name, $email, $phone, $vehicleType, $areaId)
{
    global $conn;

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

    $updated = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $updated;
}


function updateDeliverymanProfileImage($deliverymanId, $imagePath)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Deliveryman SET Profile_Image_Path = ? WHERE Deliveryman_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $imagePath, $deliverymanId);
    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}


function updateOnlineStatus($deliverymanId, $status)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Deliveryman
        SET Online_Status = ?
        WHERE Deliveryman_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $status, $deliverymanId);

    $updated = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $updated;
}


function getReadyDeliveryForStart($deliverymanId)
{
    global $conn;

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

    return $delivery;
}


function startDeliveryTransaction($deliverymanId, $deliveryId, $orderId)
{
    global $conn;

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

        return true;
    }

    mysqli_rollback($conn);

    return false;
}


function getOnTheWayDeliveryForCompletion($deliverymanId)
{
    global $conn;

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

    return $delivery;
}


function completeDeliveryTransaction($deliverymanId, $deliveryId, $orderId, $paymentMethod)
{
    global $conn;

    mysqli_begin_transaction($conn);

    if ($paymentMethod == "Cash on Delivery")
    {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE `Order`
            SET Order_Status = 'Delivered',
            Payment_Status = 'Paid'
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

        return true;
    }

    mysqli_rollback($conn);

    return false;
}


// =========================================================
// PICKUP / CLAIM FLOW
//     A Deliveryman browses Prepared orders and claims one
//     themselves (self-service). Claiming creates the Delivery
//     record and moves the Order from 'Prepared' to 'Ready'.
//     From there, the existing "Confirm Pickup & Start Delivery"
//     flow (startDeliveryTransaction) takes it to 'On The Way'.
//
//     Reach is based on Vehicle_Type, matching the rule described
//     on the admin Areas page:
//       Bicycle -> own Area only
//       Bike    -> own Area + directly adjacent Areas
//       Car     -> own Area + adjacent + two-away Areas
// =========================================================

function getReachableAreaIds($areaId, $vehicleType)
{
    global $conn;

    $areaId = (int) $areaId;
    $areaIds = [$areaId];

    if ($vehicleType == "Bicycle")
    {
        return $areaIds;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Area_ID_2 AS area_id
        FROM Area_Adjacency
        WHERE Area_ID_1 = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $areaId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $adjacentIds = [];

    while ($row = mysqli_fetch_assoc($result))
    {
        $adjacentIds[] = (int) $row["area_id"];
    }

    mysqli_stmt_close($stmt);

    $areaIds = array_merge($areaIds, $adjacentIds);

    if ($vehicleType == "Bike")
    {
        return array_values(array_unique($areaIds));
    }

    // Car: also include areas adjacent to the adjacent areas ("two-away")
    $twoAwayIds = [];

    foreach ($adjacentIds as $adjId)
    {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT Area_ID_2 AS area_id
            FROM Area_Adjacency
            WHERE Area_ID_1 = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $adjId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result))
        {
            $twoAwayIds[] = (int) $row["area_id"];
        }

        mysqli_stmt_close($stmt);
    }

    $areaIds = array_merge($areaIds, $twoAwayIds);

    return array_values(array_unique($areaIds));
}


function getAvailableOrdersForDeliveryman($deliverymanId)
{
    global $conn;

    $deliverymanId = (int) $deliverymanId;

    $deliveryman = getDeliverymanById($deliverymanId);

    if (!$deliveryman)
    {
        return [];
    }

    $areaIds = getReachableAreaIds($deliveryman["area_id"], $deliveryman["vehicle_type"]);

    if (empty($areaIds))
    {
        return [];
    }

    $placeholders = implode(",", array_fill(0, count($areaIds), "?"));
    $types = str_repeat("i", count($areaIds));

    $sql = "SELECT o.Order_ID AS order_id,
            o.Total_Amount AS total_amount,
            o.Delivery_Fee AS delivery_fee,
            o.Payment_Method AS payment_method,
            r.Name AS restaurant_name,
            a.Area_Name AS area_name,
            (
                SELECT COALESCE(SUM(oi.Quantity), 0)
                FROM Order_Item oi
                WHERE oi.Order_ID = o.Order_ID
            ) AS item_count
            FROM `Order` o
            JOIN Restaurant r ON o.Restaurant_ID = r.Restaurant_ID
            JOIN Area a ON o.Delivery_Area_ID = a.Area_ID
            LEFT JOIN Delivery d ON d.Order_ID = o.Order_ID
            WHERE o.Order_Status = 'Prepared'
            AND d.Delivery_ID IS NULL
            AND o.Delivery_Area_ID IN ($placeholders)
            ORDER BY o.Order_Date ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt)
    {
        return [];
    }

    mysqli_stmt_bind_param($stmt, $types, ...$areaIds);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $orders = [];

    while ($row = mysqli_fetch_assoc($result))
    {
        $orders[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $orders;
}


function claimOrderForDeliveryman($deliverymanId, $orderId)
{
    global $conn;

    $deliverymanId = (int) $deliverymanId;
    $orderId       = (int) $orderId;

    if ($deliverymanId <= 0 || $orderId <= 0)
    {
        return false;
    }

    if (deliverymanHasActiveOrders($deliverymanId))
    {
        // Already has an order in progress - can't claim a second one.
        return false;
    }

    $deliveryman = getDeliverymanById($deliverymanId);

    if (!$deliveryman || $deliveryman["availability_status"] != "Available")
    {
        return false;
    }

    $areaIds = getReachableAreaIds($deliveryman["area_id"], $deliveryman["vehicle_type"]);

    mysqli_begin_transaction($conn);

    // Lock and re-check the order is still Prepared, unclaimed, and reachable.
    $stmt = mysqli_prepare(
        $conn,
        "SELECT o.Order_ID
        FROM `Order` o
        LEFT JOIN Delivery d ON d.Order_ID = o.Order_ID
        WHERE o.Order_ID = ?
        AND o.Order_Status = 'Prepared'
        AND d.Delivery_ID IS NULL
        FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order  = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$order)
    {
        mysqli_rollback($conn);

        return false;
    }

    // Re-fetch the order's Area to confirm it's within this deliveryman's reach.
    $areaStmt = mysqli_prepare(
        $conn,
        "SELECT Delivery_Area_ID FROM `Order` WHERE Order_ID = ?"
    );

    mysqli_stmt_bind_param($areaStmt, "i", $orderId);
    mysqli_stmt_execute($areaStmt);
    $areaResult = mysqli_stmt_get_result($areaStmt);
    $areaRow    = mysqli_fetch_assoc($areaResult);
    mysqli_stmt_close($areaStmt);

    if (!$areaRow || !in_array((int) $areaRow["Delivery_Area_ID"], $areaIds, true))
    {
        mysqli_rollback($conn);

        return false;
    }

    $insertStmt = mysqli_prepare(
        $conn,
        "INSERT INTO Delivery (Order_ID, Deliveryman_ID, Delivery_Status)
        VALUES (?, ?, 'Preparing')"
    );

    mysqli_stmt_bind_param($insertStmt, "ii", $orderId, $deliverymanId);
    $inserted = mysqli_stmt_execute($insertStmt);
    mysqli_stmt_close($insertStmt);

    if (!$inserted)
    {
        mysqli_rollback($conn);

        return false;
    }

    $updateOrderStmt = mysqli_prepare(
        $conn,
        "UPDATE `Order` SET Order_Status = 'Ready' WHERE Order_ID = ? AND Order_Status = 'Prepared'"
    );

    mysqli_stmt_bind_param($updateOrderStmt, "i", $orderId);
    $orderUpdated = mysqli_stmt_execute($updateOrderStmt);
    mysqli_stmt_close($updateOrderStmt);

    if (!$orderUpdated)
    {
        mysqli_rollback($conn);

        return false;
    }

    $updateDeliverymanStmt = mysqli_prepare(
        $conn,
        "UPDATE Deliveryman SET Availability_Status = 'Busy' WHERE Deliveryman_ID = ?"
    );

    mysqli_stmt_bind_param($updateDeliverymanStmt, "i", $deliverymanId);
    $deliverymanUpdated = mysqli_stmt_execute($updateDeliverymanStmt);
    mysqli_stmt_close($updateDeliverymanStmt);

    if (!$deliverymanUpdated)
    {
        mysqli_rollback($conn);

        return false;
    }

    mysqli_commit($conn);

    return true;
}
