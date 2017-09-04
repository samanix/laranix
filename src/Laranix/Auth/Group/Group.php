<?php
namespace Laranix\Auth\Group;

use Laranix\Auth\Group\Events\Created;
use Laranix\Support\Database\Model;
use Laranix\Auth\User\Groups\UserGroup;

class Group extends Model
{
    /**
     * @var string
     */
    protected $primaryKey = 'group_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['group_name', 'group_color', 'group_icon', 'group_level', 'group_flags', 'is_hidden'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_hidden'     => 'boolean',
    ];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Created::class,
    ];

    /**
     * @var array
     */
    protected $flagsArray;

    /**
     * Groups constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('laranixauth.groups.table', 'groups');
    }

    /**
     * Get flags as array
     *
     * @return array
     */
    public function getFlagsAttribute() : array
    {
        if ($this->flagsArray !== null) {
            return $this->flagsArray;
        }

        return $this->flagsArray = json_decode($this->getAttributeFromArray('group_flags'), true);
    }

    /**
     * Users in group
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usergroups()
    {
        return $this->hasMany(UserGroup::class, 'group_id');
    }

    /**
     * Get group Id
     *
     * @return int
     */
    public function getIdAttribute() : int
    {
        return $this->getAttributeFromArray('group_id');
    }

    /**
     * Get group name
     *
     * @return string
     */
    public function getNameAttribute() : string
    {
        return $this->getAttributeFromArray('group_name');
    }

    /**
     * Get if group is hidden
     *
     * @return bool
     */
    public function getHiddenAttribute() : bool
    {
        return (bool) $this->getAttributeFromArray('is_hidden');
    }

    // TODO Join option?
}
