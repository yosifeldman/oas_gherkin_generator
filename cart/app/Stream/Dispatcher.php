<?php

namespace App\Stream;

use App\Stream\Events\Cart;
use App\Stream\Events\Coupon;
use Illuminate\Support\Facades\Log;
use Prwnr\Streamer\Facades\Streamer;

/**
 * Class Dispatcher
 * @package App\Streams
 */
class Dispatcher
{
    public const EVENT_UPDATED = 'updated';
    public const EVENT_CREATED = 'created';
    public const EVENT_DELETED = 'deleted';

    private const ACTION = [
        'revisionable.saved' => self::EVENT_UPDATED,
        'revisionable.created' => self::EVENT_CREATED,
        'revisionable.deleted' => self::EVENT_DELETED
    ];

    private const EVENTS = [
        'Cart' => Cart::class,
        'Product' => Cart\Product::class,
        'Coupon' => Coupon::class,
        'Shipping' => Cart\Shipping::class
    ];

    /**
     * Determines event name based on model and revision
     * and emits appropriate event
     * @param $revisionAction
     * @param $data
     */
    public static function fire($revisionAction, $data): void
    {
        [$model, $revisions] = array_values($data);
        $event = self::EVENTS[class_basename($model)];
        if (!$event) {
            return;
        }

        /** @var BaseEvent $event */
        $event = new $event($model, $revisions, self::ACTION[$revisionAction]);
        $availableEvents = config('streamer.available_events', []);
        if (!\in_array($event->name(), $availableEvents, true)) {
            Log::warning("Attempted to fire event '{$event->name()}' that is not on available events list");
            return;
        }

        Streamer::emit($event);
    }
}