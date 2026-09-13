<?php

class Food
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get all available food items for a given restaurant.
     *
     * Uses the actual Food_Item table with the correct column names.
     *
     * @param int $restaurantId Restaurant_ID
     *
     * @return array
     */
    public function getFoodsByRestaurant($restaurantId)
    {
        $restaurantId = (int)$restaurantId;

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT
                Food_ID,
                Restaurant_ID,
                Name,
                Description,
                Price,
                Is_Available
             FROM Food_Item
             WHERE Restaurant_ID = ?
               AND Is_Available = 1"
        );

        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, "i", $restaurantId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $foods = [];

        while ($row = mysqli_fetch_assoc($result)) {

            /* Expose consistent lowercase aliases for the frontend */
            $row["id"]          = $row["Food_ID"];
            $row["restaurant_id"] = $row["Restaurant_ID"];
            $row["name"]        = $row["Name"];
            $row["description"] = $row["Description"] ?? "";
            $row["price"]       = $row["Price"];
            $row["available"]   = (bool)$row["Is_Available"];

            /* Image path relative to views/ — actual files: food1.jpg … food14.jpg */
            $row["image"] =
                "../../assets/images/food" .
                $row["Food_ID"] .
                ".jpg";

            $foods[] = $row;
        }

        mysqli_stmt_close($stmt);

        return $foods;
    }

    /**
     * Get a single food item by its Food_ID.
     *
     * @param int $foodId Food_ID
     *
     * @return array|null
     */
    public function getFoodById($foodId)
    {
        $foodId = (int)$foodId;

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT
                Food_ID,
                Restaurant_ID,
                Name,
                Description,
                Price,
                Is_Available
             FROM Food_Item
             WHERE Food_ID = ?"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $foodId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $row;
    }
}

?>