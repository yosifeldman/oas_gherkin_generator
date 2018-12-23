<?php


namespace App\Listeners;

use App\Events\Event;
use App\Events\ModelDeletedEvent;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Mongodb\Eloquent\Model;
use Nmi\Authjwt\AuthJwtProvider;
use Nmi\Authjwt\Models\User;

/**
 * Class ModelDeletedListener
 * @package App\Listeners
 */
class ModelDeletedListener
{

    /**
     * Makes a revision and dispatches revision event about deleted model
     * @param ModelDeletedEvent $event
     */
    public function handle(ModelDeletedEvent $event): void
    {
        /** @var Model $model */
        $model = $event->model;
        $revisions[] = [
            'revisionable_type' => $model->getMorphClass(),
            'revisionable_id' => $model->getKey(),
            'key' => 'deleted',
            'old_value' => false,
            'new_value' => true,
            'user_id' => Auth::user()->client_id,
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ];
        \Event::fire('revisionable.deleted', array('model' => $model, 'revisions' => $revisions));
    }
}