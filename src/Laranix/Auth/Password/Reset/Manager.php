<?php
namespace Laranix\Auth\Password\Reset;

use Illuminate\Contracts\Auth\Authenticatable;
use Laranix\Auth\Password\Hasher as PasswordHasher;
use Laranix\Auth\Password\HashesPasswords;
use Laranix\Auth\User\Token\Manager as BaseManager;
use Laranix\Auth\User\User;
use Laranix\Auth\Password\Reset\Events\Created;
use Laranix\Auth\Password\Reset\Events\Failed;
use Laranix\Auth\Password\Reset\Events\Reset as ResetEvent;
use Laranix\Auth\Password\Reset\Events\Updated;
use Laranix\Auth\Password\Events\Updated as PasswordUpdated;

class Manager extends BaseManager implements PasswordHasher
{
    use HashesPasswords;

    /**
     * @inheritDoc
     */
    protected $configKey = 'password';

    /**
     * @inheritDoc
     */
    protected $model = Reset::class;

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
    protected $completedEvent = ResetEvent::class;

    /**
     * Update user after token verified
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $email
     * @param mixed                                           $password
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Laranix\Auth\User\User
     */
    protected function updateUser(Authenticatable $user, string $email, $password = null): Authenticatable
    {
        $user->password = $this->hashUserPassword($password);
        $user->save();

        event(new PasswordUpdated($user));

        return $user;
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $email
     * @return mixed
     */
    protected function canInsertToken(Authenticatable $user, string $email)
    {
        return true;
    }
}
