<?php

require_once __DIR__ . "/../config/config.php";

function getRestaurantById($restaurantId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Restaurant_ID AS restaurant_id,
        Name AS name,
        Phone_Number AS phone_number,
        Email AS email,
        Username AS username,
        Area_ID AS area_id,
        Availability_Status AS availability_status
        FROM Restaurant
        WHERE Restaurant_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $restaurantId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $restaurant = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $restaurant;
}

function getMenuItemsByRestaurant($restaurantId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Food_ID AS food_id,
        Name AS name,
        Description AS description,
        Price AS price,
        Category AS category,
        Availability_Status AS availability_status
        FROM Food_Item
        WHERE Restaurant_ID = ?
        ORDER BY Food_ID ASC"
    );

    mysqli_stmt_bind_param($stmt, "i", $restaurantId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $items = [];

    while ($row = mysqli_fetch_assoc($result))
    {
        $items[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $items;
}

function addMenuItemForRestaurant($restaurantId, $name, $description, $price, $category)
{
    global $conn;

    $availabilityStatus = "Available";

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

function toggleMenuItemAvailability($foodId, $restaurantId, $newStatus)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Food_Item
        SET Availability_Status = ?
        WHERE Food_ID = ?
        AND Restaurant_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "sii", $newStatus, $foodId, $restaurantId);
    mysqli_stmt_execute($stmt);

    $affectedRows = mysqli_stmt_affected_rows($conn);

    mysqli_stmt_close($stmt);

    return $affectedRows > 0;
}

function getAllRestaurants()
{
    global $conn;

    $query = "SELECT r.Restaurant_ID AS restaurant_id,
              r.Name AS name,
              r.Phone_Number AS phone_number,
              r.Email AS email,
              r.Username AS username,
              r.Area_ID AS area_id,
              r.Availability_Status AS availability_status,
              a.Area_Name AS area_name
              FROM Restaurant r
              LEFT JOIN Area a ON r.Area_ID = a.Area_ID";

    $result = mysqli_query($conn, $query);

    $restaurants = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $restaurants[] = $row;
        }
    }

    return $restaurants;
}


function getOrdersByRestaurant($restaurantId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT 
            o.Order_ID,
            o.Customer_ID,
            o.Order_Date,
            o.Food_Subtotal,
            o.Delivery_Fee,
            o.Total_Amount,
            o.Payment_Method,
            o.Payment_Status,
            o.Order_Status,
            c.Name AS Customer_Name
        FROM `Order` o
        LEFT JOIN Customer c ON o.Customer_ID = c.Customer_ID
        WHERE o.Restaurant_ID = ?
        ORDER BY o.Order_Date DESC"
    );

    mysqli_stmt_bind_param($stmt, "i", $restaurantId);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Fetch order items for this order
        $itemStmt = mysqli_prepare(
            $conn,
            "SELECT Food_Name_At_Purchase, Quantity, Customization
             FROM Order_Item
             WHERE Order_ID = ?"
        );
        mysqli_stmt_bind_param($itemStmt, "i", $row["Order_ID"]);
        mysqli_stmt_execute($itemStmt);
        $itemResult = mysqli_stmt_get_result($itemStmt);
        
        $items = [];
        while ($itemRow = mysqli_fetch_assoc($itemResult)) {
            $items[] = $itemRow;
        }
        mysqli_stmt_close($itemStmt);

        $row["items"] = $items;
        $orders[] = $row;
    }

    mysqli_stmt_close($stmt);

    return $orders;
}

function updateOrderStatus($orderId, $restaurantId, $newStatus)
{
    global $conn;

    // We only allow specific transitions for the Restaurant: Pending -> Preparing -> Prepared
    $allowedTransitions = [
        "Preparing" => ["Pending"],
        "Prepared" => ["Preparing"]
    ];

    if (!isset($allowedTransitions[$newStatus])) {
        return false;
    }

    // Check current status and ownership
    $stmt = mysqli_prepare(
        $conn,
        "SELECT Order_Status FROM `Order` WHERE Order_ID = ? AND Restaurant_ID = ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $orderId, $restaurantId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        return false; // Order not found or doesn't belong to this restaurant
    }

    if (!in_array($row["Order_Status"], $allowedTransitions[$newStatus])) {
        return false; // Invalid state transition
    }

    // Update status
    $updateStmt = mysqli_prepare(
        $conn,
        "UPDATE `Order` SET Order_Status = ? WHERE Order_ID = ? AND Restaurant_ID = ?"
    );
    mysqli_stmt_bind_param($updateStmt, "sii", $newStatus, $orderId, $restaurantId);
    $success = mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);

    return $success;
}
