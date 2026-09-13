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
     * Uses the actual Review table and its column names.
     * Validates that:
     *  - the restaurant exists
     *  - the customer has a delivered order from that restaurant
     *    (order must be delivered before a review is accepted)
     *  - the customer has not already reviewed this restaurant
     *
     * @param int    $userId       Customer User_ID
     * @param int    $restaurantId Restaurant_ID
     * @param int    $rating       1–5
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
         * Verify that the customer has a Delivered order from this restaurant.
         * Business rule: only customers with a delivered order may review.
         */
        $orderCheck = mysqli_prepare(
            $this->conn,
            "SELECT Order_ID
             FROM `Order`
             WHERE Customer_ID = ?
               AND Restaurant_ID = ?
               AND Order_Status = 'Delivered'
             LIMIT 1"
        );

        if (!$orderCheck) {
            return false;
        }

        mysqli_stmt_bind_param($orderCheck, "ii", $userId, $restaurantId);
        mysqli_stmt_execute($orderCheck);
        $orderResult = mysqli_stmt_get_result($orderCheck);
        $hasOrder    = mysqli_fetch_assoc($orderResult);
        mysqli_stmt_close($orderCheck);

        if (!$hasOrder) {
            return false;
        }

        /*
         * Prevent duplicate reviews: one customer, one restaurant.
         */
        $dupCheck = mysqli_prepare(
            $this->conn,
            "SELECT Review_ID
             FROM Review
             WHERE Customer_ID = ?
               AND Restaurant_ID = ?
             LIMIT 1"
        );

        if (!$dupCheck) {
            return false;
        }

        mysqli_stmt_bind_param($dupCheck, "ii", $userId, $restaurantId);
        mysqli_stmt_execute($dupCheck);
        $dupResult = mysqli_stmt_get_result($dupCheck);
        $existing  = mysqli_fetch_assoc($dupResult);
        mysqli_stmt_close($dupCheck);

        if ($existing) {
            return false;
        }

        /* Insert the review */
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO Review
            (Customer_ID, Restaurant_ID, Rating, Comment, Review_Date)
            VALUES (?, ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param(
            $stmt,
            "iiis",
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