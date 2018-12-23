<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;


/**
 * Class ModelDeleted
 * @package App\Events
 */
class ModelDeletedEvent extends Event
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}