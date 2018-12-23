<?php

namespace App\Models;


use Jenssegers\Mongodb\Relations\EmbedsMany;
use Jenssegers\Mongodb\Relations\EmbedsOne;

trait ModelFunctions
{
    public function __construct()
    {
        if(config('app.env') === 'testing') {
            $this->revisionEnabled = false;
        }
        parent::__contsruct();
    }

    /**
     * Generic function to load Model validation rules
     *
     * @param bool $update
     *
     * @return array
     */
    public static function getRules($update = false): array
    {
        $rules = property_exists(static::class, 'rules') ? static::$rules : null;
        $me = new static();
        if ($update) {
            $rules = array_map(function ($a) {
                return str_replace('required', 'sometimes', $a);
            }, $rules);
        }
        elseif (!empty($me->nested)) {
            foreach($me->nested as $method) {
                $relation = $me->$method();
                $mid = '';
                if($relation instanceof EmbedsMany) {
                    $mid = '.*.';
                } elseif($relation instanceof EmbedsOne) {
                    $mid = '.';
                } else {
                    abort(500, 'Unsupported relationship type '.\get_class($relation).'.');
                }
                $model = $relation->getRelated();
                foreach($model::getRules() as $col => $rule) {
                    $rules[$method.$mid.$col] = $rule;
                }
            }
        }

        return array_merge($rules, static::getCustomRules($update));
    }

    /**
     * Override in Model class to have even more rules
     * @param bool $update
     *
     * @return array
     */
    protected static function getCustomRules($update = false): array
    {
        return [];
    }

    public function toArray(): array
    {
        $arr = parent::toArray();

        if(!empty($this->nested)) {
            foreach($this->nested as $method) {
                $relation = $this->$method();
                if($relation instanceof EmbedsMany) {
                    $arr[$method] = $relation->toArray();
                } elseif($relation instanceof EmbedsOne) {
                    if($model = $relation->get()) {
                        $arr[$method] = $model->toArray();
                    }
                } else {
                    abort(500, 'Unsupported relationship type '.\get_class($relation).'.');
                }
            }
        }

        return $arr;
    }
}