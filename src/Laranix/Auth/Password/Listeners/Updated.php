<?php
namespace Laranix\Auth\Password\Listeners;

use Laranix\Auth\Password\Events\Settings;
use Laranix\Auth\Password\Events\Updated as UpdatedEvent;
use Laranix\Support\Listeners\Listener;

class Updated extends Listener
{
    /**
     * @var string
     */
    protected $type = 'password';

    /**
     * Handle event
     *
     * @param \Laranix\Auth\Password\Events\Updated $event
     */
    public function handle(UpdatedEvent $event)
    {
        $this->track(Settings::TYPEID_UPDATED, $event->user->id, 50);
    }
}
