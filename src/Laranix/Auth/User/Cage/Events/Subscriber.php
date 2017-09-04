<?php
namespace Laranix\Auth\User\Cage\Events;

use Laranix\Support\Listeners\Listener;

class Subscriber extends Listener
{
    /**
     * @var string
     */
    protected $type = 'user_cage';

    /**
     * @param \Laranix\Auth\User\Cage\Events\Created $event
     */
    public function onCreated(Created $event)
    {
        $this->track(Settings::TYPEID_CREATED, $event->cage->id, 25);
    }

    /**
     * @param \Laranix\Auth\User\Cage\Events\Updated $event
     */
    public function onUpdated(Updated $event)
    {
        $this->track(Settings::TYPEID_UPDATED, $event->cage->id, 10, modelDiff($event->oldcage->toArray(), $event->cage->toArray()));
    }

    /**
     * @param \Laranix\Auth\User\Cage\Events\Expired $event
     */
    public function onExpired(Expired $event)
    {
        $this->track(Settings::TYPEID_EXPIRED, $event->cage->id, 15);
    }

    /**
     * @param \Laranix\Auth\User\Cage\Events\Deleted $event
     */
    public function onDeleted(Deleted $event)
    {
        $this->track(Settings::TYPEID_DELETED, $event->cage->id, 25);
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
            'Laranix\Auth\User\Cage\Events\Subscriber@onCreated'
        );

        $events->listen(
            Updated::class,
            'Laranix\Auth\User\Cage\Events\Subscriber@onUpdated'
        );

        $events->listen(
            Expired::class,
            'Laranix\Auth\User\Cage\Events\Subscriber@onExpired'
        );

        $events->listen(
            Removed::class,
            'Laranix\Auth\User\Cage\Events\Subscriber@onRemoved'
        );

        $events->listen(
            Deleted::class,
            'Laranix\Auth\User\Cage\Events\Subscriber@onDeleted'
        );
    }
}
