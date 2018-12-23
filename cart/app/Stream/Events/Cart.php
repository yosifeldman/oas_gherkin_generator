<?php


namespace App\Stream\Events;


use App\Stream\BaseEvent;
use Jenssegers\Mongodb\Eloquent\Model;

class Cart extends BaseEvent
{
    public function __construct(Model $model, array $revisions, string $action)
    {
        $this->name = 'cart';
        parent::__construct($model, $revisions, $action);
    }
}