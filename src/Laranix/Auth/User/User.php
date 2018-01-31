<?php
namespace Laranix\Auth\User;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;
use Laranix\Auth\Password\Hasher as PasswordHasher;
use Laranix\Auth\Password\HashesPasswords;
use Laranix\Auth\User\Events\Created;
use Laranix\Auth\User\Token\Api\ApiTokenProvider;
use Laranix\Auth\User\Token\Api\GetsApiTokens;
use Laranix\Auth\Group\Group;
use Laranix\Auth\User\Groups\UserGroup;
use Laranix\Auth\User\Cage\Cage;
use Laranix\Support\Database\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    ApiTokenProvider,
    PasswordHasher,
    LastLoginUpdater
{
    use Notifiable, Authenticatable, Authorizable, CanResetPassword, GetsApiTokens, HashesPasswords, UpdatesLastLogin;

    //TODO Override notifications
    // Including sendPasswordResetNotification

    const USER_UNVERIFIED = 0;
    const USER_ACTIVE = 1;
    const USER_SUSPENDED = 2;
    const USER_BANNED = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'username', 'avatar', 'first_name', 'last_name', 'password', 'company', 'timezone'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'api_token'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_login',
    ];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => Created::class,
    ];

    /**
     * Users primary groups.
     *
     * @var \Laranix\Support\Database\Model
     */
    protected $userPrimaryGroup = null;

    /**
     * User flags.
     *
     * @var array
     */
    protected $flags = null;

    /**
     * Active cages for the user
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $activeCagesCollection = null;

    /**
     * LaranixUser constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = $this->config->get('laranixauth.user.table');
    }

    /**
     * User groups
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usergroups()
    {
        return $this->hasMany(UserGroup::class, 'user_id');
    }

//    /**
//     * Get user groups
//     *
//     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
//     */
//    public function groups()
//    {
//        return $this->hasManyThrough(
//            Group::class,
//            UserGroup::class
//        );
//    }

    /**
     * All user cages
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allCages()
    {
        return $this->hasMany(Cage::class, 'user_id');
    }

    /**
     * Active cages only
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeCages()
    {
        return $this->allCages()->active();
    }

    /**
     * Get users primary group
     *
     * @return \Laranix\Auth\Group\Group|\Laranix\Support\Database\Model|null
     */
    public function primaryGroup() : ?Model
    {
        if ($this->userPrimaryGroup !== null) {
            return $this->userPrimaryGroup;
        }

        $usergroups = $this->usergroups;

        foreach ($usergroups as $id => $usergroup) {
            if ($usergroup->primary) {
                return $this->userPrimaryGroup = $usergroup->group;
            }
        }

        return $this->userPrimaryGroup = $usergroups[0]->group;
    }

    /**
     * Get active cages from all cages
     * This saves a query
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCages() : Collection
    {
        if ($this->activeCagesCollection !== null) {
            return $this->activeCagesCollection;
        }

        $this->activeCagesCollection = new Collection();

        foreach ($this->allCages as $id => $cage) {
            if (!$cage->isExpired() && !$cage->isRemoved()) {
                $this->activeCagesCollection->push($cage);
            }
        }

        return $this->activeCagesCollection;
    }

    /**
     * Get all user flags.
     *
     * @return array
     */
    public function getUserFlags() : array
    {
        if ($this->flags !== null) {
            return $this->flags;
        }

        $this->flags = [];
        $usergroups = $this->usergroups;

        foreach ($usergroups as $usergroup) {
            $group = $usergroup->group;
            $flags = $group->flags;

            if (!empty($flags)) {
                $this->flags = array_merge($this->flags, $flags);
            }
        }

        return $this->flags = empty($this->flags) ? [] : array_flip($this->flags);
    }

    /**
     * Check if user has flag.
     *
     * @param string|array $flag
     *
     * @return bool
     */
    public function hasFlag($flag) : bool
    {
        if (is_array($flag)) {
            return $this->hasFlags($flag);
        }

        return isset($this->getUserFlags()[$flag]);
    }

    /**
     * Check if user has flags
     *
     * @param array $flags
     * @param bool  $matchAll
     * @return bool
     */
    public function hasFlags(array $flags, bool $matchAll = false) : bool
    {
        $userFlags = $this->getUserFlags();

        foreach ($flags as $flag) {
            if (!isset($userFlags[$flag]) && $matchAll) {
                return false;
            }

            if (isset($userFlags[$flag]) && !$matchAll) {
                return true;
            }
        }

        return true;
    }

    /**
     * Check if user has group.
     *
     * @param \Laranix\Auth\Group\Group $group
     *
     * @return bool
     */
    public function hasGroup(Group $group) : bool
    {
        return $this->hasGroupId($group->id);
    }

    /**
     * Check if user has group.
     *
     * @param int $id
     * @return bool
     */
    public function hasGroupId(int $id) : bool
    {
        $usergroups = $this->usergroups;

        foreach ($usergroups as $index => $usergroup) {
            if ($usergroup->group_id === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has cage.
     *
     * @param string $area
     * @param int    $level
     * @param bool   $activeOnly    If false, will search through all cages, otherwise will only search active ones
     * @return \Laranix\Auth\User\Cage\Cage|\Laranix\Support\Database\Model|null
     */
    public function hasCage(string $area, int $level = 0, bool $activeOnly = true) : ?Model
    {
        $cages = $activeOnly ? $this->getActiveCages() : $this->allCages;

        foreach ($cages as $index => $cage) {
            if ($cage->area === $area && $cage->level >= $level) {
                return $cage;
            }
        }

        return null;
    }

    /**
     * Get users full name
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->getAttributeFromArray('first_name') . ' ' . $this->getAttributeFromArray('last_name');
    }
}
