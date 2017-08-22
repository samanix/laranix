<?php
namespace Laranix\Auth\User\Events;

use Laranix\Support\Listeners\Listener;

class Subscriber extends Listener
{
    /**
     * @var string
     */
    protected $type = 'user';

    /**
     * @param \Laranix\Auth\User\Events\Created $event
     */
    public function onCreated(Created $event)
    {
        $this->track(Settings::TYPEID_CREATED, $event->user->id, 10);
    }

    /**
     * @param \Laranix\Auth\User\Events\Updated $event
     */
    public function onUpdated(Updated $event)
    {
        $this->track(Settings::TYPEID_UPDATED, $event->user->id, 10, modelDiff($event->olduser->toArray(), $event->user->toArray()));
    }

    /**
     * Register listeners
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Created::class,
            'Laranix\Auth\User\Events\Subscriber@onCreated'
        );

        $events->listen(
            Updated::class,
            'Laranix\Auth\User\Events\Subscriber@onUpdated'
        );
    }
}
