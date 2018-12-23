<?php
/**
 * Created by PhpStorm.
 * User: yoseff
 * Date: 10/29/2018
 * Time: 2:04 PM
 */

namespace App\Models;


use App\Events\ModelDeletedEvent;
use Jenssegers\Mongodb\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Product extends Model
{
    use RevisionableTrait;
    use ModelFunctions;

    protected $revisionCreationsEnabled = true;

    public static $rules = [
        'sku'     => 'required|string',
        'name'   => 'required|string',
        'description' => 'string',
        'qty' => 'required|integer|between:1,10',
        'price'  => 'required|money'
    ];

    protected $dispatchesEvents = [
        'deleted' => ModelDeletedEvent::class
    ];

    public function __construct(array $attributes = [])
    {
        $this->primaryKey = 'sku';
        $this->fillable = array_keys(static::$rules);
        $this->hidden = ['_id'];
        parent::__construct($attributes);
    }
}