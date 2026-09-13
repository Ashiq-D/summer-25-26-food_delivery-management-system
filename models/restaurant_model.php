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
        Availability_Status AS availability_status,
        Profile_Image_Path AS profile_image
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

function updateRestaurantProfile($restaurantId, $name, $email, $username)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Restaurant SET Name = ?, Email = ?, Username = ? WHERE Restaurant_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $username, $restaurantId);
    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
}

function restaurantUsernameExistsForOther($username, $restaurantId)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "SELECT Restaurant_ID FROM Restaurant WHERE Username = ? AND Restaurant_ID != ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $username, $restaurantId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $exists = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);

    return $exists;
}

function updateRestaurantProfileImage($restaurantId, $imagePath)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Restaurant SET Profile_Image_Path = ? WHERE Restaurant_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "si", $imagePath, $restaurantId);
    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $ok;
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
        Availability_Status AS availability_status,
        Image_Path AS image_path
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

function addMenuItemForRestaurant($restaurantId, $name, $description, $price, $category, $imagePath = null)
{
    global $conn;

    $availabilityStatus = "Available";

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO Food_Item
        (Restaurant_ID, Name, Description, Price, Category, Availability_Status, Image_Path)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issdsss",
        $restaurantId,
        $name,
        $description,
        $price,
        $category,
        $availabilityStatus,
        $imagePath
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}

function updateMenuItemImage($foodId, $restaurantId, $imagePath)
{
    global $conn;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE Food_Item
        SET Image_Path = ?
        WHERE Food_ID = ?
        AND Restaurant_ID = ?"
    );

    mysqli_stmt_bind_param($stmt, "sii", $imagePath, $foodId, $restaurantId);
    mysqli_stmt_execute($stmt);

    $affectedRows = mysqli_stmt_affected_rows($conn);

    mysqli_stmt_close($stmt);

    return $affectedRows > 0;
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

function getAllRestaurantsForCustomer()
{
    global $conn;

    $query = "SELECT r.Restaurant_ID AS restaurant_id,
              r.Name AS name,
              r.Phone_Number AS phone_number,
              r.Email AS email,
              r.Username AS username,
              r.Area_ID AS area_id,
              r.Availability_Status AS availability_status,
              r.Profile_Image_Path AS profile_image,
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
