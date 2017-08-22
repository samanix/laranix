<?php
namespace Laranix\Auth\Group\Events;

use Laranix\Support\Listeners\Listener;

class Subscriber extends Listener
{
    /**
     * @var string
     */
    protected $type = 'group';

    /**
     * @param \Laranix\Auth\Group\Events\Created $event
     */
    public function onCreated(Created $event)
    {
        $this->track(Settings::TYPEID_CREATED, $event->group->id, 10);
    }

    /**
     * @param \Laranix\Auth\Group\Events\Updated $event
     */
    public function onUpdated(Updated $event)
    {
        $this->track(
            Settings::TYPEID_UPDATED,
            $event->group->id,
            10,
            modelDiff($event->oldgroup->toArray(), $event->group->toArray())
        );
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
            'Laranix\Auth\Group\Events\Subscriber@onCreated'
        );

        $events->listen(
            Updated::class,
            'Laranix\Auth\Group\Events\Subscriber@onUpdated'
        );
    }
}
