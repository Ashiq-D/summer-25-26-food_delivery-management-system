<?php

include_once __DIR__ . "/../config/config.php";
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
        $restaurants = getAllRestaurants();
        $formatted = [];

        foreach ($restaurants as $r) {
            $menus = getMenuItemsByRestaurant($r['restaurant_id']);
            $formattedMenus = [];

            foreach ($menus as $m) {
                // Ensure price is numeric
                $formattedMenus[] = [
                    'id' => (int)$m['food_id'],
                    'name' => $m['name'],
                    'image' => '../../assets/images/food' . ((int)$m['food_id'] % 14 + 1) . '.jpg',
                    'description' => $m['description'],
                    'price' => (float)$m['price']
                ];
            }

            $formatted[] = [
                'id' => (int)$r['restaurant_id'],
                'name' => $r['name'],
                'image' => '../../assets/images/restaurant' . (((int)$r['restaurant_id'] - 1) % 5 + 1) . '.jpg',
                'area' => $r['area_name'] ?? 'Unknown Area',
                'menu' => $formattedMenus
            ];
        }

        return $formatted;
    }

    /**
     * Get a single restaurant by ID.
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

        $r = getRestaurantById($id);

        if (!$r) {
            return null;
        }

        $menus = getMenuItemsByRestaurant($r['restaurant_id']);
        $formattedMenus = [];

        foreach ($menus as $m) {
            $formattedMenus[] = [
                'id' => (int)$m['food_id'],
                'name' => $m['name'],
                'image' => '../../assets/images/food' . ((int)$m['food_id'] % 14 + 1) . '.jpg',
                'description' => $m['description'],
                'price' => (float)$m['price']
            ];
        }

        return [
            'id' => (int)$r['restaurant_id'],
            'name' => $r['name'],
            'image' => '../../assets/images/restaurant' . (((int)$r['restaurant_id'] - 1) % 5 + 1) . '.jpg',
            'area' => $r['area_name'] ?? 'Unknown Area',
            'menu' => $formattedMenus
        ];
    }
}

?>