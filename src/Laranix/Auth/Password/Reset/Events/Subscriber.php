<?php
namespace Laranix\Auth\Password\Reset\Events;

use Laranix\Auth\User\Token\Events\Subscriber as BaseSubscriber;

class Subscriber extends BaseSubscriber
{
    /**
     * @var string
     */
    protected $type = 'password_reset';

    /**
     * Register listeners
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Created::class,
            'Laranix\Auth\Password\Reset\Events\Subscriber@onCreated'
        );

        $events->listen(
            Updated::class,
            'Laranix\Auth\Password\Reset\Events\Subscriber@onUpdated'
        );

        $events->listen(
            VerifyAttempt::class,
            'Laranix\Auth\Password\Reset\Events\Subscriber@onVerifyAttempt'
        );

        $events->listen(
            Failed::class,
            'Laranix\Auth\Password\Reset\Events\Subscriber@onFailed'
        );

        $events->listen(
            Reset::class,
            'Laranix\Auth\Password\Reset\Events\Subscriber@onCompleted'
        );

        $events->listen(
            ForgotAttempt::class,
            'Laranix\Auth\Password\Reset\Events\Subscriber@onCreateUpdateAttempt'
        );
    }
}
