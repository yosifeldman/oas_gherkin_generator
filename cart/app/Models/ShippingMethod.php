<?php


namespace App\Models;


class ShippingMethod
{
    // all is hardcoded for now
    private static $temp_data = [
        [
            'id' => '1',
            'name' => 'free-14-days',
            'price' => 0.00,
            'days' => 14
        ],
        [
            'id' => '2',
            'name' => 'fast-4-days',
            'price' => 3.50,
            'days' => 4
        ],
        [
            'id' => '3',
            'name' => 'one-day',
            'price' => 7.00,
            'days' => 1
        ]
    ];

    public static function get($id = null): array {
        $coll = collect(self::$temp_data)->keyBy('id')->toArray();
        return !$id ? $coll : array_get($coll, $id, []);
    }
}