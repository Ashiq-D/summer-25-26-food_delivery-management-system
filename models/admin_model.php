<?php

require_once __DIR__ . "/../config/config.php";

function getAdminById($adminId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Admin_ID AS admin_id,
        Name AS name,
        Email AS email,
        Username AS username,
        Profile_Image_Path AS profile_image
        FROM Admin
        WHERE Admin_ID = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $adminId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $admin = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $admin;
}


function countAdmins()
{
    global $conn;

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Admin");

    $row = mysqli_fetch_assoc($result);

    return (int) $row["total"];
}


function adminEmailExistsForOther($email, $adminId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Admin_ID FROM Admin WHERE Email = ? AND Admin_ID != ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $email, $adminId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $exists = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);

    return $exists;
}


function adminUsernameExistsForOther($username, $adminId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Admin_ID FROM Admin WHERE Username = ? AND Admin_ID != ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $username, $adminId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $exists = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);

    return $exists;
}


function updateAdminProfile($adminId, $name, $email, $username)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Admin SET Name = ?, Email = ?, Username = ? WHERE Admin_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $username, $adminId);
    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}


function updateAdminProfileImage($adminId, $imagePath)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Admin SET Profile_Image_Path = ? WHERE Admin_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $imagePath, $adminId);
    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}


/**
 * An Admin may only ever delete their OWN account (never another Admin's),
 * and CRAVERUSH must always retain at least one Admin account.
 *
 * Returns:
 *   "deleted"    - account removed successfully
 *   "last_admin" - blocked: this is the only remaining Admin
 *   "not_self"   - blocked: the target account does not belong to the requester
 */
function deleteOwnAdminAccount($requestingAdminId, $targetAdminId)
{
    global $conn;

    $requestingAdminId = (int) $requestingAdminId;
    $targetAdminId = (int) $targetAdminId;

    if ($requestingAdminId !== $targetAdminId)
    {
        return "not_self";
    }

    if (countAdmins() <= 1)
    {
        return "last_admin";
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM Admin WHERE Admin_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $targetAdminId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return "deleted";
}


function getDashboardStats()
{
    global $conn;

    $stats = [];


    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Customer");
    $stats["total_customers"] = (int) mysqli_fetch_assoc($result)["total"];

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Restaurant");
    $stats["total_restaurants"] = (int) mysqli_fetch_assoc($result)["total"];

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Deliveryman");
    $stats["total_deliverymen"] = (int) mysqli_fetch_assoc($result)["total"];

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `Order`");
    $stats["total_orders"] = (int) mysqli_fetch_assoc($result)["total"];


    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM `Order`
        WHERE Order_Status NOT IN ('Delivered', 'Cancelled')"
    );
    $stats["active_orders"] = (int) mysqli_fetch_assoc($result)["total"];


    $result = mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(Total_Amount), 0) AS total
        FROM `Order`
        WHERE DATE(Order_Date) = CURDATE()
        AND Order_Status != 'Cancelled'"
    );
    $stats["today_order_value"] = (float) mysqli_fetch_assoc($result)["total"];

    $result = mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(Food_Subtotal), 0) AS total
        FROM `Order`
        WHERE DATE(Order_Date) = CURDATE()
        AND Order_Status = 'Delivered'"
    );
    $stats["today_craverush_revenue"] = round(((float) mysqli_fetch_assoc($result)["total"]) * 0.10, 2);


    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM Area_Change_Request WHERE Request_Status = 'Pending'"
    );
    $stats["pending_area_requests"] = (int) mysqli_fetch_assoc($result)["total"];


    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM Cancellation_Request WHERE Status = 'Pending'"
    );
    $stats["pending_cancellations"] = (int) mysqli_fetch_assoc($result)["total"];


    return $stats;
}


function getRecentOrders($limit = 5)
{
    global $conn;

    $limit = (int) $limit;

    $result = mysqli_query(
        $conn,
        "SELECT o.Order_ID AS order_id,
        o.Order_Date AS order_date,
        o.Total_Amount AS total_amount,
        o.Order_Status AS order_status,
        r.Name AS restaurant_name,
        a.Area_Name AS area_name
        FROM `Order` o
        JOIN Restaurant r ON r.Restaurant_ID = o.Restaurant_ID
        JOIN Area a ON a.Area_ID = o.Delivery_Area_ID
        ORDER BY o.Order_Date DESC
        LIMIT $limit"
    );

    $orders = [];

    while ($row = mysqli_fetch_assoc($result))
    {
        $orders[] = $row;
    }

    return $orders;
}


/* =========================================================
   USERS: Customer / Restaurant / Deliveryman lists + delete
   ========================================================= */

function getAllCustomers()
{
    global $conn;

    $result = mysqli_query(
        $conn,
        "SELECT c.Customer_ID AS customer_id,
        c.Name AS name,
        c.Phone_Number AS phone_number,
        c.Email AS email,
        a.Area_Name AS area_name
        FROM Customer c
        JOIN Area a ON a.Area_ID = c.Area_ID
        ORDER BY c.Customer_ID ASC"
    );

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function getAllRestaurants()
{
    global $conn;

    $result = mysqli_query(
        $conn,
        "SELECT r.Restaurant_ID AS restaurant_id,
        r.Name AS name,
        r.Phone_Number AS phone_number,
        r.Email AS email,
        r.Username AS username,
        r.Availability_Status AS availability_status,
        a.Area_Name AS area_name
        FROM Restaurant r
        JOIN Area a ON a.Area_ID = r.Area_ID
        ORDER BY r.Restaurant_ID ASC"
    );

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function getAllDeliverymen()
{
    global $conn;

    $result = mysqli_query(
        $conn,
        "SELECT d.Deliveryman_ID AS deliveryman_id,
        d.Name AS name,
        d.Phone_Number AS phone_number,
        d.Email AS email,
        d.Vehicle_Type AS vehicle_type,
        d.Online_Status AS online_status,
        d.Availability_Status AS availability_status,
        a.Area_Name AS area_name
        FROM Deliveryman d
        JOIN Area a ON a.Area_ID = d.Area_ID
        ORDER BY d.Deliveryman_ID ASC"
    );

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function deliverymanHasActiveOrder($deliverymanId)
{
    global $conn;

    $deliverymanId = (int) $deliverymanId;

    $result = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM Delivery del
        JOIN `Order` o ON o.Order_ID = del.Order_ID
        WHERE del.Deliveryman_ID = $deliverymanId
        AND o.Order_Status NOT IN ('Delivered', 'Cancelled')"
    );

    $row = mysqli_fetch_assoc($result);

    return ((int) $row["total"]) > 0;
}


/* =========================================================
   FOOD ITEMS (Admin CRUD, per Section 2.2 of the case study)
   ========================================================= */

function getAllFoodItems()
{
    global $conn;

    $result = mysqli_query(
        $conn,
        "SELECT f.Food_ID AS food_id,
        f.Name AS name,
        f.Description AS description,
        f.Price AS price,
        f.Category AS category,
        f.Availability_Status AS availability_status,
        r.Restaurant_ID AS restaurant_id,
        r.Name AS restaurant_name
        FROM Food_Item f
        JOIN Restaurant r ON r.Restaurant_ID = f.Restaurant_ID
        ORDER BY f.Food_ID ASC"
    );

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function insertFoodItem($restaurantId, $name, $description, $price, $category, $availabilityStatus)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO Food_Item
        (Restaurant_ID, Name, Description, Price, Category, Availability_Status)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issdss",
        $restaurantId,
        $name,
        $description,
        $price,
        $category,
        $availabilityStatus
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}


function deleteFoodItem($foodId)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "DELETE FROM Food_Item WHERE Food_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $foodId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function deleteCustomer($customerId)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "DELETE FROM Customer WHERE Customer_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $customerId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function deleteRestaurant($restaurantId)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "DELETE FROM Restaurant WHERE Restaurant_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $restaurantId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function deleteDeliveryman($deliverymanId)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "DELETE FROM Deliveryman WHERE Deliveryman_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $deliverymanId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


/* =========================================================
   ORDERS & DELIVERIES
   ========================================================= */

function getAllOrders($statusFilter = null)
{
    global $conn;

    $sql = "SELECT o.Order_ID AS order_id,
        o.Order_Date AS order_date,
        o.Total_Amount AS total_amount,
        o.Order_Status AS order_status,
        o.Note AS note,
        c.Name AS customer_name,
        r.Name AS restaurant_name,
        a.Area_Name AS area_name,
        dm.Name AS deliveryman_name
        FROM `Order` o
        LEFT JOIN Customer c ON c.Customer_ID = o.Customer_ID
        JOIN Restaurant r ON r.Restaurant_ID = o.Restaurant_ID
        JOIN Area a ON a.Area_ID = o.Delivery_Area_ID
        LEFT JOIN Delivery del ON del.Order_ID = o.Order_ID
        LEFT JOIN Deliveryman dm ON dm.Deliveryman_ID = del.Deliveryman_ID";

    if ($statusFilter !== null && $statusFilter !== "" && $statusFilter !== "All")
    {
        $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
        $sql .= " WHERE o.Order_Status = '$statusFilter'";
    }

    $sql .= " ORDER BY o.Order_Date DESC";

    $result = mysqli_query($conn, $sql);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function deleteOrder($orderId)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "DELETE FROM `Order` WHERE Order_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


/* =========================================================
   AREA CHANGE REQUESTS & CANCELLATION REQUESTS
   ========================================================= */

function getAreaChangeRequests($statusFilter = "Pending")
{
    global $conn;

    $sql = "SELECT acr.Request_ID AS request_id,
        r.Name AS restaurant_name,
        cur.Area_Name AS current_area_name,
        req.Area_Name AS requested_area_name,
        acr.Request_Status AS request_status
        FROM Area_Change_Request acr
        JOIN Restaurant r ON r.Restaurant_ID = acr.Restaurant_ID
        JOIN Area cur ON cur.Area_ID = acr.Current_Area_ID
        JOIN Area req ON req.Area_ID = acr.Requested_Area_ID";

    if ($statusFilter !== null && $statusFilter !== "" && $statusFilter !== "All")
    {
        $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
        $sql .= " WHERE acr.Request_Status = '$statusFilter'";
    }

    $sql .= " ORDER BY acr.Request_ID DESC";

    $result = mysqli_query($conn, $sql);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function approveAreaChangeRequest($requestId)
{
    global $conn;

    $requestId = (int) $requestId;

    $result = mysqli_query(
        $conn,
        "SELECT Restaurant_ID, Requested_Area_ID FROM Area_Change_Request WHERE Request_ID = $requestId"
    );
    $req = mysqli_fetch_assoc($result);

    if (!$req)
    {
        return false;
    }

    mysqli_query(
        $conn,
        "UPDATE Restaurant SET Area_ID = " . (int) $req["Requested_Area_ID"] . "
        WHERE Restaurant_ID = " . (int) $req["Restaurant_ID"]
    );

    return mysqli_query(
        $conn,
        "UPDATE Area_Change_Request SET Request_Status = 'Approved' WHERE Request_ID = $requestId"
    );
}


function rejectAreaChangeRequest($requestId)
{
    global $conn;

    $requestId = (int) $requestId;

    return mysqli_query(
        $conn,
        "UPDATE Area_Change_Request SET Request_Status = 'Rejected' WHERE Request_ID = $requestId"
    );
}


function getCancellationRequests($statusFilter = "Pending")
{
    global $conn;

    $sql = "SELECT cr.Cancellation_ID AS cancellation_id,
        cr.Order_ID AS order_id,
        c.Name AS customer_name,
        cr.Reason AS reason,
        cr.Status AS status
        FROM Cancellation_Request cr
        JOIN Customer c ON c.Customer_ID = cr.Customer_ID";

    if ($statusFilter !== null && $statusFilter !== "" && $statusFilter !== "All")
    {
        $statusFilter = mysqli_real_escape_string($conn, $statusFilter);
        $sql .= " WHERE cr.Status = '$statusFilter'";
    }

    $sql .= " ORDER BY cr.Cancellation_ID DESC";

    $result = mysqli_query($conn, $sql);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function approveCancellationRequest($cancellationId)
{
    global $conn;

    $cancellationId = (int) $cancellationId;

    $result = mysqli_query(
        $conn,
        "SELECT Order_ID FROM Cancellation_Request WHERE Cancellation_ID = $cancellationId"
    );
    $row = mysqli_fetch_assoc($result);

    if (!$row)
    {
        return false;
    }

    $orderId = (int) $row["Order_ID"];

    mysqli_query($conn, "UPDATE `Order` SET Order_Status = 'Cancelled' WHERE Order_ID = $orderId");
    mysqli_query($conn, "UPDATE Delivery SET Delivery_Status = 'Cancelled' WHERE Order_ID = $orderId");

    return mysqli_query(
        $conn,
        "UPDATE Cancellation_Request SET Status = 'Approved' WHERE Cancellation_ID = $cancellationId"
    );
}


function rejectCancellationRequest($cancellationId)
{
    global $conn;

    $cancellationId = (int) $cancellationId;

    return mysqli_query(
        $conn,
        "UPDATE Cancellation_Request SET Status = 'Rejected' WHERE Cancellation_ID = $cancellationId"
    );
}


function getSetting($key)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT Setting_Value FROM Settings WHERE Setting_Key = ?");
    mysqli_stmt_bind_param($stmt, "s", $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $row ? $row["Setting_Value"] : null;
}


function updateSetting($key, $value)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO Settings (Setting_Key, Setting_Value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE Setting_Value = VALUES(Setting_Value)"
    );
    mysqli_stmt_bind_param($stmt, "ss", $key, $value);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function getDeliveryFee()
{
    $value = getSetting("delivery_fee");

    return $value !== null ? (float) $value : 50.00;
}


function updateDeliveryFee($amount)
{
    return updateSetting("delivery_fee", (string) (float) $amount);
}


/* =========================================================
   REPORTS
   ========================================================= */

function getRevenueReport()
{
    global $conn;

    $report = [];

    $result = mysqli_query(
        $conn,
        "SELECT COALESCE(SUM(Food_Subtotal), 0) AS total_food_subtotal,
        COUNT(*) AS delivered_orders
        FROM `Order`
        WHERE Order_Status = 'Delivered'"
    );
    $row = mysqli_fetch_assoc($result);

    $report["total_food_subtotal"] = (float) $row["total_food_subtotal"];
    $report["delivered_orders"] = (int) $row["delivered_orders"];
    $report["total_revenue"] = round($report["total_food_subtotal"] * 0.10, 2);
    $report["total_profit"] = $report["total_revenue"];

    $result = mysqli_query(
        $conn,
        "SELECT DATE_FORMAT(Order_Date, '%Y-%m') AS month,
        COALESCE(SUM(Food_Subtotal), 0) AS food_subtotal,
        COUNT(*) AS delivered_orders
        FROM `Order`
        WHERE Order_Status = 'Delivered'
        GROUP BY DATE_FORMAT(Order_Date, '%Y-%m')
        ORDER BY month DESC"
    );

    $monthly = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $row["revenue"] = round(((float) $row["food_subtotal"]) * 0.10, 2);
        $monthly[] = $row;
    }
    $report["monthly"] = $monthly;

    return $report;
}


/* =========================================================
   AREAS & AREA ADJACENCY
   ========================================================= */

function addArea($areaName)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "INSERT INTO Area (Area_Name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $areaName);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function areaInUse($areaId)
{
    global $conn;

    $areaId = (int) $areaId;

    $result = mysqli_query(
        $conn,
        "SELECT
        (SELECT COUNT(*) FROM Customer WHERE Area_ID = $areaId) +
        (SELECT COUNT(*) FROM Restaurant WHERE Area_ID = $areaId) +
        (SELECT COUNT(*) FROM Deliveryman WHERE Area_ID = $areaId) AS total"
    );

    $row = mysqli_fetch_assoc($result);

    return ((int) $row["total"]) > 0;
}


function deleteArea($areaId)
{
    global $conn;

    if (areaInUse($areaId))
    {
        return false;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM Area WHERE Area_ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $areaId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function getAreaAdjacencies()
{
    global $conn;

    $result = mysqli_query(
        $conn,
        "SELECT a1.Area_ID AS area_id_1,
        a1.Area_Name AS area_name_1,
        a2.Area_ID AS area_id_2,
        a2.Area_Name AS area_name_2
        FROM Area_Adjacency aa
        JOIN Area a1 ON a1.Area_ID = aa.Area_ID_1
        JOIN Area a2 ON a2.Area_ID = aa.Area_ID_2
        WHERE aa.Area_ID_1 < aa.Area_ID_2
        ORDER BY a1.Area_Name ASC, a2.Area_Name ASC"
    );

    $rows = [];
    while ($row = mysqli_fetch_assoc($result))
    {
        $rows[] = $row;
    }

    return $rows;
}


function addAreaAdjacency($areaId1, $areaId2)
{
    global $conn;

    $areaId1 = (int) $areaId1;
    $areaId2 = (int) $areaId2;

    if ($areaId1 === $areaId2)
    {
        return false;
    }

    $stmt = mysqli_prepare(
        $conn,
        "INSERT IGNORE INTO Area_Adjacency (Area_ID_1, Area_ID_2) VALUES (?, ?), (?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiii", $areaId1, $areaId2, $areaId2, $areaId1);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}


function removeAreaAdjacency($areaId1, $areaId2)
{
    global $conn;

    $areaId1 = (int) $areaId1;
    $areaId2 = (int) $areaId2;

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM Area_Adjacency
        WHERE (Area_ID_1 = ? AND Area_ID_2 = ?)
        OR (Area_ID_1 = ? AND Area_ID_2 = ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiii", $areaId1, $areaId2, $areaId2, $areaId1);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}
