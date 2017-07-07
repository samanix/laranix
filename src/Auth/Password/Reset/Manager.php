<?php
namespace Laranix\Auth\Password\Reset;

use Illuminate\Contracts\Auth\Authenticatable;
use Laranix\Auth\Password\{Hasher as PasswordHasher, HashesPasswords};
use Laranix\Auth\User\Token\{
    Manager as BaseManager, MailSettings
};
use Laranix\Auth\Password\Reset\Mail as PasswordMail;
use Laranix\Auth\User\User;
use Laranix\Auth\Password\Reset\Events\{
    Created, Failed, Reset as ResetEvent, Updated
};
use Laranix\Auth\Password\Events\Updated as PasswordUpdated;

class Manager extends BaseManager implements PasswordHasher
{
    use HashesPasswords;

    /**
     * The model for the tokens
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model = Reset::class;

    /**
     * Key to use inside the laranixauth config
     *
     * @var string
     */
    protected $configKey = 'password';

    /**
     * The mail class name to create the email from
     *
     * @var \Laranix\Support\Mail\Mail
     */
    protected $mailTemplateClass = PasswordMail::class;

     /**
     * The mail options class to use in the mail
     *
     * @var string
     */
    protected $mailOptionsClass = MailSettings::class;

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
