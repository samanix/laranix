<?php
namespace Laranix\Session;

use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Laranix\Support\Exception\NullValueException;
use SessionHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Handler implements SessionHandlerInterface
{
    /**
     * Config repository
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Laranix\Session\Session
     */
    protected $session;

    /**
     * @var string|null
     */
    protected $sessionRead = null;

    /**
     * Create a new database session handler instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Http\Request                $request
     */
    public function __construct(Repository $config, Request $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
    * {@inheritdoc}
    */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $this->session = $this->readFromDatabase($sessionId, $this->request->getClientIp());

        if ($this->session === null || $this->expired($this->session) || !isset($this->session->session_data)) {
            return null;
        }

        $this->sessionRead = $this->session->session_id;

        return base64_decode($this->session->session_data);
    }

    /**
     * Determine if the session is expired.
     *
     * @param  \Laranix\Session\Session  $session
     * @return bool
     */
    protected function expired(?Session $session)
    {
        if ($session === null) {
            return false;
        }

        if ($session->updated_at === null) {
            return false;
        }

        return Carbon::now()->diffInMinutes($session->updated_at) > $this->config->get('session.lifetime', 120);
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $payload = $this->getPayload($sessionId, $data);

        if ($this->sessionRead === null || $this->sessionRead !== $sessionId) {
            $this->read($sessionId);
        }

        if ($this->session !== null) {
            $this->performUpdate($this->session, $payload);
        } else {
            $this->performInsert($payload);
        }

        return true;
    }

    /**
     * Perform an insert operation on the session ID.
     *
     * @param  array   $payload
     * @return \Laranix\Support\Database\Model
     */
    protected function performInsert(array $payload)
    {
        try {
            return $this->session = Session::createNew($payload);
        } catch (QueryException $e) {
            $this->performUpdate($this->session, $payload);
        }

        return $this->session;
    }

    /**
     * Perform an update operation on the session ID.
     *
     * @param \Laranix\Session\Session $session
     * @param  array                   $payload
     * @return int
     * @throws \Laranix\Support\Exception\NullValueException
     */
    protected function performUpdate(?Session $session, array $payload)
    {
        if ($session === null) {
            throw new NullValueException('Attempted to update null session');
        }

        return $session->updateExisting($payload);
    }

    /**
     * Get session payload
     *
     * @param string $sessionId
     * @param string|array $data
     * @return array
     */
    protected function getPayload(string $sessionId, $data) : array
    {
        return [
            'session_id'    => $sessionId,
            'user_id'       => $this->request->user()->user_id ?? null,
            'ipv4'          => $this->getLongIp(),
            'user_agent'    => $this->request->server('HTTP_USER_AGENT'),
            'session_data'  => base64_encode(is_array($data) ? serialize($data) : $data),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        $this->getModel()
             ->newQuery()
             ->where('session_id', $session_id)
             ->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $this->getModel()
             ->newQuery()
             ->where('updated_at', '<=', Carbon::now()->subSeconds($maxlifetime)->toDateTimeString())
             ->delete();
    }

    /**
     * Read session from database
     *
     * @param string     $sessionId
     * @param string|int $ip
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Session\Session|null
     */
    protected function readFromDatabase(string $sessionId, $ip) : ?Model
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('session_id', $sessionId)
                    ->where('ipv4', $this->getLongIp($ip))
                    ->first();
    }

    /**
     * Get ip in long format
     *
     * @param string|int $ip
     * @return int
     */
    protected function getLongIp($ip = null) : int
    {
        if ($ip === null) {
            $ip = $this->request->getClientIp();
        }

        return is_int($ip) ? $ip : ip2long($ip);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Session\Session
     */
    public function getModel() : Model
    {
        return new Session();
    }
}
