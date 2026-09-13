<?php

/*
 * controllers/customer_ajax_controller.php
 *
 * Consolidated AJAX handler for the Customer module.
 */

session_start();

include_once __DIR__ . "/../config/database.php";
include_once __DIR__ . "/../models/restaurant_model.php";
include_once __DIR__ . "/../models/order_model.php";
include_once __DIR__ . "/../models/review_model.php";
include_once __DIR__ . "/../models/food_model.php";

header("Content-Type: application/json");

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_restaurants':
        handleGetRestaurants($conn);
        break;
    case 'search':
        handleSearch($conn);
        break;
    case 'create_order':
        handleCreateOrder($conn);
        break;
    case 'track_order':
        handleTrackOrder($conn);
        break;
    case 'submit_review':
        handleSubmitReview($conn);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action."]);
        exit;
}

function handleGetRestaurants($conn) {
    $area = trim($_GET["area"] ?? "");

    if ($area === "") {
        $query = "SELECT r.Restaurant_ID, r.Name, r.Area_ID, a.Area_Name
                  FROM Restaurant r
                  LEFT JOIN Area a ON r.Area_ID = a.Area_ID
                  ORDER BY r.Restaurant_ID";
        $result = mysqli_query($conn, $query);
        if (!$result) {
            echo json_encode([]);
            exit;
        }
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT r.Restaurant_ID, r.Name, r.Area_ID, a.Area_Name
             FROM Restaurant r
             INNER JOIN Area a ON r.Area_ID = a.Area_ID
             WHERE a.Area_Name = ?
             ORDER BY r.Restaurant_ID"
        );
        if (!$stmt) {
            echo json_encode([]);
            exit;
        }
        mysqli_stmt_bind_param($stmt, "s", $area);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    }

    $restaurants = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $imagePath = "../../assets/images/restaurants/restaurant" . $row["Restaurant_ID"] . ".jpg";
        $restaurants[] = [
            "id"       => (int)$row["Restaurant_ID"],
            "name"     => $row["Name"],
            "area_id"  => (int)$row["Area_ID"],
            "area"     => $row["Area_Name"] ?? "",
            "image"    => $imagePath,
        ];
    }
    echo json_encode($restaurants);
    exit;
}

function handleSearch($conn) {
    $search = trim($_GET["search"] ?? "");

    if ($search === "") {
        echo json_encode([]);
        exit;
    }

    $searchValue = "%" . $search . "%";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT DISTINCT
            r.Restaurant_ID,
            r.Name,
            r.Area_ID,
            a.Area_Name
         FROM Restaurant r
         LEFT JOIN Area a
            ON r.Area_ID = a.Area_ID
         LEFT JOIN Food_Item fi
            ON r.Restaurant_ID = fi.Restaurant_ID
         WHERE r.Name LIKE ?
            OR a.Area_Name LIKE ?
            OR fi.Name LIKE ?
         ORDER BY r.Name"
    );

    if (!$stmt) {
        echo json_encode([]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sss", $searchValue, $searchValue, $searchValue);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $restaurants = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $imagePath = "../../assets/images/restaurants/restaurant" . $row["Restaurant_ID"] . ".jpg";
        $restaurants[] = [
            "id"      => (int)$row["Restaurant_ID"],
            "name"    => $row["Name"],
            "area_id" => (int)$row["Area_ID"],
            "area"    => $row["Area_Name"] ?? "",
            "image"   => $imagePath,
        ];
    }
    mysqli_stmt_close($stmt);
    echo json_encode($restaurants);
    exit;
}

function handleCreateOrder($conn) {
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["success" => false, "message" => "Please login first."]);
        exit;
    }

    $rawInput = file_get_contents("php://input");
    if ($rawInput === false || $rawInput === "") {
        echo json_encode(["success" => false, "message" => "Invalid request."]);
        exit;
    }

    $data = json_decode($rawInput, true);
    if (!is_array($data)) {
        echo json_encode(["success" => false, "message" => "Invalid JSON."]);
        exit;
    }

    if (!isset($data["restaurantId"]) || !isset($data["paymentMethod"]) ||
        !isset($data["deliveryAddress"]) || !isset($data["items"])) {
        echo json_encode(["success" => false, "message" => "Missing required order information."]);
        exit;
    }

    $userId          = (int)$_SESSION["user_id"];
    $restaurantId    = (int)$data["restaurantId"];
    $paymentMethod   = trim($data["paymentMethod"]);
    $deliveryAddress = trim($data["deliveryAddress"]);
    $items           = $data["items"];

    if ($restaurantId <= 0 || $paymentMethod === "" || $deliveryAddress === "" || !is_array($items) || empty($items)) {
        echo json_encode(["success" => false, "message" => "Invalid order information."]);
        exit;
    }

    $allowedPayments = ["Cash on Delivery", "Online Payment"];
    if (!in_array($paymentMethod, $allowedPayments, true)) {
        echo json_encode(["success" => false, "message" => "Invalid payment method."]);
        exit;
    }

    $orderModel = new Order($conn);
    $orderId = $orderModel->createOrder($userId, $restaurantId, $paymentMethod, $deliveryAddress, $items);

    if ($orderId) {
        echo json_encode(["success" => true, "message" => "Order placed successfully.", "orderId" => (int)$orderId]);
    } else {
        echo json_encode(["success" => false, "message" => "Order could not be placed. Please check your items and delivery area."]);
    }
    exit;
}

function handleTrackOrder($conn) {
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["success" => false, "message" => "Please login first."]);
        exit;
    }

    $orderId = (int)($_GET["orderId"] ?? 0);
    if ($orderId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid order ID."]);
        exit;
    }

    $orderModel = new Order($conn);
    $orderData = $orderModel->getOrderById($orderId, (int)$_SESSION["user_id"]);

    if ($orderData) {
        echo json_encode([
            "success" => true,
            "order"   => [
                "Order_ID"        => (int)$orderData["Order_ID"],
                "Order_Status"    => $orderData["Order_Status"],
                "Payment_Method"  => $orderData["Payment_Method"],
                "Payment_Status"  => $orderData["Payment_Status"],
                "Food_Subtotal"   => (float)$orderData["Food_Subtotal"],
                "Delivery_Fee"    => (float)$orderData["Delivery_Fee"],
                "Total_Amount"    => (float)$orderData["Total_Amount"],
                "Area_Name"       => $orderData["Area_Name"] ?? "",
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Order not found."]);
    }
    exit;
}

function handleSubmitReview($conn) {
    if (!isset($_SESSION["user_id"])) {
        echo json_encode(["success" => false, "message" => "Please login first."]);
        exit;
    }

    $rawInput = file_get_contents("php://input");
    $data     = json_decode($rawInput, true);

    if (!is_array($data)) {
        echo json_encode(["success" => false, "message" => "Invalid request."]);
        exit;
    }

    $restaurantId = (int)($data["restaurantId"] ?? 0);
    $rating       = (int)($data["rating"]       ?? 0);
    $comment      = trim($data["comment"]       ?? "");

    if ($restaurantId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid restaurant."]);
        exit;
    }

    if ($rating < 1 || $rating > 5) {
        echo json_encode(["success" => false, "message" => "Rating must be between 1 and 5."]);
        exit;
    }

    if ($comment === "") {
        echo json_encode(["success" => false, "message" => "Please write a comment."]);
        exit;
    }

    $reviewModel = new Review($conn);
    $result = $reviewModel->addReview((int)$_SESSION["user_id"], $restaurantId, $rating, $comment);

    if ($result) {
        echo json_encode(["success" => true, "message" => "Review submitted successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Review could not be submitted. You may need a delivered order first, or you may have already reviewed this restaurant."]);
    }
    exit;
}
