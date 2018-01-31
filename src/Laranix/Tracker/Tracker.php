<?php
namespace Laranix\Tracker;

use Laranix\Auth\User\User;
use Laranix\Support\Database\Model;
use Laranix\Tracker\Events\Created;

class Tracker extends Model
{
    const TRACKER_ANY = 1;
    const TRACKER_TRAIL = 2;
    const TRACKER_LIVE = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'ipv4', 'user_agent', 'type_id', 'type', 'level', 'trackable_type', 'data'
    ];

    /**
     * Hidden attributes
     *
     * @var array
     */
    protected $hidden = [
        'data_rendered'
    ];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Created::class,
    ];

    /**
     * Tracker data
     *
     * @var string|null
     */
    protected $renderedData = null;

    /**
     * LaranixUser constructor.
     *
     * @param array $attributes
     * @throws \Laranix\Support\Exception\NullValueException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('tracker.table');
    }

    /**
     * User who triggered the track
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * Get ip attribute
     *
     * @return string
     */
    public function getIpv4Attribute() : string
    {
        return long2ip($this->getAttributeFromArray('ipv4'));
    }

    /**
     * Get ip attribute
     *
     * @return int
     */
    public function getRawIpv4Attribute() : int
    {
        return $this->getAttributeFromArray('ipv4');
    }

    /**
     * Get user agent
     *
     * @return string
     */
    public function getAgentAttribute() : string
    {
        return $this->getAttributeFromArray('user_agent');
    }

    /**
     * Get method attribute
     *
     * @return string
     */
    public function getMethodAttribute() : string
    {
        return strtoupper($this->getAttributeFromArray('request_method'));
    }

    /**
     * Get url attribute
     *
     * @return string
     */
    public function getUrlAttribute() : string
    {
        return $this->getAttributeFromArray('request_url');
    }

    /**
     * Get track type
     *
     * @return int
     */
    public function getTrackTypeAttribute() : int
    {
        return $this->getAttributeFromArray('trackable_type');
    }

    /**
     * Get rendered data
     *
     * @return null|string
     */
    public function getRenderedDataAttribute() : ?string
    {
        if ($this->renderedData !== null) {
            return $this->renderedData;
        }

        if ($this->config->get('tracker.save_rendered', true) &&
            ($rendered = $this->getAttributeFromArray('data_rendered')) !== null) {
            return $this->renderedData = $rendered;
        }

        if (($raw = $this->getAttributeFromArray('data')) !== null) {
            return $this->renderedData = markdown($raw);
        }

        return null;
    }

    /**
     * Set & save rendered data
     *
     * @param   bool $save
     * @return  mixed
     */
    public function saveRenderedData(bool $save = true)
    {
        $raw = $this->getAttributeFromArray('data');

        if ($raw === null) {
            return null;
        }

        if (($rendered = markdown($raw)) === $this->getAttributeFromArray('data_rendered')) {
            return null;
        }

        $this->setAttribute('data_rendered', $rendered);

        if ($save) {
            $this->save();
        }

        $this->renderedData = $rendered;

        return $this;
    }
}
