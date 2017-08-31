<?php
namespace Laranix\Auth\Listeners;

use Laranix\Auth\Events\Settings;
use Laranix\Support\Listeners\Listener;
use Illuminate\Auth\Events\Logout as LogoutEvent;

class Logout extends Listener
{
    /**
     * @var string
     */
    protected $type = 'logout';

    /**
     * Handle event
     *
     * @param \Illuminate\Auth\Events\Logout $event
     */
    public function handle(LogoutEvent $event)
    {
        $this->track(Settings::TYPEID_LOGOUT, $event->user->id, 10, null, $event->user->id);
    }
}
