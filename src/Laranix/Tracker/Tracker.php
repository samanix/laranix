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
     * @var string
     */
    protected $primaryKey = 'tracker_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'ipv4', 'user_agent', 'tracker_type_id', 'tracker_type', 'flag_level', 'trackable_type', 'tracker_data'];

    /**
     * Hidden attributes
     *
     * @var array
     */
    protected $hidden = ['tracker_data_rendered'];

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

        $this->table = $this->config->get('tracker.table', 'tracker');
    }

    /**
     * User who triggered the track
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }

    /**
     * Get Tracker ID
     *
     * @return int
     */
    public function getIdAttribute() : int
    {
        return $this->getAttributeFromArray('tracker_id');
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
     * Get type of track
     *
     * @return string
     */
    public function getTypeAttribute() : string
    {
        return $this->getAttributeFromArray('tracker_type');
    }

    /**
     * Get tracker type Id
     *
     * @return int|null
     */
    public function getTypeIdAttribute() : ?int
    {
        return $this->getAttributeFromArray('tracker_type_id');
    }

    /**
     * Get item Id of track
     *
     * @return int|null
     */
    public function getItemIdAttribute() : ?int
    {
        return $this->getAttributeFromArray('tracker_item_id');
    }

    /**
     * Get tracker level attribute
     *
     * @return int
     */
    public function getLevelAttribute() : int
    {
        return $this->getAttributeFromArray('flag_level');
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
     * Get tracker data
     *
     * @return string|null
     */
    public function getDataAttribute() : ?string
    {
        return $this->getAttributeFromArray('tracker_data');
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
            ($rendered = $this->getAttributeFromArray('tracker_data_rendered')) !== null) {
            return $this->renderedData = $rendered;
        }

        if (($raw = $this->getAttributeFromArray('tracker_data')) !== null) {
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
        $raw = $this->getAttributeFromArray('tracker_data');

        if ($raw === null) {
            return null;
        }

        if (($rendered = markdown($raw)) === $this->getAttributeFromArray('tracker_data_rendered')) {
            return null;
        }

        $this->setAttribute('tracker_data_rendered', $rendered);

        if ($save) {
            $this->save();
        }

        $this->renderedData = $rendered;

        return $this;
    }
}
