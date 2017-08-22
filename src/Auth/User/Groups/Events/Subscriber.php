<?php
namespace Laranix\Auth\User\Groups\Events;

use Laranix\Support\Listeners\Listener;

class Subscriber extends Listener
{
    /**
     * @var string
     */
    protected $type = 'usergroup';

    /**
     * @param \Laranix\Auth\User\Groups\Events\Added $event
     */
    public function onAdded(Added $event)
    {
        $group = $event->usergroup->group;

        $this->track(
            Settings::TYPEID_ADDED,
            $event->usergroup->user_id,
            10,
            sprintf('User added to group **%s** (ID: %d)', $group->name, $group->id)
        );
    }

    /**
     * @param \Laranix\Auth\User\Groups\Events\Updated $event
     */
    public function onUpdated(Updated $event)
    {
        $this->track(
            Settings::TYPEID_UPDATED,
            $event->usergroup->user_id,
            10,
            modelDiff($event->oldusergroup->toArray(), $event->usergroup->toArray())
        );
    }

    /**
     * @param \Laranix\Auth\User\Groups\Events\Removed $event
     */
    public function onRemoved(Removed $event)
    {
        $group = $event->usergroup->group;

        $this->track(
            Settings::TYPEID_REMOVED,
            $event->usergroup->user_id,
            25,
            sprintf('User removed from group **%s** (ID: %d)', $group->name, $group->id)
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
            Added::class,
            'Laranix\Auth\User\Groups\Events\Subscriber@onAdded'
        );

        $events->listen(
            Updated::class,
            'Laranix\Auth\User\Groups\Events\Subscriber@onUpdated'
        );

        $events->listen(
            Removed::class,
            'Laranix\Auth\User\Groups\Events\Subscriber@onRemoved'
        );
    }
}
