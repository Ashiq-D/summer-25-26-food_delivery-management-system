<?php

class Order
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Create an order using server-side price validation.
     *
     * The server retrieves real food prices from Food_Item.
     * Frontend prices are NEVER trusted for financial calculations.
     *
     * @param int    $userId          Customer User_ID
     * @param int    $restaurantId    Restaurant_ID
     * @param string $paymentMethod   e.g. "Cash on Delivery", "Online Payment"
     * @param string $deliveryAddress Area name string
     * @param array  $items           Array of {id, quantity, customization}
     *
     * @return int|false  New Order_ID on success, false on failure
     */
    public function createOrder(
        $userId,
        $restaurantId,
        $paymentMethod,
        $deliveryAddress,
        $items
    ) {

        /* ----------------------------------------------------------------
         * 1. Validate inputs
         * ---------------------------------------------------------------- */

        $userId       = (int)$userId;
        $restaurantId = (int)$restaurantId;
        $paymentMethod = trim($paymentMethod);
        $deliveryAddress = trim($deliveryAddress);

        if (
            $userId <= 0 ||
            $restaurantId <= 0 ||
            $paymentMethod === "" ||
            $deliveryAddress === "" ||
            empty($items)
        ) {
            return false;
        }

        /* ----------------------------------------------------------------
         * 2. Resolve Area_ID from the area name
         * ---------------------------------------------------------------- */

        $areaStmt = mysqli_prepare(
            $this->conn,
            "SELECT Area_ID
             FROM Area
             WHERE Area_Name = ?"
        );

        if (!$areaStmt) {
            return false;
        }

        mysqli_stmt_bind_param($areaStmt, "s", $deliveryAddress);
        mysqli_stmt_execute($areaStmt);
        $areaResult = mysqli_stmt_get_result($areaStmt);
        $area = mysqli_fetch_assoc($areaResult);
        mysqli_stmt_close($areaStmt);

        if (!$area) {
            return false;
        }

        $areaId = (int)$area["Area_ID"];

        /* ----------------------------------------------------------------
         * 3. Validate each item and retrieve real prices from the database.
         *    Reject the entire order if any item is invalid or unavailable.
         * ---------------------------------------------------------------- */

        $validatedItems = [];
        $foodSubtotal   = 0.0;

        foreach ($items as $item) {

            $foodId   = (int)($item["id"] ?? $item["Food_ID"] ?? 0);
            $quantity = (int)($item["quantity"] ?? 1);
            $customization = trim($item["customization"] ?? "");

            if ($foodId <= 0 || $quantity <= 0) {
                return false;
            }

            /*
             * Fetch real food data from database.
             * Verify: food exists, belongs to this restaurant, is available.
             */
            $foodStmt = mysqli_prepare(
                $this->conn,
                "SELECT Food_ID, Name, Price, Availability_Status, Restaurant_ID
                 FROM Food_Item
                 WHERE Food_ID = ?
                   AND Restaurant_ID = ?"
            );

            if (!$foodStmt) {
                return false;
            }

            mysqli_stmt_bind_param($foodStmt, "ii", $foodId, $restaurantId);
            mysqli_stmt_execute($foodStmt);
            $foodResult = mysqli_stmt_get_result($foodStmt);
            $foodRow    = mysqli_fetch_assoc($foodResult);
            mysqli_stmt_close($foodStmt);

            if (!$foodRow) {
                /* Food does not exist or does not belong to this restaurant */
                return false;
            }

            if ($foodRow["Availability_Status"] !== 'Available') {
                /* Food is not currently available */
                return false;
            }

            /* Use the database price — never the frontend price */
            $dbPrice = (float)$foodRow["Price"];

            $foodSubtotal += $dbPrice * $quantity;

            $validatedItems[] = [
                "foodId"        => $foodId,
                "foodName"      => $foodRow["Name"],
                "price"         => $dbPrice,
                "quantity"      => $quantity,
                "customization" => $customization,
            ];
        }

        if ($foodSubtotal <= 0) {
            return false;
        }

        /* ----------------------------------------------------------------
         * 4. Calculate totals on the server
         * ---------------------------------------------------------------- */

        $deliveryFee = 50.00;

        $feeStmt = mysqli_prepare(
            $this->conn,
            "SELECT Setting_Value FROM Settings WHERE Setting_Key = 'delivery_fee'"
        );

        if ($feeStmt) {
            mysqli_stmt_execute($feeStmt);
            $feeResult = mysqli_stmt_get_result($feeStmt);
            $feeRow    = mysqli_fetch_assoc($feeResult);
            mysqli_stmt_close($feeStmt);

            if ($feeRow && $feeRow["Setting_Value"] !== null && $feeRow["Setting_Value"] !== "") {
                $deliveryFee = (float)$feeRow["Setting_Value"];
            }
        }

        $total       = $foodSubtotal + $deliveryFee;

        /* ----------------------------------------------------------------
         * 5. Run everything inside a database transaction
         * ---------------------------------------------------------------- */

        mysqli_begin_transaction($this->conn);

        try {

            /* Insert Order */
            $orderStmt = mysqli_prepare(
                $this->conn,
                "INSERT INTO `Order`
                (
                    Customer_ID,
                    Restaurant_ID,
                    Delivery_Area_ID,
                    Food_Subtotal,
                    Delivery_Fee,
                    Total_Amount,
                    Payment_Method,
                    Payment_Status,
                    Order_Status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending')"
            );

            if (!$orderStmt) {
                throw new Exception("Failed to prepare order statement.");
            }

            mysqli_stmt_bind_param(
                $orderStmt,
                "iiiddds",
                $userId,
                $restaurantId,
                $areaId,
                $foodSubtotal,
                $deliveryFee,
                $total,
                $paymentMethod
            );

            if (!mysqli_stmt_execute($orderStmt)) {
                throw new Exception("Failed to insert Order.");
            }

            $orderId = mysqli_insert_id($this->conn);
            mysqli_stmt_close($orderStmt);

            /* Insert Order_Item records using validated database prices */
            foreach ($validatedItems as $vItem) {

                $itemStmt = mysqli_prepare(
                    $this->conn,
                    "INSERT INTO Order_Item
                    (
                        Order_ID,
                        Food_ID,
                        Food_Name_At_Purchase,
                        Price_At_Purchase,
                        Quantity,
                        Customization
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                if (!$itemStmt) {
                    throw new Exception("Failed to prepare Order_Item statement.");
                }

                mysqli_stmt_bind_param(
                    $itemStmt,
                    "iisdis",
                    $orderId,
                    $vItem["foodId"],
                    $vItem["foodName"],
                    $vItem["price"],
                    $vItem["quantity"],
                    $vItem["customization"]
                );

                if (!mysqli_stmt_execute($itemStmt)) {
                    throw new Exception("Failed to insert Order_Item.");
                }

                mysqli_stmt_close($itemStmt);
            }

            mysqli_commit($this->conn);

            return $orderId;

        } catch (Exception $e) {

            mysqli_rollback($this->conn);

            return false;
        }
    }

    /**
     * Get an order by ID, restricted to the given customer.
     *
     * @param int $orderId  Order_ID
     * @param int $userId   Customer_ID (session user)
     *
     * @return array|null
     */
    public function getLatestOrderForCustomer($userId)
    {
        $sql = "SELECT * FROM `Order` WHERE Customer_ID = ? ORDER BY Order_ID DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        return false;
    }

    public function getOrderById($orderId, $userId)
    {
        $orderId = (int)$orderId;
        $userId  = (int)$userId;

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT
                o.Order_ID,
                o.Customer_ID,
                o.Restaurant_ID,
                o.Delivery_Area_ID,
                o.Food_Subtotal,
                o.Delivery_Fee,
                o.Total_Amount,
                o.Payment_Method,
                o.Payment_Status,
                o.Order_Status,
                a.Area_Name
             FROM `Order` o
             LEFT JOIN Area a
                ON o.Delivery_Area_ID = a.Area_ID
             WHERE o.Order_ID = ?
               AND o.Customer_ID = ?"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "ii", $orderId, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $row;
    }
}

?>
