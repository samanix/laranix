<?php
namespace Laranix\Auth\Email\Verification;

use Illuminate\Contracts\Auth\Authenticatable;
use Laranix\Auth\User\Token\Manager as BaseManager;
use Laranix\Auth\Email\Events\Updated as EmailUpdated;
use Laranix\Auth\Email\Verification\Events\Created;
use Laranix\Auth\Email\Verification\Events\Failed;
use Laranix\Auth\Email\Verification\Events\Updated;
use Laranix\Auth\Email\Verification\Events\Verified as VerifiedEvent;
use Laranix\Auth\User\User;
use Laranix\Support\Exception\EmailExistsException;

class Manager extends BaseManager
{
    /**
     * @inheritDoc
     */
    protected $configKey = 'verification';

    /**
     * @inheritDoc
     */
    protected $model = Verification::class;

    /**
     * @inheritDoc
     */
    protected $createdEvent = Created::class;

    /**
     * @inheritDoc
     */
    protected $updatedEvent = Updated::class;

    /**
     * @inheritDoc
     */
    protected $failedEvent = Failed::class;

    /**
     * @inheritDoc
     */
    protected $completedEvent = VerifiedEvent::class;

    /**
     * Update user after token verified
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $email
     * @param mixed                                           $extra
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Laranix\Auth\User\User
     */
    protected function updateUser(Authenticatable $user, string $email, $extra = null): Authenticatable
    {
        if ($user->account_status === User::USER_UNVERIFIED) {
            $user->account_status = User::USER_ACTIVE;
        }

        $oldemail = $user->email;

        $user->email = $email;
        $user->save();

        event(new EmailUpdated($user, $oldemail));

        return $user;
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $email
     * @return mixed
     * @throws \Laranix\Support\Exception\EmailExistsException
     */
    protected function canInsertToken(Authenticatable $user, string $email)
    {
        $existing = User::where('email', $email)->get();

        $count = $existing->count();

        if ($count === 0) {
            return true;
        }

        if ($count === 1 && $existing[0]->id === $user->id) {
            return true;
        }

        throw new EmailExistsException('Email already exists');
    }
}
