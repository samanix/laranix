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
    public function open($save_path, $name)
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
    public function read($session_id)
    {
        $this->session = $this->readFromDatabase($session_id, $this->request->getClientIp());

        if ($this->session === null || $this->expired($this->session) || !isset($this->session->data)) {
            return null;
        }

        $this->sessionRead = $this->session->id;

        return base64_decode($this->session->data);
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
    public function write($session_id, $data)
    {
        $payload = $this->getPayload($session_id, $data);

        if ($this->sessionRead === null || $this->sessionRead !== $session_id) {
            $this->read($session_id);
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
     * @param string       $session_id
     * @param string|array $data
     * @return array
     */
    protected function getPayload(string $session_id, $data) : array
    {
        return [
            'id'            => $session_id,
            'user_id'       => $this->request->user()->id ?? null,
            'ipv4'          => $this->getLongIp(),
            'user_agent'    => $this->request->server('HTTP_USER_AGENT'),
            'data'          => base64_encode(is_array($data) ? serialize($data) : $data),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        $this->getModel()
             ->newQuery()
             ->where('id', $session_id)
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
     * @param string     $session_id
     * @param string|int $ip
     * @return \Illuminate\Database\Eloquent\Model|\Laranix\Session\Session|null
     */
    protected function readFromDatabase(string $session_id, $ip) : ?Model
    {
        return $this->getModel()
                    ->newQuery()
                    ->where('id', $session_id)
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
