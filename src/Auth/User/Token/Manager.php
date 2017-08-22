<?php
namespace Laranix\Auth\User\Token;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Mail\Mailer;
use Laranix\Auth\User\User;
use Laranix\Support\Database\Model;
use Laranix\Support\Exception\InvalidPermissionException;
use Laranix\Support\Exception\NullValueException;
use Laranix\Support\Mail\Mail;
use Laranix\Support\Mail\MailSettings;
use Laranix\Support\IO\Url\Url;

abstract class Manager
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Mail\Mailer
     */
    protected $mailer;

    /**
     * The model class name for the tokens
     *
     * @var \Laranix\Support\Database\Model
     */
    protected $model = null;

    /**
     * Key to use inside the laranixauth config
     *
     * @var string
     */
    protected $configKey = null;

    /**
     * The mail class name to create the email from
     *
     * @var \Laranix\Support\Mail\Mail
     */
    protected $mailTemplateClass = Mail::class;

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
    protected $createdEvent = null;

    /**
     * Updated event class name
     *
     * @var string
     */
    protected $updatedEvent = null;

    /**
     * Failed event class name
     *
     * @var string
     */
    protected $failedEvent = null;


    /**
     * Completed event class name
     *
     * @var string
     */
    protected $completedEvent = null;

    /**
     * Update user after token verified
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $email
     * @param mixed                                           $extra
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Laranix\Auth\User\User
     */
    abstract protected function updateUser(Authenticatable $user, string $email, $extra = null) : Authenticatable;

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $email
     * @return mixed
     */
    abstract protected function canInsertToken(Authenticatable $user, string $email);

    /**
     * Tokens constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository   $config
     * @param \Illuminate\Contracts\Mail\Mailer         $mailer
     */
    public function __construct(Repository $config, Mailer $mailer)
    {
        $this->config       = $config;
        $this->mailer       = $mailer;

        $this->checkPropertiesAreSet();
    }

    /**
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function checkPropertiesAreSet()
    {
        if ($this->model === null || !class_exists($this->model) || $this->configKey === null) {
            throw new NullValueException('Null properties detected on UserAccountTokens');
        }
    }

    /**
     * Get token model
     *
     * @return \Laranix\Auth\User\Token\Token
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function getModel() : Token
    {
        return new $this->model;
    }

    /**
     * Create and insert token
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param null|string                                     $email
     * @return \Laranix\Support\Database\Model
     * @throws \Laranix\Support\Exception\InvalidPermissionException
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function createToken(?Authenticatable $user, ?string $email = null) : Model
    {
        if ($user === null) {
            throw new NullValueException("User cannot be null");
        }

        $email = $email ?? $user->email;

        if ($this->canInsertToken($user, $email)) {
            return $this->insertToken($user, $this->generateToken($this->config->get('app.key')), $email);
        }

        throw new InvalidPermissionException('Cannot create token');
    }

    /**
     * Renew a token
     *
     * @param \Laranix\Auth\User\Token\Token|null $token
     * @return \Laranix\Auth\User\Token\Token|\Laranix\Support\Database\Model
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function renewToken(?Token $token) : Token
    {
        if ($token === null) {
            throw new NullValueException("Token cannot be null");
        }

        $token->updateExisting([
            'token' => $this->generateToken($this->config->get('app.key')),
        ]);

        $this->fireUpdatedEvent($token->user, $token);

        return $token;
    }

    /**
     * Insert a token
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User $user
     * @param string                                          $token
     * @return \Laranix\Auth\User\Token\Token|\Laranix\Support\Database\Model
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function insertToken(?Authenticatable $user, string $token, string $email) : Token
    {
        if ($user === null) {
            throw new NullValueException("User cannot be null");
        }

        /** @var \Laranix\Auth\User\Token\Token $row */
        $row = $this->getModel()
                    ->updateOrCreate([
                        'user_id'   => $user->getAuthIdentifier(),
                        'email'     => $email,
                        'token'     => $token,
                    ], 'user_id');

        if ($row->wasRecentlyCreated) {
            $this->fireCreatedEvent($user, $row);
        } else {
            $this->fireUpdatedEvent($user, $row);
        }

        return $row;
    }


    /**
     * Fetch token from the database
     *
     * @param string    $token
     * @return \Illuminate\Database\Eloquent\Builder|\Laranix\Support\Database\Model|\Laranix\Auth\User\Token\Token
     */
    public function fetchToken(string $token) : ?Token
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('token', $token)
                    ->first();
    }

    /**
     * Fetch token from the database by email
     *
     * @param string $email
     * @return \Laranix\Auth\User\Token\Token|null
     */
    public function fetchTokenByEmail(string $email) : ?Token
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('email', $email)
                    ->first();
    }

    /**
     * Process token
     *
     * @param null|string $token
     * @param null|string $email
     * @param mixed       $extra
     * @return int
     */
    public function processToken(?string $token, ?string $email, $extra = null) : int
    {
        if ($email === null) {
            $this->fireFailedEvent(null, null, $email);

            return Token::TOKEN_INVALID;
        }

        $row = $this->fetchToken($token);

        if ($row === null) {
            $this->fireFailedEvent(null, null, $email);

            return Token::TOKEN_INVALID;
        }

        $user = $row->user;

        if ($row->email !== $email) {
            $this->fireFailedEvent($user, $row, $email);

            return Token::TOKEN_INVALID;
        }

        if ($this->tokenExpired($row->updated_at)) {
            $row->tokenStatus = Token::TOKEN_EXPIRED;

            $this->fireFailedEvent($user, $row, $email);

            return Token::TOKEN_EXPIRED;
        }

        $row->delete();

        $this->fireCompletedEvent($this->updateUser($user, $email, $extra));

        return Token::TOKEN_VALID;
    }

    /**
     * Fire created event
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     */
    protected function fireCreatedEvent(Authenticatable $user, Token $token)
    {
        if ($this->createdEvent === null || !class_exists($this->createdEvent)) {
            return;
        }

        event(new $this->createdEvent($user, $token));
    }

    /**
     * Fire updated event
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     */
    protected function fireUpdatedEvent(Authenticatable $user, Token $token)
    {
        if ($this->updatedEvent === null || !class_exists($this->updatedEvent)) {
            return;
        }

        event(new $this->updatedEvent($user, $token));
    }

    /**
     * Fire failed event
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param \Laranix\Auth\User\Token\Token             $token
     * @param string                                     $email
     */
    protected function fireFailedEvent(?Authenticatable $user, ?Token $token, ?string $email)
    {
        if ($this->failedEvent === null || !class_exists($this->failedEvent)) {
            return;
        }

        event(new $this->failedEvent($user, $token, $email));
    }

    /**
     * Fire completed event
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     */
    protected function fireCompletedEvent(Authenticatable $user)
    {
        if ($this->completedEvent === null || !class_exists($this->completedEvent)) {
            return;
        }

        event(new $this->completedEvent($user));
    }

    /**
     * Send mail
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User                 $user
     * @param \Laranix\Auth\User\Token\Token|\Laranix\Support\Database\Model $token
     * @return \Laranix\Support\Mail\MailSettings
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function sendMail(?Authenticatable $user, ?Token $token) : MailSettings
    {
        if ($user === null || $token === null) {
            throw new NullValueException("User cannot be null");
        }

        if (!isset($token->email) || filter_var($token->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new NullValueException("Valid email address must be provided");
        }

        $options = $this->generateMailOptions($user, $token);

        $this->mailer->send($this->createMail($options));

        return $options;
    }

    /**
     * Create email options
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User   $user
     * @param \Laranix\Auth\User\Token\Token|null               $token
     * @return \Laranix\Support\Mail\MailSettings
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function generateMailOptions(?Authenticatable $user, ?Token $token) : MailSettings
    {
        if ($user === null || $token === null) {
            throw new NullValueException('Cannot generate options when null value supplied');
        }

        Carbon::setLocale($this->config->get('app.locale', 'en'));
        $expiry = $this->getTokenExpiry();

        $route = $this->generateRoute($token);

        /** @var MailSettings $options */
        return new $this->mailOptionsClass([
            'to'            => [['email' => $token->email, 'name' => $user->username ?? $token->email]],

            // TODO Default values
            'view'          => $this->config->get("laranixauth.{$this->configKey}.mail.view"),
            'subject'       => $this->config->get("laranixauth.{$this->configKey}.mail.subject"),
            'markdown'      => $this->config->get("laranixauth.{$this->configKey}.mail.markdown", true),

            'userId'        => $user->getAuthIdentifier(),
            'username'      => $user->username,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'full_name'     => $user->full_name,

            'token'         => $token->token,
            'expiry'        => $expiry->format('jS F Y g:i:sA T'),
            'humanExpiry'   => $expiry->diffForHumans(null, true),
            'url'           => $route,
            'baseurl'       => substr($route, 0, strpos($route, '?')),
        ]);
    }

    /**
     * Create route for mail
     *
     * @param \Laranix\Auth\User\Token\Token|null             $token
     * @return string
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function generateRoute(?Token $token) : string
    {
        if ($token === null) {
            throw new NullValueException('Cannot generate route when null value supplied');
        }

        $route = route($this->config->get("laranixauth.{$this->configKey}.route"), [], false);

        return Url::to($route, ['token' => $token->token, 'email' => $token->email]);
    }

    /**
     * Create a token
     *
     * @param string $key
     * @return string
     */
    protected function generateToken(string $key) : string
    {
        return hash_hmac('sha256', str_random(30), $key);
    }

    /**
     * Get token expiry time
     *
     * @return \Carbon\Carbon
     */
    protected function getTokenExpiry() : Carbon
    {
        return Carbon::now()->addMinutes($this->getTokenLifetime());
    }

    /**
     * Return true if token has expired
     *
     * @param \Carbon\Carbon $updated
     * @return bool
     */
    protected function tokenExpired(Carbon $updated) : bool
    {
        return Carbon::now()->diffInMinutes($updated) > $this->getTokenLifetime();
    }

    /**
     * Create the email class
     *
     * @param \Laranix\Support\Mail\MailSettings $options
     * @return \Laranix\Support\Mail\Mail
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function createMail(MailSettings $options) : Mail
    {
        if ($this->mailTemplateClass === null || !class_exists($this->mailTemplateClass)) {
            throw new NullValueException('Mail template class cannot be null');
        }

        return new $this->mailTemplateClass($options);
    }

    /**
     * Get the token lifetime from config
     *
     * @return int
     */
    protected function getTokenLifetime() : int
    {
        return $this->config->get("laranixauth.{$this->configKey}.expiry", 60);
    }
}
