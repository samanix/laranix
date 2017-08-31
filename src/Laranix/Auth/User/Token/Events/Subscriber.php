<?php
namespace Laranix\Auth\User\Token\Events;

use Laranix\Support\Listeners\Listener;

abstract class Subscriber extends Listener
{
    /**
     * @param Created $event
     */
    public function onCreated(Created $event)
    {
        $this->track(Settings::TYPEID_CREATED, $event->user->id, 10, sprintf('**Email:** _<%s>_', $event->token->email));
    }

    /**
     * @param \Laranix\Auth\User\Token\Events\Updated $event
     */
    public function onUpdated(Updated $event)
    {
        $this->track(Settings::TYPEID_UPDATED, $event->user->id, 10, sprintf('**Email:** _<%s>_', $event->token->email));
    }

    /**
     * @param \Laranix\Auth\User\Token\Events\VerifyAttempt $event
     */
    public function onVerifyAttempt(VerifyAttempt $event)
    {
        $this->track(
            Settings::TYPEID_VERIFY_ATTEMPT,
            null,
            $event->email !== null ? 10 : 25,
            sprintf('**Email:** _%s_', (isset($event->email) ?  "<{$event->email}>" : 'Email not set'))
        );
    }

    /**
     * @param \Laranix\Auth\User\Token\Events\Failed $event
     */
    public function onFailed(Failed $event)
    {
        $this->track(
            Settings::TYPEID_FAILED,
            $event->user->id ?? null,
            $event->email !== null ? 50 : 75,
            sprintf('**Email:** _%s_', (isset($event->email) ?  "<{$event->email}>" : 'Email not set'))
        );
    }

    /**
     * @param \Laranix\Auth\User\Token\Events\Completed $event
     */
    public function onCompleted(Completed $event)
    {
        $this->track(Settings::TYPEID_COMPLETED, $event->user->id, 25, sprintf('**Email:** _<%s>_', $event->user->email));
    }

    /**
     * @param \Laranix\Auth\User\Token\Events\CreateUpdateAttempt $event
     */
    public function onCreateUpdateAttempt(CreateUpdateAttempt $event)
    {
        $this->track(
            Settings::TYPEID_CREATE_UPDATE_ATTEMPT,
            null,
            $event->email !== null ? 10 : 50,
            sprintf('**Email:** _%s_', (isset($event->email) ?  "<{$event->email}>" : 'Email not set'))
        );
    }

    /**
     * Register listeners
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    abstract public function subscribe($events);
}
