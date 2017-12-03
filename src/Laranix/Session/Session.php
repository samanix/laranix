<?php
namespace Laranix\Session;

use Laranix\Support\Database\Model;
use Laranix\Support\Database\HasCompositePrimaryKey;
use Laranix\Auth\User\User;

class Session extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var string
     */
    protected $primaryKey = ['id', 'ipv4'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'ipv4', 'user_agent', 'data'];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = ['none'];

    /**
     * Session constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('session.table', 'sessions');
    }

    /**
     * User who owns the session
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }

    /**
     * Get long IP as ipv4
     *
     * @return string
     */
    public function getIpv4Attribute()
    {
        return long2ip($this->getAttributeFromArray('ipv4'));
    }
}
