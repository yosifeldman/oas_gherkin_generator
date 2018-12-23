<?php

namespace App\Providers;

use App\Events\ModelDeletedEvent;
use App\Listeners\ModelDeletedListener;
use App\Stream\Dispatcher;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ModelDeletedEvent::class => [
            ModelDeletedListener::class
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();
        \Event::listen('revisionable.*', function($model, $revisions) {
            Dispatcher::fire($model, $revisions);
        });
    }
}
