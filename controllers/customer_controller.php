<?php

include_once __DIR__ . "/../config/config.php";
include_once __DIR__ . "/../helpers/helpers.php";
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
        $restaurants = getAllRestaurantsForCustomer();
        $formatted = [];

        foreach ($restaurants as $r) {
            $menus = getMenuItemsByRestaurant($r['restaurant_id']);
            $formattedMenus = [];

            foreach ($menus as $m) {
                // Skip items that are currently unavailable (matches AJAX endpoints)
                if ($m['availability_status'] !== 'Available') {
                    continue;
                }

                // Use the restaurant's uploaded photo if one exists AND still
                // exists on disk, otherwise fall back to the shared stock-photo
                // pool keyed off the item's ID.
                $imageUrl = resolveImagePath(
                    $m['image_path'] ?? null,
                    '../../assets/images/food' . (((int)$m['food_id'] - 1) % 14 + 1) . '.jpg'
                );

                // Ensure price is numeric
                $formattedMenus[] = [
                    'id' => (int)$m['food_id'],
                    'name' => $m['name'],
                    'image' => $imageUrl,
                    'description' => $m['description'],
                    'price' => (float)$m['price']
                ];
            }

            $restaurantImageUrl = resolveImagePath(
                $r['profile_image'] ?? null,
                '../../assets/images/restaurant' . (((int)$r['restaurant_id'] - 1) % 5 + 1) . '.jpg'
            );

            $formatted[] = [
                'id' => (int)$r['restaurant_id'],
                'name' => $r['name'],
                'image' => $restaurantImageUrl,
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
            // Skip items that are currently unavailable (matches AJAX endpoints)
            if ($m['availability_status'] !== 'Available') {
                continue;
            }

            $imageUrl = resolveImagePath(
                $m['image_path'] ?? null,
                '../../assets/images/food' . (((int)$m['food_id'] - 1) % 14 + 1) . '.jpg'
            );

            $formattedMenus[] = [
                'id' => (int)$m['food_id'],
                'name' => $m['name'],
                'image' => $imageUrl,
                'description' => $m['description'],
                'price' => (float)$m['price']
            ];
        }

        $restaurantImageUrl = resolveImagePath(
            $r['profile_image'] ?? null,
            '../../assets/images/restaurant' . (((int)$r['restaurant_id'] - 1) % 5 + 1) . '.jpg'
        );

        return [
            'id' => (int)$r['restaurant_id'],
            'name' => $r['name'],
            'image' => $restaurantImageUrl,
            'area' => $r['area_name'] ?? 'Unknown Area',
            'menu' => $formattedMenus
        ];
    }
}

?>