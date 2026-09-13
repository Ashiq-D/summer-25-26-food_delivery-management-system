<?php

include_once __DIR__ . "/../config/database.php";
include_once __DIR__ . "/../models/restaurant_model.php";

class CustomerController
{
    /**
     * Get all restaurants (with menus).
     *
     * @return array
     */
    public function getRestaurants()
    {
        $restaurant = new Restaurant($GLOBALS["conn"]);

        return $restaurant->getAllRestaurants();
    }

    /**
     * Get a single restaurant by ID.
     *
     * Returns null and does not expose errors if the ID is invalid.
     *
     * @param int $id
     *
     * @return array|null
     */
    public function getRestaurant($id)
    {
        $id = (int)$id;

        if ($id <= 0) {
            return null;
        }

        $restaurant = new Restaurant($GLOBALS["conn"]);

        return $restaurant->getRestaurantById($id);
    }
}

?>