<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\EmbedsMany;
use Venturecraft\Revisionable\RevisionableTrait;

class Cart extends Model
{
    use RevisionableTrait;
    use ModelFunctions;

    protected $revisionCreationsEnabled = true;
    protected $nested                   = ['products'];

    public static $rules = [
        'brand_id'    => 'required|integer',
        'customer_id' => 'required|uuid',
        'products'    => 'array',
        'tax'         => 'money',
        'subtotal'    => 'money',
        'grand_total' => 'money'
    ];

    public function __construct(array $attributes = [])
    {
        $this->connection = 'mongodb';
        $this->collection = config('database.connections.mongodb.collection');
        $this->fillable   = ['brand_id', 'customer_id', 'tax', 'subtotal', 'grand_total'];
        parent::__construct($attributes);
    }

    public function products(): EmbedsMany
    {
        return $this->embedsMany(Product::class);
    }

    public function getSummary(array $cartData = null): array
    {
        $data = $cartData ?: $this->toArray();

        // calculate totals
        $subtotal = $ship_price = $tax = $discount = $grand_total = 0.00;
        foreach ($data['products'] as $key => $product) {
            $subtotal += $product['price'] * $product['qty'];
        }

        if (!empty($data['shipping']['price'])) {
            $ship_price = (float)$data['shipping']['price'];
        }

        if (!empty($data['taxes_total_rate'])) {
            $tax = (float)$data['taxes_total_rate'];
        }

        if (!empty($data['discount']['reward_currency_amount'])) {
            $discount = (float)$data['discount']['reward_currency_amount'];
        }

        if ($ship_price && !empty($data['discount']['shipping_discount'])) {
            $ship_price -= (float)$data['discount']['shipping_discount'];
            $ship_price = max($ship_price, 0);
        }

        $grand_total = $subtotal + ($subtotal * $tax) - $discount + $ship_price;

        $summary = compact('subtotal', 'tax', 'ship_price', 'discount', 'grand_total');

        return array_merge($data, array_map(function($a){ return round($a,2); }, $summary));
    }
}