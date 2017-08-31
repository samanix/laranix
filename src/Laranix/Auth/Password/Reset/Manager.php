<?php
namespace Laranix\Auth\Password\Reset;

use Illuminate\Contracts\Auth\Authenticatable;
use Laranix\Auth\Password\Hasher as PasswordHasher;
use Laranix\Auth\Password\HashesPasswords;
use Laranix\Auth\User\Token\Manager as BaseManager;
use Laranix\Auth\User\Token\MailSettings;
use Laranix\Auth\Password\Reset\Mail as PasswordMail;
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
     * Created event class name
     *
     * @var string
     */
    protected $createdEvent = Created::class;

    /**
     * Updated event class name
     *
     * @var string
     */
    protected $updatedEvent = Updated::class;

    /**
     * Failed event class name
     *
     * @var string
     */
    protected $failedEvent = Failed::class;

    /**
     * Completed event class name
     *
     * @var string
     */
    protected $completedEvent = ResetEvent::class;

    /**
     * Get the class name for the model
     *
     * @return string
     */
    protected function getModelClass() : string
    {
        return Reset::class;
    }

    /**
     * Get the config key for the config file
     *
     * @return string
     */
    protected function getConfigKey() : string
    {
        return 'password';
    }

    /**
     * Mail settings class
     *
     * @return string
     */
    protected function getMailSettingsClass() : string
    {
        return MailSettings::class;
    }

    /**
     * Mail class
     *
     * @return string
     */
    protected function getMailTemplateClass() : string
    {
        return PasswordMail::class;
    }

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
