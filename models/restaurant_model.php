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
?>
