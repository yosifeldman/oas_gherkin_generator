<?php


namespace App\Events;

use \Prwnr\Streamer\Contracts\Event;


class OrderPlacedStreamerEvent implements Event
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Require name method, must return a string.
     * Event name can be anything, but remember that it will be used for listening
     */
    public function name(): string
    {
        return 'order.placed';
    }
    /**
     * Required type method, must return a string.
     * Type can be any string or one of predefined types from Event
     */
    public function type(): string
    {
        return Event::TYPE_COMMAND;
    }
    /**
     * Required payload method, must return array
     * This array will be your message data content
     */
    public function payload(): array
    {
        return $this->data;
    }
}