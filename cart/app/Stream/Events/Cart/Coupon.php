<?php


namespace App\Stream\Events\Cart;


use App\Stream\BaseEvent;
use Jenssegers\Mongodb\Eloquent\Model;

class Coupon extends BaseEvent
{
    public function __construct(Model $model, array $revisions, $action)
    {
        $this->name = 'cart.coupon';
        $this->payload['_parent_id'] = $model->getParentRelation()->getModel()->getKey();
        parent::__construct($model, $revisions, $action);
    }
}