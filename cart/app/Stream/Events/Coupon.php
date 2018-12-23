<?php


namespace App\Stream\Events;


use App\Stream\BaseEvent;
use Illuminate\Database\Eloquent\Model;

class Coupon extends BaseEvent
{
    public function __construct(Model $model, array $revisions, $action)
    {
        $this->name = 'coupon';
        parent::__construct($model, $revisions, $action);
    }
}