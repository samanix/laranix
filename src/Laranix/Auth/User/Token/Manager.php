<?php
namespace Laranix\Auth\User\Token;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Config\Repository;
use Laranix\Auth\User\User;
use Laranix\Support\Database\Model;
use Laranix\Support\Exception\InvalidPermissionException;
use Laranix\Support\Exception\NullValueException;
use Laranix\Support\Mail\Mail;
use Laranix\Support\Mail\Mailer;
use Laranix\Support\Mail\MailSettings;
use Laranix\Support\ValidatesRequiredProperties;

abstract class Manager
{
    use ValidatesRequiredProperties;

    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Laranix\Support\Mail\Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $configKey;

    /**
     * @var string
     */
    protected $model;

    /**
     * Created event class name
     *
     * @var string
     */
    protected $createdEvent;

    /**
     * Updated event class name
     *
     * @var string
     */
    protected $updatedEvent;

    /**
     * Failed event class name
     *
     * @var string
     */
    protected $failedEvent;

    /**
     * Completed event class name
     *
     * @var string
     */
    protected $completedEvent;

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
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Laranix\Support\Mail\Mailer            $mailer
     * @throws \Laranix\Support\Exception\InvalidTypeException
     */
    public function __construct(Repository $config, Mailer $mailer)
    {
        $this->config   = $config;
        $this->mailer   = $mailer;

        $this->validateProperties([
            'model'         => 'is_a:' . Model::class,
            'configKey'     => 'string',
        ]);
    }

    /**
     * Get token model
     *
     * @return \Laranix\Auth\User\Token\Token
     */
    public function getModel(): Token
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
     * @param string                                          $email
     * @return \Laranix\Auth\User\Token\Token
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function insertToken(?Authenticatable $user, string $token, string $email) : Token
    {
        if ($user === null) {
            throw new NullValueException("User cannot be null");
        }

        /** @var \Laranix\Auth\User\Token\Token $row */
        $row = $this->getModel()
                    ->updateOrCreateNew([
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

        if (!hash_equals($row->email, $email ?? '')) {
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
     * @param \Laranix\Auth\User\Token\Token             $token
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
     * @param \Laranix\Auth\User\Token\Token             $token
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
     * @param \Illuminate\Contracts\Auth\Authenticatable|User                $user
     * @param \Laranix\Auth\User\Token\Token|\Laranix\Support\Database\Model $token
     * @return \Laranix\Support\Mail\MailSettings
     * @throws \Laranix\Support\Exception\NullValueException
     * @throws \Exception
     */
    public function sendMail(?Authenticatable $user, ?Token $token) : MailSettings
    {
        if ($user === null || $token === null) {
            throw new NullValueException('User cannot be null');
        }

        if (!isset($token->email) || filter_var($token->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new NullValueException('Valid email address must be provided');
        }

        return $this->mailer->send(
            $this->createMailSettings($user, $token)
        );
    }

    /**
     * Create email options
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|User   $user
     * @param \Laranix\Auth\User\Token\Token|null               $token
     * @return array
     */
    protected function createMailSettings(Authenticatable $user, Token $token): array
    {
        Carbon::setLocale($this->config->get('app.locale', 'en'));
        $expiry = $this->getTokenExpiry();

        $route = $this->generateRoute($token);

        return [
            'to'            => [['email' => $token->email, 'name' => $user->username ?? $token->email]],

            'view'          => $this->config->get("laranixauth.{$this->configKey}.mail.view"),
            'subject'       => $this->config->get("laranixauth.{$this->configKey}.mail.subject"),
            'markdown'      => $this->config->get("laranixauth.{$this->configKey}.mail.markdown", true),

            'userId'        => $user->getAuthIdentifier(),
            'username'      => $user->username,
            'firstName'     => $user->first_name,
            'lastName'      => $user->last_name,
            'fullName'      => $user->full_name,

            'token'         => $token->token,
            'expiry'        => $expiry->format('jS F Y g:i:sA T'),
            'humanExpiry'   => $expiry->diffForHumans(null, true),
            'url'           => $route,
            'baseurl'       => substr($route, 0, strpos($route, '?')),
        ];
    }

    /**
     * Create route for mail
     *
     * @param \Laranix\Auth\User\Token\Token             $token
     * @return string
     */
    protected function generateRoute(Token $token) : string
    {
        $route = route($this->config->get("laranixauth.{$this->configKey}.route"), [], false);

        return urlTo($route, ['token' => $token->token, 'email' => $token->email]);
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
     * Get the token lifetime from config
     *
     * @return int
     */
    protected function getTokenLifetime() : int
    {
        return $this->config->get("laranixauth.{$this->configKey}.expiry", 60);
    }
}
