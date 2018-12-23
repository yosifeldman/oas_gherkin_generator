<?php

namespace App\Stream;

use Illuminate\Database\Eloquent\Model;
use Prwnr\Streamer\Contracts\Event;

/**
 * Class BaseEvent
 * @package App\Stream
 */
abstract class BaseEvent implements Event
{

    protected $name = '';

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var string
     */
    protected $action;

    /**
     * {@inheritdoc}
     */
    public function type(): string
    {
        return Event::TYPE_EVENT;
    }

    public function name(): string
    {
        return $this->name . '.' . $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * BaseEvent constructor.
     * @param Model $model
     * @param array $revisions
     * @param string $action
     */
    public function __construct(Model $model, array $revisions, string $action)
    {
        $this->payload['_id'] = $model->getKey();
        $this->payload['timestamp'] = time();
        $this->action = $action;
        if ($this->action === Dispatcher::EVENT_CREATED) {
            $this->prepareCreatedDiff($model);
            return;
        }

        $this->prepareDiff($revisions);
    }

    /**
     * @param array $revisions
     */
    protected function prepareDiff(array $revisions): void
    {
        foreach ($revisions as $revision) {
            $this->payload['fields'] = $revision['key'];
            $this->payload['before'][$revision['key']] = $revision['old_value'];
            $this->payload['after'][$revision['key']] = $revision['new_value'];
        }
    }

    /**
     * @param Model $model
     */
    protected function prepareCreatedDiff(Model $model): void
    {
        foreach ($model->getAttributes() as $name => $attribute) {
            $this->payload['fields'][] = $name;
            $this->payload['before'][$name] = null;
            $this->payload['after'][$name] = $attribute;
        }
    }
}