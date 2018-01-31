<?php
namespace Laranix\Auth\Group;

use Laranix\Auth\Group\Events\Created;
use Laranix\Support\Database\Model;
use Laranix\Auth\User\Groups\UserGroup;

class Group extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'color', 'icon', 'level', 'flags', 'hidden'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'hidden'     => 'boolean',
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

        $this->table = $this->config->get('laranixauth.group.table');
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

        return $this->flagsArray = json_decode($this->getAttributeFromArray('flags'), true);
    }

    /**
     * Users in group
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usergroups()
    {
        return $this->hasMany(UserGroup::class);
    }

    /**
     * Get if group is hidden
     *
     * @return bool
     */
    public function getHiddenAttribute() : bool
    {
        return (bool) $this->getAttributeFromArray('hidden');
    }

    // TODO Join option?
    // TODO Has many through
}
