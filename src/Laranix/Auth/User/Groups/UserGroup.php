<?php
namespace Laranix\Auth\User\Groups;

use Laranix\Auth\User\Groups\Events\Added;
use Laranix\Auth\User\Groups\Events\Removed;
use Laranix\Auth\User\User;
use Laranix\Support\Database\HasCompositePrimaryKey;
use Laranix\Support\Database\Model;
use Laranix\Auth\Group\Group;

class UserGroup extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @var string
     */
    protected $primaryKey = ['user_id', 'group_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'group_id', 'is_primary', 'is_hidden'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_primary'    => 'boolean',
        'is_hidden'     => 'boolean',
    ];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Added::class,
        'deleted' => Removed::class,
    ];

    /**
     * UserGroups constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('laranixauth.usergroups.table', 'usergroups');
    }

    /**
     * Group details
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group()
    {
        return $this->hasOne(Group::class, 'group_id', 'group_id');
    }

    /**
     * Users
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get if user group is primary
     *
     * @return bool
     */
    public function getPrimaryAttribute() : bool
    {
        return (bool) $this->getAttributeFromArray('is_primary');
    }

    /**
     * Get if user group is hidden
     *
     * @return bool
     */
    public function getHiddenAttribute() : bool
    {
        return (bool) $this->getAttributeFromArray('is_hidden');
    }

    // TODO Join option?
}
