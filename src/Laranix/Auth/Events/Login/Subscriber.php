<?php
namespace Laranix\Auth\Events\Login;

use Laranix\Support\Listeners\Listener;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Laranix\Auth\User\User;

class Subscriber extends Listener
{
    /**
     * @var string
     */
    protected $type = 'login';

    /**
     * @param \Illuminate\Auth\Events\Authenticated $event
     */
    public function onAuthenticated(Authenticated $event)
    {
        // Only fire if the account is active
        // We'll handle other cases another event/listener
        if ($event->user->account_status === User::USER_ACTIVE) {
            $this->track(Settings::TYPEID_AUTHENTICATED, $event->user->id, 15, null, $event->user->id);
        }
    }

    /**
     * @param \Illuminate\Auth\Events\Login $event
     */
    public function onLogin(Login $event)
    {
        $this->track(Settings::TYPEID_LOGIN, $event->user->id, 15, null, $event->user->id);
    }

    /**
     * @param \Illuminate\Auth\Events\Failed $event
     */
    public function onFailed(Failed $event)
    {
        $this->track(
            Settings::TYPEID_LOGIN_FAILED,
            $event->user->id ?? null,
            90,
            sprintf('**Email:** _%s_', (isset($event->credentials['email']) ?  "<{$event->credentials['email']}>" : 'Email not set')),
            $event->user->id ?? null
        );
    }

    /**
     * @param \Illuminate\Auth\Events\Lockout $event
     */
    public function onLockout(Lockout $event)
    {
        $email = $event->request->get('email');

        $this->track(
            Settings::TYPEID_LOGIN_LOCKOUT,
            null,
            100,
            sprintf('**Email:** _%s_', ($email !== null ?  "<{$email}>" : 'Email not set'))
        );
    }

    /**
     * @param \Laranix\Auth\Events\Login\Restricted $event
     */
    public function onLoginRestricted(Restricted $event)
    {
        // Only fire if the account is not active
        if ($event->user->account_status !== User::USER_ACTIVE) {
            $this->track(
                Settings::TYPEID_LOGIN_RESTRICTED,
                $event->user->id,
                75,
                sprintf('**Message:** _%s_', $event->message),
                $event->user->id
            );
        }
    }

    /**
     * Register listeners
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Authenticated::class,
            'Laranix\Auth\Events\Login\Subscriber@onAuthenticated'
        );

        $events->listen(
            Login::class,
            'Laranix\Auth\Events\Login\Subscriber@onLogin'
        );

        $events->listen(
            Failed::class,
            'Laranix\Auth\Events\Login\Subscriber@onFailed'
        );

        $events->listen(
            Lockout::class,
            'Laranix\Auth\Events\Login\Subscriber@onLockout'
        );

        $events->listen(
            Restricted::class,
            'Laranix\Auth\Events\Login\Subscriber@onLoginRestricted'
        );
    }
}
