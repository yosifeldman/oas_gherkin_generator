<?php


namespace App\Models;


use App\Events\ModelDeletedEvent;
use Jenssegers\Mongodb\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Coupon extends Model
{
    use RevisionableTrait;
    use ModelFunctions;

    protected $revisionCreationsEnabled = true;

    public static $rules = [
        'coupon_code'            => 'required|alpha_dash|unique:coupons,coupon_code',
        'reward_points_balance'  => 'required|integer',
        'reward_currency_amount' => 'required|money',
        'shipping_discount'      => 'required|money',
    ];

    protected $dispatchesEvents = [
        'deleted' => ModelDeletedEvent::class
    ];

    public function __construct(array $attributes = [])
    {
        $this->connection = 'mongodb';
        $this->collection = 'coupons';
        $this->fillable   = array_keys(static::$rules);
        $this->hidden     = ['_id'];
        $this->primaryKey = 'coupon_code';
        $this->timestamps = false;
        $this->casts = [
            'coupon_code' => 'string'
        ];
        parent::__construct($attributes);
    }
}