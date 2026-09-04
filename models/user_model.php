<?php

require_once __DIR__ . "/../config/config.php";


function getAreas()
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


function areaExists($areaId)
{
    global $conn;

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

    $exists = mysqli_num_rows($areaResult) > 0;

    mysqli_stmt_close($areaCheck);

    return $exists;
}


function emailExists($email)
{
    global $conn;

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

    $exists = mysqli_num_rows($emailResult) > 0;

    mysqli_stmt_close($emailCheck);

    return $exists;
}


function restaurantUsernameExists($username)
{
    global $conn;

    $usernameCheck = mysqli_prepare(
        $conn,
        "SELECT Restaurant_ID
        FROM Restaurant
        WHERE Username = ?"
    );

    mysqli_stmt_bind_param(
        $usernameCheck,
        "s",
        $username
    );

    mysqli_stmt_execute($usernameCheck);

    $usernameResult = mysqli_stmt_get_result($usernameCheck);

    $exists = mysqli_num_rows($usernameResult) > 0;

    mysqli_stmt_close($usernameCheck);

    return $exists;
}


function insertCustomer($name, $phone, $email, $hashedPassword, $areaId)
{
    global $conn;

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

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}


function insertRestaurant($name, $phone, $email, $username, $hashedPassword, $areaId)
{
    global $conn;

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
        $username,
        $hashedPassword,
        $areaId,
        $availabilityStatus
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}


function getCustomerByEmail($email)
{
    global $conn;

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

    mysqli_stmt_close($stmt);

    return $user;
}


function getRestaurantByEmail($email)
{
    global $conn;

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

    mysqli_stmt_close($stmt);

    return $user;
}


function getDeliverymanByEmail($email)
{
    global $conn;

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

    mysqli_stmt_close($stmt);

    return $user;
}


function getAdminByEmail($email)
{
    global $conn;

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

    mysqli_stmt_close($stmt);

    return $user;
}


function insertDeliveryman($name, $phone, $email, $hashedPassword, $vehicleType, $areaId)
{
    global $conn;

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

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}