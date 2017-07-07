<?php
namespace Laranix\Auth\Email\Verification\Events;

use Laranix\Auth\User\Token\Events\Subscriber as BaseSubscriber;

class Subscriber extends BaseSubscriber
{
    /**
     * @var string
     */
    protected $type = 'email_verification';

    /**
     * Register listeners
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Created::class,
            'Laranix\Auth\Email\Verification\Events\Subscriber@onCreated'
        );

        $events->listen(
            Updated::class,
            'Laranix\Auth\Email\Verification\Events\Subscriber@onUpdated'
        );

        $events->listen(
            VerifyAttempt::class,
            'Laranix\Auth\Email\Verification\Events\Subscriber@onVerifyAttempt'
        );

        $events->listen(
            Failed::class,
            'Laranix\Auth\Email\Verification\Events\Subscriber@onFailed'
        );

        $events->listen(
            Verified::class,
            'Laranix\Auth\Email\Verification\Events\Subscriber@onCompleted'
        );

        $events->listen(
            RefreshAttempt::class,
            'Laranix\Auth\Email\Verification\Events\Subscriber@onCreateUpdateAttempt'
        );
    }
}
