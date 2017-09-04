<?php
namespace Laranix\Auth\Email\Listeners;

use Laranix\Auth\Email\Events\Settings;
use Laranix\Auth\Email\Events\Updated as UpdatedEvent;
use Laranix\Support\Listeners\Listener;

class Updated extends Listener
{
    /**
     * @var string
     */
    protected $type = 'email';

    /**
     * Handle event
     *
     * @param \Laranix\Auth\Email\Events\Updated $event
     */
    public function handle(UpdatedEvent $event)
    {
        if ($event->oldemail === $event->user->email) {
            $data = sprintf('**Verified:** _<%s>_', $event->user->email);
        } else {
            $data = implode("\n", [
                sprintf('**Updated:** _<%s>_', $event->user->email),
                sprintf('**Old:** _<%s>_', $event->oldemail),
            ]);
        }

        $this->track(Settings::TYPEID_UPDATED, $event->user->id, 25, $data);
    }
}
