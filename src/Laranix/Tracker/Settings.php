<?php
namespace Laranix\Tracker;

use Illuminate\Http\Request;
use Laranix\Support\Settings as BaseSettings;

class Settings extends BaseSettings
{
    const FLAG_MAX = -1;
    const IP_MIN = 0;
    const IP_MAX = 4294967295;

    /**
     * Names of required properties
     *
     * @var array|string
     */
    protected $required = [
        'user'      => 'optional|int',
        'type'      => 'string',
        'typeId'    => 'optional|int',
        'itemId'    => 'optional|int',
        'trackType' => 'int',
        'data'      => 'optional|string',
    ];

    /**
     * User to associate track with
     *
     * @var int
     */
    public $user = -1;

    /**
    * Type of track
    *
    * @var string
    */
    public $type;

    /**
     * ID of the type
     *
     * @var int|null
     */
    public $typeId = null;

    /**
     * Type of track
     *
     * @var int|null
     */
    public $itemId = null;

    /**
     * @var int
     */
    public $flagLevel = 0;

    /**
     * @var int
     */
    public $trackType = Tracker::TRACKER_TRAIL;

    /**
     * Data to track
     *
     * @var string|null
     */
    public $data = null;

    /**
     * IP of user
     *
     * @var int
     */
    protected $ipv4;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @var string
     */
    protected $method = null;

    /**
     * Request url
     *
     * @var string
     */
    protected $url = null;

    /**
     * Settings constructor.
     *
     * @param \Illuminate\Http\Request $request
     * @param array                    $attributes
     */
    public function __construct(Request $request, array $attributes = [])
    {
        parent::__construct($attributes);

        $this->parseDefaults($request);
    }

    /**
     * Parse default values
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function parseDefaults(Request $request)
    {
        $this->ipv4         = ip2long($request->getClientIp());
        $this->userAgent    = $request->server('HTTP_USER_AGENT');
        $this->method       = $request->getMethod();
        $this->url          = urlSelf();

        if ($this->user === -1) {
            /** @var \Laranix\Auth\User\User $user */
            $user = $request->user();

            $this->user = $user === null ? null : $user->getAuthIdentifier();
        }
    }

    /**
     * @return int
     */
    public function ipv4()
    {
        return $this->ipv4;
    }

    /**
     * @return string
     */
    public function userAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return string
     */
    public function requestMethod()
    {
        return strtoupper($this->method);
    }

    /**
     * @return string
     */
    public function requestUrl()
    {
        return $this->url;
    }
}
