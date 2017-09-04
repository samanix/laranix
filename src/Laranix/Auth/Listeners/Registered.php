<?php
namespace Laranix\Auth\Listeners;

use Laranix\Auth\Events\Settings;
use Laranix\Support\Listeners\Listener;
use Illuminate\Auth\Events\Registered as RegisteredEvent;

class Registered extends Listener
{
    /**
     * @var string
     */
    protected $type = 'registration';

    /**
     * Handle event
     *
     * @param \Illuminate\Auth\Events\Registered $event
     */
    public function handle(RegisteredEvent $event)
    {
        $this->track(Settings::TYPEID_REGISTERED, $event->user->id, 10);
    }
}
