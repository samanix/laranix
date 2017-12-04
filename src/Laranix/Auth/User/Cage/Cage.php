<?php
namespace Laranix\Auth\User\Cage;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laranix\Auth\User\Cage\Events\Created;
use Laranix\Auth\User\Cage\Events\Deleted;
use Laranix\Auth\User\User;
use Laranix\Support\Database\Model;

class Cage extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['level', 'area', 'length', 'reason', 'issuer', 'user_id'];

    /**
     * Hidden attributes
     *
     * @var array
     */
    protected $hidden = ['reason_rendered'];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Created::class,
        'deleted' => Deleted::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    /**
     * Returns when cage expires.
     *
     * @var \Carbon\Carbon
     */
    protected $cageExpires = null;

    /**
     * Returns cage reason
     *
     * @var array
     */
    protected $cageReason = null;

    /**
     * Rendered reason
     *
     * @var string|null
     */
    protected $renderedReason = null;

    /**
     * UserCage constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('laranixauth.cage.table', 'user_cage');
    }

    /**
     * Get cage expiry time.
     *
     * @return \Carbon\Carbon
     */
    public function getExpiryAttribute() : Carbon
    {
        if ($this->cageExpires !== null) {
            return $this->cageExpires;
        }

        return $this->cageExpires = $this->created_at->addMinutes($this->getAttributeFromArray('length'));
    }

    /**
     * Get rendered reason
     *
     * @return null|string
     */
    public function getRenderedReasonAttribute() : ?string
    {
        if ($this->renderedReason !== null) {
            return $this->renderedReason;
        }

        if ($this->config->get('laranixauth.cage.save_rendered', true) &&
            ($rendered = $this->getAttributeFromArray('reason_rendered')) !== null) {
            return $this->renderedReason = $rendered;
        }

        if (($raw = $this->getAttributeFromArray('reason')) !== null) {
            return $this->renderedReason = markdown($raw);
        }

        return null;
    }

    /**
     * Set & save rendered data
     *
     * @param   bool $save
     * @return  mixed
     */
    public function saveRenderedReason(bool $save = true)
    {
        $raw = $this->getAttributeFromArray('reason');

        if (($rendered = markdown($raw)) === $this->getAttributeFromArray('reason_rendered')) {
            return null;
        }

        $this->setAttribute('reason_rendered', $rendered);

        if ($save) {
            $this->save();
        }

        $this->renderedReason = $rendered;

        return $this;
    }

    /**
     * Only get active cages
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $table = $this->config->get('laranixauth.cage.table', 'user_cage');

        return $query->whereRaw(
            "(`{$table}`.`length` = 0 OR 
            (TIMESTAMPDIFF(MINUTE, `{$table}`.`created_at`, NOW()) <= `{$table}`.`length`))"
        );
    }

    /**
     * Get cage issuer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function issuer()
    {
        return $this->hasOne(User::class, 'id', 'issuer_id');
    }

    /**
     * Get caged user
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
     * @return string|null
     */
    public function getIpv4Attribute() : ?string
    {
        $ip = $this->getAttributeFromArray('user_ipv4');

        return $ip !== null ? long2ip($ip) : null;
    }

    /**
     * Get ip attribute
     *
     * @return int|null
     */
    public function getRawIpv4Attribute() : ?int
    {
        return $this->getAttributeFromArray('user_ipv4');
    }

    /**
     * Check if cage is expired
     *
     * @return bool
     */
    public function isExpired() : bool
    {
        return $this->getExpiryAttribute()->timestamp < Carbon::now()->timestamp
            && $this->length !== 0
            && !$this->isRemoved();
    }

    /**
     * Check if cage is removed
     * Make sure to run query with the 'withTrashed' scope
     *
     * @return bool
     */
    public function isRemoved() : bool
    {
        return $this->trashed();
    }

    // TODO join
//    public function rawSelect()
//    {
//        return $this->newQuery()->select()
//    }
}
