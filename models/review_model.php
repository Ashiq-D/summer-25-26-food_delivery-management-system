<?php

class Review
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Add a review for a restaurant.
     *
     * Uses the Restaurant_Review table (Order_ID based - matches the
     * CRAVERUSH schema, where at most one review exists per Order).
     *
     * Validates that:
     *  - the customer has a Delivered order from that restaurant
     *  - that order does not already have a review
     *
     * @param int    $userId       Customer_ID
     * @param int    $restaurantId Restaurant_ID
     * @param int    $rating       1-5
     * @param string $comment      Review text
     *
     * @return bool
     */
    public function addReview(
        $userId,
        $restaurantId,
        $rating,
        $comment
    ) {

        $userId       = (int)$userId;
        $restaurantId = (int)$restaurantId;
        $rating       = (int)$rating;
        $comment      = trim($comment);

        if (
            $userId <= 0 ||
            $restaurantId <= 0 ||
            $rating < 1 || $rating > 5 ||
            $comment === ""
        ) {
            return false;
        }

        /*
         * Find the most recent Delivered order from this customer at this
         * restaurant that does NOT already have a Restaurant_Review.
         * (Restaurant_Review.Order_ID is UNIQUE - one review per order.)
         */
        $orderCheck = mysqli_prepare(
            $this->conn,
            "SELECT o.Order_ID
             FROM `Order` o
             LEFT JOIN Restaurant_Review rr ON rr.Order_ID = o.Order_ID
             WHERE o.Customer_ID = ?
               AND o.Restaurant_ID = ?
               AND o.Order_Status = 'Delivered'
               AND rr.Restaurant_Review_ID IS NULL
             ORDER BY o.Order_ID DESC
             LIMIT 1"
        );

        if (!$orderCheck) {
            return false;
        }

        mysqli_stmt_bind_param($orderCheck, "ii", $userId, $restaurantId);
        mysqli_stmt_execute($orderCheck);
        $orderResult = mysqli_stmt_get_result($orderCheck);
        $order       = mysqli_fetch_assoc($orderResult);
        mysqli_stmt_close($orderCheck);

        if (!$order) {
            /* No eligible (delivered, unreviewed) order for this restaurant */
            return false;
        }

        $orderId = (int)$order["Order_ID"];

        /* Insert the review */
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO Restaurant_Review
            (Order_ID, Customer_ID, Restaurant_ID, Rating, Comment, Review_Date)
            VALUES (?, ?, ?, ?, ?, CURDATE())"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param(
            $stmt,
            "iiiis",
            $orderId,
            $userId,
            $restaurantId,
            $rating,
            $comment
        );

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }
}

?>
